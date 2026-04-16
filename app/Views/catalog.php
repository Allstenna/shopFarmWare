<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Каталог</title>
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
            <li class="icon-item"><a href="/cart"><img class="icon-img" src="img/i-cart.svg" alt=""></a></li>
          </ul>
        </nav>
      </div>
      <div class="category-main">
        <aside class="filters">
          <div class="filter-div">
            <p class="filt">Фильтрация</p>
          </div>
          <h3>Регион</h3>
          <div class="filter-options">
            <?php foreach ($regions as $region): ?>
              <div class="option-region">
                <input type="checkbox" name="regions[]" id="region-<?= md5($region) ?>" value="<?= htmlspecialchars($region) ?>" <?= in_array($region, $current_filters['regions']) ? 'checked' : '' ?> onchange="applyRegionFilters()">
                <label><?= htmlspecialchars($region) ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </aside>
        <div class="container-category">
          <div class="category-contain">
            <h1>Категории</h1>
            <ul class="category-list">
              <?php foreach ($categories as $category): ?>
                <li class="category-item"><button
                    class="btn-category <?= $current_filters['category'] === $category['id'] ? 'active-category' : '' ?>"
                    data-category-id="<?= $category['id'] ?>"><img src="<?= htmlspecialchars($category['icon']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="category-icon"></button></li>
                <?php endforeach; ?>
            </ul>
          </div>
          <div class=" products-slider">
            <?php if (count($products) > 0): ?>
              <?php foreach ($products as $product): ?>
                <div class="product-item product-cat">
                  <img src="<?= htmlspecialchars($product['img_url']) ?>" alt="">
                  <div class="product-content">
                    <div class="product-info">
                      <p class="product-title"><?= htmlspecialchars($product['name']) ?></p>
                      <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                      <p class="product-price"><?= htmlspecialchars($product['price']) ?> &#8381;</p>
                    </div>
                    <div class="action-item">
                      <button class="btn-to-cart" data-product-id="<?= (int) $product['id'] ?>" type="button">В корзину</button>
                      <?php if (isset($_SESSION['user_id'])): ?>
                          <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="/admin/product/edit?id=<?= (int) $product['id'] ?>" class="btn-edit-item btn-main"></a>
                          <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <?php if ($total_pages > 0): ?>
            <div class="slider-controls" data-total="<?= (int) $total_pages ?>">
              <button class="btn-control btn-prev" data-page="<?= $current_page - 1 ?>" <?= $current_page <= 1 ? 'disabled' : '' ?>>
              </button>

              <div class="page-index">
                <?php
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);
                for ($i = $start; $i <= $end; $i++):
                  ?>
                  <button class="page-num <?= $i == $current_page ? 'active' : '' ?>" data-page="<?= $i ?>">
                    <?= $i ?>
                  </button>
                <?php endfor; ?>
              </div>

              <button class="btn-control btn-next" data-page="<?= $current_page + 1 ?>" <?= $current_page >= $total_pages ? 'disabled' : '' ?>>
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>
  </main>
</body>
<script src="js/addCart.js"></script>
<script src="js/pagination.js"></script>
<script src="js/filters.js"></script>
<script src="js/burgerMenu.js"></script>
</html>