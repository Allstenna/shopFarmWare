<?php
class CartController {
    
    public function index() {
        session_start();
        
        $cart_items = CartModel::getItems();
        $cart_total = CartModel::getSubtotal();
        $delivery_cost = DeliveryService::calculateCost($cart_total);

        include PROJECT_ROOT . '/app/Views/cart.php';
    }

    public function updateQuantity() {
        // 🔹 Сессия нужна для проверки прав (если добавите)
        session_start();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = (int) ($_POST['item_id'] ?? 0);
            $qty = (int) ($_POST['quantity'] ?? 0);
            
            if ($id <= 0 || $qty < 0) {
                throw new Exception('Неверные данные');
            }

            CartModel::update($id, $qty);
            echo json_encode(['success' => true, 'new_subtotal' => CartModel::getSubtotal()]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit; // 🔥 Критично: завершаем выполнение
    }

    public function checkout() {
        // 🔹 Сессия обязательна для получения user_id
        session_start();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $userId = $_SESSION['user_id'] ?? 0;
            if ($userId <= 0) {
                throw new Exception('Необходима авторизация');
            }

            $address = UserModel::getDeliveryAddress($userId);
            if (empty($address)) {
                throw new Exception('Адрес доставки не указан.');
            }

            $subtotal = CartModel::getSubtotal();
            if ($subtotal <= 0) {
                throw new Exception('Корзина пуста или все товары удалены');
            }

            $delivery = DeliveryService::calculateCost($subtotal);
            
            // 🔹 Единая переменная для БД
            $pdo = Database::getInstance();
            $pdo->beginTransaction();

            try {
                $orderId = OrderModel::create($userId, $address, $subtotal, $delivery);
                OrderModel::attachItems($orderId);
                $pdo->commit();
                
                echo json_encode(['success' => true, 'order_id' => $orderId]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
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
}
?>