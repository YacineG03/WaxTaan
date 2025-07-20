<?php
// Les fonctions sont déjà définies dans controller.php, pas besoin de les redéclarer ici
?>
<div class="profile-section">
    <h2>Mes Discussions</h2>
    <p style="color: var(--text-muted); margin-bottom: 16px;">Consultez vos conversations avec vos contacts et groupes</p>
</div>
<div class="search-bar">
    <input type="text" id="rechercheDiscussions" placeholder="Rechercher une discussion...">
</div>
<div class="modern-list">
<?php
// Récupérer tous les contacts avec leurs messages
$contacts_utilisateur = $contacts->xpath("//contact[user_id='$id_utilisateur']");
$discussions = [];
// Discussions avec contacts
foreach ($contacts_utilisateur as $contact) {
    $utilisateur_contact = $utilisateurs->xpath("//user[telephone='{$contact->contact_telephone}']")[0];
    if ($utilisateur_contact) {
        $nb_non_lus = compterMessagesNonLus($messages, $utilisateur_courant->telephone, $contact->contact_telephone);
        $id_utilisateur_contact = obtenirIdUtilisateurParTelephone($utilisateurs, $contact->contact_telephone);
        $messages_conversation = $messages->xpath("//message[(sender_id='$id_utilisateur' and recipient='$contact->contact_telephone') or (sender_id='$id_utilisateur_contact' and recipient='$utilisateur_courant->telephone')]");
        if (!empty($messages_conversation)) {
            // Trier les messages par timestamp (plus récent en dernier)
            usort($messages_conversation, function($a, $b) {
                $timestamp_a = (string)$a->timestamp;
                $timestamp_b = (string)$b->timestamp;
                return strtotime($timestamp_a) - strtotime($timestamp_b);
            });
            $derniers_messages = end($messages_conversation);
            $discussions[] = [
                'type' => 'contact',
                'contact' => $contact,
                'utilisateur_contact' => $utilisateur_contact,
                'nb_non_lus' => $nb_non_lus,
                'derniers_messages' => $derniers_messages,
                'nb_messages' => count($messages_conversation)
            ];
        } else {
            $discussions[] = [
                'type' => 'contact',
                'contact' => $contact,
                'utilisateur_contact' => $utilisateur_contact,
                'nb_non_lus' => 0,
                'derniers_messages' => null,
                'nb_messages' => 0
            ];
        }
    }
}
// Discussions de groupes
$groupes_utilisateur = [];
foreach ($groupes->group as $groupe) {
    // Vérifie si l'utilisateur est admin, coadmin ou membre
    $est_admin = ((string)$groupe->id_admin === $id_utilisateur);
    $est_coadmin = false;
    if (isset($groupe->id_coadmin)) {
        foreach ($groupe->id_coadmin as $id_coadmin) {
            if ((string)$id_coadmin === $id_utilisateur) {
                $est_coadmin = true;
                break;
            }
        }
    }
    $est_membre = false;
    foreach ($groupe->member_id as $id_membre) {
        if ((string)$id_membre === $id_utilisateur) {
            $est_membre = true;
            break;
        }
    }
    if ($est_admin || $est_coadmin || $est_membre) {
        $groupes_utilisateur[] = $groupe;
    }
}
foreach ($groupes_utilisateur as $groupe) {
    $messages_groupe = $messages->xpath("//message[recipient_group='{$groupe->id}']");
    if (!empty($messages_groupe)) {
        // Trier les messages par timestamp (plus récent en dernier)
        usort($messages_groupe, function($a, $b) {
            $timestamp_a = (string)$a->timestamp;
            $timestamp_b = (string)$b->timestamp;
            return strtotime($timestamp_a) - strtotime($timestamp_b);
        });
        $derniers_messages = end($messages_groupe);
        $discussions[] = [
            'type' => 'groupe',
            'groupe' => $groupe,
            'nb_non_lus' => 0, 
            'derniers_messages' => $derniers_messages,
            'nb_messages' => count($messages_groupe)
        ];
    } else {
        $discussions[] = [
            'type' => 'groupe',
            'groupe' => $groupe,
            'nb_non_lus' => 0,
            'derniers_messages' => null,
            'nb_messages' => 0
        ];
    }
}
// Trier les discussions par date du dernier message (plus récent en premier)
usort($discussions, function($a, $b) {
    if ($a['derniers_messages'] && $b['derniers_messages']) {
        $timestamp_a = (string)$a['derniers_messages']->timestamp;
        $timestamp_b = (string)$b['derniers_messages']->timestamp;
        return strtotime($timestamp_b) - strtotime($timestamp_a);
    } elseif ($a['derniers_messages']) {
        return -1;
    } elseif ($b['derniers_messages']) {
        return 1;
    }
    return 0;
});
foreach ($discussions as $discussion) {
    if ($discussion['type'] === 'contact') {
        $contact = $discussion['contact'];
        $utilisateur_contact = $discussion['utilisateur_contact'];
        $nb_non_lus = $discussion['nb_non_lus'];
        $derniers_messages = $discussion['derniers_messages'];
        $nb_messages = $discussion['nb_messages'];
?>
    <div class="list-item discussion-item">
        <div class="item-avatar">
            <?php if ($utilisateur_contact->profile_photo && (string)$utilisateur_contact->profile_photo != 'default.jpg') { ?>
                <img src="../uploads/<?php echo htmlspecialchars($utilisateur_contact->profile_photo); ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php } else { ?>
                <?php echo strtoupper(substr($contact->contact_name, 0, 1)); ?>
            <?php } ?>
        </div>
        <div class="item-content">
            <div class="item-name">
                <?php echo htmlspecialchars($contact->contact_name); ?>
                <?php if ($nb_non_lus > 0) { ?>
                    <span class="unread-badge"><?php echo $nb_non_lus; ?></span>
                <?php } ?>
            </div>
            <div class="item-meta">
                <?php if ($derniers_messages) { ?>
                    <?php 
                    $expediteur = $utilisateurs->xpath("//user[id='{$derniers_messages->sender_id}']")[0];
                    $envoye_par_moi = $derniers_messages->sender_id == $id_utilisateur;
                    ?>
                    <span class="message-preview">
                        <?php echo $envoye_par_moi ? 'Vous: ' : ''; ?>
                        <?php echo htmlspecialchars(substr($derniers_messages->content, 0, 50)); ?>
                        <?php if (strlen($derniers_messages->content) > 50) echo '...'; ?>
                    </span>
                    <span class="message-time">
                        <?php echo date('d/m H:i', strtotime((string)$derniers_messages->timestamp ?? 'now')); ?>
                    </span>
                <?php } else { ?>
                    <span class="no-messages">Aucun message</span>
                <?php } ?>
            </div>
        </div>
        <div class="item-actions">
            <a href="?conversation=contact:<?php echo urlencode($contact->id); ?>&tab=discussions" class="modern-btn btn-primary btn-small">
                💬 Ouvrir
            </a>
        </div>
    </div>
<?php } else { // Type groupe
        $groupe = $discussion['groupe'];
        $nb_non_lus = $discussion['nb_non_lus'];
        $derniers_messages = $discussion['derniers_messages'];
        $nb_messages = $discussion['nb_messages'];
?>
    <div class="list-item discussion-item">
        <div class="item-avatar">
            <?php if ($groupe->group_photo && $groupe->group_photo != 'default.jpg') { ?>
                <img src="../uploads/<?php echo htmlspecialchars($groupe->group_photo); ?>" alt="Photo Groupe" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php } else { ?>
                <?php echo strtoupper(substr($groupe->name, 0, 1)); ?>
            <?php } ?>
        </div>
        <div class="item-content">
            <div class="item-name">
                <?php echo htmlspecialchars($groupe->name); ?>
                <span style="background: var(--info-gradient); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 8px;">Groupe</span>
                <?php if ($nb_non_lus > 0) { ?>
                    <span class="unread-badge"><?php echo $nb_non_lus; ?></span>
                <?php } ?>
            </div>
            <div class="item-meta">
                <?php if ($derniers_messages) { ?>
                    <?php 
                    $expediteur = $utilisateurs->xpath("//user[id='{$derniers_messages->sender_id}']")[0];
                    $envoye_par_moi = $derniers_messages->sender_id == $id_utilisateur;
                    ?>
                    <span class="message-preview">
                        <?php echo $envoye_par_moi ? 'Vous: ' : ($expediteur ? htmlspecialchars($expediteur->prenom . ': ') : ''); ?>
                        <?php echo htmlspecialchars(substr($derniers_messages->content, 0, 50)); ?>
                        <?php if (strlen($derniers_messages->content) > 50) echo '...'; ?>
                    </span>
                    <span class="message-time">
                        <?php echo date('d/m H:i', strtotime((string)$derniers_messages->timestamp ?? 'now')); ?>
                    </span>
                <?php } else { ?>
                    <span class="no-messages">Aucun message</span>
                <?php } ?>
            </div>
        </div>
        <div class="item-actions">
            <a href="?conversation=groupe:<?php echo urlencode($groupe->id); ?>&tab=discussions" class="modern-btn btn-primary btn-small">
                💬 Ouvrir
            </a>
        </div>
    </div>
<?php } ?>
<?php } ?>
<?php if (empty($discussions)) { ?>
    <div class="empty-state">
        <div class="empty-icon">💬</div>
        <h3>Aucune discussion</h3>
        <p>Commencez à discuter avec vos contacts ou groupes pour voir vos conversations ici.</p>
    </div>
<?php } ?>
</div>
<script src="../js/global.js"></script> 