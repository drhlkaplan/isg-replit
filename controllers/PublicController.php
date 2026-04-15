<?php
use ISG\Certificate;

$certNumber = ltrim(str_replace('/dogrula', '', $uri), '/') ?: '';
$certNumber = urldecode($certNumber);
if (!$certNumber) {
    $certNumber = trim($_GET['certNumber'] ?? '');
}

$certObj = new Certificate();
$cert = $certNumber ? $certObj->verify($certNumber) : null;

view('public/verify', compact('cert', 'certNumber'));
