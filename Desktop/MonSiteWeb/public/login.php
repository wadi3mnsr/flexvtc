<?php
// /var/www/html/login.php
declare(strict_types=1);

require __DIR__ . '/config/app.php';

if (is_client_logged()) {
  redirect('/index.php');
}

$flashes = function_exists('flash_get_all') ? flash_get_all() : [];
include __DIR__ . '/includes/header.php';
?>
<section class="container" style="max-width:520px;">
  <h1 class="mt-0">Se connecter</h1>

  <?php if (!empty($flashes)): ?>
    <?php foreach ($flashes as $type => $msgs): foreach ($msgs as $msg): ?>
      <div class="card" style="border-left:4px solid <?= $type==='error' ? '#d33' : ($type==='success' ? '#2d8a34' : '#0070f3') ?>;">
        <p class="small" style="margin:0; padding-left:1rem;"><?= htmlspecialchars($msg, ENT_QUOTES) ?></p>
      </div>
    <?php endforeach; endforeach; ?>
  <?php endif; ?>

  <form method="post" action="/login_save.php" class="card">
    <?= csrf_field() ?>

    <label>Email
      <input type="email" name="email" required autocomplete="email">
    </label>

    <label>Mot de passe
      <input type="password" name="password" required autocomplete="current-password">
    </label>

    <button class="btn btn-primary" type="submit">Connexion</button>
    <p class="small" style="margin-top:.75rem;">
      Pas de compte ? <a href="/register.php">Créer un compte</a>
    </p>
  </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
