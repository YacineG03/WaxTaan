<?php
require_once 'config.php';

// Récupérer l'utilisateur connecté
$id_utilisateur = $_SESSION['id_utilisateur'];
$resultat_utilisateur = $utilisateurs->xpath("//user[id='$id_utilisateur']");
$utilisateur_courant = $resultat_utilisateur ? $resultat_utilisateur[0] : null;

// Fonction pour obtenir l'ID utilisateur à partir du numéro de téléphone
function obtenirIdUtilisateurParTelephone($utilisateurs, $telephone) {
    $utilisateur = $utilisateurs->xpath("//user[telephone='$telephone']")[0];
    return $utilisateur ? (string)$utilisateur->id : null;
}

// Fonction pour obtenir le numéro de téléphone à partir de l'ID utilisateur
function obtenirTelephoneParIdUtilisateur($utilisateurs, $id_utilisateur) {
    $utilisateur = $utilisateurs->xpath("//user[id='$id_utilisateur']")[0];
    return $utilisateur ? (string)$utilisateur->telephone : null;
}

// Fonction pour compter les nouveaux messages non lus d'un contact
function compterMessagesNonLus($messages, $telephone_utilisateur_courant, $telephone_contact) {
    $id_utilisateur_contact = obtenirIdUtilisateurParTelephone($GLOBALS['utilisateurs'], $telephone_contact);
    if (!$id_utilisateur_contact) return 0;
    // Messages reçus de ce contact (envoyés par le contact à l'utilisateur connecté)
    $messages_recus = $messages->xpath("//message[id_expediteur='$id_utilisateur_contact' and destinataire='$telephone_utilisateur_courant']");
    $non_lus = 0;
    $id_utilisateur_courant = $GLOBALS['id_utilisateur'];
    foreach ($messages_recus as $msg) {
        if (!isset($msg->lus_par) || !in_array($id_utilisateur_courant, explode(',', (string)$msg->lus_par))) {
            $non_lus++;
        }
    }
    return $non_lus;
}

// Récupérer les discussions (contacts et groupes)
$conversations = [];
if ($utilisateur_courant) {
    foreach ($contacts->xpath("//contact[id_utilisateur='$id_utilisateur']") as $contact) {
        $nb_non_lus = compterMessagesNonLus($messages, $utilisateur_courant->telephone, $contact->telephone_contact);
        $conversations[] = [
            'type' => 'contact', 
            'id' => (string)$contact->telephone_contact, 
            'nom' => (string)$contact->nom_contact,
            'nb_non_lus' => $nb_non_lus
        ];
    }
    foreach ($groupes->xpath("//group[id_membre='$id_utilisateur']") as $groupe) {
        $conversations[] = [
            'type' => 'groupe', 
            'id' => (string)$groupe->id, 
            'nom' => (string)$groupe->name,
            'nb_non_lus' => 0 // Pour les groupes, on pourrait implémenter plus tard
        ];
    }
}
?>