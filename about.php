<?php ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Союз Защиты Прав Потребителей - О нас</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="site-root">
  <header class="site-header">
    <div class="container">
      <h1 class="logo">СЗПП</h1>
      <nav>
        <ul class="nav">
          <li><a href="index.php">Главная</a></li>
          <li><a href="about.php" class="active">О нас</a></li>
          <li><a href="szpp.php">СЗПП</a></li>
          <li><a href="contact.php">Контакт</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container">
    <?php
      $frag = __DIR__ . '/content/about.html';
      if (file_exists($frag)) include $frag; else echo '<p>(нет содержимого)</p>';
    ?>
  </main>

  <footer class="site-footer">
    <div class="container">© СЗПП - 2025</div>
  </footer>
  </div>
</body>
</html>
