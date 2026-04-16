<?php
class CategoryModel {
    public static function getAll(): array {
        return Database::getInstance()->query("SELECT id, name, icon FROM Categories ORDER BY name")->fetchAll();
    }

    public static function getById(int $id): ?array {
        $stmt = Database::getInstance()->prepare("SELECT * FROM Categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getProducts(int $catId, int $limit, int $offset, array $regions = []): array {
        $pdo = Database::getInstance();
        $sql = "SELECT p.id, p.name, p.price, p.img_url, c.name as category_name, f.region
                FROM Products p 
                JOIN Categories c ON p.category_id = c.id 
                JOIN Farmers f ON p.farmer_id = f.id
                WHERE p.category_id = :catId";
        $params = ['catId' => $catId];

        if (!empty($regions)) {
            $phs = [];
            foreach ($regions as $i => $r) { $ph = ":r{$i}"; $phs[] = $ph; $params[$ph] = $r; }
            $sql .= " AND f.region IN (" . implode(',', $phs) . ")";
        }

        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countProducts(int $catId, array $regions = []): int {
        $pdo = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM Products p JOIN Farmers f ON p.farmer_id = f.id WHERE p.category_id = :catId";
        $params = ['catId' => $catId];

        if (!empty($regions)) {
            $phs = [];
            foreach ($regions as $i => $r) { $ph = ":r{$i}"; $phs[] = $ph; $params[$ph] = $r; }
            $sql .= " AND f.region IN (" . implode(',', $phs) . ")";
        }

        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
?>