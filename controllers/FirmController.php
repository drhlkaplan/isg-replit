<?php
use ISG\Auth;

$auth = new Auth();
$userId = $auth->id();
$firmUser = $auth->user();

$segments = explode('/', ltrim($uri, '/'));
$action = $segments[1] ?? '';

$firmId = $firmUser['firm_id'] ?? 0;
if (!$firmId) redirect('/giris', 'Firma bilgisi bulunamadı.', 'error');
$firm = $db->fetch('SELECT * FROM firms WHERE id = ?', [$firmId]);

match($action) {
    '', 'dashboard' => (function() use ($db, $firmId, $firm) {
        $employees = $db->fetchAll(
            'SELECT u.id, u.first_name, u.last_name, u.email, u.tc_identity_no, u.status,
                    COUNT(DISTINCT e.course_id) AS enrolled_count,
                    SUM(e.status = "completed") AS completed_count,
                    SUM(e.status = "in_progress") AS inprogress_count,
                    SUM(e.status = "failed") AS failed_count,
                    MAX(e.last_activity) AS last_activity
             FROM users u
             LEFT JOIN enrollments e ON e.user_id = u.id
             WHERE u.firm_id = ? AND u.role_id = 4
             GROUP BY u.id
             ORDER BY u.last_name, u.first_name',
            [$firmId]
        );

        $courseStats = $db->fetchAll(
            'SELECT c.id, c.title, cc.name AS category_name, cc.color AS category_color,
                    COUNT(e.id) AS enrolled,
                    SUM(e.status = "completed") AS completed,
                    SUM(e.status = "in_progress") AS in_progress,
                    AVG(e.progress_percent) AS avg_progress
             FROM enrollments e
             JOIN users u ON e.user_id = u.id
             JOIN courses c ON e.course_id = c.id
             JOIN course_categories cc ON c.category_id = cc.id
             WHERE u.firm_id = ?
             GROUP BY c.id
             ORDER BY c.title',
            [$firmId]
        );

        $expiringCerts = $db->fetchAll(
            "SELECT u.first_name, u.last_name, c.title AS course_title, cert.expires_at,
                    DATEDIFF(cert.expires_at, NOW()) AS days_left
             FROM certificates cert
             JOIN users u ON cert.user_id = u.id
             JOIN courses c ON cert.course_id = c.id
             WHERE u.firm_id = ? AND cert.is_valid = 1
               AND cert.expires_at IS NOT NULL
               AND cert.expires_at <= DATE_ADD(NOW(), INTERVAL 60 DAY)
             ORDER BY cert.expires_at ASC
             LIMIT 10",
            [$firmId]
        );

        $recentEnrollments = $db->fetchAll(
            'SELECT u.first_name, u.last_name, c.title AS course_title, e.enrolled_at, e.status
             FROM enrollments e
             JOIN users u ON e.user_id = u.id
             JOIN courses c ON e.course_id = c.id
             WHERE u.firm_id = ?
             ORDER BY e.enrolled_at DESC
             LIMIT 8',
            [$firmId]
        );

        $stats = [
            'employees'   => count($employees),
            'enrolled'    => array_sum(array_column($employees, 'enrolled_count')),
            'completed'   => array_sum(array_column($employees, 'completed_count')),
            'in_progress' => array_sum(array_column($employees, 'inprogress_count')),
            'certs'       => (int)($db->fetch('SELECT COUNT(*) AS cnt FROM certificates c JOIN users u ON c.user_id = u.id WHERE u.firm_id = ?', [$firmId])['cnt'] ?? 0),
            'active_employees' => count(array_filter($employees, fn($e) => $e['last_activity'] && strtotime($e['last_activity']) > strtotime('-7 days'))),
        ];

        $flash = flash();
        view('firma/dashboard', compact('firm', 'employees', 'courseStats', 'stats', 'expiringCerts', 'recentEnrollments', 'flash'));
    })(),

    'calisanlar' => (function() use ($db, $firmId, $firm) {
        $search = trim($_GET['ara'] ?? '');
        $statusFilter = $_GET['durum'] ?? '';

        $sql = 'SELECT u.id, u.first_name, u.last_name, u.email, u.tc_identity_no, u.phone, u.status,
                       COUNT(DISTINCT e.course_id) AS enrolled_count,
                       SUM(e.status = "completed") AS completed_count,
                       SUM(e.status = "in_progress") AS inprogress_count,
                       SUM(e.status = "failed") AS failed_count,
                       (SELECT COUNT(*) FROM certificates cert WHERE cert.user_id = u.id AND cert.is_valid = 1) AS cert_count
                FROM users u
                LEFT JOIN enrollments e ON e.user_id = u.id
                WHERE u.firm_id = ? AND u.role_id = 4';
        $params = [$firmId];
        if ($search) {
            $sql .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.tc_identity_no LIKE ?)';
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
        }
        if ($statusFilter) { $sql .= ' AND u.status = ?'; $params[] = $statusFilter; }
        $sql .= ' GROUP BY u.id ORDER BY u.last_name, u.first_name';
        $employees = $db->fetchAll($sql, $params);

        $flash = flash();
        view('firma/calisanlar', compact('firm', 'employees', 'search', 'statusFilter', 'flash'));
    })(),

    'raporlar' => (function() use ($db, $firmId, $firm) {
        $courseStats = $db->fetchAll(
            'SELECT c.id, c.title, cc.name AS category_name, cc.color AS category_color,
                    COUNT(DISTINCT e.user_id) AS enrolled,
                    SUM(e.status = "completed") AS completed,
                    SUM(e.status = "in_progress") AS in_progress,
                    SUM(e.status = "failed") AS failed,
                    AVG(e.progress_percent) AS avg_progress
             FROM enrollments e
             JOIN users u ON e.user_id = u.id
             JOIN courses c ON e.course_id = c.id
             JOIN course_categories cc ON c.category_id = cc.id
             WHERE u.firm_id = ?
             GROUP BY c.id
             ORDER BY enrolled DESC',
            [$firmId]
        );

        $expiredCerts = $db->fetchAll(
            "SELECT u.first_name, u.last_name, u.email, c.title AS course_title,
                    cert.expires_at, cert.cert_number,
                    DATEDIFF(NOW(), cert.expires_at) AS days_expired
             FROM certificates cert
             JOIN users u ON cert.user_id = u.id
             JOIN courses c ON cert.course_id = c.id
             WHERE u.firm_id = ? AND cert.is_valid = 0
               AND cert.expires_at IS NOT NULL
             ORDER BY cert.expires_at DESC
             LIMIT 20",
            [$firmId]
        );

        $flash = flash();
        view('firma/raporlar', compact('firm', 'courseStats', 'expiredCerts', 'flash'));
    })(),

    'profil' => (function() use ($db, $firmId, $firm, $method, $firmUser) {
        if (($firmUser['role'] ?? '') !== 'firm') {
            redirect('/firma', 'Bu sayfaya erişim yetkiniz yok.', 'error');
        }
        if ($method === 'POST') {
            csrf_check();
            $logoPath = $firm['logo_path'];
            if (!empty($_FILES['logo_file']['tmp_name'])) {
                if (!is_dir(LOGO_DIR)) mkdir(LOGO_DIR, 0755, true);
                $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png','jpg','jpeg','gif','svg']) && $_FILES['logo_file']['size'] <= 2097152) {
                    if ($logoPath && file_exists(LOGO_DIR . $logoPath)) {
                        @unlink(LOGO_DIR . $logoPath);
                    }
                    $logoPath = 'logo_' . $firmId . '_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['logo_file']['tmp_name'], LOGO_DIR . $logoPath);
                }
            }
            $db->update('firms', [
                'contact_name'      => trim($_POST['contact_name'] ?? ''),
                'contact_email'     => trim($_POST['contact_email'] ?? ''),
                'contact_phone'     => trim($_POST['contact_phone'] ?? ''),
                'address'           => trim($_POST['address'] ?? ''),
                'primary_color'     => trim($_POST['primary_color'] ?? '#005695'),
                'secondary_color'   => trim($_POST['secondary_color'] ?? '#0072b5'),
                'header_title'      => trim($_POST['header_title'] ?? ''),
                'footer_text'       => trim($_POST['footer_text'] ?? ''),
                'announcement'      => trim($_POST['announcement'] ?? ''),
                'announcement_type' => in_array($_POST['announcement_type'] ?? '', ['info','warning','danger','success']) ? $_POST['announcement_type'] : 'info',
                'logo_path'         => $logoPath,
            ], 'id = ?', [$firmId]);
            redirect('/firma/profil', 'Firma profili güncellendi.');
        }
        $firm = $db->fetch('SELECT * FROM firms WHERE id = ?', [$firmId]);
        $flash = flash();
        view('firma/profil', compact('firm', 'flash'));
    })(),

    default => redirect('/firma'),
};
