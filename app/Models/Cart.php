<?php
class CartModel {
    public static function add(int $productId, int $quantity = 1): void {
        $pdo = Database::getInstance();
        $price = ProductModel::getPrice($productId);
        if ($price <= 0) throw new Exception('Товар не найден');

        $stmt = Database::getInstance()->prepare("SELECT id, quantity FROM Order_Items WHERE order_id IS NULL AND product_id = :pid");
        $stmt->execute(['pid' => $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            Database::getInstance()->prepare("UPDATE Order_Items SET quantity = :qty WHERE id = :id")
                ->execute(['qty' => $existing['quantity'] + $quantity, 'id' => $existing['id']]);
        } else {
            Database::getInstance()->prepare("INSERT INTO Order_Items (order_id, product_id, quantity, price_snapshot) VALUES (NULL, :pid, :qty, :price)")
                ->execute(['pid' => $productId, 'qty' => $quantity, 'price' => $price]);
        }
    }

    public static function update(int $itemId, int $quantity): void {
        $pdo = Database::getInstance();
        Database::getInstance()->prepare("UPDATE Order_Items SET quantity = :qty WHERE id = :id AND (order_id IS NULL OR order_id = 0)")
            ->execute(['qty' => $quantity, 'id' => $itemId]);
    }

    public static function getItems(): array {
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->prepare("SELECT oi.id as item_id, oi.quantity, oi.price_snapshot, p.name, p.description, p.img_url
                               FROM Order_Items oi JOIN Products p ON oi.product_id = p.id
                               WHERE (oi.order_id IS NULL OR oi.order_id = 0) ORDER BY oi.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getSubtotal(): float {
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->query("SELECT SUM(quantity * price_snapshot) as total FROM Order_Items WHERE (order_id IS NULL OR order_id = 0) AND quantity > 0");
        return (float) ($stmt->fetch()['total'] ?? 0);
    }
}
?>