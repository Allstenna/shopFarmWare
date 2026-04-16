<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <title>Авторизация</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="shortcut icon" href="img/fw-logo.png" type="image/x-icon">
</head>

<body>
  <header>
    <nav>
      <a href="/" class="link_logo"><img src="img/fw-name.svg" alt="FARMERS WARE" class="logo"></a>
    </nav>
  </header>
  <main class="form-main">
    <div class="container">
      <h1>Вход</h1>
      <form method="POST" action="/login">
        <a href="/" class="link-back">⟵ Вернуться</a>
        <?php if (!empty($errorMsg)): ?>
          <div class="message-box">
            <?php echo $errorMsg; ?>
          </div>
        <?php endif; ?>
        <div class="input-field">
          <div class="form-input">
            <label class="label-form">Почта (email)</label>
            <input type="email" name="email" class="control-form" required>
          </div>
          <div class="form-input">
            <label class="label-form">Пароль</label>
            <input type="password" name="password" class="control-form" required>
          </div>
        </div>
        <button type="submit" class="butn">Войти</button>
        <a class="link-to" href="/register">Регистрация</a>
      </form>
    </div>
  </main>
</body>
</html>
