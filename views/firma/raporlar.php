<?php
$pageTitle = 'Firma Raporları — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Firma Raporları</h4>
            <p class="text-muted small mb-0"><?= htmlspecialchars($firm['name']) ?></p>
        </div>
        <a href="/firma" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Panele Dön</a>
    </div>

    <!-- Course Completion Stats -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold"><i class="bi bi-book me-2"></i>Kurs Bazında Tamamlanma Oranları</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kurs</th>
                        <th>Kategori</th>
                        <th>Kayıtlı</th>
                        <th>Tamamlanan</th>
                        <th>Devam</th>
                        <th>Başarısız</th>
                        <th>Ort. İlerleme</th>
                        <th>Tamamlanma %</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($courseStats as $cs):
                    $pct = $cs['enrolled'] > 0 ? round(($cs['completed'] / $cs['enrolled']) * 100) : 0;
                ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($cs['title']) ?></td>
                    <td>
                        <span class="badge small" style="background:<?= htmlspecialchars($cs['category_color']) ?>">
                            <?= htmlspecialchars($cs['category_name']) ?>
                        </span>
                    </td>
                    <td><?= $cs['enrolled'] ?></td>
                    <td><span class="badge bg-success"><?= $cs['completed'] ?></span></td>
                    <td><span class="badge bg-warning text-dark"><?= $cs['in_progress'] ?></span></td>
                    <td><span class="badge <?= $cs['failed'] > 0 ? 'bg-danger' : 'bg-light text-muted' ?>"><?= $cs['failed'] ?></span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;min-width:60px">
                                <div class="progress-bar" style="width:<?= round($cs['avg_progress']) ?>%;background:<?= htmlspecialchars($cs['category_color']) ?>"></div>
                            </div>
                            <small>%<?= round($cs['avg_progress']) ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;min-width:60px">
                                <div class="progress-bar bg-<?= $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="badge bg-<?= $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning text-dark' : 'danger') ?>">%<?= $pct ?></span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($courseStats)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">Henüz kayıt yok.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expired Certificates -->
    <?php if (!empty($expiredCerts)): ?>
    <div class="card shadow-sm">
        <div class="card-header fw-semibold text-danger"><i class="bi bi-shield-x me-2"></i>Süresi Dolmuş Sertifikalar</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr><th>Çalışan</th><th>Kurs</th><th>Sona Erme</th><th>Kaç Gün Önce</th><th>Sertifika No</th></tr>
                </thead>
                <tbody>
                <?php foreach ($expiredCerts as $ec): ?>
                <tr class="table-danger-subtle">
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($ec['first_name'] . ' ' . $ec['last_name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($ec['email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($ec['course_title']) ?></td>
                    <td><?= date('d.m.Y', strtotime($ec['expires_at'])) ?></td>
                    <td><span class="badge bg-danger"><?= $ec['days_expired'] ?> gün</span></td>
                    <td><code><?= htmlspecialchars($ec['cert_number']) ?></code></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
