<?php
$catMap = [];
foreach ($categories as $cat) {
    $catMap[$cat['code']] = $cat;
}

$temel  = [];
$tekrar = [];
foreach ($packages as $pkg) {
    if ($pkg['training_type'] === 'temel')  $temel[]  = $pkg;
    if ($pkg['training_type'] === 'tekrar') $tekrar[] = $pkg;
}

$totalUsers = number_format((int)($stats['total_users'] ?? 0));
$totalCerts = number_format((int)($stats['certs_year'] ?? 0));
$totalFirms = number_format((int)($stats['total_firms'] ?? 0));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>İSG Eğitim Platformu — T.C. İş Sağlığı ve Güvenliği Eğitimleri</title>
<meta name="description" content="6331 sayılı İş Sağlığı ve Güvenliği Kanunu kapsamında zorunlu İSG eğitimlerini online alın. Az tehlikeli, tehlikeli ve çok tehlikeli işyerleri için temel ve tekrar eğitim paketleri.">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/assets/css/isg.css">
</head>
<body class="m-0 p-0" style="background:#fff; font-family:'Segoe UI',system-ui,sans-serif;">

<!-- ═══ NAVBAR ═══════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg isg-home-navbar py-2">
  <div class="container">
    <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="/">
      <i class="bi bi-shield-fill-check fs-5" style="color:#f5c518;"></i>
      İSG Eğitim Platformu
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#homeNav">
      <i class="bi bi-list text-white fs-4"></i>
    </button>
    <div class="collapse navbar-collapse" id="homeNav">
      <ul class="navbar-nav me-auto ms-3 gap-1">
        <li class="nav-item"><a class="nav-link" href="#egitimler">Eğitimler</a></li>
        <li class="nav-item"><a class="nav-link" href="#yonetmelik">Yönetmelik</a></li>
        <li class="nav-item"><a class="nav-link" href="#nasil-calisir">Nasıl Çalışır?</a></li>
        <li class="nav-item"><a class="nav-link" href="/dogrula">Sertifika Sorgula</a></li>
      </ul>
      <div class="d-flex gap-2 mt-2 mt-lg-0">
        <a href="/giris" class="btn btn-outline-light btn-sm px-3">
          <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
        </a>
        <a href="/kayit" class="btn btn-warning btn-sm px-3 fw-semibold text-dark">
          <i class="bi bi-person-plus me-1"></i>Kayıt Ol
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- ═══ HERO ════════════════════════════════════════════ -->
<section class="isg-hero py-5">
  <div class="container position-relative" style="z-index:2;">
    <div class="row align-items-center g-5">

      <div class="col-lg-7">
        <div class="isg-hero-badge mb-3">
          <span class="dot"></span>
          Yeni 2026 Yönetmeliği Yürürlükte
        </div>

        <h1 class="mb-3">
          Zorunlu <span class="accent">İSG Eğitimlerini</span><br>
          Online Tamamlayın
        </h1>

        <p class="lead mb-4">
          6331 Sayılı Kanun kapsamında işverenler için zorunlu olan işçi sağlığı ve güvenliği eğitimlerini,
          tehlike sınıfınıza göre uygun paket ile %100 online olarak tamamlayın.
          Eğitim sonunda <strong style="color:#f5c518;">yasal geçerli sertifika</strong> edinin.
        </p>

        <div class="d-flex flex-wrap gap-3 mb-5">
          <a href="/giris" class="btn btn-warning btn-lg fw-bold px-4 shadow">
            <i class="bi bi-play-circle me-2"></i>Eğitime Başla
          </a>
          <a href="/kayit" class="btn btn-outline-light btn-lg px-4">
            <i class="bi bi-person-plus me-2"></i>Ücretsiz Kayıt
          </a>
          <a href="#egitimler" class="btn btn-light btn-lg px-4 text-primary">
            <i class="bi bi-grid-3x3-gap me-2"></i>Eğitim Paketleri
          </a>
        </div>

        <!-- Hızlı istatistikler -->
        <div class="row g-3">
          <div class="col-4">
            <div class="isg-hero-stat">
              <div class="big"><?= $totalUsers ?>+</div>
              <div class="lbl">Kayıtlı Kullanıcı</div>
            </div>
          </div>
          <div class="col-4">
            <div class="isg-hero-stat">
              <div class="big"><?= $totalCerts ?>+</div>
              <div class="lbl">Verilen Sertifika</div>
            </div>
          </div>
          <div class="col-4">
            <div class="isg-hero-stat">
              <div class="big"><?= $totalFirms ?>+</div>
              <div class="lbl">Kurumsal Firma</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5 d-none d-lg-block">
        <div class="isg-hero-visual">
          <div class="isg-shield-wrap">
            <i class="bi bi-shield-fill-check"></i>
          </div>
          <div class="isg-shield-ring"></div>
          <!-- Floating badges -->
          <div style="position:absolute; top:10%; right:0; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25); border-radius:12px; padding:.6rem 1rem; backdrop-filter:blur(4px); color:#fff; font-size:.8rem; font-weight:600;">
            <i class="bi bi-award-fill me-1 text-warning"></i> Yasal Geçerli Sertifika
          </div>
          <div style="position:absolute; bottom:15%; left:0; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25); border-radius:12px; padding:.6rem 1rem; backdrop-filter:blur(4px); color:#fff; font-size:.8rem; font-weight:600;">
            <i class="bi bi-clock-fill me-1" style="color:#4ade80;"></i> 7/24 Erişim
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══ TRUST BAR ════════════════════════════════════════ -->
<div class="isg-trust-bar">
  <div class="container">
    <div class="row g-3 justify-content-center text-center">
      <div class="col-6 col-md-3">
        <div class="item justify-content-center">
          <i class="bi bi-check-circle-fill"></i>
          <span>6331 Sayılı Kanun Uyumlu</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="item justify-content-center">
          <i class="bi bi-shield-fill-check"></i>
          <span>SCORM 1.2 / 2004 İçerik</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="item justify-content-center">
          <i class="bi bi-award-fill"></i>
          <span>QR Kodlu PDF Sertifika</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="item justify-content-center">
          <i class="bi bi-building-fill-check"></i>
          <span>Kurumsal Firma Yönetimi</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ YÖNETMELİK HIGHLIGHT ════════════════════════════ -->
