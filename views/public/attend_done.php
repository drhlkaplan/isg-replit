<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Eğitim Tamamlandı — İSG Eğitim Platformu</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/assets/css/isg.css">
<style>
body { background:linear-gradient(135deg,#002f52,#005695); min-height:100vh; }
</style>
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar py-2" style="background:rgba(0,0,0,.2);">
  <div class="container-fluid px-4">
    <a href="/" class="text-white text-decoration-none d-flex align-items-center gap-2">
      <i class="bi bi-shield-fill-check" style="color:#f5c518;"></i>
      <span class="fw-bold" style="font-size:.95rem;">İSG Eğitim Platformu</span>
    </a>
  </div>
</nav>

<div class="flex-grow-1 d-flex align-items-center justify-content-center p-3">
  <div class="bg-white shadow-lg p-5 text-center" style="border-radius:20px; max-width:440px; width:100%;">
    <div style="width:80px;height:80px;border-radius:50%;background:#d1fadf;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;animation:pop .4s ease-out;">
      <i class="bi bi-patch-check-fill text-success" style="font-size:2.5rem;"></i>
    </div>
    <h3 class="fw-bold mb-2">Tebrikler!</h3>
    <p class="text-muted mb-1">Yüz yüze eğitiminiz başarıyla tamamlandı.</p>
    <div class="fw-semibold mb-4"><?= htmlspecialchars($sess['title']) ?></div>

    <?php if (!empty($myAttendance) && $myAttendance['completion_answer']): ?>
    <div class="alert alert-success mb-3" style="border-radius:12px; font-size:.88rem;">
      <i class="bi bi-check-circle me-1"></i>
      Cevabınız <strong><?= htmlspecialchars($myAttendance['completion_answer']) ?></strong> olarak kaydedildi.
    </div>
    <?php endif; ?>

    <div class="d-grid gap-2">
      <a href="/ogrenci/yyz" class="btn btn-primary btn-lg fw-bold" style="border-radius:12px;">
        <i class="bi bi-house me-2"></i>Eğitimlerime Dön
      </a>
      <a href="/" class="btn btn-outline-secondary" style="border-radius:12px;">Ana Sayfa</a>
    </div>
  </div>
</div>
<style>
@keyframes pop { 0%{transform:scale(.5);opacity:0} 80%{transform:scale(1.1)} 100%{transform:scale(1);opacity:1} }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
