<?php
$pageTitle = 'Kayıt Ol — ' . APP_NAME;
$bodyClass = 'isg-auth-page';
include __DIR__ . '/../_layout.php';
$brandFirm = $brandFirm ?? null;
?>
<style>
/* ── Reset layout for auth page ───────────────────────────────────── */
body.isg-auth-page { background: #f0f4f8; margin: 0; }
body.isg-auth-page main { padding: 0 !important; min-height: 100vh; }

.auth-split { display: flex; min-height: 100vh; }

/* Left hero */
.auth-hero {
    flex: 0 0 52%;
    background: linear-gradient(155deg, #00254a 0%, #004a86 55%, #006bb5 100%);
    position: relative;
    display: flex; flex-direction: column; justify-content: center;
    padding: 60px 52px;
    overflow: hidden; color: #fff;
}
.auth-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 500px 600px at 120% 80%, rgba(0,200,150,.07) 0%, transparent 65%),
        radial-gradient(ellipse 400px 300px at -10% 20%, rgba(100,180,255,.07) 0%, transparent 65%);
}
.hero-deco { position: absolute; border-radius: 50%; background: rgba(255,255,255,.04); }
.hero-deco-1 { width: 480px; height: 480px; top: -100px; right: -160px; }
.hero-deco-2 { width: 240px; height: 240px; bottom: -40px; left: -70px; }

.hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(8px); border-radius: 24px;
    padding: 6px 16px; font-size: .78rem; font-weight: 600;
    color: rgba(255,255,255,.9); margin-bottom: 28px; width: fit-content;
}

.hero-steps {
    display: flex; flex-direction: column; gap: 16px;
    margin-top: 40px;
}
.hero-step {
    display: flex; align-items: flex-start; gap: 16px;
}
.step-num {
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,.12);
    border: 1.5px solid rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700; color: #ffd700;
    flex-shrink: 0; margin-top: 2px;
}
.step-body .step-title { font-size: .9rem; font-weight: 700; color: #fff; margin-bottom: 2px; }
.step-body .step-desc { font-size: .78rem; color: rgba(255,255,255,.55); line-height: 1.4; }

.hero-trust {
    margin-top: 44px; display: flex; gap: 10px; flex-wrap: wrap;
}
.hero-trust-item {
    display: flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.08); border-radius: 8px;
    padding: 6px 12px; font-size: .75rem;
    color: rgba(255,255,255,.7); font-weight: 500;
}

/* Form panel */
.auth-form-panel {
    flex: 1; display: flex; align-items: center;
    justify-content: center; background: #f7f9fc;
    padding: 32px 24px;
}
.auth-card {
    width: 100%; max-width: 460px;
    background: #fff; border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0,50,100,.08), 0 1px 4px rgba(0,0,0,.04);
    padding: 36px 36px;
}

