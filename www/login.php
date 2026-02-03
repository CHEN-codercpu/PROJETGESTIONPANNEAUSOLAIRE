<?php
session_start(); 
require_once 'dbconnection.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // On vérifie que les champs sont remplis
    if (!empty($_POST['login']) && !empty($_POST['pass'])) {
        
        // On prépare la requête pour chercher l'utilisateur
        $stmt = $dbh->prepare("SELECT * FROM Utilisateurs WHERE nom_utilisateur = ?");
        $stmt->execute([ $_POST['login'] ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // On vérifie le mot de passe
        if ($user && password_verify($_POST['pass'], $user['MotDePasse'])) {
            
            // --- SUCCÈS ---
            // On enregistre l'utilisateur dans la session
            $_SESSION['user_id'] = $user['idUtilisateurs'];
            $_SESSION['pseudo'] = $user['nom_utilisateur'];
            
            // HOP ! On redirige vers le tableau de bord
            header("Location: index.php");
            exit();
            
        } else {
	    sleep(1);

            $message = "Mauvais identifiant ou mot de passe !";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Solaire</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #e9ecef; margin: 0; }
        form { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
        h2 { color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 16px; }
        button { background-color: #28a745; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 10px; transition: background 0.3s; }
        button:hover { background-color: #218838; }
        .error { color: #dc3545; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>

    <form method="POST">
        <h2>Espace Connexion</h2>
        
        <?php if($message) { echo "<p class='error'>$message</p>"; } ?>
        
        <input type="text" name="login" placeholder="Nom d'utilisateur" required />
        <input type="password" name="pass" placeholder="Mot de passe" required />
        
        <button type="submit">Se connecter</button>
    </form>

</body>
</html>
