<?php
// 1. On appelle la connexion à la BDD (ton fichier dbconnection.php doit être dans le même dossier)
require_once 'dbconnection.php';

// 2. On récupère le "colis" brut envoyé par TTN (format JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// ... (après le json_decode)

// TEST FORCE : On ignore la vérification de TTN pour l'instant
$tension_test = 12.5; 
$courant_test = 0.5;

try {
    // On utilise nos variables de test directement ici :
    $stmt = $dbh->prepare("INSERT INTO Mesures (tension_panneau, courant_panneau, date_heure) VALUES (?, ?, NOW())");
    $stmt->execute([$tension_test, $courant_test]);

    echo "SUCCÈS : La base de données a reçu le message simulé !";
} catch (Exception $e) {
    echo "Erreur BDD : " . $e->getMessage();
}
?>
