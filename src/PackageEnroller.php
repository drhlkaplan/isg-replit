<?php
namespace ISG;

/**
 * Handles package enrollment with sequential module unlocking.
 *
 * Unlock order (by sort_order):
 *   1. on_degerlendirme  → unlocked immediately at enrollment (no score limit — always passes)
 *   2. genel             → unlocked as soon as on_degerlendirme is completed (exam OR SCORM)
 *   3. saglik            → unlocked after genel completed
 *   4. teknik            → unlocked after saglik completed
 *   5. ise_ozgu          → unlocked after teknik completed
 *   6. final_sinav       → unlocked only after ALL preceding modules completed
 *
 * Completion triggers:
 *   - SCORM lesson_status = passed/completed  (SCORMController → onModuleCompleted)
 *   - Exam submission, type = 'pre'           (ExamController  → onModuleCompleted)
 *   - Exam submission, type = 'final', passed (ExamController  → onModuleCompleted)
 *   - YYZ face-to-face attendance             (AttendController → onModuleCompleted)
 */
class PackageEnroller
{
    public function __construct(private DB $db) {}

    /**
     * Enroll a user in a package and auto-enroll all child modules
     * with sequential locking (only first module unlocked).
     */
    public function enrollPackage(int $userId, int $packageId): int
    {
        // Enroll in the parent package itself
        if (!$this->db->exists('enrollments', 'user_id = ? AND course_id = ?', [$userId, $packageId])) {
            $this->db->insert('enrollments', [
                'user_id'   => $userId,
                'course_id' => $packageId,
                'status'    => 'enrolled',
                'is_locked' => 0,
            ]);
        }

        // Get all child modules ordered by sort_order
        $modules = $this->db->fetchAll(
            "SELECT id, topic_type, sort_order FROM courses
             WHERE parent_course_id = ? AND status = 'active'
             ORDER BY sort_order ASC, id ASC",
            [$packageId]
        );

        $enrolled = 1; // count the package itself
        foreach ($modules as $i => $mod) {
            // First module (on_degerlendirme or lowest sort_order) → unlocked
            $isLocked = ($i === 0) ? 0 : 1;
            if (!$this->db->exists('enrollments', 'user_id = ? AND course_id = ?', [$userId, $mod['id']])) {
                $this->db->insert('enrollments', [
                    'user_id'   => $userId,
                    'course_id' => $mod['id'],
                    'status'    => 'enrolled',
                    'is_locked' => $isLocked,
                ]);
                $enrolled++;
            }
        }

        return $enrolled;
    }

    /**
     * Called after a module is completed (SCORM pass or YYZ attendance).
     * Unlocks the next module(s) in sequence for the user.
     */
    public function onModuleCompleted(int $userId, int $courseId): void
    {
        // Determine parent package
        $course = $this->db->fetch(
            'SELECT id, topic_type, parent_course_id FROM courses WHERE id = ?',
            [$courseId]
        );
        if (!$course || !$course['parent_course_id']) {
            return; // Not a child module — nothing to unlock
        }

        $packageId = (int)$course['parent_course_id'];

        // Get ALL modules of this package ordered by sort_order
        $modules = $this->db->fetchAll(
            "SELECT id, topic_type, sort_order FROM courses
             WHERE parent_course_id = ? AND status = 'active'
             ORDER BY sort_order ASC, id ASC",
            [$packageId]
        );

        if (empty($modules)) {
            return;
        }

        // Separate final_sinav from the rest
        $nonFinal = array_filter($modules, fn($m) => $m['topic_type'] !== 'final_sinav');
        $finalMod = array_values(array_filter($modules, fn($m) => $m['topic_type'] === 'final_sinav'));

        // Find the index of the completed module
        $completedIdx = null;
        $moduleList   = array_values($nonFinal);
        foreach ($moduleList as $idx => $mod) {
            if ($mod['id'] === $courseId) {
                $completedIdx = $idx;
                break;
            }
        }

        // If completed module is in nonFinal list, unlock the next nonFinal module
        if ($completedIdx !== null && isset($moduleList[$completedIdx + 1])) {
            $this->unlockEnrollment($userId, $moduleList[$completedIdx + 1]['id']);
        }

        // If ALL non-final modules are completed, unlock the final exam
        if (!empty($finalMod)) {
            $allNonFinalDone = $this->allCompleted($userId, array_column($moduleList, 'id'));
            if ($allNonFinalDone) {
                $this->unlockEnrollment($userId, $finalMod[0]['id']);
            }
        }

        // Update parent package progress
        $this->updatePackageProgress($userId, $packageId, $modules);
    }

