<?php require __DIR__ . '/../_layout.php'; ?>
<?php if (!empty($flash['msg'])): ?>
<div class="alert alert-<?= $flash['type']??'info' ?> alert-dismissible fade show mb-0 rounded-0">
  <?= htmlspecialchars($flash['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php
$dtStart = new DateTime($sess['scheduled_at']);
$dtEnd   = (clone $dtStart)->modify('+' . $sess['duration_minutes'] . ' minutes');
$now     = new DateTime();
$statusLabels = [
  'scheduled' => ['info',    'bi-calendar-event-fill', 'Planlandı'],
  'active'    => ['success', 'bi-play-circle-fill',    'Aktif'],
  'completed' => ['secondary','bi-check-circle-fill',  'Tamamlandı'],
  'cancelled' => ['danger',  'bi-x-circle-fill',       'İptal Edildi'],
];
[$sColor, $sIcon, $sLabel] = $statusLabels[$sess['status']] ?? ['secondary','bi-circle','?'];
$completed  = count(array_filter($attendees, fn($a) => $a['completed']));
?>

<div class="container-fluid py-4">
  <!-- Header -->
  <div class="d-flex flex-wrap align-items-start gap-2 mb-4">
    <a href="/admin/yyz" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Geri</a>
    <div class="flex-grow-1">
      <h4 class="fw-bold mb-0"><?= htmlspecialchars($sess['title']) ?></h4>
      <div class="d-flex flex-wrap gap-2 mt-1">
        <span class="badge text-bg-<?=$sColor?> d-flex align-items-center gap-1">
          <i class="bi <?=$sIcon?>"></i> <?=$sLabel?>
        </span>
        <span class="text-muted small"><i class="bi bi-book me-1"></i><?= htmlspecialchars($sess['course_title']) ?></span>
        <?php if ($sess['firm_name']): ?>
        <span class="text-muted small"><i class="bi bi-building me-1"></i><?= htmlspecialchars($sess['firm_name']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="/admin/yyz/<?=$sess['id']?>/pdf" class="btn btn-sm btn-outline-dark" target="_blank">
        <i class="bi bi-filetype-pdf me-1"></i>Yoklama PDF
      </a>
      <a href="/admin/yyz/<?=$sess['id']?>/duzenle" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-pencil me-1"></i>Düzenle
      </a>
    </div>
  </div>

  <div class="row g-4">
    <!-- Sol: QR + Bilgiler -->
    <div class="col-lg-4">

      <!-- QR Kodu -->
      <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
        <div class="card-header fw-semibold"><i class="bi bi-qr-code me-2"></i>QR Kod & Ders Kodu</div>
        <div class="card-body text-center p-3">
          <div id="qrBox" class="mb-3 d-inline-block" style="max-width:240px;">
            <?= $qrSvg ?>
          </div>
          <div class="mb-2">
            <div class="fw-bold text-muted" style="font-size:.72rem; letter-spacing:.5px; text-transform:uppercase;">Ders Kodu</div>
            <div class="font-monospace fw-black" style="font-size:2rem; color:var(--isg-primary); letter-spacing:.2em;">
              <?= htmlspecialchars($sess['attendance_code']) ?>
            </div>
          </div>
          <div style="font-size:.75rem; color:#888; word-break:break-all; background:#f8f9fa; border-radius:8px; padding:.5rem;">
            <?= htmlspecialchars($attendUrl) ?>
          </div>
          <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($attendUrl) ?>').then(()=>this.textContent='✓ Kopyalandı')"
                  class="btn btn-sm btn-outline-primary mt-2 w-100">
            <i class="bi bi-link-45deg me-1"></i>Katılım Linkini Kopyala
          </button>
        </div>
      </div>

      <!-- Oturum Detayları -->
      <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
        <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>Oturum Bilgileri</div>
        <div class="card-body p-3" style="font-size:.86rem;">
          <table class="table table-sm mb-0">
            <tr><th class="text-muted fw-normal">Tarih</th><td class="fw-semibold"><?= $dtStart->format('d.m.Y') ?></td></tr>
            <tr><th class="text-muted fw-normal">Başlangıç</th><td class="fw-semibold"><?= $dtStart->format('H:i') ?></td></tr>
            <tr><th class="text-muted fw-normal">Süre</th><td><?= $sess['duration_minutes'] ?> dk</td></tr>
            <tr><th class="text-muted fw-normal">Bitiş</th><td><?= $dtEnd->format('H:i') ?></td></tr>
            <?php if ($sess['location']): ?>
            <tr><th class="text-muted fw-normal">Konum</th><td><?= htmlspecialchars($sess['location']) ?></td></tr>
            <?php endif; ?>
            <?php if ($sess['tr_first']): ?>
            <tr><th class="text-muted fw-normal">Eğitmen</th><td><?= htmlspecialchars($sess['tr_first'].' '.$sess['tr_last']) ?></td></tr>
            <?php endif; ?>
            <?php if ($sess['max_participants']): ?>
            <tr><th class="text-muted fw-normal">Kapasite</th><td><?= $sess['max_participants'] ?></td></tr>
            <?php endif; ?>
          </table>
        </div>
      </div>

      <!-- Durum Değiştir -->
      <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
        <div class="card-header fw-semibold"><i class="bi bi-toggle-on me-2"></i>Durum Yönetimi</div>
        <div class="card-body p-3">
          <div class="d-grid gap-2">
            <?php $statusActions = [
              'active'    => ['success','bi-play-circle','Oturumu Başlat'],
              'scheduled' => ['info',   'bi-clock','Planlandı Yap'],
              'completed' => ['secondary','bi-check-circle','Tamamlandı İşaretle'],
              'cancelled' => ['danger', 'bi-x-circle','İptal Et'],
            ];
            foreach ($statusActions as $st => [$clr,$ico,$lbl]):
              if ($st === $sess['status']) continue; ?>
            <form method="POST" action="/admin/yyz/<?=$sess['id']?>/durum" onsubmit="return confirm('Durumu değiştirmek istediğinizden emin misiniz?')">
              <input type="hidden" name="status" value="<?=$st?>">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']??'' ?>">
              <button class="btn btn-outline-<?=$clr?> btn-sm w-100">
                <i class="bi <?=$ico?> me-1"></i><?=$lbl?>
              </button>
            </form>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Manuel Katılımcı Ekle -->
      <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header fw-semibold"><i class="bi bi-person-plus me-2"></i>Manuel Ekle</div>
        <div class="card-body p-3">
          <form method="POST" action="/admin/yyz/<?=$sess['id']?>/ekle">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']??'' ?>">
            <div class="input-group">
              <input type="email" name="email" class="form-control form-control-sm" placeholder="kullanici@email.com" required>
              <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-plus-lg"></i></button>
            </div>
          </form>
        </div>
      </div>

    </div>

    <!-- Sağ: Canlı Yoklama -->
    <div class="col-lg-8">

      <!-- İstatistikler -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
            <div id="stat-total" class="fw-black" style="font-size:2rem; color:var(--isg-primary);"><?= count($attendees) ?></div>
            <div class="text-muted" style="font-size:.75rem;">Toplam Katılımcı</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
            <div id="stat-completed" class="fw-black" style="font-size:2rem; color:#198754;"><?= $completed ?></div>
            <div class="text-muted" style="font-size:.75rem;">Eğitim Tamamladı</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
            <div class="fw-black" style="font-size:2rem; color:#ffc107;">
              <?= count(array_filter($attendees, fn($a)=>$a['join_method']==='qr')) ?>
            </div>
            <div class="text-muted" style="font-size:.75rem;">QR ile Katıldı</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
            <div class="fw-black" style="font-size:2rem; color:#0dcaf0;">
              <?= count(array_filter($attendees, fn($a)=>$a['join_method']==='code')) ?>
            </div>
            <div class="text-muted" style="font-size:.75rem;">Kod ile Katıldı</div>
          </div>
        </div>
      </div>

      <!-- Live Attendance Table -->
      <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header d-flex align-items-center justify-content-between">
          <span class="fw-semibold"><i class="bi bi-people-fill me-2"></i>Katılımcı Listesi</span>
          <div class="d-flex align-items-center gap-2">
            <span id="live-indicator" class="badge text-bg-success d-flex align-items-center gap-1" style="font-size:.7rem;">
              <span style="width:6px;height:6px;border-radius:50%;background:#fff;display:inline-block;animation:pulse-green 1.5s infinite;"></span>
              Canlı
            </span>
            <span class="text-muted" style="font-size:.75rem;" id="last-refresh">—</span>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" id="attendTable">
              <thead>
                <tr>
                  <th>Ad Soyad</th>
                  <th>E-posta</th>
                  <th>Katılım</th>
                  <th>Yöntem</th>
                  <th>Durum</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="attendBody">
                <?php foreach ($attendees as $a): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($a['last_name'].' '.$a['first_name']) ?></td>
                  <td class="text-muted" style="font-size:.83rem;"><?= htmlspecialchars($a['email']) ?></td>
                  <td style="font-size:.8rem;"><?= $a['joined_at'] ? date('H:i:s', strtotime($a['joined_at'])) : '—' ?></td>
                  <td>
                    <?php $m = $a['join_method'] ?? 'code'; ?>
                    <span class="badge text-bg-<?= $m==='qr'?'warning':($m==='admin'?'dark':'info') ?>">
                      <?= strtoupper($m) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($a['completed']): ?>
                      <span class="badge text-bg-success"><i class="bi bi-patch-check-fill me-1"></i>Tamamladı</span>
                    <?php else: ?>
                      <span class="badge text-bg-primary">Katıldı</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <form method="POST" action="/admin/yyz/<?=$sess['id']?>/kaldir" class="d-inline"
                          onsubmit="return confirm('Katılımcıyı listeden çıkarmak istiyor musunuz?')">
                      <input type="hidden" name="user_id" value="<?=$a['uid']?>">
                      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']??'' ?>">
                      <button class="btn btn-link btn-sm text-danger p-0" title="Kaldır"><i class="bi bi-trash3"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($attendees)): ?>
                <tr id="emptyRow">
                  <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-hourglass-split me-2"></i>Henüz katılım yok
                  </td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Completion question preview -->
      <?php if ($sess['completion_question']): ?>
      <div class="card border-0 shadow-sm mt-3" style="border-radius:12px; border-left:4px solid #ffc107!important;">
        <div class="card-body p-3">
          <div class="fw-semibold mb-2"><i class="bi bi-patch-question me-2 text-warning"></i>Tamamlama Sorusu (önizleme)</div>
          <p class="mb-2" style="font-size:.9rem;"><?= htmlspecialchars($sess['completion_question']) ?></p>
          <?php $opts = json_decode($sess['completion_options']??'[]',true)??[];
          $letters = ['A','B','C','D'];
          foreach ($opts as $i => $opt): if (!$opt) continue; ?>
          <div style="font-size:.85rem;" class="<?= ($sess['completion_answer']===($letters[$i]??''))?'text-success fw-bold':'' ?>">
            <?= $letters[$i]??'?' ?>. <?= htmlspecialchars($opt) ?>
            <?php if ($sess['completion_answer']===($letters[$i]??'')): ?> <i class="bi bi-check-circle-fill"></i><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const sessId = <?= $sess['id'] ?>;
let pollInterval;

function formatTime(iso) {
    if (!iso) return '—';
    const d = new Date(iso.replace(' ','T'));
    return d.toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
}

function refreshAttendance() {
    fetch(`/admin/yyz/${sessId}/canli`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.count;
            const completed = data.rows.filter(r=>r.completed).length;
            document.getElementById('stat-completed').textContent = completed;

            const tbody = document.getElementById('attendBody');
            if (data.count === 0) {
                tbody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-hourglass-split me-2"></i>Henüz katılım yok</td></tr>';
            } else {
                tbody.innerHTML = data.rows.map(r => {
                    const mBadge = r.join_method==='qr' ? 'warning' : (r.join_method==='admin'?'dark':'info');
                    const status = r.completed
                        ? '<span class="badge text-bg-success"><i class="bi bi-patch-check-fill me-1"></i>Tamamladı</span>'
                        : '<span class="badge text-bg-primary">Katıldı</span>';
                    return `<tr>
                        <td class="fw-semibold">${r.last_name} ${r.first_name}</td>
                        <td class="text-muted" style="font-size:.83rem;">${r.email}</td>
                        <td style="font-size:.8rem;">${formatTime(r.joined_at)}</td>
                        <td><span class="badge text-bg-${mBadge}">${r.join_method.toUpperCase()}</span></td>
                        <td>${status}</td>
                        <td></td>
                    </tr>`;
                }).join('');
            }
            document.getElementById('last-refresh').textContent = 'Son: ' + new Date().toLocaleTimeString('tr-TR');
        })
        .catch(() => {});
}

// Auto-refresh every 5 seconds
pollInterval = setInterval(refreshAttendance, 5000);
</script>

<style>
@keyframes pulse-green { 0%,100%{opacity:1}50%{opacity:.4} }
</style>

<?php require __DIR__ . '/../_layout_end.php'; ?>
