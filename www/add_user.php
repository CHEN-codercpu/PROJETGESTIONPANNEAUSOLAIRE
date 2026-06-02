<?php
session_start();
require_once 'dbconnection.php';

$message = "";

// 1. SÉCURITÉ : Vérifier si on est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. SÉCURITÉ : Vérifier si on est Administrateur
if ($_SESSION['role'] !== 'Administrateur') {
    // Si pas admin, on arrête tout
    exit("Accès refusé : Vous n'êtes pas administrateur.");
}

// 3. TRAITEMENT DU FORMULAIRE
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!empty($_POST['new_user']) && !empty($_POST['new_pass'])) {

        // Hashage du mot de passe
        $hash = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);

        try {
            // Tentative d'insertion
            $stmt = $dbh->prepare("INSERT INTO Utilisateurs (nom_utilisateur, MotDePasse, role) VALUES (?, ?, 'Utilisateur')");
            $stmt->execute([$_POST['new_user'], $hash]);
            
            // Si on arrive ici, c'est que ça a marché !
            $message = "Utilisateur ajouté avec succès !";

        } catch (PDOException $e) {
            // Gestion des erreurs (Doublon ou autre)
            if ($e->getCode() == 23000 || $e->errorInfo[1] == 1062) {
                $message = "Ce nom d'utilisateur est déjà pris !";
            } else {
                $message = "Erreur technique : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un membre</title>
    <style>
        /* --- DESIGN GLOBAL --- */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom, #2980b9, #6dd5fa);
            height: 100vh;
            display: flex;
            flex-direction: column; /* Empile le titre et la carte */
            justify-content: center;
            align-items: center;
        }

        /* Bouton Retour */
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

        /* Titre de bienvenue */
        .welcome-title {
            color: white;
            font-size: 2em;
            margin-bottom: 20px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
            text-align: center;
        }

        /* Carte Blanche */
        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 300px;
            text-align: center;
        }

        h2 { color: #333; margin-bottom: 20px; }

        /* Champs du formulaire */
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        /* Bouton Valider */
        button {
            width: 100%;
            padding: 12px;
            background-color: #e67e22;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        button:hover { background-color: #d35400; }

        /* MESSAGES (Vert ou Rouge) */
        .msg-box {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>

    <a href="index.php" class="back-btn">⬅ Retour au Dashboard</a>

    <h1 class="welcome-title">
        Bienvenue, <?php echo htmlspecialchars($_SESSION['pseudo']); ?> !
    </h1>

    <div class="card">
        <h2>Ajouter un membre</h2>

        <?php if (!empty($message)): ?>
            <div class="msg-box <?php echo (strpos($message, '❌') !== false) ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="new_user" placeholder="Nom d'utilisateur" required>
            <input type="password" name="new_pass" placeholder="Mot de passe" required>
            <button type="submit">Ajouter l'utilisateur</button>
        </form>

    </div>

</body>
</html>
