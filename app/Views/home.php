<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Главная страница</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="shortcut icon" href="img/fw-logo.png" type="image/x-icon">
</head>

<body>
  <header>
    <nav>
      <a href="/" class="link_logo"><img src="img/fw-name.svg" alt="FARMERS WARE" class="logo"></a>
    </nav>
  </header>
  <main>
    <div class="container contain">
      <div class="content-img">
        <nav class="nav" style="position: relative;">
          <button class="burger-menu" aria-label="Открыть меню" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
          </button>
          <ul class="page-list">
            <li class="page-list-item"><a class="link-item" href="/">Главная</a></li>
            <li class="page-list-item"><a class="link-item" href="/catalog">Каталог</a></li>
            <li class="page-list-item"><a class="link-item" href="/contacts">Контакты</a></li>
          </ul>
          <ul class="i-list">
            <li class="icon-item"><a href="/profile"><img class="icon-img" src="img/i-profile.svg" alt=""></a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
              <?php if ($_SESSION['user_role'] === 'client'): ?>
                <li class="icon-item"><a href="/cart"><img class="icon-img" src="img/i-cart.svg" alt=""></a></li>
              <?php endif; ?>
              <li class="icon-item"><a href="/logout"><img class="icon-img" src="img/i-logout.svg" alt=""></a></li>
            <?php endif; ?>
          </ul>
        </nav>
        <button class="btn-buy"><a href="/catalog">Купить</a></button>
      </div>
      <div class="category-contain">
        <h1>Категории</h1>
        <ul class="category-list">
          <?php foreach ($categories as $category): ?>
            <li class="category-item"><button
                class="btn-category <?= $current_filters['category'] === $category['id'] ? 'active-category' : '' ?>"
                data-category-id="<?= $category['id'] ?>"><img src="<?= htmlspecialchars($category['icon']) ?>"
                  alt="<?= htmlspecialchars($category['name']) ?>" class="category-icon"></button></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="products-slider">
        <?php if (count($products) > 0): ?>
          <?php foreach ($products as $product): ?>
            <div class="product-item">
              <img src="<?= htmlspecialchars($product['img_url']) ?>" alt="">
              <div class="product-content">
                <div class="product-info">
                  <p class="product-title"><?= htmlspecialchars($product['name']) ?></p>
                  <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                  <p class="product-price"><?= htmlspecialchars($product['price']) ?> &#8381;</p>
                </div>
                <div class="action-item">
                    <?php if (isset($_SESSION['user_id'])): ?>
                      <?php if ($_SESSION['user_role'] === 'client'): ?>
                      <button class="btn-to-cart" data-product-id="<?= (int) $product['id'] ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>" type="button">В корзину</button>
                      <?php endif; ?>
                      <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/admin/product/edit?id=<?= (int) $product['id'] ?>" class="btn-edit-item"></a>
                        <button 
                          class="btn-delete-item btn-delete-item-home" 
                          data-product-id="<?= (int) $product['id'] ?>"
                          data-product-name="<?= htmlspecialchars($product['name']) ?>"
                        ></button>
                        
                      <?php endif; ?>
                    <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <a href="/catalog" class="link-more">Смотреть весь каталог >></a>
    </div>
  </main>
</body>
<script src="js/addCart.js"></script>
<script src="js/filters.js"></script>
<script src="js/homeDelete.js"></script>
<script src="js/burgerMenu.js"></script>
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
</html>