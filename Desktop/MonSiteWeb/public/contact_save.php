<?php
require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

$firstname = trim($_POST['firstname'] ?? '');
$lastname  = trim($_POST['lastname'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$message   = trim($_POST['message'] ?? '');

$errors = [];
if($firstname==='' || $lastname==='' || $email==='' || $phone==='' || $message===''){
  $errors[] = "Tous les champs sont obligatoires.";
}
if($email && !filter_var($email, FILTER_VALIDATE_EMAIL)){
  $errors[] = "Email invalide.";
}

if($errors){
  http_response_code(400);
  include __DIR__ . '/../includes/header.php';
  echo '<section class="container"><div class="card" style="margin-top:1.5rem">';
  echo '<h1>Erreur</h1><ul>'; foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; echo '</ul>';
  echo '<p><a class="btn" href="/contact.php">Retour</a></p></div></section>';
  include __DIR__ . '/../includes/footer.php';
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO contacts (firstname, lastname, email, phone, message)
  VALUES (:firstname, :lastname, :email, :phone, :message)
");
$stmt->execute([
  ':firstname' => $firstname,
  ':lastname'  => $lastname,
  ':email'     => $email,
  ':phone'     => $phone,
  ':message'   => $message,
]);

include __DIR__ . '/../includes/header.php';
?>
<section class="container">
  <div class="card" style="margin-top:1.5rem">
    <h1 class="mt-0">Message envoyé ✅</h1>
    <p>Merci, nous reviendrons vers vous rapidement.</p>
    <p><a class="btn btn-primary" href="/index.php">Retour à l’accueil</a></p>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
