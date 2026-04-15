<?php
$pageTitle = 'Sertifika Doğrula — ' . APP_NAME;
$bodyClass = 'isg-auth-page';
include __DIR__ . '/../_layout.php';

$isExpired = ($cert && $cert['expires_at'] && strtotime($cert['expires_at']) < time());
?>
<style>
/* ── Reset layout (same as auth pages) ─────────────────────────── */
body.isg-auth-page { background: #f0f4f8; margin: 0; }
body.isg-auth-page main { padding: 0 !important; min-height: 100vh; }

/* ── Split layout ─────────────────────────────────────────────── */
.auth-split { display: flex; min-height: 100vh; }

/* Left hero */
.auth-hero {
    flex: 0 0 52%;
    background: linear-gradient(145deg, #002d52 0%, #00508f 45%, #0072b5 100%);
    position: relative;
    display: flex; flex-direction: column; justify-content: center;
    padding: 60px 56px;
    overflow: hidden; color: #fff;
}
.auth-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 600px 400px at 110% 120%, rgba(255,200,0,.07) 0%, transparent 70%),
        radial-gradient(ellipse 400px 600px at -10% -10%, rgba(0,180,255,.08) 0%, transparent 70%);
}
.hero-deco { position: absolute; border-radius: 50%; background: rgba(255,255,255,.04); }
.hero-deco-1 { width: 520px; height: 520px; top: -140px; right: -180px; }
.hero-deco-2 { width: 280px; height: 280px; bottom: -60px; left: -80px; }
.hero-deco-3 { width: 160px; height: 160px; bottom: 140px; right: 20px; }

.hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(8px);
    border-radius: 24px; padding: 6px 16px;
    font-size: .78rem; font-weight: 600; letter-spacing: .04em;
    color: rgba(255,255,255,.9); margin-bottom: 32px; width: fit-content;
    text-decoration: none;
}

.hero-shield {
    width: 96px; height: 96px;
    background: linear-gradient(135deg, rgba(255,255,255,.18), rgba(255,255,255,.06));
    border: 2px solid rgba(255,255,255,.2);
    backdrop-filter: blur(8px);
    border-radius: 28px;
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem;
    margin-bottom: 28px;
    box-shadow: 0 8px 32px rgba(0,0,0,.25), 0 0 0 1px rgba(255,255,255,.06);
}

.hero-title {
    font-size: 2.1rem; font-weight: 800; line-height: 1.2;
    letter-spacing: -.02em; margin-bottom: 14px;
}
.hero-title span { color: #ffd700; }
.hero-sub { font-size: 1rem; color: rgba(255,255,255,.7); line-height: 1.6; max-width: 360px; margin-bottom: 40px; }

.hero-features { display: flex; flex-direction: column; gap: 14px; margin-bottom: 40px; }
.hero-feature {
    display: flex; align-items: flex-start; gap: 14px;
}
.hf-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0; margin-top: 2px;
}
.hf-body .hf-title { font-size: .88rem; font-weight: 700; color: #fff; margin-bottom: 2px; }
.hf-body .hf-desc  { font-size: .76rem; color: rgba(255,255,255,.55); line-height: 1.4; }

.hero-trust {
    display: flex; gap: 10px; flex-wrap: wrap;
}
.hero-trust-item {
    display: flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.08); border-radius: 8px;
    padding: 6px 12px; font-size: .75rem;
    color: rgba(255,255,255,.7); font-weight: 500;
}

/* Right form panel */
.auth-form-panel {
    flex: 1; display: flex; align-items: center; justify-content: center;
    background: #f7f9fc; padding: 40px 24px;
}
.auth-card {
    width: 100%; max-width: 440px;
    background: #fff; border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0,50,100,.08), 0 1px 4px rgba(0,0,0,.04);
    padding: 40px 36px;
}

