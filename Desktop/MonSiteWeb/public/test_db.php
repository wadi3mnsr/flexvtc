<?php
require __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    // Requête simple
    $stmt = $pdo->query("SELECT NOW() AS now, DATABASE() AS db");
    $row = $stmt->fetch();

    echo " Connexion réussie à la base PDO  avec Succès !\n";
    echo "Base de données : " . $row['db'] . "\n";
    echo "Date/heure MySQL : " . $row['now'] . "\n";
} catch (Exception $e) {
    echo " Erreur de connexion ou de requête PDO :\n";
    echo $e->getMessage();
}
