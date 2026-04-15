<?php
$pageTitle = 'Firmalar — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-building me-2 text-secondary"></i>Firma Yönetimi</h4>
        <a href="/admin/firmalar/ekle" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Yeni Firma</a>
    </div>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Firma Adı</th><th>Şirket Kodu</th><th>Vergi No</th><th>Yetkili</th><th>Kullanıcı</th><th>Durum</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                <?php foreach ($firms as $f): ?>
                <tr>
                    <td>
                        <div class="fw-semibold d-flex align-items-center gap-2">
                            <?php if (!empty($f['logo_path']) && file_exists(LOGO_DIR . $f['logo_path'])): ?>
                            <img src="<?= LOGO_URL . htmlspecialchars($f['logo_path']) ?>" style="max-height:28px;max-width:60px" alt="">
                            <?php elseif (!empty($f['primary_color'])): ?>
                            <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:<?= htmlspecialchars($f['primary_color']) ?>"></span>
                            <?php endif; ?>
                            <?= htmlspecialchars($f['name']) ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($f['company_code']): ?>
                        <code class="small"><?= htmlspecialchars($f['company_code']) ?></code>
                        <a href="/giris?kod=<?= urlencode($f['company_code']) ?>" target="_blank"
                           class="btn btn-xs btn-outline-secondary ms-1" title="Giriş sayfasını önizle">
                            <i class="bi bi-box-arrow-up-right" style="font-size:.75rem"></i>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($f['tax_number'] ?: '—') ?></td>
                    <td class="small"><?= htmlspecialchars($f['contact_name'] ?: '—') ?></td>
                    <td><span class="badge bg-secondary"><?= $f['user_count'] ?></span></td>
                    <td>
                        <span class="badge <?= $f['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $f['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/admin/firmalar/duzenle/<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php if ($f['id'] != 1): ?>
                            <a href="/admin/firmalar/sil/<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Firmayı silmek istediğinizden emin misiniz? Kullanıcıları varsayılan firmaya taşınacak.')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($firms)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">Henüz firma eklenmemiş.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
