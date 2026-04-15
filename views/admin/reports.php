<?php
$pageTitle = 'Raporlar — ' . APP_NAME;
include __DIR__ . '/../_layout.php';

$rapor       = $rapor ?? 'kurs';
$filters     = $filters ?? [];
$reportData  = $reportData ?? [];
$sistemStats = $sistemStats ?? [];
$firms       = $firms ?? [];
$categories  = $categories ?? [];
$packages    = $packages ?? [];
$modules     = $modules ?? [];
$allUsers    = $allUsers ?? [];

$tabs = [
    'kurs'       => ['label' => 'Kurs / Ders',          'icon' => 'bi-book'],
    'kullanici'  => ['label' => 'Kullanıcı',             'icon' => 'bi-people'],
    'katilimci'  => ['label' => 'Katılımcı Detay',       'icon' => 'bi-person-lines-fill'],
    'firma'      => ['label' => 'Firma',                  'icon' => 'bi-building'],
    'yenileme'   => ['label' => 'Sertifika Yenileme',    'icon' => 'bi-arrow-clockwise'],
    'sistem'     => ['label' => 'Sistem',                 'icon' => 'bi-speedometer2'],
];

// Build a query string that preserves non-filter params
function dlUrl(string $format, string $rapor, array $filters): string {
    $p = ['format' => $format, 'rapor' => $rapor];
    foreach ($filters as $k => $v) {
        if ($k === 'user_ids') {
            // multi
            continue;
        }
        if ($v !== '' && $v !== null && $v !== 0) {
            $p[$k] = $v;
        }
    }
    $qs = http_build_query($p);
    // Append user_ids manually
    if (!empty($filters['user_ids'])) {
        foreach ($filters['user_ids'] as $uid) {
            $qs .= '&user_ids[]=' . (int)$uid;
        }
    }
    return '/admin/raporlar/indir?' . $qs;
}

