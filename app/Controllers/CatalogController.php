<?php
class CatalogController {
    public function index() {
        session_start();
        $limit = 6;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        
        $products_per_page = 6;
        $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($current_page < 1) {
            $current_page = 1;
        }
        $offset = ($current_page - 1) * $products_per_page;

        $filter_category = (int) ($_GET['category'] ?? 0);
        $filter_regions = array_filter((array) ($_GET['regions'] ?? []));

        $products = ProductModel::getFiltered($filter_category, $filter_regions, $limit, $offset);
        $total_posts = ProductModel::count($filter_category, $filter_regions);
        $total_pages = ceil($total_posts / $limit);
        $current_filters = ['category' => $filter_category, 'regions' => $filter_regions];

        // Для фильтров шаблона
        $categories = Database::getInstance()->query("SELECT id, name, icon FROM Categories ORDER BY name")->fetchAll();
        $regions = Database::getInstance()->query("SELECT DISTINCT region FROM Farmers WHERE region IS NOT NULL AND region != '' ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);

        include PROJECT_ROOT . '/app/Views/catalog.php';
    }

    public function addToCart() { (new HomeController())->addToCart(); } // Переиспользуем логику
}
?>