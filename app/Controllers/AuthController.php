<?php
class AuthController {
    public function login() {
        session_start();
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            $user = UserModel::authenticate($email, $pass);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: /");
                exit;
            } else {
                $errorMsg = "Неверный логин или пароль";
                include PROJECT_ROOT . '/app/Views/login.php';
                exit;
            }
        }
        include PROJECT_ROOT . '/app/Views/login.php';
    }

    public function logout() {
        session_start();
        $_SESSION = [];
        session_destroy();
        header('Location: /login');
        exit;
    }
    public function register() {
        session_start();
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                $errorMsg = "Ошибка безопасности: Неверный CSRF-токен!";
            } else {
                $email = trim($_POST['email'] ?? '');
                $pass = $_POST['password'] ?? '';
                $passConfirm = $_POST['password_confirm'] ?? '';

                if (empty($email) || empty($pass)) $errorMsg = "Заполните все поля!";
                elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errorMsg = "Некорректный формат Email!";
                elseif ($pass !== $passConfirm) $errorMsg = "Пароли не совпадают!";
                elseif (strlen($pass) < 8) $errorMsg = "Пароль должен содержать минимум 8 символов!";
                else {
                    $result = UserModel::register($email, $pass);
                    if ($result === 'success') {
                        header("Location: /login"); exit;
                    } else {
                        $errorMsg = $result;
                    }
                }
            }
            include PROJECT_ROOT . '/app/Views/register.php'; exit;
        }
        include PROJECT_ROOT . '/app/Views/register.php';
    }

    // Логика из update_profile.php (смена пароля)
    public function changePassword() {
        session_start();
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                $errorMsg = "Ошибка безопасности: Неверный CSRF-токен!";
            } else {
                $oldPass = $_POST['old_password'] ?? '';
                $newPass = $_POST['password'] ?? '';
                $confirmPass = $_POST['password_confirm'] ?? '';

                if (empty($newPass) || empty($confirmPass)) $errorMsg = "Заполните все поля!";
                elseif ($newPass !== $confirmPass) $errorMsg = "Пароли не совпадают!";
                else {
                    $result = UserModel::updatePassword($_SESSION['user_id'], $oldPass, $newPass);
                    if ($result === 'success') {
                        $successMsg = "Пароль успешно изменен!";
                    } else {
                        $errorMsg = $result;
                    }
                }
            }
            include PROJECT_ROOT . '/app/Views/change_pass.php'; exit;
        }
        include PROJECT_ROOT . '/app/Views/change_pass.php';
    }
}
?>