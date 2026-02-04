<?php
session_start();
require_once 'dbconnection.php'; 

// Sécurité : Si pas connecté, on dégage
if (!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$timeout_duration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Trop long ! On détruit tout
    session_unset();
    session_destroy();
    header("Location: login.php?msg=timeout");
    exit();
}

// On met à jour l'heure de dernière activité
$_SESSION['last_activity'] = time();

// 3. Anti-Vol de Session (Renouveler l'ID à chaque chargement)
session_regenerate_id(true);

// --- LOGIQUE 1 : GESTION DU CIEL (Heure) ---
// On récupère l'heure actuelle (0 à 23)
$heure = date('H');
$classe_ciel = ""; // Variable pour le CSS

if ($heure >= 6 && $heure < 10) {
    $classe_ciel = "matin"; // Aube orangée
} elseif ($heure >= 10 && $heure < 18) {
    $classe_ciel = "jour";  // Grand ciel bleu
} elseif ($heure >= 18 && $heure < 21) {
    $classe_ciel = "soir";  // Coucher de soleil violet/orange
} else {
    $classe_ciel = "nuit";  // Nuit noire étoilée
}

// --- LOGIQUE 2 : RÉCUPÉRATION DES DONNÉES ---

// A. La dernière mesure (Pour les cartes)
$req_last = $dbh->query('SELECT * FROM Mesures ORDER BY idMesures DESC LIMIT 1');
$mesure = $req_last->fetch(PDO::FETCH_ASSOC);

// Valeurs par défaut
$tension_p = 0; $courant_p = 0; $tension_b = 0; $temp_b = 0; $lux = 0; $date = "En attente";

if ($mesure) {
    $tension_p = $mesure['tension_panneau'];
    $courant_p = $mesure['courant_panneau'];
    $tension_b = $mesure['tension_batterie'];
    $temp_b = $mesure['temp_batterie'];
    $lux = $mesure['eclairement'];
    $date = $mesure['date_heure'];
}

// B. L'historique (Pour le tableau du bas)
// On récupère les 10 dernières lignes
$req_history = $dbh->query('SELECT * FROM Mesures ORDER BY idMesures DESC LIMIT 10');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Solaire - <?php echo $_SESSION['pseudo']; ?></title>
    
    <style>
        /* --- STYLE DU CIEL (BACKGROUND) --- */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            min-height: 100vh;
            transition: background 1s ease; /* Transition douce */
            padding-bottom: 50px;
        }

        /* Les 4 ambiances */
        body.matin { background: linear-gradient(to bottom, #f3904f, #3b4371); } /* Orange vers Bleu */
        body.jour  { background: linear-gradient(to bottom, #2980b9, #6dd5fa, #ffffff); } /* Bleu clair */
        body.soir  { background: linear-gradient(to bottom, #451e3e, #f09819); } /* Violet vers Orange */
        body.nuit  { background: linear-gradient(to bottom, #0f2027, #203a43, #2c5364); } /* Sombre */

        /* --- LE SOLEIL / LUNE (DÉCORATION) --- */
        .astre {
            position: absolute;
            top: 50px;
            left: 50px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            box-shadow: 0 0 40px rgba(255, 255, 255, 0.5);
        }
        /* Soleil Jaune le jour */
        body.matin .astre, body.jour .astre { background: #FFD700; box-shadow: 0 0 60px #FFD700; }
        /* Lune Blanche la nuit */
        body.nuit .astre, body.soir .astre { background: #F4F6F0; box-shadow: 0 0 30px #F4F6F0; left: auto; right: 100px;}

        /* --- BOUTON LOGOUT --- */
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            transition: 0.3s;
            z-index: 100; /* Toujours au dessus */
        }
        .logout-btn:hover { background-color: red; transform: scale(1.05); }

        /* --- TITRE --- */
        h1 { text-align: center; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); margin-top: 40px; }
        .sub-title { text-align: center; opacity: 0.8; margin-bottom: 40px; }

        /* --- CARTES (DASHBOARD) --- */
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 50px;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.9); /* Blanc semi-transparent */
            color: #333;
            padding: 20px;
            border-radius: 15px;
            width: 180px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            backdrop-filter: blur(5px); /* Effet verre flouté */
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-5px); }
        .titre { font-size: 0.9em; color: #666; text-transform: uppercase; margin-bottom: 10px; }
        .valeur { font-size: 1.8em; font-weight: bold; color: #222; }
        .unite { font-size: 0.5em; vertical-align: super; }

        /* --- TABLEAU HISTORIQUE --- */
        .history-container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            color: #333;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; color: #333; border-radius: 5px; }
        tr:hover { background-color: #f9f9f9; }
        
        /* Responsive : Sur mobile, on réduit la police du tableau */
        @media (max-width: 600px) {
            th, td { padding: 5px; font-size: 0.8em; }
        }
    </style> 
</head>

<body class="<?php echo $classe_ciel; ?>">

    <div class="astre"></div>

    <a href="logout.php" class="logout-btn">Déconnexion ⏻</a>
<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'Administrateur'): ?>
    if($_SESSION['role'] == 'Administrateur')
{
    <a href="add_user.php" class="logout-btn" style="top: 70px; background-color: #e67e22;">⚙️ Nouvel Utilisateur</a>
    <?php endif; ?>
}

    <h1>Tableau de Bord Solaire</h1>
    <div class="sub-title">Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['pseudo']); ?></strong></div>

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
            <div class="titre">Température</div>
            <div class="valeur"><?php echo $temp_b; ?> <span class="unite">°C</span></div>
        </div>
        <div class="card">
            <div class="titre">Lumière</div>
            <div class="valeur"><?php echo $lux; ?> <span class="unite">lx</span></div>
        </div>
    </div>

    <div class="history-container">
        <h3>📊 Historique (10 dernières mesures)</h3>
        <table>
            <thead>
                <tr>
                    <th>Date / Heure</th>
                    <th>Panneau (V)</th>
                    <th>Batterie (V)</th>
                    <th>Temp (°C)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $req_history->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo date("H:i:s", strtotime($row['date_heure'])); ?></td>
                    <td><?php echo $row['tension_panneau']; ?> V</td>
                    <td><?php echo $row['tension_batterie']; ?> V</td>
                    <td><?php echo $row['temp_batterie']; ?> °C</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