.auth-card-logo {
    display: flex; align-items: center; gap: 10px; margin-bottom: 28px;
}
.auth-card-logo .logo-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #005695, #0096d6);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #fff;
    box-shadow: 0 4px 12px rgba(0,86,149,.3); flex-shrink: 0;
}
.auth-card-logo .logo-text { font-size: .82rem; font-weight: 700; color: #1a2e4a; line-height: 1.2; }
.auth-card-logo .logo-sub  { font-size: .68rem; color: #7a8a9a; font-weight: 400; }

.auth-heading     { font-size: 1.45rem; font-weight: 800; color: #0d1b2e; margin-bottom: 4px; letter-spacing: -.02em; }
.auth-heading-sub { font-size: .85rem; color: #6b7c93; margin-bottom: 28px; }

.auth-field-group { margin-bottom: 16px; }
.auth-field-group label { font-size: .8rem; font-weight: 700; color: #3d5166; display: block; margin-bottom: 6px; }
.auth-input {
    display: flex; align-items: center;
    border: 1.5px solid #dde4ee; border-radius: 10px;
    background: #f8fafc;
    transition: border-color .2s, box-shadow .2s; overflow: hidden;
}
.auth-input:focus-within {
    border-color: #005695;
    box-shadow: 0 0 0 3px rgba(0,86,149,.1);
    background: #fff;
}
.auth-input .ai-icon { padding: 0 12px; color: #9aaabb; font-size: 1rem; flex-shrink: 0; }
.auth-input input {
    flex: 1; border: none; background: transparent;
    padding: 11px 12px 11px 0; font-size: .9rem;
    color: #1a2e4a; outline: none; min-width: 0;
}
.auth-input input::placeholder { color: #b0bec5; }

.auth-submit {
    width: 100%; padding: 13px;
    background: linear-gradient(135deg, #005695, #0072b5);
    color: #fff; border: none; border-radius: 12px;
    font-size: .95rem; font-weight: 700; cursor: pointer;
    letter-spacing: .01em; margin-top: 8px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 4px 16px rgba(0,86,149,.35);
    transition: opacity .2s, transform .1s;
}
.auth-submit:hover  { opacity: .92; }
.auth-submit:active { transform: scale(.98); }

.auth-footer {
    text-align: center; margin-top: 20px;
    font-size: .82rem; color: #7a8a9a;
}
.auth-footer a { color: #005695; font-weight: 700; text-decoration: none; }
.auth-footer a:hover { text-decoration: underline; }

.auth-hint { font-size: .75rem; color: #9aaabb; margin-top: 6px; display: flex; align-items: center; gap: 4px; }

/* Result states */
.verify-result-icon { font-size: 2.4rem; line-height: 1; margin-bottom: 8px; }
.verify-table { width: 100%; border-collapse: collapse; font-size: .83rem; margin-top: 16px; }
.verify-table th {
    background: #f7fafd; color: #6b82a0; font-weight: 600;
    padding: .5rem .75rem; border: 1px solid #e0eaf4;
    white-space: nowrap; width: 40%; text-align: left;
}
.verify-table td { padding: .5rem .75rem; border: 1px solid #e0eaf4; color: #1a2e4a; }

.auth-error {
    background: #fef2f2; border: 1px solid #fecaca;
    border-radius: 10px; padding: 10px 14px;
    font-size: .82rem; color: #dc2626;
    display: flex; align-items: center; gap: 8px; margin-bottom: 16px;
}
.auth-success {
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 10px; padding: 10px 14px;
    font-size: .82rem; color: #16a34a;
    display: flex; align-items: center; gap: 8px; margin-bottom: 4px;
}

.btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 20px; padding: 10px 18px;
    background: #f0f4f8; border: 1.5px solid #dde4ee;
    border-radius: 10px; font-size: .82rem; font-weight: 600;
    color: #005695; text-decoration: none;
    transition: background .15s;
}
.btn-back:hover { background: #e4eaf2; color: #005695; }

/* Mobile */
@media (max-width: 768px) {
    .auth-hero { display: none; }
    .auth-form-panel { background: #fff; padding: 24px 16px; }
    .auth-card { box-shadow: none; background: transparent; padding: 24px 0; }
}
@media (max-width: 480px) {
    .auth-card { padding: 16px 0; }
}
</style>

<div class="auth-split">

    <!-- ── Left Hero Panel ─────────────────────────────────────── -->
    <div class="auth-hero">
        <div class="hero-deco hero-deco-1"></div>
        <div class="hero-deco hero-deco-2"></div>
        <div class="hero-deco hero-deco-3"></div>

        <div style="position:relative;z-index:1;">
            <a href="/" class="hero-badge">
                <i class="bi bi-patch-check-fill" style="color:#ffd700"></i>
                ISG Akademi
            </a>

            <a href="/" style="text-decoration:none;display:inline-block;">
            <div class="hero-shield">
                <i class="bi bi-shield-fill-check" style="color:#ffd700"></i>
            </div>
            </a>

            <h1 class="hero-title">
                Sertifika <span>Doğrulama</span><br>Sistemi
            </h1>
            <p class="hero-sub">
                İSG Akademi tarafından düzenlenen sertifikaların geçerliliğini anında ve güvenle sorgulayın.
            </p>

            <div class="hero-features">
                <div class="hero-feature">
                    <div class="hf-icon"><i class="bi bi-lightning-charge-fill" style="color:#ffd700"></i></div>
                    <div class="hf-body">
                        <div class="hf-title">Anlık Doğrulama</div>
                        <div class="hf-desc">Sertifika numarasını girerek saniyeler içinde sonuç alın.</div>
                    </div>
                </div>
                <div class="hero-feature">
                    <div class="hf-icon"><i class="bi bi-qr-code" style="color:#7dd8ff"></i></div>
                    <div class="hf-body">
                        <div class="hf-title">QR Kodu ile Doğrulama</div>
                        <div class="hf-desc">Sertifika üzerindeki QR kodunu okutarak da bu sayfaya ulaşabilirsiniz.</div>
                    </div>
                </div>
                <div class="hero-feature">
                    <div class="hf-icon"><i class="bi bi-award-fill" style="color:#7dffb0"></i></div>
                    <div class="hf-body">
                        <div class="hf-title">Akredite Belgeler</div>
                        <div class="hf-desc">Tüm sertifikalar T.C. İSG mevzuatına uygun şekilde düzenlenir.</div>
                    </div>
                </div>
            </div>

            <div class="hero-trust">
                <div class="hero-trust-item">
                    <i class="bi bi-shield-lock-fill" style="color:#7dd8ff"></i> SSL Güvenli
                </div>
                <div class="hero-trust-item">
                    <i class="bi bi-building-check" style="color:#7dffb0"></i> Bakanlık Uyumlu
                </div>
                <div class="hero-trust-item">
                    <i class="bi bi-clock-history" style="color:#ffd700"></i> 7/24 Aktif
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right Form Panel ────────────────────────────────────── -->
    <div class="auth-form-panel">
        <div class="auth-card">

            <a href="/" style="text-decoration:none;color:inherit;">
            <div class="auth-card-logo">
                <div class="logo-icon"><i class="bi bi-shield-check"></i></div>
                <div>
                    <div class="logo-text">İSG Eğitim Platformu</div>
                    <div class="logo-sub">ISG Akademi</div>
                </div>
            </div>
            </a>

            <?php if (!$certNumber): ?>
            <!-- State 1: Search form -->
            <div class="auth-heading">Sertifika Sorgula</div>
            <div class="auth-heading-sub">Sertifika numaranızı girerek geçerliliğini kontrol edin.</div>

            <form method="GET" action="/dogrula">
                <div class="auth-field-group">
                    <label for="certNumber">Sertifika Numarası</label>
                    <div class="auth-input">
                        <span class="ai-icon"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" id="certNumber" name="certNumber"
                               placeholder="ISG-XXXX-XXXXXXXXXX"
                               style="font-family:monospace;letter-spacing:.06em;font-weight:600"
                               required autocomplete="off">
                    </div>
                    <div class="auth-hint">
                        <i class="bi bi-info-circle"></i>
                        Numara, belgenizin üst kısmında yazmaktadır.
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <i class="bi bi-search"></i>
                    Sertifikayı Doğrula
                </button>
            </form>

            <div class="auth-footer">
                <a href="/giris"><i class="bi bi-box-arrow-in-right me-1"></i>Sisteme Giriş Yap</a>
            </div>

            <?php elseif ($cert): ?>
            <!-- State 2: Certificate found -->
            <div class="auth-heading" style="color:#16a34a;">Sertifika Geçerli</div>
            <div class="auth-heading-sub" style="margin-bottom:0">
                <?php if ($isExpired): ?>
                <span class="badge bg-danger" style="font-size:.72rem;vertical-align:middle">Süresi Doldu</span>
                <?php else: ?>
                <span class="badge bg-success" style="font-size:.72rem;vertical-align:middle">Aktif</span>
                <?php endif; ?>
                Bu sertifika sistemimizde kayıtlıdır.
            </div>

            <table class="verify-table">
                <tr>
                    <th>Ad Soyad</th>
                    <td><?= htmlspecialchars($cert['full_name']) ?></td>
                </tr>
                <tr>
                    <th>Kurs / Program</th>
                    <td><?= htmlspecialchars($cert['course_title']) ?></td>
                </tr>
                <tr>
                    <th>Sertifika No</th>
                    <td><code style="font-size:.78rem;color:#005695"><?= htmlspecialchars($cert['cert_number']) ?></code></td>
                </tr>
                <tr>
                    <th>Düzenleme Tarihi</th>
                    <td><?= date('d.m.Y', strtotime($cert['issued_at'])) ?></td>
                </tr>
                <?php if ($cert['expires_at']): ?>
                <tr>
                    <th>Geçerlilik</th>
                    <td class="<?= $isExpired ? 'text-danger fw-bold' : 'text-success fw-semibold' ?>">
                        <?= date('d.m.Y', strtotime($cert['expires_at'])) ?>
                        <?= $isExpired ? ' (Süresi Doldu)' : ' (Geçerli)' ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <a href="/dogrula" class="btn-back">
                <i class="bi bi-arrow-left-short"></i> Yeni Sorgulama
            </a>

            <?php else: ?>
            <!-- State 3: Not found -->
            <div class="auth-heading" style="color:#dc2626;">Sertifika Bulunamadı</div>
            <div class="auth-heading-sub">Girdiğiniz numara sistemde kayıtlı değil.</div>

            <div class="auth-error">
                <i class="bi bi-x-circle-fill"></i>
                "<strong><?= htmlspecialchars($certNumber) ?></strong>" numaralı sertifika bulunamadı veya hatalı girildi.
            </div>

            <div class="auth-hint" style="margin-bottom:0">
                <i class="bi bi-info-circle"></i>
                Büyük/küçük harf veya tire işaretlerine dikkat ediniz.
            </div>

            <a href="/dogrula" class="btn-back" style="margin-top:24px">
                <i class="bi bi-arrow-left-short"></i> Yeniden Sorgula
            </a>

            <div class="auth-footer" style="margin-top:16px">
                Sorun yaşıyorsanız
                <a href="/giris">destek için giriş yapın</a>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../_layout_end.php'; ?>
