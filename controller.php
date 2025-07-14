<?php
require_once 'config.php';

// Récupérer l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$current_user = $users->xpath("//user[id='$user_id']")[0];

// Fonction pour obtenir l'ID utilisateur à partir du numéro de téléphone
function getUserIDByPhone($users, $phone) {
    $user = $users->xpath("//user[phone='$phone']")[0];
    return $user ? (string)$user->id : null;
}

// Fonction pour obtenir le numéro de téléphone à partir de l'ID utilisateur
function getPhoneByUserID($users, $user_id) {
    $user = $users->xpath("//user[id='$user_id']")[0];
    return $user ? (string)$user->phone : null;
}

// Fonction pour compter les nouveaux messages non lus d'un contact
function getUnreadMessageCount($messages, $current_user_phone, $contact_phone) {
    $contact_user_id = getUserIDByPhone($GLOBALS['users'], $contact_phone);
    if (!$contact_user_id) return 0;
    // Messages reçus de ce contact (envoyés par le contact à l'utilisateur connecté)
    $received_messages = $messages->xpath("//message[sender_id='$contact_user_id' and recipient='$current_user_phone']");
    $unread = 0;
    $current_user_id = $GLOBALS['user_id'];
    foreach ($received_messages as $msg) {
        if (!isset($msg->read_by) || !in_array($current_user_id, explode(',', (string)$msg->read_by))) {
            $unread++;
        }
    }
    return $unread;
}

// Récupérer les discussions (contacts et groupes)
$conversations = [];
foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
    $unread_count = getUnreadMessageCount($messages, $current_user->phone, $contact->contact_phone);
    $conversations[] = [
        'type' => 'contact', 
        'id' => (string)$contact->contact_phone, 
        'name' => (string)$contact->contact_name,
        'unread_count' => $unread_count
    ];
}
foreach ($groups->xpath("//group[member_id='$user_id']") as $group) {
    $conversations[] = [
        'type' => 'group', 
        'id' => (string)$group->id, 
        'name' => (string)$group->name,
        'unread_count' => 0 // Pour les groupes, on pourrait implémenter plus tard
    ];
}
?>