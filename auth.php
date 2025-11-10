<?php
// auth.php - simple session auth helper. Change username/password below.
if (session_status() === PHP_SESSION_NONE) session_start();

// Configure these credentials (change immediately)
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'adminpass';

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
