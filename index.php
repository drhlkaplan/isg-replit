<?php
declare(strict_types=1);

ob_start();
session_start();

// Kurulum tamamlanmamışsa install.php'ye yönlendir
if (!file_exists(__DIR__ . '/install.lock') && basename($_SERVER['SCRIPT_FILENAME'] ?? '') !== 'install.php') {
    // Use script-relative redirect so it works in root or subdirectory installations
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    header('Location: ' . $scriptDir . '/install.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use ISG\DB;
use ISG\Auth;

$auth = new Auth();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

function view(string $file, array $data = []): void {
    extract($data);
    $viewPath = __DIR__ . '/views/' . $file . '.php';
    if (!file_exists($viewPath)) {
        http_response_code(404);
        echo '<h1>404 - Sayfa bulunamadı</h1>';
        return;
    }
    require $viewPath;
}

function redirect(string $path, string $msg = '', string $type = 'success'): void {
    if ($msg) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }
    header('Location: ' . $path);
    exit;
}

function flash(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('CSRF doğrulama hatası.');
    }
}

$db = DB::getInstance();

// Maintenance mode check — block non-admins with 503
$maintenanceFlagFile = __DIR__ . '/maintenance.flag';
if (file_exists($maintenanceFlagFile) && !in_array($auth->role(), ['superadmin', 'admin'])) {
    http_response_code(503);
    header('Retry-After: 3600');
    $maintenanceSince = trim(file_get_contents($maintenanceFlagFile));
    include __DIR__ . '/views/maintenance.php';
    exit;
}

