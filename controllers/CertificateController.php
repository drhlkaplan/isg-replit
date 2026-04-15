<?php
use ISG\Auth;
use ISG\Certificate;

$auth = new Auth();
$userId = $auth->id();

$segments = explode('/', ltrim($uri, '/'));
$action = $segments[1] ?? '';
$certId = $segments[2] ?? '';

match($action) {
    'indir' => (function() use ($db, $userId, $certId, $auth) {
        $isAdmin = in_array($_SESSION['role'] ?? '', ['admin', 'superadmin']);
        if ($isAdmin) {
            $cert = $db->fetch('SELECT * FROM certificates WHERE cert_number = ?', [$certId]);
        } else {
            $cert = $db->fetch(
                'SELECT * FROM certificates WHERE cert_number = ? AND user_id = ?',
                [$certId, $userId]
            );
        }
        if (!$cert) {
            redirect('/ogrenci/sertifikalar', 'Sertifika bulunamadı.', 'error');
        }
        $pdfPath = CERT_DIR . $cert['pdf_path'];
        if (!file_exists($pdfPath) || !$cert['pdf_path']) {
            $certObj = new Certificate();
            $newFilename = $certObj->regeneratePdf($cert);
            $pdfPath = CERT_DIR . $newFilename;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $certId . '.pdf"');
        header('Content-Length: ' . filesize($pdfPath));
        readfile($pdfPath);
        exit;
    })(),

    'goruntule' => (function() use ($db, $userId, $certId) {
        $cert = $db->fetch('SELECT * FROM certificates WHERE cert_number = ? AND user_id = ?', [$certId, $userId]);
        if (!$cert) redirect('/ogrenci/sertifikalar', 'Sertifika bulunamadı.', 'error');
        $pdfPath = CERT_DIR . $cert['pdf_path'];
        if (!file_exists($pdfPath) || !$cert['pdf_path']) {
            $certObj = new Certificate();
            $newFilename = $certObj->regeneratePdf($cert);
            $pdfPath = CERT_DIR . $newFilename;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $certId . '.pdf"');
        readfile($pdfPath);
        exit;
    })(),

    default => redirect('/ogrenci/sertifikalar'),
};
