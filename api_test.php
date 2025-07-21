<?php
session_start();

// Gestion robuste de la session pour AJAX
if (!isset($_SESSION['id_utilisateur']) || empty($_SESSION['id_utilisateur'])) {
    header('Location: connexion/login.php');
    exit;
}

// Charger les données XML avec vérification
$utilisateurs = @simplexml_load_file('xmls/users.xml');
if ($utilisateurs === false) {
    die('Erreur : Impossible de charger users.xml. Vérifiez le fichier ou le chemin.');
}
$contacts = @simplexml_load_file('xmls/contacts.xml');
if ($contacts === false) {
    die('Erreur : Impossible de charger contacts.xml. Vérifiez le fichier ou le chemin.');
}
$groupes = @simplexml_load_file('xmls/groups.xml');
if ($groupes === false) {
    die('Erreur : Impossible de charger groups.xml. Vérifiez le fichier ou le chemin.');
}
$messages = @simplexml_load_file('xmls/messages.xml');
if ($messages === false) {
    die('Erreur : Impossible de charger messages.xml. Vérifiez le fichier ou le chemin.');
}

// Récupérer l'utilisateur connecté
$id_utilisateur = $_SESSION['id_utilisateur'];
$utilisateur_courant = $utilisateurs->xpath("//user[id='$id_utilisateur']")[0];

// Fonction pour obtenir l'ID utilisateur par téléphone
function obtenirIdUtilisateurParTelephone($utilisateurs, $telephone) {
    $utilisateur = $utilisateurs->xpath("//user[telephone='$telephone']")[0];
    return $utilisateur ? (string)$utilisateur->id : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'charger_nouvelle_discussion') {
    // Récupérer les contacts sans messages
    $contacts_sans_messages = [];
    $contacts_utilisateur = $contacts->xpath("//contact[user_id='$id_utilisateur']");
    
    foreach ($contacts_utilisateur as $contact) {
        $utilisateur_contact = $utilisateurs->xpath("//user[telephone='{$contact->contact_telephone}']")[0];
        if ($utilisateur_contact) {
            $id_utilisateur_contact = obtenirIdUtilisateurParTelephone($utilisateurs, $contact->contact_telephone);
            $messages_conversation = $messages->xpath("//message[(sender_id='$id_utilisateur' and recipient='$contact->contact_telephone') or (sender_id='$id_utilisateur_contact' and recipient='$utilisateur_courant->telephone')]");
            
            if (empty($messages_conversation)) {
                $contacts_sans_messages[] = [
                    'id' => $contact->id,
                    'nom' => $contact->contact_name,
                    'telephone' => $contact->contact_telephone,
                    'photo' => $utilisateur_contact->profile_photo
                ];
            }
        }
    }
    
    // Récupérer les groupes sans messages
    $groupes_sans_messages = [];
    foreach ($groupes->group as $groupe) {
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
            $messages_groupe = $messages->xpath("//message[recipient_group='{$groupe->id}']");
            if (empty($messages_groupe)) {
                $groupes_sans_messages[] = [
                    'id' => $groupe->id,
                    'nom' => $groupe->name,
                    'photo' => $groupe->group_photo
                ];
            }
        }
    }
    
    // Afficher la liste
    if (empty($contacts_sans_messages) && empty($groupes_sans_messages)) {
        echo '<div class="empty-state">';
        echo '<div class="empty-icon">💬</div>';
        echo '<h3>Aucun contact ou groupe disponible</h3>';
        echo '<p>Tous vos contacts et groupes ont déjà des discussions.</p>';
        echo '</div>';
    } else {
        // Afficher les contacts
        foreach ($contacts_sans_messages as $contact) {
            echo '<div class="nouvelle-discussion-item">';
            echo '<div class="item-avatar">';
            if ($contact['photo'] && $contact['photo'] != 'default.jpg') {
                echo '<img src="../uploads/' . htmlspecialchars($contact['photo']) . '" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
            } else {
                echo strtoupper(substr($contact['nom'], 0, 1));
            }
            echo '</div>';
            echo '<div class="item-content">';
            echo '<div class="item-name">' . htmlspecialchars($contact['nom']) . '</div>';
            echo '<div class="item-type">Contact</div>';
            echo '</div>';
            echo '<div class="item-actions">';
            echo '<button onclick="demarrerDiscussion(\'contact\', \'' . $contact['id'] . '\')" class="modern-btn btn-primary btn-small">💬 Ouvrir</button>';
            echo '</div>';
            echo '</div>';
        }
        
        // Afficher les groupes
        foreach ($groupes_sans_messages as $groupe) {
            echo '<div class="nouvelle-discussion-item">';
            echo '<div class="item-avatar">';
            if ($groupe['photo'] && $groupe['photo'] != 'default.jpg') {
                echo '<img src="../uploads/' . htmlspecialchars($groupe['photo']) . '" alt="Photo Groupe" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
            } else {
                echo strtoupper(substr($groupe['nom'], 0, 1));
            }
            echo '</div>';
            echo '<div class="item-content">';
            echo '<div class="item-name">' . htmlspecialchars($groupe['nom']) . '</div>';
            echo '<div class="item-type">Groupe</div>';
            echo '</div>';
            echo '<div class="item-actions">';
            echo '<button onclick="demarrerDiscussion(\'groupe\', \'' . $groupe['id'] . '\')" class="modern-btn btn-primary btn-small">💬 Ouvrir</button>';
            echo '</div>';
            echo '</div>';
        }
    }
    exit;
}
?> 