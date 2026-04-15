<?php
$isEdit = isset($user);
$pageTitle = ($isEdit ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı') . ' — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/admin/kullanicilar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0"><?= $isEdit ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı Ekle' ?></h4>
    </div>
    <div class="card shadow-sm" style="max-width:650px">
        <div class="card-body p-4">
            <form method="POST" action="/admin/kullanicilar/<?= $isEdit ? 'duzenle/' . $user['id'] : 'ekle' ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ad *</label>
                        <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Soyad *</label>
                        <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">E-posta *</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telefon</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">TC Kimlik No</label>
                        <input type="text" name="tc_identity_no" class="form-control" maxlength="11" value="<?= htmlspecialchars($user['tc_identity_no'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rol *</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($user['role_id'] ?? 4) == $r['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['description'] ?: $r['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Firma</label>
                        <select name="firm_id" class="form-select">
                            <option value="">— Seçiniz —</option>
                            <?php foreach ($firms as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($user['firm_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($isEdit): ?>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Durum</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="passive" <?= ($user['status'] ?? '') === 'passive' ? 'selected' : '' ?>>Pasif</option>
                            <option value="pending" <?= ($user['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Beklemede</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Yeni Şifre <span class="text-muted fw-normal">(boş bırakırsanız değişmez)</span></label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Değiştirmek için yeni şifre girin">
                    </div>
                    <?php else: ?>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Şifre *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Güncelle' : 'Kaydet' ?>
                    </button>
                    <a href="/admin/kullanicilar" class="btn btn-outline-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
