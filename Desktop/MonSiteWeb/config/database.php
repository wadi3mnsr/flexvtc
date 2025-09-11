<?php
// PDO connection for Dockerized MySQL
$host = "db";              // service name from docker-compose
$dbname = "flexvtc_db";
$username = "flex_user";
$password = "flex_userpass";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  die("Erreur connexion DB: " . htmlspecialchars($e->getMessage()));
}
