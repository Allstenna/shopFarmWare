<?php
class OrderController {
    
    public function buyNow() { // вместо make_order.php
        session_start();
        if (!isset($_SESSION['user_id'])) die("Сначала войдите в систему! <a href='/login'>Вход</a>");

        $productId = (int) ($_GET['id'] ?? 0);
        if ($productId <= 0) die("Неверный товар.");

        $pdo = Database::getInstance();
        if (!$pdo->query("SELECT 1 FROM products WHERE id = $productId")->fetch()) {
            die("Ошибка: Попытка заказать несуществующий товар!");
        }

        $userId = $_SESSION['user_id'];
        $lastOrderTime = OrderModel::getLastOrderTime($userId, $productId);
        if ($lastOrderTime) {
            $diff = time() - strtotime($lastOrderTime);
            if ($diff < 300) {
                die("Вы не можете заказывать этот товар так часто. Попробуйте через " . (300 - $diff) . " сек. <a href='/'>Назад</a>");
            }
        }

        try {
            if (OrderModel::createDirectOrder($userId, $productId)) {
                echo "Заказ успешно оформлен! Менеджер свяжется с вами. <a href='/'>Вернуться</a>";
            } else echo "Ошибка при создании заказа.";
        } catch (Exception $e) { echo "Ошибка: " . $e->getMessage(); }
    }

    public function details() { // вместо order_details.php
        session_start();
        $orderId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user_id'] ?? 0;
        
        if ($orderId <= 0 || $userId <= 0) die("Заказ не найден или у вас нет прав на его просмотр.");
        
        $order = OrderModel::getByIdAndUser($orderId, $userId);
        if (!$order) die("Заказ не найден или у вас нет прав на его просмотр.");

        include PROJECT_ROOT . '/app/Views/order_details.php';
    }

    public function updateStatus() { // AJAX из profile.php
        session_start();
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Доступ запрещен']); exit;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        if ($orderId <= 0 || empty($newStatus)) { echo json_encode(['success' => false, 'message' => 'Неверные данные']); exit; }

        try {
            echo json_encode(OrderModel::updateStatus($orderId, $newStatus) 
                ? ['success' => true, 'message' => 'Статус обновлен'] 
                : ['success' => false, 'message' => 'Неверный статус']);
        } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
        exit;
    }
    
    public static function getOrdersWithItems(?int $userId = null, bool $isAdmin = false): array {
        $pdo = Database::getInstance();
        
        $sql = "
            SELECT o.id, o.status, o.total_sum, o.delivery_cost, o.delivery_address, o.created_at,
                   COALESCE(u.username, u.email, 'Клиент') as customer_name,
                   oi.quantity, oi.price_snapshot,
                   p.name as product_name, p.description as product_desc
            FROM Orders o
            LEFT JOIN Order_Items oi ON o.id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.id
            LEFT JOIN users u ON o.user_id = u.id
        ";
        $params = [];
        
        // Фильтр по пользователю (если не админ)
        if (!$isAdmin && $userId !== null && $userId > 0) {
            $sql .= " WHERE o.user_id = :uid";
            $params[':uid'] = $userId;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 🔹 Группировка: один заказ → массив товаров
        $orders = [];
        foreach ($rows as $row) {
            $oid = $row['id'];
            
            // Создаём "скелет" заказа, если видим его впервые
            if (!isset($orders[$oid])) {
                $orders[$oid] = [
                    'id' => $oid,
                    'status' => $row['status'],
                    'total_sum' => $row['total_sum'],
                    'delivery_cost' => $row['delivery_cost'],
                    'delivery_address' => $row['delivery_address'],
                    'created_at' => $row['created_at'],
                    'customer_name' => $row['customer_name'],
                    'items' => [] // ← сюда будем складывать товары
                ];
            }
            
            // Добавляем товар, если он есть (защита от заказов без позиций)
            if ($row['product_name']) {
                $orders[$oid]['items'][] = [
                    'name' => $row['product_name'],
                    'desc' => $row['product_desc'],
                    'qty' => (int) $row['quantity'],
                    'price' => (float) $row['price_snapshot'],
                    'subtotal' => (float) ($row['quantity'] * $row['price_snapshot'])
                ];
            }
        }
        
        // Возвращаем нумерованный массив (удобно для foreach в шаблоне)
        return array_values($orders);
    }
}
?>