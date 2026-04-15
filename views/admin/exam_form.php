<?php
$pageTitle = 'Yeni Sınav — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/admin/sinavlar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Yeni Sınav Oluştur</h4>
    </div>
    <div class="card shadow-sm" style="max-width:650px">
        <div class="card-body p-4">
            <form method="POST" action="/admin/sinavlar/ekle">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Sınav Adı *</label>
                        <input type="text" name="title" class="form-control" required placeholder="Örn: Ağır ve Tehlikeli Sektörler Final Sınavı">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Kurs *</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">— Kurs Seçin —</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Sınav Türü *</label>
                        <select name="exam_type" class="form-select">
                            <option value="final">Final Sınavı</option>
                            <option value="pre">Ön Test</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Süre (Dakika)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="30" min="5" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Geçme Puanı (%)</label>
                        <input type="number" name="pass_score" class="form-control" value="70" min="1" max="100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Soru Sayısı</label>
                        <input type="number" name="question_count" class="form-control" value="10" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Max Deneme</label>
                        <input type="number" name="max_attempts" class="form-control" value="3" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="shuffle_questions" id="shQue" checked>
                            <label class="form-check-label" for="shQue">Soruları Karıştır</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="shuffle_answers" id="shAns" checked>
                            <label class="form-check-label" for="shAns">Şıkları Karıştır</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-warning px-4 text-white"><i class="bi bi-arrow-right-circle me-1"></i>Sınav Oluştur & Soru Ekle</button>
                    <a href="/admin/sinavlar" class="btn btn-outline-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
