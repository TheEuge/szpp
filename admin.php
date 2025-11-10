<?php
// Robust startup logging for admin.php to capture errors on remote hosts (Render)
@mkdir(__DIR__ . '/logs', 0755, true);
$__debug_file = __DIR__ . '/logs/debug-admin.log';
ini_set('display_errors', '0');
error_reporting(E_ALL);
register_shutdown_function(function() use ($__debug_file){
  $err = error_get_last();
  if ($err){
    $msg = date('c') . " SHUTDOWN: " . $err['message'] . " in " . ($err['file'] ?? '') . ":" . ($err['line'] ?? '') . "\n";
    file_put_contents($__debug_file, $msg, FILE_APPEND);
  }
});
set_exception_handler(function($e) use ($__debug_file){
  $msg = date('c') . " EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n";
  file_put_contents($__debug_file, $msg, FILE_APPEND);
  http_response_code(500);
  echo "Server error (see logs/debug-admin.log)";
  exit;
});

try{
  // showcase mode: admin/login removed so this page loads for everyone
  $csrf = null;
} catch (Throwable $e){
  file_put_contents($__debug_file, date('c') . " STARTUP-ERROR: " . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
  http_response_code(500);
  echo "Server error during startup (see logs/debug-admin.log)";
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Editor</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;padding:20px}
    /* editable area: allow scrolling for large content and optional vertical resize */
    #editor{
      min-height:300px;
      max-height:60vh;
      overflow:auto;
      border:1px solid #ccc;
      padding:12px;
      background:#fff;
      box-sizing:border-box;
      resize:vertical;
      white-space:normal;
      word-wrap:break-word;
    }
    /* improve focus visibility */
    #editor:focus{outline:2px solid rgba(11,116,222,0.25)}
  </style>
</head>
<body>
  <h1>Простой редактор</h1>
  <p>Выберите страницу, отредактируйте и нажмите Сохранить. <a href="logout.php">Logout</a></p>

  <label for="page">Страница: </label>
  <select id="page">
    <option value="home">Главная</option>
    <option value="szpp">СЗПП</option>
  <option value="about">О нас</option>
  <option value="contact">Контакт</option>
  </select>

  <div id="editor" contenteditable="true"></div>

  <div style="margin-top:8px">
    <button id="load">Загрузить</button>
    <button id="save">Сохранить</button>
    <span id="status"></span>
  </div>

  <hr style="margin:16px 0">
  <div id="image-uploader" style="margin-top:8px">
    <label for="imagefile">Вставить изображение:</label>
    <input id="imagefile" type="file" accept="image/*">
    <button id="uploadImage">Загрузить и вставить</button>
    <span id="imgstatus" style="margin-left:8px"></span>
  </div>

  <script>
  const CSRF = null;
    const editor = document.getElementById('editor');
    const status = document.getElementById('status');

    document.getElementById('load').addEventListener('click', async () => {
      const page = document.getElementById('page').value;
      const r = await fetch('/content/' + page + '.html');
      if (r.ok) editor.innerHTML = await r.text(); else editor.innerHTML = '<p>(нет содержимого)</p>';
    });

    document.getElementById('save').addEventListener('click', async () => {
      const page = document.getElementById('page').value;
      const html = editor.innerHTML;
      status.textContent = 'Saving...';
      try{
        const res = await fetch('/save.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ page, html })
        });
        const j = await res.json();
        status.textContent = j.ok ? 'Saved' : 'Error: ' + j.error;
      }catch(e){ status.textContent = 'Network error'; }
    });

    // Image upload flow: POST FormData to /upload.php and insert <img> at caret
    const imgInput = document.getElementById('imagefile');
    const uploadBtn = document.getElementById('uploadImage');
    const imgStatus = document.getElementById('imgstatus');

    function insertImageAtCaret(url){
      editor.focus();
      const sel = window.getSelection();
      if (!sel || sel.rangeCount === 0) {
        // append at end
        const img = document.createElement('img'); img.src = url; img.style.maxWidth = '100%';
        editor.appendChild(img);
        return;
      }
      const range = sel.getRangeAt(0);
      // Create image node
      const img = document.createElement('img'); img.src = url; img.style.maxWidth = '100%';
      // Insert a space after image for easier typing
      const space = document.createTextNode('\u00A0');
      range.deleteContents();
      range.insertNode(space);
      range.insertNode(img);
      // Move caret after the space
      range.setStartAfter(space);
      range.collapse(true);
      sel.removeAllRanges(); sel.addRange(range);
    }

    uploadBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      imgStatus.textContent = '';
      const f = imgInput.files && imgInput.files[0];
      if (!f){ imgStatus.textContent = 'Нет файла'; return; }
      const fd = new FormData(); fd.append('image', f);
      imgStatus.textContent = 'Uploading...';
      try{
        const res = await fetch('/upload.php', { method: 'POST', credentials: 'same-origin', body: fd });
        const j = await res.json();
        if (!j.ok){ imgStatus.textContent = 'Ошибка: ' + (j.error || 'unknown'); return; }
        insertImageAtCaret(j.url);
        imgStatus.textContent = 'Вставлено';
        // clear input
        imgInput.value = '';
      }catch(err){ imgStatus.textContent = 'Network error'; }
    });
  </script>
</body>
</html>
