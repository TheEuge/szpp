<?php ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Союз Защиты Прав Потребителей - Контакт</title>
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
          <li><a href="about.php">О нас</a></li>
          <li><a href="szpp.php">СЗПП</a></li>
          <li><a href="contact.php" class="active">Контакт</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container">
    <?php
      // optional fragment (not created by default)
      $frag = __DIR__ . '/content/contact.html';
      if (file_exists($frag)) include $frag;
    ?>

    <section id="feedback">
      <h3>Форма обратной связи</h3>
      <form id="feedback-form" novalidate>
        <label for="name">Имя *</label>
        <input id="name" name="name" type="text" placeholder="Ваше имя" required>

        <label for="email">Email *</label>
        <input id="email" name="email" type="email" placeholder="you@example.com" required>

        <label for="subject">Тема</label>
        <input id="subject" name="subject" type="text" placeholder="Краткая тема">

        <label for="message">Сообщение *</label>
        <textarea id="message" name="message" rows="6" placeholder="Ваше сообщение" required></textarea>

        <button type="submit" class="btn">Отправить</button>
        <div id="form-message" class="form-message" aria-live="polite"></div>
      </form>

      <p class="form-note">Примечание: пока это локальная демонстрация — форма валидирует и показывает сообщение об успехе, но не отправляет реальное письмо. Чтобы получать сообщения на почту, подключите серверную обработку или сервис (например, Formspree) — см. комментарий в коде.</p>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">© СЗПП - 2025</div>
  </footer>
  </div>

  <script>
  (function(){
    const form = document.getElementById('feedback-form');
    const msg = document.getElementById('form-message');
    form.addEventListener('submit', function(e){
      e.preventDefault();
      msg.textContent = '';
      const name = form.name.value.trim();
      const email = form.email.value.trim();
      const message = form.message.value.trim();
      if(!name || !email || !message){
        msg.textContent = 'Пожалуйста, заполните обязательные поля.';
        msg.style.color = 'red';
        return;
      }
      if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
        msg.textContent = 'Введите корректный email.';
        msg.style.color = 'red';
        return;
      }
      form.reset();
      msg.textContent = 'Спасибо! Ваше сообщение отправлено (локальная демонстрация).';
      msg.style.color = 'green';
    });
  })();
  </script>
</body>
</html>
