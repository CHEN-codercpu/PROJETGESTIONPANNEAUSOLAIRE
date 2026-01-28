<?php
global $dbh;
// On récupère les valeurs envoyées par Docker
// Attention : 'DB_HOST' doit correspondre exactement au nom dans docker-compose
$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');
// Sécurité : On vérifie si une des variables est vide
if (!$servername || !$username || !$password || !$dbname) {
    die("Erreur critique : Variables d'environnement manquantes !");
}

try {
$dbh = new PDO("mysql:host=$servername;dbname=$dbname" , $username , $password);

# S'il y a une erreur on va la "catch"
} catch (PDOException $e) {
    # le "die" tue le script et affiche le message d'erreur à l'écran
    die("Erreur " . $e->getMessage());
}
?>