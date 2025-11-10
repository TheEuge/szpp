<?php
// diagnostic helper - not for production
header('Content-Type: text/plain');
try{
  if (session_status() === PHP_SESSION_NONE) session_start();
  $sid_before = session_id();
  session_regenerate_id(true);
  $sid_after = session_id();
  echo "Session started. ID before: $sid_before\nID after regenerate: $sid_after\n";
  echo "Session save path: " . (session_save_path() ?: '(none)') . "\n";
  echo "Session status: " . session_status() . "\n";
}catch(Throwable $e){
  echo "Error: " . $e->getMessage() . "\n";
  echo $e->getTraceAsString();
}
