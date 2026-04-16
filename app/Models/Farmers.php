<?php
class FarmerModel {
    public static function getAll(): array {
        return Database::getInstance()->query("SELECT id, name, region, phone, description FROM Farmers ORDER BY name")->fetchAll();
    }

    public static function getById(int $id): ?array {
        $stmt = Database::getInstance()->prepare("SELECT * FROM Farmers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getDistinctRegions(): array {
        return Database::getInstance()->query("SELECT DISTINCT region FROM Farmers WHERE region IS NOT NULL AND region != '' ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getProducts(int $farmerId, int $limit, int $offset, int $catId = 0): array {
        $pdo = Database::getInstance();
        $sql = "SELECT p.id, p.name, p.price, p.img_url, c.name as category_name
                FROM Products p JOIN Categories c ON p.category_id = c.id
                WHERE p.farmer_id = :farmerId";
        $params = ['farmerId' => $farmerId];
        if ($catId > 0) { $sql .= " AND p.category_id = :catId"; $params['catId'] = $catId; }
        
        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countProducts(int $farmerId, int $catId = 0): int {
        $pdo = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM Products WHERE farmer_id = :farmerId";
        $params = ['farmerId' => $farmerId];
        if ($catId > 0) { $sql .= " AND category_id = :catId"; $params['catId'] = $catId; }
        
        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
?>