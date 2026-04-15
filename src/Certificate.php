<?php
namespace ISG;

class Certificate {
    private DB $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function issue(int $userId, int $courseId): array {
        $existing = $this->db->fetch(
            'SELECT * FROM certificates WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );
        if ($existing) return $existing;

        $certNumber = $this->generateCertNumber();
        $pdfPath = $this->generatePDF($userId, $courseId, $certNumber);

        // Determine expiry based on category's refresh_period_years (AZ=3yr, TE=2yr, CT=1yr)
        $refreshYears = 2; // Default fallback
        $catRefresh = $this->db->fetch(
            'SELECT cc.refresh_period_years FROM courses c
             JOIN course_categories cc ON c.category_id = cc.id
             WHERE c.id = ?',
            [$courseId]
        );
        if ($catRefresh && isset($catRefresh['refresh_period_years'])) {
            $refreshYears = (int)$catRefresh['refresh_period_years'];
        }

        $id = $this->db->insert('certificates', [
            'user_id'    => $userId,
            'course_id'  => $courseId,
            'cert_number' => $certNumber,
            'issued_at'  => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d', strtotime('+' . $refreshYears . ' years')),
            'pdf_path'   => $pdfPath,
            'is_valid'   => 1,
        ]);

        return $this->db->fetch('SELECT * FROM certificates WHERE id = ?', [$id]);
    }

    private function generateCertNumber(): string {
        do {
            $num = 'ISG-' . date('Y') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        } while ($this->db->exists('certificates', 'cert_number = ?', [$num]));
        return $num;
    }

    private function generatePDF(int $userId, int $courseId, string $certNumber): string {
        $user = $this->db->fetch(
            'SELECT u.first_name, u.last_name, u.tc_identity_no FROM users u WHERE u.id = ?',
            [$userId]
        );
        $course = $this->db->fetch(
            'SELECT c.title, c.duration_minutes, cc.name AS category_name
             FROM courses c JOIN course_categories cc ON c.category_id = cc.id
             WHERE c.id = ?',
            [$courseId]
        );

        if (!$user || !$course) throw new \RuntimeException('Kullanıcı veya kurs bulunamadı.');

        $filename = $certNumber . '.pdf';
        $pdfPath = CERT_DIR . $filename;

        if (!is_dir(CERT_DIR)) mkdir(CERT_DIR, 0755, true);

        require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';

        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $pdf->SetFillColor(0, 86, 149);
        $pdf->Rect(0, 0, 297, 210, 'F');
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(10, 10, 277, 190, 'F');
        $pdf->SetFillColor(0, 86, 149);
        $pdf->Rect(10, 10, 277, 4, 'F');
        $pdf->Rect(10, 196, 277, 4, 'F');

        $pdf->SetTextColor(0, 86, 149);
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->SetXY(0, 20);
        $pdf->Cell(297, 12, 'T.C. IS SAGLIGI VE GUVENLIGI', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Cell(297, 12, 'EGITIM SERTIFIKASI', 0, 1, 'C');

        $pdf->SetDrawColor(0, 86, 149);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(50, 50, 247, 50);

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(80, 80, 80);

        $fields = [
            ['Ad Soyad', $user['first_name'] . ' ' . $user['last_name']],
            ['TC Kimlik No', $user['tc_identity_no'] ?: 'Belirtilmemis'],
            ['Egitim Adi', $course['title']],
            ['Tehlike Sinifi', $course['category_name']],
            ['Egitim Suresi', ceil($course['duration_minutes'] / 60) . ' Saat'],
            ['Sertifika No', $certNumber],
            ['Duzenleme Tarihi', date('d.m.Y')],
        ];

        $y = 58;
        foreach ($fields as [$label, $value]) {
            $pdf->SetXY(40, $y);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor(0, 86, 149);
            $pdf->Cell(70, 8, $label . ':', 0, 0, 'R');
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetTextColor(40, 40, 40);
            $pdf->Cell(150, 8, $value, 0, 1, 'L');
            $y += 9;
        }

        $verifyUrl = APP_URL . '/dogrula/' . $certNumber;
        $pdf->SetXY(0, 168);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(297, 6, 'QR Kod ile dogrulama: ' . $verifyUrl, 0, 1, 'C');

        $qrX = 230;
        $qrY = 100;
        $pdf->SetDrawColor(0, 86, 149);
        $pdf->Rect($qrX, $qrY, 40, 40);
        $pdf->SetXY($qrX, $qrY + 42);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(40, 4, 'QR KOD', 0, 0, 'C');

        $pdf->Output('F', $pdfPath);
        return $filename;
    }

    public function regeneratePdf(array $cert): string {
        $pdfFilename = $this->generatePDF((int)$cert['user_id'], (int)$cert['course_id'], $cert['cert_number']);
        $this->db->update('certificates', ['pdf_path' => $pdfFilename], 'id = ?', [$cert['id']]);
        return $pdfFilename;
    }

    public function verify(string $certNumber): ?array {
        $row = $this->db->fetch(
            'SELECT c.*, u.first_name, u.last_name, u.tc_identity_no,
                    CONCAT(u.first_name, " ", u.last_name) AS full_name,
                    co.title AS course_title, cc.name AS category_name, co.duration_minutes
             FROM certificates c
             JOIN users u ON c.user_id = u.id
             JOIN courses co ON c.course_id = co.id
             JOIN course_categories cc ON co.category_id = cc.id
             WHERE c.cert_number = ? AND c.is_valid = 1',
            [$certNumber]
        );
        return $row;
    }
}
