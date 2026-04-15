<?php
// Public/Student face-to-face attendance controller
// Routes: /attend, /attend/:token, /attend/:token/tamamla

$seg    = array_values(array_filter(explode('/', ltrim($uri, '/')), fn($s) => $s !== ''));
$token  = $seg[1] ?? '';      // qr_token or empty
$subsub = $seg[2] ?? '';      // 'tamamla' or empty
$loggedIn = $auth->check();
$userId   = $loggedIn ? $auth->id() : 0;

// ── /attend  (code entry form) ─────────────────────────────────────────────
if (!$token) {
    if ($method === 'POST') {
        $code = strtoupper(trim($_POST['attendance_code'] ?? ''));
        $sess = $code ? $db->fetch(
            "SELECT id, qr_token FROM face_to_face_sessions WHERE attendance_code = ? AND status IN ('scheduled','active')",
            [$code]
        ) : null;
        if ($sess) {
            redirect('/attend/' . $sess['qr_token']);
        }
        view('public/attend_code', ['error' => 'Geçersiz veya süresi dolmuş ders kodu.', 'flash' => []]);
    } else {
        view('public/attend_code', ['error' => null, 'flash' => flash()]);
    }
    return;
}

// ── /attend/:token ─────────────────────────────────────────────────────────
$sess = $db->fetch(
    'SELECT s.*, c.title AS course_title, c.category_id,
            f.name AS firm_name,
            u.first_name AS tr_first, u.last_name AS tr_last
     FROM face_to_face_sessions s
     JOIN courses c ON s.course_id = c.id
     LEFT JOIN firms f ON s.firm_id = f.id
     LEFT JOIN users u ON s.trainer_id = u.id
     WHERE s.qr_token = ?',
    [$token]
);

if (!$sess) {
    http_response_code(404);
    view('404');
    return;
}

$sessId    = (int)$sess['id'];
$now       = new DateTime();
$startDt   = new DateTime($sess['scheduled_at']);
$endDt     = (clone $startDt)->modify('+' . $sess['duration_minutes'] . ' minutes');

$beforeStart = $now < $startDt->modify('-15 minutes');   // more than 15 min before
$afterEnd    = $now > $endDt;
$isActive    = in_array($sess['status'], ['scheduled', 'active']);

// Existing attendance for this user
$existingAttendance = $loggedIn ? $db->fetch(
    'SELECT * FROM face_to_face_attendance WHERE session_id = ? AND user_id = ?',
    [$sessId, $userId]
) : null;

// ── POST: mark attendance ──────────────────────────────────────────────────
if ($method === 'POST' && $subsub === '') {
    if (!$loggedIn) {
        redirect('/giris?redirect=/attend/' . urlencode($token), 'Katılım için giriş yapmanız gerekiyor.');
    }
    if (!$isActive) {
        redirect('/attend/' . $token, 'Bu oturum aktif değil.', 'warning');
    }
    if ($afterEnd) {
        redirect('/attend/' . $token, 'Oturum sona erdi, katılım kaydı alınamıyor.', 'warning');
    }
    if ($existingAttendance) {
        redirect('/attend/' . $token, 'Katılımınız zaten kayıtlı.', 'info');
    }

    $joinMethod = isset($_POST['via_qr']) ? 'qr' : 'code';
    $ip         = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    $ua         = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $db->query(
        'INSERT INTO face_to_face_attendance (session_id, user_id, join_method, ip_address, user_agent) VALUES (?,?,?,?,?)',
        [$sessId, $userId, $joinMethod, $ip, $ua]
    );
    redirect('/attend/' . $token, 'Katılımınız başarıyla kaydedildi!');
}

// ── POST: tamamla (completion question) ───────────────────────────────────
if ($method === 'POST' && $subsub === 'tamamla') {
    if (!$loggedIn || !$existingAttendance) {
        redirect('/attend/' . $token);
    }
    $answer = strtoupper(trim($_POST['answer'] ?? ''));
    $db->query(
        'UPDATE face_to_face_attendance SET completed = 1, completion_answer = ? WHERE session_id = ? AND user_id = ?',
        [$answer ?: null, $sessId, $userId]
    );

    // Mark the YYZ course module enrollment as completed
    $yyzcourseId = (int)$sess['course_id'];
    $db->query(
        "UPDATE enrollments SET status = 'completed', progress_percent = 100, completed_at = NOW()
         WHERE user_id = ? AND course_id = ? AND status != 'completed'",
        [$userId, $yyzcourseId]
    );

    // Unlock next module in the package sequence
    if ($yyzcourseId) {
        (new \ISG\PackageEnroller($db))->onModuleCompleted($userId, $yyzcourseId);
    }

    redirect('/attend/' . $token . '/tamam');
}

// ── GET: tamam (completion success page) ──────────────────────────────────
if ($subsub === 'tamam') {
    $myAttendance = $loggedIn ? $db->fetch(
        'SELECT * FROM face_to_face_attendance WHERE session_id = ? AND user_id = ?',
        [$sessId, $userId]
    ) : null;
    view('public/attend_done', compact('sess', 'myAttendance'));
    return;
}

// ── GET: show session info page ────────────────────────────────────────────
$completionOpts = json_decode($sess['completion_options'] ?? '[]', true) ?: [];
$correctAnswer  = $sess['completion_answer'] ?? null;

$flash = flash();
view('public/attend', compact(
    'sess', 'token', 'loggedIn', 'userId',
    'existingAttendance', 'isActive', 'afterEnd', 'beforeStart',
    'completionOpts', 'correctAnswer', 'flash'
));
