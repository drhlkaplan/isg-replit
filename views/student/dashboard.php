<?php
$pageTitle = 'Eğitimlerim — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
$user = $_SESSION['user'];
$today = new DateTime();

$failedCourses   = [];
$expiringCerts   = [];
foreach ($enrollments as $e) {
    if ($e['status'] === 'failed') $failedCourses[] = $e;
    if ($e['cert_expires_at'] && $e['cert_is_valid']) {
        $diff     = $today->diff(new DateTime($e['cert_expires_at']));
        $daysLeft = (int)$diff->days * ($diff->invert ? -1 : 1);
        if ($daysLeft <= 30) {
            $e['days_until_expiry'] = $daysLeft;
            $expiringCerts[] = $e;
        }
    }
}
?>
<div class="container-xl px-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>Eğitimlerim</h4>
            <p class="text-muted mb-0">Hoş geldiniz, <?= htmlspecialchars($user['first_name']) ?>!</p>
        </div>
        <a href="/ogrenci/profil" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-key me-1"></i>Grup Anahtarı
        </a>
    </div>

    <?php if (!empty($flash['message'])): ?>
    <div class="alert alert-<?= $flash['type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Alert: Expiring/Expired Certs -->
    <?php foreach ($expiringCerts as $ec): ?>
    <?php $dLeft = $ec['days_until_expiry']; ?>
    <?php if ($dLeft < 0): ?>
    <div class="alert alert-danger d-flex align-items-center gap-3 mb-3">
        <i class="bi bi-shield-exclamation fs-4 flex-shrink-0"></i>
        <div><strong>Sertifikanız süresi doldu!</strong>
        <?= htmlspecialchars($ec['title']) ?> — <strong><?= date('d.m.Y', strtotime($ec['cert_expires_at'])) ?></strong> tarihinde sona ermiştir. Tekrar eğitim almanız gerekmektedir.</div>
    </div>
    <?php elseif ($dLeft <= 7): ?>
    <div class="alert alert-danger d-flex align-items-center gap-3 mb-3">
        <i class="bi bi-alarm-fill fs-4 flex-shrink-0 text-danger"></i>
        <div><strong>ACİL: Sertifika yenileme gerekli!</strong>
        <?= htmlspecialchars($ec['title']) ?> — <strong><?= $dLeft ?> gün</strong> sonra sona eriyor. Hemen eğitimi yenileyin.</div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning d-flex align-items-center gap-3 mb-3">
        <i class="bi bi-clock-history fs-4 flex-shrink-0"></i>
        <div><strong>Sertifika yenileme yaklaşıyor.</strong>
        <?= htmlspecialchars($ec['title']) ?> — <strong><?= $dLeft ?> gün</strong> sonra sona eriyor (<?= date('d.m.Y', strtotime($ec['cert_expires_at'])) ?>).</div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <!-- Alert: Failed -->
    <?php foreach ($failedCourses as $fc): ?>
    <div class="alert alert-danger d-flex align-items-center gap-3 mb-3">
        <i class="bi bi-x-octagon-fill fs-4 flex-shrink-0"></i>
        <div><strong>Eğitim başarısız!</strong>
        <em><?= htmlspecialchars($fc['title']) ?></em> — Final sınavından 3 denemede de başarısız oldunuz.</div>
    </div>
    <?php endforeach; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <?php
        $statCards = [
            ['icon'=>'bi-book-fill',      'color'=>'primary', 'label'=>'Toplam Paket',    'value'=>$stats['total']],
            ['icon'=>'bi-play-circle-fill','color'=>'warning', 'label'=>'Devam Ediyor',    'value'=>$stats['in_progress']],
            ['icon'=>'bi-check2-circle',  'color'=>'success', 'label'=>'Tamamlanan',       'value'=>$stats['completed']],
            ['icon'=>'bi-award-fill',     'color'=>'info',    'label'=>'Sertifikalarım',   'value'=>$stats['certificates'], 'url'=>'/ogrenci/sertifikalar'],
        ];
        foreach ($statCards as $s):
        ?>
        <div class="col-6 col-xl-3">
            <?php $tag = isset($s['url']) ? "a href=\"{$s['url']}\" class=\"text-decoration-none\"" : 'div'; ?>
            <<?= $tag ?>>
            <div class="card border-0 shadow-sm isg-stat-card isg-stat-<?= $s['color'] ?>">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="isg-stat-icon bg-<?= $s['color'] ?> bg-opacity-10 text-<?= $s['color'] ?>">
                        <i class="bi <?= $s['icon'] ?> fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold lh-1"><?= $s['value'] ?></div>
                        <div class="text-muted small"><?= $s['label'] ?></div>
                    </div>
                </div>
            </div>
            <?= isset($s['url']) ? '</a>' : '</div>' ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($enrollments)): ?>
    <div class="isg-empty-state">
        <i class="bi bi-book-half"></i>
        <h5>Henüz kayıtlı eğitiminiz yok</h5>
        <p class="text-muted">Kayıt için yöneticinize veya eğitmeninize başvurun ya da bir grup anahtarı kullanın.</p>
        <a href="/ogrenci/profil" class="btn btn-primary">Grup Anahtarı Kullan</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <!-- Enrollment Cards: 2/3 width -->
        <div class="col-lg-8">
            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-collection me-2"></i>Eğitim Paketlerim</h6>
            <div class="row g-3">
            <?php foreach ($enrollments as $e):
                $isFailed = $e['status'] === 'failed';
                $certExpiry = $e['cert_expires_at'] ?? null;
                $expired = false; $expiring = false; $daysLeft = null;
                if ($certExpiry && $e['cert_is_valid']) {
                    $diff     = $today->diff(new DateTime($certExpiry));
                    $daysLeft = (int)$diff->days * ($diff->invert ? -1 : 1);
                    if ($daysLeft < 0) $expired = true;
                    elseif ($daysLeft <= 30) $expiring = true;
                }
                $borderClass = $isFailed || $expired ? 'border-danger border-2' : ($expiring ? 'border-warning border-2' : '');
            ?>
            <div class="col-12">
                <div class="card shadow-sm <?= $borderClass ?>">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0 mt-1">
                                <div style="width:42px;height:42px;border-radius:10px;background:<?= htmlspecialchars($e['category_color']) ?>22;display:flex;align-items:center;justify-content:center">
                                    <i class="bi bi-shield-check" style="color:<?= htmlspecialchars($e['category_color']) ?>;font-size:1.3rem"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-truncate" style="max-width:320px"><?= htmlspecialchars($e['title']) ?></h6>
                                        <span class="badge small me-1" style="background:<?= htmlspecialchars($e['category_color']) ?>"><?= htmlspecialchars($e['category_name']) ?></span>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= ceil($e['duration_minutes']/60) ?> Saat</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php if ($isFailed): ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Başarısız</span>
                                        <?php elseif ($e['status'] === 'completed'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Tamamlandı</span>
                                        <?php elseif ($e['status'] === 'in_progress'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-play-circle me-1"></i>Devam Ediyor</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-circle me-1"></i>Başlamadı</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($expired && $certExpiry): ?>
                                <div class="small text-danger mt-1"><i class="bi bi-shield-x me-1"></i>Sertifika süresi doldu (<?= date('d.m.Y', strtotime($certExpiry)) ?>)</div>
                                <?php elseif ($expiring && $certExpiry): ?>
                                <div class="small text-warning mt-1"><i class="bi bi-clock me-1"></i><?= $daysLeft ?> gün içinde yenileme gerekli</div>
                                <?php endif; ?>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar" style="width:<?= $e['progress_percent'] ?>%;background:<?= htmlspecialchars($e['category_color']) ?>"></div>
                                    </div>
                                    <small class="text-muted fw-semibold" style="min-width:36px">%<?= $e['progress_percent'] ?></small>
                                    <a href="/ogrenci/kurs/<?= $e['course_id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                    <?php if ($e['cert_number'] && !$expired): ?>
                                    <a href="/sertifika/goruntule/<?= htmlspecialchars($e['cert_number']) ?>" target="_blank"
                                       class="btn btn-sm btn-outline-success py-0 px-2" title="Sertifikayı Görüntüle">
                                        <i class="bi bi-award"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Activity -->
            <?php if (!empty($recentActivity)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-activity me-2"></i>Son Aktiviteler</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentActivity as $a): ?>
                    <a href="/ogrenci/kurs/<?= $a['course_id'] ?>" class="list-group-item list-group-item-action py-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                  style="width:28px;height:28px;background:<?= htmlspecialchars($a['category_color']) ?>22">
                                <i class="bi bi-play-fill" style="color:<?= htmlspecialchars($a['category_color']) ?>;font-size:.75rem"></i>
                            </span>
                            <div class="flex-grow-1 min-width-0">
                                <div class="small fw-semibold text-truncate"><?= htmlspecialchars($a['title']) ?></div>
                                <div class="text-muted" style="font-size:.7rem"><?= $a['last_activity'] ? date('d.m.Y H:i', strtotime($a['last_activity'])) : '—' ?></div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Links -->
            <div class="card shadow-sm">
                <div class="card-header fw-semibold"><i class="bi bi-lightning me-2"></i>Hızlı Erişim</div>
                <div class="list-group list-group-flush">
                    <a href="/ogrenci/profil" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-key-fill text-primary fs-5"></i>
                        <div><div class="fw-semibold small">Grup Anahtarı</div><small class="text-muted">Anahtarla kursa katıl</small></div>
                    </a>
                    <a href="/ogrenci/sertifikalar" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-award-fill text-warning fs-5"></i>
                        <div><div class="fw-semibold small">Sertifikalarım</div><small class="text-muted">Sertifikaları görüntüle ve indir</small></div>
                    </a>
                    <a href="/ogrenci/profil" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-person-gear text-secondary fs-5"></i>
                        <div><div class="fw-semibold small">Profilim</div><small class="text-muted">Bilgileri ve firma kodunu güncelle</small></div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