// Module map for JS
$modulesByPackage = [];
foreach ($modules as $m) {
    $modulesByPackage[$m['parent_course_id']][] = $m;
}
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<style>
.ts-wrapper.form-select { padding: 0; }
.filter-card { background: #f8fafd; border: 1px solid #e0eaf4; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; }
.filter-label { font-size: .75rem; font-weight: 700; color: #4a6080; text-transform: uppercase; letter-spacing: .06em; margin-bottom: .3rem; display: block; }
.report-badge-paket { background: #dbeafe; color: #1d4ed8; }
.report-badge-ders  { background: #ede9fe; color: #6d28d9; }
.status-tamamlandi  { background: #dcfce7; color: #166534; }
.status-devam       { background: #dbeafe; color: #1e40af; }
.status-kayitli     { background: #fef9c3; color: #854d0e; }
.status-basarisiz   { background: #fee2e2; color: #991b1b; }
.progress-sm { height: 5px; border-radius: 3px; }
</style>

<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-info"></i>Raporlar</h4>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-0" id="reportTabs">
        <?php foreach ($tabs as $key => $tab): ?>
        <li class="nav-item">
            <a class="nav-link <?= $rapor === $key ? 'active' : '' ?>"
               href="?rapor=<?= $key ?>">
                <i class="bi <?= $tab['icon'] ?> me-1"></i><?= $tab['label'] ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="card shadow-sm border-top-0 rounded-0 rounded-bottom">
        <div class="card-body pt-3">

            <!-- ── Filter Form ──────────────────────────────────────────── -->
            <?php if ($rapor !== 'sistem'): ?>
            <form method="GET" id="filterForm" class="filter-card">
                <input type="hidden" name="rapor" value="<?= htmlspecialchars($rapor) ?>">

                <div class="row g-2 align-items-end">

                    <!-- Tarih aralığı -->
                    <div class="col-sm-6 col-md-3 col-xl-2">
                        <label class="filter-label">Başlangıç Tarihi</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-sm-6 col-md-3 col-xl-2">
                        <label class="filter-label">Bitiş Tarihi</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>

                    <!-- Firma -->
                    <div class="col-sm-6 col-md-3 col-xl-2">
                        <label class="filter-label">Firma</label>
                        <select name="firm_id" class="form-select form-select-sm">
                            <option value="">Tüm Firmalar</option>
                            <?php foreach ($firms as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($filters['firm_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Kategori -->
                    <?php if ($rapor !== 'yenileme'): ?>
                    <div class="col-sm-6 col-md-3 col-xl-2">
                        <label class="filter-label">Kategori</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">Tüm Kategoriler</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Kurs Türü (paket / ders) — not for firma or yenileme -->
                    <?php if (!in_array($rapor, ['firma','yenileme'])): ?>
                    <div class="col-sm-6 col-md-3 col-xl-2">
                        <label class="filter-label">Kurs Türü</label>
                        <select name="course_type" class="form-select form-select-sm">
                            <option value="">Paket + Ders</option>
                            <option value="paket" <?= ($filters['course_type'] ?? '') === 'paket' ? 'selected' : '' ?>>Yalnızca Paket</option>
                            <option value="ders"  <?= ($filters['course_type'] ?? '') === 'ders'  ? 'selected' : '' ?>>Yalnızca Ders</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Paket seçimi -->
                    <?php if (!in_array($rapor, ['firma','yenileme'])): ?>
                    <div class="col-sm-6 col-md-4 col-xl-3">
                        <label class="filter-label"><i class="bi bi-collection me-1"></i>Paket Seçin</label>
                        <select name="course_id" id="packageSelect" class="form-select form-select-sm">
                            <option value="">Tüm Paketler & Dersler</option>
                            <optgroup label="── Paketler ──">
                            <?php foreach ($packages as $pkg): ?>
                            <option value="<?= $pkg['id'] ?>"
                                    data-type="paket"
                                    <?= ($filters['course_id'] ?? '') == $pkg['id'] ? 'selected' : '' ?>>
                                📦 <?= htmlspecialchars($pkg['title']) ?>
                            </option>
                            <?php endforeach; ?>
                            </optgroup>
                            <?php if (!empty($modules)): ?>
                            <optgroup label="── Dersler ──">
                            <?php foreach ($modules as $mod): ?>
                            <option value="<?= $mod['id'] ?>"
                                    data-type="ders"
                                    data-parent="<?= $mod['parent_course_id'] ?>"
                                    <?= ($filters['course_id'] ?? '') == $mod['id'] ? 'selected' : '' ?>>
                                📄 <?= htmlspecialchars($mod['title']) ?>
                            </option>
                            <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Durum -->
                    <?php if ($rapor === 'yenileme'): ?>
                    <div class="col-sm-6 col-md-3 col-xl-2">
                        <label class="filter-label">Yenileme Periyodu</label>
                        <select name="expiry_days" class="form-select form-select-sm">
                            <option value="0"  <?= ($filters['expiry_days'] ?? 30) == 0  ? 'selected' : '' ?>>Süresi Dolmuş</option>
                            <option value="30" <?= ($filters['expiry_days'] ?? 30) == 30 ? 'selected' : '' ?>>30 Gün İçinde</option>
                            <option value="60" <?= ($filters['expiry_days'] ?? 30) == 60 ? 'selected' : '' ?>>60 Gün İçinde</option>
                            <option value="90" <?= ($filters['expiry_days'] ?? 30) == 90 ? 'selected' : '' ?>>90 Gün İçinde</option>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="col-sm-6 col-md-3 col-xl-2">
                        <label class="filter-label">Durum</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Tüm Durumlar</option>
                            <option value="enrolled"    <?= ($filters['status'] ?? '') === 'enrolled'    ? 'selected' : '' ?>>Kayıtlı</option>
                            <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>Devam Ediyor</option>
                            <option value="completed"   <?= ($filters['status'] ?? '') === 'completed'   ? 'selected' : '' ?>>Tamamlandı</option>
                            <option value="failed"      <?= ($filters['status'] ?? '') === 'failed'      ? 'selected' : '' ?>>Başarısız</option>
                        </select>
                    </div>
                    <?php endif; ?>

                </div><!-- /row -->

                <!-- Kullanıcı çoklu seçim — all tabs except firma, sistem, yenileme -->
                <?php if (!in_array($rapor, ['firma','sistem','yenileme'])): ?>
                <div class="mt-3">
                    <label class="filter-label"><i class="bi bi-people me-1"></i>Kullanıcı Filtresi <span class="text-muted fw-normal">(birden fazla seçilebilir)</span></label>
                    <select name="user_ids[]" id="userMultiSelect" multiple class="form-select form-select-sm" placeholder="Tüm kullanıcılar...">
                        <?php foreach ($allUsers as $u): ?>
                        <option value="<?= $u['id'] ?>"
                                <?= in_array($u['id'], $filters['user_ids'] ?? []) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['last_name'] . ', ' . $u['first_name']) ?> — <?= htmlspecialchars($u['email']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Boş bırakılırsa tüm kullanıcılar gösterilir.</div>
                </div>
                <?php endif; ?>

                <!-- Buttons -->
                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrele
                    </button>
                    <a href="?rapor=<?= $rapor ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Sıfırla
                    </a>
                    <div class="ms-auto d-flex gap-2">
                        <?php $dlFilters = $rapor === 'sistem' ? [] : $filters; ?>
                        <a href="<?= dlUrl('excel', $rapor, $dlFilters) ?>" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </a>
                        <a href="<?= dlUrl('pdf', $rapor, $dlFilters) ?>" class="btn btn-sm btn-danger">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </a>
                    </div>
                </div>
            </form>
            <?php endif; ?>

            <!-- ── KURS / DERS RAPORU ───────────────────────────────────── -->
            <?php if ($rapor === 'kurs'): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-secondary"><?= count($reportData) ?> kayıt</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Tür</th>
                            <th>Kurs / Ders Adı</th>
                            <th>Paket</th>
                            <th>Kategori</th>
                            <th class="text-center">Toplam Kayıt</th>
                            <th class="text-center">Devam Eden</th>
                            <th class="text-center">Tamamlayan</th>
                            <th class="text-center">Tamamlanma %</th>
                            <th class="text-center">Ort. İlerleme</th>
                            <th class="text-center">Ort. Puan</th>
                            <th class="text-center">Öğrenci</th>
                            <th>Top Firma</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reportData as $r):
                        $pct = $r['toplam_kayit'] > 0 ? round(($r['tamamlayan'] / $r['toplam_kayit']) * 100) : 0;
                    ?>
                    <tr>
                        <td>
                            <span class="badge <?= $r['tur'] === 'Paket' ? 'report-badge-paket' : 'report-badge-ders' ?>">
                                <?= $r['tur'] === 'Paket' ? '📦' : '📄' ?> <?= $r['tur'] ?>
                            </span>
                        </td>
                        <td class="fw-semibold"><?= htmlspecialchars($r['title']) ?></td>
                        <td class="text-muted small"><?= $r['parent_kurs'] ? htmlspecialchars($r['parent_kurs']) : '<span class="text-muted">—</span>' ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['kategori'] ?? '—') ?></span></td>
                        <td class="text-center fw-semibold"><?= $r['toplam_kayit'] ?></td>
                        <td class="text-center text-primary"><?= $r['devam_eden'] ?></td>
                        <td class="text-center text-success fw-semibold"><?= $r['tamamlayan'] ?></td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <div class="progress flex-fill progress-sm" style="min-width:50px">
                                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small class="fw-semibold">%<?= $pct ?></small>
                            </div>
                        </td>
                        <td class="text-center">%<?= $r['ort_ilerleme'] ?></td>
                        <td class="text-center"><?= $r['ort_puan'] !== null ? $r['ort_puan'] : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center"><?= $r['benzersiz_ogrenci'] ?></td>
                        <td class="small text-muted"><?= $r['top_firma'] ? htmlspecialchars($r['top_firma']) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reportData)): ?>
                    <tr><td colspan="12" class="text-center py-4 text-muted">Veri bulunamadı.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── KULLANICI RAPORU ─────────────────────────────────────── -->
            <?php elseif ($rapor === 'kullanici'): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-secondary"><?= count($reportData) ?> kayıt</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Firma</th>
                            <th>Kurs / Ders</th>
                            <th class="text-center">Durum</th>
                            <th class="text-center">İlerleme</th>
                            <th>Kayıt Tarihi</th>
                            <th>Tamamlama</th>
                            <th class="text-center">Puan</th>
                            <th>Sertifika</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($r['email']) ?></td>
                        <td><?= $r['firma'] ? htmlspecialchars($r['firma']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= htmlspecialchars($r['kurs']) ?></td>
                        <td class="text-center">
                            <?php $sc = match($r['durum']) {
                                'completed'   => 'status-tamamlandi',
                                'in_progress' => 'status-devam',
                                'enrolled'    => 'status-kayitli',
                                default       => 'bg-light text-dark',
                            }; ?>
                            <span class="badge <?= $sc ?>">
                                <?= match($r['durum']) {
                                    'completed'   => 'Tamamlandı',
                                    'in_progress' => 'Devam Ediyor',
                                    'enrolled'    => 'Kayıtlı',
                                    'failed'      => 'Başarısız',
                                    default       => $r['durum'],
                                } ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <div class="progress flex-fill progress-sm" style="min-width:40px">
                                    <div class="progress-bar bg-info" style="width:<?= $r['ilerleme'] ?>%"></div>
                                </div>
                                <small>%<?= $r['ilerleme'] ?></small>
                            </div>
                        </td>
                        <td class="small"><?= $r['kayit_tarihi'] ? date('d.m.Y', strtotime($r['kayit_tarihi'])) : '-' ?></td>
                        <td class="small"><?= $r['tamamlama_tarihi'] ? date('d.m.Y', strtotime($r['tamamlama_tarihi'])) : '-' ?></td>
                        <td class="text-center fw-semibold"><?= $r['sinav_puani'] ?? '<span class="text-muted">—</span>' ?></td>
                        <td>
                            <?php if ($r['sertifika_no']): ?>
                            <span class="badge bg-info text-dark" style="font-size:.7rem"><?= htmlspecialchars($r['sertifika_no']) ?></span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reportData)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">Veri bulunamadı.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── KATILIMCİ DETAY RAPORU ──────────────────────────────── -->
            <?php elseif ($rapor === 'katilimci'): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-secondary"><?= count($reportData) ?> kayıt</span>
                <small class="text-muted">Kişi × Eğitim bazında detaylı kayıt raporu</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Telefon</th>
                            <th>Firma</th>
                            <th>Tür</th>
                            <th>Paket</th>
                            <th>Kurs / Ders</th>
                            <th class="text-center">Durum</th>
                            <th class="text-center">İlerleme</th>
                            <th>Başlama</th>
                            <th>Bitirme</th>
                            <th>Son Aktivite</th>
                            <th class="text-center">Puan</th>
                            <th>Sertifika</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($r['email']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($r['phone'] ?? '—') ?></td>
                        <td class="small"><?= $r['firma'] ? htmlspecialchars($r['firma']) : '<span class="text-muted">—</span>' ?></td>
                        <td>
                            <span class="badge <?= $r['tur'] === 'Paket' ? 'report-badge-paket' : 'report-badge-ders' ?>">
                                <?= $r['tur'] === 'Paket' ? '📦' : '📄' ?> <?= $r['tur'] ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= $r['parent_kurs'] ? htmlspecialchars($r['parent_kurs']) : '—' ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($r['kurs']) ?></td>
                        <td class="text-center">
                            <?php $sc2 = match($r['durum']) {
                                'completed'   => 'status-tamamlandi',
                                'in_progress' => 'status-devam',
                                'enrolled'    => 'status-kayitli',
                                'failed'      => 'status-basarisiz',
                                default       => 'bg-light text-dark',
                            }; ?>
                            <span class="badge <?= $sc2 ?>">
                                <?= match($r['durum']) {
                                    'completed'   => 'Tamamlandı',
                                    'in_progress' => 'Devam',
                                    'enrolled'    => 'Kayıtlı',
                                    'failed'      => 'Başarısız',
                                    default       => $r['durum'],
                                } ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <div class="progress flex-fill progress-sm" style="min-width:40px">
                                    <div class="progress-bar <?= $r['durum'] === 'completed' ? 'bg-success' : 'bg-info' ?>"
                                         style="width:<?= $r['ilerleme'] ?>%"></div>
                                </div>
                                <small>%<?= $r['ilerleme'] ?></small>
                            </div>
                        </td>
                        <td class="small text-nowrap">
                            <?= $r['baslama'] ? date('d.m.Y', strtotime($r['baslama'])) : '—' ?>
                            <?php if ($r['baslama']): ?>
                            <br><span class="text-muted"><?= date('H:i', strtotime($r['baslama'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-nowrap">
                            <?php if ($r['bitirme']): ?>
                            <span class="text-success"><?= date('d.m.Y', strtotime($r['bitirme'])) ?></span>
                            <br><span class="text-muted"><?= date('H:i', strtotime($r['bitirme'])) ?></span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td class="small text-muted text-nowrap">
                            <?= $r['son_aktivite'] ? date('d.m.Y H:i', strtotime($r['son_aktivite'])) : '—' ?>
                        </td>
                        <td class="text-center fw-semibold">
                            <?php if ($r['sinav_puani'] !== null): ?>
                            <span class="badge <?= $r['sinav_puani'] >= 60 ? 'bg-success' : 'bg-danger' ?>">
                                <?= $r['sinav_puani'] ?>
                            </span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['sertifika_no']): ?>
                            <span class="badge bg-info text-dark" style="font-size:.7rem"><?= htmlspecialchars($r['sertifika_no']) ?></span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reportData)): ?>
                    <tr><td colspan="14" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox me-2"></i>Veri bulunamadı. Filtre uygulayarak daraltın veya sıfırlayın.
                    </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── FİRMA RAPORU ────────────────────────────────────────── -->
            <?php elseif ($rapor === 'firma'): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-secondary"><?= count($reportData) ?> firma</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Firma Adı</th>
                            <th>Vergi No</th>
                            <th class="text-center">Çalışan</th>
                            <th class="text-center">Toplam Kayıt</th>
                            <th class="text-center">Devam Eden</th>
                            <th class="text-center">Tamamlanan</th>
                            <th class="text-center">Tamamlanma %</th>
                            <th class="text-center">Ort. İlerleme</th>
                            <th class="text-center">Sertifika</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reportData as $r):
                        $pct = $r['toplam_kayit'] > 0 ? round(($r['tamamlanan'] / $r['toplam_kayit']) * 100) : 0;
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($r['firma']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($r['tax_number'] ?? '-') ?></td>
                        <td class="text-center"><?= $r['calisan_sayisi'] ?></td>
                        <td class="text-center fw-semibold"><?= $r['toplam_kayit'] ?></td>
                        <td class="text-center text-primary"><?= $r['devam_eden'] ?></td>
                        <td class="text-center text-success fw-semibold"><?= $r['tamamlanan'] ?></td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <div class="progress flex-fill progress-sm" style="min-width:50px">
                                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small class="fw-semibold">%<?= $pct ?></small>
                            </div>
                        </td>
                        <td class="text-center">%<?= $r['ort_ilerleme'] ?? 0 ?></td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark"><?= $r['sertifika_sayisi'] ?></span>
                        </td>
                        <td>
                            <a href="?rapor=katilimci&firm_id=<?= $r['firm_id'] ?>"
                               class="btn btn-xs btn-outline-primary btn-sm" style="font-size:.72rem;padding:2px 8px;">
                                <i class="bi bi-person-lines-fill me-1"></i>Katılımcılar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reportData)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">Veri bulunamadı.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── SERTİFİKA YENİLEME ──────────────────────────────────── -->
            <?php elseif ($rapor === 'yenileme'): ?>
            <?php
                $expiryLabel = match((int)($filters['expiry_days'] ?? 30)) {
                    0  => 'Süresi Dolmuş Sertifikalar',
                    30 => 'Önümüzdeki 30 Gün İçinde Yenilenmesi Gereken',
                    60 => 'Önümüzdeki 60 Gün İçinde Yenilenmesi Gereken',
                    90 => 'Önümüzdeki 90 Gün İçinde Yenilenmesi Gereken',
                    default => 'Sertifika Yenileme Listesi',
                };
            ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-arrow-clockwise fs-5"></i>
                <span><strong><?= htmlspecialchars($expiryLabel) ?></strong> — <?= count($reportData) ?> sertifika</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Firma</th>
                            <th>Kurs</th>
                            <th>Kategori</th>
                            <th>Sertifika No</th>
                            <th class="text-center">Veriliş</th>
                            <th class="text-center">Son Geçerlilik</th>
                            <th class="text-center">Kalan Gün</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reportData as $r):
                        $remaining = (int)($r['kalan_gun'] ?? -1);
                        $rowClass  = $remaining < 0 ? 'table-danger' : ($remaining <= 30 ? 'table-warning' : '');
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td class="fw-semibold"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($r['email']) ?></td>
                        <td><?= $r['firma'] ? htmlspecialchars($r['firma']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= htmlspecialchars($r['kurs']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['kategori'] ?? '—') ?></span></td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['sertifika_no']) ?></span></td>
                        <td class="text-center"><?= $r['verilis_tarihi'] ? date('d.m.Y', strtotime($r['verilis_tarihi'])) : '—' ?></td>
                        <td class="text-center fw-semibold"><?= $r['son_gecerlilik_tarihi'] ? date('d.m.Y', strtotime($r['son_gecerlilik_tarihi'])) : '—' ?></td>
                        <td class="text-center">
                            <?php if ($remaining < 0): ?>
                            <span class="badge bg-danger">Doldu</span>
                            <?php elseif ($remaining <= 30): ?>
                            <span class="badge bg-warning text-dark"><?= $remaining ?> gün</span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?= $remaining ?> gün</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reportData)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Bu periyotta yenilenmesi gereken sertifika bulunamadı.
                    </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── SİSTEM RAPORU ───────────────────────────────────────── -->
            <?php elseif ($rapor === 'sistem'): ?>
            <div class="row g-3 mb-4">
                <?php
                $statCards = [
                    ['label' => 'Toplam Kullanıcı',    'val' => $sistemStats['toplam_kullanici'], 'icon' => 'bi-people-fill',     'color' => 'primary'],
                    ['label' => 'Toplam Öğrenci',       'val' => $sistemStats['toplam_ogrenci'],  'icon' => 'bi-mortarboard-fill', 'color' => 'info'],
                    ['label' => 'Toplam Kurs',          'val' => $sistemStats['toplam_kurs'],      'icon' => 'bi-book-fill',        'color' => 'success'],
                    ['label' => 'Toplam Kayıt',         'val' => $sistemStats['toplam_kayit'],     'icon' => 'bi-person-check-fill','color' => 'warning'],
                    ['label' => 'Tamamlanan Eğitim',    'val' => $sistemStats['tamamlanan'],       'icon' => 'bi-check2-circle',    'color' => 'success'],
                    ['label' => 'Verilen Sertifika',    'val' => $sistemStats['toplam_sertifika'], 'icon' => 'bi-award-fill',       'color' => 'info'],
                    ['label' => 'Toplam Firma',         'val' => $sistemStats['toplam_firma'],     'icon' => 'bi-building-fill',    'color' => 'secondary'],
                    ['label' => 'Şu An Aktif (15 dk)',  'val' => $sistemStats['aktif_kullanici'],  'icon' => 'bi-circle-fill',      'color' => 'danger'],
                ];
                foreach ($statCards as $card):
                ?>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <div class="rounded-circle bg-<?= $card['color'] ?> bg-opacity-10 text-<?= $card['color'] ?> p-3">
                                <i class="bi <?= $card['icon'] ?> fs-4"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold"><?= number_format($card['val']) ?></div>
                                <div class="text-muted small"><?= $card['label'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="alert alert-info d-flex align-items-center gap-2">
                <i class="bi bi-circle-fill text-danger me-1"></i>
                <span>
                    <strong>Canlı:</strong> Son 15 dakika içinde giriş yapmış
                    <strong><?= $sistemStats['aktif_kullanici'] ?></strong> kullanıcı şu an aktif.
                </span>
            </div>
            <div class="mt-3 d-flex gap-2">
                <a href="<?= dlUrl('excel', 'sistem', []) ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel İndir
                </a>
                <a href="<?= dlUrl('pdf', 'sistem', []) ?>" class="btn btn-sm btn-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF İndir
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// User multi-select with Tom Select
var userSelect = document.getElementById('userMultiSelect');
if (userSelect) {
    new TomSelect(userSelect, {
        plugins: ['remove_button', 'checkbox_options'],
        placeholder: 'Tüm kullanıcılar (arama yapın)...',
        maxOptions: 2000,
        closeAfterSelect: false,
        hideSelected: false,
    });
}

// Package select with Tom Select (searchable)
var pkgSelect = document.getElementById('packageSelect');
if (pkgSelect) {
    new TomSelect(pkgSelect, {
        placeholder: 'Tüm Paketler & Dersler',
        allowEmptyOption: true,
        maxOptions: 5000,
    });
}
</script>
<?php include __DIR__ . '/../_layout_end.php'; ?>
