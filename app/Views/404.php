<?php
// Устанавливаем правильный HTTP статус
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, follow">
  <title>Страница не найдена</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
  <style>
    body {
      background-color: #f8f9fa;
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 20px;
    }

    .error-container {
      max-width: 600px;
      margin: 0 auto;
      text-align: center;
    }

    .error-code {
      font-size: 6rem;
      font-weight: 700;
      color: #6c757d;
      margin-bottom: 0;
    }

    .error-text {
      font-size: 1.5rem;
      color: #495057;
      margin-bottom: 1.5rem;
    }

    .error-desc {
      color: #6c757d;
      margin-bottom: 2rem;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="error-container">
      <h1 class="error-code">404</h1>
      <h2 class="error-text">Страница не найдена</h2>
      <p class="error-desc">Запрашиваемая страница не существует или была удалена.</p>
      <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
        <a href="/" class="btn btn-primary px-4">На главную</a>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (только при необходимости интерактива) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>