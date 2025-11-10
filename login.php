<?php
require_once __DIR__ . '/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if ($u === $ADMIN_USER && $p === $ADMIN_PASS){
        $_SESSION['user'] = $u;
        // regenerate session id
        session_regenerate_id(true);
        header('Location: admin.php'); exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Login</title></head><body>
<h1>Login</h1>
<?php if($error) echo '<p style="color:red">'.htmlspecialchars($error).'</p>'; ?>
<form method="post">
Username: <input name="username"><br>
Password: <input name="password" type="password"><br>
<button type="submit">Login</button>
</form>
</body></html>
