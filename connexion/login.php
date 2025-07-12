<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $users = simplexml_load_file('../xmls/users.xml');
    if ($users === false) {
        die("Erreur : impossible de charger le fichier XML des utilisateurs.");
    }

    foreach ($users->user as $user) {
        if ($user->phone == $phone && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = (string)$user->id;
            header('Location: ../views/view.php'); 
            exit;
        }
    }
    $error = "Numéro ou mot de passe incorrect";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - WaxTaan</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Connexion</h1>
            <?php if (isset($error)) { echo "<div class='auth-error'>$error</div>"; } ?>
            
            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="phone">Numéro de téléphone</label>
                    <input type="text" id="phone" name="phone" required pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Se connecter</button>
            </form>
            
            <div class="auth-link">
                Pas de compte ? <a href="register.php">S'inscrire</a>
            </div>
        </div>
    </div>
</body>
</html>
