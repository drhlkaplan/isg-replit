<?php
$pageTitle = 'Kurs Kayıtları — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/admin/kurslar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-info"></i>Kayıtlı Öğrenciler</h4>
            <small class="text-muted"><?= htmlspecialchars($course['title']) ?> — <?= htmlspecialchars($course['category_name']) ?></small>
        </div>
        <span class="ms-auto badge bg-primary fs-6"><?= count($enrollments) ?> Kayıt</span>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($course['start_date']) || !empty($course['end_date'])): ?>
    <div class="alert alert-info small mb-3">
        <i class="bi bi-calendar-range me-2"></i>
        <strong>Kurs Takvimi:</strong>
        <?php if (!empty($course['start_date'])): ?>
        Başlangıç: <strong><?= date('d.m.Y', strtotime($course['start_date'])) ?></strong>
        <?php endif; ?>
        <?php if (!empty($course['end_date'])): ?>
        — Bitiş: <strong><?= date('d.m.Y', strtotime($course['end_date'])) ?></strong>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>TC Kimlik</th>
                        <th>Firma</th>
                        <th>İlerleme</th>
                        <th>Durum</th>
                        <th>Kayıt Tarihi</th>
                        <th>Son Tarih</th>
                        <th>Tamamlanma</th>
                        <th>Sertifika</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($enrollments as $e):
                    $statusMap = [
                        'enrolled'    => ['bg-secondary', 'Kayıtlı'],
                        'in_progress' => ['bg-warning text-dark', 'Devam Ediyor'],
                        'completed'   => ['bg-success', 'Tamamlandı'],
                        'failed'      => ['bg-danger', 'Başarısız'],
                    ];
                    $st = $statusMap[$e['status']] ?? ['bg-secondary', $e['status']];
                    $today = date('Y-m-d');
                    $isOverdue = !empty($e['due_date']) && $today > $e['due_date'] && $e['status'] !== 'completed';
                ?>
                <tr class="<?= $isOverdue ? 'table-warning' : '' ?>">
                    <td class="fw-semibold"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($e['email']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($e['tc_identity_no'] ?: '—') ?></td>
                    <td class="small"><?= htmlspecialchars($e['firm_name'] ?: '—') ?></td>
                    <td style="min-width:120px">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-fill" style="height:8px">
                                <div class="progress-bar bg-primary" style="width:<?= $e['progress_percent'] ?>%"></div>
                            </div>
                            <small class="fw-semibold">%<?= $e['progress_percent'] ?></small>
                        </div>
                    </td>
                    <td><span class="badge <?= $st[0] ?>"><?= $st[1] ?></span></td>
                    <td class="small text-muted"><?= date('d.m.Y', strtotime($e['enrolled_at'])) ?></td>
                    <td style="min-width:160px">
                        <form method="POST" action="/admin/kurslar/kayitlar/<?= $course['id'] ?>" class="d-flex align-items-center gap-1">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="enrollment_id" value="<?= $e['id'] ?>">
                            <input type="date" name="due_date" class="form-control form-control-sm py-0 px-1" style="font-size:.8rem"
                                   value="<?= htmlspecialchars($e['due_date'] ?? '') ?>">
                            <button class="btn btn-sm btn-outline-primary py-0 px-1" title="Kaydet">
                                <i class="bi bi-check"></i>
                            </button>
                        </form>
                        <?php if ($isOverdue): ?>
                        <span class="badge bg-danger mt-1" style="font-size:.7rem">Gecikmiş</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= $e['completed_at'] ? date('d.m.Y', strtotime($e['completed_at'])) : '—' ?></td>
                    <td>
                        <?php if ($e['cert_number']): ?>
                        <a href="/dogrula/<?= htmlspecialchars($e['cert_number']) ?>" target="_blank"
                           class="badge bg-success text-decoration-none">
                            <i class="bi bi-award me-1"></i><?= htmlspecialchars($e['cert_number']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($enrollments)): ?>
                <tr><td colspan="10" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>Bu kursa henüz kayıtlı öğrenci yok.
                </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
