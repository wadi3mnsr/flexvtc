<?php include __DIR__ . '/includes/header.php'; ?>


<h1>Avis</h1>
<div id="reviews" class="reviews-list" aria-live="polite"></div>
<h2>Laisser un avis</h2>
<form id="review-form" class="form-grid" novalidate>
  <label>Votre nom<input type="text" name="name" required></label>
  <label>Note
    <select name="rating" required>
      <option value="5">★★★★★</option>
      <option value="4">★★★★☆</option>
      <option value="3">★★★☆☆</option>
      <option value="2">★★☆☆☆</option>
      <option value="1">★☆☆☆☆</option>
    </select>
  </label>
  <label>Commentaire<textarea name="comment" rows="3" required></textarea></label>
  <button class="btn btn-primary" type="submit">Publier</button>
  <p class="small">Démo locale (stocké côté navigateur). Nous le brancherons à MySQL ensuite.</p>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
