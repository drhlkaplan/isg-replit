<?php
$pageTitle = 'Veritabanı Yedekleme — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-download me-2 text-primary"></i>Veritabanı Yedekleme</h4>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-database me-2"></i>SQL Yedek Al</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/admin/sistem/yedek">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Yedekleme Kapsamı</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tables" id="tablesAll" value="all" checked onchange="toggleTableList(this)">
                                <label class="form-check-label" for="tablesAll">
                                    <strong>Tam Veritabanı</strong> <span class="text-muted small">(tüm tablolar)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tables" id="tablesSelected" value="selected" onchange="toggleTableList(this)">
                                <label class="form-check-label" for="tablesSelected">
                                    <strong>Seçili Tablolar</strong>
                                </label>
                            </div>
                        </div>

                        <div id="tableListSection" class="mb-3" style="display:none">
                            <label class="form-label fw-semibold small">Tablo Seçin</label>
                            <div class="border rounded p-3 bg-light">
                                <?php foreach (['users' => 'Kullanıcılar', 'enrollments' => 'Kayıtlar', 'scorm_tracking' => 'SCORM İzleme', 'certificates' => 'Sertifikalar'] as $tbl => $lbl): ?>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="table_list[]" value="<?= $tbl ?>" id="tbl_<?= $tbl ?>" checked>
                                    <label class="form-check-label" for="tbl_<?= $tbl ?>"><?= $lbl ?> <span class="text-muted small">(<?= $tbl ?>)</span></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Yedek indirilecek. Devam edilsin mi?')">
                            <i class="bi bi-download me-2"></i>Yedek İndir (.sql.gz)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mt-3 mt-lg-0">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle-fill text-info me-2"></i>Bilgi</h6>
                </div>
                <div class="card-body p-3 small text-muted">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-dot"></i>Yedek dosyası <strong>.sql.gz</strong> (gzip sıkıştırılmış SQL) olarak indirilir.</li>
                        <li class="mb-2"><i class="bi bi-dot"></i>Tam yedek tüm tabloları ve verileri içerir.</li>
                        <li class="mb-2"><i class="bi bi-dot"></i>Yedekleme indirme işlemleri denetim günlüğüne kaydedilir.</li>
                        <li><i class="bi bi-dot"></i>Geri yükleme için MySQL CLI veya phpMyAdmin kullanın: <code>mysql -u root isg_lms &lt; dosya.sql</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTableList(el) {
    document.getElementById('tableListSection').style.display = (el.value === 'selected') ? 'block' : 'none';
}
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
