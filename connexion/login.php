<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telephone = $_POST['telephone'];
    $mot_de_passe = $_POST['mdp'];
    $utilisateurs = simplexml_load_file('../xmls/users.xml');
    if ($utilisateurs === false) {
        die("Erreur : impossible de charger le fichier XML des utilisateurs.");
    }

    foreach ($utilisateurs->user as $utilisateur) {
        if ($utilisateur->telephone == $telephone && password_verify($mot_de_passe, $utilisateur->mdp)) {
            $_SESSION['id_utilisateur'] = (string)$utilisateur->id;
            header('Location: ../views/view.php'); 
            exit;
        }
    }
    $erreur = "Numéro ou mot de passe incorrect";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - WaxTaan</title>
    <link rel="stylesheet" href="../css/connexion.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-layout">
            <!-- Logo à droite -->
            <div class="auth-logo-side">
                <img src="../css/logo/image.png" alt="WaxTaan Logo">
            </div>
            <div class="auth-card">
                <h1>Connexion</h1>
                <?php if (isset($erreur)) { echo "<div class='auth-error'>$erreur</div>"; } ?>
                
                <form method="post" class="auth-form">
                    <div class="form-group">
                        <label for="telephone">Numéro de téléphone</label>
                        <input type="text" id="telephone" name="telephone" required pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567">
                    </div>
                    
                    <div class="form-group">
                        <label for="mdp">Mot de passe</label>
                        <input type="mdp" id="mdp" name="mdp" required>
                    </div>
                    
                    <button type="submit">Se connecter</button>
                </form>
                
                <div class="auth-link">
                    Pas de compte ? <a href="register.php">S'inscrire</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/global.js"></script>
</body>
</html>