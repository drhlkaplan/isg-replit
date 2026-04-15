<?php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'isg_lms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_SOCKET', '/home/runner/mysql_run/mysql.sock');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'İSG Eğitim Platformu');
define('APP_VERSION', '1.0.0');
define('APP_LANG', 'tr');

// Dynamically resolve public base URL — works on Replit proxy AND localhost
(function() {
    $host = $_SERVER['HTTP_HOST'] ?? (getenv('REPLIT_DEV_DOMAIN') ?: 'localhost:5000');
    // Use HTTPS when behind Replit's proxy (X-Forwarded-Proto) or when host looks like a cloud domain
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (strpos($host, 'replit.dev') !== false)
        || (strpos($host, 'replit.app') !== false);
    $scheme = $isSecure ? 'https' : 'http';
    define('APP_URL', $scheme . '://' . $host);
})();

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('SCORM_DIR', __DIR__ . '/uploads/scorm/');
define('CERT_DIR', __DIR__ . '/uploads/certificates/');
define('THUMB_DIR', __DIR__ . '/uploads/thumbnails/');
define('LOGO_DIR', __DIR__ . '/uploads/logos/');

define('SCORM_URL', APP_URL . '/uploads/scorm/');
define('CERT_URL', APP_URL . '/uploads/certificates/');
define('LOGO_URL', APP_URL . '/uploads/logos/');

define('SESSION_LIFETIME', 7200);
define('BCRYPT_COST', 12);

date_default_timezone_set('Europe/Istanbul');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
