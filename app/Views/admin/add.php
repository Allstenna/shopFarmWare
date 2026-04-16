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
      <h1>Добавить<br>товар</h1>
      <form method="POST" enctype="multipart/form-data">
        <a href="/" class="link-back">⟵ На главную</a>
        <?php if (!empty($message)): ?>
          <div class="message-box">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>
        <div class="input-field">
          <div class="form-input">
            <label class="label-form">Название</label>
            <input type="text" name="title" class="control-form" required>
          </div>
          <div class="form-input">
            <label class="label-form">Цена (руб.)</label>
            <input type="number" name="price" class="control-form" required>
          </div>
          <div class="form-input">
            <label class="label-form">Картинка</label>
            <div class="control-form"><input type="file" name="img"></div>
          </div>
          <div class="form-input">
            <label class="label-form">Описание</label>
            <textarea name="description" class="control-form"></textarea>
          </div>
        </div>
        <button type="submit" class="butn">Сохранить в БД</button>
      </form>
    </div>
  </main>
</body>
</html>
