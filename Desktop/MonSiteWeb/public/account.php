<?php
// account.php
require __DIR__ . '/lib/auth.php';
require_client_login();
$me = current_client();

include __DIR__ . '/includes/header.php';
?>
<main class="container" style="max-width:800px;margin:2rem auto;">
  <h1>Mon compte</h1>
  <p>Bonjour <strong><?= htmlspecialchars($me['firstname'] . ' ' . $me['lastname']) ?></strong> 👋</p>

  <ul>
    <li>Email : <?= htmlspecialchars($me['email']) ?></li>
    <li>Téléphone : <?= htmlspecialchars($me['phone'] ?? '—') ?></li>
    <li>Client depuis : <?= htmlspecialchars($me['created_at'] ?? '—') ?></li>
  </ul>

  <p style="margin-top:1rem;">
    <a class="btn btn-secondary" href="/logout.php">Se déconnecter</a>
  </p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
