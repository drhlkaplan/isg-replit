<?php
$pageTitle = 'Denetim Günlüğü — ' . APP_NAME;
include __DIR__ . '/../_layout.php';

$actionLabels = [
    'login'                    => ['Giriş', 'success'],
    'logout'                   => ['Çıkış', 'secondary'],
    'user_create'              => ['Kullanıcı Oluşturuldu', 'primary'],
    'user_update'              => ['Kullanıcı Güncellendi', 'info'],
    'user_delete'              => ['Kullanıcı Silindi', 'danger'],
    'course_create'            => ['Kurs Oluşturuldu', 'primary'],
    'course_update'            => ['Kurs Güncellendi', 'info'],
    'course_delete'            => ['Kurs Silindi', 'danger'],
    'course_complete'          => ['Kurs Tamamlandı', 'success'],
    'firm_create'              => ['Firma Oluşturuldu', 'primary'],
    'firm_update'              => ['Firma Güncellendi', 'info'],
    'firm_delete'              => ['Firma Silindi', 'danger'],
    'exam_pass'                => ['Sınav Geçti', 'success'],
    'exam_fail'                => ['Sınav Kaldı', 'danger'],
    'cert_issue'               => ['Sertifika Verildi', 'success'],
    'cert_revoke'              => ['Sertifika İptal', 'danger'],
    'cert_restore'             => ['Sertifika Etkinleştirildi', 'warning'],
    'enrollment_due_date_set'  => ['Son Tarih Ayarlandı', 'warning'],
    'maintenance_on'           => ['Bakım Modu Açıldı', 'danger'],
    'maintenance_off'          => ['Bakım Modu Kapatıldı', 'success'],
    'backup_download'          => ['Yedek İndirildi', 'info'],
];
?>
<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Denetim Günlüğü</h4>
        <span class="badge bg-secondary fs-6"><?= number_format($total) ?> kayıt</span>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="/admin/denetim-gunlugu" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label form-label-sm fw-semibold">Kullanıcı Ara</label>
                    <input type="text" name="ara" class="form-control form-control-sm" placeholder="Ad, soyad, e-posta..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm fw-semibold">Eylem</label>
                    <select name="eylem" class="form-select form-select-sm">
                        <option value="">— Tümü —</option>
                        <?php foreach ($actions as $a): ?>
                        <option value="<?= htmlspecialchars($a['action']) ?>" <?= $action === $a['action'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($actionLabels[$a['action']][0] ?? $a['action']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm fw-semibold">Başlangıç</label>
                    <input type="date" name="tarih_baslangic" class="form-control form-control-sm" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm fw-semibold">Bitiş</label>
                    <input type="date" name="tarih_bitis" class="form-control form-control-sm" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th style="width:145px">Tarih</th>
                        <th>Kullanıcı</th>
                        <th>Eylem</th>
                        <th>Varlık</th>
                        <th>IP Adresi</th>
                        <th>Detaylar</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>Kayıt bulunamadı.
                </td></tr>
                <?php else: ?>
                <?php foreach ($logs as $log):
                    $al = $actionLabels[$log['action']] ?? [$log['action'], 'secondary'];
                    $details = null;
                    if ($log['details']) {
                        $details = json_decode($log['details'], true);
                    }
                ?>
                <tr>
                    <td class="text-muted" style="white-space:nowrap">
                        <?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?>
                    </td>
                    <td>
                        <?php if ($log['first_name']): ?>
                        <span class="fw-semibold"><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></span>
                        <br><span class="text-muted"><?= htmlspecialchars($log['email'] ?? '') ?></span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $al[1] ?>"><?= htmlspecialchars($al[0]) ?></span>
                    </td>
                    <td class="text-muted">
                        <?php if ($log['entity_type']): ?>
                        <?= htmlspecialchars($log['entity_type']) ?>
                        <?php if ($log['entity_id']): ?>
                        <span class="text-muted">#<?= $log['entity_id'] ?></span>
                        <?php endif; ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td class="text-muted font-monospace"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                    <td>
                        <?php if ($details): ?>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#detail-<?= $log['id'] ?>">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="collapse mt-1" id="detail-<?= $log['id'] ?>">
                            <pre class="mb-0 p-2 bg-light rounded small" style="font-size:.75rem;max-width:300px;white-space:pre-wrap"><?= htmlspecialchars(json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
                        </div>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pages > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['sayfa' => $p])) ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
