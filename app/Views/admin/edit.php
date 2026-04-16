<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <title>Добавление товара</title>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="shortcut icon" href="/img/fw-logo.png" type="image/x-icon">
</head>

<body>
  <header>
    <nav>
      <a href="/" class="link_logo"><img src="/img/fw-name.svg" alt="FARMERS WARE" class="logo"></a>
    </nav>
  </header>
  <main class="form-main">
    <div class="container">
      <h1>Редактировать товар</h1>
      <form action="POST" action="/login">
        <a href="/" class="link-back">⟵ На главную</a>
        <?php if (!empty($errorMsg)): ?>
          <div class="message-box">
            <?php echo $errorMsg; ?>
          </div>
        <?php endif; ?>
        <div class="input-field">
          <div class="form-input">
            <label class="label-form">Название</label>
            <input type="text" name="title" class="control-form" placeholder="<?= htmlspecialchars($product['name']) ?>" required>
          </div>
          <div class="form-input">
            <label class="label-form">Цена (руб.)</label>
            <input type="number" name="price" class="control-form" placeholder="<?= htmlspecialchars($product['price']) ?>" required>
          </div>
          <div class="form-input">
            <label class="label-form">Картинка</label>
            <input type="text" name="image_url" class="control-form" required>
          </div>
          <div class="form-input">
            <label class="label-form">Описание</label>
            <textarea name="description" class="control-form" placeholder="<?= htmlspecialchars($product['description']) ?>" required></textarea>
          </div>
        </div>
        <button type="submit" class="butn">Обновить</button>
      </form>
    </div>
  </main>
</body>
</html>
