<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero hero-reservation">
  <div class="container">
    <h1>Réserver votre trajet</h1>
    <p class="lead">Indiquez votre départ et votre arrivée, l’itinéraire et le prix s’affichent.</p>
  </div>
</section>

<section class="container">
  <form id="booking-form" class="form-grid" method="post" action="reservation_save.php" novalidate>
    <fieldset class="form-grid">
      <legend>Votre trajet</legend>

      <label>Adresse de départ
        <input type="text" name="from" id="from" required placeholder="Ex : 1 Rue de Strasbourg, Nantes">
      </label>

      <label>Adresse d’arrivée
        <input type="text" name="to" id="to" required placeholder="Ex : Aéroport Nantes Atlantique">
      </label>

      <div class="grid-2">
        <label>Date
          <input type="date" name="date" required>
        </label>
        <label>Heure
          <input type="time" name="time" required>
        </label>
      </div>

      <label>Options
        <select name="option" id="option">
          <option value="">Aucune</option>
          <option value="seat">Siège enfant (+3€)</option>
          <option value="van">Van (+15€)</option>
          <option value="xl">Bagage XL (+5€)</option>
        </select>
      </label>

      <div class="grid-2">
        <label>Prénom
          <input type="text" name="firstname" required>
        </label>
        <label>Nom
          <input type="text" name="lastname" required>
        </label>
      </div>
      <div class="grid-2">
        <label>Téléphone
          <input type="tel" name="phone" required>
        </label>
        <label>Email
          <input type="email" name="email" required>
        </label>
      </div>
      <label>Commentaires
        <textarea name="notes" rows="3" placeholder="Infos vol, digicode, bagages, etc."></textarea>
      </label>

      <!-- Champs cachés remplis par le calcul (pour sauvegarder en base) -->
      <input type="hidden" name="distance" id="distance_hidden">
      <input type="hidden" name="duration" id="duration_hidden">
      <input type="hidden" name="price_estimate" id="price_hidden">
      <input type="hidden" name="from_lat" id="from_lat">
      <input type="hidden" name="from_lng" id="from_lng">
      <input type="hidden" name="to_lat" id="to_lat">
      <input type="hidden" name="to_lng" id="to_lng">
    </fieldset>

    <!-- Carte -->
    <div class="form-grid">
      <label>Carte</label>
      <div id="map" aria-label="Carte de réservation"></div>

      <div class="grid-2" style="align-items:center;">
        <button class="btn" type="button" id="route-btn">Tracer l’itinéraire & calculer</button>

        <!-- Résultats -->
        <div class="route-results" id="route-results" hidden>
          <div class="stat">
            <span class="label">Distance</span>
            <span class="value" id="stat-distance">—</span>
          </div>
          <div class="stat">
            <span class="label">Durée</span>
            <span class="value" id="stat-duration">—</span>
          </div>
          <div class="stat">
            <span class="label">Prix estimé</span>
            <span class="value" id="stat-price">—</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid-2">
      <button class="btn btn-primary" type="submit">Envoyer ma réservation</button>
      <button class="btn" type="reset">Annuler</button>
    </div>

    <p class="form-hint">
      Entrez les adresses puis cliquez « Tracer l’itinéraire & calculer ». Vous pouvez ajuster les options pour mettre à jour le prix.
    </p>
  </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
