<?php
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

function yyz_gen_code(int $len = 6): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $code = '';
        for ($i = 0; $i < $len; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
    } while (false); // uniqueness checked by caller
    return $code;
}

function yyz_gen_token(): string {
    return bin2hex(random_bytes(24));
}

function yyz_qr_svg(string $url): string {
    $qr     = QrCode::create($url)
        ->setSize(220)
        ->setMargin(10)
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh());
    $writer = new SvgWriter();
    return $writer->write($qr)->getString();
}

// segments: /admin/yyz[/sub[/id[/action]]]
$seg     = array_values(array_filter(explode('/', ltrim($uri, '/')), fn($s) => $s !== ''));
// $seg[0] = 'admin', $seg[1] = 'yyz', $seg[2] = id|'yeni'|null, $seg[3] = sub-action
$sub     = $seg[2] ?? '';      // id or 'yeni'
$subsub  = $seg[3] ?? '';      // duzenle / durum / ekle / kaldir / canli / pdf
$sessId  = is_numeric($sub) ? (int)$sub : 0;

$host    = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $scheme . '://' . $host;

// ── AJAX: live attendance JSON ─────────────────────────────────────────────
if ($sessId && $subsub === 'canli' && $method === 'GET') {
    $rows = $db->fetchAll(
        'SELECT u.first_name, u.last_name, u.email, a.joined_at, a.join_method, a.completed, a.completion_answer
         FROM face_to_face_attendance a
         JOIN users u ON a.user_id = u.id
         WHERE a.session_id = ?
         ORDER BY a.joined_at DESC',
        [$sessId]
    );
    header('Content-Type: application/json');
    echo json_encode(['count' => count($rows), 'rows' => $rows]);
    exit;
}

// ── AJAX: POST durum (status change) ──────────────────────────────────────
if ($sessId && $subsub === 'durum' && $method === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    $allowed   = ['scheduled', 'active', 'completed', 'cancelled'];
    if (in_array($newStatus, $allowed)) {
        $db->query('UPDATE face_to_face_sessions SET status = ? WHERE id = ?', [$newStatus, $sessId]);
    }
    redirect("/admin/yyz/{$sessId}", 'Durum güncellendi.');
}

// ── Admin manual add participant ───────────────────────────────────────────
if ($sessId && $subsub === 'ekle' && $method === 'POST') {
    $targetEmail = trim($_POST['email'] ?? '');
    $targetUser  = $targetEmail ? $db->fetch('SELECT id FROM users WHERE email = ?', [$targetEmail]) : null;
    if ($targetUser) {
        try {
            $db->query(
                'INSERT INTO face_to_face_attendance (session_id, user_id, join_method) VALUES (?,?,?)',
                [$sessId, $targetUser['id'], 'admin']
            );
            redirect("/admin/yyz/{$sessId}", 'Katılımcı eklendi.');
        } catch (\Throwable) {
            redirect("/admin/yyz/{$sessId}", 'Bu kullanıcı zaten kayıtlı.', 'warning');
        }
    } else {
        redirect("/admin/yyz/{$sessId}", 'Kullanıcı bulunamadı.', 'danger');
    }
}

// ── Remove participant ─────────────────────────────────────────────────────
if ($sessId && $subsub === 'kaldir' && $method === 'POST') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid) {
        $db->query('DELETE FROM face_to_face_attendance WHERE session_id = ? AND user_id = ?', [$sessId, $uid]);
    }
    redirect("/admin/yyz/{$sessId}", 'Katılımcı kaldırıldı.');
}

