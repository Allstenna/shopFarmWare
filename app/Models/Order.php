<?php
class OrderModel {
    public static function create(int $userId, string $address, float $subtotal, float $deliveryCost): int {
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->prepare("INSERT INTO Orders (user_id, delivery_address, status, total_sum, delivery_cost, created_at) VALUES (?, ?, 'pending', ?, ?, NOW())");
        $stmt->execute([$userId, $address, $subtotal + $deliveryCost, $deliveryCost]);
        return (int) Database::getInstance()->lastInsertId();
    }
    public static function attachItems(int $orderId): void {
        $pdo = Database::getInstance();
        Database::getInstance()->prepare("UPDATE Order_Items SET order_id = :oid WHERE order_id IS NULL OR order_id = 0")
            ->execute(['oid' => $orderId]);
    }
    public static function getByIdAndUser(int $orderId, int $userId): ?array {
        $stmt = Database::getInstance()->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $orderId, 'uid' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public static function getLastOrderTime(int $userId, int $productId): ?string {
        $stmt = Database::getInstance()->prepare("
            SELECT created_at FROM orders 
            WHERE user_id = :uid AND product_id = :pid 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute(['uid' => $userId, 'pid' => $productId]);
        return $stmt->fetchColumn();
    }

    public static function createDirectOrder(int $userId, int $productId): bool {
        $stmt = Database::getInstance()->prepare("
            INSERT INTO orders (user_id, product_id, status, created_at) 
            VALUES (:uid, :pid, 'pending', NOW())
        ");
        return $stmt->execute(['uid' => $userId, 'pid' => $productId]);
    }

    public static function getOrdersWithItems(int $userId, bool $isAdmin = false): array {
        $pdo = Database::getInstance();
        
        $sql = "
            SELECT o.id, o.status, o.total_sum, o.delivery_cost, o.delivery_address, o.created_at,
                   COALESCE (u.email) as customer_name,
                   oi.quantity, oi.price_snapshot,
                   p.name as product_name, p.description as product_desc
            FROM Orders o
            LEFT JOIN Order_Items oi ON o.id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.id
            LEFT JOIN users u ON o.user_id = u.id
        ";
        $params = [];
        
        if (!$isAdmin) {
            $sql .= " WHERE o.user_id = :uid";
            $params[':uid'] = $userId;
        }
        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Группируем товары по order_id
        $orders = [];
        foreach ($rows as $row) {
            $oid = $row['id'];
            if (!isset($orders[$oid])) {
                $orders[$oid] = [
                    'id' => $oid,
                    'status' => $row['status'],
                    'total_sum' => $row['total_sum'],
                    'delivery_cost' => $row['delivery_cost'],
                    'delivery_address' => $row['delivery_address'],
                    'created_at' => $row['created_at'],
                    'customer_name' => $row['customer_name'],
                    'items' => []
                ];
            }
            if ($row['product_name']) {
                $orders[$oid]['items'][] = [
                    'name' => $row['product_name'],
                    'desc' => $row['product_desc'],
                    'qty' => $row['quantity'],
                    'price' => $row['price_snapshot'],
                    'subtotal' => $row['quantity'] * $row['price_snapshot']
                ];
            }
        }
        return $orders;
    }

    public static function updateStatus(int $orderId, string $newStatus): bool {
        $allowed = ['Новый', 'В обработке', 'Отправлен', 'Выполнен', 'Отменен'];
        if (!in_array($newStatus, $allowed)) return false;
        $stmt = Database::getInstance()->prepare("UPDATE Orders SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $newStatus, 'id' => $orderId]);
    }
    public static function getAllAdmin(?string $status = null, ?int $userId = null, int $limit = 20, int $offset = 0): array {
        $pdo = Database::getInstance();
        
        // 1. Получаем заказы с товарами одним запросом
        $sql = "SELECT o.id, o.status, o.total_sum, o.delivery_cost, o.delivery_address, o.created_at,
                       COALESCE(u.email) as client_info,
                       oi.id as item_id, oi.quantity, oi.price_snapshot,
                       p.name as product_name, p.description as product_desc
                FROM Orders o
                LEFT JOIN Order_Items oi ON o.id = oi.order_id
                LEFT JOIN Products p ON oi.product_id = p.id
                LEFT JOIN users u ON o.user_id = u.id";
        
        $params = [];
        $where = [];
        
        if ($status && $status !== 'all') {
            $where[] = "o.status = :status";
            $params['status'] = $status;
        }
        if ($userId && $userId > 0) {
            $where[] = "o.user_id = :uid";
            $params['uid'] = $userId;
        }
        
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY o.created_at DESC, oi.id ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. 🔹 ГРУППИРУЕМ товары по order_id (как в profile.php)
        $orders = [];
        foreach ($rows as $row) {
            $oid = $row['id'];
            
            // Создаём заказ, если ещё нет
            if (!isset($orders[$oid])) {
                $orders[$oid] = [
                    'id' => $oid,
                    'status' => $row['status'],
                    'total_sum' => $row['total_sum'],
                    'delivery_cost' => $row['delivery_cost'],
                    'delivery_address' => $row['delivery_address'],
                    'created_at' => $row['created_at'],
                    'client_info' => $row['client_info'], // 👈 client_info, как ждёт шаблон
                    'items' => []
                ];
            }
            
            // Добавляем товар, если он есть
            if ($row['product_name']) {
                $orders[$oid]['items'][] = [
                    'id' => $row['item_id'],
                    'name' => $row['product_name'],
                    'desc' => $row['product_desc'] ?? '',
                    'qty' => $row['quantity'],
                    'price_snapshot' => $row['price_snapshot'],
                    'subtotal' => $row['quantity'] * $row['price_snapshot']
                ];
            }
        }
        
        return array_values($orders); // Сбрасываем ключи для корректного foreach в шаблоне
    }

    public static function countAdmin(?string $status = null, ?int $userId = null): int {
        $pdo = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM Orders o";
        $params = [];
        $where = [];

        if ($status && $status !== 'all') { $where[] = "o.status = :status"; $params['status'] = $status; }
        if ($userId && $userId > 0) { $where[] = "o.user_id = :uid"; $params['uid'] = $userId; }

        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public static function getOrderWithItems(int $orderId): ?array {
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->prepare("
            SELECT o.*, COALESCE(u.email) as customer_name,
                   oi.quantity, oi.price_snapshot, p.name as product_name, p.description
            FROM Orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN Order_Items oi ON o.id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.id
            WHERE o.id = :id
            ORDER BY oi.id
        ");
        $stmt->execute(['id' => $orderId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return null;

        $order = [
            'id' => $rows[0]['id'], 'status' => $rows[0]['status'], 'total_sum' => $rows[0]['total_sum'],
            'delivery_cost' => $rows[0]['delivery_cost'], 'delivery_address' => $rows[0]['delivery_address'],
            'created_at' => $rows[0]['created_at'], 'customer_name' => $rows[0]['customer_name'], 'items' => []
        ];
        foreach ($rows as $row) {
            if ($row['product_name']) {
                $order['items'][] = [
                    'name' => $row['product_name'], 'desc' => $row['description'],
                    'qty' => $row['quantity'], 'price' => $row['price_snapshot'],
                    'subtotal' => $row['quantity'] * $row['price_snapshot']
                ];
            }
        }
        return $order;
    }
}
?>