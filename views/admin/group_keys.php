<?php
$pageTitle = 'Grup Anahtarları — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-key me-2 text-warning"></i>Grup Anahtarları</h4>
        <a href="/admin/grup-anahtarlari/ekle" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Yeni Anahtar</a>
    </div>

    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
        <div>
            <strong>Grup Anahtarı Nedir?</strong> Öğrencilere paylaşabileceğiniz özel kodlardır.
            Öğrenci bu kodu profil sayfasına girdiğinde, anahtara bağlı tüm kurslara otomatik olarak kaydedilir.
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Anahtar Kodu</th>
                        <th>Ad / Açıklama</th>
                        <th>Kurs Sayısı</th>
                        <th>Kullanım</th>
                        <th>Oluşturan</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($keys as $k): ?>
                <tr>
                    <td>
                        <code class="fs-6 fw-bold text-primary"><?= htmlspecialchars($k['key_code']) ?></code>
                        <button class="btn btn-sm btn-link p-0 ms-1 copy-btn" data-code="<?= htmlspecialchars($k['key_code']) ?>" title="Kopyala">
                            <i class="bi bi-clipboard text-muted"></i>
                        </button>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($k['name']) ?></div>
                        <?php if ($k['description']): ?>
                        <small class="text-muted"><?= htmlspecialchars(mb_substr($k['description'], 0, 60)) ?><?= mb_strlen($k['description']) > 60 ? '…' : '' ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-info"><?= $k['course_count'] ?> kurs</span></td>
                    <td><span class="badge bg-secondary"><?= $k['usage_count'] ?> öğrenci</span></td>
                    <td class="small text-muted">
                        <?= $k['creator_first'] ? htmlspecialchars($k['creator_first'] . ' ' . $k['creator_last']) : '—' ?>
                    </td>
                    <td>
                        <span class="badge <?= $k['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $k['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= date('d.m.Y', strtotime($k['created_at'])) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/admin/grup-anahtarlari/duzenle/<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="/admin/grup-anahtarlari/sil/<?= $k['id'] ?>"
                                  onsubmit="return confirm('Bu grup anahtarını silmek istediğinizden emin misiniz?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($keys)): ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-key fs-1 d-block mb-2"></i>
                        Henüz grup anahtarı oluşturulmamış.<br>
                        <a href="/admin/grup-anahtarlari/ekle" class="btn btn-primary btn-sm mt-3">
                            <i class="bi bi-plus-circle me-1"></i>İlk Anahtarı Oluştur
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        navigator.clipboard.writeText(btn.dataset.code);
        const icon = btn.querySelector('i');
        icon.className = 'bi bi-check-lg text-success';
        setTimeout(() => icon.className = 'bi bi-clipboard text-muted', 1500);
    });
});
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
