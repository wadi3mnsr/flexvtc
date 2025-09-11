<?php
require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

// Récupération / nettoyage
$from      = trim($_POST['from'] ?? '');
$to        = trim($_POST['to'] ?? '');
$date      = $_POST['date'] ?? '';
$time      = $_POST['time'] ?? '';
$option    = $_POST['option'] ?? '';

$firstname = trim($_POST['firstname'] ?? '');
$lastname  = trim($_POST['lastname'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$email     = trim($_POST['email'] ?? '');
$notes     = trim($_POST['notes'] ?? '');

$distance  = $_POST['distance'] ?? null;
$duration  = $_POST['duration'] ?? null;
$price     = $_POST['price_estimate'] ?? null;

$from_lat  = $_POST['from_lat'] ?? null;
$from_lng  = $_POST['from_lng'] ?? null;
$to_lat    = $_POST['to_lat'] ?? null;
$to_lng    = $_POST['to_lng'] ?? null;

// Validation serveur
$errors = [];
if ($from === '' || $to === '') $errors[] = "Adresses départ et arrivée obligatoires.";
if ($date === '' || $time === '') $errors[] = "Date et heure obligatoires.";
if ($firstname === '' || $lastname === '' || $phone === '' || $email === '') $errors[] = "Coordonnées obligatoires.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

$ride_datetime = date('Y-m-d H:i:s', strtotime("$date $time"));

$distance_km  = is_numeric($distance) ? round((float)$distance, 2) : null;
$duration_min = is_numeric($duration) ? (int)$duration : null;
$price_eur    = is_numeric($price)    ? round((float)$price, 2) : null;

$start_lat = is_numeric($from_lat) ? (float)$from_lat : null;
$start_lng = is_numeric($from_lng) ? (float)$from_lng : null;
$end_lat   = is_numeric($to_lat)   ? (float)$to_lat   : null;
$end_lng   = is_numeric($to_lng)   ? (float)$to_lng   : null;

if ($errors) {
  http_response_code(400);
  include __DIR__ . '/includes/header.php';
  echo '<section class="container"><div class="card" style="margin-top:1.5rem">';
  echo '<h1 class="mt-0">Erreur</h1><ul>';
  foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>';
  echo '</ul><p><a class="btn" href="/reservation.php">Retour</a></p></div></section>';
  include __DIR__ . '/includes/footer.php';
  exit;
}

// Insertion
$stmt = $pdo->prepare("
  INSERT INTO reservations
    (firstname, lastname, phone, email,
     start_address, end_address, start_lat, start_lng, end_lat, end_lng,
     ride_datetime, distance_km, duration_min, price_eur,
     option_code, notes, status)
  VALUES
    (:firstname, :lastname, :phone, :email,
     :start_address, :end_address, :start_lat, :start_lng, :end_lat, :end_lng,
     :ride_datetime, :distance_km, :duration_min, :price_eur,
     :option_code, :notes, 'new')
");

$stmt->execute([
  ':firstname'     => $firstname,
  ':lastname'      => $lastname,
  ':phone'         => $phone,
  ':email'         => $email,
  ':start_address' => $from,
  ':end_address'   => $to,
  ':start_lat'     => $start_lat,
  ':start_lng'     => $start_lng,
  ':end_lat'       => $end_lat,
  ':end_lng'       => $end_lng,
  ':ride_datetime' => $ride_datetime,
  ':distance_km'   => $distance_km,
  ':duration_min'  => $duration_min,
  ':price_eur'     => $price_eur,
  ':option_code'   => $option ?: null,
  ':notes'         => $notes ?: null,
]);

$id = (int)$pdo->lastInsertId();

// === Envoi d'e-mails via MailHog ===
require_once __DIR__ . '/lib/send_smtp.php';

// Client
$clientSubject = "Votre demande de réservation #$id – FlexVTC";
$clientBody = "<p>Bonjour ".htmlspecialchars($firstname).",</p>
<p>Merci pour votre demande. Voici votre récapitulatif :</p>
<ul>
  <li><strong>Trajet :</strong> ".htmlspecialchars($from)." → ".htmlspecialchars($to)."</li>
  <li><strong>Date :</strong> ".htmlspecialchars(date('d/m/Y H:i', strtotime($ride_datetime)))."</li>"
  . ($price_eur !== null ? "<li><strong>Prix estimé :</strong> ".number_format($price_eur,2,',',' ')." €</li>" : "") .
"</ul>
<p>Nous reviendrons vers vous pour confirmation.</p>
<p>— FlexVTC</p>";
@send_smtp_basic($email, $clientSubject, $clientBody);

// Gérant
$adminSubject = "Nouvelle réservation #$id";
$adminBody = "<p>Nouvelle demande :</p>
<ul>
  <li><strong>Client :</strong> ".htmlspecialchars($firstname.' '.$lastname)." (".htmlspecialchars($phone).", ".htmlspecialchars($email).")</li>
  <li><strong>Trajet :</strong> ".htmlspecialchars($from)." → ".htmlspecialchars($to)."</li>
  <li><strong>Date :</strong> ".htmlspecialchars($ride_datetime)."</li>
  <li><strong>Distance/Durée/Prix :</strong> "
    . (($distance_km!==null)? number_format($distance_km,2,',',' ')." km" : "—")
    . " / "
    . (($duration_min!==null)? (int)$duration_min." min" : "—")
    . " / "
    . (($price_eur!==null)? number_format($price_eur,2,',',' ')." €" : "—")
  . "</li>
</ul>";
@send_smtp_basic('gerant@flexvtc.local', $adminSubject, $adminBody);

// Page confirmation
include __DIR__ . '/includes/header.php';
?>
<section class="container">
  <div class="card" style="margin-top:1.5rem">
    <h1 class="mt-0">Réservation envoyée ✅</h1>
    <p>Numéro de demande : <strong>#<?= $id ?></strong></p>
    <p>Merci <?= htmlspecialchars($firstname) ?>, un e-mail de confirmation vous a été envoyé.</p>
    <hr>
    <p class="small">
      <strong>Trajet :</strong> <?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?><br>
      <strong>Quand :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($ride_datetime))) ?><br>
      <?php if($distance_km!==null): ?>Distance : <?= number_format($distance_km, 2, ',', ' ') ?> km — <?php endif; ?>
      <?php if($duration_min!==null): ?>Durée : <?= (int)$duration_min ?> min — <?php endif; ?>
      <?php if($price_eur!==null): ?>Prix estimé : <?= number_format($price_eur, 2, ',', ' ') ?> €<?php endif; ?>
    </p>
    <p>
      <a class="btn" href="/reservation.php">Nouvelle réservation</a>
      <a class="btn btn-primary" href="/index.php">Retour Accueil</a>
    </p>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
