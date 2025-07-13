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
            </div>

            <!-- Contenu de la sidebar -->
            <div class="sidebar-content">
                <!-- Onglet Profil -->
                <div class="tab-panel active" id="profile-panel">
                    <div class="profile-section">
                        <h2>Modifier le Profil</h2>
                        <form action="../api.php" method="post" enctype="multipart/form-data" class="modern-form">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="firstname" value="<?php echo htmlspecialchars($current_user->firstname); ?>" class="form-input" placeholder="Votre prénom">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Nom</label>
                                <input type="text" name="lastname" value="<?php echo htmlspecialchars($current_user->lastname); ?>" class="form-input" placeholder="Votre nom">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Sexe</label>
                                <select name="sex" class="form-input">
                                    <option value="M" <?php echo $current_user->sex == 'M' ? 'selected' : ''; ?>>Masculin</option>
                                    <option value="F" <?php echo $current_user->sex == 'F' ? 'selected' : ''; ?>>Féminin</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Âge</label>
                                <input type="number" name="age" value="<?php echo htmlspecialchars($current_user->age); ?>" class="form-input" placeholder="Votre âge">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($current_user->phone); ?>" class="form-input" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Photo de profil</label>
                                <input type="file" name="profile_photo" class="form-input" accept="image/*">
                            </div>
                            
                            <button type="submit" class="modern-btn btn-primary">
                                <span>💾</span>
                                Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Onglet Contacts -->
                <div class="tab-panel" id="contacts-panel">
                    <div class="profile-section">
                        <h2>Ajouter un Contact</h2>
                        <form action="../api.php" method="post" class="modern-form" id="addContactForm">
                            <input type="hidden" name="action" value="add_contact">
                            
                            <div class="form-group">
                                <label class="form-label">Nom du contact</label>
                                <input type="text" name="contact_name" class="form-input" placeholder="Nom du contact" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Numéro de téléphone</label>
                                <input type="text" name="contact_phone" class="form-input" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567" required>
                                <small class="form-help">Le numéro doit correspondre à un utilisateur existant</small>
                            </div>
                            
                            <button type="submit" class="modern-btn btn-primary">
                                <span>➕</span>
                                Ajouter Contact
                            </button>
                        </form>
                    </div>

                    <div class="modern-list">
                        <?php foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) { ?>
                            <?php
                            $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                            if ($contact_user) {
                                $unread_count = getUnreadMessageCount($messages, $current_user->phone, $contact->contact_phone);
                            ?>
                                <div class="list-item">
                                    <div class="item-avatar">
                                        <?php if ($contact_user->profile_photo && $contact_user->profile_photo != 'default.jpg') { ?>
                                            <img src="uploads/<?php echo htmlspecialchars($contact_user->profile_photo); ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                        <?php } else { ?>
                                            <?php echo strtoupper(substr($contact->contact_name, 0, 1)); ?>
                                        <?php } ?>
                                    </div>
                                    
                                    <div class="item-content">
                                        <div class="item-name">
                                            <?php echo htmlspecialchars($contact->contact_name); ?>
                                            <?php if ($unread_count > 0) { ?>
                                                <span class="unread-badge"><?php echo $unread_count; ?></span>
                                            <?php } ?>
                                        </div>
                                        <div class="item-meta">
                                            <?php echo htmlspecialchars($contact->contact_phone); ?>
                                            <?php if ($unread_count > 0) { ?>
                                                <span class="unread-indicator">Nouveaux messages</span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    
                                    <div class="item-actions">
                                        <a href="?conversation=contact:<?php echo urlencode($contact->contact_phone); ?>" class="modern-btn btn-secondary btn-small">
                                            💬 Chat
                                        </a>
                                        <button type="button" onclick="confirmDeleteContact('<?php echo $contact->id; ?>', '<?php echo htmlspecialchars($contact->contact_name); ?>')" class="modern-btn btn-danger btn-small">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <!-- Onglet Groupes -->
                <div class="tab-panel" id="groups-panel">
                    <div class="profile-section">
                        <h2>Créer un Groupe</h2>
                        <form action="../api.php" method="post" enctype="multipart/form-data" class="modern-form">
                            <input type="hidden" name="action" value="create_group">
                            
                            <div class="form-group">
                                <label class="form-label">Nom du groupe</label>
                                <input type="text" name="group_name" class="form-input" placeholder="Nom du groupe" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Photo du groupe</label>
                                <input type="file" name="group_photo" class="form-input" accept="image/*">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Sélectionner les membres</label>
                                <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px;">
                                    <?php
                                    foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
                                        $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                                        if ($contact_user) {
                                            echo "<label style='display: flex; align-items: center; gap: 8px; padding: 8px; cursor: pointer; border-radius: 6px; transition: background 0.3s ease;' onmouseover='this.style.background=\"var(--bg-secondary)\"' onmouseout='this.style.background=\"transparent\"'>";
                                            echo "<input type='checkbox' name='member_ids[]' value='" . htmlspecialchars($contact_user->id) . "' style='margin: 0;'>";
                                            echo "<span>" . htmlspecialchars($contact->contact_name) . "</span>";
                                            echo "</label>";
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <button type="submit" class="modern-btn btn-primary">
                                <span>🏠</span>
                                Créer le Groupe
                            </button>
                        </form>
                    </div>

                    <div class="modern-list">
                        <?php foreach ($groups->xpath("//group[member_id='$user_id']") as $group) { ?>
                            <div class="list-item">
                                <div class="item-avatar">
                                    <?php if ($group->group_photo && $group->group_photo != 'default.jpg') { ?>
                                        <img src="uploads/<?php echo htmlspecialchars($group->group_photo); ?>" alt="Group Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    <?php } else { ?>
                                        <?php echo strtoupper(substr($group->name, 0, 1)); ?>
                                    <?php } ?>
                                </div>
                                
                                <div class="item-content">
                                    <div class="item-name">
                                        <?php echo htmlspecialchars($group->name); ?>
                                        <?php if ((string)$group->admin_id === $user_id) { ?>
                                            <span style="background: var(--success-gradient); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 8px;">Admin</span>
                                        <?php } ?>
                                    </div>
                                    <div class="item-meta"><?php echo count($group->member_id); ?> membres</div>
                                </div>
                                
                                <div class="item-actions">
                                    <a href="?conversation=group:<?php echo $group->id; ?>" class="modern-btn btn-secondary btn-small">
                                        💬 Chat
                                    </a>
                                    <?php if ((string)$group->admin_id === $user_id) { ?>
                                        <a href="../api.php?action=delete_group&group_id=<?php echo $group->id; ?>" class="modern-btn btn-danger btn-small">
                                            🗑️
                                        </a>
                                    <?php } else { ?>
                                        <a href="../api.php?action=leave_group&group_id=<?php echo $group->id; ?>" class="modern-btn btn-danger btn-small">
                                            🚪
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
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
                    } else {
                        $messages_to_show = [];
                    }
                    
                    $contact_info_result = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='$id']");
                    $contact_info = !empty($contact_info_result) ? $contact_info_result[0] : null;
                    $conversation_name = $contact_info ? htmlspecialchars($contact_info->contact_name) : 'Contact';
                    $conversation_avatar = strtoupper(substr($conversation_name, 0, 1));
                } elseif ($type === 'group') {
                    $messages_to_show = $messages->xpath("//message[recipient_group='$id']");
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
                    <div class="chat-avatar"><?php echo $conversation_avatar; ?></div>
                    <div class="chat-info">
                        <h3><?php echo $conversation_name; ?></h3>
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
                                        <div style="margin-top: 8px;">
                                            <a href="uploads/<?php echo $message->file; ?>" download style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; background: rgba(255,255,255,0.1); border-radius: 4px; font-size: 12px;">
                                                📎 Fichier joint
                                            </a>
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
                            <textarea name="message" class="message-input" placeholder="Tapez votre message..." rows="1" required></textarea>
                            
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

    <!-- Formulaire caché pour la suppression de contact -->
    <form id="deleteContactForm" action="../api.php" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete_contact">
        <input type="hidden" name="contact_id" id="contactIdToDelete">
    </form>

    <script>
        // Gestion des onglets
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Retirer la classe active de tous les onglets
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                
                // Ajouter la classe active à l'onglet cliqué
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab + '-panel').classList.add('active');
            });
        });

        // Fonction de confirmation pour la suppression de contact
        function confirmDeleteContact(contactId, contactName) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le contact "${contactName}" ?\n\nCette action est irréversible.`)) {
                document.getElementById('contactIdToDelete').value = contactId;
                document.getElementById('deleteContactForm').submit();
            }
        }

        // Validation du formulaire d'ajout de contact
        document.getElementById('addContactForm').addEventListener('submit', function(e) {
            const contactName = this.querySelector('input[name="contact_name"]').value.trim();
            const contactPhone = this.querySelector('input[name="contact_phone"]').value.trim();
            
            // Vérifier que le nom n'est pas vide
            if (contactName.length < 2) {
                e.preventDefault();
                alert('Le nom du contact doit contenir au moins 2 caractères.');
                return false;
            }
            
            // Vérifier le format du numéro de téléphone
            const phonePattern = /^(77|70|78|76)[0-9]{7}$/;
            if (!phonePattern.test(contactPhone)) {
                e.preventDefault();
                alert('Le numéro de téléphone doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres.');
                return false;
            }
            
            // Vérifier que l'utilisateur ne s'ajoute pas lui-même
            const currentUserPhone = '<?php echo $current_user->phone; ?>';
            if (contactPhone === currentUserPhone) {
                e.preventDefault();
                alert('Vous ne pouvez pas vous ajouter vous-même comme contact.');
                return false;
            }
        });

        // Auto-scroll du chat
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Auto-resize du textarea
        const messageInput = document.querySelector('.message-input');
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
        }

        // Notification pour les erreurs et succès
        setTimeout(() => {
            const errorNotif = document.querySelector('[style*="position: fixed"]');
            if (errorNotif) {
                errorNotif.style.transform = 'translateX(400px)';
                errorNotif.style.opacity = '0';
                setTimeout(() => errorNotif.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>
