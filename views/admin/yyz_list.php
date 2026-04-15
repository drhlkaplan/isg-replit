<?php require __DIR__ . '/../_layout.php'; ?>
<?php if (!empty($flash['msg'])): ?>
<div class="alert alert-<?= $flash['type'] ?? 'info' ?> alert-dismissible fade show mb-0 rounded-0" role="alert">
  <?= htmlspecialchars($flash['msg']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h4 class="fw-bold mb-0"><i class="bi bi-person-video2 me-2 text-primary"></i>Yüz Yüze Eğitim Oturumları</h4>
      <small class="text-muted">6331 Sayılı Kanun — Madde 12/3: Tehlikeli ve Çok Tehlikeli İşyerleri İçin Zorunlu</small>
    </div>
    <a href="/admin/yyz/yeni" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Yeni Oturum
    </a>
  </div>

  <!-- Filters -->
  <form method="GET" class="row g-2 mb-4">
    <div class="col-auto">
      <select name="durum" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Tüm Durumlar</option>
        <?php foreach (['scheduled'=>'Planlandı','active'=>'Aktif','completed'=>'Tamamlandı','cancelled'=>'İptal'] as $v=>$l): ?>
          <option value="<?=$v?>" <?= $filterStatus===$v?'selected':'' ?>><?=$l?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <select name="firma" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Tüm Firmalar</option>
        <?php foreach ($firms as $f): ?>
          <option value="<?=$f['id']?>" <?= $filterFirm==$f['id']?'selected':'' ?>><?= htmlspecialchars($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <a href="/admin/yyz" class="btn btn-outline-secondary btn-sm">Temizle</a>
    </div>
  </form>

  <?php if (empty($sessions)): ?>
    <div class="isg-empty-state">
      <i class="bi bi-person-video2"></i>
      <h5>Henüz oturum yok</h5>
      <p>Yüz yüze eğitim oturumu oluşturmak için "Yeni Oturum" butonuna tıklayın.</p>
      <a href="/admin/yyz/yeni" class="btn btn-primary mt-2">Yeni Oturum Oluştur</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($sessions as $s):
        $statusConfig = [
          'scheduled'  => ['info',    'bi-calendar-event',  'Planlandı'],
          'active'     => ['success', 'bi-play-circle-fill','Aktif'],
          'completed'  => ['secondary','bi-check-circle',   'Tamamlandı'],
          'cancelled'  => ['danger',  'bi-x-circle',        'İptal'],
        ][$s['status']] ?? ['secondary','bi-circle','?'];
        $dtStart = new DateTime($s['scheduled_at']);
        $isPast  = $dtStart < new DateTime();
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid var(--bs-<?=$statusConfig[0]?>) !important;">
          <div class="card-body p-3">
            <div class="d-flex align-items-start gap-2 mb-2">
              <span class="badge text-bg-<?=$statusConfig[0]?> d-flex align-items-center gap-1" style="font-size:.72rem;">
                <i class="bi <?=$statusConfig[1]?>"></i> <?=$statusConfig[2]?>
              </span>
              <div class="ms-auto text-muted" style="font-size:.75rem;">
                <i class="bi bi-people-fill"></i> <?=(int)$s['attendee_count']?>
                <?php if ($s['max_participants']): ?>/<?=$s['max_participants']?><?php endif; ?>
              </div>
            </div>
            <h6 class="fw-bold mb-1 lh-sm"><?= htmlspecialchars($s['title']) ?></h6>
            <div class="text-muted" style="font-size:.78rem;"><?= htmlspecialchars($s['course_title']) ?></div>
            <hr class="my-2">
            <div class="row g-1" style="font-size:.78rem; color:#555;">
              <div class="col-6"><i class="bi bi-calendar3 me-1"></i><?= $dtStart->format('d.m.Y') ?></div>
              <div class="col-6"><i class="bi bi-clock me-1"></i><?= $dtStart->format('H:i') ?> (<?=$s['duration_minutes']?>dk)</div>
              <?php if ($s['location']): ?>
              <div class="col-12"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['location']) ?></div>
              <?php endif; ?>
              <?php if ($s['firm_name']): ?>
              <div class="col-12"><i class="bi bi-building me-1"></i><?= htmlspecialchars($s['firm_name']) ?></div>
              <?php endif; ?>
            </div>
            <div class="d-flex gap-2 mt-3">
              <a href="/admin/yyz/<?=$s['id']?>" class="btn btn-sm btn-primary flex-fill">
                <i class="bi bi-eye me-1"></i>Detay
              </a>
              <a href="/admin/yyz/<?=$s['id']?>/pdf" class="btn btn-sm btn-outline-secondary" title="Yoklama PDF">
                <i class="bi bi-filetype-pdf"></i>
              </a>
              <a href="/admin/yyz/<?=$s['id']?>/duzenle" class="btn btn-sm btn-outline-primary" title="Düzenle">
                <i class="bi bi-pencil"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../_layout_end.php'; ?>
