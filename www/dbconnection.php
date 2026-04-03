<?php
global $dbh;

// Récupération des variables d'environnement
$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

// Vérification de sécurité
if (!$servername || !$username || !$password || !$dbname) {
    die("Erreur critique : Variables d'environnement manquantes !");
}

try {
    $dbh = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // On active les erreurs SQL pour voir les problèmes
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

