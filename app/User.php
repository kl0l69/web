<?php

/**
 * User.php - كلاس إدارة المستخدمين والتحقق من الهوية
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * تسجيل دخول المستخدم
     * @param string $username اسم المستخدم أو البريد الإلكتروني
     * @param string $password كلمة المرور
     * @return string|false دور المستخدم عند النجاح، أو false عند الفشل
     */
    public function login(string $username, string $password)
    {
        $sql = "SELECT id, fullname, username, password, role FROM users WHERE username = :uname OR email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uname' => $username, 'email' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['last_act'] = time();
            return $user['role'];
        }
        return false;
    }

    /**
     * تسجيل مستخدم جديد (مريض) في النظام
     */
    public function register(string $fullname, string $username, string $email, string $password, string $role = 'patient', ?string $phone = null): bool
    {
        // التحقق من تكرار اسم المستخدم أو البريد الإلكتروني
        $dup = $this->isDuplicate($username, $email);
        if ($dup['username'] || $dup['email']) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (fullname, username, email, password, role, phone) VALUES (:fullname, :username, :email, :password, :role, :phone)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'fullname' => $fullname,
            'username' => $username,
            'email'    => $email,
            'password' => $hash,
            'role'     => $role,
            'phone'    => $phone
        ]);
    }

    /**
     * التحقق من تكرار اسم المستخدم أو البريد الإلكتروني
     */
    public function isDuplicate(string $username, string $email, ?int $excludeUserId = null): array
    {
        $result = ['username' => false, 'email' => false];

        // تحقق من اسم المستخدم
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username" . ($excludeUserId ? " AND id != :id" : "");
        $stmt = $this->pdo->prepare($sql);
        $params = ['username' => $username];
        if ($excludeUserId) $params['id'] = $excludeUserId;
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $result['username'] = true;
        }

        // تحقق من البريد الإلكتروني
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email" . ($excludeUserId ? " AND id != :id" : "");
        $stmt = $this->pdo->prepare($sql);
        $params = ['email' => $email];
        if ($excludeUserId) $params['id'] = $excludeUserId;
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $result['email'] = true;
        }

        return $result;
    }

    /**
     * تسجيل الخروج وتدمير الجلسة
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * التحقق من تسجيل دخول المستخدم حالياً
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * الحصول على دور المستخدم الحالي
     */
    public static function getRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * الحصول على اسم المستخدم الحالي
     */
    public static function getUsername(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    /**
     * الحصول على الاسم الكامل للمستخدم الحالي
     */
    public static function getFullName(): ?string
    {
        return $_SESSION['fullname'] ?? null;
    }

    /**
     * الحصول على معرّف المستخدم الحالي
     */
    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * التحقق من دور المستخدم الحالي
     */
    public static function hasRole(string $role): bool
    {
        return self::getRole() === $role;
    }

    /**
     * تغيير كلمة مرور المستخدم
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        $sql = "SELECT password FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($oldPassword, $row['password'])) {
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $upd = $this->pdo->prepare("UPDATE users SET password = :hash WHERE id = :id");
        return $upd->execute(['hash' => $hash, 'id' => $userId]);
    }

    /**
     * الحصول على بيانات المستخدم الحالي الكاملة
     */
    public function getCurrentUser(): ?array
    {
        if (!self::isLoggedIn()) return null;
        $sql = "SELECT id, fullname, username, email, role, phone FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => self::getUserId()]);
        return $stmt->fetch();
    }
}
