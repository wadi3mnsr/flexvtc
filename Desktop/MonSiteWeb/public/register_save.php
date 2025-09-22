<?php
require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

// 1) Honeypot anti-bot
if (!empty($_POST['website'] ?? '')) { http_response_code(204); exit; }

// 2) Récupération
$firstname = trim($_POST['firstname'] ?? '');
$lastname  = trim($_POST['lastname'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['password_confirm'] ?? '';
$terms     = !empty($_POST['terms']);

// 3) Validation
$errors = [];
if ($firstname==='') $errors[] = "Prénom obligatoire.";
if ($lastname==='')  $errors[] = "Nom obligatoire.";
if ($email==='' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
if ($phone==='')    $errors[] = "Téléphone obligatoire.";
if (strlen($password) < 8) $errors[] = "Mot de passe : 8 caractères minimum.";
if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";
if (!$terms) $errors[] = "Vous devez accepter les conditions.";

if ($errors) {
  include __DIR__ . '/includes/header.php';
  echo '<section class="container"><div class="card" style="margin-top:1.5rem">';
  echo '<h1>Erreur</h1><ul>'; foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; echo '</ul>';
  echo '<p><a class="btn" href="/register.php">Retour</a></p></div></section>';
  include __DIR__ . '/includes/footer.php';
  exit;
}

// 4) Vérification email unique
$check = $pdo->prepare("SELECT id FROM customers WHERE email = :email LIMIT 1");
$check->execute([':email' => $email]);
if ($check->fetch()) {
  include __DIR__ . '/includes/header.php';
  echo '<section class="container"><div class="card" style="margin-top:1.5rem">';
  echo '<h1>Adresse email déjà utilisée</h1>';
  echo '<p>Veuillez utiliser une autre adresse ou vous connecter si vous avez déjà un compte.</p>';
  echo '<p><a class="btn" href="/register.php">Retour</a></p></div></section>';
  include __DIR__ . '/includes/footer.php';
  exit;
}

// 5) Hachage + insertion
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO customers (firstname, lastname, email, phone, password_hash) 
                       VALUES (:fn, :ln, :em, :ph, :pw)");
$stmt->execute([
  ':fn' => $firstname,
  ':ln' => $lastname,
  ':em' => $email,
  ':ph' => $phone,
  ':pw' => $hash,
]);

// (Option) auto-login après inscription
$_SESSION['customer_id'] = (int)$pdo->lastInsertId();
$_SESSION['customer_name'] = $firstname;

// 6) Confirmation
include __DIR__ . '/includes/header.php';
?>
<section class="container">
  <div class="card" style="margin-top:1.5rem">
    <h1 class="mt-0">Compte créé ✅</h1>
    <p>Bienvenue <?= htmlspecialchars($firstname) ?>, votre compte a bien été créé.</p>
    <p><a class="btn btn-primary" href="/index.php">Aller à l’accueil</a></p>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