match(true) {
    $uri === '/' && $method === 'GET' => (function() use ($auth, $db) {
        if ($auth->check()) {
            if ($auth->isAdmin()) redirect('/admin');
            elseif (($auth->user()['role'] ?? '') === 'firm') redirect('/firma');
            else redirect('/ogrenci');
        }
        $categories = $db->fetchAll('SELECT * FROM course_categories ORDER BY id');
        $packages   = $db->fetchAll(
            "SELECT c.*, cat.name AS cat_name, cat.code AS cat_code, cat.color AS cat_color
             FROM courses c
             JOIN course_categories cat ON cat.id = c.category_id
             WHERE c.topic_type = 'paket' AND c.status = 'active'
               AND c.id IN (1,2,3,4,5,6)
             ORDER BY c.training_type, cat.id"
        );
        $stats = $db->fetch(
            "SELECT
               (SELECT COUNT(*) FROM users WHERE role_id IN (4,5)) AS total_users,
               (SELECT COUNT(*) FROM certificates WHERE issued_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)) AS certs_year,
               (SELECT COUNT(*) FROM firms WHERE status='active') AS total_firms"
        );
        view('home', compact('categories', 'packages', 'stats'));
    })(),

    $uri === '/giris' => (function() use ($auth, $method, $db) {
        if ($auth->check()) redirect('/');
        $kod = strtoupper(trim($_GET['kod'] ?? $_POST['company_code'] ?? ''));
        $brandFirm = $kod ? $db->fetch('SELECT * FROM firms WHERE company_code = ?', [$kod]) : null;
        if ($method === 'POST') {
            csrf_check();
            $loginMode = $_POST['login_mode'] ?? 'email';
            $pass      = $_POST['password'] ?? '';
            $ok = false;
            if ($loginMode === 'tc') {
                $tcNo = preg_replace('/\D/', '', $_POST['tc_identity_no'] ?? '');
                $ok   = $auth->loginWithTc($tcNo, $pass);
                $errMsg = 'TC kimlik numarası veya şifre hatalı.';
            } else {
                $email = trim($_POST['email'] ?? '');
                $ok    = $auth->login($email, $pass);
                $errMsg = 'E-posta veya şifre hatalı.';
            }
            if ($ok) {
                if ($auth->isAdmin()) redirect('/admin', 'Hoş geldiniz!');
                elseif (($auth->user()['role'] ?? '') === 'firm') redirect('/firma', 'Hoş geldiniz!');
                else redirect('/ogrenci', 'Hoş geldiniz!');
            } else {
                view('auth/login', ['error' => $errMsg, 'flash' => [], 'brandFirm' => $brandFirm, 'loginMode' => $loginMode]);
            }
        } else {
            view('auth/login', ['error' => null, 'flash' => flash(), 'brandFirm' => $brandFirm, 'loginMode' => 'email']);
        }
    })(),

    $uri === '/kayit' => (function() use ($auth, $method, $db) {
        if ($auth->check()) redirect('/');
        $kod = strtoupper(trim($_GET['kod'] ?? $_POST['company_code'] ?? ''));
        $brandFirm = $kod ? $db->fetch('SELECT * FROM firms WHERE company_code = ?', [$kod]) : null;
        if ($method === 'POST') {
            csrf_check();
            $firmId = $brandFirm ? (int)$brandFirm['id'] : 1;
            $data = [
                'first_name'     => trim($_POST['first_name'] ?? ''),
                'last_name'      => trim($_POST['last_name'] ?? ''),
                'email'          => trim($_POST['email'] ?? ''),
                'phone'          => trim($_POST['phone'] ?? ''),
                'tc_identity_no' => trim($_POST['tc_identity_no'] ?? ''),
                'password'       => $_POST['password'] ?? '',
                'role_id'        => 4,
                'firm_id'        => $firmId,
            ];
            if ($db->exists('users', 'email = ?', [$data['email']])) {
                view('auth/register', ['error' => 'Bu e-posta adresi kayıtlı.', 'flash' => [], 'brandFirm' => $brandFirm]);
            } else {
                $newUserId = $auth->register($data);
                // Redeem group key if provided
                $groupKeyCode = trim($_POST['group_key_code'] ?? '');
                if ($groupKeyCode && $newUserId) {
                    $redeemer = new \ISG\GroupKeyRedeemer($db);
                    $redeemer->redeem($groupKeyCode, $newUserId);
                }
                redirect('/giris' . ($kod ? '?kod=' . urlencode($kod) : ''), 'Kayıt başarılı! Giriş yapabilirsiniz.');
            }
        } else {
            view('auth/register', ['error' => null, 'flash' => flash(), 'brandFirm' => $brandFirm]);
        }
    })(),

    $uri === '/cikis' => (function() use ($auth) {
        $auth->logout();
        redirect('/giris', 'Çıkış yapıldı.');
    })(),

    // Role switching — back to original admin role
    $uri === '/rol-geri-don' => (function() use ($auth) {
        $auth->requireLogin();
        if (!empty($_SESSION['original_role'])) {
            $origRole = $_SESSION['original_role'];
            $_SESSION['role'] = $origRole;
            $_SESSION['user']['role'] = $origRole;
            unset($_SESSION['original_role']);
        }
        redirect('/admin', 'Yönetim moduna geri döndünüz.');
    })(),

    str_starts_with($uri, '/ogrenci') => (function() use ($auth, $uri, $method, $db) {
        $auth->requireRole('student', 'admin', 'superadmin', 'egitmen', 'firm');
        require __DIR__ . '/controllers/StudentController.php';
    })(),

    str_starts_with($uri, '/admin') => (function() use ($auth, $uri, $method, $db) {
        $auth->requireRole('admin', 'superadmin', 'egitmen');
        require __DIR__ . '/controllers/AdminController.php';
    })(),

    str_starts_with($uri, '/firma') => (function() use ($auth, $uri, $method, $db) {
        $auth->requireRole('firm', 'admin', 'superadmin');
        require __DIR__ . '/controllers/FirmController.php';
    })(),

    str_starts_with($uri, '/scorm') => (function() use ($auth, $uri, $method, $db) {
        $auth->requireLogin();
        require __DIR__ . '/controllers/SCORMController.php';
    })(),

    str_starts_with($uri, '/sinav') => (function() use ($auth, $uri, $method, $db) {
        $auth->requireRole('student', 'admin', 'superadmin');
        require __DIR__ . '/controllers/ExamController.php';
    })(),

    str_starts_with($uri, '/sertifika') => (function() use ($auth, $uri, $method, $db) {
        $auth->requireLogin();
        require __DIR__ . '/controllers/CertificateController.php';
    })(),

    str_starts_with($uri, '/dogrula') => (function() use ($uri, $db) {
        require __DIR__ . '/controllers/PublicController.php';
    })(),

    str_starts_with($uri, '/attend') => (function() use ($auth, $uri, $method, $db) {
        require __DIR__ . '/controllers/AttendController.php';
    })(),

    default => (function() {
        http_response_code(404);
        view('404');
    })(),
};
