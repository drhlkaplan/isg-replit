<?php
$pageTitle = 'Kursa Kaydet — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/admin/kullanicilar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Kullanıcıyı Kursa Kaydet</h4>
    </div>
    <div class="card shadow-sm" style="max-width:500px">
        <div class="card-header">
            <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
            <span class="text-muted small ms-2"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/kullanicilar/kayit/<?= $user['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <label class="form-label fw-semibold">Kurs Seçin *</label>
                <select name="course_id" class="form-select mb-3" required>
                    <option value="">— Kurs Seçin —</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-journal-plus me-1"></i>Kaydet</button>
                    <a href="/admin/kullanicilar" class="btn btn-outline-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