<section id="yonetmelik" class="isg-yonetmelik py-5">
  <div class="container">

    <div class="row align-items-center mb-4">
      <div class="col">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
          <div class="isg-yonetmelik-badge">
            <i class="bi bi-newspaper"></i>
            Yeni Yönetmelik Yürürlükte
          </div>
          <span class="badge text-bg-dark fw-normal" style="font-size:.72rem; border-radius:50px; padding:.28rem .8rem;">
            RG Sayı: 33212 · 2 Nisan 2026
          </span>
        </div>
        <h2 class="fw-bold fs-3 mb-1">Çalışanların İSG Eğitimlerinin Usul ve Esasları Hakkında Yönetmelik</h2>
        <p class="text-muted mb-0">
          Çalışma ve Sosyal Güvenlik Bakanlığı — 6331 Sayılı Kanun Md. 16, 17, 18 ve 30 kapsamında işverenler için zorunlu hükümler
        </p>
      </div>
      <div class="col-auto d-none d-md-block">
        <a href="/giris" class="btn btn-warning fw-semibold px-4">
          <i class="bi bi-journal-check me-1"></i>Eğitimlere Git
        </a>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-6 col-lg-4">
        <div class="isg-yonetmelik-item">
          <div class="ico"><i class="bi bi-person-fill-exclamation"></i></div>
          <div>
            <div class="fw-semibold" style="font-size:.9rem;">İşe Başlama Eğitimi Zorunlu <span class="badge text-bg-warning text-dark ms-1" style="font-size:.65rem;">Madde 7</span></div>
            <div class="text-muted" style="font-size:.8rem;">Tüm çalışanlar göreve fiilen başlamadan önce en az <strong>2 saatlik</strong> uygulamalı ve yüz yüze işe başlama eğitimi almak zorunda. Bu süre temel eğitimden sayılmaz.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="isg-yonetmelik-item">
          <div class="ico"><i class="bi bi-hourglass-split"></i></div>
          <div>
            <div class="fw-semibold" style="font-size:.9rem;">Temel Eğitim Süreleri <span class="badge text-bg-warning text-dark ms-1" style="font-size:.65rem;">Madde 13</span></div>
            <div class="text-muted" style="font-size:.8rem;">Her çalışana <strong>az tehlikeli</strong> işyerinde en az 8 ders saati, <strong>tehlikeli</strong>de 12 ders saati, <strong>çok tehlikeli</strong>de 16 ders saati temel eğitim verilmesi zorunlu.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="isg-yonetmelik-item">
          <div class="ico"><i class="bi bi-arrow-repeat"></i></div>
          <div>
            <div class="fw-semibold" style="font-size:.9rem;">Temel Eğitimin Tekrarlama Periyodu <span class="badge text-bg-warning text-dark ms-1" style="font-size:.65rem;">Madde 14</span></div>
            <div class="text-muted" style="font-size:.8rem;">Çok tehlikeli: <strong>yılda en az 1</strong> kez · Tehlikeli: <strong>2 yılda 1</strong> kez · Az tehlikeli: <strong>3 yılda 1</strong> kez tekrar eğitimi zorunlu.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="isg-yonetmelik-item">
          <div class="ico"><i class="bi bi-display"></i></div>
          <div>
            <div class="fw-semibold" style="font-size:.9rem;">Uzaktan Eğitim Kısıtlaması <span class="badge text-bg-warning text-dark ms-1" style="font-size:.65rem;">Madde 12</span></div>
            <div class="text-muted" style="font-size:.8rem;">1., 2. ve 3. konular online verilebilir. Ancak <strong>tehlikeli ve çok tehlikeli</strong> işyerlerinde "işyerine özgü" 4. konu başlığı <strong>sadece yüz yüze</strong> verilmek zorunda.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="isg-yonetmelik-item">
          <div class="ico"><i class="bi bi-shield-lock-fill"></i></div>
          <div>
            <div class="fw-semibold" style="font-size:.9rem;">Online Platform Gereksinimleri <span class="badge text-bg-warning text-dark ms-1" style="font-size:.65rem;">Madde 12/5</span></div>
            <div class="text-muted" style="font-size:.8rem;">Uzaktan eğitim platformu; <strong>ileri sarma ve sekme kapatmayı engellemeli</strong>, belirli aralıklarla açılır soru/bilgi penceresi göstermeli, giriş-çıkış ve tamamlanma oranlarını kayıt altına almalı.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="isg-yonetmelik-item">
          <div class="ico"><i class="bi bi-file-earmark-check-fill"></i></div>
          <div>
            <div class="fw-semibold" style="font-size:.9rem;">İşverenin Temel Yükümlülükleri <span class="badge text-bg-warning text-dark ms-1" style="font-size:.65rem;">Madde 5</span></div>
            <div class="text-muted" style="font-size:.8rem;">Eğitim maliyeti çalışanlara <strong>yansıtılamaz</strong>. Eğitimlerde geçen süre <strong>çalışma süresinden sayılır</strong>. Tehlikeli ve çok tehlikeli işyerlerinde belge olmaksızın başka işyerinden çalışan işe <strong>başlatılamaz</strong>.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 p-3 rounded-3" style="background:#fff3cd; border:1px solid #ffc107; font-size:.83rem;">
      <i class="bi bi-info-circle-fill me-2" style="color:#b8860b;"></i>
      <strong>Yönetmelik bilgisi:</strong> Bu bilgiler, Çalışma ve Sosyal Güvenlik Bakanlığı tarafından 2 Nisan 2026 tarih ve 33212 sayılı Resmî Gazete'de yayımlanan yönetmeliğe dayanmaktadır.
      Hukuki danışmanlık için bir İSG uzmanına başvurunuz.
    </div>

  </div>
