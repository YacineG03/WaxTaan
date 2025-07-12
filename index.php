<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Charger les données XML avec vérification
$users = @simplexml_load_file('xmls/users.xml');
if ($users === false) {
    die('Erreur : Impossible de charger users.xml. Vérifiez le fichier ou le chemin.');
}
$contacts = @simplexml_load_file('xmls/contacts.xml');
if ($contacts === false) {
    die('Erreur : Impossible de charger contacts.xml. Vérifiez le fichier ou le chemin.');
}
$groups = @simplexml_load_file('xmls/groups.xml');
if ($groups === false) {
    die('Erreur : Impossible de charger groups.xml. Vérifiez le fichier ou le chemin.');
}
$messages = @simplexml_load_file('xmls/messages.xml');
if ($messages === false) {
    die('Erreur : Impossible de charger messages.xml. Vérifiez le fichier ou le chemin.');
}

var_dump($users);
var_dump($contacts);
var_dump($groups);
var_dump($messages);

// Récupérer l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$current_user = $users->xpath("//user[id='$user_id']")[0];

// Récupérer les discussions (contacts et groupes)
$conversations = [];
foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
    $conversations[] = ['type' => 'contact', 'id' => (string)$contact->contact_phone, 'name' => (string)$contact->contact_name];
}
foreach ($groups->xpath("//group[member_id='$user_id']") as $group) {
    $conversations[] = ['type' => 'group', 'id' => (string)$group->id, 'name' => (string)$group->name];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WaxTaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #chat-container { height: 70vh; overflow-y: auto; }
        .message { max-width: 70%; margin: 10px; padding: 10px; border-radius: 10px; }
        .message.sent { background-color: #DCF8C6; margin-left: auto; }
        .message.received { background-color: #E5E7EB; }
        .contact-item img { width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; }
        .selected-contact { background-color: #e0e7ff; }
    </style>
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
                <a href="logout.php" class="text-red-500">Déconnexion</a>
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

            <!-- Contacts -->
            <div class="p-4">
                <h2 class="font-semibold">Contacts</h2>
                <form action="api.php" method="post" class="space-y-2">
                    <input type="hidden" name="action" value="add_contact">
                    <input type="text" name="contact_name" class="w-full p-2 border rounded" placeholder="Nom du contact">
                    <input type="text" name="contact_phone" class="w-full p-2 border rounded" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres">
                    <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded">Ajouter</button>
                </form>
                <ul class="mt-2">
                    <?php foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) { ?>
                        <li class="p-2 flex items-center">
                            <?php
                            $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                            if ($contact_user->profile_photo) { ?>
                                <img src="uploads/<?php echo htmlspecialchars($contact_user->profile_photo); ?>" alt="Photo" class="contact-item">
                            <?php } else { ?>
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-2">?</div>
                            <?php } ?>
                            <a href="?conversation=contact:<?php echo urlencode($contact->contact_phone); ?>" class="hover:underline"><span class="mr-2"><?php echo htmlspecialchars($contact->contact_name); ?></span></a>
                            <a href="api.php?action=delete_contact&contact_id=<?php echo $contact->id; ?>" class="text-red-500 ml-2">Supprimer</a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Groupes -->
            <div class="p-4">
                <h2 class="font-semibold">Groupes</h2>
                <form action="api.php" method="post" enctype="multipart/form-data" class="space-y-2">
                    <input type="hidden" name="action" value="create_group">
                    <input type="text" name="group_name" class="w-full p-2 border rounded" placeholder="Nom du groupe" required>
                    <input type="file" name="group_photo" class="w-full p-2 border rounded" accept="image/*">
                    <div class="space-y-2 max-h-40 overflow-y-auto border rounded p-2">
                        <?php
                        // Cases à cocher pour les contacts
                        foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
                            $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                            if ($contact_user) {
                                echo "<label class='flex items-center'>";
                                echo "<input type='checkbox' name='member_ids[]' value='" . htmlspecialchars($contact_user->id) . "' class='mr-2'>";
                                echo htmlspecialchars($contact->contact_name);
                                // echo htmlspecialchars($contact_user->firstname . ' ' . $contact_user->lastname);
                                echo "</label>";
                            }
                        }
                        ?>
                    </div>
                    <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded">Créer</button>
                </form>
                <ul class="mt-2">
                    <?php foreach ($groups->xpath("//group[member_id='$user_id']") as $group) { ?>
                        <li class="p-2">
                            <?php $is_admin = (string)$group->admin_id === $user_id; ?>
                            <a href="?conversation=group:<?php echo $group->id; ?>" class="hover:underline">
                                <?php if ($group->group_photo) { ?>
                                    <img src="uploads/<?php echo htmlspecialchars($group->group_photo); ?>" alt="Group Photo" class="w-10 h-10 rounded-full mr-2 inline-block">
                                <?php } else { ?>
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-2 inline-block">?</div>
                                <?php } ?>
                                <?php echo htmlspecialchars($group->name); ?>
                            </a>
                            <?php if ($is_admin) { ?>
                                <a href="api.php?action=delete_group&group_id=<?php echo $group->id; ?>" class="text-red-500 ml-2">Supprimer</a>
                                <a href="api.php?action=list_members&group_id=<?php echo $group->id; ?>" class="text-blue-500 ml-2">Lister membres</a>
                                <form action="api.php" method="post" style="display:inline;" class="ml-2">
                                    <input type="hidden" name="action" value="remove_member">
                                    <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                                    <select name="member_id" required>
                                        <?php foreach ($group->member_id as $member_id) {
                                            $member = $users->xpath("//user[id='$member_id']")[0];
                                            if ($member_id != $user_id) {
                                                echo "<option value='{$member_id}'>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</option>";
                                            }
                                        } ?>
                                    </select>
                                    <button type="submit" class="p-1 bg-red-500 text-white rounded">Retirer</button>
                                </form>
                                <form action="api.php" method="post" style="display:inline;" class="ml-2">
                                    <input type="hidden" name="action" value="add_coadmin">
                                    <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                                    <select name="coadmin_id" required>
                                        <?php foreach ($group->member_id as $member_id) {
                                            $member = $users->xpath("//user[id='$member_id']")[0];
                                            if ($member_id != $user_id && !in_array($member_id, explode(',', $group->coadmins ?? ''))) {
                                                echo "<option value='{$member_id}'>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</option>";
                                            }
                                        } ?>
                                    </select>
                                    <button type="submit" class="p-1 bg-green-500 text-white rounded">Ajouter Co-Admin</button>
                                </form>
                            <?php } ?>
                            <a href="api.php?action=leave_group&group_id=<?php echo $group->id; ?>" class="text-yellow-500 ml-2">Quitter</a>
                            <a href="api.php?action=toggle_notifications&group_id=<?php echo $group->id; ?>" class="text-purple-500 ml-2">
                                <?php echo in_array($group->id, explode(',', $_SESSION['muted_groups'] ?? '')) ? 'Activer notifications' : 'Désactiver notifications'; ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Chat -->
        <div class="w-2/3 flex flex-col">
            <?php
            $current_conversation = $_GET['conversation'] ?? '';
            $messages_to_show = [];
            if ($current_conversation) {
                list($type, $id) = explode(':', $current_conversation);
                if ($type === 'contact') {
                    $messages_to_show = $messages->xpath("//message[(sender_id='$user_id' and recipient='$id') or (sender_id='$id' and recipient='$user_id')]");
                } elseif ($type === 'group') {
                    $messages_to_show = $messages->xpath("//message[recipient_group='$id']");
                }
            }
            ?>
            <div id="chat-container" class="flex-1 p-4 overflow-y-auto">
                <?php foreach ($messages_to_show as $message) { ?>
                    <div class="message <?php echo $message->sender_id == $user_id ? 'sent' : 'received'; ?>">
                        <?php $sender = $users->xpath("//user[id='{$message->sender_id}']")[0]; ?>
                        <?php if ($sender->profile_photo) { ?>
                            <img src="uploads/<?php echo htmlspecialchars($sender->profile_photo); ?>" alt="Profile" class="w-8 h-8 rounded-full inline-block mr-2">
                        <?php } else { ?>
                            <div class="w-8 h-8 bg-gray-300 rounded-full inline-block mr-2 flex items-center justify-center">?</div>
                        <?php } ?>
                        <p class="font-semibold inline-block"><?php echo htmlspecialchars($sender->firstname . ' ' . $sender->lastname); ?></p>
                        <p class="text-xs text-gray-500 inline-block ml-2"><?php echo date('d/m/Y H:i', strtotime($message['timestamp'] ?? 'now')); ?></p>
                        <p class="mt-1"><?php echo htmlspecialchars($message->content); ?></p>
                        <?php if ($message->file) { ?>
                            <a href="uploads/<?php echo $message->file; ?>" download class="text-blue-500">Télécharger fichier</a>
                        <?php } ?>
                        <?php $is_admin = in_array($user_id, explode(',', $groups->xpath("//group[id='$id']")[0]->admin_id . ',' . ($groups->xpath("//group[id='$id']")[0]->coadmins ?? ''))); ?>
                        <?php if ($is_admin && $message->id) { ?>
                            <a href="api.php?action=delete_message&message_id=<?php echo $message->id; ?>&group_id=<?php echo $id; ?>" class="text-red-500 ml-2">Supprimer</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            <?php if ($current_conversation && strpos($current_conversation, 'group:') === 0) { ?>
                <form action="api.php" method="post" enctype="multipart/form-data" class="p-4 bg-white border-t">
                    <input type="hidden" name="action" value="send_message">
                    <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="recipient_type" value="group">
                    <div class="flex space-x-2">
                        <textarea name="message" class="w-full p-2 border rounded" placeholder="Votre message..."></textarea>
                        <input type="file" name="file" class="p-2" accept="image/*,video/*,application/*">
                        <button type="submit" class="p-2 bg-blue-500 text-white rounded">Envoyer</button>
                    </div>
                </form>
            <?php } elseif ($current_conversation) { ?>
                <form action="api.php" method="post" enctype="multipart/form-data" class="p-4 bg-white border-t">
                    <input type="hidden" name="action" value="send_message">
                    <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="recipient_type" value="contact">
                    <div class="flex space-x-2">
                        <textarea name="message" class="w-full p-2 border rounded" placeholder="Votre message..."></textarea>
                        <input type="file" name="file" class="p-2" accept="image/*,video/*,application/*">
                        <button type="submit" class="p-2 bg-blue-500 text-white rounded">Envoyer</button>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-container');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    </script>
</body>
</html>