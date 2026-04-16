<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Корзина</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="shortcut icon" href="/img/fw-logo.png" type="image/x-icon">
</head>

<body>
  <header>
    <nav>
      <a href="/" class="link_logo"><img src="/img/fw-name.svg" alt="FARMERS WARE" class="logo"></a>
    </nav>
  </header>

  <main>
    <div class="container contain">
      <div class="profile-btn">
        <button class="btn-main"><a href="/">⟵ На главную</a></button>
      </div>

      <div class="profile-content admin-order-content">
        <div class="orders-contain">
          <h1 class="admin-orders-title ad-or-ti-cart">Заказы</h1>

          <?php if (empty($cart_items)): ?>
            <div class="empty-cart text-center py-5 text-muted">
              Корзина пуста. <a href="/catalog">Перейти в каталог</a>
            </div>
          <?php else: ?>
            <div class="order-card">
              <div class="order-details or-det-cart">
                <div class="order-items">
                  <?php $position = 1;
                  foreach ($cart_items as $item): ?>
                    <?php $subtotal = $item['quantity'] * $item['price_snapshot']; ?>
                    <div class="order-item <?= $item['quantity'] <= 0 ? 'is-zero' : '' ?>"
                      data-item-id="<?= $item['item_id'] ?>" data-price-snapshot="<?= $item['price_snapshot'] ?>"
                      data-initial-qty="<?= $item['quantity'] ?>">

                      <div class="order-item-info">
                        <p class="item-id"><?= $position++ ?></p>
                        <p class="item-name"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                      </div>

                      <div class="order-price-num">
                        <div class="num-change">
                          <button class="btn-num-change btn-less" type="button" aria-label="Уменьшить" <?= $item['quantity'] <= 0 ? 'style="display:none"' : '' ?>></button>
                          <p class="item-num" <?= $item['quantity'] <= 0 ? 'style="display:none"' : '' ?>>
                            <?= $item['quantity'] ?> шт.
                          </p>
                          <button class="btn-num-change btn-more" type="button" aria-label="Увеличить"></button>
                        </div>
                        <p class="price" data-subtotal="<?= $subtotal ?>" <?= $item['quantity'] <= 0 ? 'style="text-decoration: line-through; opacity: 0.6"' : '' ?>>
                          <?= number_format($subtotal, 0, ',', ' ') ?> руб.
                        </p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <hr>
                <div class="cart-summary" data-delivery="<?= (float) $delivery_cost ?>">
                  <div class="summary-line delivery-line">
                    <span class="label">Доставка:</span>
                    <span class="value" id="delivery-amount"><?= number_format($delivery_cost, 0, ',', ' ') ?> руб.</span>
                  </div>

                  <div class="summary-line total-line">
                    <span class="label">Итого:</span>
                    <span class="value grand-total" id="cart-grand-total">
                      <?= number_format($cart_total + $delivery_cost, 0, ',', ' ') ?> руб.
                    </span>
                  </div>

                </div>
                <button class="btn-make-order" type="button">Оформить заказ</button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <!-- Подключение внешнего JS -->
  <script src="/js/changeQty.js" defer></script>
</body>

</html>