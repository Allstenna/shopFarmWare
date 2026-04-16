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
        <nav class="nav"  style="position: relative;">
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
            <li class="icon-item"><a href="#"><img class="icon-img" src="img/i-cart.svg" alt=""></a></li>
          </ul>
        </nav>
      </div>
      <div class="contacts-main">
        <aside class="contatcs-info">
          <h1>Контакты</h1>
          <div class="contacts-options">
            <div class="option-contact">
              <p>Почта (email)</p>
              <a class="info" href="mailto: farmersware@shop.com">farmersware@shop.com</a>
            </div>
            <div class="option-contact">
              <p>Телефон</p>
              <a class="info" href="tel: +77777777777">+7 (777) 777-77-77</a>
            </div>
            <div class="option-contact">
              <p>Социальные сети</p>
              <div class="socials">
                <img src="img/i-vk.svg" alt="" class="media-i">
                <img src="img/i-max.svg" alt="" class="media-i">
              </div>
            </div>

          </div>
        </aside>
        <div class="contacts-form">
          <form method="POST" action="/contact">
            <?php if (!empty($errorMsg)): ?>
              <div class="message-box">
                <?php echo $errorMsg; ?>
              </div>
            <?php endif; ?>
            <div class="input-field">
              <div class="form-input">
                <label class="label-form">Имя</label>
                <input type="text" name="name" class="control-form" required>
              </div>
              <div class="form-input">
                <label class="label-form">Почта (email)</label>
                <input type="email" name="email" class="control-form" required>
              </div>
              <div class="form-input">
                <label class="label-form">Сообщение</label>
                <textarea name="message" class="control-form" required></textarea>
              </div>
            </div>
            <button type="submit" class="butn">Отправить сообщение</button>
          </form>

        </div>
      </div>
  </main>
</body>
<script src="js/burgerMenu.js"></script>
</html>