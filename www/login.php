<?php
session_start();

require_once 'dbconnection.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    if (!empty($_POST['login']) && !empty($_POST['pass'])) {

    
   $stmt = $dbh->prepare("SELECT * FROM Utilisateurs WHERE nom_utilisateur = ?");

   $stmt->execute([$_POST['login']]);

   $user = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($user && password_verify($_POST['pass'], $user['MotDePasse'])) {

    $_SESSION['user_id'] = $user['idUtilisateurs'];
    $_SESSION['pseudo'] = $user['nom_utilsateur'];

    header("Location: index.php");
    exit();
   }
} else {

    $message = "Mauvais identifiant ou mot de passe !";
}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Connexion</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f9; }
        form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; }
        input { display: block; width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>

    <form method="POST">
        <h2>Connexion</h2>
        
        <?php if($message) { echo "<p class='error'>$message</p>"; } ?>
        
        <input type="text" name="login" placeholder="Nom d'utilisateur" required />
        <input type="password" name="pass" placeholder="Mot de passe" required />
        
        <button type="submit">Se connecter</button>
    </form>

</body>
</html>
