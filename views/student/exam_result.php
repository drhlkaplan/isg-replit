<?php
$pageTitle = 'Sınav Sonucu — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container py-5" style="max-width:600px">
    <div class="card shadow text-center">
        <div class="card-body p-5">
            <?php if ($isPassed): ?>
            <div class="mb-3"><i class="bi bi-trophy-fill text-warning" style="font-size:4rem"></i></div>
            <h3 class="fw-bold text-success mb-2">Tebrikler!</h3>
            <p class="text-muted">Sınavı başarıyla geçtiniz.</p>
            <?php elseif (!empty($allAttemptsExhausted)): ?>
            <div class="mb-3"><i class="bi bi-x-octagon-fill text-danger" style="font-size:4rem"></i></div>
            <h3 class="fw-bold text-danger mb-2">Tüm Denemeler Kullanıldı</h3>
            <div class="alert alert-danger text-start small mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Bu eğitimi tekrar almanız gerekmektedir.</strong><br>
                3 deneme hakkınızın tamamında final sınavını geçemediniz. Eğitim kaydınız başarısız olarak işaretlendi.
                Yeniden kayıt için eğitim yetkilinizle iletişime geçin.
            </div>
            <?php else: ?>
            <div class="mb-3"><i class="bi bi-emoji-frown text-danger" style="font-size:4rem"></i></div>
            <h3 class="fw-bold text-danger mb-2">Başarısız</h3>
            <p class="text-muted">Sınavı geçemediniz. Tekrar deneyebilirsiniz.</p>
            <?php endif; ?>

            <div class="display-3 fw-bold <?= $isPassed ? 'text-success' : 'text-danger' ?> mb-2">
                %<?= number_format($score, 1) ?>
            </div>
            <p class="text-muted mb-1">Geçme Notu: %<?= $exam['pass_score'] ?></p>

            <div class="row g-3 mt-3 mb-4">
                <div class="col-4">
                    <div class="border rounded p-3">
                        <div class="fw-bold text-success"><?= count(array_filter($answerLog, fn($a) => $a['is_correct'])) ?></div>
                        <small class="text-muted">Doğru</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-3">
                        <div class="fw-bold text-danger"><?= count(array_filter($answerLog, fn($a) => !$a['is_correct'])) ?></div>
                        <small class="text-muted">Yanlış</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-3">
                        <div class="fw-bold text-primary"><?= count($answerLog) ?></div>
                        <small class="text-muted">Toplam</small>
                    </div>
                </div>
            </div>

            <?php
            $certificate = null;
            if ($isPassed && $exam['exam_type'] === 'final') {
                $db2 = \ISG\DB::getInstance();
                $certificate = $db2->fetch(
                    'SELECT * FROM certificates WHERE user_id = ? AND course_id = ?',
                    [$_SESSION['user_id'] ?? 0, $exam['course_id']]
                );
            }
            ?>
            <?php if ($certificate): ?>
            <div class="alert alert-success border-success mb-4">
                <i class="bi bi-award-fill me-2"></i>
                <strong>Sertifikanız hazırlandı!</strong><br>
                <small class="text-muted">Sertifika No: <?= htmlspecialchars($certificate['cert_number']) ?></small>
            </div>
            <div class="d-flex gap-2 justify-content-center mb-3">
                <a href="/sertifika/indir/<?= htmlspecialchars($certificate['cert_number']) ?>" class="btn btn-success">
                    <i class="bi bi-download me-1"></i>Sertifikayı İndir
                </a>
            </div>
            <?php endif; ?>
            <div class="d-flex gap-2 justify-content-center">
                <a href="/ogrenci" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-1"></i>Ana Sayfa
                </a>
                <a href="/ogrenci/kurs/<?= $exam['course_id'] ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-left-circle me-1"></i>Kursa Dön
                </a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
