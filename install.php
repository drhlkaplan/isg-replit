<?php
/**
 * İSG LMS — Kurulum Sihirbazı
 * Sürüm: 1.0.0
 * PHP >= 8.1 gerektirir
 */
declare(strict_types=1);
ob_start();

// ── AJAX: Veritabanı bağlantı testi (en üstte — HTML output öncesi) ──
if (isset($_GET['test_db']) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    $h    = trim($_POST['host'] ?? 'localhost');
    $port = (int)($_POST['port'] ?? 3306);
    $u    = trim($_POST['user'] ?? '');
    $p    = $_POST['pass'] ?? '';
    try {
        $pdo = new PDO(
            "mysql:host={$h};port={$port};charset=utf8mb4",
            $u, $p,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
        echo json_encode(['ok' => true, 'msg' => "Bağlantı başarılı! MySQL {$ver}"]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
    }
    exit;
}

define('INSTALLER_VERSION', '1.0.0');
define('REQUIRED_PHP',      '8.1.0');

// Kurulum tamamlandıysa engelle
if (file_exists(__DIR__ . '/install.lock')) {
    die(<<<HTML
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kurulum Tamamlandı</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head><body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="card shadow-sm p-5 text-center" style="max-width:420px">
<div class="display-1 text-success mb-3">🔒</div>
<h4 class="fw-bold">Kurulum Zaten Tamamlandı</h4>
<p class="text-muted">Güvenlik nedeniyle <code>install.php</code> ve <code>install.lock</code> dosyalarını sunucudan silin.</p>
<a href="/" class="btn btn-primary mt-2">Platforma Git →</a>
</div></body></html>
HTML);
}

session_start();

// ─── Yardımcı fonksiyonlar ────────────────────────────────────────────────────

function checkRequirements(): array {
    $results = [];

    $results[] = [
        'label' => 'PHP Sürümü ≥ 8.1',
        'ok'    => version_compare(PHP_VERSION, REQUIRED_PHP, '>='),
        'value' => PHP_VERSION,
    ];

    $extensions = [
        'pdo'       => 'PDO',
        'pdo_mysql' => 'PDO MySQL',
        'json'      => 'JSON',
        'mbstring'  => 'Mbstring',
        'zip'       => 'ZipArchive (SCORM)',
        'gd'        => 'GD (Sertifika)',
        'curl'      => 'cURL',
        'xml'       => 'XML',
        'openssl'   => 'OpenSSL',
        'fileinfo'  => 'FileInfo',
    ];
    foreach ($extensions as $ext => $label) {
        $loaded = extension_loaded($ext);
        $results[] = [
            'label'    => $label . ' eklentisi',
            'ok'       => $loaded,
            'value'    => $loaded ? 'Yüklü ✓' : 'Eksik ✗',
            'required' => in_array($ext, ['pdo', 'pdo_mysql', 'json', 'mbstring']),
        ];
    }

    $dirs = [
        'uploads/'              => 'Yüklemeler dizini (uploads/)',
        'uploads/scorm/'        => 'SCORM dizini',
        'uploads/certificates/' => 'Sertifika dizini',
        'uploads/logos/'        => 'Logo dizini',
        'uploads/thumbnails/'   => 'Küçük resim dizini',
    ];
    foreach ($dirs as $dir => $label) {
        $path   = __DIR__ . '/' . $dir;
        $exists = is_dir($path);
        if (!$exists) @mkdir($path, 0755, true);
        $writable = is_writable($path ?: __DIR__);
        $results[] = [
            'label'    => $label . ' yazılabilir',
            'ok'       => $writable,
            'value'    => $writable ? 'Yazılabilir ✓' : 'Yazma izni yok ✗',
            'required' => true,
        ];
    }

    $results[] = [
        'label' => 'config.php yazılabilir',
        'ok'    => is_writable(__DIR__) || (file_exists(__DIR__ . '/config.php') && is_writable(__DIR__ . '/config.php')),
        'value' => 'Kontrol edildi',
    ];

    $uploadMb = (int)ini_get('upload_max_filesize');
    $results[] = [
        'label' => 'SCORM yükleme limiti (≥ 64 MB önerilir)',
        'ok'    => $uploadMb >= 64,
        'value' => $uploadMb . ' MB',
        'required' => false,
    ];

    return $results;
}

function hasFatalError(array $reqs): bool {
    foreach ($reqs as $r) {
        if (!$r['ok'] && ($r['required'] ?? true)) return true;
    }
    return false;
}

function writePDO(array $p): PDO {
    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $p['host'], $p['port']);
    $pdo = new PDO($dsn, $p['user'], $p['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function runSqlFile(PDO $pdo, string $file): void {
    $sql = file_get_contents($file);
    // Split on semicolons but keep delimiter awareness simple
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($s) { return $s !== ''; }
    );
    foreach ($statements as $stmt) {
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            // Skip "table already exists" etc.
            if (!in_array($e->getCode(), ['42S01', '42000'])) throw $e;
        }
    }
}

function writeConfig(array $p): void {
    $dbHost    = addslashes($p['db_host']);
    $dbPort    = (int)$p['db_port'];
    $dbName    = addslashes($p['db_name']);
    $dbUser    = addslashes($p['db_user']);
    $dbPass    = addslashes($p['db_pass']);
    $appName   = addslashes($p['app_name']);
    $baseUrl   = rtrim($p['base_url'], '/');

    $baseUrlLine = $baseUrl
        ? "define('APP_URL', '" . addslashes($baseUrl) . "');"
        : <<<'PHP'
(function() {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    define('APP_URL', ($isSecure ? 'https' : 'http') . '://' . $host);
})();
PHP;

    $content = <<<PHP
<?php
define('DB_HOST',    '{$dbHost}');
define('DB_PORT',    {$dbPort});
define('DB_NAME',    '{$dbName}');
define('DB_USER',    '{$dbUser}');
define('DB_PASS',    '{$dbPass}');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',    '{$appName}');
define('APP_VERSION', '1.0.0');
define('APP_LANG',    'tr');

{$baseUrlLine}

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('SCORM_DIR',  __DIR__ . '/uploads/scorm/');
define('CERT_DIR',   __DIR__ . '/uploads/certificates/');
define('THUMB_DIR',  __DIR__ . '/uploads/thumbnails/');
define('LOGO_DIR',   __DIR__ . '/uploads/logos/');

define('SCORM_URL', APP_URL . '/uploads/scorm/');
define('CERT_URL',  APP_URL . '/uploads/certificates/');
define('LOGO_URL',  APP_URL . '/uploads/logos/');

define('SESSION_LIFETIME', 7200);
define('BCRYPT_COST',      12);

date_default_timezone_set('Europe/Istanbul');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
PHP;

    file_put_contents(__DIR__ . '/config.php', $content);
}

// ─── POST işleme ──────────────────────────────────────────────────────────────

$step      = (int)($_SESSION['install_step'] ?? 1);
$errors    = [];
$success   = [];
$installed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'install') {
        $p = [
            'db_host'        => trim($_POST['db_host']        ?? 'localhost'),
            'db_port'        => trim($_POST['db_port']        ?? '3306'),
            'db_name'        => trim($_POST['db_name']        ?? 'isg_lms'),
            'db_user'        => trim($_POST['db_user']        ?? ''),
            'db_pass'        => $_POST['db_pass']             ?? '',
            'app_name'       => trim($_POST['app_name']       ?? 'İSG Eğitim Platformu'),
            'base_url'       => trim($_POST['base_url']       ?? ''),
            'admin_first'    => trim($_POST['admin_first']    ?? ''),
            'admin_last'     => trim($_POST['admin_last']     ?? ''),
            'admin_email'    => trim($_POST['admin_email']    ?? ''),
            'admin_pass'     => $_POST['admin_pass']          ?? '',
            'admin_pass2'    => $_POST['admin_pass2']         ?? '',
            'seed_demo'      => !empty($_POST['seed_demo']),
            'table_prefix'   => '',
        ];

        // Validation
        if (!$p['db_user'])        $errors[] = 'Veritabanı kullanıcı adı boş olamaz.';
        if (!$p['admin_first'])    $errors[] = 'Admin adı boş olamaz.';
        if (!$p['admin_last'])     $errors[] = 'Admin soyadı boş olamaz.';
        if (!filter_var($p['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir admin e-posta adresi girin.';
        if (strlen($p['admin_pass']) < 8) $errors[] = 'Admin şifresi en az 8 karakter olmalıdır.';
        if ($p['admin_pass'] !== $p['admin_pass2']) $errors[] = 'Şifreler eşleşmiyor.';

        if (empty($errors)) {
            try {
                // 1. DB bağlantısı
                $pdo = writePDO($p);
                $success[] = 'Veritabanı sunucusuna bağlandı.';

                // 2. Veritabanı oluştur
                $dbName = preg_replace('/[^a-zA-Z0-9_]/', '_', $p['db_name']);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");
                $success[] = "Veritabanı '{$dbName}' hazır.";

                // 3. Schema'yı yükle
                $schemaFile = __DIR__ . '/db/schema.sql';
                if (!file_exists($schemaFile)) throw new RuntimeException('db/schema.sql bulunamadı.');
                runSqlFile($pdo, $schemaFile);
                $success[] = 'Tablo yapısı oluşturuldu (20 tablo).';

                // 4. Demo verisi (isteğe bağlı)
                if ($p['seed_demo']) {
                    $seedFile = __DIR__ . '/db/seed_courses.sql';
                    if (file_exists($seedFile)) {
                        runSqlFile($pdo, $seedFile);
                        $success[] = 'Demo kurs verileri yüklendi.';
                    }
                }

                // 5. Admin kullanıcısı oluştur / güncelle
                $hash = password_hash($p['admin_pass'], PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("
                    INSERT INTO users (firm_id, role_id, first_name, last_name, email, password, status)
                    VALUES (1, 1, ?, ?, ?, ?, 'active')
                    ON DUPLICATE KEY UPDATE
                        first_name = VALUES(first_name),
                        last_name  = VALUES(last_name),
                        password   = VALUES(password),
                        role_id    = 1,
                        status     = 'active'
                ");
                $stmt->execute([$p['admin_first'], $p['admin_last'], $p['admin_email'], $hash]);
                $success[] = "Süper admin hesabı oluşturuldu: {$p['admin_email']}";

                // 6. Firma kaydını güncelle
                $pdo->prepare("
                    UPDATE firms SET contact_email = ?, contact_name = ? WHERE id = 1
                ")->execute([$p['admin_email'], $p['admin_first'] . ' ' . $p['admin_last']]);

                // 7. config.php yaz
                writeConfig($p);
                $success[] = 'config.php yazıldı.';

                // 8. Yükleme dizinlerini oluştur
                $uploadDirs = ['uploads', 'uploads/scorm', 'uploads/certificates', 'uploads/logos', 'uploads/thumbnails'];
                foreach ($uploadDirs as $d) {
                    @mkdir(__DIR__ . '/' . $d, 0755, true);
                }
                $success[] = 'Yükleme dizinleri hazırlandı.';

                // 9. install.lock oluştur
                file_put_contents(__DIR__ . '/install.lock', date('Y-m-d H:i:s') . ' | ' . gethostname());

                // Başarılı kurulum verisini session'a kaydet
                $_SESSION['install_result'] = [
                    'admin_email' => $p['admin_email'],
                    'base_url'    => $p['base_url'] ?: '(Otomatik)',
                    'db_name'     => $dbName,
                    'success'     => $success,
                ];
                $installed = true;

            } catch (PDOException $e) {
                $errors[] = 'Veritabanı hatası: ' . $e->getMessage();
            } catch (Throwable $e) {
                $errors[] = 'Hata: ' . $e->getMessage();
            }
        }
    }
}

$reqs    = checkRequirements();
$hasErr  = hasFatalError($reqs);
$result  = $_SESSION['install_result'] ?? null;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>İSG LMS — Kurulum Sihirbazı</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root { --brand: #005695; --brand-dark: #003f6b; }
body  { background: #f0f4f8; font-family: 'Segoe UI', system-ui, sans-serif; }

.installer-wrap {
    max-width: 860px;
    margin: 40px auto;
    padding: 0 16px 60px;
}

.installer-header {
    background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand) 60%, #0088cc 100%);
    border-radius: 18px 18px 0 0;
    padding: 36px 40px 28px;
    color: #fff;
}
.installer-header .badge-version {
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 20px;
    padding: 3px 12px;
    font-size: .72rem;
    letter-spacing: .03em;
}

.installer-body {
    background: #fff;
    border-radius: 0 0 18px 18px;
    padding: 36px 40px;
    box-shadow: 0 4px 32px rgba(0,0,0,.08);
}

.section-title {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #8a9ab0;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e9ecef;
}

.req-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 4px;
    font-size: .85rem;
}
.req-ok   { background: #f0fdf4; }
.req-warn { background: #fffbeb; }
.req-err  { background: #fef2f2; }

.form-label { font-weight: 600; font-size: .875rem; }

.install-btn {
    background: linear-gradient(135deg, var(--brand) 0%, #0088cc 100%);
    border: none;
    border-radius: 10px;
    padding: 14px 36px;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: .02em;
    transition: opacity .15s;
}
.install-btn:hover { opacity: .9; color: #fff; }
.install-btn:disabled { opacity: .5; }

.success-card {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
    border-radius: 18px;
    color: #fff;
    padding: 40px;
}
.cred-box {
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 10px;
    padding: 16px 20px;
    font-family: monospace;
    font-size: .9rem;
}
.warn-box {
    background: #fef3c7;
    border: 2px solid #f59e0b;
    border-radius: 10px;
    padding: 16px 20px;
    color: #92400e;
    font-weight: 600;
}
.step-indicator {
    display: flex;
    gap: 8px;
    margin-bottom: 28px;
}
.step-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,.35);
}
.step-dot.active { background: #fff; }

@media (max-width: 600px) {
    .installer-header, .installer-body { padding: 24px 20px; }
}
</style>
</head>
<body>

<div class="installer-wrap">

<!-- Header -->
<div class="installer-header">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-shield-fill-check fs-3"></i>
                <h1 class="h3 fw-bold mb-0">İSG LMS</h1>
                <span class="badge-version">v<?= INSTALLER_VERSION ?></span>
            </div>
            <p class="mb-0 opacity-75">Türkiye İş Sağlığı ve Güvenliği Eğitim Platformu — Kurulum Sihirbazı</p>
        </div>
        <div class="text-end opacity-60" style="font-size:.75rem;line-height:1.7">
            PHP <?= PHP_VERSION ?><br>
            <?= PHP_OS_FAMILY ?> / <?= php_uname('m') ?>
        </div>
    </div>
</div>

<!-- Body -->
<div class="installer-body">

<?php if ($installed && !empty($_SESSION['install_result'])): $r = $_SESSION['install_result']; unset($_SESSION['install_result']); ?>

<!-- ═══ BAŞARI EKRANI ═══ -->
<div class="success-card mb-4">
    <div class="text-center mb-4">
        <div style="font-size:4rem">✅</div>
        <h2 class="fw-bold mt-2">Kurulum Tamamlandı!</h2>
        <p class="opacity-75">İSG LMS başarıyla kuruldu. Platforma giriş yapabilirsiniz.</p>
    </div>

    <div class="mb-3">
        <div class="section-title text-white opacity-60" style="font-size:.65rem">GİRİŞ BİLGİLERİ</div>
        <div class="cred-box mb-2">
            <div><span class="opacity-60">E-posta &nbsp;</span> <strong><?= htmlspecialchars($r['admin_email']) ?></strong></div>
            <div class="mt-1"><span class="opacity-60">Şifre &nbsp;&nbsp;&nbsp;</span> <em>(kurulum sırasında girdiğiniz şifre)</em></div>
            <div class="mt-1"><span class="opacity-60">Veritabanı </span> <strong><?= htmlspecialchars($r['db_name']) ?></strong></div>
        </div>
    </div>

    <div class="warn-box mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Güvenlik Uyarısı:</strong> Kurulumu tamamladınız. Lütfen
        <code>install.php</code> ve <code>install.lock</code> dosyalarını
        sunucudan hemen silin!
    </div>

    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="/" class="btn btn-light fw-bold px-4">
            <i class="bi bi-box-arrow-in-right me-2"></i>Platforma Git
        </a>
        <a href="/giris" class="btn btn-outline-light px-4">
            <i class="bi bi-person-fill me-2"></i>Admin Girişi
        </a>
    </div>
</div>

<div class="mb-3">
    <div class="section-title">KURULUM DETAYLARI</div>
    <?php foreach ($r['success'] as $s): ?>
    <div class="d-flex align-items-center gap-2 py-1 text-success" style="font-size:.84rem">
        <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($s) ?>
    </div>
    <?php endforeach; ?>
</div>

<?php elseif (!empty($errors)): ?>

<!-- ═══ HATA EKRANI ═══ -->
<div class="alert alert-danger border-0 rounded-3 mb-4">
    <div class="fw-bold mb-2"><i class="bi bi-x-octagon-fill me-2"></i>Kurulum Başarısız</div>
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<a href="install.php" class="btn btn-outline-danger">← Geri Dön ve Düzelt</a>

<?php else: ?>

<!-- ═══ KURULUM FORMU ═══ -->

<!-- Sistem Gereksinimleri -->
<div class="mb-4">
    <div class="section-title"><i class="bi bi-cpu me-1"></i>Sistem Gereksinimleri</div>

    <?php foreach ($reqs as $r):
        $cls = $r['ok'] ? 'req-ok' : (($r['required'] ?? true) ? 'req-err' : 'req-warn');
        $icon = $r['ok'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : (($r['required'] ?? true) ? '<i class="bi bi-x-circle-fill text-danger"></i>' : '<i class="bi bi-exclamation-circle-fill text-warning"></i>');
    ?>
    <div class="req-row <?= $cls ?>">
        <span><?= $icon ?> &nbsp;<?= htmlspecialchars($r['label']) ?></span>
        <span class="text-muted" style="font-size:.8rem"><?= htmlspecialchars($r['value']) ?></span>
    </div>
    <?php endforeach; ?>

    <?php if ($hasErr): ?>
    <div class="alert alert-danger mt-3 mb-0">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Zorunlu gereksinimler karşılanmıyor.</strong>
        Hosting sağlayıcınızla iletişime geçin ve eksik PHP eklentilerini etkinleştirin.
    </div>
    <?php else: ?>
    <div class="alert alert-success mt-3 mb-0">
        <i class="bi bi-check-circle-fill me-2"></i>
        Tüm zorunlu gereksinimler karşılanıyor. Kuruluma devam edebilirsiniz.
    </div>
    <?php endif; ?>
</div>

<?php if (!$hasErr): ?>

<form method="post" action="install.php" id="installForm" autocomplete="off">
<input type="hidden" name="action" value="install">

<!-- Veritabanı Ayarları -->
<div class="mb-4">
    <div class="section-title"><i class="bi bi-database me-1"></i>Veritabanı Ayarları</div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Veritabanı Sunucusu (Host)</label>
            <input type="text" class="form-control" name="db_host" value="localhost" required
                   placeholder="localhost veya 127.0.0.1">
            <div class="form-text">cPanel genelde <code>localhost</code> kullanır.</div>
        </div>
        <div class="col-md-2">
            <label class="form-label">Port</label>
            <input type="number" class="form-control" name="db_port" value="3306" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Veritabanı Adı</label>
            <input type="text" class="form-control" name="db_name" value="isg_lms" required
                   pattern="[a-zA-Z0-9_]+" title="Yalnızca harf, rakam ve alt çizgi">
            <div class="form-text">cPanel'de önce veritabanı oluşturun.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Veritabanı Kullanıcı Adı</label>
            <input type="text" class="form-control" name="db_user" required
                   placeholder="cPanel'deki MySQL kullanıcı adı">
        </div>
        <div class="col-md-6">
            <label class="form-label">Veritabanı Şifresi</label>
            <div class="input-group">
                <input type="password" class="form-control" name="db_pass" id="dbpass"
                       placeholder="MySQL şifresi">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('dbpass')">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="testDbBtn" onclick="testDb()">
            <i class="bi bi-plug me-1"></i>Bağlantıyı Test Et
        </button>
        <span id="testDbResult" class="ms-2 small"></span>
    </div>
</div>

<!-- Site Ayarları -->
<div class="mb-4">
    <div class="section-title"><i class="bi bi-globe me-1"></i>Site Ayarları</div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Platform Adı</label>
            <input type="text" class="form-control" name="app_name"
                   value="İSG Eğitim Platformu" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Site URL'si (opsiyonel)</label>
            <input type="url" class="form-control" name="base_url"
                   placeholder="https://isg.firmaniz.com"
                   value="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>">
            <div class="form-text">Boş bırakılırsa otomatik tespit edilir.</div>
        </div>
    </div>
</div>

<!-- Admin Hesabı -->
<div class="mb-4">
    <div class="section-title"><i class="bi bi-person-fill-gear me-1"></i>Süper Admin Hesabı</div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Ad</label>
            <input type="text" class="form-control" name="admin_first" required placeholder="Ad">
        </div>
        <div class="col-md-4">
            <label class="form-label">Soyad</label>
            <input type="text" class="form-control" name="admin_last" required placeholder="Soyad">
        </div>
        <div class="col-md-4">
            <label class="form-label">E-posta</label>
            <input type="email" class="form-control" name="admin_email" required
                   placeholder="admin@firmainiz.com">
        </div>
        <div class="col-md-6">
            <label class="form-label">Şifre <small class="text-muted fw-normal">(min. 8 karakter)</small></label>
            <div class="input-group">
                <input type="password" class="form-control" name="admin_pass" id="adminpass"
                       required minlength="8">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('adminpass')">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Şifre (Tekrar)</label>
            <div class="input-group">
                <input type="password" class="form-control" name="admin_pass2" id="adminpass2"
                       required minlength="8">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('adminpass2')">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Ek Seçenekler -->
<div class="mb-4">
    <div class="section-title"><i class="bi bi-sliders me-1"></i>Ek Seçenekler</div>
    <div class="form-check form-switch ps-0">
        <div class="d-flex align-items-start gap-3 p-3 border rounded-3">
            <input class="form-check-input mt-1 flex-shrink-0" type="checkbox" name="seed_demo"
                   id="seedDemo" style="width:2.5em;height:1.5em">
            <label class="form-check-label" for="seedDemo">
                <div class="fw-semibold">Demo Kurs Verilerini Yükle</div>
                <div class="text-muted" style="font-size:.82rem">
                    Örnek kurs paketleri ve kategorileri otomatik oluşturulsun.
                    Gerçek üretim kurulumunda işaretlememek önerilir.
                </div>
            </label>
        </div>
    </div>
</div>

<!-- Bilgilendirme -->
<div class="alert alert-info border-0 rounded-3 mb-4" style="font-size:.84rem">
    <i class="bi bi-info-circle-fill me-2"></i>
    <strong>Kurulum şunları yapar:</strong> Veritabanı tablolarını oluşturur,
    süper admin hesabını kaydeder, <code>config.php</code> dosyasını yazar
    ve <code>install.lock</code> oluşturarak kurulum sayfasını kilitler.
</div>

<!-- Kurulum Butonu -->
<div class="d-flex justify-content-between align-items-center">
    <div class="text-muted" style="font-size:.8rem">
        İSG LMS v<?= INSTALLER_VERSION ?> &bull; PHP <?= PHP_MAJOR_VERSION ?>.<?= PHP_MINOR_VERSION ?>
    </div>
    <button type="submit" class="install-btn btn" id="submitBtn">
        <i class="bi bi-rocket-takeoff me-2"></i>Kurulumu Başlat
    </button>
</div>

</form>

<?php endif; // !$hasErr ?>
<?php endif; // !$installed && !errors ?>

</div><!-- /installer-body -->

<!-- cPanel Rehberi -->
<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold text-dark border-0 pt-3">
        <i class="bi bi-question-circle-fill text-primary me-2"></i>cPanel / Plesk Kurulum Rehberi
    </div>
    <div class="card-body text-muted" style="font-size:.85rem">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-bold text-dark">📁 Dosyaları Yükleme</h6>
                <ol class="mb-0">
                    <li>ZIP dosyasını bilgisayarınıza indirin</li>
                    <li>cPanel → <strong>Dosya Yöneticisi</strong> → <code>public_html/</code></li>
                    <li>ZIP'i bu dizine yükleyin ve açın (Extract)</li>
                    <li>Tüm dosyalar <code>public_html/</code> içinde olmalı</li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-dark">🗄️ Veritabanı Hazırlama</h6>
                <ol class="mb-0">
                    <li>cPanel → <strong>MySQL Veritabanları</strong></li>
                    <li>Yeni veritabanı oluşturun: <code>isg_lms</code></li>
                    <li>Yeni MySQL kullanıcısı oluşturun</li>
                    <li>Kullanıcıyı veritabanına ekleyin (<strong>Tüm Yetkiler</strong>)</li>
                    <li>Bu sayfadaki formu doldurun ve "Kurulumu Başlat"a tıklayın</li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-dark">🔒 Kurulum Sonrası</h6>
                <ul class="mb-0">
                    <li><code>install.php</code> dosyasını silin</li>
                    <li><code>install.lock</code> dosyasını silin</li>
                    <li><code>db/</code> klasörünü silin veya koruyun (yedek)</li>
                    <li><code>uploads/</code> dizinine 755 izni verin</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-dark">⚡ Composer (Opsiyonel)</h6>
                <p class="mb-1">Vendor klasörü ZIP içinde hazır gelir. Yeniden yüklemek isterseniz:</p>
                <code class="d-block bg-light p-2 rounded" style="font-size:.78rem">
                    composer install --no-dev --optimize-autoloader
                </code>
                <p class="mt-1 mb-0">PHP 8.1+ ve Composer gerektirir.</p>
            </div>
        </div>
    </div>
</div>

</div><!-- /installer-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id) {
    var el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

function testDb() {
    var btn = document.getElementById('testDbBtn');
    var res = document.getElementById('testDbResult');
    var host   = document.querySelector('[name=db_host]').value;
    var port   = document.querySelector('[name=db_port]').value;
    var user   = document.querySelector('[name=db_user]').value;
    var pass   = document.querySelector('[name=db_pass]').value;
    var dbname = document.querySelector('[name=db_name]').value;

    btn.disabled = true;
    res.innerHTML = '<span class="text-muted">Test ediliyor…</span>';

    fetch('install.php?test_db=1', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({host: host, port: port, user: user, pass: pass, dbname: dbname})
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.ok) {
            res.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> ' + d.msg + '</span>';
        } else {
            res.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ' + d.msg + '</span>';
        }
    })
    .catch(function() { res.innerHTML = '<span class="text-danger">İstek başarısız.</span>'; })
    .finally(function() { btn.disabled = false; });
}

document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('installForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var p1 = document.getElementById('adminpass');
        var p2 = document.getElementById('adminpass2');
        if (p1 && p2 && p1.value !== p2.value) {
            e.preventDefault();
            p2.setCustomValidity('Şifreler eşleşmiyor.');
            p2.reportValidity();
            return;
        }
        if (p2) p2.setCustomValidity('');
        var btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kuruluyor…';
        }
    });
});
</script>
</body>
</html>
