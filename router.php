<?php
/**
 * ISG LMS PHP Built-in Server Router
 * Routes static files directly; passes everything else to index.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files from assets/ and uploads/ directly
$staticDirs = ['/assets/', '/uploads/'];
foreach ($staticDirs as $dir) {
    if (str_starts_with($uri, $dir)) {
        $filePath = __DIR__ . $uri;
        if (file_exists($filePath) && is_file($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimes = [
                'css'  => 'text/css',
                'js'   => 'application/javascript',
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'svg'  => 'image/svg+xml',
                'ico'  => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2'=> 'font/woff2',
                'ttf'  => 'font/ttf',
                'eot'  => 'application/vnd.ms-fontobject',
                'otf'  => 'font/otf',
                'pdf'  => 'application/pdf',
                'html' => 'text/html; charset=utf-8',
                'htm'  => 'text/html; charset=utf-8',
                'xhtml'=> 'application/xhtml+xml',
                'swf'  => 'application/x-shockwave-flash',
                'xml'  => 'application/xml',
                'json' => 'application/json',
                'mp4'  => 'video/mp4',
                'webm' => 'video/webm',
                'ogv'  => 'video/ogg',
                'mp3'  => 'audio/mpeg',
                'ogg'  => 'audio/ogg',
                'wav'  => 'audio/wav',
                'zip'  => 'application/zip',
                'txt'  => 'text/plain',
            ];
            if (isset($mimes[$ext])) {
                header('Content-Type: ' . $mimes[$ext]);
            }
            return false; // serve directly
        }
    }
}

// Everything else → index.php
require __DIR__ . '/index.php';
