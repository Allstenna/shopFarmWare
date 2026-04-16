<?php
class ProductModel {
    public static function getPrice(int $id): float {
        $stmt = Database::getInstance()->prepare("SELECT price FROM Products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public static function getFiltered(int $category, array $regions, int $limit, int $offset): array {
        $pdo = Database::getInstance();
        $sql = "SELECT p.id, p.name, p.description, p.price, p.img_url, p.category_id, c.name as category_name, f.region
                FROM Products p 
                JOIN Categories c ON p.category_id = c.id
                JOIN Farmers f ON p.farmer_id = f.id";
        $params = [];
        $where = [];

        if ($category > 0) {
            $where[] = "p.category_id = :category";
            $params['category'] = $category;
        }
        if (!empty($regions)) {
            $placeholders = [];
            foreach ($regions as $i => $r) {
                $ph = ":region{$i}";
                $placeholders[] = $ph;
                $params[$ph] = $r;
            }
            $where[] = "f.region IN (" . implode(',', $placeholders) . ")";
        }

        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";

        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
    public static function getAll(?int $categoryId = null, string $search = '', int $limit = 20, int $offset = 0): array {
        $pdo = Database::getInstance();
        $sql = "SELECT p.id, p.name, p.description, p.price, p.img_url, p.category_id, 
                       c.name as category_name, f.name as farmer_name
                FROM Products p
                LEFT JOIN Categories c ON p.category_id = c.id
                LEFT JOIN Farmers f ON p.farmer_id = f.id";
        $params = [];
        $where = [];

        if ($categoryId && $categoryId > 0) {
            $where[] = "p.category_id = :cat";
            $params['cat'] = $categoryId;
        }
        if ($search) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";

        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(?int $categoryId = null, array $regions = [], string $search = ''): int {
        $pdo = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM Products p JOIN Farmers f ON p.farmer_id = f.id";
        $params = [];
        $where = [];

        if ($categoryId && $categoryId > 0) {
            $where[] = "p.category_id = :cat";
            $params['cat'] = $categoryId;
        }
        if (!empty($regions)) {
            $phs = [];
            foreach ($regions as $i => $r) {
                $ph = ":r{$i}";
                $phs[] = $ph;
                $params[$ph] = $r;
            }
            $where[] = "f.region IN (" . implode(',', $phs) . ")";
        }
        if ($search !== '') {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public static function getById(int $id): ?array {
        $stmt = Database::getInstance()->prepare("SELECT * FROM Products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $pdo = Database::getInstance();
        $stmt = Database::getInstance()->prepare("INSERT INTO Products (name, description, price, img_url, category_id, farmer_id) 
                               VALUES (:name, :desc, :price, :img, :cat, :farmer)");
        $stmt->execute([
            'name' => $data['name'],
            'desc' => $data['description'] ?? '',
            'price' => $data['price'],
            'img' => $data['img_url'],
            'cat' => $data['category_id'] ?? null,
            'farmer' => $data['farmer_id'] ?? null
        ]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = Database::getInstance();
        $sql = "UPDATE Products SET name = :name, description = :desc, price = :price";
        $params = ['id' => $id, 'name' => $data['name'], 'desc' => $data['description'], 'price' => $data['price']];
        
        if (!empty($data['img_url'])) {
            $sql .= ", img_url = :img";
            $params['img'] = $data['img_url'];
        }
        if (isset($data['category_id'])) {
            $sql .= ", category_id = :cat";
            $params['cat'] = $data['category_id'];
        }
        
        $sql .= " WHERE id = :id";
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool {
        $pdo = Database::getInstance();
        // Сначала удаляем товары из корзины/заказов, если есть внешние ключи
        Database::getInstance()->prepare("DELETE FROM Order_Items WHERE product_id = :id")->execute(['id' => $id]);
        return Database::getInstance()->prepare("DELETE FROM Products WHERE id = :id")->execute(['id' => $id]);
    }
}
?>