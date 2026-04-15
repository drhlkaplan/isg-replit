<?php
use ISG\Auth;
use ISG\SCORMManager;
use ISG\Certificate;
use ISG\ReportExporter;
use ISG\Audit;

$auth = new Auth();
$userId = $auth->id();
$audit = new Audit();

$segments = explode('/', ltrim($uri, '/'));
$action = $segments[1] ?? '';
$subaction = $segments[2] ?? '';
$entityId = (int)($segments[3] ?? 0);

match($action) {
    '', 'dashboard' => (function() use ($db) {
        $stats = [
            'users'          => $db->count('users', 'role_id = 4'),
            'courses'        => $db->count('courses', "status = 'active' AND topic_type = 'paket'"),
            'enrollments'    => $db->count('enrollments'),
            'completed'      => $db->count('enrollments', 'status = "completed"'),
            'in_progress'    => $db->count('enrollments', 'status = "in_progress"'),
            'certificates'   => $db->count('certificates', 'is_valid = 1'),
            'firms'          => $db->count('firms', "status = 'active'"),
            'active_now'     => (int)($db->fetch(
                'SELECT COUNT(*) AS cnt FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
            )['cnt'] ?? 0),
            'certs_this_month' => (int)($db->fetch(
                "SELECT COUNT(*) AS cnt FROM certificates WHERE issued_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
            )['cnt'] ?? 0),
            'expired_certs'  => (int)($db->fetch(
                'SELECT COUNT(*) AS cnt FROM certificates WHERE is_valid = 0 AND expires_at IS NOT NULL'
            )['cnt'] ?? 0),
        ];
        $recentUsers = $db->fetchAll(
            'SELECT u.first_name, u.last_name, u.email, u.created_at, r.name AS role_name, f.name AS firm_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN firms f ON u.firm_id = f.id
             ORDER BY u.created_at DESC LIMIT 8'
        );
        $firmStats = $db->fetchAll(
            'SELECT f.id, f.name, f.status, f.primary_color,
                    COUNT(DISTINCT u.id) AS employee_count,
                    COUNT(DISTINCT e.id) AS enrollment_count,
                    SUM(e.status = "completed") AS completed_count
             FROM firms f
             LEFT JOIN users u ON u.firm_id = f.id AND u.role_id = 4
             LEFT JOIN enrollments e ON e.user_id = u.id
             GROUP BY f.id
             ORDER BY employee_count DESC
             LIMIT 8'
        );
        $recentEnrollments = $db->fetchAll(
            'SELECT u.first_name, u.last_name, c.title AS course_title, e.enrolled_at, e.status,
                    f.name AS firm_name
             FROM enrollments e
             JOIN users u ON e.user_id = u.id
             JOIN courses c ON e.course_id = c.id
             LEFT JOIN firms f ON u.firm_id = f.id
             ORDER BY e.enrolled_at DESC
             LIMIT 6'
        );
        $flash = flash();
        view('admin/dashboard', compact('stats', 'recentUsers', 'firmStats', 'recentEnrollments', 'flash'));
    })(),

    'kurslar' => (function() use ($db, $subaction, $entityId, $method, $audit, $userId) {
        match($subaction) {
            'ekle' => (function() use ($db, $method, $audit, $userId) {
                if ($method === 'POST') {
                    csrf_check();

                    $uploadErr = $_FILES['scorm_package']['error'] ?? UPLOAD_ERR_NO_FILE;
                    if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
                        $categories    = $db->fetchAll('SELECT * FROM course_categories');
                        $parentPackages = $db->fetchAll("SELECT id, title FROM courses WHERE topic_type='paket' ORDER BY training_type, title");
                        $flash = ['msg' => 'SCORM dosyası çok büyük. Maksimum 256 MB yüklenebilir.', 'type' => 'error'];
                        view('admin/course_form', compact('categories', 'parentPackages', 'flash'));
                        return;
                    }

                    $courseTitle = trim($_POST['title']);
                    $parentId = ((int)($_POST['parent_course_id'] ?? 0)) ?: null;
                    $courseId = $db->insert('courses', [
                        'category_id'        => (int)$_POST['category_id'],
                        'parent_course_id'   => $parentId,
                        'title'              => $courseTitle,
                        'description'        => trim($_POST['description'] ?? ''),
                        'training_type'      => $_POST['training_type'] ?: null,
                        'topic_type'         => $_POST['topic_type'] ?: null,
                        'delivery_method'    => $_POST['delivery_method'] ?? 'online',
                        'workplace_variant'  => $_POST['workplace_variant'] ?? 'genel',
                        'sort_order'         => (int)($_POST['sort_order'] ?? 0),
                        'duration_minutes'   => (int)($_POST['duration_minutes'] ?? 60),
                        'completion_required'=> isset($_POST['completion_required']) ? 1 : 0,
                        'status'             => $_POST['status'] ?? 'draft',
                        'start_date'         => $_POST['start_date'] ?: null,
                        'end_date'           => $_POST['end_date'] ?: null,
                    ]);
                    $audit->log($userId, 'course_create', 'course', $courseId, ['title' => $courseTitle]);

                    if (!empty($_FILES['scorm_package']['tmp_name']) && $uploadErr === UPLOAD_ERR_OK) {
                        try {
                            $scorm = new SCORMManager();
                            $tmpPath = $_FILES['scorm_package']['tmp_name'];
                            $manifest = $scorm->extractPackage($tmpPath, $courseId);
                            $db->insert('scorm_packages', $manifest);
                            $db->update('courses', ['status' => 'active'], 'id = ?', [$courseId]);
                            redirect('/admin/kurslar', 'Kurs ve SCORM paketi başarıyla eklendi.');
                        } catch (\Exception $e) {
                            redirect('/admin/kurslar', 'Kurs eklendi fakat SCORM hatası: ' . $e->getMessage(), 'warning');
                        }
                    } else {
                        redirect('/admin/kurslar', 'Kurs eklendi. SCORM paketi daha sonra ekleyebilirsiniz.');
                    }
                }
                $categories = $db->fetchAll('SELECT * FROM course_categories');
                $parentPackages = $db->fetchAll("SELECT id, title FROM courses WHERE topic_type='paket' ORDER BY training_type, title");
                $flash = flash();
                view('admin/course_form', compact('categories', 'parentPackages', 'flash'));
            })(),

            'duzenle' => (function() use ($db, $entityId, $method, $audit, $userId) {
                $course = $db->fetch('SELECT * FROM courses WHERE id = ?', [$entityId]);
                if (!$course) redirect('/admin/kurslar', 'Kurs bulunamadı.', 'error');
                if ($method === 'POST') {
                    csrf_check();

                    $uploadErr = $_FILES['scorm_package']['error'] ?? UPLOAD_ERR_NO_FILE;
                    if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
                        $categories     = $db->fetchAll('SELECT * FROM course_categories');
                        $parentPackages = $db->fetchAll("SELECT id, title FROM courses WHERE topic_type='paket' ORDER BY training_type, title");
                        $package = $db->fetch('SELECT * FROM scorm_packages WHERE course_id = ?', [$entityId]);
                        $flash = ['msg' => 'SCORM dosyası çok büyük. Maksimum 256 MB yüklenebilir.', 'type' => 'error'];
                        view('admin/course_form', compact('course', 'categories', 'parentPackages', 'package', 'flash'));
                        return;
                    }

                    $parentIdUpd = ((int)($_POST['parent_course_id'] ?? 0)) ?: null;
                    $db->update('courses', [
                        'category_id'        => (int)$_POST['category_id'],
                        'parent_course_id'   => $parentIdUpd,
                        'title'              => trim($_POST['title']),
                        'description'        => trim($_POST['description'] ?? ''),
                        'training_type'      => $_POST['training_type'] ?: null,
                        'topic_type'         => $_POST['topic_type'] ?: null,
                        'delivery_method'    => $_POST['delivery_method'] ?? 'online',
                        'workplace_variant'  => $_POST['workplace_variant'] ?? 'genel',
                        'sort_order'         => (int)($_POST['sort_order'] ?? 0),
                        'duration_minutes'   => (int)($_POST['duration_minutes'] ?? 60),
                        'completion_required'=> isset($_POST['completion_required']) ? 1 : 0,
                        'status'             => $_POST['status'] ?? 'draft',
                        'start_date'         => $_POST['start_date'] ?: null,
                        'end_date'           => $_POST['end_date'] ?: null,
                    ], 'id = ?', [$entityId]);
                    $audit->log($userId, 'course_update', 'course', $entityId, [
                        'old' => ['title' => $course['title'], 'status' => $course['status']],
                        'new' => ['title' => trim($_POST['title']), 'status' => $_POST['status'] ?? 'draft'],
                    ]);

                    // Handle YYZ session assignment for yuz_yuze courses
                    $assignSessionId = (int)($_POST['assign_session_id'] ?? 0);
                    if ($assignSessionId && ($_POST['delivery_method'] ?? '') === 'yuz_yuze') {
                        $db->query(
                            'UPDATE face_to_face_sessions SET course_id = ? WHERE id = ?',
                            [$entityId, $assignSessionId]
                        );
                    }

                    if (!empty($_FILES['scorm_package']['tmp_name']) && $uploadErr === UPLOAD_ERR_OK) {
                        try {
                            $scorm = new SCORMManager();
                            $targetDir = SCORM_DIR . 'course_' . $entityId . '/';
                            if (is_dir($targetDir)) {
                                $iter = new \RecursiveIteratorIterator(
                                    new \RecursiveDirectoryIterator($targetDir, \FilesystemIterator::SKIP_DOTS),
                                    \RecursiveIteratorIterator::CHILD_FIRST
                                );
                                foreach ($iter as $f) {
                                    $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
                                }
                            }
                            $manifest = $scorm->extractPackage($_FILES['scorm_package']['tmp_name'], $entityId);
                            $db->delete('scorm_packages', 'course_id = ?', [$entityId]);
                            $db->insert('scorm_packages', $manifest);
                            $db->update('courses', ['status' => 'active'], 'id = ?', [$entityId]);
                            redirect('/admin/kurslar', 'Kurs ve SCORM paketi güncellendi.');
                        } catch (\Exception $e) {
                            redirect('/admin/kurslar', 'SCORM yükleme hatası: ' . $e->getMessage(), 'danger');
                        }
                    } else {
                        redirect('/admin/kurslar', 'Kurs güncellendi.');
                    }
                }
                $categories = $db->fetchAll('SELECT * FROM course_categories');
                $parentPackages = $db->fetchAll("SELECT id, title FROM courses WHERE topic_type='paket' ORDER BY training_type, title");
                $package = $db->fetch('SELECT * FROM scorm_packages WHERE course_id = ?', [$entityId]);
                // Load YYZ data for the course form
                $linkedSessions = $db->fetchAll(
                    "SELECT s.*, u.first_name AS tr_first, u.last_name AS tr_last, f.name AS firm_name
                     FROM face_to_face_sessions s
                     LEFT JOIN users u ON s.trainer_id = u.id
                     LEFT JOIN firms f ON s.firm_id = f.id
                     WHERE s.course_id = ?
                     ORDER BY s.scheduled_at DESC",
                    [$entityId]
                );
                $allSessions = $db->fetchAll(
                    "SELECT s.id, s.title, s.scheduled_at, s.status, s.course_id,
                            c.title AS course_title,
                            f.name AS firm_name
                     FROM face_to_face_sessions s
                     JOIN courses c ON s.course_id = c.id
                     LEFT JOIN firms f ON s.firm_id = f.id
                     ORDER BY s.scheduled_at DESC
                     LIMIT 100"
                );
                $flash = flash();
                view('admin/course_form', compact('course', 'categories', 'parentPackages', 'package', 'linkedSessions', 'allSessions', 'flash'));
            })(),

            'sil' => (function() use ($db, $entityId, $audit, $userId) {
                $course = $db->fetch('SELECT * FROM courses WHERE id = ?', [$entityId]);
                if ($course) {
                    $scormDir = SCORM_DIR . 'course_' . $entityId . '/';
                    if (is_dir($scormDir)) {
                        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($scormDir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
                        foreach ($iter as $file) { $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname()); }
                        rmdir($scormDir);
                    }
                    $audit->log($userId, 'course_delete', 'course', $entityId, ['title' => $course['title']]);
                    $db->delete('courses', 'id = ?', [$entityId]);
                }
                redirect('/admin/kurslar', 'Kurs silindi.');
            })(),

            'kayitlar' => (function() use ($db, $entityId, $method, $audit, $userId) {
                $course = $db->fetch('SELECT c.*, cc.name AS category_name FROM courses c JOIN course_categories cc ON c.category_id = cc.id WHERE c.id = ?', [$entityId]);
                if (!$course) redirect('/admin/kurslar', 'Kurs bulunamadı.', 'error');
                if ($method === 'POST') {
                    csrf_check();
                    $enrollId = (int)($_POST['enrollment_id'] ?? 0);
                    $dueDate  = $_POST['due_date'] ?: null;
                    if ($enrollId) {
                        $db->update('enrollments', ['due_date' => $dueDate], 'id = ?', [$enrollId]);
                        $audit->log($userId, 'enrollment_due_date_set', 'enrollment', $enrollId, [
                            'course_id' => $entityId,
                            'due_date'  => $dueDate,
                        ]);
                    }
                    redirect('/admin/kurslar/kayitlar/' . $entityId, 'Son tarih güncellendi.');
                }
                $enrollments = $db->fetchAll(
                    'SELECT e.*, u.first_name, u.last_name, u.email, u.tc_identity_no,
                            f.name AS firm_name, cert.cert_number
                     FROM enrollments e
                     JOIN users u ON e.user_id = u.id
                     LEFT JOIN firms f ON u.firm_id = f.id
                     LEFT JOIN certificates cert ON cert.user_id = e.user_id AND cert.course_id = e.course_id
                     WHERE e.course_id = ?
                     ORDER BY e.enrolled_at DESC',
                    [$entityId]
                );
                $flash = flash();
                view('admin/course_enrollments', compact('course', 'enrollments', 'flash'));
            })(),

            default => (function() use ($db) {
                $courses = $db->fetchAll(
                    'SELECT c.*, cc.name AS category_name, cc.color AS category_color, cc.code AS category_code,
                            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS student_count,
                            (SELECT 1 FROM scorm_packages sp WHERE sp.course_id = c.id) AS has_scorm
                     FROM courses c JOIN course_categories cc ON c.category_id = cc.id
                     ORDER BY c.training_type ASC, cc.id ASC, c.sort_order ASC, c.topic_type ASC, c.id ASC'
                );
                $flash = flash();
                view('admin/courses', compact('courses', 'flash'));
            })(),
        };
    })(),

    'kullanicilar' => (function() use ($db, $subaction, $entityId, $method, $audit, $userId) {
        match($subaction) {
            'ekle' => (function() use ($db, $method, $audit, $userId) {
                if ($method === 'POST') {
                    csrf_check();
                    $auth2 = new Auth();
                    $newUserId = $auth2->register([
                        'first_name'    => trim($_POST['first_name']),
                        'last_name'     => trim($_POST['last_name']),
                        'email'         => trim($_POST['email']),
                        'phone'         => trim($_POST['phone'] ?? ''),
                        'tc_identity_no'=> trim($_POST['tc_identity_no'] ?? ''),
                        'password'      => $_POST['password'],
                        'role_id'       => (int)($_POST['role_id'] ?? 4),
                        'firm_id'       => (int)($_POST['firm_id'] ?? 1),
                    ]);
                    $audit->log($userId, 'user_create', 'user', $newUserId, ['email' => trim($_POST['email'])]);
                    redirect('/admin/kullanicilar', 'Kullanıcı eklendi.');
                }
                $roles = $db->fetchAll('SELECT * FROM roles');
                $firms = $db->fetchAll('SELECT * FROM firms');
                $flash = flash();
                view('admin/user_form', compact('roles', 'firms', 'flash'));
            })(),

            'duzenle' => (function() use ($db, $entityId, $method, $audit, $userId) {
                $user = $db->fetch('SELECT * FROM users WHERE id = ?', [$entityId]);
                if (!$user) redirect('/admin/kullanicilar', 'Kullanıcı bulunamadı.', 'error');
                if ($method === 'POST') {
                    csrf_check();
                    $update = [
                        'first_name'     => trim($_POST['first_name']),
                        'last_name'      => trim($_POST['last_name']),
                        'email'          => trim($_POST['email']),
                        'phone'          => trim($_POST['phone'] ?? ''),
                        'tc_identity_no' => trim($_POST['tc_identity_no'] ?? ''),
                        'role_id'        => (int)$_POST['role_id'],
                        'firm_id'        => (int)($_POST['firm_id'] ?? 1),
                        'status'         => $_POST['status'] ?? 'active',
                    ];
                    if (!empty($_POST['password'])) {
                        $update['password_hash'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    }
                    $db->update('users', $update, 'id = ?', [$entityId]);
                    $audit->log($userId, 'user_update', 'user', $entityId, [
                        'old' => ['email' => $user['email'], 'status' => $user['status'], 'role_id' => $user['role_id']],
                        'new' => ['email' => trim($_POST['email']), 'status' => $_POST['status'] ?? 'active', 'role_id' => (int)$_POST['role_id']],
                    ]);
                    redirect('/admin/kullanicilar', 'Kullanıcı güncellendi.');
                }
                $roles = $db->fetchAll('SELECT * FROM roles');
                $firms = $db->fetchAll('SELECT * FROM firms');
                $flash = flash();
                view('admin/user_form', compact('user', 'roles', 'firms', 'flash'));
            })(),

            'sil' => (function() use ($db, $entityId, $audit, $userId) {
                $user = $db->fetch('SELECT id, email FROM users WHERE id = ?', [$entityId]);
                if ($user) $audit->log($userId, 'user_delete', 'user', $entityId, ['email' => $user['email']]);
                $db->delete('users', 'id = ?', [$entityId]);
                redirect('/admin/kullanicilar', 'Kullanıcı silindi.');
            })(),

            'kayit' => (function() use ($db, $entityId, $method, $audit, $userId) {
                if ($method === 'POST') {
                    csrf_check();
                    $courseId = (int)($_POST['course_id'] ?? 0);
                    if (!$db->exists('enrollments', 'user_id = ? AND course_id = ?', [$entityId, $courseId])) {
                        // Use PackageEnroller for packages so child modules are auto-enrolled and sequentially locked
                        $course = $db->fetch('SELECT id, parent_course_id FROM courses WHERE id = ?', [$courseId]);
                        if ($course && $course['parent_course_id'] === null) {
                            // Package → enroll in package + all child modules with sequential locking
                            $enrolled = (new \ISG\PackageEnroller($db))->enrollPackage($entityId, $courseId);
                            $enrollId = $db->fetch('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?', [$entityId, $courseId])['id'] ?? 0;
                        } else {
                            // Standalone or child course → direct enroll
                            $enrollId = $db->insert('enrollments', ['user_id' => $entityId, 'course_id' => $courseId]);
                        }
                        $audit->log($userId, 'user_enroll', 'enrollment', $enrollId, [
                            'user_id' => $entityId, 'course_id' => $courseId,
                        ]);
                    }
                    redirect('/admin/kullanicilar', 'Kullanıcı kursa kaydedildi.');
                }
                $user = $db->fetch('SELECT * FROM users WHERE id = ?', [$entityId]);
                // Only show packages and standalone courses (not child modules) for enrollment
                $courses = $db->fetchAll("SELECT id, title, topic_type FROM courses WHERE status = 'active' AND (parent_course_id IS NULL) ORDER BY title");
                $flash = flash();
                view('admin/enroll_form', compact('user', 'courses', 'flash'));
            })(),

            default => (function() use ($db) {
                $search = trim($_GET['ara'] ?? '');
                $sql = 'SELECT u.*, r.name AS role_name, f.name AS firm_name
                        FROM users u JOIN roles r ON u.role_id = r.id
                        LEFT JOIN firms f ON u.firm_id = f.id';
                $params = [];
                if ($search) {
                    $sql .= ' WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?';
                    $params = ["%$search%", "%$search%", "%$search%"];
                }
                $sql .= ' ORDER BY u.created_at DESC';
                $users = $db->fetchAll($sql, $params);
                $flash = flash();
                view('admin/users', compact('users', 'search', 'flash'));
            })(),
        };
    })(),

    'raporlar' => (function() use ($db, $subaction, $method) {
        $exporter = new ReportExporter($db);

        // Parse shared filters (used both for download and display)
        $buildFilters = function() {
            return [
                'date_from'   => $_GET['date_from']   ?? '',
                'date_to'     => $_GET['date_to']     ?? '',
                'firm_id'     => (int)($_GET['firm_id']     ?? 0) ?: null,
                'category_id' => (int)($_GET['category_id'] ?? 0) ?: null,
                'course_id'   => (int)($_GET['course_id']   ?? 0) ?: null,
                'course_type' => $_GET['course_type'] ?? '',
                'status'      => $_GET['status']      ?? '',
                'expiry_days' => (isset($_GET['expiry_days']) && $_GET['expiry_days'] !== '') ? (int)$_GET['expiry_days'] : 30,
                'user_ids'    => array_filter(array_map('intval', (array)($_GET['user_ids'] ?? []))),
            ];
        };

        // Download endpoint: /admin/raporlar/indir?format=excel|pdf&rapor=...
        if ($subaction === 'indir') {
            $format  = $_GET['format'] ?? 'excel';
            $rapor   = $_GET['rapor']  ?? 'kurs';
            $filters = $buildFilters();
            if ($format === 'pdf') {
                $exporter->exportPdf($rapor, $filters);
            } else {
                $exporter->exportExcel($rapor, $filters);
            }
            exit;
        }

        $rapor   = $_GET['rapor'] ?? 'kurs';
        $filters = $buildFilters();

        $reportData  = [];
        $sistemStats = [];

        switch ($rapor) {
            case 'kullanici':
                $reportData = $exporter->getKullaniciData($filters);
                break;
            case 'firma':
                $reportData = $exporter->getFirmaData($filters);
                break;
            case 'katilimci':
                $reportData = $exporter->getKatilimciData($filters);
                break;
            case 'sistem':
                $sistemStats = $exporter->getSistemData();
                break;
            case 'yenileme':
                $reportData = $exporter->getYenilemeData($filters);
                break;
            default: // kurs
                $rapor      = 'kurs';
                $reportData = $exporter->getKursData($filters);
                break;
        }

        $firms      = $db->fetchAll('SELECT id, name FROM firms ORDER BY name');
        $categories = $db->fetchAll('SELECT id, name FROM course_categories ORDER BY name');
        $packages   = $db->fetchAll("SELECT id, title FROM courses WHERE parent_course_id IS NULL AND status = 'active' ORDER BY title");
        $modules    = $db->fetchAll("SELECT id, title, parent_course_id FROM courses WHERE parent_course_id IS NOT NULL AND status = 'active' ORDER BY title");
        $allUsers   = $db->fetchAll("SELECT id, first_name, last_name, email, firm_id FROM users WHERE role_id = 4 AND status = 'active' ORDER BY last_name, first_name");
        $flash      = flash();
        view('admin/reports', compact(
            'rapor', 'filters', 'reportData', 'sistemStats',
            'firms', 'categories', 'packages', 'modules', 'allUsers', 'flash'
        ));
    })(),

    'sertifikalar' => (function() use ($db, $subaction, $entityId, $method, $audit, $userId) {
        if ($method === 'POST') {
            csrf_check();
            if ($subaction === 'iptal') {
                $cert = $db->fetch('SELECT * FROM certificates WHERE id = ?', [$entityId]);
                if ($cert) {
                    $db->update('certificates', ['is_valid' => 0], 'id = ?', [$entityId]);
                    $audit->log($userId, 'cert_revoke', 'certificate', $entityId, ['cert_number' => $cert['cert_number']]);
                    redirect('/admin/sertifikalar', 'Sertifika iptal edildi.');
                }
            } elseif ($subaction === 'etkinlestir') {
                $cert = $db->fetch('SELECT * FROM certificates WHERE id = ?', [$entityId]);
                if ($cert) {
                    $db->update('certificates', ['is_valid' => 1], 'id = ?', [$entityId]);
                    $audit->log($userId, 'cert_restore', 'certificate', $entityId, ['cert_number' => $cert['cert_number']]);
                    redirect('/admin/sertifikalar', 'Sertifika yeniden etkinleştirildi.');
                }
            } elseif ($subaction === 'ekle') {
                $targetUserId = (int)$_POST['user_id'];
                $courseId     = (int)$_POST['course_id'];
                if ($targetUserId && $courseId) {
                    $certObj = new Certificate();
                    $cert    = $certObj->issue($targetUserId, $courseId);
                    $audit->log($userId, 'cert_issue', 'certificate', $cert['id'] ?? null, [
                        'cert_number' => $cert['cert_number'] ?? null,
                        'user_id'     => $targetUserId,
                        'course_id'   => $courseId,
                    ]);
                    redirect('/admin/sertifikalar', "Sertifika oluşturuldu: " . ($cert['cert_number'] ?? ''));
                }
            }
            redirect('/admin/sertifikalar');
        }

        // Search/filter
        $search    = trim($_GET['search'] ?? '');
        $firmFilter = (int)($_GET['firm_id'] ?? 0);
        $validFilter = $_GET['valid'] ?? '';

        $where  = ['1=1'];
        $params = [];
        if ($search) {
            $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR cert.cert_number LIKE ?)';
            $like     = "%$search%";
            $params   = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($firmFilter) {
            $where[]  = 'u.firm_id = ?';
            $params[] = $firmFilter;
        }
        if ($validFilter !== '') {
            $where[]  = 'cert.is_valid = ?';
            $params[] = (int)$validFilter;
        }

        $certs = $db->fetchAll(
            'SELECT cert.*, u.first_name, u.last_name, u.email,
                    c.title AS kurs, f.name AS firma, cert.pdf_path
             FROM certificates cert
             JOIN users u ON u.id = cert.user_id
             JOIN courses c ON c.id = cert.course_id
             LEFT JOIN firms f ON f.id = u.firm_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY cert.issued_at DESC',
            $params
        );

        $firms   = $db->fetchAll('SELECT id, name FROM firms ORDER BY name');
        $users   = $db->fetchAll('SELECT id, first_name, last_name, email FROM users WHERE role_id = 4 ORDER BY first_name');
        $courses = $db->fetchAll('SELECT id, title FROM courses WHERE status = "active" ORDER BY title');
        $flash   = flash();
        view('admin/sertifikalar', compact('certs', 'firms', 'users', 'courses', 'search', 'firmFilter', 'validFilter', 'flash'));
    })(),

    'sinavlar' => (function() use ($db, $subaction, $entityId, $method, $audit, $userId) {
        match($subaction) {
            'ekle' => (function() use ($db, $method) {
                if ($method === 'POST') {
                    csrf_check();
                    $examId = $db->insert('exams', [
                        'course_id'          => (int)$_POST['course_id'],
                        'title'              => trim($_POST['title']),
                        'exam_type'          => $_POST['exam_type'] ?? 'final',
                        'duration_minutes'   => (int)($_POST['duration_minutes'] ?? 30),
                        'pass_score'         => (int)($_POST['pass_score'] ?? 70),
                        'question_count'     => (int)($_POST['question_count'] ?? 10),
                        'shuffle_questions'  => isset($_POST['shuffle_questions']) ? 1 : 0,
                        'shuffle_answers'    => isset($_POST['shuffle_answers']) ? 1 : 0,
                        'max_attempts'       => (int)($_POST['max_attempts'] ?? 3),
                        'status'             => 'active',
                    ]);
                    redirect('/admin/sinavlar/sorular/' . $examId, 'Sınav oluşturuldu. Şimdi soru ekleyin.');
                }
                $courses = $db->fetchAll('SELECT id, title FROM courses WHERE status = "active"');
                $flash = flash();
                view('admin/exam_form', compact('courses', 'flash'));
            })(),

            'sorular' => (function() use ($db, $entityId, $method) {
                $exam = $db->fetch('SELECT e.*, c.title AS course_title FROM exams e JOIN courses c ON e.course_id = c.id WHERE e.id = ?', [$entityId]);
                if (!$exam) redirect('/admin/sinavlar');
                if ($method === 'POST') {
                    csrf_check();
                    $questionId = $db->insert('questions', [
                        'exam_id'       => $entityId,
                        'question_text' => trim($_POST['question_text']),
                        'question_type' => $_POST['question_type'] ?? 'multiple_choice',
                    ]);
                    $options = $_POST['options'] ?? [];
                    $correct = (int)($_POST['correct_option'] ?? 0);
                    foreach ($options as $i => $optText) {
                        if (trim($optText) === '') continue;
                        $db->insert('question_options', [
                            'question_id' => $questionId,
                            'option_text' => trim($optText),
                            'is_correct'  => ($i === $correct) ? 1 : 0,
                            'order_num'   => $i,
                        ]);
                    }
                    redirect('/admin/sinavlar/sorular/' . $entityId, 'Soru eklendi.');
                }
                $questions = $db->fetchAll('SELECT * FROM questions WHERE exam_id = ? ORDER BY order_num', [$entityId]);
                foreach ($questions as &$q) {
                    $q['options'] = $db->fetchAll('SELECT * FROM question_options WHERE question_id = ? ORDER BY order_num', [$q['id']]);
                }
                $flash = flash();
                view('admin/exam_questions', compact('exam', 'questions', 'flash'));
            })(),

            'sil' => (function() use ($db, $entityId) {
                $db->delete('exams', 'id = ?', [$entityId]);
                redirect('/admin/sinavlar', 'Sınav silindi.');
            })(),

            'soru-sil' => (function() use ($db, $entityId) {
                $question = $db->fetch('SELECT exam_id FROM questions WHERE id = ?', [$entityId]);
                if ($question) {
                    $db->delete('questions', 'id = ?', [$entityId]);
                    redirect('/admin/sinavlar/sorular/' . $question['exam_id'], 'Soru silindi.');
                }
                redirect('/admin/sinavlar');
            })(),

            default => (function() use ($db) {
                $exams = $db->fetchAll(
                    'SELECT e.*, c.title AS course_title,
                            (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) AS question_count_actual
                     FROM exams e JOIN courses c ON e.course_id = c.id
                     ORDER BY e.created_at DESC'
                );
                $flash = flash();
                view('admin/exams', compact('exams', 'flash'));
            })(),
        };
    })(),

    'firmalar' => (function() use ($db, $subaction, $entityId, $method, $audit, $userId) {
        match($subaction) {
            'ekle' => (function() use ($db, $method, $audit, $userId) {
                if ($method === 'POST') {
                    csrf_check();
                    $companyCode = strtoupper(trim($_POST['company_code'] ?? ''));
                    if ($companyCode && $db->exists('firms', 'company_code = ?', [$companyCode])) {
                        $flash = flash();
                        view('admin/firma_form', ['flash' => $flash, 'error' => 'Bu şirket kodu zaten kullanılıyor.']);
                        return;
                    }
                    $logoPath = null;
                    if (!empty($_FILES['logo_file']['tmp_name'])) {
                        if (!is_dir(LOGO_DIR)) mkdir(LOGO_DIR, 0755, true);
                        $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['png','jpg','jpeg','gif','svg']) && $_FILES['logo_file']['size'] <= 2097152) {
                            $logoPath = 'logo_' . uniqid() . '.' . $ext;
                            move_uploaded_file($_FILES['logo_file']['tmp_name'], LOGO_DIR . $logoPath);
                        }
                    }
                    $newFirmId = $db->insert('firms', [
                        'name'              => trim($_POST['name']),
                        'tax_number'        => trim($_POST['tax_number'] ?? ''),
                        'contact_name'      => trim($_POST['contact_name'] ?? ''),
                        'contact_email'     => trim($_POST['contact_email'] ?? ''),
                        'contact_phone'     => trim($_POST['contact_phone'] ?? ''),
                        'address'           => trim($_POST['address'] ?? ''),
                        'status'            => $_POST['status'] ?? 'active',
                        'company_code'      => $companyCode ?: null,
                        'logo_path'         => $logoPath,
                        'primary_color'     => trim($_POST['primary_color'] ?? '#005695'),
                        'secondary_color'   => trim($_POST['secondary_color'] ?? '#0072b5'),
                        'header_title'      => trim($_POST['header_title'] ?? ''),
                        'footer_text'       => trim($_POST['footer_text'] ?? ''),
                        'announcement'      => trim($_POST['announcement'] ?? ''),
                        'announcement_type' => in_array($_POST['announcement_type'] ?? '', ['info','warning','danger','success']) ? $_POST['announcement_type'] : 'info',
                    ]);
                    $audit->log($userId, 'firm_create', 'firm', $newFirmId, ['name' => trim($_POST['name'])]);
                    redirect('/admin/firmalar', 'Firma eklendi.');
                }
                $flash = flash();
                view('admin/firma_form', compact('flash'));
            })(),

            'duzenle' => (function() use ($db, $entityId, $method, $audit, $userId) {
                $firm = $db->fetch('SELECT * FROM firms WHERE id = ?', [$entityId]);
                if (!$firm) redirect('/admin/firmalar', 'Firma bulunamadı.', 'error');
                if ($method === 'POST') {
                    csrf_check();
                    $companyCode = strtoupper(trim($_POST['company_code'] ?? ''));
                    if ($companyCode && $db->fetch('SELECT id FROM firms WHERE company_code = ? AND id != ?', [$companyCode, $entityId])) {
                        $flash = flash();
                        view('admin/firma_form', compact('firm', 'flash') + ['error' => 'Bu şirket kodu zaten kullanılıyor.']);
                        return;
                    }
                    $logoPath = $firm['logo_path'];
                    if (!empty($_FILES['logo_file']['tmp_name'])) {
                        if (!is_dir(LOGO_DIR)) mkdir(LOGO_DIR, 0755, true);
                        $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['png','jpg','jpeg','gif','svg']) && $_FILES['logo_file']['size'] <= 2097152) {
                            if ($logoPath && file_exists(LOGO_DIR . $logoPath)) {
                                @unlink(LOGO_DIR . $logoPath);
                            }
                            $logoPath = 'logo_' . $entityId . '_' . uniqid() . '.' . $ext;
                            move_uploaded_file($_FILES['logo_file']['tmp_name'], LOGO_DIR . $logoPath);
                        }
                    }
                    $db->update('firms', [
                        'name'              => trim($_POST['name']),
                        'tax_number'        => trim($_POST['tax_number'] ?? ''),
                        'contact_name'      => trim($_POST['contact_name'] ?? ''),
                        'contact_email'     => trim($_POST['contact_email'] ?? ''),
                        'contact_phone'     => trim($_POST['contact_phone'] ?? ''),
                        'address'           => trim($_POST['address'] ?? ''),
                        'status'            => $_POST['status'] ?? 'active',
                        'company_code'      => $companyCode ?: null,
                        'logo_path'         => $logoPath,
                        'primary_color'     => trim($_POST['primary_color'] ?? '#005695'),
                        'secondary_color'   => trim($_POST['secondary_color'] ?? '#0072b5'),
                        'header_title'      => trim($_POST['header_title'] ?? ''),
                        'footer_text'       => trim($_POST['footer_text'] ?? ''),
                        'announcement'      => trim($_POST['announcement'] ?? ''),
                        'announcement_type' => in_array($_POST['announcement_type'] ?? '', ['info','warning','danger','success']) ? $_POST['announcement_type'] : 'info',
                    ], 'id = ?', [$entityId]);
                    $audit->log($userId, 'firm_update', 'firm', $entityId, [
                        'old' => ['name' => $firm['name'], 'status' => $firm['status']],
                        'new' => ['name' => trim($_POST['name']), 'status' => $_POST['status'] ?? 'active'],
                    ]);
                    redirect('/admin/firmalar', 'Firma güncellendi.');
                }
                $flash = flash();
                view('admin/firma_form', compact('firm', 'flash'));
            })(),

            'sil' => (function() use ($db, $entityId, $audit, $userId) {
                if ($entityId === 1) {
                    redirect('/admin/firmalar', 'Varsayılan firma silinemez.', 'error');
                }
                $firm = $db->fetch('SELECT id, name FROM firms WHERE id = ?', [$entityId]);
                if ($firm) $audit->log($userId, 'firm_delete', 'firm', $entityId, ['name' => $firm['name']]);
                $db->update('users', ['firm_id' => 1], 'firm_id = ?', [$entityId]);
                $db->delete('firms', 'id = ?', [$entityId]);
                redirect('/admin/firmalar', 'Firma silindi.');
            })(),

            default => (function() use ($db) {
                $firms = $db->fetchAll(
                    'SELECT f.*, COUNT(u.id) AS user_count
                     FROM firms f LEFT JOIN users u ON u.firm_id = f.id
                     GROUP BY f.id ORDER BY f.name'
                );
                $flash = flash();
                view('admin/firmalar', compact('firms', 'flash'));
            })(),
        };
    })(),

    'grup-anahtarlari' => (function() use ($db, $subaction, $entityId, $method, $userId) {
        match($subaction) {
            'ekle' => (function() use ($db, $method, $userId) {
                if ($method === 'POST') {
                    csrf_check();
                    $code = strtoupper(trim($_POST['key_code'] ?? ''));
                    if (!$code) {
                        $code = strtoupper(bin2hex(random_bytes(4)));
                    }
                    if ($db->exists('group_keys', 'key_code = ?', [$code])) {
                        $code = $code . '-' . strtoupper(bin2hex(random_bytes(2)));
                    }
                    $keyId = $db->insert('group_keys', [
                        'key_code'   => $code,
                        'name'       => trim($_POST['name']),
                        'description'=> trim($_POST['description'] ?? ''),
                        'created_by' => $userId,
                        'status'     => $_POST['status'] ?? 'active',
                    ]);
                    $courseIds = $_POST['course_ids'] ?? [];
                    foreach ($courseIds as $cId) {
                        $cId = (int)$cId;
                        if ($cId) $db->insert('group_key_courses', ['group_key_id' => $keyId, 'course_id' => $cId]);
                    }
                    redirect('/admin/grup-anahtarlari', 'Grup anahtarı oluşturuldu.');
                }
                $courses = $db->fetchAll('SELECT id, title FROM courses WHERE status = "active" ORDER BY title');
                $flash = flash();
                view('admin/group_key_form', compact('courses', 'flash'));
            })(),

            'sil' => (function() use ($db, $entityId, $method) {
                if ($method !== 'POST') redirect('/admin/grup-anahtarlari');
                csrf_check();
                $db->delete('group_keys', 'id = ?', [$entityId]);
                redirect('/admin/grup-anahtarlari', 'Grup anahtarı silindi.');
            })(),

            'duzenle' => (function() use ($db, $entityId, $method) {
                $key = $db->fetch('SELECT * FROM group_keys WHERE id = ?', [$entityId]);
                if (!$key) redirect('/admin/grup-anahtarlari', 'Bulunamadı.', 'error');
                if ($method === 'POST') {
                    csrf_check();
                    $db->update('group_keys', [
                        'name'       => trim($_POST['name']),
                        'description'=> trim($_POST['description'] ?? ''),
                        'status'     => $_POST['status'] ?? 'active',
                    ], 'id = ?', [$entityId]);
                    $db->delete('group_key_courses', 'group_key_id = ?', [$entityId]);
                    foreach ($_POST['course_ids'] ?? [] as $cId) {
                        $cId = (int)$cId;
                        if ($cId) $db->insert('group_key_courses', ['group_key_id' => $entityId, 'course_id' => $cId]);
                    }
                    redirect('/admin/grup-anahtarlari', 'Grup anahtarı güncellendi.');
                }
                $courses = $db->fetchAll('SELECT id, title FROM courses WHERE status = "active" ORDER BY title');
                $selectedCourses = array_column(
                    $db->fetchAll('SELECT course_id FROM group_key_courses WHERE group_key_id = ?', [$entityId]),
                    'course_id'
                );
                $flash = flash();
                view('admin/group_key_form', compact('key', 'courses', 'selectedCourses', 'flash'));
            })(),

            default => (function() use ($db) {
                $keys = $db->fetchAll(
                    'SELECT gk.*,
                            u.first_name AS creator_first, u.last_name AS creator_last,
                            COUNT(DISTINCT gkc.course_id) AS course_count,
                            COUNT(DISTINCT gku.user_id) AS usage_count
                     FROM group_keys gk
                     LEFT JOIN users u ON gk.created_by = u.id
                     LEFT JOIN group_key_courses gkc ON gkc.group_key_id = gk.id
                     LEFT JOIN group_key_usage gku ON gku.group_key_id = gk.id
                     GROUP BY gk.id ORDER BY gk.created_at DESC'
                );
                $flash = flash();
                view('admin/group_keys', compact('keys', 'flash'));
            })(),
        };
    })(),

    'denetim-gunlugu' => (function() use ($db) {
        $page      = max(1, (int)($_GET['sayfa'] ?? 1));
        $perPage   = 50;
        $offset    = ($page - 1) * $perPage;
        $search    = trim($_GET['ara'] ?? '');
        $action    = trim($_GET['eylem'] ?? '');
        $dateFrom  = trim($_GET['tarih_baslangic'] ?? '');
        $dateTo    = trim($_GET['tarih_bitis'] ?? '');

        $where  = ['1=1'];
        $params = [];
        if ($search) {
            $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
            $like     = "%$search%";
            $params   = array_merge($params, [$like, $like, $like]);
        }
        if ($action) {
            $where[]  = 'a.action = ?';
            $params[] = $action;
        }
        if ($dateFrom) {
            $where[]  = 'a.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $where[]  = 'a.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        $totalRow = $db->fetch(
            'SELECT COUNT(*) AS cnt FROM audit_log a LEFT JOIN users u ON a.user_id = u.id WHERE ' . implode(' AND ', $where),
            $params
        );
        $total   = (int)($totalRow['cnt'] ?? 0);
        $pages   = max(1, (int)ceil($total / $perPage));

        $logs = $db->fetchAll(
            'SELECT a.*, u.first_name, u.last_name, u.email
             FROM audit_log a LEFT JOIN users u ON a.user_id = u.id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY a.created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $params
        );

        $actions = $db->fetchAll('SELECT DISTINCT action FROM audit_log ORDER BY action');
        $flash = flash();
        view('admin/denetim_gunlugu', compact('logs', 'actions', 'search', 'action', 'dateFrom', 'dateTo', 'page', 'pages', 'total', 'flash'));
    })(),

    'sistem' => (function() use ($db, $subaction, $method, $auth, $audit, $userId) {
        $auth->requireRole('superadmin');

        $maintenanceFlagFile = __DIR__ . '/../maintenance.flag';

        if ($subaction === 'bakim') {
            if ($method === 'POST') {
                csrf_check();
                $toggle = $_POST['toggle'] ?? '';
                if ($toggle === 'on') {
                    file_put_contents($maintenanceFlagFile, date('Y-m-d H:i:s'));
                    $audit->log($userId, 'maintenance_on', 'system', null);
                    redirect('/admin/sistem/bakim', 'Bakım modu AKTİF edildi.');
                } else {
                    if (file_exists($maintenanceFlagFile)) @unlink($maintenanceFlagFile);
                    $audit->log($userId, 'maintenance_off', 'system', null);
                    redirect('/admin/sistem/bakim', 'Bakım modu devre dışı bırakıldı.');
                }
            }
            $isMaintenanceOn = file_exists($maintenanceFlagFile);
            $flash = flash();
            view('admin/maintenance', compact('isMaintenanceOn', 'flash'));

        } elseif ($subaction === 'yedek') {
            if ($method === 'POST') {
                csrf_check();
                $tables = $_POST['tables'] ?? 'all';
                $tableList = '';
                if ($tables !== 'all') {
                    $allowed = ['users', 'enrollments', 'scorm_tracking', 'certificates'];
                    $selected = array_filter((array)$_POST['table_list'] ?? [], fn($t) => in_array($t, $allowed));
                    $tableList = implode(' ', array_map('escapeshellarg', $selected));
                }
                $dbName = 'isg_lms';
                $host   = '127.0.0.1';
                $user   = 'root';
                $filename = 'isg_backup_' . date('Ymd_His') . '.sql.gz';
                $audit->log($userId, 'backup_download', 'system', null, ['tables' => $tables]);

                header('Content-Type: application/gzip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: no-cache, no-store');
                ob_end_clean();

                $cmd = "mysqldump -u $user -h $host $dbName $tableList 2>/dev/null | gzip";
                passthru($cmd);
                exit;
            }
            $flash = flash();
            view('admin/backup', compact('flash'));

        } else {
            redirect('/admin/sistem/bakim');
        }
    })(),

    'rol-degistir' => (function() use ($auth, $userId, $subaction) {
        $auth->requireRole('admin', 'superadmin');
        $allowed = ['ogrenci' => 'student', 'egitmen' => 'egitmen'];
        if (!isset($allowed[$subaction])) {
            redirect('/admin', 'Geçersiz rol.', 'error');
        }
        $newRole = $allowed[$subaction];
        $_SESSION['original_role'] = $_SESSION['role'];
        $_SESSION['role'] = $newRole;
        $_SESSION['user']['role'] = $newRole;

        if ($newRole === 'student') {
            redirect('/ogrenci', 'Öğrenci görünümüne geçildi. Geri dönmek için "Yönetim Moduna Dön" butonuna tıklayın.');
        } else {
            redirect('/admin', 'Eğitmen görünümüne geçildi.');
        }
    })(),

    'yyz' => (function() use ($db, $uri, $method, $auth, $userId) {
        require __DIR__ . '/FaceToFaceAdminController.php';
    })(),

    default => redirect('/admin/dashboard'),
};
