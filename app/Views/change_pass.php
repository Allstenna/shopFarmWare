<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <title>Сброс пароля</title>
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
    <div class="container">
      <h1>Сброс пароля</h1>
      <form action="POST" action="/update_profile">
        <a href="/profile" class="link-back">⟵ Вернуться</a>
        <?php if (!empty($errorMsg)): ?>
          <div class="message-box">
            <?php echo $errorMsg; ?>
          </div>
        <?php endif; ?>
        <div class="input-field">
          <div class="form-input">
            <label class="label-form">Старый пароль</label>
            <input type="password" name="old_password" class="control-form" required>
          </div>
          <div class="form-input">
            <label class="label-form">Новый пароль</label>
            <input type="password" name="password" class="control-form" required>
          </div>
          <div class="form-input">
            <label class="label-form">Подтверждение пароля</label>
            <input type="password" name="confirm_password" class="control-form" required>
          </div>
        </div>
        <button type="submit" class="butn">Изменить</button>
      </form>
    </div>
  </main>
</body>
</html>