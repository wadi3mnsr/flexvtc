<?php include __DIR__ . '/includes/header.php'; ?>


<h1>Contact</h1>
<p>Écrivez‑nous pour un devis sur‑mesure.</p>
<form class="form-grid" method="post" action="/contact_send.php" novalidate>
  <div class="grid-2">
    <label>Prénom<input type="text" name="firstname" required></label>
    <label>Nom<input type="text" name="lastname" required></label>
  </div>
  <label>Email<input type="email" name="email" required></label>
  <label>Message<textarea name="message" rows="5" required></textarea></label>
  <button class="btn btn-primary">Envoyer</button>
  <p class="small">Prochaine étape : persister le message en base et afficher un accusé.</p>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