</section>

<!-- ═══ EĞİTİM PAKETLERİ ══════════════════════════════════ -->
<section id="egitimler" class="isg-pkg-section py-5">
  <div class="container">

    <div class="text-center mb-5">
      <span class="badge text-bg-primary fw-semibold mb-2 px-3 py-2" style="border-radius:50px; font-size:.78rem;">
        <i class="bi bi-grid-3x3-gap me-1"></i>EĞİTİM PAKETLERİ
      </span>
      <h2 class="fw-bold fs-2 mb-2">Tehlike Sınıfına Göre Eğitimler</h2>
      <p class="text-muted mx-auto" style="max-width:540px;">
        İşyerinizin tehlike sınıfına ve eğitim türüne (temel / tekrar) göre uygun paketi seçin.
        Tüm paketleri görüntülemek için giriş yapın.
      </p>
    </div>

    <!-- TEHLİKE SINIFI AÇIKLAMASI -->
    <div class="row g-3 mb-5">
      <?php $cats = [
        ['code'=>'AZ', 'color'=>'#28a745', 'icon'=>'bi-emoji-smile',   'title'=>'Az Tehlikeli', 'desc'=>'Ofis, perakende, satış gibi düşük risk içeren işyerleri. Yıllık 8 saat eğitim.', 'ex'=>'Ofis, Mağaza, Banka...'],
        ['code'=>'TE', 'color'=>'#ffc107', 'icon'=>'bi-exclamation-triangle', 'title'=>'Tehlikeli', 'desc'=>'Kimyasal, gürültü veya makine riski bulunan işyerleri. Yıllık 12 saat eğitim.', 'ex'=>'İmalat, Depo, Güzellik...'],
        ['code'=>'CT', 'color'=>'#dc3545', 'icon'=>'bi-fire',          'title'=>'Çok Tehlikeli', 'desc'=>'Yüksek kaza riski içeren, madencilik veya ağır sanayi işyerleri. Yıllık 16 saat.', 'ex'=>'Mutfak, İnşaat, Maden...'],
      ]; foreach($cats as $c): ?>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px; border-top:4px solid <?= $c['color'] ?> !important;">
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <span style="width:40px;height:40px;border-radius:10px;background:<?= $c['color'] ?>20;display:flex;align-items:center;justify-content:center;">
                <i class="bi <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:1.2rem;"></i>
              </span>
              <div>
                <div class="fw-bold" style="color:<?= $c['color'] ?>;"><?= $c['title'] ?></div>
                <div class="text-muted" style="font-size:.73rem;"><?= $c['ex'] ?></div>
              </div>
            </div>
            <p class="text-muted mb-0" style="font-size:.84rem;"><?= $c['desc'] ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- TEMEL EĞİTİMLER -->
    <div class="mb-5">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="isg-cat-header" style="background:linear-gradient(135deg,#005695,#0091ce);">
          <i class="bi bi-book-fill"></i> Temel Eğitimler
        </div>
        <div class="text-muted" style="font-size:.84rem;">İlk kez İSG eğitimi alacaklar için başlangıç paketi</div>
      </div>
      <div class="row g-3">
        <?php foreach($temel as $pkg):
          $clr = $pkg['cat_color'] ?? '#005695';
          $hrs = round(($pkg['duration_minutes'] ?? 480) / 60);
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="card isg-pkg-card shadow-sm" style="border-top:4px solid <?= htmlspecialchars($clr) ?> !important;">
            <div class="card-body p-3">
              <div class="d-flex align-items-start gap-2 mb-2">
                <span style="width:34px;height:34px;border-radius:8px;background:<?= htmlspecialchars($clr) ?>18;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="bi bi-book-fill" style="color:<?= htmlspecialchars($clr) ?>;font-size:.95rem;"></i>
                </span>
                <div class="flex-grow-1">
                  <div class="fw-semibold lh-sm mb-1" style="font-size:.88rem;"><?= htmlspecialchars($pkg['title']) ?></div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="type-badge" style="background:<?= htmlspecialchars($clr) ?>18;color:<?= htmlspecialchars($clr) ?>;">
                      <?= htmlspecialchars($pkg['cat_code']) ?>
                    </span>
                    <span class="isg-pkg-duration"><i class="bi bi-clock me-1"></i><?= $hrs ?> Saat</span>
                  </div>
                </div>
              </div>
              <div class="text-end mt-2">
                <a href="/giris" class="btn btn-sm px-3 fw-semibold" style="background:<?= htmlspecialchars($clr) ?>;color:#fff;border-radius:8px;font-size:.78rem;">
                  <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- TEKRAR EĞİTİMLER -->
    <div class="mb-4">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="isg-cat-header" style="background:linear-gradient(135deg,#5a3e8c,#7b57b5);">
          <i class="bi bi-arrow-repeat"></i> Tekrar Eğitimleri
        </div>
        <div class="text-muted" style="font-size:.84rem;">Sertifika yenileme zorunluluğu doğanlar için 8 saatlik tazeleme eğitimi</div>
      </div>
      <div class="row g-3">
        <?php foreach($tekrar as $pkg):
          $clr = $pkg['cat_color'] ?? '#7b57b5';
          $hrs = round(($pkg['duration_minutes'] ?? 480) / 60);
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="card isg-pkg-card shadow-sm" style="border-top:4px solid <?= htmlspecialchars($clr) ?> !important;">
            <div class="card-body p-3">
              <div class="d-flex align-items-start gap-2 mb-2">
                <span style="width:34px;height:34px;border-radius:8px;background:<?= htmlspecialchars($clr) ?>18;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="bi bi-arrow-repeat" style="color:<?= htmlspecialchars($clr) ?>;font-size:.95rem;"></i>
                </span>
                <div class="flex-grow-1">
                  <div class="fw-semibold lh-sm mb-1" style="font-size:.88rem;"><?= htmlspecialchars($pkg['title']) ?></div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="type-badge" style="background:<?= htmlspecialchars($clr) ?>18;color:<?= htmlspecialchars($clr) ?>;">
                      <?= htmlspecialchars($pkg['cat_code']) ?>
                    </span>
                    <span class="isg-pkg-duration"><i class="bi bi-clock me-1"></i><?= $hrs ?> Saat</span>
                  </div>
                </div>
              </div>
              <div class="text-end mt-2">
                <a href="/giris" class="btn btn-sm px-3 fw-semibold" style="background:<?= htmlspecialchars($clr) ?>;color:#fff;border-radius:8px;font-size:.78rem;">
                  <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="text-center mt-4">
      <div class="alert alert-primary d-inline-flex align-items-center gap-2 px-4 py-3 border-0 shadow-sm" style="border-radius:14px; font-size:.9rem;">
        <i class="bi bi-lock-fill fs-5"></i>
        <span>Tüm eğitim paketlerine erişmek için
          <a href="/giris" class="fw-bold">giriş yapın</a> veya
          <a href="/kayit" class="fw-bold">ücretsiz kayıt olun</a>.
        </span>
      </div>
    </div>

  </div>
