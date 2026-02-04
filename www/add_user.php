<?php

// Démarrage de la session
session_start();

// On vérifie que l'utilisateur est bien connecté
if(isset($_SESSION['pseudo']))
{
    // On l'accueille 
    echo "Bienvenuee" .$_SESSION['pseudo'];
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
} catch (PDOException $e) {
     echo "Erreur : " .$e->getMessage();
}
}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<title>Ajout</title>
</head>

<body>

  <h1>Ajouter un membre</h1>
  <form method="post">
  <input type="text" name="new_user" placeholder="Nom d'utilisateur">
  <input type="password" name="new_pass" placeholder="Mot de passe">
  <button type="submit">Ajouter</button>
  </form>

</body>
</html>
