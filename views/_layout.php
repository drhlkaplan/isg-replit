<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/isg.css" rel="stylesheet">
    <?php
        $currentRole = $_SESSION['role'] ?? '';
        $brandingFirm = null;
        $brandingFirmId = $_SESSION['user']['firm_id'] ?? 0;
        if ($brandingFirmId) {
            $brandingFirm = (\ISG\DB::getInstance())->fetch('SELECT * FROM firms WHERE id = ?', [$brandingFirmId]);
        }
        if ($brandingFirm && ($brandingFirm['primary_color'] || $brandingFirm['secondary_color'])):
    ?>
    <style>
    :root {
        --isg-primary: <?= htmlspecialchars($brandingFirm['primary_color'] ?: '#005695') ?>;
        --isg-secondary: <?= htmlspecialchars($brandingFirm['secondary_color'] ?: '#0072b5') ?>;
    }
    </style>
    <?php endif; ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">

<?php $user = $_SESSION['user'] ?? null; ?>
<?php if ($user): ?>
<nav class="navbar navbar-expand-lg navbar-dark isg-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/">
            <?php if (!empty($brandingFirm) && !empty($brandingFirm['logo_path']) && file_exists(LOGO_DIR . $brandingFirm['logo_path'])): ?>
            <img src="<?= LOGO_URL . htmlspecialchars($brandingFirm['logo_path']) ?>"
                 alt="<?= htmlspecialchars($brandingFirm['name']) ?>" style="max-height:36px;max-width:160px" class="me-1">
            <?php else: ?>
            <i class="bi bi-shield-check me-2"></i><?= !empty($brandingFirm) ? htmlspecialchars($brandingFirm['header_title'] ?: $brandingFirm['name']) : APP_NAME ?>
            <?php endif; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <?php $currentRole = $_SESSION['role'] ?? ''; ?>
                <?php if (in_array($currentRole, ['admin','superadmin'])): ?>
                <li class="nav-item"><a class="nav-link" href="/admin"><i class="bi bi-speedometer2 me-1"></i>Panel</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/kurslar"><i class="bi bi-book me-1"></i>Kurslar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/kullanicilar"><i class="bi bi-people me-1"></i>Kullanıcılar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/firmalar"><i class="bi bi-building me-1"></i>Firmalar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/grup-anahtarlari"><i class="bi bi-key me-1"></i>Grup Anahtarları</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/sinavlar"><i class="bi bi-clipboard-check me-1"></i>Sınavlar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/raporlar"><i class="bi bi-bar-chart me-1"></i>Raporlar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/yyz"><i class="bi bi-person-video2 me-1"></i>Yüz Yüze</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/sertifikalar"><i class="bi bi-award me-1"></i>Sertifikalar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/denetim-gunlugu"><i class="bi bi-journal-text me-1"></i>Denetim</a></li>
                <?php if ($currentRole === 'superadmin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-gear me-1"></i>Sistem</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/sistem/bakim"><i class="bi bi-tools me-2"></i>Bakım Modu</a></li>
                        <li><a class="dropdown-item" href="/admin/sistem/yedek"><i class="bi bi-download me-2"></i>Veritabanı Yedekle</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php elseif ($currentRole === 'egitmen'): ?>
                <li class="nav-item"><a class="nav-link" href="/admin"><i class="bi bi-speedometer2 me-1"></i>Panel</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/kurslar"><i class="bi bi-book me-1"></i>Kurslar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/sinavlar"><i class="bi bi-clipboard-check me-1"></i>Sınavlar</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/raporlar"><i class="bi bi-bar-chart me-1"></i>Raporlar</a></li>
                <?php elseif ($currentRole === 'firm'): ?>
                <li class="nav-item"><a class="nav-link" href="/firma"><i class="bi bi-building me-1"></i>Firma Paneli</a></li>
                <li class="nav-item"><a class="nav-link" href="/firma/calisanlar"><i class="bi bi-people me-1"></i>Çalışanlar</a></li>
                <li class="nav-item"><a class="nav-link" href="/firma/raporlar"><i class="bi bi-bar-chart me-1"></i>Raporlar</a></li>
                <li class="nav-item"><a class="nav-link" href="/firma/profil"><i class="bi bi-pencil-square me-1"></i>Profil & Tema</a></li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="/ogrenci"><i class="bi bi-house me-1"></i>Eğitimlerim</a></li>
                <li class="nav-item"><a class="nav-link" href="/ogrenci/yyz"><i class="bi bi-person-video2 me-1"></i>Yüz Yüze</a></li>
                <li class="nav-item"><a class="nav-link" href="/ogrenci/sertifikalar"><i class="bi bi-award me-1"></i>Sertifikalarım</a></li>
                <li class="nav-item"><a class="nav-link" href="/ogrenci/profil"><i class="bi bi-person-circle me-1"></i>Profilim</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php $isRoleSwitched = !empty($_SESSION['original_role']); ?>
                <?php $isOrigAdmin = in_array($_SESSION['original_role'] ?? '', ['admin','superadmin']); ?>
                <?php if ($isRoleSwitched && $isOrigAdmin): ?>
                <li class="nav-item">
                    <a href="/rol-geri-don" class="btn btn-warning btn-sm my-auto ms-2 fw-semibold">
                        <i class="bi bi-arrow-left-circle me-1"></i>Yönetim Moduna Dön
                    </a>
                </li>
                <?php elseif (in_array($_SESSION['role'] ?? '', ['admin','superadmin'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-shuffle me-1"></i>Rol Değiştir
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Görünüm Değiştir</h6></li>
                        <li>
                            <a class="dropdown-item" href="/admin/rol-degistir/ogrenci">
                                <i class="bi bi-mortarboard me-2 text-primary"></i>Öğrenci Olarak Görüntüle
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/admin/rol-degistir/egitmen">
                                <i class="bi bi-person-video3 me-2 text-success"></i>Eğitmen Olarak Görüntüle
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars($_SESSION['role'] ?? $user['role']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (!in_array($_SESSION['role'] ?? '', ['admin','superadmin','egitmen','firm'])): ?>
                        <li><a class="dropdown-item" href="/ogrenci/profil"><i class="bi bi-person me-2"></i>Profilim</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="/cikis"><i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php if (!empty($flash['msg'])): ?>
<div class="container-fluid mt-2 px-4">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] ?? 'success') ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
<?php if (!empty($_SESSION['original_role']) && in_array($_SESSION['original_role'] ?? '', ['admin','superadmin'])): ?>
<div class="alert alert-warning mb-0 rounded-0 border-0 text-center py-2" style="background:#fff3cd;border-bottom:2px solid #ffc107!important;">
    <i class="bi bi-eye me-1"></i>
    <strong>Görünüm Modu:</strong>
    <?php
        $viewingAs = match($_SESSION['role'] ?? '') {
            'student' => 'Öğrenci',
            'egitmen' => 'Eğitmen',
            default   => ucfirst($_SESSION['role'] ?? '')
        };
    ?>
    <span class="badge bg-warning text-dark mx-1"><?= $viewingAs ?></span> rolüyle görüntülüyorsunuz.
    <a href="/rol-geri-don" class="btn btn-sm btn-dark ms-2 py-0">
        <i class="bi bi-arrow-left-circle me-1"></i>Yönetim Moduna Dön
    </a>
</div>
<?php endif; ?>

<?php if (!empty($brandingFirm['announcement'])): ?>
<div class="alert alert-<?= htmlspecialchars($brandingFirm['announcement_type'] ?: 'info') ?> mb-0 rounded-0 border-0 text-center py-2 small fw-semibold" role="alert">
    <i class="bi bi-megaphone me-1"></i><?= htmlspecialchars($brandingFirm['announcement']) ?>
</div>
<?php endif; ?>

<main class="<?= isset($user) ? 'py-4' : '' ?>">
<?php // Main content from view ?>
