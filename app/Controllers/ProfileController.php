<?php
class ProfileController {
    
    
    // Логика из profile.php
    public function index() {
        session_start();
        
        // 🔹 Проверка авторизации
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        // 🔹 Получаем данные пользователя
        $user = UserModel::getById($userId);
        $userAvatar = $user['avatar_url'] ?? null;

        // 🔹 Получаем заказы через модель (вся логика внутри OrderModel)
        $orders = OrderModel::getOrdersWithItems($userId, $isAdmin);

        // 🔹 Хелпер для классов статусов (можно вынести в шаблон или отдельный сервис)
        $getStatusClass = function($status) {
    // Если в БД статус хранится как 'pending', приводим к русскому названию
        $s = ($status === 'pending') ? 'Новый' : $status;
        $map = [
            'Новый' => 'status-new',
            'В обработке' => 'status-process',
            'Отправлен' => 'status-process',
            'Отменен' => 'status-cancelled',
            'Выполнен' => 'status-completed'
        ];
        return $map[$s] ?? '';
};

        // 🔹 AJAX: Обновление статуса заказа (админ)
        // Должен быть ДО include, чтобы не смешивать JSON с HTML
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_order_status') {
            @ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
                exit;
            }
            
            try {
                $orderId = (int) ($_POST['order_id'] ?? 0);
                $newStatus = trim($_POST['status'] ?? '');
                $allowed = ['Новый', 'В обработке', 'Отправлен', 'Выполнен', 'Отменен'];
                
                if ($orderId <= 0 || !in_array($newStatus, $allowed)) {
                    throw new Exception('Неверные данные');
                }
                
                // Вызываем метод обновления статуса из модели
                $success = OrderModel::updateStatus($orderId, $newStatus);
                echo json_encode($success 
                    ? ['success' => true, 'message' => 'Статус обновлен'] 
                    : ['success' => false, 'message' => 'Не удалось обновить статус']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }

        // 🔹 Передаём все данные в шаблон
        include PROJECT_ROOT . '/app/Views/profile.php';
    }

    // Логика из upload.php
    public function uploadAvatar() {
        session_start();
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }

        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar'])) {
            echo json_encode(['success' => false, 'message' => 'Неверный запрос']); exit;
        }

        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];

        if ($file['error'] !== UPLOAD_ERR_OK) echo json_encode(['success' => false, 'message' => 'Ошибка загрузки']); exit;
        if (!in_array($file['type'], $allowed)) echo json_encode(['success' => false, 'message' => 'Разрешены только JPG, PNG, GIF']); exit;

        // Абсолютный путь для сохранения, относительный для БД
        $uploadDir = PROJECT_ROOT . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = 'img_' . uniqid() . '.' . $ext;
        $destination = $uploadDir . $newName;
        $dbPath = 'uploads/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            UserModel::updateAvatar($_SESSION['user_id'], $dbPath);
            echo json_encode(['success' => true, 'avatar_url' => $dbPath, 'message' => 'Аватар обновлен']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл']);
        }
        exit;
    }
    
}
?>