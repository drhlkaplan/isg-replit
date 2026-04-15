<?php
namespace ISG;

class Auth {
    private DB $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function login(string $email, string $password): bool {
        $user = $this->db->fetch(
            'SELECT u.*, r.name AS role_name FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.email = ? AND u.status = "active"',
            [$email]
        );
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        $this->setSession($user);
        $this->db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        (new Audit())->log($user['id'], 'login', 'user', $user['id'], ['email' => $user['email']]);
        return true;
    }

    public function loginWithTc(string $tcNo, string $password): bool {
        $tcNo = preg_replace('/\D/', '', $tcNo);
        if (strlen($tcNo) !== 11) return false;
        $user = $this->db->fetch(
            'SELECT u.*, r.name AS role_name FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.tc_identity_no = ? AND u.status = "active"',
            [$tcNo]
        );
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        $this->setSession($user);
        $this->db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        (new Audit())->log($user['id'], 'login', 'user', $user['id'], ['tc_identity_no' => $tcNo]);
        return true;
    }

    public function logout(): void {
        if (isset($_SESSION['user_id'])) {
            (new Audit())->log($_SESSION['user_id'], 'logout', 'user', $_SESSION['user_id']);
        }
        session_destroy();
    }

    public function register(array $data): int {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        unset($data['password']);
        $data['role_id'] = $data['role_id'] ?? 4;
        $data['status'] = 'active';
        return $this->db->insert('users', $data);
    }

    public function check(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function user(): ?array {
        if (!$this->check()) return null;
        return $_SESSION['user'] ?? null;
    }

    public function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public function role(): ?string {
        return $_SESSION['role'] ?? null;
    }

    public function isAdmin(): bool {
        return in_array($this->role(), ['superadmin', 'admin']);
    }

    public function isSuperAdmin(): bool {
        return $this->role() === 'superadmin';
    }

    public function isFirm(): bool {
        return $this->role() === 'firm';
    }

    public function isStudent(): bool {
        return $this->role() === 'student';
    }

    public function requireLogin(): void {
        if (!$this->check()) {
            header('Location: /giris');
            exit;
        }
    }

    public function requireRole(string ...$roles): void {
        $this->requireLogin();
        if (!in_array($this->role(), $roles)) {
            http_response_code(403);
            include __DIR__ . '/../views/403.php';
            exit;
        }
    }

    private function setSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role_name'];
        $_SESSION['user'] = [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'firm_id'    => $user['firm_id'],
            'role'       => $user['role_name'],
        ];
    }

    public function refreshSession(): void {
        if (!$this->check()) return;
        $user = $this->db->fetch(
            'SELECT u.*, r.name AS role_name FROM users u
             JOIN roles r ON u.role_id = r.id WHERE u.id = ?',
            [$this->id()]
        );
        if ($user) $this->setSession($user);
    }
}
