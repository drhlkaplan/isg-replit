<?php require __DIR__ . '/../_layout.php'; ?>
<?php if (!empty($flash['msg'])): ?>
<div class="alert alert-<?= $flash['type']??'info' ?> alert-dismissible fade show mb-0 rounded-0">
  <?= htmlspecialchars($flash['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h4 class="fw-bold mb-0"><i class="bi bi-person-video2 me-2 text-primary"></i>Yüz Yüze Eğitimlerim</h4>
      <small class="text-muted">Zorunlu işyerine özgü yüz yüze oturumlara katılım durumunuz</small>
    </div>
    <a href="/attend" class="btn btn-primary">
      <i class="bi bi-qr-code-scan me-1"></i>Ders Kodu ile Katıl
    </a>
  </div>

  <!-- Bilgi kartı -->
  <div class="alert alert-info d-flex gap-2 mb-4" style="border-radius:10px;">
    <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
    <div style="font-size:.88rem;">
      <strong>Yüz yüze eğitim nedir?</strong> 6331 sayılı İş Sağlığı ve Güvenliği Kanunu'na göre tehlikeli ve çok tehlikeli işyerlerinde
      "İşe ve İşyerine Özgü Konular" modülü <strong>yüz yüze</strong> tamamlanmak zorundadır.
      Eğitmeninizin size verdiği <strong>6 haneli ders kodunu</strong> veya QR kodu okutarak katılımınızı kaydedin.
    </div>
  </div>

  <?php if (empty($sessions)): ?>
    <div class="isg-empty-state">
      <i class="bi bi-person-video2"></i>
      <h5>Henüz planlanmış oturum yok</h5>
      <p>Eğitmeniniz veya firmanız bir oturum planladığında burada görünecek.<br>
         Ders kodunuz varsa hemen katılabilirsiniz.</p>
      <a href="/attend" class="btn btn-primary mt-2">Ders Kodu ile Katıl</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($sessions as $s):
        $dtStart = new DateTime($s['scheduled_at']);
        $dtEnd   = (clone $dtStart)->modify('+' . $s['duration_minutes'] . ' minutes');
        $now     = new DateTime();
        $attended = !empty($s['joined_at']);
        $completed = !empty($s['completed']);

        if ($s['status'] === 'completed' || $completed) {
            $cardBorder = '#198754'; $cardBg = '#f0fff4';
        } elseif ($attended) {
            $cardBorder = '#0d6efd'; $cardBg = '#f0f7ff';
        } elseif ($s['status'] === 'active') {
            $cardBorder = '#198754'; $cardBg = '#fff';
        } elseif ($s['status'] === 'cancelled') {
            $cardBorder = '#dc3545'; $cardBg = '#fff5f5';
        } else {
            $cardBorder = '#dee2e6'; $cardBg = '#fff';
        }
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid <?=$cardBorder?> !important; background:<?=$cardBg?>;">
          <div class="card-body p-3">
            <!-- Status badges -->
            <div class="d-flex flex-wrap gap-1 mb-2">
              <?php if ($completed): ?>
                <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Tamamlandı</span>
              <?php elseif ($attended): ?>
                <span class="badge text-bg-primary"><i class="bi bi-check me-1"></i>Katıldım</span>
              <?php elseif ($s['status'] === 'active'): ?>
                <span class="badge text-bg-success"><i class="bi bi-broadcast me-1"></i>Aktif — Katılabilirsin</span>
              <?php elseif ($s['status'] === 'scheduled'): ?>
                <span class="badge text-bg-info"><i class="bi bi-calendar-event me-1"></i>Planlandı</span>
              <?php elseif ($s['status'] === 'cancelled'): ?>
                <span class="badge text-bg-danger">İptal Edildi</span>
              <?php else: ?>
                <span class="badge text-bg-secondary">Tamamlandı</span>
              <?php endif; ?>
            </div>

            <h6 class="fw-bold lh-sm mb-1"><?= htmlspecialchars($s['title']) ?></h6>
            <div class="text-muted mb-2" style="font-size:.78rem;"><?= htmlspecialchars($s['course_title']) ?></div>

            <div class="row g-1 mb-3" style="font-size:.79rem; color:#555;">
              <div class="col-6"><i class="bi bi-calendar3 me-1"></i><?= $dtStart->format('d.m.Y') ?></div>
              <div class="col-6"><i class="bi bi-clock me-1"></i><?= $dtStart->format('H:i') ?></div>
              <?php if ($s['location']): ?>
              <div class="col-12"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['location']) ?></div>
              <?php endif; ?>
              <?php if ($s['firm_name']): ?>
              <div class="col-12"><i class="bi bi-building me-1"></i><?= htmlspecialchars($s['firm_name']) ?></div>
              <?php endif; ?>
            </div>

            <?php if ($attended && !$completed && $s['completion_question']): ?>
              <a href="/attend/<?= $s['qr_token'] ?>" class="btn btn-sm btn-warning w-100">
                <i class="bi bi-patch-question me-1"></i>Tamamlama Sorusunu Cevapla
              </a>
            <?php elseif (!$attended && $s['status'] === 'active'): ?>
              <a href="/attend/<?= $s['qr_token'] ?>" class="btn btn-sm btn-success w-100">
                <i class="bi bi-qr-code-scan me-1"></i>Katılımımı Kaydet
              </a>
            <?php elseif (!$attended && $s['status'] === 'scheduled'): ?>
              <div class="text-muted text-center" style="font-size:.8rem;">
                <i class="bi bi-clock me-1"></i>Eğitim başladığında katılabilirsiniz
              </div>
            <?php elseif ($completed): ?>
              <div class="text-success text-center" style="font-size:.84rem; font-weight:600;">
                <i class="bi bi-patch-check-fill me-1"></i>Eğitim tamamlandı
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../_layout_end.php'; ?>
