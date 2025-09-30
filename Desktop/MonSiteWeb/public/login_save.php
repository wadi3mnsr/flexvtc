<?php
// /var/www/html/login_save.php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/login.php');
}

if (!csrf_check()) {
  flash_set('error', 'Session expirée. Veuillez réessayer.');
  redirect('/login.php');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
if ($password === '') $errors[] = 'Mot de passe requis.';

if ($errors) {
  foreach ($errors as $e) flash_set('error', $e);
  redirect('/login.php');
}

try {
  $stmt = $pdo->prepare("SELECT id, firstname, lastname, email, password_hash FROM clients WHERE email = :em LIMIT 1");
  $stmt->execute([':em' => $email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user || !password_verify($password, $user['password_hash'])) {
    flash_set('error', 'Identifiants incorrects.');
    redirect('/login.php');
  }

  session_regenerate_id(true);
  client_login($user);

  if (function_exists('after_login_redirect_or')) {
    after_login_redirect_or('/index.php');
  }
  redirect('/index.php');

} catch (Throwable $e) {
  flash_set('error', 'Erreur serveur. Veuillez réessayer.');
  redirect('/login.php');
}
