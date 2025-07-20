<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = htmlspecialchars($_POST['prenom']);
    $nom = htmlspecialchars($_POST['nom']);
    $sexe = htmlspecialchars($_POST['sexe']);
    $age = htmlspecialchars($_POST['age']);
    $telephone = $_POST['telephone'];
    $mot_de_passe = password_hash($_POST['mdp'],PASSWORD_DEFAULT);
    
    if (!$age || $age < 12 || $age > 120) {
        $erreur = "L'âge doit être un nombre entre 12 et 120 ans.";
    } else if (!preg_match('/^(77|70|78|76)[0-9]{7}$/', $telephone)) {
        $erreur = "Le numéro de téléphone doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres.";
    } else {
        $utilisateurs = simplexml_load_file('../xmls/users.xml');
        $utilisateur_existe = $utilisateurs->xpath("//user[telephone='$telephone']");
        
        if ($utilisateur_existe) {
            $erreur = "Ce numéro est déjà utilisé.";
        } else {
            $nouvel_utilisateur = $utilisateurs->addChild('user');
            $nouvel_utilisateur->addChild('id', uniqid());
            $nouvel_utilisateur->addChild('prenom', $prenom);
            $nouvel_utilisateur->addChild('nom', $nom);
            $nouvel_utilisateur->addChild('sexe', $sexe);
            $nouvel_utilisateur->addChild('age', $age);
            $nouvel_utilisateur->addChild('telephone', $telephone);
            $nouvel_utilisateur->addChild('mdp', $mot_de_passe);
            if (!empty($_FILES['profile_photo']['name'])) {
                $nom_fichier = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], 'uploads/' . $nom_fichier);
                $nouvel_utilisateur->addChild('profile_photo', $nom_fichier);
            } else {
                $nouvel_utilisateur->addChild('profile_photo', 'default.jpg');
            }
            $utilisateurs->asXML('../xmls/users.xml');
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - WaxTaan</title>
    <link rel="stylesheet" href="../css/connexion.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-layout">
            <!-- Logo à gauche -->
            <div class="auth-logo-side">
                <img src="../css/logo/image.png" alt="WaxTaan Logo">
            </div>
            <div class="auth-card">
                <h1>Inscription</h1>
                <?php if (isset($erreur)) { echo "<div class='auth-error'>$erreur</div>"; } ?>
                
                <form method="post" enctype="multipart/form-data" class="auth-form">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sexe">Sexe</label>
                        <select id="sexe" name="sexe" required>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="age">Âge</label>
                        <input type="number" id="age" name="age" required min="12" max="120">
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Numéro de téléphone</label>
                        <input type="text" id="telephone" name="telephone" required pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567">
                    </div>
                    
                    <div class="form-group">
                        <label for="mdp">Mot de passe</label>
                        <input type="mdp" id="mdp" name="mdp" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_photo">Photo de profil</label>
                        <div class="file-upload">
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                            <label for="profile_photo" class="file-upload-label">
                                📷 Choisir une photo
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit">S'inscrire</button>
                </form>
                
                <div class="auth-link">
                    Déjà un compte ? <a href="login.php">Se connecter</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/global.js"></script>
</body>
</html>