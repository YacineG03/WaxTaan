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
    <link rel="stylesheet" href="../css/modern-app.css?v=1.1">
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
                $error_message = 'Erreur : Aucun utilisateur trouvé avec ce numéro de télételephone.';
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
            case 'update_failed':
                $error_message = 'Erreur : Échec de la mise à jour du profil.';
                break;
            case 'missing_profile_data':
                $error_message = 'Erreur : Données du profil manquantes.';
                break;
            case 'message_send_failed':
                $error_message = 'Erreur : Échec de l\'envoi du message.';
                break;
            case 'missing_message_data':
                $error_message = 'Erreur : Données du message manquantes.';
                break;
            case 'telephone_already_used':
                $error_message = 'Erreur : Ce numéro de téléphone est déjà utilisé par un autre utilisateur.';
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
            case 'contact_updated':
                $success_message = '✅ Contact modifié avec succès !';
                break;
            case 'message_sent':
                $success_message = '✅ Message envoyé avec succès !';
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
            case 'profile_updated':
                $success_message = '✅ Profil mis à jour avec succès !';
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
                    <p class="user-welcome">Bienvenue, <?php echo htmlspecialchars($utilisateur_courant->prenom . ' ' . $utilisateur_courant->nom); ?>!</p>
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
                    // Récupérer les informations du contact par son ID
                    $contact_info_result = $contacts->xpath("//contact[id='$id']");
                    $contact_info = !empty($contact_info_result) ? $contact_info_result[0] : null;

                    if ($contact_info) {
                        // Cas contact connu
                        $contact_user_id = obtenirIdUtilisateurParTelephone($utilisateurs, $contact_info->telephone_contact);
                        if ($contact_user_id) {
                            $messages_to_show = $messages->xpath("//message[(id_expediteur='$id_utilisateur' and destinataire='{$contact_info->telephone_contact}') or (id_expediteur='$contact_user_id' and destinataire='$utilisateur_courant->telephone')]");
                            foreach ($messages->xpath("//message[id_expediteur='$contact_user_id' and destinataire='$utilisateur_courant->telephone']") as $msg) {
                                if (!isset($msg->lus_par) || !in_array($id_utilisateur, explode(',', (string)$msg->lus_par))) {
                                    $lus_par = isset($msg->lus_par) ? (string)$msg->lus_par : '';
                                    $lus_par_arr = $lus_par ? explode(',', $lus_par) : [];
                                    $lus_par_arr[] = $id_utilisateur;
                                    $msg->lus_par = implode(',', array_unique($lus_par_arr));
                                }
                            }
                            $messages->asXML('../xmls/messages.xml');
                        } else {
                            $messages_to_show = [];
                        }
                        $conversation_name = htmlspecialchars($contact_info->nom_contact);
                        $conversation_avatar = strtoupper(substr($conversation_name, 0, 1));
                    } else {
                        // Cas d'un contact inconnu (pas dans la liste des contacts)
                        $utilisateur_inconnu_result = $utilisateurs->xpath("//user[id='$id']");
                        $utilisateur_inconnu = !empty($utilisateur_inconnu_result) ? $utilisateur_inconnu_result[0] : null;
                        if ($utilisateur_inconnu) {
                            $telephone_inconnu = (string)$utilisateur_inconnu->telephone;
                            // Récupérer les messages entre l'utilisateur courant et l'inconnu
                            $messages_to_show = $messages->xpath("//message[(id_expediteur='$id_utilisateur' and destinataire='$telephone_inconnu') or (id_expediteur='$id' and destinataire='$utilisateur_courant->telephone')]");
                            // Marquer comme lus tous les messages reçus non lus
                            foreach ($messages->xpath("//message[id_expediteur='$id' and destinataire='$utilisateur_courant->telephone']") as $msg) {
                                if (!isset($msg->lus_par) || !in_array($id_utilisateur, explode(',', (string)$msg->lus_par))) {
                                    $lus_par = isset($msg->lus_par) ? (string)$msg->lus_par : '';
                                    $lus_par_arr = $lus_par ? explode(',', $lus_par) : [];
                                    $lus_par_arr[] = $id_utilisateur;
                                    $msg->lus_par = implode(',', array_unique($lus_par_arr));
                                }
                            }
                            $messages->asXML('../xmls/messages.xml');
                            $conversation_name = htmlspecialchars($telephone_inconnu);
                            $conversation_avatar = strtoupper(substr($telephone_inconnu, 0, 1));
                        } else {
                            $messages_to_show = [];
                            $conversation_name = 'Inconnu';
                            $conversation_avatar = '?';
                        }
                    }
                } elseif (trim($type) === 'groupe') {
                    $messages_to_show = $messages->xpath("//message[groupe_destinataire='$id']");
                    // Marquer comme lus tous les messages de groupe non lus
                    foreach ($messages->xpath("//message[groupe_destinataire='$id']") as $msg) {
                        if (!isset($msg->lus_par) || !in_array($id_utilisateur, explode(',', (string)$msg->lus_par))) {
                            $lus_par = isset($msg->lus_par) ? (string)$msg->lus_par : '';
                            $lus_par_arr = $lus_par ? explode(',', $lus_par) : [];
                            $lus_par_arr[] = $id_utilisateur;
                            $msg->lus_par = implode(',', array_unique($lus_par_arr));
                        }
                    }
                    $messages->asXML('../xmls/messages.xml');
                    $group_info_result = $groupes->xpath("//group[id='$id']");
                    $group_info = !empty($group_info_result) ? $group_info_result[0] : null;
                    $conversation_name = $group_info ? htmlspecialchars($group_info->name) : 'Groupe';
                    $conversation_avatar = strtoupper(substr($conversation_name, 0, 1));
                }
            }
            ?>
            
            <?php if ($current_conversation) { ?>
                <!-- Header du chat -->
                <div class="chat-header" style="position: relative;">
                    <div class="chat-avatar">
                        <?php if ($type === 'groupe' && $group_info && $group_info->photo_groupe && $group_info->photo_groupe != 'default.jpg') { ?>
                            <img src="../uploads/<?php echo htmlspecialchars($group_info->photo_groupe); ?>" alt="Group Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php } elseif ($type === 'contact' && $contact_info) { 
                            $utilisateur_contact = $utilisateurs->xpath("//user[telephone='{$contact_info->telephone_contact}']")[0];
                            if ($utilisateur_contact && $utilisateur_contact->profile_photo && (string)$utilisateur_contact->profile_photo != 'default.jpg') { ?>
                                <img src="../uploads/<?php echo htmlspecialchars($utilisateur_contact->profile_photo); ?>" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php } else { ?>
                                <?php echo $conversation_avatar; ?>
                            <?php } ?>
                        <?php } else { ?>
                            <?php echo $conversation_avatar; ?>
                        <?php } ?>
                    </div>
                    <div class="chat-info" style="flex:1;">
                        <h3 style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
                            <?php echo $conversation_name; ?>
                            <span style="display:flex;align-items:center;gap:8px;">
                                <a href="?tab=discussions" class="modern-btn btn-danger btn-small" style="margin-left:0;">✖</a>
                                <!-- Bouton menu (3 points) -->
                                <button type="button" class="chat-menu-btn" onclick="toggleChatMenu(event)" style="background:none;border:none;outline:none;cursor:pointer;padding:8px 12px;">
                                    <span style="display:inline-block;font-size:28px;line-height:1;vertical-align:middle;">&#8942;</span>
                                </button>
                            </span>
                        </h3>
                        <div class="chat-status">
                            <?php if ($type === 'groupe' && $group_info) { ?>
                                <?php 
                                $nb_membres = 0;
                                if (isset($group_info->id_membre)) {
                                    $nb_membres = count($group_info->id_membre);
                                }
                                echo $nb_membres; 
                                ?> membres
                            <?php } else { ?>
                                En ligne
                            <?php } ?>
                        </div>
                        <!-- Menu déroulant -->
                        <div id="chatMenu" class="chat-menu-dropdown" style="display:none;position:absolute;top:48px;right:0;z-index:1001;background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.12);padding:8px 0;min-width:220px;">
                            <form id="deleteDiscussionForm" action="../api.php" method="post" style="margin:0;">
                                <input type="hidden" name="action" value="supprimer_discussion">
                                <input type="hidden" name="type" value="<?php echo $type; ?>">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="hidden" name="scope" id="deleteScope" value="self">
                                <button type="submit" class="modern-btn btn-danger btn-small" style="width:100%;text-align:left;">🗑️ Supprimer la discussion pour moi</button>
                            </form>
                            <button type="button" class="modern-btn btn-secondary btn-small" onclick="openSearchMessageModal()" style="width:100%;text-align:left;">🔍 Rechercher dans la discussion</button>
                            <?php if ($type === 'groupe' && isset($group_info) && (string)$group_info->id_admin === $id_utilisateur) { ?>
                            <form id="deleteDiscussionAllForm" action="../api.php" method="post" style="margin:0;margin-top:4px;">
                                <input type="hidden" name="action" value="supprimer_discussion">
                                <input type="hidden" name="type" value="groupe">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="hidden" name="scope" value="all">
                                <button type="submit" class="modern-btn btn-danger btn-small" style="width:100%;text-align:left;">🗑️ Supprimer la discussion pour tout le monde</button>
                            </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <script>
                function toggleChatMenu(e) {
                    e.stopPropagation();
                    var menu = document.getElementById('chatMenu');
                    if (menu.style.display === 'none' || menu.style.display === '') {
                        menu.style.display = 'block';
                        document.addEventListener('click', closeChatMenuOnClickOutside);
                    } else {
                        menu.style.display = 'none';
                        document.removeEventListener('click', closeChatMenuOnClickOutside);
                    }
                }
                function closeChatMenuOnClickOutside(e) {
                    var menu = document.getElementById('chatMenu');
                    if (!menu.contains(e.target)) {
                        menu.style.display = 'none';
                        document.removeEventListener('click', closeChatMenuOnClickOutside);
                    }
                }
                </script>

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
                            <div class="message-bubble <?php echo $message->id_expediteur == $id_utilisateur ? 'sent' : 'received'; ?>">
                                <?php if ($message->id_expediteur != $id_utilisateur) { ?>
                                    <div class="message-meta">
                                        <?php
                                        $sender = $utilisateurs->xpath("//user[id='{$message->id_expediteur}']")[0];
                                        $sender_tel = (string)$sender->telephone;
                                        $contact_sender = $contacts->xpath("//contact[id_utilisateur='$id_utilisateur' and telephone_contact='$sender_tel']");
                                        if (!empty($contact_sender)) {
                                            // Afficher le nom du contact
                                            echo '<span class="message-sender">' . htmlspecialchars($contact_sender[0]->nom_contact) . '</span>';
                                        } else {
                                            // Sinon, afficher nom/prénom du user
                                            echo '<span class="message-sender">' . htmlspecialchars($sender->prenom . ' ' . $sender->nom) . '</span>';
                                        }
                                        ?>
                                        <span class="message-time"><?php echo date('H:i', strtotime($message->date_heure ?? 'now')); ?></span>
                                    </div>
                                <?php } ?>
                                
                                <div class="message-content">
                                    <p><?php echo htmlspecialchars($message->contenu); ?></p>
                                    
                                    <?php if ($message->fichier) { ?>
                                        <div class="message-file" style="margin-top: 8px;">
                                            <?php
                                            $file_extension = strtolower(pathinfo($message->fichier, PATHINFO_EXTENSION));
                                            $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $is_video = in_array($file_extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm']);
                                            ?>
                                            
                                            <?php if ($is_image) { ?>
                                                <!-- Affichage des images -->
                                                <div class="file-preview">
                                                    <img src="../uploads/<?php echo htmlspecialchars($message->fichier); ?>" alt="Image" class="message-image" onclick="openImageModal('../uploads/<?php echo htmlspecialchars($message->fichier); ?>')">
                                                </div>
                                            <?php } elseif ($is_video) { ?>
                                                <!-- Affichage des vidéos -->
                                                <div class="file-preview">
                                                    <video controls class="message-video">
                                                        <source src="../uploads/<?php echo htmlspecialchars($message->fichier); ?>" type="video/<?php echo $file_extension; ?>">
                                                        Votre navigateur ne supporte pas la lecture de vidéos.
                                                    </video>
                                                </div>
                                            <?php } else { ?>
                                                <!-- Affichage des autres fichiers -->
                                                <a href="../uploads/<?php echo htmlspecialchars($message->fichier); ?>" download class="file-download">
                                                    <span class="file-icon">📎</span>
                                                    <span class="file-name"><?php echo htmlspecialchars($message->fichier); ?></span>
                                                    <span class="file-size">Télécharger</span>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <?php if ($message->id_expediteur == $id_utilisateur) { ?>
                                    <div class="message-meta" style="justify-content: flex-end; margin-top: 4px;">
                                        <span class="message-time"><?php echo date('H:i', strtotime($message->date_heure ?? 'now')); ?></span>
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
                        <input type="hidden" name="recipient" value="<?php echo isset($type) && $type === 'contact' && isset($contact_info) ? htmlspecialchars($contact_info->telephone_contact) : (isset($id) ? htmlspecialchars($id) : ''); ?>">
                        <input type="hidden" name="recipient_type" value="<?php echo isset($type) ? $type : ''; ?>">
                        
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

    <!-- Modal de prévisualisation d'upload -->
    <div id="uploadPreviewModal" class="modal" style="display:none;">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Prévisualisation du fichier</h3>
          <button type="button" class="modal-close" onclick="closeUploadPreviewModal()">&times;</button>
        </div>
        <div class="modal-body" id="uploadPreviewBody">
          <!-- Prévisualisation dynamique JS -->
        </div>
        <div class="modal-footer" style="display:flex;gap:12px;justify-content:flex-end;">
          <button type="button" class="modern-btn btn-secondary" onclick="closeUploadPreviewModal()">Annuler</button>
          <button type="button" class="modern-btn btn-primary" id="confirmUploadBtn">Envoyer</button>
        </div>
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
        <input type="hidden" name="id_group" id="groupIdToDelete">
    </form>

    <!-- Formulaire caché pour quitter un groupe -->
    <form id="leaveGroupForm" action="../api.php" method="post" style="display: none;">
        <input type="hidden" name="action" value="leave_group">
        <input type="hidden" name="id_group" id="groupIdToLeave">
    </form>

    <!-- Champ caché pour le télételephone de l'utilisateur actuel -->
    <input type="hidden" name="current_user_telephone" value="<?php echo $utilisateur_courant->telephone; ?>">

    <!-- Modal de recherche de message -->
    <div id="searchMessageModal" class="modal" style="display:none;">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Rechercher un message</h3>
          <button type="button" class="modal-close" onclick="closeSearchMessageModal()">&times;</button>
        </div>
        <div class="modal-body">
          <input type="text" id="searchMessageInput" class="form-input" placeholder="Tapez un mot ou une phrase...">
          <div id="searchMessageResults" style="margin-top:16px;max-height:250px;overflow-y:auto;"></div>
        </div>
      </div>
    </div>

    <script src="../js/global.js"></script>
</body>
</html>
