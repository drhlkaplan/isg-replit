<?php
use ISG\Auth;
use ISG\Certificate;
use ISG\Audit;

$auth = new Auth();
$userId = $auth->id();
$audit = new Audit();

$segments = explode('/', ltrim($uri, '/'));
$action = $segments[1] ?? '';
$examId = (int)($segments[2] ?? 0);

match($action) {
    'baslat' => (function() use ($db, $userId, $examId, $method, $audit) {
        $exam = $db->fetch('SELECT e.*, c.title AS course_title, c.topic_type AS course_topic_type FROM exams e JOIN courses c ON e.course_id = c.id WHERE e.id = ? AND e.status = "active"', [$examId]);
        if (!$exam) redirect('/ogrenci', 'Sınav bulunamadı.', 'error');

        // Final exams: hard cap at 3 total attempts (Turkish ISG regulation)
        // min() ensures configured max_attempts cannot exceed 3, not just at-least-3
        $effectiveMax = ($exam['exam_type'] === 'final') ? min((int)$exam['max_attempts'] ?: 3, 3) : (int)$exam['max_attempts'];
        $attemptCount = $db->count('exam_attempts', 'user_id = ? AND exam_id = ?', [$userId, $examId]);
        if ($attemptCount >= $effectiveMax) {
            redirect('/ogrenci/kurs/' . $exam['course_id'], 'Maksimum deneme hakkı kullanıldı.', 'error');
        }

        $allQuestions = $db->fetchAll('SELECT * FROM questions WHERE exam_id = ?', [$examId]);
        if ($exam['shuffle_questions']) shuffle($allQuestions);
        $questions = array_slice($allQuestions, 0, $exam['question_count']);

        foreach ($questions as &$q) {
            $opts = $db->fetchAll('SELECT * FROM question_options WHERE question_id = ?', [$q['id']]);
            if ($exam['shuffle_answers']) shuffle($opts);
            $q['options'] = $opts;
        }

        $_SESSION['active_exam'] = [
            'exam_id'     => $examId,
            'questions'   => array_column($questions, 'id'),
            'started_at'  => time(),
            'course_id'   => $exam['course_id'],
        ];

        $flash = flash();
        view('student/exam', compact('exam', 'questions', 'flash'));
    })(),

    'gonder' => (function() use ($db, $userId, $examId, $method, $audit) {
        if ($method !== 'POST') redirect('/ogrenci');
        csrf_check();

        $examSession = $_SESSION['active_exam'] ?? null;
        if (!$examSession || $examSession['exam_id'] != $examId) {
            redirect('/ogrenci', 'Geçersiz sınav oturumu.', 'error');
        }

        $exam = $db->fetch('SELECT e.*, c.topic_type AS course_topic_type FROM exams e JOIN courses c ON e.course_id = c.id WHERE e.id = ?', [$examId]);
        if (!$exam) redirect('/ogrenci', 'Sınav bulunamadı.', 'error');

        // Ön değerlendirme sınavları için pass_score = 0 (her zaman geçer)
        $isPreExam = ($exam['exam_type'] === 'pre' || $exam['course_topic_type'] === 'on_degerlendirme');
        $effectivePassScore = $isPreExam ? 0 : max((float)$exam['pass_score'], 60.0);

        $answers = $_POST['answers'] ?? [];
        $questionIds = $examSession['questions'];
        $totalPoints = 0;
        $earnedPoints = 0;
        $answerLog = [];

        foreach ($questionIds as $qId) {
            $question = $db->fetch('SELECT * FROM questions WHERE id = ?', [$qId]);
            if (!$question) continue;
            $options = $db->fetchAll('SELECT * FROM question_options WHERE question_id = ?', [$qId]);
            $correctOpt = null;
            foreach ($options as $opt) {
                if ($opt['is_correct']) { $correctOpt = $opt['id']; break; }
            }
            $selected = (int)($answers[$qId] ?? 0);
            $isCorrect = $selected && $selected === $correctOpt;
            $totalPoints += $question['points'];
            if ($isCorrect) $earnedPoints += $question['points'];
            $answerLog[$qId] = ['selected' => $selected, 'correct' => $correctOpt, 'is_correct' => $isCorrect];
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $isPassed = $isPreExam ? true : ($score >= $effectivePassScore);

        $newAttemptNumber = $db->count('exam_attempts', 'user_id = ? AND exam_id = ?', [$userId, $examId]) + 1;
        $attemptId = $db->insert('exam_attempts', [
            'user_id'        => $userId,
            'exam_id'        => $examId,
            'submitted_at'   => date('Y-m-d H:i:s'),
            'score'          => $score,
            'is_passed'      => $isPassed ? 1 : 0,
            'answers'        => json_encode($answerLog),
            'attempt_number' => $newAttemptNumber,
        ]);

        $audit->log($userId, $isPassed ? 'exam_pass' : 'exam_fail', 'exam', $examId, [
            'course_id'  => $exam['course_id'],
            'score'      => $score,
            'pass_score' => $effectivePassScore,
            'attempt'    => $newAttemptNumber,
        ]);

        $allAttemptsExhausted = false;
        $enrollmentFailed = false;

        if (!$isPassed && $exam['exam_type'] === 'final') {
            $effectiveMax = min((int)$exam['max_attempts'] ?: 3, 3);
            $totalAttempts = $db->count('exam_attempts', 'user_id = ? AND exam_id = ?', [$userId, $examId]);
            if ($totalAttempts >= $effectiveMax) {
                // Tüm denemeler başarısız → kayıt durumunu 'failed' yap
                $db->update('enrollments',
                    ['status' => 'failed'],
                    'user_id = ? AND course_id = ?',
                    [$userId, $exam['course_id']]
                );
                $audit->log($userId, 'enrollment_failed_max_attempts', 'enrollment', null, [
                    'exam_id'   => $examId,
                    'course_id' => $exam['course_id'],
                    'attempts'  => $totalAttempts,
                ]);
                $allAttemptsExhausted = true;
                $enrollmentFailed = true;
            }
        }

        // On değerlendirme (pre) exam completion: always passes, marks module done, unlocks next (genel)
        if ($isPreExam) {
            $preExamCourse = $db->fetch('SELECT id, parent_course_id FROM courses WHERE id = ?', [$exam['course_id']]);
            if (!empty($preExamCourse['parent_course_id'])) {
                $prevEnroll = $db->fetch('SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?', [$userId, $exam['course_id']]);
                if ($prevEnroll && $prevEnroll['status'] !== 'completed') {
                    $db->update('enrollments',
                        ['status' => 'completed', 'progress_percent' => 100, 'completed_at' => date('Y-m-d H:i:s'), 'last_activity' => date('Y-m-d H:i:s')],
                        'user_id = ? AND course_id = ?',
                        [$userId, $exam['course_id']]
                    );
                    $audit->log($userId, 'course_complete', 'enrollment', null, ['course_id' => $exam['course_id'], 'via' => 'pre_exam_done']);
                    (new \ISG\PackageEnroller($db))->onModuleCompleted($userId, $exam['course_id']);
                }
            }
        }

        if ($isPassed && $exam['exam_type'] === 'final') {
            $examCourse = $db->fetch('SELECT id, parent_course_id FROM courses WHERE id = ?', [$exam['course_id']]);
            $isChildModule = !empty($examCourse['parent_course_id']);

            if ($isChildModule) {
                // Child module (e.g. final_sinav): mark enrollment completed, then let PackageEnroller handle package progress
                $currentEnrollment = $db->fetch('SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?', [$userId, $exam['course_id']]);
                if ($currentEnrollment && $currentEnrollment['status'] !== 'completed') {
                    $db->update('enrollments',
                        ['status' => 'completed', 'progress_percent' => 100, 'completed_at' => date('Y-m-d H:i:s'), 'last_activity' => date('Y-m-d H:i:s')],
                        'user_id = ? AND course_id = ?',
                        [$userId, $exam['course_id']]
                    );
                    $audit->log($userId, 'course_complete', 'enrollment', null, ['course_id' => $exam['course_id'], 'via' => 'exam_pass']);
                }
                (new \ISG\PackageEnroller($db))->onModuleCompleted($userId, $exam['course_id']);
            } else {
                // Standalone course: issue cert if SCORM also done (or exam-only)
                $enrollment = $db->fetch('SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?', [$userId, $exam['course_id']]);
                // For exam-only courses (no SCORM package), mark enrollment completed now
                $hasScorm = $db->exists('scorm_packages', 'course_id = ?', [$exam['course_id']]);
                if (!$hasScorm && $enrollment && $enrollment['status'] !== 'completed') {
                    $db->update('enrollments',
                        ['status' => 'completed', 'progress_percent' => 100, 'completed_at' => date('Y-m-d H:i:s'), 'last_activity' => date('Y-m-d H:i:s')],
                        'user_id = ? AND course_id = ?',
                        [$userId, $exam['course_id']]
                    );
                    $enrollment = ['status' => 'completed'];
                }
                if ($enrollment && $enrollment['status'] === 'completed') {
                    $hasCert = $db->exists('certificates', 'user_id = ? AND course_id = ?', [$userId, $exam['course_id']]);
                    if (!$hasCert) {
                        $cert = new Certificate();
                        $certData = $cert->issue($userId, $exam['course_id']);
                        if ($certData) {
                            $audit->log($userId, 'cert_issue', 'certificate', $certData['id'] ?? null, [
                                'cert_number' => $certData['cert_number'] ?? null,
                                'course_id'   => $exam['course_id'],
                                'via'         => 'exam_pass',
                            ]);
                        }
                    }
                }
            }
        }

        unset($_SESSION['active_exam']);
        $flash = flash();
        view('student/exam_result', compact('exam', 'score', 'isPassed', 'answerLog', 'allAttemptsExhausted', 'enrollmentFailed', 'flash'));
    })(),

    default => redirect('/ogrenci'),
};
