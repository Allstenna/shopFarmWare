<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 1. Определяем корень проекта (универсально для Beget/localhost)
define('PROJECT_ROOT', dirname(__DIR__));

// Автозагрузка классов (PSR-4 стиль)
spl_autoload_register(function ($class) {
    $prefix = ''; // Можно добавить неймспейсы позже
    $baseDir = PROJECT_ROOT . '/app/';
    
    $classMap = [
        'Database' => 'Models/Database.php',
        'ProductModel' => 'Models/Product.php',
        'CartModel' => 'Models/Cart.php',
        'OrderModel' => 'Models/Order.php',
        'UserModel' => 'Models/User.php',
        'CategoryModel' => 'Models/Category.php',
        'FarmerModel' => 'Models/Farmer.php',
        'DeliveryService' => 'Models/Delivery.php',
    ];
    
    if (isset($classMap[$class])) {
        $file = $baseDir . $classMap[$class];
        if (file_exists($file)) require_once $file;
        return;
    }
    
    // Контроллеры
    $controller = $class;
    if (str_ends_with($controller, 'Controller')) {
        $file = $baseDir . 'Controllers/' . $controller . '.php';
        if (file_exists($file)) require_once $file;
    }
});

// 2. Парсинг запроса
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Убираем имя скрипта и поддиректорию (farmware/)
$basePath = rtrim(dirname($scriptName), '/');
$path = $requestUri;
if ($basePath && $basePath !== '/') {
    $path = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $path);
}
$path = strtok($path, '?'); // Отделяем путь от query-параметров
$path = trim($path, '/');
$segments = $path ? explode('/', $path) : [];

// 3. Маршрутизация: [путь] => [контроллер => метод]
$routes = [
    ''              => ['controller' => 'HomeController', 'action' => 'index'],
    'home'          => ['controller' => 'HomeController', 'action' => 'index'],
    'home/delete'   => ['controller' => 'HomeController', 'action' => 'deleteProductFromHome'],
    
    'catalog'       => ['controller' => 'CatalogController', 'action' => 'index'],
    
    'cart'          => ['controller' => 'CartController', 'action' => 'index'],
    'cart/add'      => ['controller' => 'CartController', 'action' => 'addToCart'],
    'cart/update'   => ['controller' => 'CartController', 'action' => 'updateQuantity'],
    'cart/checkout' => ['controller' => 'CartController', 'action' => 'checkout'],
    
    'contacts'      => ['controller' => 'ContactsController', 'action' => 'index'],
    
    'login'         => ['controller' => 'AuthController', 'action' => 'login'],
    'logout'        => ['controller' => 'AuthController', 'action' => 'logout'],
    'register'      => ['controller' => 'AuthController', 'action' => 'register'],
    'change_pass'   => ['controller' => 'AuthController', 'action' => 'changePassword'],
    
    'profile'       => ['controller' => 'ProfileController', 'action' => 'index'],
    'upload/avatar' => ['controller' => 'ProfileController', 'action' => 'uploadAvatar'],
    
    'category'      => ['controller' => 'CategoryController', 'action' => 'show'],
    'farmer'        => ['controller' => 'FarmerController', 'action' => 'show'],
    
    'order'         => ['controller' => 'OrderController', 'action' => 'index'],
    'order/details' => ['controller' => 'OrderController', 'action' => 'details'],
    'order/buy'     => ['controller' => 'OrderController', 'action' => 'buyNow'],
    'order/status'  => ['controller' => 'OrderController', 'action' => 'updateStatus'],
    
    'admin'              => ['controller' => 'AdminController', 'action' => 'index'],
    'admin/orders'       => ['controller' => 'AdminController', 'action' => 'orders'],
    'admin/order'        => ['controller' => 'AdminController', 'action' => 'orderDetails'],
    'admin/order/status' => ['controller' => 'AdminController', 'action' => 'updateOrderStatus'],
    'admin/products'     => ['controller' => 'AdminController', 'action' => 'products'],
    'admin/product/add'  => ['controller' => 'AdminController', 'action' => 'addProduct'],
    'admin/product/edit' => ['controller' => 'AdminController', 'action' => 'editProduct'],
    'admin/product/delete' => ['controller' => 'AdminController', 'action' => 'deleteProduct'],
];

// 4. Поиск маршрута + обработка параметров из URL
$routeKey = $path;
$route = $routes[$routeKey] ?? null;

// Обработка динамических маршрутов: /order/details/10 → action=details, id=10
if (!$route && count($segments) >= 2) {
    $baseRoute = $segments[0] . '/' . $segments[1];
    if (isset($routes[$baseRoute]) && !empty($segments[2])) {
        $route = $routes[$baseRoute];
        $_GET['id'] = (int) $segments[2]; // Передаём ID из URL в $_GET
    }
}

// 5. Если маршрут не найден → 404
if (!$route) {
    http_response_code(404);
    require PROJECT_ROOT . '/app/Views/404.php';
    exit;
}

// 6. Запуск контроллера
$controllerName = $route['controller'];
$actionMethod = $route['action'];

// Преобразование snake_case → camelCase для методов (update_status → updateStatus)
$actionMethod = lcfirst(str_replace('_', '', ucwords($actionMethod, '_')));

if (!class_exists($controllerName)) {
    http_response_code(500);
    exit("Ошибка: Контроллер '$controllerName' не найден");
}

$controller = new $controllerName();

if (!method_exists($controller, $actionMethod)) {
    http_response_code(404);
    exit("Ошибка: Метод '$actionMethod' не найден в '$controllerName'");
}

// 7. Выполнение действия
$controller->$actionMethod();
?>