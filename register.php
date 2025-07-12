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
        $users = simplexml_load_file('xmls/users.xml');
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
                $new_user->addChild('profile_photo', 'default.jpg'); // Photo par défaut
            }
            $users->asXML('xmls/users.xml');
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Inscription</h1>
        <?php if (isset($error)) { echo "<p class='text-red-500 mb-4'>$error</p>"; } ?>
        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Prénom</label>
                <input type="text" name="firstname" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium">Nom</label>
                <input type="text" name="lastname" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium">Sexe</label>
                <select name="sex" class="w-full p-2 border rounded" required>
                    <option value="M">Masculin</option>
                    <option value="F">Féminin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Âge</label>
                <input type="number" name="age" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium">Numéro de téléphone</label>
                <input type="text" name="phone" required class="w-full p-2 border rounded" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567">
            </div>
            <div>
                <label class="block text-sm font-medium">Mot de passe</label>
                <input type="password" name="password" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium">Photo de profil</label>
                <input type="file" name="profile_photo" class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded">S'inscrire</button>
        </form>
        <p class="mt-4 text-center">Déjà un compte ? <a href="login.php" class="text-blue-500">Se connecter</a></p>
    </div>
</body>
</html>