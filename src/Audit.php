<?php
namespace ISG;

class Audit {
    private DB $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        mixed $details = null
    ): void {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);
        $this->db->insert('audit_log', [
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => substr($ip, 0, 45),
            'user_agent'  => $ua,
        ]);
    }
}