// ── PDF Yoklama Tutanağı ───────────────────────────────────────────────────
if ($sessId && $subsub === 'pdf') {
    $sess = $db->fetch(
        'SELECT s.*, c.title AS course_title, f.name AS firm_name, u.first_name AS tr_first, u.last_name AS tr_last
         FROM face_to_face_sessions s
         JOIN courses c ON s.course_id = c.id
         LEFT JOIN firms f ON s.firm_id = f.id
         LEFT JOIN users u ON s.trainer_id = u.id
         WHERE s.id = ?',
        [$sessId]
    );
    if (!$sess) { redirect('/admin/yyz'); }

    $attendees = $db->fetchAll(
        'SELECT u.first_name, u.last_name, u.email, a.joined_at, a.join_method, a.completed
         FROM face_to_face_attendance a
         JOIN users u ON a.user_id = u.id
         WHERE a.session_id = ?
         ORDER BY u.last_name, u.first_name',
        [$sessId]
    );

    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('İSG Eğitim Platformu');
    $pdf->SetTitle('Yüz Yüze Eğitim Yoklama Tutanağı');
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(true, 25);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'YÜZ YÜZE EĞİTİM YOKLAMA TUTANAĞI', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 5, 'T.C. İş Sağlığı ve Güvenliği Eğitim Platformu — 6331 Sayılı Kanun', 0, 1, 'C');
    $pdf->Ln(4);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(0, 86, 149);
    $pdf->SetTextColor(255);
    $pdf->Cell(0, 7, '  EĞİTİM BİLGİLERİ', 0, 1, 'L', true);
    $pdf->SetTextColor(0);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Ln(2);

    $infoRows = [
        ['Eğitim Başlığı', $sess['title']],
        ['Kurs', $sess['course_title']],
        ['Firma', $sess['firm_name'] ?? 'Genel'],
        ['Eğitmen', trim(($sess['tr_first'] ?? '') . ' ' . ($sess['tr_last'] ?? '')) ?: 'Belirtilmedi'],
        ['Tarih / Saat', date('d.m.Y H:i', strtotime($sess['scheduled_at']))],
        ['Süre', $sess['duration_minutes'] . ' dakika'],
        ['Konum / Yer', $sess['location'] ?? 'Belirtilmedi'],
        ['Durum', strtoupper($sess['status'])],
        ['Ders Kodu', $sess['attendance_code']],
        ['Toplam Katılımcı', count($attendees)],
    ];
    foreach ($infoRows as [$k, $v]) {
        $pdf->SetFont('helvetica', 'B', 9); $pdf->Cell(55, 6, $k . ':', 0, 0);
        $pdf->SetFont('helvetica', '', 9);  $pdf->Cell(0, 6, $v, 0, 1);
    }
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(0, 86, 149);
    $pdf->SetTextColor(255);
    $pdf->Cell(0, 7, '  KATILIMCI LİSTESİ', 0, 1, 'L', true);
    $pdf->SetTextColor(0);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(220, 230, 241);
    $pdf->Cell(8,  7, 'No',         1, 0, 'C', true);
    $pdf->Cell(55, 7, 'Ad Soyad',   1, 0, 'C', true);
    $pdf->Cell(65, 7, 'E-posta',    1, 0, 'C', true);
    $pdf->Cell(35, 7, 'Katılım Saati', 1, 0, 'C', true);
    $pdf->Cell(22, 7, 'Yöntem',     1, 0, 'C', true);
    $pdf->Cell(30, 7, 'İmza',       1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 8);
    foreach ($attendees as $i => $a) {
        $pdf->Cell(8,  6, $i + 1, 1, 0, 'C');
        $pdf->Cell(55, 6, mb_strtoupper($a['last_name']) . ' ' . $a['first_name'], 1, 0);
        $pdf->Cell(65, 6, $a['email'], 1, 0);
        $pdf->Cell(35, 6, $a['joined_at'] ? date('d.m.Y H:i', strtotime($a['joined_at'])) : '—', 1, 0, 'C');
        $pdf->Cell(22, 6, strtoupper($a['join_method']), 1, 0, 'C');
        $pdf->Cell(30, 6, '', 1, 1);
    }

    // Empty rows for manual additions
    for ($i = count($attendees); $i < max(count($attendees) + 3, 10); $i++) {
        $pdf->Cell(8, 6, $i + 1, 1, 0, 'C');
        $pdf->Cell(55, 6, '', 1, 0);
        $pdf->Cell(65, 6, '', 1, 0);
        $pdf->Cell(35, 6, '', 1, 0);
        $pdf->Cell(22, 6, '', 1, 0);
        $pdf->Cell(30, 6, '', 1, 1);
    }

    $pdf->Ln(8);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(90, 6, 'Eğitmen İmzası: ___________________________', 0, 0);
    $pdf->Cell(90, 6, 'İşveren / Yetkili İmzası: ___________________________', 0, 1);
    $pdf->Ln(2);
    $pdf->Cell(0, 5, 'Tarih: ' . date('d.m.Y'), 0, 1);
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->Cell(0, 5, 'Bu tutanak 6331 Sayılı İş Sağlığı ve Güvenliği Kanunu kapsamında düzenlenmiştir. İSG Eğitim Platformu — ' . $baseUrl, 0, 1, 'C');

    $filename = 'yoklama-' . $sessId . '-' . date('Ymd') . '.pdf';
    $pdf->Output($filename, 'I');
    exit;
}

