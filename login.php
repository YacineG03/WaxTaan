<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $users = simplexml_load_file('xmls/users.xml');
    
    foreach ($users->user as $user) {
        if ($user->phone == $phone && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = (string)$user->id;
            header('Location: index.php');
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Connexion</h1>
        <?php if (isset($error)) { echo "<p class='text-red-500 mb-4'>$error</p>"; } ?>
        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Numéro de téléphone</label>
                <input type="text" name="phone" required class="w-full p-2 border rounded" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567">
            </div>
            <div>
                <label class="block text-sm font-medium">Mot de passe</label>
                <input type="password" name="password" required class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded">Se connecter</button>
        </form>
        <p class="mt-4 text-center">Pas de compte ? <a href="register.php" class="text-blue-500">S'inscrire</a></p>
    </div>
</body>
</html>