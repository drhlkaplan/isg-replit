<?php
$pageTitle = 'Sınav Soruları — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-2">
        <a href="/admin/sinavlar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold mb-0"><?= htmlspecialchars($exam['title']) ?></h4>
            <small class="text-muted"><?= htmlspecialchars($exam['course_title']) ?> — Hedef: <?= $exam['question_count'] ?> soru / Geçme: %<?= $exam['pass_score'] ?></small>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold"><i class="bi bi-plus-circle me-2"></i>Yeni Soru Ekle</div>
                <div class="card-body">
                    <form method="POST" action="/admin/sinavlar/sorular/<?= $exam['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Soru Metni *</label>
                            <textarea name="question_text" class="form-control" rows="3" required placeholder="Soruyu buraya yazın..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Şıklar (Doğru şıkkı seçin)</label>
                            <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="input-group mb-2">
                                <div class="input-group-text">
                                    <input class="form-check-input" type="radio" name="correct_option" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                                </div>
                                <input type="text" name="options[]" class="form-control" placeholder="<?= ['A','B','C','D'][$i] ?> şıkkı..." required>
                            </div>
                            <?php endfor; ?>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Radyo butonu ile doğru şıkkı seçin</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus me-1"></i>Soruyu Ekle</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between">
                    <span class="fw-semibold"><i class="bi bi-list-ol me-2"></i>Mevcut Sorular</span>
                    <span class="badge bg-<?= count($questions) >= $exam['question_count'] ? 'success' : 'warning' ?>">
                        <?= count($questions) ?>/<?= $exam['question_count'] ?>
                    </span>
                </div>
                <?php if (empty($questions)): ?>
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Henüz soru eklenmemiş.
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($questions as $i => $q): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="fw-semibold">
                                <span class="badge bg-primary me-2"><?= $i+1 ?></span>
                                <?= htmlspecialchars($q['question_text']) ?>
                            </div>
                            <a href="/admin/sinavlar/soru-sil/<?= $q['id'] ?>" class="btn btn-sm btn-outline-danger ms-2 flex-shrink-0"
                               onclick="return confirm('Bu soruyu silmek istediğinizden emin misiniz?')" title="Soruyu Sil">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                        <div class="row g-1 ps-4">
                            <?php foreach ($q['options'] as $opt): ?>
                            <div class="col-6">
                                <span class="small <?= $opt['is_correct'] ? 'text-success fw-bold' : 'text-muted' ?>">
                                    <?= $opt['is_correct'] ? '<i class="bi bi-check-circle me-1"></i>' : '<i class="bi bi-circle me-1"></i>' ?>
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if (count($questions) >= $exam['question_count']): ?>
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                Sınav tamamlandı! <?= $exam['question_count'] ?> soru hazır. <a href="/admin/sinavlar" class="alert-link">Sınav listesine dön</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
