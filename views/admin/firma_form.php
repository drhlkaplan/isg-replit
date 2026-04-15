<?php
$isEdit = isset($firm);
$pageTitle = ($isEdit ? 'Firma Düzenle' : 'Yeni Firma') . ' — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/admin/firmalar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0"><?= $isEdit ? 'Firma Düzenle' : 'Yeni Firma Ekle' ?></h4>
    </div>
    <form method="POST"
          action="/admin/firmalar/<?= $isEdit ? 'duzenle/' . $firm['id'] : 'ekle' ?>"
          enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Basic Info -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold"><i class="bi bi-building me-2"></i>Temel Bilgiler</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Firma Adı *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= htmlspecialchars($firm['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vergi Numarası</label>
                                <input type="text" name="tax_number" class="form-control" maxlength="20"
                                       value="<?= htmlspecialchars($firm['tax_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Durum</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($firm['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="passive" <?= ($firm['status'] ?? '') === 'passive' ? 'selected' : '' ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Yetkili Ad Soyad</label>
                                <input type="text" name="contact_name" class="form-control"
                                       value="<?= htmlspecialchars($firm['contact_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Yetkili E-posta</label>
                                <input type="email" name="contact_email" class="form-control"
                                       value="<?= htmlspecialchars($firm['contact_email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Telefon</label>
                                <input type="tel" name="contact_phone" class="form-control"
                                       value="<?= htmlspecialchars($firm['contact_phone'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Adres</label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($firm['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold"><i class="bi bi-palette me-2"></i>Marka & Görünüm</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Şirket Kodu
                                    <span class="text-muted fw-normal small">(benzersiz, büyük harf)</span>
                                </label>
                                <input type="text" name="company_code" class="form-control font-monospace text-uppercase"
                                       maxlength="50" placeholder="örn: ACME"
                                       value="<?= htmlspecialchars(strtoupper($firm['company_code'] ?? '')) ?>"
                                       oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g,'')">
                                <div class="form-text">Öğrenciler bu kodu profilden girer.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Özel Portal Başlığı</label>
                                <input type="text" name="header_title" class="form-control" maxlength="150"
                                       placeholder="Boş = firma adı"
                                       value="<?= htmlspecialchars($firm['header_title'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ana Renk</label>
                                <div class="input-group">
                                    <input type="color" name="primary_color" id="primaryColorPicker" class="form-control form-control-color"
                                           value="<?= htmlspecialchars($firm['primary_color'] ?? '#005695') ?>" style="max-width:54px">
                                    <input type="text" id="primaryColorText" class="form-control font-monospace"
                                           maxlength="7" placeholder="#005695"
                                           value="<?= htmlspecialchars($firm['primary_color'] ?? '#005695') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">İkincil Renk</label>
                                <div class="input-group">
                                    <input type="color" name="secondary_color" id="secondaryColorPicker" class="form-control form-control-color"
                                           value="<?= htmlspecialchars($firm['secondary_color'] ?? '#0072b5') ?>" style="max-width:54px">
                                    <input type="text" id="secondaryColorText" class="form-control font-monospace"
                                           maxlength="7" placeholder="#0072b5"
                                           value="<?= htmlspecialchars($firm['secondary_color'] ?? '#0072b5') ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Firma Logosu</label>
                                <?php if ($isEdit && !empty($firm['logo_path']) && file_exists(LOGO_DIR . $firm['logo_path'])): ?>
                                <div class="mb-2 p-2 border rounded d-inline-flex align-items-center gap-2">
                                    <img src="<?= LOGO_URL . htmlspecialchars($firm['logo_path']) ?>"
                                         alt="Mevcut logo" style="max-height:50px">
                                    <small class="text-muted">Yeni dosya seçerseniz değiştirilir</small>
                                </div><br>
                                <?php endif; ?>
                                <input type="file" name="logo_file" class="form-control"
                                       accept="image/png,image/jpeg,image/gif,image/svg+xml">
                                <div class="form-text">PNG, JPG veya SVG. Max 2 MB. Önerilen: 300×80px.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer & Announcement -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold"><i class="bi bi-megaphone me-2"></i>Duyuru & Footer</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Footer Metni</label>
                                <input type="text" name="footer_text" class="form-control" maxlength="255"
                                       placeholder="Boş bırakırsanız varsayılan platform footer metni görünür"
                                       value="<?= htmlspecialchars($firm['footer_text'] ?? '') ?>">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label fw-semibold">Duyuru Metni</label>
                                <input type="text" name="announcement" class="form-control" maxlength="255"
                                       placeholder="Boş bırakırsanız duyuru gösterilmez"
                                       value="<?= htmlspecialchars($firm['announcement'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Duyuru Türü</label>
                                <select name="announcement_type" class="form-select">
                                    <?php foreach (['info'=>'Bilgi (Mavi)','warning'=>'Uyarı (Sarı)','danger'=>'Önemli (Kırmızı)','success'=>'Başarı (Yeşil)'] as $val => $lbl): ?>
                                    <option value="<?= $val ?>" <?= ($firm['announcement_type'] ?? 'info') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary px-5">
                <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Güncelle' : 'Kaydet' ?>
            </button>
            <a href="/admin/firmalar" class="btn btn-outline-secondary">İptal</a>
        </div>
    </form>
</div>
<script>
(function() {
    var pp = document.getElementById('primaryColorPicker'),
        pt = document.getElementById('primaryColorText'),
        sp = document.getElementById('secondaryColorPicker'),
        st = document.getElementById('secondaryColorText');
    pp.addEventListener('input', function() { pt.value = this.value; });
    pt.addEventListener('input', function() { if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) pp.value = this.value; });
    sp.addEventListener('input', function() { st.value = this.value; });
    st.addEventListener('input', function() { if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) sp.value = this.value; });
})();
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
