<?php
$pageTitle = 'Yönetim Paneli — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Yönetim Paneli</h4>
        <div class="d-flex gap-2">
            <a href="/admin/kullanicilar/ekle" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-plus me-1"></i>Kullanıcı Ekle</a>
            <a href="/admin/kurslar/ekle" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i>Yeni Kurs</a>
        </div>
    </div>

    <!-- Top Stat Row -->
    <div class="row g-3 mb-4">
        <?php
        $statCards = [
            ['icon'=>'bi-people-fill',      'color'=>'primary', 'label'=>'Toplam Öğrenci',      'value'=>$stats['users'],            'url'=>'/admin/kullanicilar'],
            ['icon'=>'bi-building-fill',    'color'=>'secondary','label'=>'Aktif Firma',          'value'=>$stats['firms'],            'url'=>'/admin/firmalar'],
            ['icon'=>'bi-book-fill',        'color'=>'success', 'label'=>'Aktif Paket',          'value'=>$stats['courses'],          'url'=>'/admin/kurslar'],
            ['icon'=>'bi-check2-circle',    'color'=>'info',    'label'=>'Tamamlanan',           'value'=>$stats['completed'],        'url'=>'/admin/raporlar'],
            ['icon'=>'bi-play-circle-fill', 'color'=>'warning', 'label'=>'Devam Ediyor',         'value'=>$stats['in_progress'],      'url'=>'/admin/raporlar'],
            ['icon'=>'bi-award-fill',       'color'=>'danger',  'label'=>'Geçerli Sertifika',    'value'=>$stats['certificates'],     'url'=>'/admin/sertifikalar'],
        ];
        foreach ($statCards as $s):
        ?>
        <div class="col-6 col-xl-2">
            <a href="<?= $s['url'] ?>" class="text-decoration-none">
            <div class="card shadow-sm border-0 isg-stat-card isg-stat-<?= $s['color'] ?>">
                <div class="card-body d-flex align-items-center gap-2 p-3">
                    <div class="isg-stat-icon bg-<?= $s['color'] ?> bg-opacity-10 text-<?= $s['color'] ?>">
                        <i class="bi <?= $s['icon'] ?> fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1"><?= number_format($s['value']) ?></div>
                        <div class="text-muted" style="font-size:.72rem"><?= $s['label'] ?></div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Secondary metrics row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid var(--isg-primary)!important">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px">
                        <i class="bi bi-circle-fill text-success fs-6"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-success"><?= number_format($stats['active_now']) ?></div>
                        <div class="text-muted small">Şu an aktif kullanıcı (15 dk)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px">
                        <i class="bi bi-award text-info fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-info"><?= number_format($stats['certs_this_month']) ?></div>
                        <div class="text-muted small">Bu ay verilen sertifika</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px">
                        <i class="bi bi-shield-x text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-danger"><?= number_format($stats['expired_certs']) ?></div>
                        <div class="text-muted small">Süresi dolmuş sertifika</div>
                        <?php if ($stats['expired_certs'] > 0): ?>
                        <a href="/admin/raporlar?rapor=yenileme&expiry_days=0" class="small text-danger fw-semibold">Görüntüle →</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Recent Enrollments -->
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold d-flex justify-content-between">
                    <span><i class="bi bi-clock-history me-2"></i>Son Kayıtlar</span>
                    <a href="/admin/raporlar" class="btn btn-sm btn-outline-primary">Tüm Raporlar</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr><th>Öğrenci</th><th>Firma</th><th>Kurs</th><th>Tarih</th><th>Durum</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentEnrollments as $re):
                            $stMap = ['enrolled'=>['bg-secondary','Kayıtlı'],'in_progress'=>['bg-warning text-dark','Devam'],'completed'=>['bg-success','Tamam'],'failed'=>['bg-danger','Başarısız']];
                            $st = $stMap[$re['status']] ?? ['bg-secondary',$re['status']];
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($re['first_name'] . ' ' . $re['last_name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($re['firm_name'] ?: '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars(mb_substr($re['course_title'],0,30)) ?></td>
                            <td class="text-muted"><?= date('d.m.Y', strtotime($re['enrolled_at'])) ?></td>
                            <td><span class="badge <?= $st[0] ?>"><?= $st[1] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentEnrollments)): ?>
                        <tr><td colspan="5" class="text-center py-3 text-muted">Henüz kayıt yok.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="card shadow-sm">
                <div class="card-header fw-semibold d-flex justify-content-between">
                    <span><i class="bi bi-person-badge me-2"></i>Son Kayıt Olan Kullanıcılar</span>
                    <a href="/admin/kullanicilar/ekle" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Ekle</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr><th>Ad Soyad</th><th>E-posta</th><th>Firma</th><th>Rol</th><th>Kayıt Tarihi</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['firm_name'] ?: '—') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role_name']) ?></span></td>
                            <td class="text-muted"><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-lightning me-2"></i>Hızlı İşlemler</div>
                <div class="list-group list-group-flush">
                    <a href="/admin/kurslar/ekle" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-plus-circle-fill text-success fs-5"></i>
                        <div><div class="fw-semibold small">Yeni Kurs Ekle</div><small class="text-muted">SCORM paketi yükle</small></div>
                    </a>
                    <a href="/admin/kullanicilar/ekle" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-person-plus-fill text-primary fs-5"></i>
                        <div><div class="fw-semibold small">Kullanıcı Ekle</div><small class="text-muted">Yeni öğrenci kaydı</small></div>
                    </a>
                    <a href="/admin/firmalar/ekle" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-building-add text-secondary fs-5"></i>
                        <div><div class="fw-semibold small">Firma Ekle</div><small class="text-muted">Yeni firma tanımla</small></div>
                    </a>
                    <a href="/admin/sinavlar/ekle" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-clipboard-plus-fill text-warning fs-5"></i>
                        <div><div class="fw-semibold small">Sınav Oluştur</div><small class="text-muted">Soru bankası ekle</small></div>
                    </a>
                    <a href="/admin/grup-anahtarlari/ekle" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-key-fill text-info fs-5"></i>
                        <div><div class="fw-semibold small">Grup Anahtarı Oluştur</div><small class="text-muted">Toplu kayıt linki</small></div>
                    </a>
                    <a href="/admin/raporlar" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-bar-chart-fill text-danger fs-5"></i>
                        <div><div class="fw-semibold small">Raporlar & İhracat</div><small class="text-muted">Excel / PDF export</small></div>
                    </a>
                </div>
            </div>

            <!-- Firm Overview -->
            <div class="card shadow-sm">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-building me-2"></i>Firma Özeti</span>
                    <a href="/admin/firmalar" class="btn btn-sm btn-outline-secondary">Tümü</a>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($firmStats as $fs):
                        $completionPct = $fs['enrollment_count'] > 0 ? round(($fs['completed_count'] / $fs['enrollment_count']) * 100) : 0;
                    ?>
                    <a href="/admin/firmalar/duzenle/<?= $fs['id'] ?>" class="list-group-item list-group-item-action py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="fw-semibold small"><?= htmlspecialchars($fs['name']) ?></div>
                                <small class="text-muted"><?= $fs['employee_count'] ?> çalışan · <?= $fs['enrollment_count'] ?> kayıt</small>
                            </div>
                            <span class="badge <?= $fs['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:.65rem">
                                <?= $fs['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                            </span>
                        </div>
                        <div class="progress" style="height:4px">
                            <div class="progress-bar" style="width:<?= $completionPct ?>%;background:<?= htmlspecialchars($fs['primary_color'] ?: '#005695') ?>"></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($firmStats)): ?>
                    <div class="list-group-item text-center text-muted py-3">Firma yok.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
