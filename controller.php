<?php
require_once 'config.php';

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