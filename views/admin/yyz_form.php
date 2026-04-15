<?php require __DIR__ . '/../_layout.php';
$editing = !empty($sess['id']);
$title   = $editing ? 'Oturum Düzenle' : 'Yeni Yüz Yüze Oturum';
$action  = $editing ? "/admin/yyz/{$sess['id']}/duzenle" : '/admin/yyz/yeni';
?>
<?php if (!empty($flash['msg'])): ?>
<div class="alert alert-<?= $flash['type']??'info' ?> alert-dismissible fade show mb-0 rounded-0">
  <?= htmlspecialchars($flash['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger mb-0 rounded-0"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="container py-4" style="max-width:820px;">
  <div class="d-flex align-items-center gap-2 mb-4">
    <a href="/admin/yyz" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Geri</a>
    <h4 class="fw-bold mb-0"><i class="bi bi-person-video2 me-2 text-primary"></i><?= $title ?></h4>
  </div>

  <form method="POST" action="<?= $action ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
      <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>Temel Bilgiler</div>
      <div class="card-body p-4">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Başlık <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required maxlength="255"
                   value="<?= htmlspecialchars($sess['title'] ?? '') ?>" placeholder="Örn: Tehlikeli İşyerleri Yüz Yüze ISG — Mayıs 2026">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Yüz Yüze Kurs <span class="text-danger">*</span></label>
            <select name="course_id" class="form-select" required>
              <option value="">Kurs seçin...</option>
              <?php foreach ($courses as $c): ?>
              <option value="<?=$c['id']?>" <?= ($sess['course_id']??'')==$c['id']?'selected':'' ?>>
                <?= htmlspecialchars($c['title']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Firma</label>
            <select name="firm_id" class="form-select">
              <option value="">Firma bağlantısı yok</option>
              <?php foreach ($firms as $f): ?>
              <option value="<?=$f['id']?>" <?= ($sess['firm_id']??'')==$f['id']?'selected':'' ?>>
                <?= htmlspecialchars($f['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Eğitmen</label>
            <select name="trainer_id" class="form-select">
              <option value="">Eğitmen belirtilmedi</option>
              <?php foreach ($trainers as $t): ?>
              <option value="<?=$t['id']?>" <?= ($sess['trainer_id']??'')==$t['id']?'selected':'' ?>>
                <?= htmlspecialchars($t['last_name'] . ' ' . $t['first_name']) ?> (<?= $t['email'] ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Konum / Yer</label>
            <input type="text" name="location" class="form-control" maxlength="255"
                   value="<?= htmlspecialchars($sess['location'] ?? '') ?>" placeholder="İşyeri adresi veya toplantı odası">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Başlangıç Tarihi ve Saati</label>
            <input type="datetime-local" name="scheduled_at" class="form-control" required
                   value="<?= $editing ? date('Y-m-d\TH:i', strtotime($sess['scheduled_at'])) : date('Y-m-d\TH:i', strtotime('+1 day')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Süre (dakika)</label>
            <input type="number" name="duration_minutes" class="form-control" min="30" max="600"
                   value="<?= $sess['duration_minutes'] ?? 120 ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Maks. Katılımcı</label>
            <input type="number" name="max_participants" class="form-control" min="1" max="500"
                   value="<?= $sess['max_participants'] ?? '' ?>" placeholder="Sınırsız">
          </div>
        </div>
      </div>
    </div>

    <!-- Tamamlama Sorusu -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
      <div class="card-header fw-semibold">
        <i class="bi bi-patch-question me-2"></i>Eğitim Sonu Zorunlu Soru
        <small class="text-muted fw-normal ms-2">Cevaptan bağımsız olarak katılım tamamlanmış sayılır</small>
      </div>
      <div class="card-body p-4">
        <div class="mb-3">
          <label class="form-label fw-semibold">Soru Metni</label>
          <textarea name="completion_question" class="form-control" rows="2" maxlength="500"
                    placeholder="Örn: Bu eğitimde öğrendiğiniz en önemli konuyu seçiniz."><?= htmlspecialchars($sess['completion_question'] ?? '') ?></textarea>
        </div>
        <div class="row g-2 mb-3">
          <?php foreach (['A','B','C','D'] as $letter):
            $key = 'opt_' . strtolower($letter);
            $val = $opts[array_search($letter, ['A','B','C','D'])] ?? ($opts[ord($letter)-ord('A')] ?? '');
          ?>
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text fw-bold" style="width:38px;"><?=$letter?></span>
              <input type="text" name="<?=$key?>" class="form-control" maxlength="200"
                     value="<?= htmlspecialchars($val) ?>" placeholder="<?=$letter?> şıkkı">
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div>
          <label class="form-label fw-semibold">Doğru Cevap</label>
          <select name="completion_answer" class="form-select" style="max-width:160px;">
            <option value="">Seçiniz</option>
            <?php foreach (['A','B','C','D'] as $l): ?>
            <option value="<?=$l?>" <?= ($sess['completion_answer']??'')===$l?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
      <div class="card-header fw-semibold"><i class="bi bi-sticky me-2"></i>Notlar</div>
      <div class="card-body p-4">
        <textarea name="notes" class="form-control" rows="3" placeholder="İç notlar, eğitim planı..."><?= htmlspecialchars($sess['notes'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i><?= $editing ? 'Güncelle' : 'Oturum Oluştur' ?>
      </button>
      <a href="/admin/yyz<?= $editing ? '/'.$sess['id'] : '' ?>" class="btn btn-outline-secondary">İptal</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../_layout_end.php'; ?>
