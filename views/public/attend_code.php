<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ders Kodu ile Katıl — İSG Eğitim Platformu</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/assets/css/isg.css">
<style>
body { background:linear-gradient(135deg,#002f52,#005695); min-height:100vh; }
.code-card { max-width:420px; width:100%; border-radius:20px; }
.code-input { font-size:1.8rem; font-weight:800; letter-spacing:.3em; text-align:center; text-transform:uppercase; }
</style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar py-2" style="background:rgba(0,0,0,.2);">
  <div class="container-fluid px-4 d-flex align-items-center gap-2">
    <a href="/" class="text-white text-decoration-none d-flex align-items-center gap-2">
      <i class="bi bi-shield-fill-check" style="color:#f5c518;"></i>
      <span class="fw-bold" style="font-size:.95rem;">İSG Eğitim Platformu</span>
    </a>
    <div class="ms-auto">
      <a href="/giris" class="btn btn-sm btn-warning px-3 fw-semibold text-dark">Giriş Yap</a>
    </div>
  </div>
</nav>

<div class="flex-grow-1 d-flex align-items-center justify-content-center p-3">
  <div class="code-card bg-white shadow-lg">
    <div class="p-4 text-center border-bottom" style="background:linear-gradient(135deg,#005695,#0091ce); border-radius:20px 20px 0 0;">
      <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
        <i class="bi bi-qr-code-scan text-white" style="font-size:2rem;"></i>
      </div>
      <h4 class="fw-bold text-white mb-1">Yüz Yüze Eğitime Katıl</h4>
      <p class="mb-0" style="color:rgba(255,255,255,.75); font-size:.85rem;">Eğitmeninizin verdiği 6 haneli ders kodunu girin</p>
    </div>
    <div class="p-4">
      <?php if ($error): ?>
        <div class="alert alert-danger mb-3" style="border-radius:10px;">
          <i class="bi bi-x-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <form method="POST" action="/attend">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="mb-4">
          <input type="text" name="attendance_code"
                 class="form-control code-input"
                 maxlength="10" minlength="4"
                 required autocomplete="off" spellcheck="false"
                 placeholder="ABC123"
                 oninput="this.value=this.value.toUpperCase()">
          <div class="text-muted text-center mt-1" style="font-size:.78rem;">
            Büyük/küçük harf fark etmez
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold" style="border-radius:12px;">
          <i class="bi bi-arrow-right-circle me-2"></i>Katılıma Devam Et
        </button>
      </form>
      <hr class="my-3">
      <div class="text-center" style="font-size:.82rem; color:#888;">
        QR kod okutarak da katılabilirsiniz — kameranzı QR koda tutun
      </div>
    </div>
    <div class="p-3 border-top text-center text-muted" style="font-size:.75rem; border-radius:0 0 20px 20px; background:#f8f9fa;">
      <i class="bi bi-shield-fill-check me-1" style="color:#005695;"></i>
      İSG Eğitim Platformu — 6331 Sayılı Kanun
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
