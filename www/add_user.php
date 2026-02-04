<?php

// Démarrage de la session
session_start();

require_once 'dbconnection.php';

$message = "";
// On vérifie que l'utilisateur est bien connecté
if(isset($_SESSION['pseudo']))
{
    // On l'accueille 
    echo "Bienvenue " .$_SESSION['pseudo'];
}
else 
{
    // S'il n'est pas connecté ou qu'il est inconnu à la DB on le redirigie vers la page login.php
    header('Location: login.php');
    exit(0);
}

// On vérifie s'il à le rôle Administrateur
if($_SESSION['role'] == 'Administrateur')
{

}
else
{
   // Sinon message d'erreur, il sort
   exit('Vous ne disposez pas des droits');
}

// Vérifie si la méthode utilisée par le serveur est "POST"
if ($_SERVER["REQUEST_METHOD"] == "POST") {

// Vérifie si les champs $_POST['new_user'] et $_POST['new_pass'] ne sont pas vides
if (!empty($_POST['new_user']) && !empty($_POST['new_pass'])) {

//Création de la fonction qui va crée automatiquement le hash du MotDePasse que j'écris
$hash = password_hash($_POST['new_pass'] , PASSWORD_DEFAULT);

// On envoie toutes les nouvelles données vers la base de données
try {
$stmt = $dbh->prepare("INSERT INTO Utilisateurs (nom_utilisateur , MotDePasse, role) Values( ?, ?, 'Utilisateur')");
$stmt->execute([$_POST['new_user'] , $hash]);
$message = "✅ Utilisateur ajouté avec succès !";
} catch (PDOException $e) {
     echo "Erreur : " .$e->getMessage();
}
}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<title>Page d'ajout de Membre</title>
<style>
    /* Le fond Ciel (Le même que ton dashboard) */
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to bottom, #2980b9, #6dd5fa); /* Ciel bleu */
        height: 100vh; /* Prend toute la hauteur de l'écran */
        display: flex; /* Active le mode Flexbox */
        justify-content: center; /* Centre horizontalement */
        align-items: center; /* Centre verticalement */
    }

    /* Le bouton Retour en haut à gauche */
    .back-btn {
        position: absolute;
        top: 20px;
        left: 20px;
        text-decoration: none;
        color: white;
        font-weight: bold;
        background: rgba(0,0,0,0.2);
        padding: 10px 20px;
        border-radius: 20px;
        transition: 0.3s;
    }
    .back-btn:hover { background: rgba(0,0,0,0.4); }

    /* La Carte Blanche au milieu */
    .card {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2); /* Ombre portée */
        width: 300px;
        text-align: center;
    }

    h2 { color: #333; margin-bottom: 20px; }

    /* Les champs de texte */
    input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box; /* Pour que le padding ne casse pas la largeur */
    }

    /* Le bouton Ajouter */
    button {
        width: 100%;
        padding: 12px;
        background-color: #e67e22; /* Orange */
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
    }
    button:hover { background-color: #d35400; }

    /* Le message de succès (Vert) */
    .success-msg {
        background-color: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #c3e6cb;
        margin-bottom: 15px;
    }
</style>
</head>

<body>
<a href="index.php" class="back-btn">⬅ Retour au Dashboard</a>

<div class="container">

    <div class="card">
        <h2>Ajouter un membre</h2>

        <?php if($message != ""): ?>
            <p class="success-msg"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="new_user" placeholder="Nom d'utilisateur" required>
            <input type="password" name="new_pass" placeholder="Mot de passe" required>
            <button type="submit">Ajouter l'utilisateur</button>
        </form>

    </div>
</div>
</body>
</html>
