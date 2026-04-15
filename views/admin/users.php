<?php
$pageTitle = 'Kullanıcılar — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Kullanıcı Yönetimi</h4>
        <a href="/admin/kullanicilar/ekle" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Yeni Kullanıcı</a>
    </div>
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form class="d-flex gap-2" method="GET" action="/admin/kullanicilar">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="ara" class="form-control" placeholder="Ad, soyad veya e-posta ara..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit" class="btn btn-outline-primary">Ara</button>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Ad Soyad</th><th>E-posta</th><th>Rol</th><th>Firma</th><th>Durum</th><th>Kayıt</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role_name']) ?></span></td>
                    <td class="small"><?= htmlspecialchars($u['firm_name'] ?? '—') ?></td>
                    <td>
                        <span class="badge <?= $u['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $u['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/admin/kullanicilar/duzenle/<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="/admin/kullanicilar/kayit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-success" title="Kursa Kaydet">
                                <i class="bi bi-journal-plus"></i>
                            </a>
                            <a href="/admin/kullanicilar/sil/<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Kullanıcıyı silmek istediğinizden emin misiniz?')"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">Kullanıcı bulunamadı.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
