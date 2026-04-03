<?php
// 1. On appelle la connexion à la BDD (ton fichier dbconnection.php doit être dans le même dossier)
require_once 'dbconnection.php';

// 2. On récupère le "colis" brut envoyé par TTN (format JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 3. LOGIQUE : On vérifie si le colis contient bien des données de capteurs
if (isset($data['uplink_message']['decoded_payload'])) {
    
    $payload = $data['uplink_message']['decoded_payload'];

    // On extrait les valeurs (Assure-toi que ces noms correspondent à ceux de tes potes !)
    $tension = $payload['tension'] ?? 0; 
    $courant = $payload['courant'] ?? 0;

    // 4. On prépare la commande pour ranger ça dans la base de données
    try {
        $stmt = $dbh->prepare("INSERT INTO Mesures (tension_panneau, courant_panneau, date_heure) VALUES (?, ?, NOW())");
        $stmt->execute([$tension, $courant]);
        
        // Petit message pour confirmer dans les logs de Ngrok
        echo "Donnée enregistrée avec succès !";
    } catch (Exception $e) {
        echo "Erreur BDD : " . $e->getMessage();
    }
} else {
    echo "Pas de données capteurs détectées.";
}
?>
