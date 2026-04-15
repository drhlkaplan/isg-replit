<?php
$pageTitle = 'Sınavlar — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-clipboard-check me-2 text-warning"></i>Sınav Yönetimi</h4>
        <a href="/admin/sinavlar/ekle" class="btn btn-warning text-white"><i class="bi bi-plus-circle me-1"></i>Yeni Sınav</a>
    </div>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Sınav Adı</th><th>Kurs</th><th>Tür</th><th>Süre</th><th>Geçme</th><th>Soru</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                <?php foreach ($exams as $e): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($e['title']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($e['course_title']) ?></td>
                    <td><span class="badge <?= $e['exam_type'] === 'pre' ? 'bg-info' : 'bg-warning text-dark' ?>"><?= $e['exam_type'] === 'pre' ? 'Ön Test' : 'Final' ?></span></td>
                    <td><?= $e['duration_minutes'] ?> dk</td>
                    <td>%<?= $e['pass_score'] ?></td>
                    <td><span class="badge bg-secondary"><?= $e['question_count_actual'] ?>/<?= $e['question_count'] ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/admin/sinavlar/sorular/<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-list-ol me-1"></i>Sorular</a>
                            <a href="/admin/sinavlar/sil/<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Bu sınavı ve tüm sorularını silmek istediğinizden emin misiniz?')"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($exams)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">Henüz sınav oluşturulmamış.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
