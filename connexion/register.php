<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $sex = htmlspecialchars($_POST['sex']);
    $age = htmlspecialchars($_POST['age']);
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    if (!$age || $age < 1 || $age > 120) {
        $error = "L'âge doit être un nombre entre 1 et 120.";
    } else if (!preg_match('/^(77|70|78|76)[0-9]{7}$/', $phone)) {
        $error = "Le numéro de téléphone doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres.";
    } else {
        $users = simplexml_load_file('../xmls/users.xml');
        $user_exists = $users->xpath("//user[phone='$phone']");
        
        if ($user_exists) {
            $error = "Ce numéro est déjà utilisé.";
        } else {
            $new_user = $users->addChild('user');
            $new_user->addChild('id', uniqid());
            $new_user->addChild('firstname', $firstname);
            $new_user->addChild('lastname', $lastname);
            $new_user->addChild('sex', $sex);
            $new_user->addChild('age', $age);
            $new_user->addChild('phone', $phone);
            $new_user->addChild('password', $password);
            if (!empty($_FILES['profile_photo']['name'])) {
                $file_name = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], 'uploads/' . $file_name);
                $new_user->addChild('profile_photo', $file_name);
            } else {
                $new_user->addChild('profile_photo', 'default.jpg');
            }
            $users->asXML('../xmls/users.xml');
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
            <h1>Inscription</h1>
            <?php if (isset($error)) { echo "<div class='auth-error'>$error</div>"; } ?>
            
            <form method="post" enctype="multipart/form-data" class="auth-form">
                <div class="form-group">
                    <label for="firstname">Prénom</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>
                
                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>
                
                <div class="form-group">
                    <label for="sex">Sexe</label>
                    <select id="sex" name="sex" required>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="age">Âge</label>
                    <input type="number" id="age" name="age" required min="1" max="120">
                </div>
                
                <div class="form-group">
                    <label for="phone">Numéro de téléphone</label>
                    <input type="text" id="phone" name="phone" required pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
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
</body>
</html>
