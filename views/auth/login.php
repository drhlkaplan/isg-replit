<?php
$pageTitle = 'Giriş Yap — ' . APP_NAME;
$bodyClass = 'isg-auth-page';
include __DIR__ . '/../_layout.php';
$brandFirm = $brandFirm ?? null;
$loginMode  = $loginMode ?? 'email';
?>
<style>
/* ── Reset layout for auth page ───────────────────────────────────── */
body.isg-auth-page { background: #f0f4f8; margin: 0; }
body.isg-auth-page main { padding: 0 !important; min-height: 100vh; }

/* ── Split layout ─────────────────────────────────────────────────── */
.auth-split {
    display: flex;
    min-height: 100vh;
}

/* Left hero panel */
.auth-hero {
    flex: 0 0 58%;
    background: linear-gradient(145deg, #002d52 0%, #00508f 45%, #0072b5 100%);
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px 56px;
    overflow: hidden;
    color: #fff;
}
.auth-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 600px 400px at 110% 120%, rgba(255,200,0,.07) 0%, transparent 70%),
        radial-gradient(ellipse 400px 600px at -10% -10%, rgba(0,180,255,.08) 0%, transparent 70%);
}
.hero-deco {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
}
.hero-deco-1 { width: 520px; height: 520px; top: -140px; right: -180px; }
.hero-deco-2 { width: 280px; height: 280px; bottom: -60px; left: -80px; }
.hero-deco-3 { width: 160px; height: 160px; bottom: 140px; right: 20px; }

.hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(8px);
    border-radius: 24px;
    padding: 6px 16px;
    font-size: .78rem; font-weight: 600; letter-spacing: .04em;
    color: rgba(255,255,255,.9);
    margin-bottom: 32px;
    width: fit-content;
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
    font-size: 2.25rem; font-weight: 800; line-height: 1.2;
    letter-spacing: -.02em; margin-bottom: 14px;
}
.hero-title span { color: #ffd700; }

.hero-sub {
    font-size: 1rem; color: rgba(255,255,255,.7);
    line-height: 1.6; max-width: 360px; margin-bottom: 48px;
}

.hero-stats {
    display: flex; gap: 32px; flex-wrap: wrap;
}
.hero-stat { text-align: left; }
.hero-stat .val {
    font-size: 1.75rem; font-weight: 800; color: #fff;
    letter-spacing: -.02em; line-height: 1;
}
.hero-stat .lbl {
    font-size: .72rem; color: rgba(255,255,255,.55);
    margin-top: 4px; font-weight: 500; letter-spacing: .04em;
    text-transform: uppercase;
}

.hero-trust {
    margin-top: 48px;
    display: flex; gap: 12px; flex-wrap: wrap;
}
.hero-trust-item {
    display: flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.08);
    border-radius: 8px; padding: 6px 12px;
    font-size: .75rem; color: rgba(255,255,255,.7);
    font-weight: 500;
}

/* Right form panel */
.auth-form-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f7f9fc;
    padding: 40px 24px;
}

.auth-card {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0,50,100,.08), 0 1px 4px rgba(0,0,0,.04);
    padding: 40px 36px;
}

