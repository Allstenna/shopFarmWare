<?php
class AdminController {
    private function requireAdmin() {
        session_start();
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            header('Location: /');
            exit;
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    // Главная панель администратора
    public function index() {
        $this->requireAdmin();
        
        // Статистика для дашборда
        $pdo = Database::getInstance();
        $stats = [
            'total_orders' => Database::getInstance()->query("SELECT COUNT(*) FROM Orders")->fetchColumn(),
            'pending_orders' => Database::getInstance()->query("SELECT COUNT(*) FROM Orders WHERE status = 'Новый'")->fetchColumn(),
            'total_products' => Database::getInstance()->query("SELECT COUNT(*) FROM Products")->fetchColumn(),
            'total_users' => Database::getInstance()->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn(),
        ];
        
        $recentOrders = OrderModel::getAllAdmin(null, null, 5, 0);
        
        include PROJECT_ROOT . '/app/Views/admin/admin_panel.php';
    }

    // Список заказов (админ)
    public function orders() {
        $this->requireAdmin();
        
        // 🔹 AJAX: Обновление количества позиции + пересчёт итогов
        // Должен быть ДО любых include и HTML-вывода
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_quantity') {
            header('Content-Type: application/json; charset=utf-8');
            
            try {
                $item_id = (int) ($_POST['item_id'] ?? 0);
                $qty = (int) ($_POST['quantity'] ?? 0);
                
                if ($item_id <= 0 || $qty < 0) {
                    throw new Exception('Неверные данные');
                }
                
                $pdo = Database::getInstance();
                
                // 1. Обновляем количество
                $pdo->prepare("UPDATE Order_Items SET quantity = :qty WHERE id = :id")
                    ->execute(['qty' => $qty, 'id' => $item_id]);
                
                // 2. Получаем order_id для пересчёта
                $stmt = $pdo->prepare("SELECT order_id FROM Order_Items WHERE id = :id");
                $stmt->execute(['id' => $item_id]);
                $order_id = (int) $stmt->fetchColumn();
                
                if ($order_id > 0) {
                    // 3. Считаем сумму АКТИВНЫХ товаров (qty > 0)
                    $stmt = $pdo->prepare("
                        SELECT SUM(quantity * price_snapshot) as subtotal 
                        FROM Order_Items 
                        WHERE order_id = :oid AND quantity > 0
                    ");
                    $stmt->execute(['oid' => $order_id]);
                    $subtotal = (float) ($stmt->fetchColumn() ?? 0);
                    
                    // 4. 🔹 Рассчитываем доставку (тот же алгоритм, что в корзине!)
                    if ($subtotal >= 3000) {
                        $delivery = 0;
                    } elseif ($subtotal >= 2000) {
                        $delivery = ceil($subtotal * 0.10);
                    } elseif ($subtotal >= 1000) {
                        $delivery = ceil($subtotal * 0.25);
                    } else {
                        $delivery = ceil($subtotal * 0.35);
                    }
                    
                    $grand_total = $subtotal + $delivery;
                    
                    // 5. Обновляем заказ
                    $pdo->prepare("
                        UPDATE Orders 
                        SET total_sum = :total, delivery_cost = :delivery 
                        WHERE id = :oid
                    ")->execute([
                        'total' => $grand_total,
                        'delivery' => $delivery,
                        'oid' => $order_id
                    ]);
                    
                    // 6. Возвращаем новые значения для обновления UI
                    echo json_encode([
                        'success' => true,
                        'new_subtotal' => $subtotal,
                        'new_delivery' => $delivery,
                        'new_total' => $grand_total
                    ]);
                } else {
                    echo json_encode(['success' => true]);
                }
                
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit; // 🔥 Критично: завершаем выполнение для AJAX
        }
        
        // 🔹 ОТОБРАЖЕНИЕ СПИСКА ЗАКАЗОВ (не AJAX)
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $statusFilter = $_GET['status'] ?? 'all';
        $userIdFilter = (int) ($_GET['user_id'] ?? 0);
        
        // 🔹 ИСПРАВЛЕНО: используем getAllAdmin() с пагинацией и фильтрами
        $orders = OrderModel::getAllAdmin(
            $statusFilter, 
            $userIdFilter > 0 ? $userIdFilter : null, 
            $limit, 
            $offset
        );
        
        // 🔹 Подсчёт общего количества для пагинации
        $total = OrderModel::countAdmin(
            $statusFilter, 
            $userIdFilter > 0 ? $userIdFilter : null
        );
        $totalPages = ceil($total / $limit);
        
        // 🔹 Хелпер для классов статусов (исправлен синтаксис)
        $getStatusClass = function($status) {
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
        
        // 🔹 Списки для фильтров в шаблоне
        $users = Database::getInstance()->query(
            "SELECT id, email FROM users WHERE role = 'client' ORDER BY email"
        )->fetchAll();
        
        // 🔹 Передаём ВСЕ переменные в шаблон
        include PROJECT_ROOT . '/app/Views/admin/admin_orders.php';
    }

    // Детали заказа (админ)
    public function orderDetails() {
        $this->requireAdmin();

        $orderId = (int) ($_GET['id'] ?? 0);
        if ($orderId <= 0) { http_response_code(404); exit('Заказ не найден'); }
        
        $order = OrderModel::getOrderWithItems($orderId);
        if (!$order) { http_response_code(404); exit('Заказ не найден'); }
        
        include PROJECT_ROOT . '/Views/admin/admin_orders.php';
    }

    // Обновление статуса заказа (AJAX)
    public function updateOrderStatus() {
        $this->requireAdmin();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $newStatus = trim($_POST['status'] ?? '');
            $allowed = ['Новый', 'В обработке', 'Отправлен', 'Выполнен', 'Отменен'];
            
            if ($orderId <= 0 || !in_array($newStatus, $allowed)) {
                throw new Exception('Неверные данные');
            }
            
            $pdo = Database::getInstance();
            
            // 🔹 Если статус "Отменен" — обнуляем суммы и товары
            if ($newStatus === 'Отменен') {
                // 1. Обнуляем количество во всех позициях заказа
                $pdo->prepare("UPDATE Order_Items SET quantity = 0 WHERE order_id = :oid")
                    ->execute(['oid' => $orderId]);
                
                // 2. Обнуляем итоговые суммы в заказе
                $pdo->prepare("UPDATE Orders SET total_sum = 0, delivery_cost = 0 WHERE id = :oid")
                    ->execute(['oid' => $orderId]);
            }
            
            // 3. Обновляем статус (в любом случае)
            OrderModel::updateStatus($orderId, $newStatus);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Статус обновлен',
                'was_cancelled' => ($newStatus === 'Отменен') // Флаг для фронтенда
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Добавление товара
    public function addProduct() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                $errorMsg = "Ошибка безопасности: Неверный CSRF-токен!";
            } else {
                $name = trim($_POST['name'] ?? '');
                $price = (float) ($_POST['price'] ?? 0);
                $desc = trim($_POST['description'] ?? '');
                
                if (empty($name) || $price <= 0) {
                    $errorMsg = "Заполните название и укажите цену > 0";
                } else {
                    $imgPath = null;
                    if (!empty($_FILES['img']['name']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (!in_array($_FILES['img']['type'], $allowed)) {
                            $errorMsg = "Разрешены только изображения (JPG, PNG, GIF, WebP)";
                        } else {
                            $uploadDir = PROJECT_ROOT . '/public_html/uploads/';
                            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                            
                            $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
                            $newName = 'prod_' . uniqid() . '.' . $ext;
                            $destination = $uploadDir . $newName;
                            
                            if (move_uploaded_file($_FILES['img']['tmp_name'], $destination)) {
                                $imgPath = 'uploads/' . $newName;
                            } else {
                                $errorMsg = "Не удалось сохранить изображение";
                            }
                        }
                    }
                    
                    if (!isset($errorMsg)) {
                        try {
                            ProductModel::create([
                                'name' => $name,
                                'description' => $desc,
                                'price' => $price,
                                'img_url' => $imgPath
                                
                            ]);
                            header("Location: /admin/products?success=1");
                            exit;
                        } catch (Exception $e) {
                            $errorMsg = "Ошибка БД: " . $e->getMessage();
                        }
                    }
                }
            }
        }
        include PROJECT_ROOT . '/app/Views/admin/add.php';
    }

    // Редактирование товара
    public function editProduct() {
        $this->requireAdmin();
        
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(404); exit('Товар не найден'); }
        
        $product = ProductModel::getById($id);
        if (!$product) { http_response_code(404); exit('Товар не найден'); }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                $errorMsg = "Ошибка безопасности: Неверный CSRF-токен!";
            } else {
                $name = trim($_POST['name'] ?? '');
                $price = (float) ($_POST['price'] ?? 0);
                $desc = trim($_POST['description'] ?? '');
                $catId = (int) ($_POST['category_id'] ?? 0);
                
                if (empty($name) || $price <= 0) {
                    $errorMsg = "Заполните название и укажите цену > 0";
                } else {
                    $data = ['name' => $name, 'description' => $desc, 'price' => $price];
                    if (isset($_POST['category_id'])) $data['category_id'] = $catId > 0 ? $catId : null;
                    
                    // Обработка нового изображения
                    if (!empty($_FILES['img']['name']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (in_array($_FILES['img']['type'], $allowed)) {
                            $uploadDir = PROJECT_ROOT . '/public_html/uploads/';
                            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                            
                            $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
                            $newName = 'prod_' . uniqid() . '.' . $ext;
                            $destination = $uploadDir . $newName;
                            
                            if (move_uploaded_file($_FILES['img']['tmp_name'], $destination)) {
                                // Удаляем старое изображение, если это не дефолтное
                                $oldImg = $product['img_url'];
                                if ($oldImg && strpos($oldImg, 'uploads/') === 0 && file_exists(PROJECT_ROOT . '/public/' . $oldImg)) {
                                    unlink(PROJECT_ROOT . '/public_html/' . $oldImg);
                                }
                                $data['img_url'] = 'uploads/' . $newName;
                            }
                        }
                    }
                    
                    try {
                        ProductModel::update($id, $data);
                        header("Location: /admin/products?success=1");
                        exit;
                    } catch (Exception $e) {
                        $errorMsg = "Ошибка БД: " . $e->getMessage();
                    }
                }
            }
        }
        
        $categories = CategoryModel::getAll();
        include PROJECT_ROOT . '/app/Views/admin/edit.php';
    }

    // Удаление товара (AJAX или POST)
    public function deleteProduct() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            try {
                if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                    throw new Exception('Ошибка безопасности');
                }
                
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception('Неверный ID');
                
                $product = ProductModel::getById($id);
                if (!$product) throw new Exception('Товар не найден');
                
                // Удаляем файл изображения
                $img = $product['img_url'];
                if ($img && strpos($img, 'uploads/') === 0 && file_exists(PROJECT_ROOT . '/public/' . $img)) {
                    unlink(PROJECT_ROOT . '/public/' . $img);
                }
                
                ProductModel::delete($id);
                echo json_encode(['success' => true, 'message' => 'Товар удалён']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
        
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Метод не разрешён']);
        exit;
    }
}
?>