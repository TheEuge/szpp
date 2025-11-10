<?php
// auth.php - session auth helper. Credentials are read from environment variables.
if (session_status() === PHP_SESSION_NONE) session_start();

// Admin username: from env ADMIN_USER or default 'admin'
$ADMIN_USER = getenv('ADMIN_USER') ?: 'admin';

// Password handling:
// - If ADMIN_PASS_HASH is set, verify with password_verify()
// - Else if ADMIN_PASS is set, compare plaintext (legacy)
// - Else fallback to 'adminpass' for local/dev convenience (change immediately)
$ADMIN_PASS_HASH = getenv('ADMIN_PASS_HASH') ?: null;
$ADMIN_PASS_PLAIN = getenv('ADMIN_PASS') ?: null;
// Do NOT fallback to any default password. Require explicit environment
// configuration to avoid accidental use of weak defaults.

function verify_admin_password($password){
    global $ADMIN_PASS_HASH, $ADMIN_PASS_PLAIN;
    if (!empty($ADMIN_PASS_HASH)){
        return password_verify($password, $ADMIN_PASS_HASH);
    }
    if (!empty($ADMIN_PASS_PLAIN)){
        // use hash_equals to avoid timing attacks
        return hash_equals((string)$ADMIN_PASS_PLAIN, (string)$password);
    }
    return false;
}

function is_logged_in(){
    return !empty($_SESSION['user']) && $_SESSION['user'] === $GLOBALS['ADMIN_USER'];
}

function require_login(){
    if (!is_logged_in()){
        header('Location: login.php');
        exit;
    }
}

function generate_csrf(){
    if (empty($_SESSION['csrf'])){
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}
