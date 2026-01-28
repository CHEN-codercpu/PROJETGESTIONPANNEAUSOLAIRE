<?php

include ('dbconnection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    var_export($_POST);
   $result = $dbh->query("SELECT MotDePasse FROM Utilisateurs WHERE nom_utilisateur = " . $_POST['login']);
   foreach ($result as $row)
   {
    echo $row["MotDePasse"];
   }
}
else  
{
    ?>
<!DOCTYPE html>
<html lang="fr"> <body>

    <form method="POST"> <input type="text" name="login" placeholder="Nom d'utilisateur" />
        <input type="password" name="pass" placeholder="Mot de passe" />
        <button name="access" type="submit">Accéder au site</button>
      
    </form>

</body>
</html>
<?php
}

?>

