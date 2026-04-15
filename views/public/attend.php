<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Yüz Yüze Eğitim Katılımı — İSG Eğitim Platformu</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/assets/css/isg.css">
<style>
.attend-hero { background:linear-gradient(135deg,#002f52,#005695); min-height:100vh; }
.attend-card { border-radius:20px; max-width:520px; width:100%; }
</style>
</head>
<body class="m-0 p-0">
<?php
$dtStart  = new DateTime($sess['scheduled_at']);
$dtEnd    = (clone $dtStart)->modify('+' . $sess['duration_minutes'] . ' minutes');
$now      = new DateTime();
$isActive = in_array($sess['status'], ['scheduled','active']) && !$beforeStart && !$afterEnd;
?>

<div class="attend-hero d-flex flex-column min-vh-100">
  <!-- Navbar -->
  <nav class="navbar py-2" style="background:rgba(0,0,0,.2);">
    <div class="container-fluid px-4 d-flex align-items-center gap-2">
      <a href="/" class="text-white text-decoration-none d-flex align-items-center gap-2">
        <i class="bi bi-shield-fill-check" style="color:#f5c518;"></i>
        <span class="fw-bold" style="font-size:.95rem;">İSG Eğitim Platformu</span>
      </a>
      <div class="ms-auto">
        <?php if ($loggedIn): ?>
          <a href="/ogrenci" class="btn btn-sm btn-outline-light px-3">Panelim</a>
        <?php else: ?>
          <a href="/giris" class="btn btn-sm btn-warning px-3 fw-semibold text-dark">Giriş Yap</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Flash -->
  <?php if (!empty($flash['msg'])): ?>
  <div class="alert alert-<?= $flash['type']??'info' ?> alert-dismissible mb-0 rounded-0 fade show" role="alert">
    <div class="container"><?= htmlspecialchars($flash['msg']) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <!-- Main card -->
  <div class="flex-grow-1 d-flex align-items-center justify-content-center p-3">
    <div class="attend-card bg-white shadow-lg">

      <!-- Session header -->
      <div class="p-4 border-bottom" style="background:linear-gradient(135deg,#005695,#0091ce); border-radius:20px 20px 0 0; color:#fff;">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="bi bi-person-video2 fs-5"></i>
          <span style="font-size:.82rem; opacity:.8;">Yüz Yüze Eğitim Oturumu</span>
          <?php if ($sess['status']==='active'): ?>
            <span class="badge ms-auto" style="background:rgba(255,255,255,.2); font-size:.7rem;">
              <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#4ade80;margin-right:4px;animation:pulse-green 1.5s infinite;"></span>Aktif
            </span>
          <?php endif; ?>
        </div>
        <h4 class="fw-bold mb-1 lh-sm"><?= htmlspecialchars($sess['title']) ?></h4>
        <div style="font-size:.82rem; opacity:.78;"><?= htmlspecialchars($sess['course_title']) ?></div>
      </div>

      <div class="p-4">
        <!-- Session info pills -->
        <div class="d-flex flex-wrap gap-2 mb-4">
          <span class="badge text-bg-light border" style="font-size:.8rem;">
            <i class="bi bi-calendar3 me-1"></i><?= $dtStart->format('d.m.Y') ?>
          </span>
          <span class="badge text-bg-light border" style="font-size:.8rem;">
            <i class="bi bi-clock me-1"></i><?= $dtStart->format('H:i') ?> – <?= $dtEnd->format('H:i') ?>
          </span>
          <?php if ($sess['location']): ?>
          <span class="badge text-bg-light border" style="font-size:.8rem;">
            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($sess['location']) ?>
          </span>
          <?php endif; ?>
          <?php if ($sess['tr_first']): ?>
          <span class="badge text-bg-light border" style="font-size:.8rem;">
            <i class="bi bi-person me-1"></i><?= htmlspecialchars($sess['tr_first'].' '.$sess['tr_last']) ?>
          </span>
          <?php endif; ?>
        </div>

        <?php if (!$loggedIn): ?>
          <!-- Not logged in -->
          <div class="alert alert-warning d-flex gap-2 mb-3" style="border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
            <div style="font-size:.88rem;">Katılımınızı kaydetmek için önce giriş yapmanız gerekiyor.</div>
          </div>
          <a href="/giris?redirect=<?= urlencode('/attend/'.$token) ?>" class="btn btn-primary w-100 btn-lg fw-bold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap ve Katıl
          </a>

        <?php elseif ($existingAttendance && $existingAttendance['completed']): ?>
          <!-- Already completed -->
          <div class="text-center py-3">
            <div style="width:72px;height:72px;border-radius:50%;background:#d1fadf;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
              <i class="bi bi-patch-check-fill text-success" style="font-size:2rem;"></i>
            </div>
            <h5 class="fw-bold text-success">Eğitim Tamamlandı!</h5>
            <p class="text-muted" style="font-size:.88rem;">Bu oturuma katılımınız ve tamamlama sorunuz kaydedilmiştir.</p>
            <a href="/ogrenci/yyz" class="btn btn-outline-primary mt-2">Yüz Yüze Eğitimlerime Dön</a>
          </div>

        <?php elseif ($existingAttendance && !$existingAttendance['completed'] && $sess['completion_question']): ?>
          <!-- Attended, show completion question -->
          <div class="alert alert-success d-flex gap-2 mb-3" style="border-radius:10px;">
            <i class="bi bi-check-circle-fill flex-shrink-0 mt-1"></i>
            <div style="font-size:.88rem;">Katılımınız kaydedildi! Aşağıdaki soruyu cevaplayarak eğitimi tamamlayın.</div>
          </div>

          <div class="card border-0 bg-light mb-3" style="border-radius:12px;">
            <div class="card-body p-3">
              <p class="fw-semibold mb-3"><?= htmlspecialchars($sess['completion_question']) ?></p>
              <form method="POST" action="/attend/<?= htmlspecialchars($token) ?>/tamamla">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <?php
                $opts = json_decode($sess['completion_options']??'[]',true)??[];
                $letters = ['A','B','C','D'];
                foreach ($opts as $i => $opt): if (!$opt) continue; ?>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="radio" name="answer" id="opt<?=$letters[$i]?>" value="<?=$letters[$i]?>" required>
                  <label class="form-check-label" for="opt<?=$letters[$i]?>">
                    <strong><?=$letters[$i]?>.</strong> <?= htmlspecialchars($opt) ?>
                  </label>
                </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-success w-100 mt-3 fw-bold">
                  <i class="bi bi-check-lg me-2"></i>Cevabı Gönder
                </button>
              </form>
            </div>
          </div>

        <?php elseif ($existingAttendance): ?>
          <!-- Attended, no question -->
          <div class="text-center py-2">
            <div style="width:64px;height:64px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
              <i class="bi bi-check-circle-fill text-primary" style="font-size:1.8rem;"></i>
            </div>
            <h5 class="fw-bold">Katılımınız Kaydedildi</h5>
            <p class="text-muted" style="font-size:.85rem;">
              Saat <?= date('H:i', strtotime($existingAttendance['joined_at'])) ?> itibarıyla kaydınız alındı.
            </p>
            <a href="/ogrenci/yyz" class="btn btn-outline-primary">Yüz Yüze Eğitimlerime Dön</a>
          </div>

        <?php elseif ($beforeStart): ?>
          <!-- Too early -->
          <div class="text-center py-3">
            <i class="bi bi-hourglass text-warning" style="font-size:3rem;"></i>
            <h5 class="fw-bold mt-2">Eğitim Henüz Başlamadı</h5>
            <p class="text-muted" style="font-size:.88rem;">
              Katılım <?= $dtStart->format('d.m.Y H:i') ?> itibarıyla açılacak.
              Eğitime başlamadan 15 dakika önce tekrar gelin.
            </p>
          </div>

        <?php elseif ($afterEnd && !$existingAttendance): ?>
          <!-- Too late -->
          <div class="text-center py-3">
            <i class="bi bi-clock-history text-danger" style="font-size:3rem;"></i>
            <h5 class="fw-bold mt-2 text-danger">Kayıt Süresi Doldu</h5>
            <p class="text-muted" style="font-size:.88rem;">
              Bu oturum <?= $dtEnd->format('H:i') ?> itibarıyla sona erdi. Katılım kaydı yapılamıyor.
            </p>
          </div>

        <?php elseif ($sess['status'] === 'cancelled'): ?>
          <div class="alert alert-danger text-center">
            <i class="bi bi-x-circle-fill me-2"></i>Bu oturum iptal edildi.
          </div>

        <?php else: ?>
          <!-- Join button -->
          <div class="text-center mb-3">
            <form method="POST" action="/attend/<?= htmlspecialchars($token) ?>">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <input type="hidden" name="via_qr" value="1">
              <button type="submit" class="btn btn-success btn-lg fw-bold px-5 w-100" style="border-radius:14px; padding:1rem;">
                <i class="bi bi-check-circle-fill me-2"></i>Katılımımı Kaydet
              </button>
            </form>
            <div class="text-muted mt-2" style="font-size:.8rem;">
              Adınıza katılım kaydı oluşturulacak
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Footer -->
      <div class="p-3 border-top text-center text-muted" style="font-size:.75rem; border-radius:0 0 20px 20px; background:#f8f9fa;">
        <i class="bi bi-shield-fill-check me-1" style="color:#005695;"></i>
        İSG Eğitim Platformu — 6331 Sayılı Kanun
      </div>
    </div>
  </div>
</div>

<style>
@keyframes pulse-green { 0%,100%{opacity:1}50%{opacity:.4} }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
