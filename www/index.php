<?php
include ('dbconnection.php');



$tension_b =0;
$temp_b = 0;
$courant_p = 0;
$tension_p = 0;
$lux = "En attente";
$date = "En attente";



$sth = $dbh->query('SELECT * FROM Mesures ORDER BY idMesures DESC LIMIT 1');

$mesure = $sth->fetch((PDO::FETCH_ASSOC));

if ($mesure)
{
$tension_p = $mesure['tension_panneau'];
$courant_p = $mesure['courant_panneau'];
$tension_b = $mesure['tension_batterie'];
$temp_b = $mesure['temp_batterie'];
$lux = $mesure['eclairement'];
$date = $mesure['date_heure'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Projet</title>
    
    <style>
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            /* Pour centrer horizontalement les cartes dans la page : */
            justify-content: center; 
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 30px;
            width: 200px;
            /* Un peu d'ombre pour faire joli (optionnel) */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        body {
            background-color: #eee;
            /* Pour centrer le texte et utiliser une belle police : */
            font-family: Arial, sans-serif;
            text-align: center;
        }
    </style> </head>

<body>
    <div class="dashboard">
    
    <div class="card">
        <div class="titre">Tension Panneau</div>
        <div class="valeur"><?php echo $tension_p; ?> <span class="unite">V</span></div>
</div>
    <div class="card">
        <div class="titre">Courant Panneau</div>
        <div class="valeur"><?php echo $courant_p; ?> <span class="unite">A</span></div>

</div>

    <div class="card">
    <div class="titre">Tension Batterie</div>
    <div class="valeur"><?php echo $tension_b; ?> <span class="unite">V</span></div>

</div>

    <div class="card">
    <div class="titre">Température Batterie</div><div class="valeur"><?php echo $temp_b; ?> <span class="unite">°C</span></div>

</div>

      <div class="card">
    <div class="titre">Eclairement</div>
    <div class="valeur"><?php echo $lux; ?>  <span class="unite">lx</span></div>

</div>

      <div class="card">
    <div class="titre">Date D'enregistrement</div>
     <div class="valeur"><?php echo $date; ?> <span class="unite"></span></div> 

</div> </div>
    </body>
</html>

