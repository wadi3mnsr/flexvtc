<?php
// Démarrer la session (avant tout output)
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// CSRF helpers
function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}
function csrf_check(): bool {
  $ok = isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
  // on régénère pour éviter la réutilisation
  unset($_SESSION['csrf_token']);
  return $ok;
}

// Auth helpers
function is_admin_logged(): bool {
  return !empty($_SESSION['admin_id']);
}
function require_admin() {
  if (!is_admin_logged()) {
    header('Location: /admin/login.php');
    exit;
  }
}
