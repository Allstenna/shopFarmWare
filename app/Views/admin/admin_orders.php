<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Главная страница</title>
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
        <div class="orders-contain ">
          <h1 class="admin-orders-title">Заказы</h1>
          <?php if (empty($orders)): ?>
            <div class="empty-orders text-center py-5 text-muted">Заказов пока нет.</div>
          <?php else: ?>
            <?php $global_pos = 1;
            foreach ($orders as $order): ?>
              <div class="order-card">
                <div class="order-header">
                  <div class="order-info">
                    <h3 class="order-date">Заказ от <?= date('d.m.Y', strtotime($order['created_at'])) ?> ·
                      <?= htmlspecialchars($order['client_info']) ?>
                    </h3>
                  </div>
                  <div class="edit_order">
                    <?php if ($isAdmin): ?>
                      <button class="btn-edit-order" data-order-id="<?= $order['id'] ?>">Изменить</button>
                      <select class="status-select d-none" data-order-id="<?= $order['id'] ?>">
                        <option value="Новый">Новый</option>
                        <option value="В обработке">В обработке</option>
                        <option value="Отправлен">Отправлен</option>
                        <option value="Отменен">Отменен</option>
                      </select>
                    <?php endif; ?>
                  </div>
                  <div class="order-meta ">
                    <div class="order-stat <?= $getStatusClass($order['status']) ?>">
                      <?= htmlspecialchars($order['status'] === 'pending' ? 'Новый' : $order['status']) ?>
                    </div>
                    <div class="order-total"><strong id="order-grand-total"><?= number_format($order['total_sum'], 0, ',', ' ') ?> руб.</strong></div>


                    <div class="order-arrow">
                      <img src="/img/i-arrow-up.svg" alt="Показать детали" class="arrow">
                    </div>
                  </div>
                </div>

                
                <!-- Скрыто по умолчанию -->
                <div class="order-details" style="display: none;">
                  <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                      <div class="order-item <?= $item['qty'] <= 0 ? 'is-zero' : '' ?>" 
                           data-item-id="<?= $item['id'] ?>"
                           data-price-snapshot="<?= $item['price_snapshot'] ?>" 
                           data-qty="<?= $item['qty'] ?>">
                        <div class="order-item-info">
                          <p class="item-id"><?= $global_pos++ ?></p>
                          <p class="item-name"><?= htmlspecialchars($item['name']) ?></p>
                          <p class="item-desc"><?= htmlspecialchars($item['desc'] ?? '') ?></p>
                        </div>
                        <div class="order-price-num">
                          <div class="num-change">
                            <button class="btn-num-change btn-less" type="button" <?= $item['qty'] <= 0 ? 'style="display:none"' : '' ?>></button>
                            <p class="item-num mb-0" <?= $item['qty'] <= 0 ? 'style="display:none"' : '' ?>><?= $item['qty'] ?> шт.</p>
                            <button class="btn-num-change btn-more" type="button"></button>
                          </div>
                          <p class="price mb-0"><?= number_format($item['subtotal'], 0, ',', ' ') ?> руб.</p>
                          <button class="btn-delete-item" type="button" title="Удалить позицию" <?= $item['qty'] <= 0 ? 'style="display:none"' : '' ?>></button>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  
                  <hr>
                  
                  <?php if (!empty($order['delivery_address'])): ?>
                    <p class="text-muted mb-0 mt-2" style="font-size: 14px;">📍 Адрес: <?= htmlspecialchars($order['delivery_address']) ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>
  </main>
</body>
<script src="/js/adminOrders.js" defer></script>

</html>