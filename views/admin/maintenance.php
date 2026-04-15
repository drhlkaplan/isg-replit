<?php
$pageTitle = 'Bakım Modu — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-tools me-2 text-warning"></i>Bakım Modu</h4>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success') ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <?php if ($isMaintenanceOn): ?>
                        <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Bakım Modu AKTİF</span>
                        <?php else: ?>
                        <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle-fill me-2"></i>Sistem Normal</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted">Bakım modu açıkken, Süper Admin ve Admin rolündeki kullanıcılar dışındaki herkese <strong>503 Servis Dışı</strong> sayfası gösterilir.</p>

                    <?php if ($isMaintenanceOn): ?>
                    <form method="POST" action="/admin/sistem/bakim">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="toggle" value="off">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-2"></i>Bakım Modunu Kapat
                        </button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="/admin/sistem/bakim">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="toggle" value="on">
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Bakım modu aktif edilsin mi? Tüm öğrenciler platforma erişemeyecek.')">
                            <i class="bi bi-tools me-2"></i>Bakım Modunu Aç
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle text-info me-2"></i>Bakım Sayfasını Önizle</h6>
                    <p class="text-muted small mb-2">Bakım modu aktifken öğrenciler oturum açamaz ve aşağıdaki mesajı görür. Önizlemek için gizli/inkognito pencerede siteyi açın.</p>
                    <div class="alert alert-light border small rounded mb-0">
                        <i class="bi bi-tools me-1 text-warning"></i>
                        <strong>Sistem Bakımda</strong> — Sistemimiz şu anda bakım modundadır. Lütfen daha sonra tekrar deneyiniz.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mt-3 mt-lg-0">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Önemli Notlar</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2"><i class="bi bi-dot"></i>Bakım modunu açmadan önce kullanıcıları bilgilendirin.</li>
                        <li class="mb-2"><i class="bi bi-dot"></i>Admin ve Süper Admin rolleri bakım modundan etkilenmez.</li>
                        <li class="mb-2"><i class="bi bi-dot"></i>Bakım işlemi bittikten sonra modu hemen kapatın.</li>
                        <li><i class="bi bi-dot"></i>Bakım modu açma/kapama işlemleri denetim günlüğüne kaydedilir.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
