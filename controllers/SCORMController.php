<?php
use ISG\Auth;
use ISG\SCORMManager;
use ISG\Audit;

$auth = new Auth();
$scorm = new SCORMManager();
$userId = $auth->id();
$audit = new Audit();

$action = ltrim(str_replace('/scorm', '', $uri), '/');
$action = explode('/', $action)[0] ?? '';

match($action) {
    'player' => (function() use ($auth, $db, $scorm, $userId, $uri) {
        $courseId = (int)($_GET['kurs'] ?? 0);
        if (!$courseId) redirect('/ogrenci');

        $enrollment = $db->fetch(
            'SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );
        if (!$enrollment && !$auth->isAdmin()) redirect('/ogrenci');

        $package = $db->fetch('SELECT * FROM scorm_packages WHERE course_id = ?', [$courseId]);
        if (!$package) redirect('/ogrenci', 'SCORM paketi bulunamadı.', 'error');

        $course = $db->fetch('SELECT * FROM courses WHERE id = ?', [$courseId]);

        // Enforce course date restrictions (students only)
        if (!$auth->isAdmin()) {
            $today = date('Y-m-d');
            if (!empty($course['start_date']) && $today < $course['start_date']) {
                redirect('/ogrenci/kurs/' . $courseId,
                    'Bu kurs henüz başlamadı. Başlangıç tarihi: ' . date('d.m.Y', strtotime($course['start_date'])), 'warning');
            }
            if (!empty($course['end_date']) && $today > $course['end_date']) {
                redirect('/ogrenci/kurs/' . $courseId,
                    'Bu kursun erişim süresi dolmuştur (son tarih: ' . date('d.m.Y', strtotime($course['end_date'])) . ').', 'warning');
            }
            // Check per-enrollment due_date
            if ($enrollment && !empty($enrollment['due_date']) && $today > $enrollment['due_date']) {
                redirect('/ogrenci/kurs/' . $courseId,
                    'Kurs son tarihiniz geçmiştir (' . date('d.m.Y', strtotime($enrollment['due_date'])) . ').', 'warning');
            }
        }

        $tracking = $scorm->getTrackingData($userId, $courseId);

        view('student/scorm_player', compact('course', 'package', 'tracking', 'userId', 'courseId'));
    })(),

    'commit' => (function() use ($auth, $db, $scorm, $userId, $audit) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'POST gerekli']);
            exit;
        }

        $courseId = (int)($_GET['kurs'] ?? $_POST['course_id'] ?? 0);
        if (!$courseId || !$userId) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek']);
            exit;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $package = $db->fetch('SELECT scorm_version FROM scorm_packages WHERE course_id = ?', [$courseId]);
        $scormVersion = $package['scorm_version'] ?? '1.2';

        if ($scormVersion === '1.2') {
            $mapped = [
                'lesson_status'  => $data['lesson_status'] ?? null,
                'score_raw'      => $data['score_raw'] ?? $data['core_score_raw'] ?? null,
                'score_min'      => $data['score_min'] ?? $data['core_score_min'] ?? null,
                'score_max'      => $data['score_max'] ?? $data['core_score_max'] ?? null,
                'total_time'     => $data['total_time'] ?? null,
                'session_time'   => $data['session_time'] ?? null,
                'suspend_data'   => $data['suspend_data'] ?? null,
                'location'       => $data['location'] ?? null,
                'entry'          => $data['entry'] ?? null,
            ];
        } else {
            $mapped = [
                'completion_status' => $data['completion_status'] ?? null,
                'success_status'    => $data['success_status'] ?? null,
                'score_raw'         => $data['score_raw'] ?? null,
                'score_scaled'      => $data['score_scaled'] ?? null,
                'total_time'        => $data['total_time'] ?? null,
                'session_time'      => $data['session_time'] ?? null,
                'suspend_data'      => $data['suspend_data'] ?? null,
                'location'          => $data['location'] ?? null,
            ];
        }
        $mapped = array_filter($mapped, fn($v) => $v !== null && $v !== '');

        // Compliance fields (always merge if present, even without lesson_status update)
        foreach (['tab_switch_count','fast_forward_count','low_quality_flag'] as $cf) {
            if (isset($data[$cf]) && $data[$cf] !== '') {
                $mapped[$cf] = (int)$data[$cf];
            }
        }

        $scorm->saveTrackingData($userId, $courseId, $mapped);

        $enrollment = $db->fetch(
            'SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );

        // Audit course completion + trigger sequential unlock for child modules
        $freshEnrollment = $db->fetch('SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?', [$userId, $courseId]);
        $justCompleted = ($freshEnrollment['status'] ?? '') === 'completed' && ($enrollment['status'] ?? '') !== 'completed';
        $courseRow = $db->fetch('SELECT parent_course_id FROM courses WHERE id = ?', [$courseId]);

        if ($justCompleted) {
            $audit->log($userId, 'course_complete', 'enrollment', null, ['course_id' => $courseId]);

            // Child module (e.g. on_degerlendirme, genel, saglik…) completed via SCORM → unlock next in sequence
            if (!empty($courseRow['parent_course_id'])) {
                (new \ISG\PackageEnroller($db))->onModuleCompleted($userId, $courseId);
            }
        }

        // Auto-issue certificate when standalone SCORM completes (no parent package, no exam required)
        // Child module completions are handled by PackageEnroller — no cert issued here
        if (($freshEnrollment['status'] ?? '') === 'completed') {
            $isChildModule = !empty($courseRow['parent_course_id']);
            if (!$isChildModule) {
                $hasExam = $db->exists('exams', 'course_id = ? AND exam_type = "final" AND status = "active"', [$courseId]);
                $hasCert = $db->exists('certificates', 'user_id = ? AND course_id = ?', [$userId, $courseId]);
                if (!$hasExam && !$hasCert) {
                    try {
                        $cert = new \ISG\Certificate();
                        $certData = $cert->issue($userId, $courseId);
                        if ($certData) {
                            $audit->log($userId, 'cert_issue', 'certificate', $certData['id'] ?? null, [
                                'cert_number' => $certData['cert_number'] ?? null,
                                'course_id'   => $courseId,
                                'via'         => 'scorm_complete',
                            ]);
                        }
                    } catch (\Throwable $e) {
                        error_log('Auto-cert error: ' . $e->getMessage());
                    }
                }
            }
        }

        echo json_encode([
            'status'      => 'ok',
            'progress'    => $freshEnrollment['progress_percent'] ?? 0,
            'enrollStatus' => $freshEnrollment['status'] ?? 'in_progress',
        ]);
        exit;
    })(),

    'data' => (function() use ($db, $scorm, $userId) {
        header('Content-Type: application/json');
        $courseId = (int)($_GET['kurs'] ?? 0);
        if (!$courseId || !$userId) {
            echo json_encode([]);
            exit;
        }
        $tracking = $scorm->getTrackingData($userId, $courseId);
        $package = $db->fetch('SELECT scorm_version FROM scorm_packages WHERE course_id = ?', [$courseId]);
        $tracking['scorm_version'] = $package['scorm_version'] ?? '1.2';
        echo json_encode($tracking);
        exit;
    })(),

    default => redirect('/ogrenci'),
};
