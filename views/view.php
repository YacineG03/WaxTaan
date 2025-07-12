<?php require_once '../controller.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WaxTaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100">
    <?php
    if (isset($_GET['error']) && $_GET['error'] === 'minimum_two_members') {
        echo "<p class='text-red-500 p-4'>Erreur : Vous devez sélectionner au moins deux contacts pour créer un groupe.</p>";
    }
    ?>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-1/3 bg-white border-r">
            <div class="p-4 border-b">
                <h1 class="text-xl font-bold">WaxTaan</h1>
                <p class="text-sm">Bienvenue, <?php echo htmlspecialchars($current_user->firstname . ' ' . $current_user->lastname); ?>!</p>
                <a href="../connexion/logout.php" class="text-red-500">Déconnexion</a>
            </div>

            <!-- Profil -->
            <div class="p-4">
                <h2 class="font-semibold">Modifier le Profil</h2>
                <form action="api.php" method="post" enctype="multipart/form-data" class="space-y-2">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="text" name="firstname" value="<?php echo htmlspecialchars($current_user->firstname); ?>" class="w-full p-2 border rounded" placeholder="Prénom">
                    <input type="text" name="lastname" value="<?php echo htmlspecialchars($current_user->lastname); ?>" class="w-full p-2 border rounded" placeholder="Nom">
                    <select name="sex" class="w-full p-2 border rounded">
                        <option value="M" <?php echo $current_user->sex == 'M' ? 'selected' : ''; ?>>Masculin</option>
                        <option value="F" <?php echo $current_user->sex == 'F' ? 'selected' : ''; ?>>Féminin</option>
                    </select>
                    <input type="number" name="age" value="<?php echo htmlspecialchars($current_user->age); ?>" class="w-full p-2 border rounded" placeholder="Âge">
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($current_user->phone); ?>" class="w-full p-2 border rounded" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres">
                    <input type="file" name="profile_photo" class="w-full p-2 border rounded">
                    <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded">Mettre à jour</button>
                </form>
            </div>

            <?php include 'contacts_view.php'; ?>
            <?php include 'groups_view.php'; ?>
        </div>

        <?php include 'chat_view.php'; ?>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-container');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    </script>
</body>
</html>