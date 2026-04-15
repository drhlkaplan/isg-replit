<?php
namespace ISG;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportExporter
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // ─── Shared where-clause helpers ─────────────────────────────────────────

    private function addCourseId(array &$where, array &$params, array $filters): void
    {
        if (!empty($filters['course_id'])) {
            $where[]  = 'e.course_id = ?';
            $params[] = (int)$filters['course_id'];
        }
    }

    private function addCourseType(array &$where, array $filters): void
    {
        if (($filters['course_type'] ?? '') === 'paket') {
            $where[] = 'c.parent_course_id IS NULL';
        } elseif (($filters['course_type'] ?? '') === 'ders') {
            $where[] = 'c.parent_course_id IS NOT NULL';
        }
    }

    private function addUserIds(array &$where, array &$params, array $filters): void
    {
        $ids = $filters['user_ids'] ?? [];
        if (!empty($ids)) {
            $ids = array_map('intval', (array)$ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $where[]  = "e.user_id IN ($placeholders)";
            $params   = array_merge($params, $ids);
        }
    }

    // ─── Data fetchers ───────────────────────────────────────────────────────

    public function getKursData(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['firm_id'])) {
            $where[]  = 'u.firm_id = ?';
            $params[] = $filters['firm_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[]  = 'c.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'e.enrolled_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'e.enrolled_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $where[]  = 'e.status = ?';
            $params[] = $filters['status'];
        }
        $this->addCourseId($where, $params, $filters);
        $this->addCourseType($where, $filters);
        $this->addUserIds($where, $params, $filters);

        $sql = 'SELECT c.id AS course_id,
                    CASE WHEN c.parent_course_id IS NULL THEN "Paket" ELSE "Ders" END AS tur,
                    c.title,
                    pc.title AS parent_kurs,
                    cc.name AS kategori,
                    COUNT(DISTINCT e.id)  AS toplam_kayit,
                    COUNT(DISTINCT CASE WHEN e.status IN ("in_progress","enrolled") THEN e.id END) AS devam_eden,
                    COUNT(DISTINCT CASE WHEN e.status = "completed" THEN e.id END) AS tamamlayan,
                    ROUND(AVG(e.progress_percent),1) AS ort_ilerleme,
                    ROUND((SELECT AVG(best.score) FROM
                        (SELECT ea2.user_id, MAX(ea2.score) AS score
                         FROM exam_attempts ea2
                         JOIN exams ex2 ON ex2.id = ea2.exam_id
                         WHERE ex2.course_id = c.id
                         GROUP BY ea2.user_id) best), 1) AS ort_puan,
                    COUNT(DISTINCT e.user_id) AS benzersiz_ogrenci,
                    (SELECT GROUP_CONCAT(topf.fname ORDER BY topf.cnt DESC SEPARATOR " | ")
                     FROM (SELECT u2.firm_id, f2.name AS fname, COUNT(*) AS cnt
                           FROM enrollments e2
                           JOIN users u2 ON u2.id = e2.user_id
                           JOIN firms f2 ON f2.id = u2.firm_id
                           WHERE e2.course_id = c.id AND u2.firm_id IS NOT NULL
                           GROUP BY u2.firm_id, f2.name ORDER BY cnt DESC LIMIT 3) topf) AS top_firma
                FROM courses c
                LEFT JOIN courses pc       ON pc.id = c.parent_course_id
                LEFT JOIN enrollments e    ON e.course_id = c.id
                LEFT JOIN users u          ON u.id = e.user_id
                LEFT JOIN course_categories cc ON cc.id = c.category_id
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY c.id ORDER BY toplam_kayit DESC';
        return $this->db->fetchAll($sql, $params);
    }

    public function getKullaniciData(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['firm_id'])) {
            $where[]  = 'u.firm_id = ?';
            $params[] = $filters['firm_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[]  = 'c.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'e.enrolled_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'e.enrolled_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $where[]  = 'e.status = ?';
            $params[] = $filters['status'];
        }
        $this->addCourseId($where, $params, $filters);
        $this->addCourseType($where, $filters);
        $this->addUserIds($where, $params, $filters);

        $sql = 'SELECT u.first_name, u.last_name, u.email,
                    f.name AS firma, c.title AS kurs,
                    e.status AS durum, e.progress_percent AS ilerleme,
                    e.enrolled_at AS kayit_tarihi, e.completed_at AS tamamlama_tarihi,
                    best_ea.best_score AS sinav_puani,
                    cert.cert_number AS sertifika_no
                FROM enrollments e
                JOIN users u ON u.id = e.user_id
                JOIN courses c ON c.id = e.course_id
                LEFT JOIN firms f ON f.id = u.firm_id
                LEFT JOIN (
                    SELECT ea2.user_id, ex2.course_id, MAX(ea2.score) AS best_score
                    FROM exam_attempts ea2
                    JOIN exams ex2 ON ex2.id = ea2.exam_id
                    GROUP BY ea2.user_id, ex2.course_id
                ) best_ea ON best_ea.user_id = e.user_id AND best_ea.course_id = e.course_id
                LEFT JOIN certificates cert ON cert.user_id = e.user_id AND cert.course_id = e.course_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY u.last_name, u.first_name, e.enrolled_at DESC';
        return $this->db->fetchAll($sql, $params);
    }

    public function getFirmaData(array $filters = []): array
    {
        $fWhere  = ['1=1'];
        $fParams = [];
        $eConds  = ['1=1'];
        $eParams = [];
        if (!empty($filters['firm_id'])) {
            $fWhere[]  = 'f.id = ?';
            $fParams[] = $filters['firm_id'];
        }
        if (!empty($filters['category_id'])) {
            $eConds[]  = 'course_id IN (SELECT id FROM courses WHERE category_id = ?)';
            $eParams[] = $filters['category_id'];
        }
        if (!empty($filters['course_id'])) {
            $eConds[]  = 'course_id = ?';
            $eParams[] = (int)$filters['course_id'];
        }
        if (!empty($filters['date_from'])) {
            $eConds[]  = 'enrolled_at >= ?';
            $eParams[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $eConds[]  = 'enrolled_at <= ?';
            $eParams[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $eConds[]  = 'status = ?';
            $eParams[] = $filters['status'];
        }
        $fWhereSql = implode(' AND ', $fWhere);
        $eWhere    = implode(' AND ', $eConds);
        $allParams = array_merge($fParams, $eParams);

        $sql = "SELECT f.id AS firm_id, f.name AS firma, f.tax_number,
                    COUNT(DISTINCT u.id) AS calisan_sayisi,
                    COUNT(DISTINCT e.id) AS toplam_kayit,
                    COUNT(DISTINCT CASE WHEN e.status IN ('in_progress','enrolled') THEN e.id END) AS devam_eden,
                    COUNT(DISTINCT CASE WHEN e.status = 'completed' THEN e.id END) AS tamamlanan,
                    ROUND(AVG(e.progress_percent),1) AS ort_ilerleme,
                    COUNT(DISTINCT cert.id) AS sertifika_sayisi
                FROM firms f
                LEFT JOIN users u ON u.firm_id = f.id AND u.role_id = 4
                LEFT JOIN (
                    SELECT id, user_id, status, progress_percent
                    FROM enrollments
                    WHERE $eWhere
                ) e ON e.user_id = u.id
                LEFT JOIN certificates cert ON cert.user_id = u.id
                WHERE $fWhereSql
                GROUP BY f.id ORDER BY toplam_kayit DESC";
        return $this->db->fetchAll($sql, $allParams);
    }

    public function getKatilimciData(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['firm_id'])) {
            $where[]  = 'u.firm_id = ?';
            $params[] = (int)$filters['firm_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[]  = 'c.category_id = ?';
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'e.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'e.enrolled_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'e.enrolled_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        $this->addCourseId($where, $params, $filters);
        $this->addCourseType($where, $filters);
        $this->addUserIds($where, $params, $filters);

        $sql = 'SELECT
                    u.id AS user_id,
                    u.first_name, u.last_name, u.email, u.phone, u.tc_identity_no,
                    f.name AS firma,
                    c.id AS course_id,
                    c.title AS kurs,
                    c.parent_course_id,
                    CASE WHEN c.parent_course_id IS NULL THEN "Paket" ELSE "Ders" END AS tur,
                    pc.title AS parent_kurs,
                    e.status AS durum,
                    e.enrolled_at AS baslama,
                    e.completed_at AS bitirme,
                    e.progress_percent AS ilerleme,
                    e.last_activity AS son_aktivite,
                    best_ea.best_score AS sinav_puani,
                    cert.cert_number AS sertifika_no
                FROM enrollments e
                JOIN users u   ON u.id = e.user_id
                JOIN courses c ON c.id = e.course_id
                LEFT JOIN courses pc ON pc.id = c.parent_course_id
                LEFT JOIN firms f    ON f.id = u.firm_id
                LEFT JOIN (
                    SELECT ea2.user_id, ex2.course_id, MAX(ea2.score) AS best_score
                    FROM exam_attempts ea2
                    JOIN exams ex2 ON ex2.id = ea2.exam_id
                    GROUP BY ea2.user_id, ex2.course_id
                ) best_ea ON best_ea.user_id = e.user_id AND best_ea.course_id = e.course_id
                LEFT JOIN certificates cert ON cert.user_id = e.user_id AND cert.course_id = e.course_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY u.last_name, u.first_name, ISNULL(c.parent_course_id) DESC, c.sort_order, e.enrolled_at';
        return $this->db->fetchAll($sql, $params);
    }

    public function getYenilemeData(array $filters = []): array
    {
        $where  = ['cert.is_valid = 1'];
        $params = [];

        $days = (int)($filters['expiry_days'] ?? 30);
        if ($days > 0) {
            $where[]  = 'cert.expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)';
            $params[] = $days;
        } else {
            $where[]  = 'cert.expires_at < CURDATE()';
        }

        if (!empty($filters['firm_id'])) {
            $where[]  = 'u.firm_id = ?';
            $params[] = $filters['firm_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[]  = 'c.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['course_id'])) {
            $where[]  = 'cert.course_id = ?';
            $params[] = (int)$filters['course_id'];
        }

        $sql = 'SELECT u.first_name, u.last_name, u.email,
                    f.name AS firma, c.title AS kurs,
                    cc.name AS kategori,
                    cert.cert_number AS sertifika_no,
                    cert.issued_at AS verilis_tarihi,
                    cert.expires_at AS son_gecerlilik_tarihi,
                    DATEDIFF(cert.expires_at, CURDATE()) AS kalan_gun
                FROM certificates cert
                JOIN users u    ON u.id = cert.user_id
                JOIN courses c  ON c.id = cert.course_id
                LEFT JOIN course_categories cc ON cc.id = c.category_id
                LEFT JOIN firms f ON f.id = u.firm_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY cert.expires_at ASC, u.last_name, u.first_name';
        return $this->db->fetchAll($sql, $params);
    }

    public function getSistemData(): array
    {
        return [
            'toplam_kullanici' => $this->db->count('users'),
            'toplam_ogrenci'   => $this->db->count('users', 'role_id = 4'),
            'toplam_kurs'      => $this->db->count('courses'),
            'toplam_kayit'     => $this->db->count('enrollments'),
            'tamamlanan'       => $this->db->count('enrollments', 'status = "completed"'),
            'toplam_sertifika' => $this->db->count('certificates'),
            'toplam_firma'     => $this->db->count('firms'),
            'aktif_kullanici'  => (int)$this->db->fetch(
                'SELECT COUNT(*) AS cnt FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
            )['cnt'],
        ];
    }

    // ─── Excel export ────────────────────────────────────────────────────────

    public function exportExcel(string $rapor, array $filters = []): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        switch ($rapor) {
            case 'kurs':
                $data = $this->getKursData($filters);
                $sheet->setTitle('Kurs Raporu');
                $headers = ['Tür', 'Kurs / Ders Adı', 'Paket', 'Kategori', 'Toplam Kayıt', 'Devam Eden', 'Tamamlayan', 'Ort. İlerleme %', 'Ort. Sınav Puanı', 'Benzersiz Öğrenci', 'En Çok Kayıt Yapan Firma'];
                $this->writeHeaders($sheet, $headers, $headerStyle);
                foreach ($data as $i => $row) {
                    $r = $i + 2;
                    $sheet->fromArray([
                        $row['tur'], $row['title'], $row['parent_kurs'] ?? '-',
                        $row['kategori'], $row['toplam_kayit'],
                        $row['devam_eden'], $row['tamamlayan'],
                        $row['ort_ilerleme'], $row['ort_puan'] ?? '-',
                        $row['benzersiz_ogrenci'], $row['top_firma'] ?? '-',
                    ], null, "A$r");
                }
                $this->autoSize($sheet, range('A', 'K'));
                break;

            case 'kullanici':
                $data = $this->getKullaniciData($filters);
                $sheet->setTitle('Kullanıcı Raporu');
                $headers = ['Ad', 'Soyad', 'E-posta', 'Firma', 'Kurs', 'Durum', 'İlerleme %', 'Kayıt Tarihi', 'Tamamlama', 'Sınav Puanı', 'Sertifika No'];
                $this->writeHeaders($sheet, $headers, $headerStyle);
                foreach ($data as $i => $row) {
                    $r = $i + 2;
                    $sheet->fromArray([
                        $row['first_name'], $row['last_name'], $row['email'],
                        $row['firma'] ?? '-', $row['kurs'],
                        $this->statusLabel($row['durum']),
                        $row['ilerleme'], $row['kayit_tarihi'] ?? '', $row['tamamlama_tarihi'] ?? '',
                        $row['sinav_puani'] ?? '-', $row['sertifika_no'] ?? '-',
                    ], null, "A$r");
                }
                $this->autoSize($sheet, range('A', 'K'));
                break;

            case 'firma':
                $data = $this->getFirmaData($filters);
                $sheet->setTitle('Firma Raporu');
                $headers = ['Firma', 'Vergi No', 'Çalışan', 'Toplam Kayıt', 'Devam Eden', 'Tamamlanan', 'Ort. İlerleme %', 'Sertifika'];
                $this->writeHeaders($sheet, $headers, $headerStyle);
                foreach ($data as $i => $row) {
                    $r = $i + 2;
                    $sheet->fromArray([
                        $row['firma'], $row['tax_number'] ?? '',
                        $row['calisan_sayisi'], $row['toplam_kayit'],
                        $row['devam_eden'], $row['tamamlanan'],
                        $row['ort_ilerleme'], $row['sertifika_sayisi'],
                    ], null, "A$r");
                }
                $this->autoSize($sheet, range('A', 'H'));
                break;

            case 'katilimci':
                $data = $this->getKatilimciData($filters);
                $sheet->setTitle('Katılımcı Raporu');
                $headers = ['Ad', 'Soyad', 'E-posta', 'Telefon', 'Firma', 'Tür', 'Paket', 'Kurs / Ders', 'Durum', 'Başlama', 'Bitirme', 'İlerleme %', 'Sınav Puanı', 'Sertifika No'];
                $this->writeHeaders($sheet, $headers, $headerStyle);
                foreach ($data as $i => $row) {
                    $r = $i + 2;
                    $sheet->fromArray([
                        $row['first_name'], $row['last_name'], $row['email'],
                        $row['phone'] ?? '-', $row['firma'] ?? '-',
                        $row['tur'], $row['parent_kurs'] ?? '-', $row['kurs'],
                        $this->statusLabel($row['durum']),
                        $row['baslama'] ? date('d.m.Y H:i', strtotime($row['baslama'])) : '-',
                        $row['bitirme'] ? date('d.m.Y H:i', strtotime($row['bitirme'])) : '-',
                        $row['ilerleme'], $row['sinav_puani'] ?? '-', $row['sertifika_no'] ?? '-',
                    ], null, "A$r");
                }
                $this->autoSize($sheet, range('A', 'N'));
                break;

            case 'sistem':
                $data = $this->getSistemData();
                $sheet->setTitle('Sistem Raporu');
                $sheet->fromArray(['Metrik', 'Değer'], null, 'A1');
                $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);
                $rows = [
                    ['Toplam Kullanıcı', $data['toplam_kullanici']],
                    ['Toplam Öğrenci', $data['toplam_ogrenci']],
                    ['Toplam Kurs', $data['toplam_kurs']],
                    ['Toplam Kayıt', $data['toplam_kayit']],
                    ['Tamamlanan Eğitim', $data['tamamlanan']],
                    ['Verilen Sertifika', $data['toplam_sertifika']],
                    ['Toplam Firma', $data['toplam_firma']],
                    ['Şu An Aktif (15 dk)', $data['aktif_kullanici']],
                ];
                foreach ($rows as $i => $row) {
                    $sheet->fromArray($row, null, 'A' . ($i + 2));
                }
                $this->autoSize($sheet, ['A', 'B']);
                break;

            case 'yenileme':
                $data = $this->getYenilemeData($filters);
                $sheet->setTitle('Sertifika Yenileme');
                $headers = ['Ad', 'Soyad', 'E-posta', 'Firma', 'Kurs', 'Kategori', 'Sertifika No', 'Veriliş', 'Son Geçerlilik', 'Kalan Gün'];
                $this->writeHeaders($sheet, $headers, $headerStyle);
                foreach ($data as $i => $row) {
                    $r = $i + 2;
                    $sheet->fromArray([
                        $row['first_name'], $row['last_name'], $row['email'],
                        $row['firma'] ?? '-', $row['kurs'], $row['kategori'] ?? '-',
                        $row['sertifika_no'],
                        $row['verilis_tarihi'] ? date('d.m.Y', strtotime($row['verilis_tarihi'])) : '-',
                        $row['son_gecerlilik_tarihi'] ? date('d.m.Y', strtotime($row['son_gecerlilik_tarihi'])) : '-',
                        $row['kalan_gun'] ?? '—',
                    ], null, "A$r");
                }
                $this->autoSize($sheet, range('A', 'J'));
                break;
        }

        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $lastCol = $sheet->getHighestColumn();
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray($dataStyle);
        }

        $filename = 'rapor_' . $rapor . '_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ─── PDF export ──────────────────────────────────────────────────────────

    public function exportPdf(string $rapor, array $filters = []): void
    {
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator(APP_NAME);
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Rapor - ' . strtoupper($rapor));
        $pdf->SetHeaderData('', 0, APP_NAME . ' — ' . $this->rapLabel($rapor) . ' Raporu', date('d.m.Y H:i'));
        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(10, 25, 10);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 8);

        switch ($rapor) {
            case 'kurs':
                $data = $this->getKursData($filters);
                $html = $this->buildTable(
                    ['Tür', 'Kurs / Ders', 'Paket', 'Kategori', 'Kayıt', 'Devam', 'Bitiren', 'İlerleme%', 'Puan', 'Öğrenci'],
                    array_map(fn($r) => [
                        $r['tur'], $r['title'], $r['parent_kurs'] ?? '-', $r['kategori'],
                        $r['toplam_kayit'], $r['devam_eden'], $r['tamamlayan'],
                        $r['ort_ilerleme'], $r['ort_puan'] ?? '-', $r['benzersiz_ogrenci'],
                    ], $data)
                );
                break;
            case 'kullanici':
                $data = $this->getKullaniciData($filters);
                $html = $this->buildTable(
                    ['Ad Soyad', 'E-posta', 'Firma', 'Kurs', 'Durum', 'İlerleme%', 'Kayıt', 'Sertifika'],
                    array_map(fn($r) => [
                        $r['first_name'] . ' ' . $r['last_name'], $r['email'],
                        $r['firma'] ?? '-', $r['kurs'],
                        $this->statusLabel($r['durum']), $r['ilerleme'],
                        $r['kayit_tarihi'] ? date('d.m.Y', strtotime($r['kayit_tarihi'])) : '-',
                        $r['sertifika_no'] ?? '-',
                    ], $data)
                );
                break;
            case 'firma':
                $data = $this->getFirmaData($filters);
                $html = $this->buildTable(
                    ['Firma', 'Vergi No', 'Çalışan', 'Kayıt', 'Devam', 'Tamamlanan', 'İlerleme%', 'Sertifika'],
                    array_map(fn($r) => [
                        $r['firma'], $r['tax_number'] ?? '-',
                        $r['calisan_sayisi'], $r['toplam_kayit'],
                        $r['devam_eden'], $r['tamamlanan'],
                        $r['ort_ilerleme'], $r['sertifika_sayisi'],
                    ], $data)
                );
                break;
            case 'katilimci':
                $data = $this->getKatilimciData($filters);
                $html = $this->buildTable(
                    ['Ad Soyad', 'E-posta', 'Firma', 'Tür', 'Kurs / Ders', 'Durum', 'Başlama', 'Bitirme', 'İlerleme%', 'Puan', 'Sertifika'],
                    array_map(fn($r) => [
                        $r['first_name'] . ' ' . $r['last_name'], $r['email'],
                        $r['firma'] ?? '-', $r['tur'], $r['kurs'],
                        $this->statusLabel($r['durum']),
                        $r['baslama'] ? date('d.m.Y H:i', strtotime($r['baslama'])) : '-',
                        $r['bitirme'] ? date('d.m.Y H:i', strtotime($r['bitirme'])) : '-',
                        $r['ilerleme'] . '%', $r['sinav_puani'] ?? '-', $r['sertifika_no'] ?? '-',
                    ], $data)
                );
                break;
            case 'sistem':
                $data = $this->getSistemData();
                $html = $this->buildTable(
                    ['Metrik', 'Değer'],
                    [
                        ['Toplam Kullanıcı', $data['toplam_kullanici']],
                        ['Toplam Öğrenci', $data['toplam_ogrenci']],
                        ['Toplam Kurs', $data['toplam_kurs']],
                        ['Toplam Kayıt', $data['toplam_kayit']],
                        ['Tamamlanan Eğitim', $data['tamamlanan']],
                        ['Verilen Sertifika', $data['toplam_sertifika']],
                        ['Toplam Firma', $data['toplam_firma']],
                        ['Şu An Aktif (15 dk)', $data['aktif_kullanici']],
                    ]
                );
                break;
            case 'yenileme':
                $data = $this->getYenilemeData($filters);
                $html = $this->buildTable(
                    ['Ad Soyad', 'E-posta', 'Firma', 'Kurs', 'Sertifika No', 'Son Geçerlilik', 'Kalan Gün'],
                    array_map(fn($r) => [
                        $r['first_name'] . ' ' . $r['last_name'], $r['email'],
                        $r['firma'] ?? '-', $r['kurs'],
                        $r['sertifika_no'],
                        $r['son_gecerlilik_tarihi'] ? date('d.m.Y', strtotime($r['son_gecerlilik_tarihi'])) : '-',
                        $r['kalan_gun'] !== null ? (int)$r['kalan_gun'] . ' gün' : '—',
                    ], $data)
                );
                break;
            default:
                $html = '<p>Geçersiz rapor tipi.</p>';
        }

        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'rapor_' . $rapor . '_' . date('Ymd_His') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function writeHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $headers, array $style): void
    {
        $sheet->fromArray($headers, null, 'A1');
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($style);
    }

    private function autoSize(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $cols): void
    {
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildTable(array $headers, array $rows): string
    {
        $th = implode('', array_map(fn($h) => "<th style='background:#0d6efd;color:#fff;padding:4px 6px;border:1px solid #ccc;font-weight:bold;'>$h</th>", $headers));
        $html = "<table border='0' cellpadding='3' cellspacing='0' style='width:100%;border-collapse:collapse;font-size:8px;'>";
        $html .= "<thead><tr>$th</tr></thead><tbody>";
        foreach ($rows as $i => $row) {
            $bg = $i % 2 === 0 ? '#ffffff' : '#f8f9fa';
            $td = implode('', array_map(fn($v) => "<td style='border:1px solid #ddd;padding:3px 5px;background:{$bg};'>" . htmlspecialchars((string)($v ?? '-')) . '</td>', $row));
            $html .= "<tr>$td</tr>";
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function statusLabel(string $status): string
    {
        return match($status) {
            'completed'   => 'Tamamlandı',
            'in_progress' => 'Devam Ediyor',
            'enrolled'    => 'Kayıtlı',
            'failed'      => 'Başarısız',
            default       => $status,
        };
    }

    private function rapLabel(string $rapor): string
    {
        return match($rapor) {
            'kurs'       => 'Kurs',
            'kullanici'  => 'Kullanıcı',
            'firma'      => 'Firma',
            'katilimci'  => 'Katılımcı',
            'sistem'     => 'Sistem',
            'yenileme'   => 'Sertifika Yenileme',
            default      => ucfirst($rapor),
        };
    }
}
