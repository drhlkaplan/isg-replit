<?php
$pageTitle = (isset($course) ? 'Kursu Düzenle' : 'Yeni Kurs') . ' — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
$isEdit = isset($course);
$tt = $course['topic_type'] ?? '';
$dm = $course['delivery_method'] ?? 'online';
$variant = $course['workplace_variant'] ?? 'genel';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/admin/kurslar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0"><?= $isEdit ? 'Kursu Düzenle' : 'Yeni Kurs / Modül Ekle' ?></h4>
    </div>
    <?php if (!empty($flash['message'])): ?>
    <div class="alert alert-<?= $flash['type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card shadow-sm" style="max-width:800px">
        <div class="card-body p-4">
            <form method="POST" action="/admin/kurslar/<?= $isEdit ? 'duzenle/' . $course['id'] : 'ekle' ?>" enctype="multipart/form-data" id="courseForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- === Temel Bilgiler === -->
                <h6 class="fw-bold text-muted mb-3 text-uppercase letter-spacing-1"><i class="bi bi-info-circle me-2"></i>Temel Bilgiler</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Kurs / Modül Adı *</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($course['title'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tehlike Sınıfı *</label>
                        <select name="category_id" id="categoryId" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-code="<?= $cat['code'] ?>" <?= ($course['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Eğitim Süresi (Dakika) *</label>
                        <input type="number" name="duration_minutes" class="form-control" min="1" required value="<?= $course['duration_minutes'] ?? 60 ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Açıklama</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- === ISG Sınıflandırma === -->
                <h6 class="fw-bold text-muted mb-3 text-uppercase"><i class="bi bi-tags me-2"></i>İSG Sınıflandırma</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Eğitim Türü</label>
                        <select name="training_type" class="form-select">
                            <option value="">— Seçiniz —</option>
                            <option value="temel"  <?= ($course['training_type'] ?? '') === 'temel'  ? 'selected' : '' ?>>Temel Eğitim</option>
                            <option value="tekrar" <?= ($course['training_type'] ?? '') === 'tekrar' ? 'selected' : '' ?>>Tekrar / Yenileme</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Konu Türü</label>
                        <select name="topic_type" id="topicType" class="form-select">
                            <option value="">— Seçiniz —</option>
                            <option value="paket"            <?= $tt === 'paket'            ? 'selected' : '' ?>>Paket (Ana)</option>
                            <option value="on_degerlendirme" <?= $tt === 'on_degerlendirme' ? 'selected' : '' ?>>Ön Değerlendirme Sınavı</option>
                            <option value="genel"            <?= $tt === 'genel'            ? 'selected' : '' ?>>Genel Konular</option>
                            <option value="saglik"           <?= $tt === 'saglik'           ? 'selected' : '' ?>>Sağlık Konuları</option>
                            <option value="teknik"           <?= $tt === 'teknik'           ? 'selected' : '' ?>>Teknik Konular</option>
                            <option value="ise_ozgu"         <?= $tt === 'ise_ozgu'         ? 'selected' : '' ?>>İşe ve İşyerine Özgü</option>
                            <option value="final_sinav"      <?= $tt === 'final_sinav'      ? 'selected' : '' ?>>Final Sınavı</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Üst Paket (Modül ise seçin)</label>
                        <select name="parent_course_id" class="form-select">
                            <option value="">— Bağımsız Paket —</option>
                            <?php foreach ($parentPackages as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($course['parent_course_id'] ?? null) == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sıralama (sort_order)</label>
                        <input type="number" name="sort_order" class="form-control" min="0" value="<?= $course['sort_order'] ?? 0 ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sunum Yöntemi</label>
                        <select name="delivery_method" id="deliveryMethod" class="form-select">
                            <option value="online"    <?= $dm === 'online'    ? 'selected' : '' ?>>Online (Uzaktan)</option>
                            <option value="yuz_yuze"  <?= $dm === 'yuz_yuze'  ? 'selected' : '' ?>>Yüz Yüze (Örgün)</option>
                            <option value="hibrit"    <?= $dm === 'hibrit'    ? 'selected' : '' ?>>Hibrit</option>
                        </select>
                        <div id="yuzYuzeWarning" class="alert alert-warning py-1 px-2 mt-1 small d-none">
                            <i class="bi bi-exclamation-triangle me-1"></i>Tehlikeli / Çok Tehlikeli İşe Özgü konular için yüz yüze eğitim zorunludur.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">İşyeri Varyantı</label>
                        <select name="workplace_variant" class="form-select">
                            <option value="genel"         <?= $variant === 'genel'         ? 'selected' : '' ?>>Genel</option>
                            <option value="gratis_depo"   <?= $variant === 'gratis_depo'   ? 'selected' : '' ?>>Gratis Depo</option>
                            <option value="beauty"        <?= $variant === 'beauty'        ? 'selected' : '' ?>>Beauty / Kozmetik</option>
                            <option value="gratis_magaza" <?= $variant === 'gratis_magaza' ? 'selected' : '' ?>>Gratis Mağaza</option>
                            <option value="mutfak"        <?= $variant === 'mutfak'        ? 'selected' : '' ?>>Mutfak / Yemek Servisi</option>
                        </select>
                    </div>
                </div>

                <!-- === Durum ve Tarihler === -->
                <h6 class="fw-bold text-muted mb-3 text-uppercase"><i class="bi bi-calendar me-2"></i>Durum ve Tarihler</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Durum</label>
                        <select name="status" class="form-select">
                            <option value="draft"    <?= ($course['status'] ?? '') === 'draft'    ? 'selected' : '' ?>>Taslak</option>
                            <option value="active"   <?= ($course['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Aktif</option>
                            <option value="archived" <?= ($course['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arşiv</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Eğitim Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($course['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Eğitim Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($course['end_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tamamlama Zorunlu</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="completion_required" id="compReq" <?= ($course['completion_required'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="compReq">Evet, tamamlama zorunlu</label>
                        </div>
                    </div>
                </div>

                <!-- === SCORM Paketi === -->
                <div id="scormSection">
                <h6 class="fw-bold text-muted mb-3 text-uppercase"><i class="bi bi-file-zip me-2 text-primary"></i>SCORM Paketi</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <?php if ($isEdit && isset($package)): ?>
                        <div class="d-flex align-items-center gap-2 p-3 rounded mb-3"
                             style="background:#e8f4fd;border:1px solid #b3d9f2;">
                            <i class="bi bi-archive-fill text-primary fs-5"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size:.88rem;">
                                    <?= htmlspecialchars($package['title'] ?? $package['launch_url']) ?>
                                </div>
                                <div style="font-size:.75rem;color:#5a7a95;">
                                    Mevcut paket &nbsp;·&nbsp;
                                    <span class="badge bg-primary" style="font-size:.68rem;">SCORM <?= htmlspecialchars($package['scorm_version']) ?></span>
                                </div>
                            </div>
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        </div>
                        <?php endif; ?>

                        <!-- Hidden real file input -->
                        <input type="file" name="scorm_package" id="scormFileInput" accept=".zip" style="display:none">

                        <!-- Drop zone -->
                        <div id="scormDropZone" style="
                            border: 2px dashed #adc8e0;
                            border-radius: 12px;
                            background: #f5faff;
                            padding: 32px 24px;
                            text-align: center;
                            cursor: pointer;
                            transition: border-color .2s, background .2s;
                        ">
                            <div id="dzDefault">
                                <div style="font-size: 2.5rem; color: #6eb0d8; margin-bottom: 10px;">
                                    <i class="bi bi-cloud-upload"></i>
                                </div>
                                <div style="font-size:.95rem;font-weight:700;color:#2c4f6b;margin-bottom:4px;">
                                    ZIP dosyasını buraya sürükleyin
                                </div>
                                <div style="font-size:.8rem;color:#7a9bb5;margin-bottom:16px;">
                                    veya
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm px-4" onclick="document.getElementById('scormFileInput').click()">
                                    <i class="bi bi-folder2-open me-1"></i>Dosya Seç
                                </button>
                                <div style="font-size:.73rem;color:#9ab5ca;margin-top:12px;">
                                    SCORM 1.2 / 2004 &nbsp;·&nbsp; imsmanifest.xml içeren ZIP &nbsp;·&nbsp; Maks. 256 MB
                                </div>
                            </div>
                            <div id="dzSelected" style="display:none;">
                                <div style="font-size:2rem;color:#28a745;margin-bottom:8px;">
                                    <i class="bi bi-file-earmark-zip-fill"></i>
                                </div>
                                <div id="dzFileName" style="font-size:.92rem;font-weight:700;color:#1a3a52;margin-bottom:4px;"></div>
                                <div id="dzFileMeta" style="font-size:.78rem;color:#6b8a9a;margin-bottom:14px;"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearScormFile()">
                                    <i class="bi bi-x-circle me-1"></i>Değiştir
                                </button>
                            </div>
                        </div>

                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            SCORM 1.2 veya 2004 uyumlu ZIP paketi yükleyin (imsmanifest.xml içermeli).
                            <?= $isEdit ? '<strong>Boş bırakırsanız mevcut paket korunur.</strong>' : '' ?>
                        </div>
                    </div>
                </div>
                </div>

                <!-- ── SCORM Upload Progress Overlay ─────────────────── -->
                <div id="scormUploadOverlay" style="
                    display: none;
                    position: fixed; inset: 0; z-index: 9999;
                    background: rgba(5, 20, 40, 0.80);
                    backdrop-filter: blur(6px);
                    align-items: center; justify-content: center;
                ">
                    <div style="
                        background: #fff; border-radius: 20px;
                        padding: 44px 52px; max-width: 480px; width: 90%;
                        box-shadow: 0 24px 64px rgba(0,0,0,.35);
                        text-align: center;
                    ">
                        <!-- Animated icon area -->
                        <div id="overlayIconUpload" style="font-size:3rem;color:#005695;margin-bottom:16px;animation:pulse 1.2s ease-in-out infinite;">
                            <i class="bi bi-cloud-upload-fill"></i>
                        </div>
                        <div id="overlayIconProcess" style="font-size:3rem;color:#0d6efd;margin-bottom:16px;display:none;">
                            <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
                        </div>
                        <div id="overlayIconDone" style="font-size:3rem;color:#28a745;margin-bottom:16px;display:none;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>

                        <div id="overlayTitle" style="font-size:1.2rem;font-weight:800;color:#0d1b2e;margin-bottom:4px;">
                            Dosya Yükleniyor…
                        </div>
                        <div id="overlaySubtitle" style="font-size:.84rem;color:#6b7c93;margin-bottom:24px;">
                            Lütfen sayfayı kapatmayın
                        </div>

                        <!-- Progress bar -->
                        <div id="overlayProgressWrap" style="background:#e8f0f8;border-radius:8px;overflow:hidden;margin-bottom:12px;height:12px;">
                            <div id="overlayProgressFill" style="
                                height:100%; width:0%;
                                background: linear-gradient(90deg,#005695,#0096d6);
                                border-radius:8px;
                                transition: width .3s ease;
                            "></div>
                        </div>

                        <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#7a8a9a;margin-bottom:20px;">
                            <span id="overlayLoaded">0 MB</span>
                            <span id="overlayPct">0%</span>
                            <span id="overlayTotal">— MB</span>
                        </div>

                        <!-- Phase steps -->
                        <div style="display:flex;justify-content:center;gap:28px;margin-top:4px;">
                            <div id="step1" style="text-align:center;">
                                <div style="width:32px;height:32px;border-radius:50%;background:#005695;color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-size:.8rem;font-weight:700;">1</div>
                                <div style="font-size:.7rem;color:#005695;font-weight:600;">Yükleme</div>
                            </div>
                            <div style="flex:1;height:1px;background:#dde4ee;margin-top:16px;"></div>
                            <div id="step2" style="text-align:center;opacity:.4;">
                                <div style="width:32px;height:32px;border-radius:50%;background:#dde4ee;color:#7a8a9a;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-size:.8rem;font-weight:700;">2</div>
                                <div style="font-size:.7rem;color:#7a8a9a;font-weight:600;">İşleme</div>
                            </div>
                            <div style="flex:1;height:1px;background:#dde4ee;margin-top:16px;"></div>
                            <div id="step3" style="text-align:center;opacity:.4;">
                                <div style="width:32px;height:32px;border-radius:50%;background:#dde4ee;color:#7a8a9a;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-size:.8rem;font-weight:700;">3</div>
                                <div style="font-size:.7rem;color:#7a8a9a;font-weight:600;">Tamamlandı</div>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                @keyframes pulse {
                    0%,100% { transform: scale(1); opacity:1; }
                    50%      { transform: scale(1.08); opacity:.8; }
                }
                #scormDropZone.dz-hover {
                    border-color: #005695 !important;
                    background: #eaf4fd !important;
                }
                </style>

                <!-- === YYZ Seansları (yüz yüze kurslarda görünür) === -->
                <div id="yyzSection" class="d-none">
                <h6 class="fw-bold text-muted mb-3 text-uppercase"><i class="bi bi-people-fill me-2 text-warning"></i>Yüz Yüze Eğitim Seansları</h6>
                <?php if ($isEdit && !empty($linkedSessions ?? [])): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-success small"><i class="bi bi-link-45deg me-1"></i>Bu Kursa Bağlı Seanslar</label>
                    <div class="list-group">
                    <?php foreach ($linkedSessions as $ls): ?>
                        <div class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                            <?php
                                $sLabel = match($ls['status']) {
                                    'scheduled'  => 'Planlandı',
                                    'active'     => 'Aktif',
                                    'completed'  => 'Tamamlandı',
                                    'cancelled'  => 'İptal',
                                    default      => $ls['status'],
                                };
                                $sBadge = match($ls['status']) {
                                    'active'    => 'success',
                                    'completed' => 'secondary',
                                    'cancelled' => 'danger',
                                    default     => 'warning',
                                };
                            ?>
                            <span class="badge bg-<?= $sBadge ?> rounded-pill"><?= htmlspecialchars($sLabel) ?></span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size:.88rem;"><?= htmlspecialchars($ls['title']) ?></div>
                                <small class="text-muted">
                                    <?= date('d.m.Y H:i', strtotime($ls['scheduled_at'])) ?> •
                                    <?= htmlspecialchars($ls['firm_name'] ?? '—') ?>
                                    <?php if ($ls['tr_first']): ?> • Eğitmen: <?= htmlspecialchars($ls['tr_first'] . ' ' . $ls['tr_last']) ?><?php endif; ?>
                                </small>
                            </div>
                            <a href="/admin/yyz/detay/<?= $ls['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
                <?php elseif ($isEdit): ?>
                <div class="alert alert-warning border-0 py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>Bu kursa henüz bağlı bir yüz yüze seans yok.
                </div>
                <?php endif; ?>

                <?php if ($isEdit && !empty($allSessions ?? [])): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mevcut Seanslardan Ata</label>
                    <select name="assign_session_id" class="form-select">
                        <option value="">— Seans ata (opsiyonel) —</option>
                        <?php foreach ($allSessions as $as): ?>
                        <option value="<?= $as['id'] ?>" <?= ($as['course_id'] == ($course['id'] ?? 0)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($as['title']) ?> —
                            <?= date('d.m.Y', strtotime($as['scheduled_at'])) ?>
                            (<?= htmlspecialchars($as['course_title']) ?>)
                            <?= $as['firm_name'] ? '| ' . htmlspecialchars($as['firm_name']) : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Seçilen seansın kursu bu kursa güncellenir.</div>
                </div>
                <?php endif; ?>
                <div class="mb-4">
                    <a href="/admin/yyz/yeni<?= $isEdit ? '?course_id=' . ($course['id'] ?? '') : '' ?>" class="btn btn-outline-warning btn-sm" target="_blank">
                        <i class="bi bi-plus-circle me-1"></i>Bu Kurs İçin Yeni Seans Oluştur
                    </a>
                </div>
                </div>

                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Güncelle' : 'Kaydet' ?>
                    </button>
                    <a href="/admin/kurslar" class="btn btn-outline-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
/* ── Section visibility logic ──────────────────────────────────── */
(function(){
    var topicType    = document.getElementById('topicType');
    var deliveryMeth = document.getElementById('deliveryMethod');
    var categoryId   = document.getElementById('categoryId');
    var warning      = document.getElementById('yuzYuzeWarning');
    var scormSection = document.getElementById('scormSection');
    var yyzSection   = document.getElementById('yyzSection');

    function updateSections() {
        var isYYZ = (deliveryMeth ? deliveryMeth.value : 'online') === 'yuz_yuze';
        if (scormSection) scormSection.classList.toggle('d-none', isYYZ);
        if (yyzSection)   yyzSection.classList.toggle('d-none', !isYYZ);
    }
    function checkDelivery() {
        var tt = topicType ? topicType.value : '';
        var dm = deliveryMeth ? deliveryMeth.value : '';
        var cat = categoryId ? categoryId.selectedOptions[0].dataset.code : '';
        warning && warning.classList.toggle('d-none', !(tt === 'ise_ozgu' && (cat === 'TE' || cat === 'CT') && dm !== 'yuz_yuze'));
        updateSections();
    }
    function autoSetDelivery() {
        var tt = topicType ? topicType.value : '';
        var cat = categoryId ? categoryId.selectedOptions[0].dataset.code : '';
        if (tt === 'ise_ozgu' && (cat === 'TE' || cat === 'CT'))
            if (deliveryMeth) deliveryMeth.value = 'yuz_yuze';
        checkDelivery();
    }
    if (topicType)    topicType.addEventListener('change', autoSetDelivery);
    if (categoryId)   categoryId.addEventListener('change', function(){ autoSetDelivery(); checkDelivery(); });
    if (deliveryMeth) deliveryMeth.addEventListener('change', checkDelivery);
    checkDelivery();
})();

/* ── SCORM Dropzone ────────────────────────────────────────────── */
(function() {
    var fileInput = document.getElementById('scormFileInput');
    var dropZone  = document.getElementById('scormDropZone');
    var dzDefault = document.getElementById('dzDefault');
    var dzSelected = document.getElementById('dzSelected');
    var dzFileName = document.getElementById('dzFileName');
    var dzFileMeta = document.getElementById('dzFileMeta');
    if (!fileInput || !dropZone) return;

    function fmtSize(bytes) {
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function showFile(file) {
        dzDefault.style.display = 'none';
        dzSelected.style.display = 'block';
        dzFileName.textContent = file.name;
        dzFileMeta.textContent = fmtSize(file.size) + '  ·  ZIP arşivi  ·  SCORM paketi';
        dropZone.style.borderColor = '#28a745';
        dropZone.style.background  = '#f0fff4';
    }

    window.clearScormFile = function() {
        fileInput.value = '';
        dzDefault.style.display = 'block';
        dzSelected.style.display = 'none';
        dropZone.style.borderColor = '#adc8e0';
        dropZone.style.background  = '#f5faff';
    };

    fileInput.addEventListener('change', function() {
        if (this.files[0]) showFile(this.files[0]);
    });

    // Drag events
    dropZone.addEventListener('click', function(e) {
        if (!e.target.closest('button')) fileInput.click();
    });
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault(); this.classList.add('dz-hover');
    });
    dropZone.addEventListener('dragleave', function() {
        this.classList.remove('dz-hover');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dz-hover');
        var file = e.dataTransfer.files[0];
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.zip')) {
            alert('Lütfen geçerli bir ZIP dosyası seçin.');
            return;
        }
        // Assign to file input via DataTransfer
        var dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFile(file);
    });
})();

/* ── XHR Upload with progress overlay ─────────────────────────── */
(function() {
    var form      = document.getElementById('courseForm');
    var fileInput = document.getElementById('scormFileInput');
    var overlay   = document.getElementById('scormUploadOverlay');
    if (!form || !fileInput || !overlay) return;

    var iconUpload  = document.getElementById('overlayIconUpload');
    var iconProcess = document.getElementById('overlayIconProcess');
    var iconDone    = document.getElementById('overlayIconDone');
    var oTitle      = document.getElementById('overlayTitle');
    var oSub        = document.getElementById('overlaySubtitle');
    var oFill       = document.getElementById('overlayProgressFill');
    var oLoaded     = document.getElementById('overlayLoaded');
    var oPct        = document.getElementById('overlayPct');
    var oTotal      = document.getElementById('overlayTotal');
    var step1El     = document.getElementById('step1');
    var step2El     = document.getElementById('step2');
    var step3El     = document.getElementById('step3');

    function fmtMB(bytes) { return (bytes / (1024 * 1024)).toFixed(1) + ' MB'; }

    function setStep(n) {
        var steps = [step1El, step2El, step3El];
        var colors = ['#005695', '#0d6efd', '#28a745'];
        steps.forEach(function(s, i) {
            if (!s) return;
            var circle = s.querySelector('div');
            var label  = s.querySelectorAll('div')[1];
            if (i < n) {
                s.style.opacity = '1';
                if (circle) { circle.style.background = colors[i]; circle.style.color = '#fff'; }
            } else if (i === n) {
                s.style.opacity = '1';
                if (circle) { circle.style.background = colors[i]; circle.style.color = '#fff'; }
            } else {
                s.style.opacity = '.35';
            }
        });
    }

    function goPhase(phase) {
        if (phase === 'upload') {
            iconUpload.style.display  = 'block';
            iconProcess.style.display = 'none';
            iconDone.style.display    = 'none';
            oTitle.textContent = 'Dosya Yükleniyor…';
            oSub.textContent   = 'Lütfen sayfayı kapatmayın';
            setStep(0);
        } else if (phase === 'process') {
            iconUpload.style.display  = 'none';
            iconProcess.style.display = 'block';
            iconDone.style.display    = 'none';
            oTitle.textContent = 'SCORM Paketi İşleniyor…';
            oSub.textContent   = 'imsmanifest.xml okunuyor, içerik çıkartılıyor';
            oFill.style.width  = '100%';
            oLoaded.textContent = oTotal.textContent;
            oPct.textContent    = '100%';
            setStep(1);
        } else if (phase === 'done') {
            iconUpload.style.display  = 'none';
            iconProcess.style.display = 'none';
            iconDone.style.display    = 'block';
            oTitle.textContent = 'Tamamlandı!';
            oSub.textContent   = 'Yönlendiriliyor…';
            setStep(2);
        }
    }

    form.addEventListener('submit', function(e) {
        if (!fileInput.files || !fileInput.files[0]) return; // no file → normal submit

        e.preventDefault();

        var fd = new FormData(form);
        var xhr = new XMLHttpRequest();

        // Show overlay
        overlay.style.display = 'flex';
        goPhase('upload');

        xhr.upload.addEventListener('progress', function(ev) {
            if (!ev.lengthComputable) return;
            var pct = Math.round((ev.loaded / ev.total) * 100);
            oFill.style.width    = pct + '%';
            oPct.textContent     = pct + '%';
            oLoaded.textContent  = fmtMB(ev.loaded);
            oTotal.textContent   = fmtMB(ev.total);
        });

        xhr.upload.addEventListener('load', function() {
            goPhase('process');
        });

        xhr.addEventListener('load', function() {
            goPhase('done');
            setTimeout(function() {
                window.location.href = xhr.responseURL || '/admin/kurslar';
            }, 900);
        });

        xhr.addEventListener('error', function() {
            overlay.style.display = 'none';
            alert('Yükleme sırasında bir hata oluştu. Lütfen tekrar deneyin.');
        });

        xhr.open('POST', form.action, true);
        xhr.send(fd);
    });
})();
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