.auth-card-logo {
    display: flex; align-items: center; gap: 10px; margin-bottom: 24px;
}
.auth-card-logo .logo-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #005695, #0096d6);
    border-radius: 12px; display: flex; align-items: center;
    justify-content: center; font-size: 1.3rem; color: #fff;
    box-shadow: 0 4px 12px rgba(0,86,149,.3); flex-shrink: 0;
}
.auth-card-logo .logo-text { font-size: .82rem; font-weight: 700; color: #1a2e4a; line-height: 1.2; }
.auth-card-logo .logo-sub  { font-size: .68rem; color: #7a8a9a; }

.auth-heading { font-size: 1.35rem; font-weight: 800; color: #0d1b2e; margin-bottom: 4px; letter-spacing: -.02em; }
.auth-heading-sub { font-size: .83rem; color: #6b7c93; margin-bottom: 24px; }

/* Field grid */
.reg-grid { display: grid; gap: 14px; }
.reg-grid .col-half { grid-column: span 1; }
.reg-grid-2 { grid-template-columns: 1fr 1fr; }

.auth-field-group label {
    font-size: .78rem; font-weight: 700; color: #3d5166;
    display: block; margin-bottom: 6px;
}
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
.auth-input .ai-icon { padding: 0 10px; color: #9aaabb; font-size: .95rem; flex-shrink: 0; }
.auth-input input {
    flex: 1; border: none; background: transparent;
    padding: 10px 10px 10px 0; font-size: .88rem;
    color: #1a2e4a; outline: none; min-width: 0;
}
.auth-input input::placeholder { color: #b0bec5; }
.auth-input .ai-btn {
    background: none; border: none; padding: 0 10px;
    color: #9aaabb; cursor: pointer; font-size: .9rem; line-height: 1;
}
.auth-input .ai-btn:hover { color: #005695; }

/* TC field highlight */
.auth-input.tc-field { border-color: #bbdefb; background: #f0f7ff; }
.auth-input.tc-field:focus-within { border-color: #005695; background: #fff; }

.field-hint { font-size: .7rem; color: #9aaabb; margin-top: 4px; display: flex; align-items: center; gap: 4px; }

.tc-badge {
    background: linear-gradient(90deg, #005695, #0072b5);
    color: #fff; font-size: .62rem; font-weight: 700;
    padding: 2px 6px; border-radius: 4px; letter-spacing: .04em;
}

/* Collapsible sections */
.section-card {
    background: #f7f9fc; border: 1.5px dashed #dde4ee;
    border-radius: 10px; padding: 12px 14px; margin-top: 4px;
}
.section-toggle {
    display: flex; align-items: center; justify-content: space-between;
    cursor: pointer; font-size: .8rem; font-weight: 600; color: #6b7c93;
    user-select: none;
}
.section-toggle:hover { color: #005695; }

.auth-submit {
    width: 100%; padding: 13px;
    background: linear-gradient(135deg, #005695, #0072b5);
    color: #fff; border: none; border-radius: 12px;
    font-size: .95rem; font-weight: 700; cursor: pointer;
    letter-spacing: .01em; margin-top: 20px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 4px 16px rgba(0,86,149,.35);
    transition: opacity .2s, transform .1s;
}
.auth-submit:hover { opacity: .92; }
.auth-submit:active { transform: scale(.98); }

.auth-error {
    background: #fef2f2; border: 1px solid #fecaca;
    border-radius: 10px; padding: 10px 14px;
    font-size: .82rem; color: #dc2626;
    display: flex; align-items: center; gap: 8px; margin-bottom: 16px;
}

.auth-footer { text-align: center; margin-top: 18px; font-size: .82rem; color: #7a8a9a; }
.auth-footer a { color: #005695; font-weight: 700; text-decoration: none; }
.auth-footer a:hover { text-decoration: underline; }

/* Password strength bar */
.pwd-strength { height: 3px; border-radius: 2px; margin-top: 6px; background: #edf1f6; overflow: hidden; }
.pwd-strength-fill { height: 100%; border-radius: 2px; width: 0%; transition: width .3s, background .3s; }

@media (max-width: 820px) { .reg-grid-2 { grid-template-columns: 1fr; } }
@media (max-width: 768px) {
    .auth-hero { display: none; }
    .auth-form-panel { background: #fff; padding: 20px 16px; }
    .auth-card { box-shadow: none; background: transparent; padding: 16px 0; }
}
</style>

<div class="auth-split">
    <!-- ── Left Hero Panel ─────────────────────────────────────── -->
    <div class="auth-hero">
        <div class="hero-deco hero-deco-1"></div>
        <div class="hero-deco hero-deco-2"></div>

        <div style="position:relative;z-index:1;">
            <a href="/" style="text-decoration:none;">
            <div class="hero-badge">
                <i class="bi bi-patch-check-fill" style="color:#ffd700"></i>
                ISG Akademi
            </div>
            </a>

            <?php if ($brandFirm && $brandFirm['logo_path'] && file_exists(LOGO_DIR . $brandFirm['logo_path'])): ?>
            <div style="margin-bottom:20px;">
                <img src="<?= LOGO_URL . htmlspecialchars($brandFirm['logo_path']) ?>"
                     alt="<?= htmlspecialchars($brandFirm['name']) ?>" style="max-height:64px;max-width:200px">
            </div>
            <h2 style="font-size:1.75rem;font-weight:800;margin-bottom:10px;letter-spacing:-.02em;">
                <?= htmlspecialchars($brandFirm['header_title'] ?: $brandFirm['name']) ?>
            </h2>
            <p style="color:rgba(255,255,255,.65);font-size:.95rem;line-height:1.6;max-width:340px;margin-bottom:36px;">
                Şirketinizin eğitim portalına kayıt olun ve İSG eğitimlerinizi hemen başlatın.
            </p>
            <?php else: ?>
            <h2 style="font-size:2rem;font-weight:800;margin-bottom:10px;line-height:1.2;letter-spacing:-.02em;">
                İSG Eğitimine <span style="color:#ffd700">Bugün Başlayın</span>
            </h2>
            <p style="color:rgba(255,255,255,.65);font-size:.95rem;line-height:1.6;max-width:360px;margin-bottom:36px;">
                Dakikalar içinde hesabınızı oluşturun, sertifikalı eğitimlere erişin.
            </p>
            <?php endif; ?>

            <div class="hero-steps">
                <div class="hero-step">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <div class="step-title">Bilgilerinizi Girin</div>
                        <div class="step-desc">Ad, soyad, e-posta ve TC kimlik numaranız ile kayıt olun.</div>
                    </div>
                </div>
                <div class="hero-step">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <div class="step-title">Eğitimlere Erişin</div>
                        <div class="step-desc">Yüzlerce İSG eğitim modülüne anında erişim kazanın.</div>
                    </div>
                </div>
                <div class="hero-step">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <div class="step-title">Sertifikanızı Alın</div>
                        <div class="step-desc">Sınavları geçin, akredite sertifikanızı dijital olarak indirin.</div>
                    </div>
                </div>
            </div>

            <div class="hero-trust">
                <div class="hero-trust-item"><i class="bi bi-shield-lock-fill" style="color:#7dd8ff"></i> Güvenli Kayıt</div>
                <div class="hero-trust-item"><i class="bi bi-award-fill" style="color:#ffd700"></i> Akredite</div>
                <div class="hero-trust-item"><i class="bi bi-person-check-fill" style="color:#7dffb0"></i> KVKK Uyumlu</div>
            </div>
        </div>
    </div>

    <!-- ── Right Form Panel ────────────────────────────────────── -->
    <div class="auth-form-panel">
        <div class="auth-card">
            <?php if (!$brandFirm): ?>
            <a href="/" style="text-decoration:none;color:inherit;">
            <div class="auth-card-logo">
                <div class="logo-icon"><i class="bi bi-person-plus"></i></div>
                <div>
                    <div class="logo-text">Yeni Hesap Oluştur</div>
                    <div class="logo-sub">ISG Akademi</div>
                </div>
            </div>
            </a>
            <?php else: ?>
            <div style="text-align:center;margin-bottom:20px;">
                <div style="width:48px;height:48px;background:linear-gradient(135deg,#005695,#0096d6);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;box-shadow:0 4px 12px rgba(0,86,149,.3);">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div style="font-size:.82rem;font-weight:700;color:#1a2e4a;margin-top:8px;"><?= htmlspecialchars($brandFirm['header_title'] ?: $brandFirm['name']) ?></div>
            </div>
            <?php endif; ?>

            <div class="auth-heading">Hesap Oluştur</div>
            <div class="auth-heading-sub">Birkaç saniyede ücretsiz kaydolun</div>

            <?php if ($error): ?>
            <div class="auth-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/kayit">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <?php if ($brandFirm): ?>
                <input type="hidden" name="company_code" value="<?= htmlspecialchars($brandFirm['company_code']) ?>">
                <?php endif; ?>

                <!-- Name row -->
                <div class="reg-grid reg-grid-2" style="margin-bottom:14px;">
                    <div class="auth-field-group col-half">
                        <label for="r-first">Ad</label>
                        <div class="auth-input">
                            <span class="ai-icon"><i class="bi bi-person"></i></span>
                            <input type="text" id="r-first" name="first_name" placeholder="Adınız" required
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" autocomplete="given-name">
                        </div>
                    </div>
                    <div class="auth-field-group col-half">
                        <label for="r-last">Soyad</label>
                        <div class="auth-input">
                            <span class="ai-icon"><i class="bi bi-person"></i></span>
                            <input type="text" id="r-last" name="last_name" placeholder="Soyadınız" required
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" autocomplete="family-name">
                        </div>
                    </div>
                </div>

                <!-- E-posta -->
                <div class="auth-field-group" style="margin-bottom:14px;">
                    <label for="r-email">E-posta Adresi</label>
                    <div class="auth-input">
                        <span class="ai-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="r-email" name="email" placeholder="ornek@firma.com" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="email">
                    </div>
                </div>

                <!-- Phone + TC row -->
                <div class="reg-grid reg-grid-2" style="margin-bottom:14px;">
                    <div class="auth-field-group col-half">
                        <label for="r-phone">Telefon</label>
                        <div class="auth-input">
                            <span class="ai-icon"><i class="bi bi-phone"></i></span>
                            <input type="tel" id="r-phone" name="phone" placeholder="05xx xxx xx xx"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" autocomplete="tel">
                        </div>
                    </div>
                    <div class="auth-field-group col-half">
                        <label for="r-tc">
                            TC Kimlik No
                            <span class="tc-badge">TC</span>
                        </label>
                        <div class="auth-input tc-field">
                            <span class="ai-icon"><i class="bi bi-person-badge"></i></span>
                            <input type="text" id="r-tc" name="tc_identity_no"
                                   placeholder="_ _ _ _ _ _ _ _ _ _ _"
                                   maxlength="11" inputmode="numeric" pattern="\d{11}"
                                   value="<?= htmlspecialchars($_POST['tc_identity_no'] ?? '') ?>"
                                   autocomplete="off" style="letter-spacing:.1em;font-weight:600">
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="auth-field-group" style="margin-bottom:14px;">
                    <label for="r-pass">Şifre <span style="font-weight:400;color:#9aaabb">(en az 6 karakter)</span></label>
                    <div class="auth-input">
                        <span class="ai-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" id="r-pass" name="password" placeholder="••••••••" required minlength="6"
                               autocomplete="new-password" oninput="updateStrength(this.value)">
                        <button type="button" class="ai-btn" onclick="toggleRPwd()" title="Göster/gizle">
                            <i class="bi bi-eye" id="r-pwd-eye"></i>
                        </button>
                    </div>
                    <div class="pwd-strength"><div class="pwd-strength-fill" id="pwd-fill"></div></div>
                    <div class="field-hint" id="pwd-hint" style="display:none;">
                        <i class="bi bi-info-circle"></i> <span id="pwd-hint-text"></span>
                    </div>
                </div>

                <!-- Firm/Group codes -->
                <?php if (!$brandFirm): ?>
                <div class="section-card" style="margin-bottom:14px;">
                    <div class="section-toggle" onclick="toggleSection('codes-body','codes-chev')">
                        <span><i class="bi bi-key me-1"></i> Şirket Kodu &amp; Grup Anahtarı <span style="font-weight:400;color:#b0bec5">(opsiyonel)</span></span>
                        <i class="bi bi-chevron-down" id="codes-chev"></i>
                    </div>
                    <div id="codes-body" style="display:none;margin-top:12px;flex-direction:column;gap:10px;">
                        <div class="auth-field-group" style="margin-bottom:0">
                            <label for="r-company">Şirket Kodu</label>
                            <div class="auth-input">
                                <span class="ai-icon"><i class="bi bi-building"></i></span>
                                <input type="text" id="r-company" name="company_code" placeholder="FIRMA-KODU"
                                       class="font-monospace" maxlength="50"
                                       value="<?= htmlspecialchars(strtoupper($_POST['company_code'] ?? $_GET['kod'] ?? '')) ?>"
                                       oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase">
                            </div>
                            <div class="field-hint"><i class="bi bi-info-circle"></i> Firmanıza otomatik kayıt olun.</div>
                        </div>
                        <div class="auth-field-group" style="margin-bottom:0">
                            <label for="r-gkey">Grup Anahtarı</label>
                            <div class="auth-input">
                                <span class="ai-icon"><i class="bi bi-person-badge-fill"></i></span>
                                <input type="text" id="r-gkey" name="group_key_code" placeholder="GRUP-ANAHTARI"
                                       class="font-monospace" maxlength="50"
                                       value="<?= htmlspecialchars(strtoupper($_POST['group_key_code'] ?? '')) ?>"
                                       oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase">
                            </div>
                            <div class="field-hint"><i class="bi bi-info-circle"></i> Eğitim sorumlusunuzdan alınan anahtar.</div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-field-group" style="margin-bottom:14px;">
                    <label for="r-gkey2">Grup Anahtarı <span style="font-weight:400;color:#9aaabb">(opsiyonel)</span></label>
                    <div class="auth-input">
                        <span class="ai-icon"><i class="bi bi-person-badge-fill"></i></span>
                        <input type="text" id="r-gkey2" name="group_key_code" placeholder="GRUP-ANAHTARI"
                               class="font-monospace" maxlength="50"
                               value="<?= htmlspecialchars(strtoupper($_POST['group_key_code'] ?? '')) ?>"
                               oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase">
                    </div>
                    <div class="field-hint"><i class="bi bi-info-circle"></i> Eğitim sorumlusunuzdan alınan anahtar ile kurslara otomatik kayıt.</div>
                </div>
                <?php endif; ?>

                <button type="submit" class="auth-submit">
                    <i class="bi bi-check-circle-fill"></i>
                    Hesap Oluştur
                </button>
            </form>

            <div class="auth-footer" style="margin-top:18px;">
                Zaten hesabınız var mı?
                <a href="/giris<?= $brandFirm ? '?kod=' . urlencode($brandFirm['company_code']) : '' ?>">
                    Giriş Yapın →
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// TC digits only
document.getElementById('r-tc').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
});

// Password strength
function updateStrength(val) {
    var fill = document.getElementById('pwd-fill');
    var hint = document.getElementById('pwd-hint');
    var hintText = document.getElementById('pwd-hint-text');
    if (!val) { fill.style.width = '0%'; hint.style.display = 'none'; return; }
    var score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/\d/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var pct   = [0, 20, 45, 65, 82, 100][score];
    var color = ['#e0e0e0','#ef4444','#f59e0b','#eab308','#22c55e','#16a34a'][score];
    var label = ['','Çok Zayıf','Zayıf','Orta','Güçlü','Çok Güçlü'][score];
    fill.style.width = pct + '%';
    fill.style.background = color;
    hintText.textContent = label;
    hint.style.display = score > 0 ? '' : 'none';
    hint.style.color = color;
}

// Toggle password visibility
function toggleRPwd() {
    var inp = document.getElementById('r-pass');
    var eye = document.getElementById('r-pwd-eye');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    eye.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Collapsible section
function toggleSection(bodyId, chevId) {
    var body = document.getElementById(bodyId);
    var chev = document.getElementById(chevId);
    var open = body.style.display === 'flex';
    body.style.display = open ? 'none' : 'flex';
    if (chev) chev.className = 'bi bi-chevron-' + (open ? 'down' : 'up');
}

// Auto-open codes section if pre-filled
(function() {
    var company = document.getElementById('r-company');
    if (company && company.value) toggleSection('codes-body', 'codes-chev');
})();
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
