<?php
$pageTitle = htmlspecialchars($firm['name']) . ' — Firma Paneli — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($firm['logo_path']) && file_exists(LOGO_DIR . $firm['logo_path'])): ?>
            <img src="<?= LOGO_URL . htmlspecialchars($firm['logo_path']) ?>"
                 alt="<?= htmlspecialchars($firm['name']) ?>" style="max-height:52px;max-width:150px">
            <?php endif; ?>
            <div>
                <h4 class="fw-bold mb-0" style="color:var(--isg-primary)">
                    <?= htmlspecialchars($firm['header_title'] ?: $firm['name']) ?>
                </h4>
                <p class="text-muted mb-0 small">Firma Eğitim Takip Paneli</p>
            </div>
        </div>
        <a href="/firma/profil" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil-square me-1"></i>Profil & Tema
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <?php
        $statCards = [
            ['icon'=>'bi-people-fill',      'color'=>'primary', 'label'=>'Toplam Çalışan',      'value'=>$stats['employees'],       'url'=>'/firma/calisanlar'],
            ['icon'=>'bi-person-check-fill','color'=>'success', 'label'=>'Aktif (7 gün)',        'value'=>$stats['active_employees'],'url'=>'/firma/calisanlar'],
            ['icon'=>'bi-check2-circle',    'color'=>'info',    'label'=>'Tamamlanan Eğitim',    'value'=>$stats['completed'],       'url'=>'/firma/raporlar'],
            ['icon'=>'bi-play-circle-fill', 'color'=>'warning', 'label'=>'Devam Ediyor',         'value'=>$stats['in_progress'],     'url'=>'/firma/raporlar'],
            ['icon'=>'bi-award-fill',       'color'=>'danger',  'label'=>'Alınan Sertifika',     'value'=>$stats['certs'],           'url'=>'/firma/raporlar'],
        ];
        foreach ($statCards as $s):
        ?>
        <div class="col-6 col-xl">
            <a href="<?= $s['url'] ?>" class="text-decoration-none">
            <div class="card shadow-sm border-0 isg-stat-card isg-stat-<?= $s['color'] ?>">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="isg-stat-icon bg-<?= $s['color'] ?> bg-opacity-10 text-<?= $s['color'] ?>">
                        <i class="bi <?= $s['icon'] ?> fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold lh-1"><?= number_format($s['value']) ?></div>
                        <div class="text-muted small"><?= $s['label'] ?></div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <!-- Left: Employee Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i>Çalışan Eğitim Durumu</span>
                    <a href="/firma/calisanlar" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Ad Soyad</th>
                                <th>TC Kimlik</th>
                                <th>Kayıtlı</th>
                                <th>Tamamlanan</th>
                                <th>Devam</th>
                                <th>Son Aktivite</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_slice($employees, 0, 10) as $emp): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($emp['email']) ?></small>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($emp['tc_identity_no'] ?: '—') ?></td>
                            <td><span class="badge bg-info"><?= $emp['enrolled_count'] ?></span></td>
                            <td><span class="badge bg-success"><?= $emp['completed_count'] ?></span></td>
                            <td><span class="badge bg-warning text-dark"><?= $emp['inprogress_count'] ?></span></td>
                            <td class="text-muted">
                                <?= $emp['last_activity'] ? date('d.m.Y', strtotime($emp['last_activity'])) : '—' ?>
                            </td>
                            <td>
                                <span class="badge <?= $emp['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $emp['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($employees)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>Firmanıza kayıtlı öğrenci bulunamadı.
                        </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Enrollments -->
            <?php if (!empty($recentEnrollments)): ?>
            <div class="card shadow-sm">
                <div class="card-header fw-semibold"><i class="bi bi-clock-history me-2"></i>Son Kayıtlar</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th>Çalışan</th><th>Kurs</th><th>Kayıt Tarihi</th><th>Durum</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentEnrollments as $r):
                            $stMap = ['enrolled'=>['bg-secondary','Kayıtlı'],'in_progress'=>['bg-warning text-dark','Devam'],'completed'=>['bg-success','Tamamlandı'],'failed'=>['bg-danger','Başarısız']];
                            $st = $stMap[$r['status']] ?? ['bg-secondary',$r['status']];
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars(mb_substr($r['course_title'],0,40)) ?></td>
                            <td class="text-muted"><?= date('d.m.Y', strtotime($r['enrolled_at'])) ?></td>
                            <td><span class="badge <?= $st[0] ?>"><?= $st[1] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Course Completion -->
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bar-chart me-2"></i>Kurs Tamamlanma</span>
                    <a href="/firma/raporlar" class="btn btn-sm btn-outline-secondary">Detay</a>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($courseStats, 0, 6) as $cs):
                        $pct = $cs['enrolled'] > 0 ? round(($cs['completed'] / $cs['enrolled']) * 100) : 0;
                    ?>
                    <div class="list-group-item py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-truncate" style="max-width:200px"><?= htmlspecialchars($cs['title']) ?></span>
                            <small class="fw-bold text-<?= $pct >= 70 ? 'success' : 'warning' ?> ms-2">%<?= $pct ?></small>
                        </div>
                        <div class="progress" style="height:5px">
                            <div class="progress-bar bg-<?= $pct >= 70 ? 'success' : 'warning' ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $cs['completed'] ?>/<?= $cs['enrolled'] ?> tamamlandı</small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($courseStats)): ?>
                    <div class="list-group-item text-center text-muted py-4">Henüz kayıt yok.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Expiring Certs -->
            <?php if (!empty($expiringCerts)): ?>
            <div class="card shadow-sm border-warning border-opacity-50">
                <div class="card-header fw-semibold bg-warning bg-opacity-10 text-warning-emphasis">
                    <i class="bi bi-clock-history me-2"></i>Yakında Sona Erecek Sertifikalar
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($expiringCerts as $ec): ?>
                    <div class="list-group-item py-2 <?= $ec['days_left'] <= 0 ? 'list-group-item-danger' : ($ec['days_left'] <= 14 ? 'list-group-item-warning' : '') ?>">
                        <div class="fw-semibold small"><?= htmlspecialchars($ec['first_name'] . ' ' . $ec['last_name']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars(mb_substr($ec['course_title'],0,38)) ?></div>
                        <div class="<?= $ec['days_left'] <= 0 ? 'text-danger' : 'text-warning' ?>" style="font-size:.75rem;font-weight:600">
                            <?= $ec['days_left'] <= 0 ? abs($ec['days_left']) . ' gün önce sona erdi' : $ec['days_left'] . ' gün kaldı' ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
