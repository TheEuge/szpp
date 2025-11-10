<?php
// upload.php - simple image upload for admin users
require_once __DIR__ . '/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$maxBytes = 2 * 1024 * 1024; // 2 MB
$allowed = ['image/jpeg','image/png','image/gif','image/webp'];

if (empty($_FILES['image'])){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'no file']); exit; }
$f = $_FILES['image'];
if ($f['error'] !== UPLOAD_ERR_OK){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'upload error']); exit; }
if ($f['size'] > $maxBytes){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'too large']); exit; }
$mime = mime_content_type($f['tmp_name']);
if (!in_array($mime, $allowed)){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad type']); exit; }

$ext = '';
switch($mime){ case 'image/jpeg': $ext='jpg'; break; case 'image/png': $ext='png'; break; case 'image/gif': $ext='gif'; break; case 'image/webp': $ext='webp'; break; }
$uploads = __DIR__ . '/uploads'; if (!is_dir($uploads)) mkdir($uploads,0755,true);
$name = bin2hex(random_bytes(12)) . '.' . $ext;
$dest = $uploads . '/' . $name;
if (!move_uploaded_file($f['tmp_name'], $dest)) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'write failed']); exit; }

// Return the public URL (relative)
echo json_encode(['ok'=>true,'url'=>'/uploads/' . $name]);