// ── Session DETAIL ─────────────────────────────────────────────────────────
if ($sessId && $subsub === '') {
    $sess = $db->fetch(
        'SELECT s.*, c.title AS course_title, c.duration_minutes AS course_dur,
                f.name AS firm_name, u.first_name AS tr_first, u.last_name AS tr_last
         FROM face_to_face_sessions s
         JOIN courses c ON s.course_id = c.id
         LEFT JOIN firms f ON s.firm_id = f.id
         LEFT JOIN users u ON s.trainer_id = u.id
         WHERE s.id = ?',
        [$sessId]
    );
    if (!$sess) { redirect('/admin/yyz'); }

    $attendees = $db->fetchAll(
        'SELECT a.*, u.first_name, u.last_name, u.email, u.id AS uid
         FROM face_to_face_attendance a
         JOIN users u ON a.user_id = u.id
         WHERE a.session_id = ?
         ORDER BY a.joined_at DESC',
        [$sessId]
    );

    $attendUrl = $baseUrl . '/attend/' . $sess['qr_token'];
    $qrSvg     = yyz_qr_svg($attendUrl);

    $flash = flash();
    view('admin/yyz_detay', compact('sess', 'attendees', 'qrSvg', 'attendUrl', 'flash'));
    return;
}

// ── SESSION EDIT ───────────────────────────────────────────────────────────
if ($sessId && $subsub === 'duzenle') {
    $sess    = $db->fetch('SELECT * FROM face_to_face_sessions WHERE id = ?', [$sessId]);
    if (!$sess) { redirect('/admin/yyz'); }
    $courses = $db->fetchAll("SELECT id, title FROM courses WHERE delivery_method='yuz_yuze' AND status='active' ORDER BY title");
    $firms   = $db->fetchAll("SELECT id, name FROM firms WHERE status='active' ORDER BY name");
    $trainers= $db->fetchAll("SELECT id, first_name, last_name, email FROM users WHERE role_id IN (1,2,5) ORDER BY last_name");

    if ($method === 'POST') {
        $db->query(
            'UPDATE face_to_face_sessions SET course_id=?, firm_id=?, trainer_id=?, title=?,
             scheduled_at=?, duration_minutes=?, location=?, max_participants=?,
             completion_question=?, completion_options=?, completion_answer=?, notes=?
             WHERE id=?',
            [
                (int)($_POST['course_id']??0),
                ($_POST['firm_id']??'') ?: null,
                ($_POST['trainer_id']??'') ?: null,
                trim($_POST['title']??''),
                $_POST['scheduled_at']??'',
                (int)($_POST['duration_minutes']??120),
                trim($_POST['location']??'') ?: null,
                ($_POST['max_participants']??'') ?: null,
                trim($_POST['completion_question']??'') ?: null,
                json_encode(array_filter(array_map('trim', [
                    $_POST['opt_a']??'', $_POST['opt_b']??'', $_POST['opt_c']??'', $_POST['opt_d']??''
                ]))),
                ($_POST['completion_answer']??'') ?: null,
                trim($_POST['notes']??'') ?: null,
                $sessId,
            ]
        );
        redirect("/admin/yyz/{$sessId}", 'Oturum güncellendi.');
    }

    $flash = flash();
    $opts = json_decode($sess['completion_options'] ?? '[]', true) ?: [];
    view('admin/yyz_form', compact('sess', 'courses', 'firms', 'trainers', 'flash', 'opts'));
    return;
}

