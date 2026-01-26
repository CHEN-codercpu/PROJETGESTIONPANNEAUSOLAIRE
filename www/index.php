<?php
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
$dbh = new PDO('mysql:host=$servername;dbname=$dbname' , $username , $password);

} catch (PDOException $e) {
    return 'catch';
}
$sth = $dbh->query('SELECT * FROM Mesures idMesures ORDER BY DESC LIMIT 1');

$mesure = $sth->fetch((PDO::FETCH_ASSOC));

$courant_p = $mesure['courant_panneau'];
$tension_b = $mesure['tension_batterie'];
$temp_b = $mesure['temp_batterie'];
?>