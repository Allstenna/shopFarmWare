<?php
class HomeController {
    public function index() {
        session_start();
        $filter_category = isset($_GET['category']) ? (int) $_GET['category'] : 0;
        $categories = CategoryModel::getAll();
        $products = ProductModel::getFiltered($filter_category, [], 4, 0);
        $total_posts = ProductModel::count($filter_category, []);
        $total_pages = ceil($total_posts / 4);
        $current_filters = ['category' => $filter_category];

        include PROJECT_ROOT . '/app/Views/home.php';
    }

    public function addToCart() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $pid = (int) ($_POST['product_id'] ?? 0);
            $qty = (int) ($_POST['quantity'] ?? 1);
            if ($pid <= 0) throw new Exception('Неверный ID товара');
            
            CartModel::add($pid, $qty);
            echo json_encode(['success' => true, 'message' => 'Товар добавлен']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
public function deleteProductFromHome() {
        session_start();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // 1. Проверка прав администратора
            if (($_SESSION['user_role'] ?? '') !== 'admin') {
                throw new Exception('Доступ запрещён');
            }
            
            // 2. Проверка CSRF-токена
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
                throw new Exception('Ошибка безопасности: неверный CSRF-токен');
            }
            
            // 3. Получаем и валидируем ID
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('Неверный ID товара');
            
            // 4. Получаем товар для удаления изображения
            $product = ProductModel::getById($id);
            if (!$product) throw new Exception('Товар не найден');
            
            // 5. Удаляем файл изображения, если он есть
            $img = $product['img_url'] ?? '';
            if ($img && strpos($img, 'uploads/') === 0) {
                $filePath = PROJECT_ROOT . '/public_html/' . $img;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // 6. Удаляем товар из БД (модель сама удалит связанные записи)
            ProductModel::delete($id);
            
            echo json_encode(['success' => true, 'message' => 'Товар удалён']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>