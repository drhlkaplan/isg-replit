<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bakım Modu — <?= defined('APP_NAME') ? APP_NAME : 'İSG LMS' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 100vh; }
.maint-card { max-width: 520px; margin: 0 auto; }
</style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="maint-card text-center p-4">
    <div class="mb-4">
        <i class="bi bi-tools" style="font-size:4rem;color:#e67e22"></i>
    </div>
    <h2 class="fw-bold mb-2">Sistem Bakımda</h2>
    <p class="text-muted lead">Platform geçici olarak bakım çalışmaları nedeniyle kullanılamıyor.</p>
    <?php if (!empty($maintenanceSince)): ?>
    <p class="text-muted small">Bakım başlangıcı: <?= htmlspecialchars($maintenanceSince) ?></p>
    <?php endif; ?>
    <hr>
    <p class="text-muted small mb-0">Kısa süre içinde geri döneceğiz. Anlayışınız için teşekkür ederiz.</p>
    <p class="mt-3"><a href="/giris" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Giriş Sayfasına Dön</a></p>
</div>
</body>
</html>
