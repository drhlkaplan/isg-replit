<?php
$pageTitle = 'Eğitim Paketleri — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-grid me-2 text-primary"></i>Eğitim Paketleri</h4>
            <p class="text-muted small mb-0">Kayıt olabileceğiniz İSG eğitim paketleri</p>
        </div>
    </div>

    <form class="row g-3 mb-4" method="GET" action="/ogrenci/katalog">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="ara" class="form-control" placeholder="Paket ara..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="kategori" class="form-select">
                <option value="">Tüm Tehlike Sınıfları</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrele</button>
        </div>
    </form>

    <?php if (empty($courses)): ?>
    <div class="isg-empty-state">
        <i class="bi bi-search"></i>
        <h5>Eğitim paketi bulunamadı</h5>
        <p class="text-muted">Farklı filtreler deneyin.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($courses as $c): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 isg-course-card shadow-sm <?= $c['is_enrolled'] ? 'border-success border-2' : '' ?>">
                <div class="card-header" style="background:<?= htmlspecialchars($c['category_color']) ?>20;border-top:3px solid <?= htmlspecialchars($c['category_color']) ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge me-1" style="background:<?= htmlspecialchars($c['category_color']) ?>"><?= htmlspecialchars($c['category_name']) ?></span>
                            <span class="badge bg-secondary"><?= htmlspecialchars($c['category_code']) ?></span>
                        </div>
                        <?php if ($c['is_enrolled']): ?>
                        <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Kayıtlı</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold"><?= htmlspecialchars($c['title']) ?></h6>
                    <p class="text-muted small mb-2"><?= htmlspecialchars(substr($c['description'] ?? '', 0, 110)) ?><?= strlen($c['description'] ?? '') > 110 ? '...' : '' ?></p>
                    <div class="d-flex gap-3 flex-wrap">
                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= ceil($c['duration_minutes'] / 60) ?> Saat</small>
                        <?php if ($c['module_count'] > 0): ?>
                        <small class="text-muted"><i class="bi bi-collection me-1"></i><?= $c['module_count'] ?> Modül</small>
                        <?php endif; ?>
                        <?php if ($c['training_type']): ?>
                        <small class="text-muted"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($c['training_type']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <?php if ($c['is_enrolled']): ?>
                    <a href="/ogrenci/kurs/<?= $c['id'] ?>" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-play-circle me-1"></i>Eğitime Devam Et
                    </a>
                    <?php else: ?>
                    <form method="POST" action="/ogrenci/kayit">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-plus-circle me-1"></i>Pakete Kaydol
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