// ── NEW SESSION ────────────────────────────────────────────────────────────
if ($sub === 'yeni' || ($sub === '' && $method === 'POST')) {
    $courses = $db->fetchAll("SELECT id, title FROM courses WHERE delivery_method='yuz_yuze' AND status='active' ORDER BY title");
    $firms   = $db->fetchAll("SELECT id, name FROM firms WHERE status='active' ORDER BY name");
    $trainers= $db->fetchAll("SELECT id, first_name, last_name, email FROM users WHERE role_id IN (1,2,5) ORDER BY last_name");
    $error   = null;
    $sess    = null;
    $opts    = [];

    if ($method === 'POST') {
        $courseId = (int)($_POST['course_id']??0);
        $title    = trim($_POST['title']??'');

        if (!$courseId || !$title) {
            $error = 'Kurs ve başlık zorunludur.';
        } else {
            // Generate unique codes
            $code = yyz_gen_code(6);
            $token = yyz_gen_token();

            $db->query(
                'INSERT INTO face_to_face_sessions
                 (course_id, firm_id, trainer_id, title, scheduled_at, duration_minutes,
                  location, max_participants, attendance_code, qr_token,
                  completion_question, completion_options, completion_answer, notes)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $courseId,
                    ($_POST['firm_id']??'') ?: null,
                    ($_POST['trainer_id']??'') ?: null,
                    $title,
                    $_POST['scheduled_at']??date('Y-m-d\TH:i'),
                    (int)($_POST['duration_minutes']??120),
                    trim($_POST['location']??'') ?: null,
                    ($_POST['max_participants']??'') ?: null,
                    $code,
                    $token,
                    trim($_POST['completion_question']??'') ?: null,
                    json_encode(array_filter(array_map('trim', [
                        $_POST['opt_a']??'', $_POST['opt_b']??'', $_POST['opt_c']??'', $_POST['opt_d']??''
                    ]))),
                    ($_POST['completion_answer']??'') ?: null,
                    trim($_POST['notes']??'') ?: null,
                ]
            );
            $newId = (int)$db->pdo()->lastInsertId();
            redirect("/admin/yyz/{$newId}", 'Yüz yüze oturum oluşturuldu!');
        }
    }

    $flash = flash();
    view('admin/yyz_form', compact('sess', 'courses', 'firms', 'trainers', 'error', 'flash', 'opts'));
    return;
}

// ── SESSION LIST (default) ─────────────────────────────────────────────────
$filterStatus = $_GET['durum'] ?? '';
$filterFirm   = (int)($_GET['firma'] ?? 0);

$where  = '1=1';
$params = [];
if ($filterStatus) { $where .= ' AND s.status = ?'; $params[] = $filterStatus; }
if ($filterFirm)   { $where .= ' AND s.firm_id = ?'; $params[] = $filterFirm; }

$sessions = $db->fetchAll(
    "SELECT s.*, c.title AS course_title, f.name AS firm_name,
            (SELECT COUNT(*) FROM face_to_face_attendance a WHERE a.session_id = s.id) AS attendee_count
     FROM face_to_face_sessions s
     JOIN courses c ON s.course_id = c.id
     LEFT JOIN firms f ON s.firm_id = f.id
     WHERE {$where}
     ORDER BY s.scheduled_at DESC
     LIMIT 100",
    $params
);
$firms = $db->fetchAll("SELECT id, name FROM firms WHERE status='active' ORDER BY name");
$flash = flash();
view('admin/yyz_list', compact('sessions', 'firms', 'filterStatus', 'filterFirm', 'flash'));
