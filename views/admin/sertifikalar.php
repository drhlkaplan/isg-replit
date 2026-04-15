<?php
$pageTitle = 'Sertifika Yönetimi — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
$certs      = $certs ?? [];
$firms      = $firms ?? [];
$users      = $users ?? [];
$courses    = $courses ?? [];
$search     = $search ?? '';
$firmFilter = $firmFilter ?? 0;
$validFilter = $validFilter ?? '';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-award me-2 text-warning"></i>Sertifika Yönetimi</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCertModal">
            <i class="bi bi-plus-circle me-1"></i>Manuel Sertifika Ekle
        </button>
    </div>

    <?php if (!empty($flash['message'])): ?>
    <div class="alert alert-<?= $flash['type'] ?? 'success' ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Ara (ad, e-posta, sertifika no)</label>
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Ara..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Firma</label>
            <select name="firm_id" class="form-select form-select-sm">
                <option value="">Tüm Firmalar</option>
                <?php foreach ($firms as $f): ?>
                <option value="<?= $f['id'] ?>" <?= $firmFilter == $f['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Durum</label>
            <select name="valid" class="form-select form-select-sm">
                <option value="">Tümü</option>
                <option value="1" <?= $validFilter === '1' ? 'selected' : '' ?>>Geçerli</option>
                <option value="0" <?= $validFilter === '0' ? 'selected' : '' ?>>İptal</option>
            </select>
        </div>
        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtrele</button>
            <a href="/admin/sertifikalar" class="btn btn-sm btn-outline-secondary">Sıfırla</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Sertifikalar <span class="badge bg-secondary ms-1"><?= count($certs) ?></span></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Sertifika No</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Firma</th>
                        <th>Kurs</th>
                        <th>Veriliş Tarihi</th>
                        <th class="text-center">Durum</th>
                        <th class="text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($certs as $c): ?>
                <tr class="<?= $c['is_valid'] ? '' : 'table-danger opacity-75' ?>">
                    <td class="fw-semibold font-monospace small"><?= htmlspecialchars($c['cert_number']) ?></td>
                    <td><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= $c['firma'] ? htmlspecialchars($c['firma']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= htmlspecialchars($c['kurs']) ?></td>
                    <td><?= $c['issued_at'] ? date('d.m.Y', strtotime($c['issued_at'])) : '-' ?></td>
                    <td class="text-center">
                        <?php if ($c['is_valid']): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Geçerli</span>
                        <?php else: ?>
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>İptal</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="/sertifika/indir/<?= urlencode($c['cert_number']) ?>" class="btn btn-xs btn-outline-primary"
                               title="PDF İndir" target="_blank">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php if ($c['is_valid']): ?>
                            <form method="POST" action="/admin/sertifikalar/iptal/<?= $c['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="İptal Et"
                                        onclick="return confirm('Sertifikayı iptal etmek istediğinizden emin misiniz?')">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="/admin/sertifikalar/etkinlestir/<?= $c['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-xs btn-outline-success" title="Yeniden Etkinleştir"
                                        onclick="return confirm('Sertifikayı yeniden etkinleştirmek istiyor musunuz?')">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($certs)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">Sertifika bulunamadı.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Manuel Sertifika Ekle -->
<div class="modal fade" id="addCertModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/admin/sertifikalar/ekle" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-award me-2"></i>Manuel Sertifika Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Öğrenci</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">— Öğrenci Seç —</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>">
                            <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' (' . $u['email'] . ')') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kurs</label>
                    <select name="course_id" class="form-select" required>
                        <option value="">— Kurs Seç —</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Manuel eklenen sertifikaya otomatik numara atanır ve PDF otomatik oluşturulur.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Ekle</button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-xs { padding: 2px 6px; font-size: .75rem; }
</style>
<?php include __DIR__ . '/../_layout_end.php'; ?>
