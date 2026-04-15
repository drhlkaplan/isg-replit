<?php
$pageTitle = 'Kurslar — ' . APP_NAME;
include __DIR__ . '/../_layout.php';

// Build hierarchical structure: group packages by training_type then category
$typeLabels = ['temel' => 'İSG Temel Eğitim', 'tekrar' => 'İSG Tekrar Eğitim'];
$typeIcons  = ['temel' => 'bi-book-fill text-primary', 'tekrar' => 'bi-arrow-repeat text-warning'];
$topicLabels = [
    'on_degerlendirme' => 'Ön Değerlendirme',
    'genel'            => 'Genel Konular',
    'saglik'           => 'Sağlık Konuları',
    'teknik'           => 'Teknik Konular',
    'ise_ozgu'         => 'İşe ve İşyerine Özgü',
    'final_sinav'      => 'Final Sınavı',
    'paket'            => 'Paket',
];
$topicIcons = [
    'on_degerlendirme' => 'bi-clipboard-check text-secondary',
    'genel'            => 'bi-journal-text text-info',
    'saglik'           => 'bi-heart-pulse text-danger',
    'teknik'           => 'bi-wrench-adjustable text-warning',
    'ise_ozgu'         => 'bi-building text-success',
    'final_sinav'      => 'bi-check-circle-fill text-success',
    'paket'            => 'bi-box-seam text-primary',
];
$deliveryLabels = ['online' => 'Online', 'yuz_yuze' => 'Yüz Yüze', 'hibrit' => 'Hibrit'];
$deliveryBadge  = ['online' => 'bg-info', 'yuz_yuze' => 'bg-warning text-dark', 'hibrit' => 'bg-secondary'];
$statusMap      = ['active' => 'success', 'draft' => 'warning', 'archived' => 'secondary'];
$statusLabel    = ['active' => 'Aktif', 'draft' => 'Taslak', 'archived' => 'Arşiv'];

// Separate packages from modules
$packages = [];
$modulesByParent = [];
$standalone = [];
foreach ($courses as $c) {
    if ($c['topic_type'] === 'paket') {
        $packages[$c['id']] = $c;
    } elseif ($c['parent_course_id']) {
        $modulesByParent[$c['parent_course_id']][] = $c;
    } else {
        $standalone[] = $c;
    }
}

