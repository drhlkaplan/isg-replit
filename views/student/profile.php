<?php
$pageTitle = 'Profilim — ' . APP_NAME;
include __DIR__ . '/../_layout.php';
?>
<div class="container-xl px-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-person-circle me-2 text-primary"></i>Profilim</h4>

    <?php if (!empty($flash['msg'])): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left: Profile forms -->
        <div class="col-lg-5">
            <!-- Personal Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-person me-2"></i>Kişisel Bilgiler</div>
                <div class="card-body">
                    <form method="POST" action="/ogrenci/profil">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="profile">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ad *</label>
                                <input type="text" name="first_name" class="form-control" required
                                       value="<?= htmlspecialchars($user['first_name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Soyad *</label>
                                <input type="text" name="last_name" class="form-control" required
                                       value="<?= htmlspecialchars($user['last_name']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">E-posta</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                                <small class="text-muted">E-posta değiştirmek için yöneticinize başvurun.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Telefon</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">TC Kimlik No</label>
                                <input type="text" name="tc_identity_no" class="form-control" maxlength="11"
                                       value="<?= htmlspecialchars($user['tc_identity_no'] ?? '') ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-check-lg me-1"></i>Bilgileri Güncelle
                        </button>
                    </form>
                </div>
            </div>

            <!-- Password Change -->
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-lock me-2"></i>Şifre Değiştir</div>
                <div class="card-body">
                    <form method="POST" action="/ogrenci/profil">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="password">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mevcut Şifre</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Yeni Şifre</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Yeni Şifre (Tekrar)</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 mt-3">
                            <i class="bi bi-lock me-1"></i>Şifreyi Değiştir
                        </button>
                    </form>
                </div>
            </div>

            <!-- Firma Kodu -->
            <div class="card shadow-sm mb-4 border-primary border-opacity-25">
                <div class="card-header bg-primary bg-opacity-10 fw-semibold text-primary-emphasis">
                    <i class="bi bi-building me-2"></i>Firma Bağlantısı
                </div>
                <div class="card-body">
                    <?php if (!empty($firmInfo)): ?>
                    <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded" style="background:var(--isg-primary,#005695)11">
                        <?php if (!empty($firmInfo['logo_path']) && file_exists(LOGO_DIR . $firmInfo['logo_path'])): ?>
                        <img src="<?= LOGO_URL . htmlspecialchars($firmInfo['logo_path']) ?>" alt="" style="max-height:36px;max-width:100px">
                        <?php else: ?>
                        <i class="bi bi-building fs-3 text-primary"></i>
                        <?php endif; ?>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($firmInfo['name']) ?></div>
                            <small class="text-muted">Bağlı olduğunuz firma</small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <p class="text-muted small mb-3">
                        Firma kodunuzu girerek kurumunuzun temasını ve özel eğitim içeriklerini görüntüleyebilirsiniz. Kodu sorumlu yöneticinizden alın.
                    </p>
                    <form method="POST" action="/ogrenci/profil">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="firma_kodu">
                        <div class="input-group">
                            <input type="text" name="firma_kodu" class="form-control font-monospace text-uppercase"
                                   placeholder="FİRMA KODU (örn: ACME)" maxlength="50"
                                   oninput="this.value = this.value.toUpperCase()">
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-link-45deg me-1"></i>Bağlan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Group Key -->
            <div class="card shadow-sm border-warning border-opacity-50">
                <div class="card-header bg-warning bg-opacity-10 fw-semibold text-warning-emphasis">
                    <i class="bi bi-key me-2"></i>Grup Anahtarı Kullan
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Eğitim sorumlusunuzdan aldığınız grup anahtarını girerek ilgili kurslara otomatik kaydolun.
                    </p>
                    <form method="POST" action="/ogrenci/profil">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="group_key">
                        <div class="input-group">
                            <input type="text" name="group_key_code" class="form-control font-monospace text-uppercase"
                                   placeholder="ÖRNEK2024" maxlength="50" required
                                   oninput="this.value = this.value.toUpperCase()">
                            <button type="submit" class="btn btn-warning fw-semibold">
                                <i class="bi bi-arrow-right-circle me-1"></i>Kullan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Training History -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-2"></i>Eğitim Geçmişim</span>
                    <span class="badge bg-primary"><?= count($history) ?> kayıt</span>
                </div>
                <?php if (empty($history)): ?>
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>Henüz herhangi bir eğitim paketine kayıtlı değilsiniz.
                    <br><small>Kayıt için yöneticinize veya eğitmeninize başvurun ya da bir grup anahtarı kullanın.</small>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Kurs</th>
                                <th>İlerleme</th>
                                <th>Durum</th>
                                <th>Kayıt</th>
                                <th>Sertifika</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($history as $h):
                            $stMap = [
                                'enrolled'    => ['bg-secondary', 'Kayıtlı'],
                                'in_progress' => ['bg-warning text-dark', 'Devam Ediyor'],
                                'completed'   => ['bg-success', 'Tamamlandı'],
                                'failed'      => ['bg-danger', 'Başarısız'],
                            ];
                            $st = $stMap[$h['status']] ?? ['bg-secondary', $h['status']];
                        ?>
                        <tr>
                            <td>
                                <a href="/ogrenci/kurs/<?= $h['course_id'] ?>" class="fw-semibold text-decoration-none">
                                    <?= htmlspecialchars(mb_substr($h['title'], 0, 40)) ?><?= mb_strlen($h['title']) > 40 ? '…' : '' ?>
                                </a>
                                <br><span class="badge small" style="background:<?= htmlspecialchars($h['category_color']) ?>">
                                    <?= htmlspecialchars($h['category_name']) ?>
                                </span>
                            </td>
                            <td style="min-width:90px">
                                <div class="progress" style="height:5px">
                                    <div class="progress-bar bg-primary" style="width:<?= $h['progress_percent'] ?>%"></div>
                                </div>
                                <small class="text-muted">%<?= $h['progress_percent'] ?></small>
                            </td>
                            <td><span class="badge <?= $st[0] ?>"><?= $st[1] ?></span></td>
                            <td class="text-muted"><?= date('d.m.Y', strtotime($h['enrolled_at'])) ?></td>
                            <td>
                                <?php if ($h['cert_number']): ?>
                                <a href="/sertifika/indir/<?= htmlspecialchars($h['cert_number']) ?>"
                                   class="btn btn-xs btn-success btn-sm py-0">
                                    <i class="bi bi-download me-1"></i>PDF
                                </a>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../_layout_end.php'; ?>
