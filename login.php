<?php
$display_err = true;
if ($display_err){
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}
require_once __DIR__ . '/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// capture fatal errors on shutdown
@mkdir(__DIR__ . '/logs', 0755, true);
register_shutdown_function(function(){
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])){
        $msg = date('c') . " - shutdown error: " . $err['message'] . " in " . $err['file'] . ":" . $err['line'] . "\n";
        file_put_contents(__DIR__ . '/logs/debug.log', $msg, FILE_APPEND);
    }
});
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    try{
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';
        if ($u === $ADMIN_USER && verify_admin_password($p)){
            $_SESSION['user'] = $u;
                // regenerate session id (suppress errors if environment disallows)
                if (function_exists('session_regenerate_id')){
                    $ok = @session_regenerate_id(true);
                    if ($ok === false){
                        @file_put_contents(__DIR__ . '/logs/debug.log', date('c') . " - session_regenerate_id returned false\n", FILE_APPEND);
                    }
                }
                header('Location: admin.php'); exit;
        } else {
            $error = 'Invalid credentials';
        }
    }catch(Throwable $e){
        // ensure logs dir exists
        @mkdir(__DIR__ . '/logs', 0755, true);
        file_put_contents(__DIR__ . '/logs/debug.log', date('c') . " - login error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
        http_response_code(500);
        echo 'Server error; check logs/debug.log';
        exit;
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
