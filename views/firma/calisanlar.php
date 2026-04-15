<?php
$pageTitle = 'Çalışanlar — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Çalışanlar</h4>
            <p class="text-muted small mb-0"><?= htmlspecialchars($firm['name']) ?> — Tüm Çalışanlar</p>
        </div>
        <a href="/firma" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Panele Dön</a>
    </div>

    <form class="row g-3 mb-4" method="GET" action="/firma/calisanlar">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="ara" class="form-control" placeholder="Ad, soyad, e-posta, TC ara..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="durum" class="form-select">
                <option value="">Tüm Durumlar</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="passive" <?= $statusFilter === 'passive' ? 'selected' : '' ?>>Pasif</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filtrele</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Çalışan Listesi</span>
            <span class="badge bg-primary"><?= count($employees) ?> kişi</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta / TC</th>
                        <th>Kayıtlı</th>
                        <th>Tamamlanan</th>
                        <th>Devam</th>
                        <th>Başarısız</th>
                        <th>Sertifika</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($emp['phone'] ?: '—') ?></small>
                    </td>
                    <td>
                        <div class="small"><?= htmlspecialchars($emp['email']) ?></div>
                        <small class="text-muted font-monospace"><?= htmlspecialchars($emp['tc_identity_no'] ?: '—') ?></small>
                    </td>
                    <td><span class="badge bg-info"><?= $emp['enrolled_count'] ?></span></td>
                    <td><span class="badge bg-success"><?= $emp['completed_count'] ?></span></td>
                    <td><span class="badge bg-warning text-dark"><?= $emp['inprogress_count'] ?></span></td>
                    <td><span class="badge <?= $emp['failed_count'] > 0 ? 'bg-danger' : 'bg-light text-muted' ?>"><?= $emp['failed_count'] ?></span></td>
                    <td><span class="badge bg-secondary"><?= $emp['cert_count'] ?></span></td>
                    <td>
                        <span class="badge <?= $emp['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $emp['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($employees)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-2 d-block mb-2"></i>Sonuç bulunamadı.
                </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