.auth-card-logo {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 28px;
}
.auth-card-logo .logo-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #005695, #0096d6);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #fff;
    box-shadow: 0 4px 12px rgba(0,86,149,.3);
    flex-shrink: 0;
}
.auth-card-logo .logo-text { font-size: .82rem; font-weight: 700; color: #1a2e4a; line-height: 1.2; }
.auth-card-logo .logo-sub { font-size: .68rem; color: #7a8a9a; font-weight: 400; }

.auth-heading { font-size: 1.45rem; font-weight: 800; color: #0d1b2e; margin-bottom: 4px; letter-spacing: -.02em; }
.auth-heading-sub { font-size: .85rem; color: #6b7c93; margin-bottom: 28px; }

/* Mode tabs */
.login-tabs {
    display: flex;
    background: #f0f4f8;
    border-radius: 12px;
    padding: 4px;
    margin-bottom: 24px;
    gap: 4px;
    position: relative;
}
.login-tab {
    flex: 1;
    padding: 9px 12px;
    border: none;
    background: transparent;
    border-radius: 9px;
    cursor: pointer;
    font-size: .82rem; font-weight: 600;
    color: #7a8a9a;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: all .2s;
    position: relative; z-index: 1;
}
.login-tab.active {
    background: #fff;
    color: #005695;
    box-shadow: 0 2px 8px rgba(0,50,100,.1);
}

/* Input fields */
.auth-field-group { margin-bottom: 16px; }
.auth-field-group label { font-size: .8rem; font-weight: 700; color: #3d5166; display: block; margin-bottom: 6px; }
.auth-input {
    display: flex; align-items: center;
    border: 1.5px solid #dde4ee;
    border-radius: 10px;
    background: #f8fafc;
    transition: border-color .2s, box-shadow .2s;
    overflow: hidden;
}
.auth-input:focus-within {
    border-color: #005695;
    box-shadow: 0 0 0 3px rgba(0,86,149,.1);
    background: #fff;
}
.auth-input .ai-icon {
    padding: 0 12px;
    color: #9aaabb;
    font-size: 1rem;
    flex-shrink: 0;
}
.auth-input input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 11px 12px 11px 0;
    font-size: .9rem;
    color: #1a2e4a;
    outline: none;
    min-width: 0;
}
.auth-input input::placeholder { color: #b0bec5; }
.auth-input .ai-btn {
    background: none; border: none;
    padding: 0 12px;
    color: #9aaabb; cursor: pointer;
    font-size: .95rem;
    line-height: 1;
}
.auth-input .ai-btn:hover { color: #005695; }

/* TC input badge */
.tc-badge {
    background: linear-gradient(90deg, #005695, #0072b5);
    color: #fff;
    font-size: .65rem; font-weight: 700;
    padding: 2px 7px; border-radius: 4px;
    letter-spacing: .05em;
    white-space: nowrap;
}

/* Company code collapse */
.company-section {
    background: #f7f9fc;
    border: 1.5px dashed #dde4ee;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 16px;
}
.company-toggle {
    display: flex; align-items: center; justify-content: space-between;
    cursor: pointer;
    font-size: .8rem; font-weight: 600; color: #6b7c93;
    user-select: none;
}
.company-toggle:hover { color: #005695; }

/* Submit button */
.auth-submit {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, #005695, #0072b5);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: .95rem; font-weight: 700;
    cursor: pointer;
    letter-spacing: .01em;
    transition: opacity .2s, transform .1s;
    margin-top: 8px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 4px 16px rgba(0,86,149,.35);
}
.auth-submit:hover { opacity: .92; }
.auth-submit:active { transform: scale(.98); }

.auth-footer {
    text-align: center;
    margin-top: 20px;
    font-size: .82rem; color: #7a8a9a;
}
.auth-footer a { color: #005695; font-weight: 700; text-decoration: none; }
.auth-footer a:hover { text-decoration: underline; }

.auth-divider {
    display: flex; align-items: center; gap: 12px;
    margin: 20px 0;
    font-size: .75rem; color: #b0bec5; font-weight: 500;
}
.auth-divider::before, .auth-divider::after {
    content: ''; flex: 1; height: 1px; background: #edf1f6;
}

/* Error alert */
.auth-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .82rem; color: #dc2626;
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 16px;
}

/* Flash alert */
.auth-success {
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 10px; padding: 10px 14px;
    font-size: .82rem; color: #16a34a;
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 16px;
}

/* Brand logo override area */
.brand-logo-area { margin-bottom: 28px; text-align: center; }
.brand-logo-area img { max-height: 60px; }

/* Mobile responsive */
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
            <a href="/" style="text-decoration:none;">
            <div class="hero-badge">
                <i class="bi bi-patch-check-fill" style="color:#ffd700"></i>
                ISG Akademi
            </div>
            </a>

            <?php if ($brandFirm && $brandFirm['logo_path'] && file_exists(LOGO_DIR . $brandFirm['logo_path'])): ?>
            <div style="margin-bottom:28px;">
                <img src="<?= LOGO_URL . htmlspecialchars($brandFirm['logo_path']) ?>"
                     alt="<?= htmlspecialchars($brandFirm['name']) ?>" style="max-height:72px;max-width:220px">
            </div>
            <?php else: ?>
            <a href="/" style="text-decoration:none;display:inline-block;">
            <div class="hero-shield">
                <i class="bi bi-shield-fill-check" style="color:#ffd700"></i>
            </div>
            </a>
            <?php endif; ?>

            <h1 class="hero-title">
                <?php if ($brandFirm): ?>
                    <?= htmlspecialchars($brandFirm['header_title'] ?: $brandFirm['name']) ?>
                <?php else: ?>
                    <a href="/" style="text-decoration:none;color:inherit;">İSG <span>Eğitim</span><br>Platformu</a>
                <?php endif; ?>
            </h1>
            <p class="hero-sub">
                <?php if ($brandFirm): ?>
                    <?= htmlspecialchars($brandFirm['name']) ?> çalışan eğitim portalına hoş geldiniz.
                    Kurumsal İSG eğitimlerinizi güvenle tamamlayın.
                <?php else: ?>
                    Türkiye'nin önde gelen online İSG eğitim platformu.
                    Sertifikalı eğitimler, sınavlar ve akredite belgeler.
                <?php endif; ?>
            </p>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="val">50K+</div>
                    <div class="lbl">Kayıtlı Öğrenci</div>
                </div>
                <div class="hero-stat">
                    <div class="val">1.200+</div>
                    <div class="lbl">Tamamlanan Sertifika</div>
                </div>
                <div class="hero-stat">
                    <div class="val">300+</div>
                    <div class="lbl">Aktif Firma</div>
                </div>
            </div>

            <div class="hero-trust">
                <div class="hero-trust-item">
                    <i class="bi bi-shield-lock-fill" style="color:#7dd8ff"></i>
                    SSL Güvenli
                </div>
                <div class="hero-trust-item">
                    <i class="bi bi-award-fill" style="color:#ffd700"></i>
                    Akredite İçerik
                </div>
                <div class="hero-trust-item">
                    <i class="bi bi-building-check" style="color:#7dffb0"></i>
                    Bakanlık Uyumlu
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right Form Panel ────────────────────────────────────── -->
    <div class="auth-form-panel">
        <div class="auth-card">

            <?php if (!$brandFirm): ?>
            <a href="/" style="text-decoration:none;color:inherit;">
            <div class="auth-card-logo">
                <div class="logo-icon"><i class="bi bi-shield-check"></i></div>
                <div>
                    <div class="logo-text">İSG Eğitim Platformu</div>
                    <div class="logo-sub">ISG Akademi</div>
                </div>
            </div>
            </a>
            <?php else: ?>
            <div class="brand-logo-area">
                <img src="<?= LOGO_URL . htmlspecialchars($brandFirm['logo_path'] ?? '') ?>"
                     alt="<?= htmlspecialchars($brandFirm['name']) ?>">
            </div>
            <?php endif; ?>

            <div class="auth-heading">Hoş Geldiniz</div>
            <div class="auth-heading-sub">Hesabınıza giriş yapın</div>

            <?php if ($error): ?>
            <div class="auth-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($flash['msg'])): ?>
            <div class="auth-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
                <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
            <?php endif; ?>

            <!-- Login mode tabs -->
            <div class="login-tabs" role="tablist">
                <button type="button" class="login-tab <?= $loginMode !== 'tc' ? 'active' : '' ?>"
                        id="tab-email" onclick="switchMode('email')">
                    <i class="bi bi-envelope-fill"></i> E-posta
                </button>
                <button type="button" class="login-tab <?= $loginMode === 'tc' ? 'active' : '' ?>"
                        id="tab-tc" onclick="switchMode('tc')">
                    <i class="bi bi-person-badge-fill"></i> TC Kimlik
                </button>
            </div>

            <form method="POST" action="/giris" id="login-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="login_mode" id="login_mode" value="<?= htmlspecialchars($loginMode) ?>">
                <?php if ($brandFirm): ?>
                <input type="hidden" name="company_code" value="<?= htmlspecialchars($brandFirm['company_code']) ?>">
                <?php endif; ?>

                <!-- E-posta fields -->
                <div id="fields-email" style="<?= $loginMode === 'tc' ? 'display:none' : '' ?>">
                    <div class="auth-field-group">
                        <label for="inp-email">E-posta Adresi</label>
                        <div class="auth-input">
                            <span class="ai-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="inp-email" name="email"
                                   placeholder="ornek@firma.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   autocomplete="email">
                        </div>
                    </div>
                </div>

                <!-- TC Kimlik fields -->
                <div id="fields-tc" style="<?= $loginMode !== 'tc' ? 'display:none' : '' ?>">
                    <div class="auth-field-group">
                        <label for="inp-tc">
                            TC Kimlik Numarası
                            <span class="tc-badge">11 hane</span>
                        </label>
                        <div class="auth-input">
                            <span class="ai-icon"><i class="bi bi-person-badge"></i></span>
                            <input type="text" id="inp-tc" name="tc_identity_no"
                                   placeholder="_ _ _ _ _ _ _ _ _ _ _"
                                   maxlength="11" inputmode="numeric" pattern="\d{11}"
                                   value="<?= htmlspecialchars($_POST['tc_identity_no'] ?? '') ?>"
                                   autocomplete="off" style="letter-spacing:.12em;font-weight:600">
                        </div>
                    </div>
                </div>

                <!-- Password (shared) -->
                <div class="auth-field-group">
                    <label for="inp-pass">Şifre</label>
                    <div class="auth-input">
                        <span class="ai-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" id="inp-pass" name="password"
                               placeholder="••••••••" autocomplete="current-password">
                        <button type="button" class="ai-btn" onclick="togglePwd()" title="Şifreyi göster/gizle">
                            <i class="bi bi-eye" id="pwd-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Company code (optional, collapsible) -->
                <?php if (!$brandFirm): ?>
                <div class="company-section" id="company-section">
                    <div class="company-toggle" onclick="toggleCompany()">
                        <span><i class="bi bi-building me-1"></i>Şirket Kodu <span class="text-muted fw-normal">(opsiyonel)</span></span>
                        <i class="bi bi-chevron-down" id="company-chevron"></i>
                    </div>
                    <div id="company-fields" style="display:none;margin-top:10px">
                        <div class="auth-input">
                            <span class="ai-icon"><i class="bi bi-building"></i></span>
                            <input type="text" name="company_code" id="companyCodeInput"
                                   class="font-monospace text-uppercase"
                                   maxlength="50" placeholder="FIRMA-KODU"
                                   value="<?= htmlspecialchars($_GET['kod'] ?? '') ?>"
                                   oninput="this.value=this.value.toUpperCase()">
                            <button type="button" class="ai-btn" onclick="applyBrand()" title="Markalı sayfaya git">
                                <i class="bi bi-arrow-right-circle"></i>
                            </button>
                        </div>
                        <div style="font-size:.72rem;color:#9aaabb;margin-top:6px;">
                            <i class="bi bi-info-circle me-1"></i>Şirket kodunuzu girerek markalı giriş sayfasına geçin.
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <button type="submit" class="auth-submit">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Giriş Yap
                </button>
            </form>

            <div class="auth-divider">veya</div>

            <div class="auth-footer">
                Hesabınız yok mu?
                <a href="/kayit<?= $brandFirm ? '?kod=' . urlencode($brandFirm['company_code']) : '' ?>">
                    Ücretsiz Kayıt Ol →
                </a>
            </div>

            <?php if ($brandFirm): ?>
            <div class="auth-footer" style="margin-top:12px;">
                <a href="/giris" style="color:#9aaabb;font-weight:400;font-size:.75rem;">
                    <i class="bi bi-x-circle me-1"></i>Firma markalamasını kaldır
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function switchMode(mode) {
    document.getElementById('login_mode').value = mode;
    var isTC = (mode === 'tc');
    document.getElementById('fields-email').style.display = isTC ? 'none' : '';
    document.getElementById('fields-tc').style.display   = isTC ? '' : 'none';
    document.getElementById('tab-email').classList.toggle('active', !isTC);
    document.getElementById('tab-tc').classList.toggle('active', isTC);
    // Focus
    setTimeout(function() {
        var el = isTC ? document.getElementById('inp-tc') : document.getElementById('inp-email');
        if (el) el.focus();
    }, 50);
}

function togglePwd() {
    var inp = document.getElementById('inp-pass');
    var eye = document.getElementById('pwd-eye');
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        eye.className = 'bi bi-eye';
    }
}

var _companyOpen = <?= (!empty($_GET['kod']) || !empty($_POST['company_code'])) ? 'true' : 'false' ?>;
function toggleCompany() {
    _companyOpen = !_companyOpen;
    document.getElementById('company-fields').style.display = _companyOpen ? '' : 'none';
    document.getElementById('company-chevron').className = 'bi bi-chevron-' + (_companyOpen ? 'up' : 'down');
}
// Auto-open if code present
if (_companyOpen) toggleCompany();

function applyBrand() {
    var code = document.getElementById('companyCodeInput').value.trim();
    if (code) window.location.href = '/giris?kod=' + encodeURIComponent(code);
}

// TC No — digits only
document.getElementById('inp-tc').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
});

// Form validation
document.getElementById('login-form').addEventListener('submit', function(e) {
    var mode = document.getElementById('login_mode').value;
    if (mode === 'tc') {
        var tc = document.getElementById('inp-tc').value.replace(/\D/g, '');
        if (tc.length !== 11) {
            e.preventDefault();
            document.getElementById('inp-tc').focus();
            alert('TC kimlik numarası 11 haneli olmalıdır.');
        }
    }
});
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
