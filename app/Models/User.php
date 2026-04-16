<?php
class UserModel {
    public static function getDeliveryAddress(int $userId): string {
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->prepare("SELECT delivery_address FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return trim($stmt->fetchColumn() ?: '');
    }
    public static function authenticate(string $email, string $password): ?array {
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->prepare("SELECT id, email, password_hash, role, avatar_url FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return ($user && password_verify($password, $user['password_hash'])) ? $user : null;
    }

    public static function getProfileData(int $userId): ?array {
        $stmt = Database::getInstance()->prepare("SELECT avatar_url, delivery_address, username, email FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch() ?: null;
    }
    public static function getById(int $id): ?array {
        $stmt = Database::getInstance()->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByEmail(string $email): ?array {
        $stmt = Database::getInstance()->prepare("SELECT id, email, password_hash, role FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public static function register(string $email, string $password): string {
        if (self::getByEmail($email)) {
            return 'Такой email уже зарегистрирован.';
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->prepare("INSERT INTO users (email, password_hash, role) VALUES (:email, :hash, 'client')");
        try {
            $stmt->execute(['email' => $email, 'hash' => $hash]);
            return 'success';
        } catch (PDOException $e) {
            return 'Ошибка БД при регистрации.';
        }
    }
    public static function updatePassword(int $userId, string $oldPass, string $newPass): string {
        $user = self::getById($userId);
        if (!$user || !password_verify($oldPass, $user['password_hash'])) {
            return 'Старый пароль указан неверно.';
        }
        if (strlen($newPass) < 8) {
            return 'Пароль должен содержать минимум 8 символов.';
        }
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        Database::getInstance()->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")
            ->execute(['hash' => $hash, 'id' => $userId]);
        return 'success';
    }

    public static function updateAvatar(int $userId, string $path): bool {
        Database::getInstance()->prepare("UPDATE users SET avatar_url = :path WHERE id = :id")
            ->execute(['path' => $path, 'id' => $userId]);
        return true;
    }
}
?>