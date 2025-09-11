# FlexVTC — Starter Docker (PHP + MySQL)

## Démarrage rapide
```bash
docker compose up -d --build
# Site:       http://localhost:8080
# phpMyAdmin: http://localhost:8081 (user: flex_user / pass: flex_userpass)
```

## Import base de données
1. Ouvre phpMyAdmin (http://localhost:8081) — connexion `flex_user` / `flex_userpass`.
2. Va dans la base `flexvtc_db`.
3. Onglet **Importer** → sélectionne `database/schema.sql`.

## Prochaines étapes guidées
- Étape 1 ✅: structure + Docker OK
- Étape 2 ⏭: implémenter `/reservation_save.php` (insertion en table `reservations`)
- Étape 3: persister les **avis** et le **contact**
- Étape 4: **auth admin** (sessions), **dashboard**, suppression/modération avis
- Étape 5: validations serveur (filter_input, regex), sécurité (CSRF), pagination

## Accès
- Site: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- MySQL hôte: localhost:3307 (pour outil externe) — Base: `flexvtc_db` — User: `flex_user`

© 2025 FlexVTC (démo pédagogique)
