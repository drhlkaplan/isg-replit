<?php
use ISG\Auth;
use ISG\SCORMManager;
use ISG\Certificate;
use ISG\PackageEnroller;

$auth = new Auth();
$userId = $auth->id();

$action = ltrim(str_replace('/ogrenci', '', $uri), '/');
$action = explode('/', $action)[0] ?? '';

match($action) {
    '', 'dashboard' => (function() use ($db, $userId) {
        $enrollments = $db->fetchAll(
            'SELECT e.*, c.title, c.duration_minutes, c.thumbnail_path,
                    c.topic_type, c.training_type,
                    cc.name AS category_name, cc.color AS category_color, cc.refresh_period_years,
                    cert.cert_number, cert.expires_at AS cert_expires_at, cert.is_valid AS cert_is_valid
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             JOIN course_categories cc ON c.category_id = cc.id
             LEFT JOIN certificates cert ON cert.user_id = e.user_id AND cert.course_id = e.course_id
             WHERE e.user_id = ? AND (c.parent_course_id IS NULL OR c.parent_course_id = 0)
             ORDER BY e.enrolled_at DESC',
            [$userId]
        );

        $stats = [
            'total'       => count($enrollments),
            'completed'   => count(array_filter($enrollments, fn($e) => $e['status'] === 'completed')),
            'in_progress' => count(array_filter($enrollments, fn($e) => $e['status'] === 'in_progress')),
            'enrolled'    => count(array_filter($enrollments, fn($e) => $e['status'] === 'enrolled')),
            'certificates'=> $db->fetch('SELECT COUNT(*) AS cnt FROM certificates WHERE user_id = ? AND is_valid = 1', [$userId])['cnt'] ?? 0,
        ];

        $recentActivity = $db->fetchAll(
            'SELECT e.*, c.title, cc.color AS category_color, cc.name AS category_name
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             JOIN course_categories cc ON c.category_id = cc.id
             WHERE e.user_id = ? AND e.last_activity IS NOT NULL
               AND (c.parent_course_id IS NULL OR c.parent_course_id = 0)
             ORDER BY e.last_activity DESC
             LIMIT 5',
            [$userId]
        );

        $flash = flash();
        view('student/dashboard', compact('enrollments', 'stats', 'recentActivity', 'flash'));
    })(),

    'kurs' => (function() use ($db, $userId, $uri) {
        $courseId = (int)(explode('/', $uri)[3] ?? 0);
        if (!$courseId) redirect('/ogrenci');

        $enrollment = $db->fetch(
            'SELECT e.*, c.title, c.description, c.duration_minutes, c.topic_type, c.delivery_method,
                    cc.name AS category_name, cc.color AS category_color
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             JOIN course_categories cc ON c.category_id = cc.id
             WHERE e.user_id = ? AND e.course_id = ?',
            [$userId, $courseId]
        );
        if (!$enrollment) redirect('/ogrenci', 'Bu kursa kaydınız yok.', 'error');

        $package = $db->fetch('SELECT * FROM scorm_packages WHERE course_id = ?', [$courseId]);
        $exams = $db->fetchAll('SELECT * FROM exams WHERE course_id = ? AND status = "active" ORDER BY exam_type', [$courseId]);
        $certificate = $db->fetch('SELECT * FROM certificates WHERE user_id = ? AND course_id = ?', [$userId, $courseId]);

        // Safety net: if enrolled in a package but child module rows don't exist, create them now
        if ($enrollment['topic_type'] === 'paket') {
            $hasChildEnrollments = $db->fetch(
                'SELECT COUNT(*) AS cnt FROM enrollments e
                 JOIN courses c ON c.id = e.course_id
                 WHERE e.user_id = ? AND c.parent_course_id = ?',
                [$userId, $courseId]
            )['cnt'] ?? 0;
            if ((int)$hasChildEnrollments === 0) {
                (new PackageEnroller($db))->enrollPackage($userId, $courseId);
            }
        }

        // Load child modules if this is a package
        $modules = [];
        if ($enrollment['topic_type'] === 'paket') {
            $modules = $db->fetchAll(
                "SELECT c.id, c.title, c.topic_type, c.delivery_method, c.sort_order, c.duration_minutes,
                        e.status AS enroll_status, e.progress_percent, e.is_locked,
                        (SELECT COUNT(*) FROM scorm_packages sp WHERE sp.course_id = c.id) AS has_scorm,
                        ex.id AS exam_id, ex.exam_type AS exam_type,
                        fts.id AS session_id, fts.qr_token AS session_qr_token
                 FROM courses c
                 LEFT JOIN enrollments e ON e.course_id = c.id AND e.user_id = ?
                 LEFT JOIN exams ex ON ex.course_id = c.id AND ex.status = 'active'
                 LEFT JOIN face_to_face_sessions fts ON fts.course_id = c.id
                 WHERE c.parent_course_id = ? AND c.status = 'active'
                 ORDER BY c.sort_order ASC, c.id ASC",
                [$userId, $courseId]
            );
        }

        $flash = flash();
        view('student/course', compact('enrollment', 'package', 'exams', 'certificate', 'courseId', 'modules', 'flash'));
    })(),

    'katalog' => (function() {
        redirect('/ogrenci', 'Eğitimlere kayıt yalnızca yönetici veya eğitmen tarafından yapılabilir.', 'error');
    })(),

    'kayit' => (function() {
        redirect('/ogrenci', 'Eğitimlere kayıt yalnızca yönetici veya eğitmen tarafından yapılabilir.', 'error');
    })(),

    'sertifikalar' => (function() use ($db, $userId) {
        $certs = $db->fetchAll(
            'SELECT cert.*, c.title, cc.name AS category_name, cc.color AS category_color
             FROM certificates cert
             JOIN courses c ON cert.course_id = c.id
             JOIN course_categories cc ON c.category_id = cc.id
             WHERE cert.user_id = ?
               AND (c.parent_course_id IS NULL OR c.parent_course_id = 0)
             ORDER BY cert.issued_at DESC',
            [$userId]
        );
        $flash = flash();
        view('student/certificates', compact('certs', 'flash'));
    })(),

    'profil' => (function() use ($db, $userId, $method, $auth) {
        $user = $db->fetch('SELECT * FROM users WHERE id = ?', [$userId]);
        if ($method === 'POST') {
            csrf_check();
            $action = $_POST['action'] ?? 'profile';

            if ($action === 'profile') {
                $db->update('users', [
                    'first_name'    => trim($_POST['first_name']),
                    'last_name'     => trim($_POST['last_name']),
                    'phone'         => trim($_POST['phone'] ?? ''),
                    'tc_identity_no'=> trim($_POST['tc_identity_no'] ?? ''),
                ], 'id = ?', [$userId]);
                $_SESSION['user']['first_name'] = trim($_POST['first_name']);
                $_SESSION['user']['last_name']  = trim($_POST['last_name']);
                redirect('/ogrenci/profil', 'Profil bilgileriniz güncellendi.');

            } elseif ($action === 'password') {
                $current  = $_POST['current_password'] ?? '';
                $newPass  = $_POST['new_password'] ?? '';
                $confirm  = $_POST['confirm_password'] ?? '';
                if (!password_verify($current, $user['password_hash'])) {
                    redirect('/ogrenci/profil', 'Mevcut şifreniz hatalı.', 'error');
                }
                if (strlen($newPass) < 6) {
                    redirect('/ogrenci/profil', 'Yeni şifre en az 6 karakter olmalıdır.', 'error');
                }
                if ($newPass !== $confirm) {
                    redirect('/ogrenci/profil', 'Yeni şifreler eşleşmiyor.', 'error');
                }
                $db->update('users', ['password_hash' => password_hash($newPass, PASSWORD_BCRYPT)], 'id = ?', [$userId]);
                redirect('/ogrenci/profil', 'Şifreniz başarıyla değiştirildi.');

            } elseif ($action === 'group_key') {
                $redeemer = new \ISG\GroupKeyRedeemer($db);
                $result = $redeemer->redeem($_POST['group_key_code'] ?? '', $userId);
                if (!$result['success']) {
                    redirect('/ogrenci/profil', $result['message'], 'error');
                }
                redirect('/ogrenci/profil', $result['message']);

            } elseif ($action === 'firma_kodu') {
                $code = strtoupper(trim($_POST['firma_kodu'] ?? ''));
                if (!$code) {
                    redirect('/ogrenci/profil', 'Firma kodu boş olamaz.', 'error');
                }
                $firm = $db->fetch("SELECT id, name FROM firms WHERE company_code = ? AND status = 'active'", [$code]);
                if (!$firm) {
                    redirect('/ogrenci/profil', 'Geçersiz firma kodu. Lütfen sorumlu yöneticinizden doğru kodu alın.', 'error');
                }
                $db->update('users', ['firm_id' => $firm['id']], 'id = ?', [$userId]);
                $_SESSION['user']['firm_id'] = $firm['id'];
                redirect('/ogrenci/profil', 'Firma bağlantısı güncellendi: ' . $firm['name'] . '. Temanız değiştirildi.');
            }
        }

        $firmInfo = $db->fetch('SELECT id, name, primary_color, logo_path, company_code FROM firms WHERE id = ?', [$user['firm_id'] ?? 0]);

        $history = $db->fetchAll(
            'SELECT e.*, c.title, c.duration_minutes,
                    cc.name AS category_name, cc.color AS category_color,
                    cert.cert_number
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             JOIN course_categories cc ON c.category_id = cc.id
             LEFT JOIN certificates cert ON cert.user_id = e.user_id AND cert.course_id = e.course_id
             WHERE e.user_id = ?
               AND (c.parent_course_id IS NULL OR c.parent_course_id = 0)
             ORDER BY e.enrolled_at DESC',
            [$userId]
        );

        $flash = flash();
        view('student/profile', compact('user', 'history', 'firmInfo', 'flash'));
    })(),

    'yyz' => (function() use ($db, $userId) {
        $sessions = $db->fetchAll(
            "SELECT s.*, c.title AS course_title, cat.color AS cat_color,
                    f.name AS firm_name,
                    a.joined_at, a.completed, a.join_method,
                    (SELECT COUNT(*) FROM face_to_face_attendance x WHERE x.session_id = s.id) AS total_attendees
             FROM face_to_face_sessions s
             JOIN courses c ON s.course_id = c.id
             JOIN course_categories cat ON c.category_id = cat.id
             LEFT JOIN firms f ON s.firm_id = f.id
             LEFT JOIN face_to_face_attendance a ON a.session_id = s.id AND a.user_id = ?
             ORDER BY s.scheduled_at DESC",
            [$userId]
        );
        $flash = flash();
        view('student/yyz', compact('sessions', 'flash'));
    })(),

    default => redirect('/ogrenci'),
};
