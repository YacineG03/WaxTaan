<?php
if (!isset($contacts)) {
    require_once '../controller.php';
}
// Les fonctions sont déjà définies dans controller.php, pas besoin de les redéclarer ici
?>
<div class="profile-section">
    <div class="section-header">
    <h2>Mes Discussions</h2>
    </div>
    <div class="section-actions">
        <button type="button" onclick="afficherModalNouvelleDiscussion()" class="modern-btn btn-primary btn-large">
            <span>➕</span>
            Nouvelle Discussion
        </button>
    </div>
    <p style="color: var(--text-muted); margin-bottom: 16px;">Consultez vos conversations avec vos contacts et groupes</p>
</div>
<div class="search-bar">
    <input type="text" id="rechercheDiscussions" placeholder="Rechercher une discussion..." class="form-input">
</div>
<div class="modern-list">
<?php
// Récupérer tous les contacts avec leurs messages
$contacts_utilisateur = $contacts->xpath("//contact[id_utilisateur='$id_utilisateur']");
$discussions = [];
// Discussions avec contacts
foreach ($contacts_utilisateur as $contact) {
    $utilisateur_contact = $utilisateurs->xpath("//user[telephone='{$contact->telephone_contact}']")[0];
    if ($utilisateur_contact) {
        $nb_non_lus = compterMessagesNonLus($messages, $utilisateur_courant->telephone, $contact->telephone_contact);
        $id_utilisateur_contact = obtenirIdUtilisateurParTelephone($utilisateurs, $contact->telephone_contact);
        $messages_conversation = $messages->xpath("//message[(id_expediteur='$id_utilisateur' and destinataire='$contact->telephone_contact') or (id_expediteur='$id_utilisateur_contact' and destinataire='$utilisateur_courant->telephone')]");
        if (!empty($messages_conversation)) {
            // Trier les messages par date_heure (plus récent en dernier)
            usort($messages_conversation, function($a, $b) {
                $timestamp_a = (string)$a->date_heure;
                $timestamp_b = (string)$b->date_heure;
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
    foreach ($groupe->id_membre as $id_membre) {
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
    $messages_groupe = $messages->xpath("//message[groupe_destinataire='{$groupe->id}']");
    if (!empty($messages_groupe)) {
        // Trier les messages par date_heure (plus récent en dernier)
        usort($messages_groupe, function($a, $b) {
            $timestamp_a = (string)$a->date_heure;
            $timestamp_b = (string)$b->date_heure;
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
    }
}
// Discussions avec des inconnus (expéditeurs non enregistrés comme contact)
$messages_recus = $messages->xpath("//message[destinataire='$utilisateur_courant->telephone']");
$expediteurs_inconnus = [];
foreach ($messages_recus as $msg) {
    $id_expediteur = (string)$msg->id_expediteur;
    // Vérifier si ce id_expediteur est déjà dans les contacts
    $utilisateur_expediteur = $utilisateurs->xpath("//user[id='$id_expediteur']");
    if ($utilisateur_expediteur) {
        $telephone_expediteur = (string)$utilisateur_expediteur[0]->telephone;
        $contact_existe = $contacts->xpath("//contact[id_utilisateur='$id_utilisateur' and telephone_contact='$telephone_expediteur']");
        if (!$contact_existe && $id_expediteur != $id_utilisateur) {
            // On n'a pas encore ajouté ce numéro comme contact
            $expediteurs_inconnus[$id_expediteur] = $msg;
        }
    }
}
// Pour chaque expéditeur inconnu, créer une discussion
foreach ($expediteurs_inconnus as $id_expediteur => $dernier_msg) {
    $utilisateur_expediteur = $utilisateurs->xpath("//user[id='$id_expediteur']")[0];
    $telephone_expediteur = (string)$utilisateur_expediteur->telephone;
    // Récupérer tous les messages de cette personne
    $messages_conversation = $messages->xpath("//message[(id_expediteur='$id_expediteur' and destinataire='$utilisateur_courant->telephone') or (id_expediteur='$id_utilisateur' and destinataire='$telephone_expediteur')]");
    // Trier par date
    usort($messages_conversation, function($a, $b) {
        $timestamp_a = (string)$a->date_heure;
        $timestamp_b = (string)$b->date_heure;
        return strtotime($timestamp_a) - strtotime($timestamp_b);
    });
    $dernier_message = end($messages_conversation);
    // Compter les non lus
    $nb_non_lus = 0;
    foreach ($messages_conversation as $msg) {
        if ($msg->id_expediteur == $id_expediteur && (!isset($msg->lus_par) || !in_array($id_utilisateur, explode(',', (string)$msg->lus_par)))) {
            $nb_non_lus++;
        }
    }
    $discussions[] = [
        'type' => 'inconnu',
        'utilisateur_expediteur' => $utilisateur_expediteur,
        'telephone_expediteur' => $telephone_expediteur,
        'nb_non_lus' => $nb_non_lus,
        'derniers_messages' => $dernier_message,
        'nb_messages' => count($messages_conversation)
    ];
}
// Trier les discussions par date du dernier message (plus récent en premier)
usort($discussions, function($a, $b) {
    if ($a['derniers_messages'] && $b['derniers_messages']) {
        $timestamp_a = (string)$a['derniers_messages']->date_heure;
        $timestamp_b = (string)$b['derniers_messages']->date_heure;
        return strtotime($timestamp_b) - strtotime($timestamp_a);
    } elseif ($a['derniers_messages']) {
        return -1;
    } elseif ($b['derniers_messages']) {
        return 1;
    }
    return 0;
});
foreach ($discussions as $discussion) {
    if ($discussion['type'] === 'inconnu') {
        $utilisateur_expediteur = $discussion['utilisateur_expediteur'];
        $telephone_expediteur = $discussion['telephone_expediteur'];
        $nb_non_lus = $discussion['nb_non_lus'];
        $dernier_message = $discussion['derniers_messages'];
        $nb_messages = $discussion['nb_messages'];
        ?>
        <div class="list-item discussion-item">
            <div class="item-avatar">
                <?php echo strtoupper(substr($telephone_expediteur, 0, 1)); ?>
            </div>
            <div class="item-content">
                <div class="item-name">
                    <?php echo htmlspecialchars($telephone_expediteur); ?>
                    <span style="background: var(--warning-gradient); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 8px;">Inconnu</span>
                    <?php if ($nb_non_lus > 0) { ?>
                        <span class="unread-badge"><?php echo $nb_non_lus; ?></span>
                    <?php } ?>
                </div>
                <div class="item-meta">
                    <?php if ($dernier_message) { ?>
                        <span class="message-preview">
                            <?php echo $dernier_message->id_expediteur == $id_utilisateur ? 'Vous: ' : ''; ?>
                            <?php echo htmlspecialchars(substr($dernier_message->contenu, 0, 50)); ?>
                            <?php if (strlen($dernier_message->contenu) > 50) echo '...'; ?>
                        </span>
                        <span class="message-time">
                            <?php echo date('d/m H:i', strtotime((string)$dernier_message->date_heure ?? 'now')); ?>
                        </span>
                    <?php } else { ?>
                        <span class="no-messages">Aucun message</span>
                    <?php } ?>
                </div>
            </div>
            <div class="item-actions">
                <a href="?conversation=contact:<?php echo urlencode($utilisateur_expediteur->id); ?>&tab=discussions" class="modern-btn btn-primary btn-small">
                    💬 Ouvrir
                </a>
            </div>
        </div>
        <?php
    } else if ($discussion['type'] === 'contact') {
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
                <?php echo strtoupper(substr($contact->nom_contact, 0, 1)); ?>
            <?php } ?>
        </div>
        <div class="item-content">
            <div class="item-name">
                <?php echo htmlspecialchars($contact->nom_contact); ?>
                <?php if ($nb_non_lus > 0) { ?>
                    <span class="unread-badge"><?php echo $nb_non_lus; ?></span>
                <?php } ?>
            </div>
            <div class="item-meta">
                <?php if ($derniers_messages) { ?>
                    <?php 
                    $expediteur = $utilisateurs->xpath("//user[id='{$derniers_messages->id_expediteur}']")[0];
                    $envoye_par_moi = $derniers_messages->id_expediteur == $id_utilisateur;
                    ?>
                    <span class="message-preview">
                        <?php echo $envoye_par_moi ? 'Vous: ' : ''; ?>
                        <?php echo htmlspecialchars(substr($derniers_messages->contenu, 0, 50)); ?>
                        <?php if (strlen($derniers_messages->contenu) > 50) echo '...'; ?>
                    </span>
                    <span class="message-time">
                        <?php echo date('d/m H:i', strtotime((string)$derniers_messages->date_heure ?? 'now')); ?>
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
<?php } else if ($discussion['type'] === 'groupe') {
        $groupe = $discussion['groupe'];
        $nb_non_lus = $discussion['nb_non_lus'];
        $derniers_messages = $discussion['derniers_messages'];
        $nb_messages = $discussion['nb_messages'];
?>
    <div class="list-item discussion-item">
        <div class="item-avatar">
            <?php if ($groupe->photo_groupe && $groupe->photo_groupe != 'default.jpg') { ?>
                <img src="../uploads/<?php echo htmlspecialchars($groupe->photo_groupe); ?>" alt="Photo Groupe" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
                    $expediteur = $utilisateurs->xpath("//user[id='{$derniers_messages->id_expediteur}']")[0];
                    $envoye_par_moi = $derniers_messages->id_expediteur == $id_utilisateur;
                    ?>
                    <span class="message-preview">
                        <?php echo $envoye_par_moi ? 'Vous: ' : ($expediteur ? htmlspecialchars($expediteur->prenom . ': ') : ''); ?>
                        <?php echo htmlspecialchars(substr($derniers_messages->contenu, 0, 50)); ?>
                        <?php if (strlen($derniers_messages->contenu) > 50) echo '...'; ?>
                    </span>
                    <span class="message-time">
                        <?php echo date('d/m H:i', strtotime((string)$derniers_messages->date_heure ?? 'now')); ?>
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
<?php }
}
?>
<?php if (empty($discussions)) { ?>
    <div class="empty-state">
        <div class="empty-icon">💬</div>
        <h3>Aucune discussion</h3>
        <p>Commencez à discuter avec vos contacts ou groupes pour voir vos conversations ici.</p>
    </div>
<?php } ?>
</div> 

<!-- Modal Nouvelle Discussion -->
<div id="modalNouvelleDiscussion" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nouvelle Discussion</h3>
            <button type="button" class="modal-close" onclick="fermerModalNouvelleDiscussion()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="search-bar">
                <input type="text" id="rechercheNouvelleDiscussion" placeholder="Rechercher un contact ou groupe...">
            </div>
            <div class="nouvelle-discussion-list">
                <!-- Les contacts et groupes sans messages seront chargés ici -->
            </div>
        </div>
    </div>
</div>

<script src="../js/global.js"></script> 