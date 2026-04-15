<?php
$pageTitle = htmlspecialchars($enrollment['title']) . ' — ' . APP_NAME;
include __DIR__ . '/../_layout.php';

$topicLabels = [
    'on_degerlendirme' => ['Ön Değerlendirme Sınavı', 'bi-clipboard-check', 'warning'],
    'genel'            => ['Genel Konular',            'bi-book-fill',        'primary'],
    'saglik'           => ['Sağlık Konuları',          'bi-heart-pulse-fill', 'danger'],
    'teknik'           => ['Teknik Konular',            'bi-tools',            'info'],
    'ise_ozgu'         => ['İşe ve İşyerine Özgü',     'bi-building-gear',    'secondary'],
    'final_sinav'      => ['Final Sınavı',              'bi-trophy-fill',      'success'],
];
?>
<div class="container-xl px-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ogrenci">Eğitimlerim</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($enrollment['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header" style="background:<?= htmlspecialchars($enrollment['category_color']) ?>15;border-left:4px solid <?= htmlspecialchars($enrollment['category_color']) ?>">
                    <div class="d-flex align-items-center">
                        <span class="badge me-2" style="background:<?= htmlspecialchars($enrollment['category_color']) ?>"><?= htmlspecialchars($enrollment['category_name']) ?></span>
                        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($enrollment['title']) ?></h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?= nl2br(htmlspecialchars($enrollment['description'] ?? '')) ?></p>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="isg-stat-mini text-center">
                                <i class="bi bi-clock text-primary fs-4"></i>
                                <div class="fw-bold"><?= ceil($enrollment['duration_minutes']/60) ?> Saat</div>
                                <small class="text-muted">Süre</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="isg-stat-mini text-center">
                                <i class="bi bi-bar-chart text-success fs-4"></i>
                                <div class="fw-bold"><?= $enrollment['progress_percent'] ?>%</div>
                                <small class="text-muted">İlerleme</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="isg-stat-mini text-center">
                                <?php $statusMap = ['enrolled'=>['bi-circle','secondary','Başlamadı'],'in_progress'=>['bi-play-circle','warning','Devam Ediyor'],'completed'=>['bi-check-circle','success','Tamamlandı'],'failed'=>['bi-x-circle','danger','Başarısız']]; ?>
                                <?php $st = $statusMap[$enrollment['status']] ?? $statusMap['enrolled']; ?>
                                <i class="bi <?= $st[0] ?> text-<?= $st[1] ?> fs-4"></i>
                                <div class="fw-bold text-<?= $st[1] ?>"><?= $st[2] ?></div>
                                <small class="text-muted">Durum</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="isg-stat-mini text-center">
                                <i class="bi bi-award text-warning fs-4"></i>
                                <div class="fw-bold"><?= $certificate ? 'Mevcut' : '—' ?></div>
                                <small class="text-muted">Sertifika</small>
                            </div>
                        </div>
                    </div>

                    <?php if (($enrollment['topic_type'] ?? '') !== 'paket'): ?>
                        <?php if ($package): ?>
                        <div class="d-grid mb-3">
                            <a href="/scorm/player?kurs=<?= $courseId ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-play-circle-fill me-2"></i>
                                <?= $enrollment['status'] === 'in_progress' ? 'Eğitime Devam Et' : 'Eğitimi Başlat' ?>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Bu kurs için içerik henüz yüklenmemiş.</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php
                        $doneCount = count(array_filter($modules ?? [], fn($m) => ($m['enroll_status'] ?? '') === 'completed'));
                        $total     = count($modules ?? []);
                        ?>
                        <div class="alert alert-info border-0 py-2 mb-0" style="font-size:.88rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Modülleri sırasıyla tamamlayın. İlerlemeniz otomatik kaydedilir.
                            <strong><?= $doneCount ?>/<?= $total ?></strong> modül tamamlandı.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($modules)): ?>
            <!-- ── MODULE LIST ─────────────────────────────────────────── -->
            <div class="card shadow-sm">
                <div class="card-header fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-list-ol text-primary"></i>Eğitim Modülleri
                    <span class="badge bg-secondary ms-auto"><?= count($modules) ?> modül</span>
                </div>
                <div class="list-group list-group-flush">
                <?php foreach ($modules as $i => $mod):
                    $tl      = $topicLabels[$mod['topic_type']] ?? ['Modül', 'bi-circle', 'secondary'];
                    $locked  = (bool)($mod['is_locked'] ?? true);
                    $mStatus = $mod['enroll_status'] ?? 'enrolled';
                    $isYYZ   = $mod['delivery_method'] === 'yuz_yuze';

                    $actionUrl = null;
                    $btnClass  = 'btn-outline-secondary disabled';
                    $btnText   = 'Kilitli';

                    if ($mStatus === 'completed') {
                        $btnClass = 'btn-success';
                        $btnText  = 'Tamamlandı';
                        // Still allow exam review if the module has an exam
                        if ($mod['exam_id'] && !$mod['has_scorm']) {
                            $actionUrl = '/sinav/' . $mod['exam_id'];
                            $btnText   = 'Tamamlandı ✓';
                        }
                    } elseif (!$locked) {
                        if ($isYYZ && $mod['session_id']) {
                            $qrToken   = $mod['session_qr_token'] ?? '';
                            $actionUrl = $qrToken ? '/attend/' . $qrToken : null;
                            $btnClass  = $actionUrl ? 'btn-outline-warning' : 'btn-outline-secondary disabled';
                            $btnText   = 'Derse Katıl';
                        } elseif ($mod['has_scorm'] && $mod['exam_id']) {
                            // Has both SCORM and exam (e.g. content then assessment)
                            $actionUrl = '/scorm/player?kurs=' . $mod['id'];
                            $btnClass  = 'btn-outline-primary';
                            $btnText   = $mStatus === 'in_progress' ? 'Devam Et' : 'Başlat';
                        } elseif ($mod['has_scorm']) {
                            $actionUrl = '/scorm/player?kurs=' . $mod['id'];
                            $btnClass  = 'btn-outline-primary';
                            $btnText   = $mStatus === 'in_progress' ? 'Devam Et' : 'Başlat';
                        } elseif ($mod['exam_id']) {
                            // Exam-only module (e.g. on_degerlendirme, final_sinav with no SCORM)
                            $actionUrl = '/sinav/' . $mod['exam_id'];
                            $btnClass  = $mod['topic_type'] === 'on_degerlendirme' ? 'btn-outline-warning' : 'btn-outline-danger';
                            $btnText   = $mod['topic_type'] === 'on_degerlendirme' ? 'Sınava Gir' : 'Final Sınavı';
                        } else {
                            $btnClass = 'btn-outline-secondary disabled';
                            $btnText  = 'İçerik Yok';
                        }
                    }
                ?>
                <div class="list-group-item px-3 py-3 <?= $locked ? 'bg-light' : '' ?>">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Step indicator -->
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold"
                             style="width:38px;height:38px;font-size:.82rem;<?php
                                if ($mStatus === 'completed')      echo 'background:#d1fae5;color:#059669;';
                                elseif (!$locked)                  echo 'background:#dbeafe;color:#2563eb;';
                                else                               echo 'background:#f1f3f5;color:#adb5bd;';
                             ?>">
                            <?php if ($mStatus === 'completed'): ?>
                                <i class="bi bi-check-lg"></i>
                            <?php elseif ($locked): ?>
                                <i class="bi bi-lock-fill" style="font-size:.75rem;"></i>
                            <?php else: ?>
                                <?= $i + 1 ?>
                            <?php endif; ?>
                        </div>
                        <!-- Info -->
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="fw-semibold <?= $locked ? 'text-muted' : '' ?>" style="font-size:.9rem;"><?= htmlspecialchars($mod['title']) ?></span>
                                <span class="badge rounded-pill" style="font-size:.68rem;background:var(--bs-<?= $tl[2] ?>-bg-subtle,#e9ecef);color:var(--bs-<?= $tl[2] ?>-text-emphasis,#495057);border:1px solid var(--bs-<?= $tl[2] ?>-border-subtle,#dee2e6);">
                                    <i class="bi <?= $tl[1] ?> me-1"></i><?= $tl[0] ?>
                                </span>
                                <?php if ($isYYZ): ?>
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.68rem;">
                                    <i class="bi bi-people-fill me-1"></i>Yüz Yüze
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= $mod['duration_minutes'] ?> dk</small>
                                <?php if ($mStatus === 'in_progress' && !$locked): ?>
                                <small class="text-warning fw-semibold"><i class="bi bi-play-fill me-1"></i>Devam ediyor</small>
                                <?php elseif ($mStatus === 'completed'): ?>
                                <small class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Tamamlandı</small>
                                <?php elseif ($locked): ?>
                                <small class="text-muted"><i class="bi bi-lock me-1"></i>Önceki modülü tamamlayın</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Action -->
                        <div class="flex-shrink-0">
                            <?php if ($actionUrl): ?>
                            <a href="<?= htmlspecialchars($actionUrl) ?>" class="btn btn-sm <?= $btnClass ?> px-3">
                                <?= $btnText ?>
                            </a>
                            <?php else: ?>
                            <span class="btn btn-sm <?= $btnClass ?> px-3"><?= $btnText ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <?php if (!empty($exams)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-clipboard-check me-2"></i>Sınavlar</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($exams as $exam): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($exam['title']) ?></div>
                                <small class="text-muted">
                                    <?= $exam['exam_type'] === 'pre' ? 'Ön Test' : 'Final Sınavı' ?> •
                                    <?= $exam['duration_minutes'] ?> dk •
                                    Geçme: %<?= $exam['pass_score'] ?>
                                </small>
                            </div>
                            <?php $canTake = $enrollment['status'] === 'completed' || $exam['exam_type'] === 'pre'; ?>
                            <a href="/sinav/baslat/<?= $exam['id'] ?>"
                               class="btn btn-sm <?= $canTake ? 'btn-outline-primary' : 'btn-outline-secondary disabled' ?>">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($certificate): ?>
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white fw-semibold">
                    <i class="bi bi-award-fill me-2"></i>Sertifikanız Hazır!
                </div>
                <div class="card-body text-center">
                    <p class="mb-2 text-muted"><small>Sertifika No: <strong><?= htmlspecialchars($certificate['cert_number']) ?></strong></small></p>
                    <a href="/sertifika/indir/<?= htmlspecialchars($certificate['cert_number']) ?>" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-download me-1"></i>PDF İndir
                    </a>
                    <a href="/dogrula/<?= htmlspecialchars($certificate['cert_number']) ?>" target="_blank" class="btn btn-outline-secondary w-100 btn-sm">
                        <i class="bi bi-qr-code me-1"></i>Doğrula
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
