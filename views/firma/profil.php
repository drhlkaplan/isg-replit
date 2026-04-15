<?php
$pageTitle = 'Firma Profili & Tema — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/firma" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0"><i class="bi bi-palette me-2 text-primary"></i>Firma Profili & Tema</h4>
    </div>

    <?php if (!empty($flash['msg'])): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="POST" action="/firma/profil" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="row g-4">
            <!-- Contact Info -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold"><i class="bi bi-person-lines-fill me-2"></i>İletişim Bilgileri</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Firma Adı</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($firm['name']) ?>" readonly disabled>
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

                        <div class="mt-3 p-3 rounded" style="background:#f8f9fa">
                            <div class="small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Vergi numarası ve şirket kodu yalnızca platform yöneticisi tarafından değiştirilebilir.
                                <?php if ($firm['company_code']): ?>
                                <br>Şirket kodunuz: <code class="fw-bold"><?= htmlspecialchars($firm['company_code']) ?></code>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold"><i class="bi bi-brush me-2"></i>Marka & Görünüm</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Özel Portal Başlığı</label>
                                <input type="text" name="header_title" class="form-control" maxlength="150"
                                       placeholder="Boş bırakırsanız firma adı kullanılır"
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
                                <?php if (!empty($firm['logo_path']) && file_exists(LOGO_DIR . $firm['logo_path'])): ?>
                                <div class="mb-2 p-2 border rounded d-inline-flex align-items-center gap-2">
                                    <img src="<?= LOGO_URL . htmlspecialchars($firm['logo_path']) ?>"
                                         alt="Mevcut logo" style="max-height:50px">
                                    <small class="text-muted">Yeni dosya seçerseniz değiştirilir</small>
                                </div><br>
                                <?php endif; ?>
                                <input type="file" name="logo_file" class="form-control" accept="image/png,image/jpeg,image/gif,image/svg+xml">
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
                                       placeholder="Tüm sayfalarda footer'da görünür. Boş bırakırsanız varsayılan metin kullanılır."
                                       value="<?= htmlspecialchars($firm['footer_text'] ?? '') ?>">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label fw-semibold">Duyuru Metni</label>
                                <input type="text" name="announcement" class="form-control" maxlength="255"
                                       placeholder="Boş bırakırsanız duyuru gösterilmez"
                                       value="<?= htmlspecialchars($firm['announcement'] ?? '') ?>">
                                <div class="form-text">Firmaya bağlı tüm kullanıcıların (öğrenciler dahil) sayfalarının üstünde banner olarak gösterilir.</div>
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
                <i class="bi bi-check-lg me-1"></i>Kaydet
            </button>
            <a href="/firma" class="btn btn-outline-secondary">İptal</a>
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
