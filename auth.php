<?php
// auth.php - session auth helper. Credentials are read from environment variables.
// For showcase/development mode we disable authorization and CSRF.
// These functions are intentionally permissive so the admin UI works
// without credentials. DO NOT use this in production.
function verify_admin_password($password){ return true; }
function is_logged_in(){ return true; }
function require_login(){ return; }
function generate_csrf(){ return 'dev-csrf-token'; }