// Group packages by training_type → category
$grouped = [];
foreach ($packages as $p) {
    $tt = $p['training_type'] ?? 'other';
    $cat = $p['category_name'];
    $grouped[$tt][$cat][] = $p;
}
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-book me-2 text-success"></i>Kurs Yönetimi</h4>
        <a href="/admin/kurslar/ekle" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Yeni Kurs / Modül</a>
    </div>

    <?php if (!empty($flash['message'])): ?>
    <div class="alert alert-<?= $flash['type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php foreach (['temel','tekrar'] as $trainingType): ?>
    <?php if (empty($grouped[$trainingType])): continue; endif; ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3" style="background:<?= $trainingType === 'temel' ? '#e8f0fe' : '#fff8e1' ?>;border-left:4px solid <?= $trainingType === 'temel' ? '#1a73e8' : '#f9a825' ?>">
            <h5 class="mb-0 fw-bold">
                <i class="bi <?= $typeIcons[$trainingType] ?> me-2"></i>
                <?= $typeLabels[$trainingType] ?>
                <small class="text-muted fw-normal ms-2 fs-6">
                    <?= count($grouped[$trainingType]) ?> tehlike sınıfı
                </small>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php $catColors = ['Az Tehlikeli' => '#28a745', 'Tehlikeli' => '#ffc107', 'Çok Tehlikeli' => '#dc3545']; ?>
            <?php foreach ($grouped[$trainingType] as $catName => $catPackages): ?>
            <?php $catColor = $catColors[$catName] ?? '#6c757d'; ?>
            <?php foreach ($catPackages as $pkg): ?>
            <?php
                $pkgModules = $modulesByParent[$pkg['id']] ?? [];
                usort($pkgModules, fn($a,$b) => $a['sort_order'] - $b['sort_order']);
                $variant = ($pkg['workplace_variant'] && $pkg['workplace_variant'] !== 'genel')
                    ? ' <span class="badge bg-secondary ms-1 small">' . htmlspecialchars($pkg['workplace_variant']) . '</span>' : '';
            ?>
            <div class="border-bottom">
                <!-- Package row -->
                <div class="d-flex align-items-center px-3 py-2 bg-light" style="border-left:4px solid <?= $catColor ?>">
                    <button class="btn btn-sm btn-outline-secondary me-2 py-0 px-1"
                            data-bs-toggle="collapse" data-bs-target="#pkg<?= $pkg['id'] ?>" aria-expanded="false">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <i class="bi bi-box-seam me-2" style="color:<?= $catColor ?>"></i>
                    <strong class="me-2 flex-grow-1"><?= htmlspecialchars($pkg['title']) ?></strong>
                    <?= $variant ?>
                    <span class="badge me-2" style="background:<?= $catColor ?>"><?= htmlspecialchars($catName) ?></span>
                    <span class="badge bg-light text-dark border me-2"><i class="bi bi-clock me-1"></i><?= round($pkg['duration_minutes']/45) ?> ders saati</span>
                    <span class="badge bg-<?= $statusMap[$pkg['status']] ?? 'secondary' ?> me-2"><?= $statusLabel[$pkg['status']] ?? $pkg['status'] ?></span>
                    <span class="badge bg-secondary me-3"><?= $pkg['student_count'] ?? 0 ?> öğrenci</span>
                    <div class="d-flex gap-1 ms-auto">
                        <a href="/admin/kurslar/kayitlar/<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-info" title="Kayıtlar"><i class="bi bi-people"></i></a>
                        <a href="/admin/kurslar/duzenle/<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-primary" title="Düzenle"><i class="bi bi-pencil"></i></a>
                        <a href="/admin/kurslar/sil/<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Paketi ve tüm içeriklerini silmek istediğinizden emin misiniz?')" title="Sil"><i class="bi bi-trash"></i></a>
                    </div>
                </div>
                <!-- Module rows (collapsible) -->
                <div class="collapse" id="pkg<?= $pkg['id'] ?>">
                    <?php if (empty($pkgModules)): ?>
                    <div class="text-muted small ps-5 py-2">Henüz modül eklenmemiş.</div>
                    <?php endif; ?>
                    <?php foreach ($pkgModules as $mod): ?>
                    <?php
                        $topicIcon  = $topicIcons[$mod['topic_type']] ?? 'bi-dot';
                        $topicLabel = $topicLabels[$mod['topic_type']] ?? $mod['topic_type'];
                        $delBadgeClass = $deliveryBadge[$mod['delivery_method']] ?? 'bg-light text-dark';
                        $delLabel = $deliveryLabels[$mod['delivery_method']] ?? $mod['delivery_method'];
                        $isYuzYuze = $mod['delivery_method'] === 'yuz_yuze';
                    ?>
                    <div class="d-flex align-items-center ps-5 pe-3 py-1 border-top border-light <?= $isYuzYuze ? 'bg-warning bg-opacity-10' : '' ?>">
                        <i class="bi <?= $topicIcon ?> me-2 flex-shrink-0"></i>
                        <span class="me-2 flex-grow-1 small"><?= htmlspecialchars($mod['title']) ?></span>
                        <span class="badge <?= $delBadgeClass ?> me-2 small"><?= $delLabel ?></span>
                        <?php if ($isYuzYuze): ?>
                        <span class="badge bg-warning text-dark me-2 small"><i class="bi bi-exclamation-triangle me-1"></i>Zorunlu Yüz Yüze</span>
                        <?php endif; ?>
                        <span class="text-muted small me-3"><?= $mod['duration_minutes'] ?> dk</span>
                        <span class="badge bg-<?= $statusMap[$mod['status']] ?? 'secondary' ?> me-2 small"><?= $statusLabel[$mod['status']] ?? '' ?></span>
                        <span class="badge bg-secondary me-2 small"><?= $mod['student_count'] ?? 0 ?></span>
                        <div class="d-flex gap-1">
                            <a href="/admin/kurslar/duzenle/<?= $mod['id'] ?>" class="btn btn-xs py-0 px-1 btn-outline-primary" title="Düzenle"><i class="bi bi-pencil" style="font-size:11px"></i></a>
                            <a href="/admin/kurslar/sil/<?= $mod['id'] ?>" class="btn btn-xs py-0 px-1 btn-outline-danger"
                               onclick="return confirm('Modülü silmek istediğinizden emin misiniz?')" title="Sil"><i class="bi bi-trash" style="font-size:11px"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($standalone)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header py-2 bg-light">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-grid me-2"></i>Diğer Kurslar</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr><th>Kurs Adı</th><th>Tehlike Sınıfı</th><th>Süre</th><th>Öğrenci</th><th>SCORM</th><th>Durum</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                <?php foreach ($standalone as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($c['title']) ?></td>
                    <td><span class="badge" style="background:<?= htmlspecialchars($c['category_color']) ?>"><?= htmlspecialchars($c['category_name']) ?></span></td>
                    <td><?= $c['duration_minutes'] ?> dk</td>
                    <td><span class="badge bg-secondary"><?= $c['student_count'] ?></span></td>
                    <td><?= $c['has_scorm'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' ?></td>
                    <td><span class="badge bg-<?= $statusMap[$c['status']] ?? 'secondary' ?>"><?= $statusLabel[$c['status']] ?? '' ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/admin/kurslar/kayitlar/<?= $c['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-people"></i></a>
                            <a href="/admin/kurslar/duzenle/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="/admin/kurslar/sil/<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Silmek istediğinizden emin misiniz?')"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($packages) && empty($standalone)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-book fs-1 d-block mb-3"></i>
        Henüz kurs eklenmemiş.
    </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
