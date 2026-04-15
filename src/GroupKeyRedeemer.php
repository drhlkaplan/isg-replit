<?php
namespace ISG;

class GroupKeyRedeemer
{
    public function __construct(private DB $db) {}

    /**
     * Validate and redeem a group key for a user.
     * Returns an array with 'success', 'enrolled' count, and 'message'.
     */
    public function redeem(string $rawCode, int $userId): array
    {
        $code = strtoupper(trim($rawCode));
        if (!$code) {
            return ['success' => false, 'enrolled' => 0, 'message' => 'Geçersiz anahtar kodu.'];
        }

        $groupKey = $this->db->fetch(
            'SELECT * FROM group_keys WHERE key_code = ? AND status = "active"',
            [$code]
        );

        if (!$groupKey) {
            return ['success' => false, 'enrolled' => 0, 'message' => 'Geçersiz veya pasif grup anahtarı.'];
        }

        // Log usage (idempotent)
        if (!$this->db->exists('group_key_usage', 'group_key_id = ? AND user_id = ?', [$groupKey['id'], $userId])) {
            $this->db->insert('group_key_usage', [
                'group_key_id' => $groupKey['id'],
                'user_id'      => $userId,
            ]);
        }

        // Enroll in all active courses attached to this key
        $keyCourses = $this->db->fetchAll(
            'SELECT gkc.course_id, c.topic_type FROM group_key_courses gkc
             JOIN courses c ON c.id = gkc.course_id
             WHERE gkc.group_key_id = ? AND c.status = "active"',
            [$groupKey['id']]
        );

        $enrolled  = 0;
        $enroller  = new PackageEnroller($this->db);
        foreach ($keyCourses as $kc) {
            if ($this->db->exists('enrollments', 'user_id = ? AND course_id = ?', [$userId, $kc['course_id']])) {
                continue;
            }
            if ($kc['topic_type'] === 'paket') {
                // Auto-enroll package + all child modules with sequential locking
                $enrolled += $enroller->enrollPackage($userId, $kc['course_id']);
            } else {
                $this->db->insert('enrollments', [
                    'user_id'   => $userId,
                    'course_id' => $kc['course_id'],
                    'status'    => 'enrolled',
                    'is_locked' => 0,
                ]);
                $enrolled++;
            }
        }

        $message = $enrolled > 0
            ? "Grup anahtarı kullanıldı! $enrolled kursa/modüle kaydedildiniz."
            : 'Grup anahtarı geçerli — zaten tüm kurslara kayıtlısınız.';

        return ['success' => true, 'enrolled' => $enrolled, 'message' => $message];
    }
}
