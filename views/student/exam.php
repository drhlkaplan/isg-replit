<?php
$pageTitle = htmlspecialchars($exam['title']) . ' — ' . APP_NAME;
$bodyClass = 'bg-light';
include __DIR__ . '/../_layout.php';
?>
<div class="container py-4" style="max-width:800px">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold"><i class="bi bi-clipboard-check me-2"></i><?= htmlspecialchars($exam['title']) ?></h5>
                <small><?= $exam['exam_type'] === 'pre' ? 'Ön Test' : 'Final Sınavı' ?> — <?= $exam['course_title'] ?></small>
            </div>
            <div class="text-end">
                <div id="exam-timer" class="fs-4 fw-bold font-monospace">
                    <?= sprintf('%02d:%02d', $exam['duration_minutes'], 0) ?>
                </div>
                <small>Kalan Süre</small>
            </div>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="/sinav/gonder/<?= $exam['id'] ?>" id="examForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <?php foreach ($questions as $i => $q): ?>
                <div class="mb-4 p-3 bg-white rounded border">
                    <p class="fw-semibold mb-3">
                        <span class="badge bg-primary me-2"><?= $i + 1 ?></span>
                        <?= htmlspecialchars($q['question_text']) ?>
                    </p>
                    <div class="ps-2">
                        <?php foreach ($q['options'] as $opt): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>" id="opt_<?= $opt['id'] ?>" required>
                            <label class="form-check-label" for="opt_<?= $opt['id'] ?>">
                                <?= htmlspecialchars($opt['option_text']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success btn-lg fw-bold" onclick="return confirm('Sınavı bitirmek istediğinizden emin misiniz?')">
                        <i class="bi bi-check-circle me-2"></i>Sınavı Bitir ve Gönder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function() {
    let totalSeconds = <?= $exam['duration_minutes'] * 60 ?>;
    const timerEl = document.getElementById('exam-timer');
    const interval = setInterval(() => {
        totalSeconds--;
        if (totalSeconds <= 0) {
            clearInterval(interval);
            document.getElementById('examForm').submit();
            return;
        }
        const m = Math.floor(totalSeconds / 60);
        const s = totalSeconds % 60;
        timerEl.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        if (totalSeconds <= 120) timerEl.style.color = '#dc3545';
    }, 1000);
})();
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
