<?php
require_once __DIR__ . '/../lib/auth.php';
$me = current_client();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FlexVTC</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
  <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
</head>
<body class="site">
<header>
  <div class="container nav">
    <a href="/index.php" class="logo" style="font-weight:800;text-decoration:none;">
      Flex<span style="opacity:.7">VTC</span>
    </a>

    <nav id="primary-nav" aria-label="Navigation principale">
      <a href="/index.php">Accueil</a>
      <a href="/about.php">À propos</a>
      <a href="/reservation.php">Réservation</a>
      <a href="/avis.php">Avis</a>
      <a href="/contact.php">Contact</a>
    </nav>

    <div class="nav-actions" style="margin-left:auto;display:flex;gap:.5rem;">
      <?php if (is_client_logged()): ?>
        <a href="/account.php" class="btn btn-ghost">
          Bonjour <?= htmlspecialchars($me['firstname']) ?>
        </a>
        <a href="/logout.php" class="btn btn-danger">Déconnexion</a>
      <?php else: ?>
        <a href="/login.php" class="btn btn-ghost">Se connecter</a>
        <a href="/register.php" class="btn btn-primary">Créer un compte</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="container">