</section>

<!-- ═══ NASIL ÇALIŞIR ════════════════════════════════════ -->
<section id="nasil-calisir" class="py-5" style="background:#fff;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold fs-2 mb-2">Nasıl Çalışır?</h2>
      <p class="text-muted">4 adımda zorunlu İSG sertifikanızı edinin</p>
    </div>
    <div class="row g-4 position-relative">
      <?php $steps = [
        ['n'=>'1','ico'=>'bi-person-plus-fill','clr'=>'#005695','title'=>'Kayıt Ol',      'desc'=>'Ücretsiz hesap oluşturun. Firma kodunuz varsa firmaya otomatik bağlanırsınız.'],
        ['n'=>'2','ico'=>'bi-grid-3x3-gap-fill','clr'=>'#0072b5','title'=>'Paket Seç',    'desc'=>'Tehlike sınıfınıza ve eğitim türüne (temel/tekrar) göre uygun paketi seçin.'],
        ['n'=>'3','ico'=>'bi-play-btn-fill',    'clr'=>'#0091ce','title'=>'Eğitimi Al',   'desc'=>'Online modülleri izleyin. İlerlemeniz kaydedilir, istediğiniz an devam edebilirsiniz.'],
        ['n'=>'4','ico'=>'bi-award-fill',       'clr'=>'#f5c518','title'=>'Sertifika Al', 'desc'=>'Final sınavında %60 puan alın, QR kodlu PDF sertifikanızı indirin.'],
      ]; foreach ($steps as $i => $s): ?>
      <div class="col-md-6 col-lg-3">
        <div class="text-center px-2">
          <div class="isg-step-num mb-3" style="background:<?= $s['clr'] ?>;">
            <i class="bi <?= $s['ico'] ?>" style="font-size:1.3rem;"></i>
          </div>
          <h5 class="fw-bold mb-1"><?= $s['title'] ?></h5>
          <p class="text-muted" style="font-size:.88rem;"><?= $s['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ ÖZELLİKLER ══════════════════════════════════════ -->
<section class="py-5" style="background:#f4f6fa;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold fs-2 mb-2">Neden Bu Platform?</h2>
      <p class="text-muted">Yasal zorunlulukları kolayca karşılayan, modern ve güvenilir İSG eğitim çözümü</p>
    </div>
    <div class="row g-4">
      <?php $features = [
        ['ico'=>'bi-shield-fill-check', 'clr'=>'#005695', 'title'=>'Yasal Uyumluluk',      'desc'=>'6331 sayılı İSG Kanunu ve ilgili yönetmelikler kapsamında sertifikalı eğitimler.'],
        ['ico'=>'bi-laptop',            'clr'=>'#0072b5', 'title'=>'%100 Online',           'desc'=>'Dilediğiniz zaman, dilediğiniz cihazdan eğitime devam edin. İnternet bağlantısı yeterli.'],
        ['ico'=>'bi-buildings-fill',    'clr'=>'#28a745', 'title'=>'Kurumsal Yönetim',      'desc'=>'Firma panelinizden tüm çalışanların eğitim durumunu ve sertifika takibini yapın.'],
        ['ico'=>'bi-bar-chart-fill',    'clr'=>'#ffc107', 'title'=>'Detaylı Raporlama',     'desc'=>'Kim ne kadar ilerledi? Hangi sertifika yakında bitiyor? Tüm analizler elinizde.'],
        ['ico'=>'bi-qr-code-scan',      'clr'=>'#6f42c1', 'title'=>'Doğrulanabilir Sertifika','desc'=>'QR kodlu dijital PDF sertifikalar anlık sorgulanabilir. Denetimde kabul edilir.'],
        ['ico'=>'bi-headset',           'clr'=>'#dc3545', 'title'=>'Türkçe Destek',         'desc'=>'Tüm içerik, arayüz ve destek hizmeti Türkçe. Mevzuata uygun güncel içerik.'],
      ]; foreach ($features as $f): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
          <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
              <span style="width:48px;height:48px;border-radius:12px;background:<?= $f['clr'] ?>15;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi <?= $f['ico'] ?>" style="color:<?= $f['clr'] ?>;font-size:1.4rem;"></i>
              </span>
              <div>
                <h6 class="fw-bold mb-1"><?= $f['title'] ?></h6>
                <p class="text-muted mb-0" style="font-size:.85rem;"><?= $f['desc'] ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ CTA SECTION ══════════════════════════════════════ -->
<section class="isg-cta-section py-5">
  <div class="container text-center py-3">
    <h2 class="fw-bold fs-2 mb-2 text-white">Hemen Başlayın</h2>
    <p class="mb-4" style="color:rgba(255,255,255,.78); font-size:1.05rem; max-width:500px; margin:0 auto 1.5rem;">
      Zorunlu İSG eğitimlerini ertelemeyin. Kayıt olun, doğru paketi seçin ve bugün sertifikanızı alın.
    </p>
    <div class="d-flex flex-wrap gap-3 justify-content-center">
      <a href="/kayit" class="btn btn-warning btn-lg fw-bold px-5 shadow">
        <i class="bi bi-person-plus me-2"></i>Ücretsiz Kayıt Ol
      </a>
      <a href="/giris" class="btn btn-outline-light btn-lg px-5">
        <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
      </a>
    </div>
    <div class="mt-3" style="color:rgba(255,255,255,.5); font-size:.82rem;">
      Kurumsal kullanım için <a href="/giris" style="color:rgba(255,255,255,.7);">firma hesabı</a> oluşturun
    </div>
  </div>
</section>

<!-- ═══ FOOTER ═══════════════════════════════════════════ -->
<footer class="isg-home-footer">
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="bi bi-shield-fill-check" style="color:#f5c518; font-size:1.3rem;"></i>
          <span class="text-white fw-bold">İSG Eğitim Platformu</span>
        </div>
        <p style="font-size:.83rem; color:rgba(255,255,255,.45); max-width:280px;">
          T.C. İş Sağlığı ve Güvenliği mevzuatına uygun online eğitim ve sertifika platformu.
        </p>
      </div>
      <div class="col-6 col-lg-2">
        <div class="text-white fw-semibold mb-2" style="font-size:.88rem;">Platform</div>
        <ul class="list-unstyled mb-0" style="font-size:.82rem;">
          <li class="mb-1"><a href="/giris">Giriş Yap</a></li>
          <li class="mb-1"><a href="/kayit">Kayıt Ol</a></li>
          <li class="mb-1"><a href="/dogrula">Sertifika Sorgula</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <div class="text-white fw-semibold mb-2" style="font-size:.88rem;">Eğitimler</div>
        <ul class="list-unstyled mb-0" style="font-size:.82rem;">
          <li class="mb-1"><a href="/giris">Az Tehlikeli</a></li>
          <li class="mb-1"><a href="/giris">Tehlikeli</a></li>
          <li class="mb-1"><a href="/giris">Çok Tehlikeli</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <div class="text-white fw-semibold mb-2" style="font-size:.88rem;">Yasal Dayanak</div>
        <p style="font-size:.78rem; color:rgba(255,255,255,.4);">
          6331 Sayılı İş Sağlığı ve Güvenliği Kanunu · İSG Eğitimi ve Çalışan Temsilcisi Hakkında Yönetmelik (RG: 15.05.2013) · 2024 Revizyon Tebliği
        </p>
      </div>
    </div>
    <hr style="border-color:rgba(255,255,255,.1);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div style="font-size:.78rem;">© <?= date('Y') ?> İSG Eğitim Platformu — T.C. İş Sağlığı ve Güvenliği Eğitim Sistemi</div>
      <div style="font-size:.78rem;">
        <a href="/dogrula" class="me-3">Sertifika Doğrula</a>
        <a href="/giris">Giriş</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const el = document.querySelector(a.getAttribute('href'));
    if (el) { e.preventDefault(); el.scrollIntoView({behavior:'smooth', block:'start'}); }
  });
});
</script>
</body>
</html>
