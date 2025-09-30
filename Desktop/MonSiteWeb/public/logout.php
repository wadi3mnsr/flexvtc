<?php
// /var/www/html/logout.php
declare(strict_types=1);

require __DIR__ . '/config/app.php';

if (is_client_logged()) {
  client_logout();
  flash_set('success', 'Vous êtes bien déconnecté.');
}
redirect('/index.php');
