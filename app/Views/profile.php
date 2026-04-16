<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Профиль</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
      <div class="profile-btn">
        <button class="btn-main"><a href="/">⟵ На главную</a></button>
      </div>
      <div class="profile-content">
        <div class="user-contain">
          <div class="user-info">
            <img src="<?= htmlspecialchars($userAvatar) ?>" class="avatar">
            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#ModalEditImg">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FFF" class="bi bi-pencil"
                viewBox="0 0 16 16">
                <path
                  d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325" />
              </svg>
            </button>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
              <p class="username">Админ</p>
            <?php else: ?>
              <p class="username">Клиент</p>
            <?php endif; ?>
          </div>
          <div class="profile-opts">
            <ul class="profile-opt">
              <li class="profile-opt-item"><a href="/change_pass">Сменить пароль</a></li>
            </ul>
          </div>
        </div>
        <div class="orders-contain">
          <div class="menu">
            <h1 class="orders-title">Заказы</h1>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
              <div class="admin-actions">
                 <button class="btn-make-order btn-edit-orders"><a href="/admin/orders">Редактировать заказы</a></button>
                <button class="btn-make-order"><a href="/admin/product/add">Добавить товар</a></button>
              </div>
            <?php endif; ?>
          </div>

          <?php if (empty($orders)): ?>
            <div class="empty-orders text-center py-5 text-muted">
              У вас пока нет оформленных заказов. <a href="/catalog">Перейти в каталог</a>
            </div>
          <?php else: ?>
            <?php $global_pos = 1;
            foreach ($orders as $order): ?>
              <div class="order-card">
                <div class="order-header">
                  <div class="order-info">
                    <h3 class="order-date">Заказ от <?= date('d.m.Y', strtotime($order['created_at'])) ?></h3>
                  </div>
                  <?php if ($isAdmin): ?>
                    <button class="btn-edit-order" data-order-id="<?= $order['id'] ?>">Изменить</button>
                    <select class="status-select d-none" data-order-id="<?= $order['id'] ?>">
                      <option value="Новый">Новый</option>
                      <option value="В обработке">В обработке</option>
                      <option value="Отправлен">Отправлен</option>
                      <option value="Выполнен">Выполнен</option>
                      <option value="Отменен">Отменен</option>
                    </select>
                  <?php endif; ?>
                  <div class="order-meta">
                    <div class="order-stat <?= $getStatusClass($order['status']) ?>">
                      <?= htmlspecialchars($order['status'] === 'pending' ? 'Новый' : $order['status']) ?>
                    </div>
                    <div class="order-total"><?= number_format($order['total_sum'], 0, ',', ' ') ?> руб.</div>


                    <div class="order-arrow">
                      <img src="/img/i-arrow-up.svg" alt="Показать детали" class="arrow">
                    </div>
                  </div>
                </div>

                <!-- Скрыто по умолчанию -->
                <div class="order-details" style="display: none;">
                  <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                      <!-- ✅ Добавлен класс is-zero при qty <= 0 -->
                      <div class="order-item <?= ($item['qty'] <= 0) ? 'is-zero' : '' ?>">
                        <div class="order-item-info">
                          <p class="item-id"><?= $global_pos++ ?></p>
                          <p class="item-name"><?= htmlspecialchars($item['name']) ?></p>
                          <p class="item-desc"><?= htmlspecialchars($item['desc'] ?? '') ?></p>
                        </div>
                        <div class="order-price-num">
                          <p class="item-num"><?= $item['qty'] ?> шт.</p>
                          <p class="price"><?= number_format($item['subtotal'], 0, ',', ' ') ?> руб.</p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <hr>
                  <div class="order-delivery">
                    <div class="order-delivery-sum">
                        <p>Доставка</p>
                        <p>: <?= number_format($order['delivery_cost'], 0, ',', ' ') ?> руб.</p>
                    </div>
                        <?php if (!empty($order['delivery_address'])): ?>
                          <p class="text-muted mb-0" style="font-size: 14px;">Адрес:
                            <?= htmlspecialchars($order['delivery_address']) ?>
                          </p>
                        <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal fade" id="ModalEditImg" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-title" id="exampleModalLabel">Загрузите изображение</div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="form-btstrp" action="/upload" method="POST" enctype="multipart/form-data">
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Выберите изображение:</label>
                  <input type="file" name="file" class="form-control" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-dark">Загрузить</button>
              </div>
            </form>
          </div>
        </div>
      </div>
  </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/showOrder.js"></script>

</html>