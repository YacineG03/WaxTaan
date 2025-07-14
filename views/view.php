<?php require_once '../controller.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WaxTaan - Messagerie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/modern-app.css">
</head>
<body>
    <?php
    // Notifications d'erreur et de succès
    if (isset($_GET['error'])) {
        $error_message = '';
        switch ($_GET['error']) {
            case 'minimum_two_members':
                $error_message = 'Erreur : Vous devez sélectionner au moins deux contacts pour créer un groupe.';
                break;
            case 'contact_not_found':
                $error_message = 'Erreur : Contact introuvable.';
                break;
            case 'unauthorized':
                $error_message = 'Erreur : Vous n\'êtes pas autorisé à supprimer ce contact.';
                break;
            case 'delete_failed':
                $error_message = 'Erreur : Échec de la suppression du contact.';
                break;
            case 'missing_contact_id':
                $error_message = 'Erreur : ID du contact manquant.';
                break;
            case 'contact_already_exists':
                $error_message = 'Erreur : Ce contact existe déjà dans votre liste.';
                break;
            case 'user_not_found':
                $error_message = 'Erreur : Aucun utilisateur trouvé avec ce numéro de téléphone.';
                break;
            case 'cannot_add_self':
                $error_message = 'Erreur : Vous ne pouvez pas vous ajouter vous-même comme contact.';
                break;
            case 'add_failed':
                $error_message = 'Erreur : Échec de l\'ajout du contact.';
                break;
            case 'missing_contact_data':
                $error_message = 'Erreur : Données du contact manquantes.';
                break;
            case 'group_not_found':
                $error_message = 'Erreur : Groupe introuvable.';
                break;
            case 'group_delete_failed':
                $error_message = 'Erreur : Échec de la suppression du groupe.';
                break;
            case 'group_leave_failed':
                $error_message = 'Erreur : Échec de la sortie du groupe.';
                break;
            case 'member_remove_failed':
                $error_message = 'Erreur : Échec du retrait du membre.';
                break;
            case 'coadmin_manage_failed':
                $error_message = 'Erreur : Échec de la gestion des co-admins.';
                break;
            case 'unauthorized_group_action':
                $error_message = 'Erreur : Vous n\'êtes pas autorisé à effectuer cette action.';
                break;
            case 'member_not_found':
                $error_message = 'Erreur : Membre introuvable dans le groupe.';
                break;
            case 'missing_group_id':
                $error_message = 'Erreur : ID du groupe manquant.';
                break;
            case 'missing_group_data':
                $error_message = 'Erreur : Données du groupe manquantes.';
                break;
            case 'coadmin_already_exists':
                $error_message = 'Erreur : Cet utilisateur est déjà co-admin du groupe.';
                break;
            case 'coadmin_not_found':
                $error_message = 'Erreur : Co-admin introuvable dans le groupe.';
                break;
            case 'group_creation_failed':
                $error_message = 'Erreur : Échec de la création du groupe.';
                break;
            default:
                $error_message = 'Une erreur est survenue.';
        }
        if ($error_message) {
            echo "<div style='position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 25px rgba(240, 147, 251, 0.3); z-index: 1000;'>$error_message</div>";
        }
    }
    
    if (isset($_GET['success'])) {
        $success_message = '';
        switch ($_GET['success']) {
            case 'contact_deleted':
                $success_message = '✅ Contact supprimé avec succès !';
                break;
            case 'contact_added':
                $success_message = '✅ Contact ajouté avec succès !';
                break;
            case 'group_created':
                $success_message = '✅ Groupe créé avec succès !';
                break;
            case 'group_deleted':
                $success_message = '✅ Groupe supprimé avec succès !';
                break;
            case 'group_left':
                $success_message = '✅ Vous avez quitté le groupe avec succès !';
                break;
            case 'member_removed':
                $success_message = '✅ Membre retiré du groupe avec succès !';
                break;
            case 'coadmin_added':
                $success_message = '✅ Co-admin ajouté avec succès !';
                break;
            case 'coadmin_removed':
                $success_message = '✅ Co-admin retiré avec succès !';
                break;
            default:
                $success_message = 'Opération réussie !';
        }
        if ($success_message) {
            echo "<div style='position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%); color: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 25px rgba(74, 222, 128, 0.3); z-index: 1000;'>$success_message</div>";
        }
    }
    ?>
    
    <div class="app-container">
        <!-- Sidebar moderne -->
        <div class="modern-sidebar">
            <!-- Header -->
            <div class="sidebar-header">
                <div class="user-info">
                    <h1>WaxTaan</h1>
                    <p class="user-welcome">Bienvenue, <?php echo htmlspecialchars($current_user->firstname . ' ' . $current_user->lastname); ?>!</p>
                    <a href="../connexion/logout.php" class="logout-btn">
                        <span>🚪</span>
                        Déconnexion
                    </a>
                </div>
            </div>

            <!-- Navigation par onglets -->
            <div class="sidebar-nav">
                <button class="nav-tab active" data-tab="profile">
                    <span class="nav-tab-icon">👤</span>
                    Profil
                </button>
                <button class="nav-tab" data-tab="contacts">
                    <span class="nav-tab-icon">👥</span>
                    Contacts
                </button>
                <button class="nav-tab" data-tab="groups">
                    <span class="nav-tab-icon">🏠</span>
                    Groupes
                </button>
                <button class="nav-tab" data-tab="discussions">
                    <span class="nav-tab-icon">💬</span>
                    Discussions
                </button>
            </div>

            <!-- Contenu de la sidebar -->
            <div class="sidebar-content">
                <!-- Onglet Profil -->
                <div class="tab-panel active" id="profile-panel">
                    <?php include 'profile_view.php'; ?>
                </div>

                <!-- Onglet Contacts -->
                <div class="tab-panel" id="contacts-panel">
                    <?php include 'contacts_view.php'; ?>
                </div>

                <!-- Onglet Groupes -->
                <div class="tab-panel" id="groups-panel">
                    <?php include 'groups_view.php'; ?>
                </div>

                <!-- Onglet Discussions -->
                <div class="tab-panel" id="discussions-panel">
                    <?php include 'discussions_view.php'; ?>
                </div>
            </div>
        </div>

        <!-- Zone de chat -->
        <div class="chat-area">
            <?php
            $current_conversation = $_GET['conversation'] ?? '';
            $messages_to_show = [];
            $conversation_name = '';
            $conversation_avatar = '';
            
            if ($current_conversation) {
                list($type, $id) = explode(':', $current_conversation);
                if ($type === 'contact') {
                    // Utiliser la fonction de correspondance
                    $contact_user_id = getUserIDByPhone($users, $id);
                    
                    if ($contact_user_id) {
                        // Récupérer les messages entre les deux utilisateurs
                        $messages_to_show = $messages->xpath("//message[(sender_id='$user_id' and recipient='$id') or (sender_id='$contact_user_id' and recipient='$current_user->phone')]");
                        // Marquer comme lus tous les messages reçus non lus
                        foreach ($messages->xpath("//message[sender_id='$contact_user_id' and recipient='$current_user->phone']") as $msg) {
                            if (!isset($msg->read_by) || !in_array($user_id, explode(',', (string)$msg->read_by))) {
                                $read_by = isset($msg->read_by) ? (string)$msg->read_by : '';
                                $read_by_arr = $read_by ? explode(',', $read_by) : [];
                                $read_by_arr[] = $user_id;
                                $msg->read_by = implode(',', array_unique($read_by_arr));
                            }
                        }
                        $messages->asXML('../xmls/messages.xml');
                    } else {
                        $messages_to_show = [];
                    }
                    
                    $contact_info_result = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='$id']");
                    $contact_info = !empty($contact_info_result) ? $contact_info_result[0] : null;
                    $conversation_name = $contact_info ? htmlspecialchars($contact_info->contact_name) : 'Contact';
                    $conversation_avatar = strtoupper(substr($conversation_name, 0, 1));
                } elseif ($type === 'group') {
                    $messages_to_show = $messages->xpath("//message[recipient_group='$id']");
                    // Marquer comme lus tous les messages de groupe non lus
                    foreach ($messages->xpath("//message[recipient_group='$id']") as $msg) {
                        if (!isset($msg->read_by) || !in_array($user_id, explode(',', (string)$msg->read_by))) {
                            $read_by = isset($msg->read_by) ? (string)$msg->read_by : '';
                            $read_by_arr = $read_by ? explode(',', $read_by) : [];
                            $read_by_arr[] = $user_id;
                            $msg->read_by = implode(',', array_unique($read_by_arr));
                        }
                    }
                    $messages->asXML('../xmls/messages.xml');
                    $group_info_result = $groups->xpath("//group[id='$id']");
                    $group_info = !empty($group_info_result) ? $group_info_result[0] : null;
                    $conversation_name = $group_info ? htmlspecialchars($group_info->name) : 'Groupe';
                    $conversation_avatar = strtoupper(substr($conversation_name, 0, 1));
                }
            }
            ?>
            
            <?php if ($current_conversation) { ?>
                <!-- Header du chat -->
                <div class="chat-header">
                    <div class="chat-avatar">
                        <?php if ($group->group_photo && $group->group_photo != 'default.jpg') { ?>
                            <img src="../uploads/<?php echo htmlspecialchars($group->group_photo); ?>" alt="Group Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php } else { ?>
                            <?php echo strtoupper(substr($group->name, 0, 1)); ?>
                        <?php } ?>
                    </div>
                    <div class="chat-info" style="flex:1;">
                        <h3 style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
                            <?php echo $conversation_name; ?>
                            <a href="?" class="modern-btn btn-danger btn-small" style="margin-left:16px;">✖</a>
                        </h3>
                        <div class="chat-status">
                            <?php if ($type === 'group' && $group_info) { ?>
                                <?php echo count($group_info->member_id); ?> membres
                            <?php } else { ?>
                                En ligne
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="chat-messages" id="chat-container">
                    <?php if (empty($messages_to_show)) { ?>
                        <div class="empty-chat">
                            <div class="empty-chat-icon">💬</div>
                            <h3>Aucun message</h3>
                            <p>Commencez la conversation en envoyant votre premier message !</p>
                        </div>
                    <?php } else { ?>
                        <?php foreach ($messages_to_show as $message) { ?>
                            <div class="message-bubble <?php echo $message->sender_id == $user_id ? 'sent' : 'received'; ?>">
                                <?php if ($message->sender_id != $user_id) { ?>
                                    <div class="message-meta">
                                        <?php $sender = $users->xpath("//user[id='{$message->sender_id}']")[0]; ?>
                                        <span class="message-sender"><?php echo htmlspecialchars($sender->firstname . ' ' . $sender->lastname); ?></span>
                                        <span class="message-time"><?php echo date('H:i', strtotime($message['timestamp'] ?? 'now')); ?></span>
                                    </div>
                                <?php } ?>
                                
                                <div class="message-content">
                                    <p><?php echo htmlspecialchars($message->content); ?></p>
                                    
                                    <?php if ($message->file) { ?>
                                        <div class="message-file" style="margin-top: 8px;">
                                            <?php
                                            $file_extension = strtolower(pathinfo($message->file, PATHINFO_EXTENSION));
                                            $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $is_video = in_array($file_extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm']);
                                            ?>
                                            
                                            <?php if ($is_image) { ?>
                                                <!-- Affichage des images -->
                                                <div class="file-preview">
                                                    <img src="../uploads/<?php echo htmlspecialchars($message->file); ?>" alt="Image" class="message-image" onclick="openImageModal('../uploads/<?php echo htmlspecialchars($message->file); ?>')">
                                                </div>
                                            <?php } elseif ($is_video) { ?>
                                                <!-- Affichage des vidéos -->
                                                <div class="file-preview">
                                                    <video controls class="message-video">
                                                        <source src="../uploads/<?php echo htmlspecialchars($message->file); ?>" type="video/<?php echo $file_extension; ?>">
                                                        Votre navigateur ne supporte pas la lecture de vidéos.
                                                    </video>
                                                </div>
                                            <?php } else { ?>
                                                <!-- Affichage des autres fichiers -->
                                                <a href="../uploads/<?php echo htmlspecialchars($message->file); ?>" download class="file-download">
                                                    <span class="file-icon">📎</span>
                                                    <span class="file-name"><?php echo htmlspecialchars($message->file); ?></span>
                                                    <span class="file-size">Télécharger</span>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <?php if ($message->sender_id == $user_id) { ?>
                                    <div class="message-meta" style="justify-content: flex-end; margin-top: 4px;">
                                        <span class="message-time"><?php echo date('H:i', strtotime($message['timestamp'] ?? 'now')); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>

                <!-- Zone de saisie -->
                <div class="chat-input">
                    <form action="../api.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="send_message">
                        <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($id); ?>">
                        <input type="hidden" name="recipient_type" value="<?php echo $type; ?>">
                        
                        <div class="input-container">
                            <textarea name="message" class="message-input" placeholder="Tapez votre message..." rows="1" ></textarea>
                            
                            <div class="file-input-wrapper">
                                <input type="file" name="file" class="file-input" accept="image/*,video/*,application/*">
                                <div class="file-input-btn">
                                    📎
                                </div>
                            </div>
                            
                            <button type="submit" class="send-btn">
                                <span>📤</span>
                                Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <!-- État vide -->
                <div class="chat-messages">
                    <div class="empty-chat">
                        <div class="empty-chat-icon">💬</div>
                        <h3>Bienvenue sur WaxTaan</h3>
                        <p>Sélectionnez un contact ou un groupe pour commencer à discuter.</p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Modal pour les actions de groupe -->
    <div id="groupActionsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="groupActionsTitle">Actions de groupe</h3>
                <button type="button" onclick="closeGroupActionsModal()" class="modal-close">&times;</button>
            </div>
            <div id="groupActionsContent" class="modal-body">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les images -->
    <div id="imageModal" class="modal" style="display: none;">
        <div class="modal-content">
            <img id="modalImage" src="" alt="Image" style="max-width: 100%; max-height: 80vh;">
            <button type="button" onclick="closeImageModal()" class="modal-close">&times;</button>
        </div>
    </div>

    <!-- Formulaire caché pour la suppression de contact -->
    <form id="deleteContactForm" action="../api.php" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete_contact">
        <input type="hidden" name="contact_id" id="contactIdToDelete">
    </form>

    <!-- Formulaire caché pour la suppression de groupe -->
    <form id="deleteGroupForm" action="../api.php" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete_group">
        <input type="hidden" name="group_id" id="groupIdToDelete">
    </form>

    <!-- Formulaire caché pour quitter un groupe -->
    <form id="leaveGroupForm" action="../api.php" method="post" style="display: none;">
        <input type="hidden" name="action" value="leave_group">
        <input type="hidden" name="group_id" id="groupIdToLeave">
    </form>

    <!-- Champ caché pour le téléphone de l'utilisateur actuel -->
    <input type="hidden" name="current_user_phone" value="<?php echo $current_user->phone; ?>">

    <!-- Inclusion du fichier JavaScript externe -->
    <script src="../js/view.js"></script>
</body>
</html>