    /**
     * Unlock a specific enrollment row for a user.
     */
    private function unlockEnrollment(int $userId, int $courseId): void
    {
        $this->db->query(
            'UPDATE enrollments SET is_locked = 0 WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );
    }

    /**
     * Check whether all given course IDs are completed for this user.
     */
    private function allCompleted(int $userId, array $courseIds): bool
    {
        if (empty($courseIds)) {
            return true;
        }
        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
        $completed = $this->db->fetchAll(
            "SELECT course_id FROM enrollments
             WHERE user_id = ? AND course_id IN ($placeholders) AND status = 'completed'",
            array_merge([$userId], $courseIds)
        );
        return count($completed) === count($courseIds);
    }

    /**
     * Recalculate and update the parent package enrollment progress.
     *
     * Completion rule:
     *   - ALL child modules (including final_sinav) must be status='completed'
     *   - The final_sinav exam must have a best score >= 60
     *
     * On completion: issue a package-level certificate (if not already issued).
     */
    private function updatePackageProgress(int $userId, int $packageId, array $modules): void
    {
        if (empty($modules)) {
            return;
        }
        $moduleIds    = array_column($modules, 'id');
        $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
        $doneCount    = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM enrollments
             WHERE user_id = ? AND course_id IN ($placeholders) AND status = 'completed'",
            array_merge([$userId], $moduleIds)
        )['cnt'] ?? 0;

        $percent = (int)round(($doneCount / count($modules)) * 100);

        // All modules done in enrollment sense — now check the final exam score gate
        $allModulesDone = ($doneCount === count($modules));
        $finalExamPassed = true; // default true if no final_sinav exam exists

        if ($allModulesDone) {
            $finalMods = array_values(array_filter($modules, fn($m) => $m['topic_type'] === 'final_sinav'));
            if (!empty($finalMods)) {
                $finalCourseId = $finalMods[0]['id'];
                $finalExam = $this->db->fetch(
                    'SELECT id FROM exams WHERE course_id = ? AND exam_type = "final" AND status = "active" LIMIT 1',
                    [$finalCourseId]
                );
                if ($finalExam) {
                    $bestScore = $this->db->fetch(
                        'SELECT MAX(score) AS best FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND is_passed = 1',
                        [$userId, $finalExam['id']]
                    )['best'] ?? null;
                    $finalExamPassed = ($bestScore !== null && (float)$bestScore >= 60.0);
                }
            }
        }

        $allDone = $allModulesDone && $finalExamPassed;

        // Avoid downgrading an already-completed package (e.g. if called again)
        $currentPkg = $this->db->fetch('SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?', [$userId, $packageId]);
        if (($currentPkg['status'] ?? '') === 'completed') {
            return;
        }

        $this->db->query(
            'UPDATE enrollments SET progress_percent = ?, status = ?' .
            ($allDone ? ', completed_at = NOW()' : '') .
            ' WHERE user_id = ? AND course_id = ?',
            [$percent, $allDone ? 'completed' : 'in_progress', $userId, $packageId]
        );

        // Issue package-level certificate on first completion
        if ($allDone) {
            $hasCert = $this->db->exists('certificates', 'user_id = ? AND course_id = ?', [$userId, $packageId]);
            if (!$hasCert) {
                try {
                    $cert = new Certificate();
                    $cert->issue($userId, $packageId);
                } catch (\Throwable $e) {
                    error_log('PackageEnroller cert error: ' . $e->getMessage());
                }
            }
        }
    }
}
