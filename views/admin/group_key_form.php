<?php
$isEdit = isset($key);
$pageTitle = ($isEdit ? 'Grup Anahtarı Düzenle' : 'Yeni Grup Anahtarı') . ' — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
$selectedCourses = $selectedCourses ?? [];
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/admin/grup-anahtarlari" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0"><?= $isEdit ? 'Grup Anahtarı Düzenle' : 'Yeni Grup Anahtarı Oluştur' ?></h4>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="/admin/grup-anahtarlari/<?= $isEdit ? 'duzenle/' . $key['id'] : 'ekle' ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Anahtar Adı *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= htmlspecialchars($key['name'] ?? '') ?>"
                                       placeholder="Örn: 2024 İnşaat Güvenliği Grubu">
                            </div>
                            <?php if (!$isEdit): ?>
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Anahtar Kodu
                                    <span class="text-muted fw-normal small">(boş bırakırsanız otomatik oluşturulur)</span>
                                </label>
                                <input type="text" name="key_code" class="form-control font-monospace text-uppercase"
                                       maxlength="50" placeholder="Örn: INSAAT2024"
                                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g,'')">
                            </div>
                            <?php else: ?>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Anahtar Kodu</label>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace fw-bold text-primary"
                                           value="<?= htmlspecialchars($key['key_code']) ?>" readonly>
                                    <button type="button" class="btn btn-outline-secondary copy-btn"
                                            data-code="<?= htmlspecialchars($key['key_code']) ?>">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Durum</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($key['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="inactive" <?= ($key['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <textarea name="description" class="form-control" rows="3"
                                          placeholder="Bu anahtar için not veya açıklama..."><?= htmlspecialchars($key['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-light rounded">
                            <label class="form-label fw-semibold mb-3">
                                <i class="bi bi-book me-1"></i>Bağlı Kurslar
                                <span class="text-muted fw-normal small">(birden fazla seçebilirsiniz)</span>
                            </label>
                            <?php if (empty($courses)): ?>
                            <p class="text-muted small mb-0">Aktif kurs bulunamadı.</p>
                            <?php else: ?>
                            <div class="row g-2" style="max-height:300px;overflow-y:auto">
                                <?php foreach ($courses as $c): ?>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="course_ids[]" value="<?= $c['id'] ?>"
                                               id="course_<?= $c['id'] ?>"
                                               <?= in_array($c['id'], $selectedCourses) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="course_<?= $c['id'] ?>">
                                            <?= htmlspecialchars($c['title']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Güncelle' : 'Oluştur' ?>
                            </button>
                            <a href="/admin/grup-anahtarlari" class="btn btn-outline-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-warning">
                <div class="card-header bg-warning bg-opacity-10 fw-semibold text-warning-emphasis">
                    <i class="bi bi-info-circle me-1"></i>Nasıl Çalışır?
                </div>
                <div class="card-body small">
                    <ol class="ps-3 mb-0">
                        <li class="mb-2">Bu formu doldurup kaydedin.</li>
                        <li class="mb-2">Oluşan <strong>Anahtar Kodu</strong>'nu öğrencilerinizle paylaşın (e-posta, QR kodu vs.).</li>
                        <li class="mb-2">Öğrenci, profilindeki <em>"Grup Anahtarı"</em> alanına kodu girer.</li>
                        <li>Öğrenci anında seçili tüm kurslara kaydedilir.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        navigator.clipboard.writeText(btn.dataset.code);
        const icon = btn.querySelector('i');
        icon.className = 'bi bi-check-lg text-success';
        setTimeout(() => icon.className = 'bi bi-clipboard', 1500);
    });
});
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
