<?php
$pageTitle = 'Sertifikalarım — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-award me-2 text-warning"></i>Sertifikalarım</h4>
    <?php if (empty($certs)): ?>
    <div class="isg-empty-state">
        <i class="bi bi-award"></i>
        <h5>Henüz sertifikanız yok</h5>
        <p class="text-muted">Kursları tamamlayın ve sınavları geçin, sertifikanızı kazanın.</p>
        <a href="/ogrenci/profil" class="btn btn-primary">Grup Anahtarı Kullan</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($certs as $c): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm border-0 isg-cert-card">
                <div class="card-body text-center p-4">
                    <div class="isg-cert-icon mb-3">
                        <i class="bi bi-patch-check-fill text-warning fs-1"></i>
                    </div>
                    <h6 class="fw-bold"><?= htmlspecialchars($c['title']) ?></h6>
                    <span class="badge mb-2" style="background:<?= htmlspecialchars($c['category_color']) ?>"><?= htmlspecialchars($c['category_name']) ?></span>
                    <div class="small text-muted mb-1">
                        <i class="bi bi-hash"></i> <?= htmlspecialchars($c['cert_number']) ?>
                    </div>
                    <div class="small text-muted mb-3">
                        <i class="bi bi-calendar me-1"></i><?= date('d.m.Y', strtotime($c['issued_at'])) ?>
                        <?php if ($c['expires_at']): ?>
                        — <i class="bi bi-calendar-x me-1"></i><?= date('d.m.Y', strtotime($c['expires_at'])) ?>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/sertifika/indir/<?= htmlspecialchars($c['cert_number']) ?>" class="btn btn-success btn-sm flex-fill">
                            <i class="bi bi-download me-1"></i>İndir
                        </a>
                        <a href="/dogrula/<?= htmlspecialchars($c['cert_number']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm flex-fill">
                            <i class="bi bi-qr-code me-1"></i>Doğrula
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
