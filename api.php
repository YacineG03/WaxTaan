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

// Récupérer l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$current_user = $users->xpath("//user[id='$user_id']")[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    var_dump($_POST); // Pour débogage
    var_dump($_POST['member_ids']);
    var_dump($users->xpath("//user[id='$user_id']"));

    switch ($action) {
        case 'update_profile':
            // Mettre à jour le profil
            if (isset($_POST['firstname'], $_POST['lastname'], $_POST['sex'], $_POST['age'], $_POST['phone'])) {
                $current_user->firstname = htmlspecialchars($_POST['firstname']);
                $current_user->lastname = htmlspecialchars($_POST['lastname']);
                $current_user->sex = htmlspecialchars($_POST['sex']);
                $current_user->age = htmlspecialchars($_POST['age']);
                $current_user->phone = htmlspecialchars($_POST['phone']);
                if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $file_name = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
                    $target_file = $upload_dir . $file_name;
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                        $current_user->profile_photo = $file_name;
                    }
                }
                $users->asXML('xmls/users.xml');
            }
            header('Location: views/view.php');
            exit;

        case 'add_contact':
            // Ajouter un contact
            if (isset($_POST['contact_name'], $_POST['contact_phone'])) {
                $contact_name = htmlspecialchars($_POST['contact_name']);
                $contact_phone = htmlspecialchars($_POST['contact_phone']);
                
                // Vérifier si le contact existe déjà pour cet utilisateur
                $existing_contact = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='$contact_phone']")[0];
                
                if ($existing_contact) {
                    // Le contact existe déjà
                    header('Location: views/view.php?error=contact_already_exists');
                    exit;
                }
                
                // Vérifier si le numéro de téléphone correspond à un utilisateur existant
                $user_exists = $users->xpath("//user[phone='$contact_phone']")[0];
                if (!$user_exists) {
                    // L'utilisateur n'existe pas
                    header('Location: views/view.php?error=user_not_found');
                    exit;
                }
                
                // Vérifier que l'utilisateur ne s'ajoute pas lui-même
                if ($contact_phone === $current_user->phone) {
                    header('Location: views/view.php?error=cannot_add_self');
                    exit;
                }
                
                // Ajouter le contact
                $contact = $contacts->addChild('contact');
                $contact->addChild('id', uniqid());
                $contact->addChild('user_id', $user_id);
                $contact->addChild('contact_name', $contact_name);
                $contact->addChild('contact_phone', $contact_phone);
                
                // Sauvegarder le fichier
                $result = $contacts->asXML('xmls/contacts.xml');
                
                if ($result) {
                    header('Location: views/view.php?success=contact_added');
                } else {
                    header('Location: views/view.php?error=add_failed');
                }
            } else {
                header('Location: views/view.php?error=missing_contact_data');
            }
            exit;

        case 'create_group':
            // Créer un groupe
            if (!isset($_POST['member_ids']) || count($_POST['member_ids']) < 2) {
                            header('Location: views/view.php?error=minimum_two_members');
            exit;
            }
            $group = $groups->addChild('group');
            $group->addChild('id', uniqid());
            $group->addChild('name', htmlspecialchars($_POST['group_name']));
            $group->addChild('admin_id', $user_id);
            if (isset($_FILES['group_photo']) && $_FILES['group_photo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $file_name = uniqid() . '_' . basename($_FILES['group_photo']['name']);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['group_photo']['tmp_name'], $target_file)) {
                    $group->addChild('group_photo', $file_name);
                }
            }
            foreach ($_POST['member_ids'] as $member_id) {
                $group->addChild('member_id', htmlspecialchars($member_id));
            }
            $groups->asXML('xmls/groups.xml');
            header('Location: views/view.php');
            exit;

        case 'send_message':
            // Envoyer un message
            if (isset($_POST['recipient'], $_POST['message'], $_POST['recipient_type'])) {
                $message = $messages->addChild('message');
                $message->addChild('id', uniqid());
                $message->addChild('sender_id', $user_id);
                $message->addChild('content', htmlspecialchars($_POST['message']));
                $message->addAttribute('timestamp', date('Y-m-d H:i:s'));
                if ($_POST['recipient_type'] === 'contact') {
                    // Pour les contacts, on stocke le numéro de téléphone du destinataire
                    $message->addChild('recipient', htmlspecialchars($_POST['recipient']));
                } elseif ($_POST['recipient_type'] === 'group') {
                    $message->addChild('recipient_group', htmlspecialchars($_POST['recipient']));
                }
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $file_name = uniqid() . '_' . basename($_FILES['file']['name']);
                    $target_file = $upload_dir . $file_name;
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                        $message->addChild('file', $file_name);
                    }
                }
                $messages->asXML('xmls/messages.xml');
            }
            header('Location: views/view.php?conversation=' . ($_POST['recipient_type'] === 'group' ? 'group:' : 'contact:') . urlencode($_POST['recipient']));
            exit;

        case 'delete_contact':
            // Supprimer un contact
            if (isset($_POST['contact_id'])) {
                $contact_id = htmlspecialchars($_POST['contact_id']);
                
                // Vérifier que le contact existe
                $contact = $contacts->xpath("//contact[id='$contact_id']")[0];
                
                if ($contact) {
                    // Vérifier que l'utilisateur connecté est le propriétaire du contact
                    if ((string)$contact->user_id === $user_id) {
                        // Supprimer le contact
                        $dom = dom_import_simplexml($contact);
                        $dom->parentNode->removeChild($dom);
                        
                        // Sauvegarder le fichier
                        $result = $contacts->asXML('xmls/contacts.xml');
                        
                        if ($result) {
                            header('Location: views/view.php?success=contact_deleted');
                        } else {
                            header('Location: views/view.php?error=delete_failed');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized');
                    }
                } else {
                    header('Location: views/view.php?error=contact_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_contact_id');
            }
            exit;

        case 'delete_group':
            if (isset($_GET['group_id'])) {
                $group = $groups->xpath("//group[id='" . $_GET['group_id'] . "']")[0];
                if ($group && (string)$group->admin_id === $user_id) {
                    $dom = dom_import_simplexml($group);
                    $dom->parentNode->removeChild($dom);
                    $groups->asXML('xmls/groups.xml');
                }
            }
            header('Location: index.php');
            exit;

        case 'remove_member':
            if (isset($_POST['group_id'], $_POST['member_id'])) {
                $group = $groups->xpath("//group[id='" . $_POST['group_id'] . "']")[0];
                if ($group && (string)$group->admin_id === $user_id) {
                    $member_ids = [];
                    foreach ($group->member_id as $member_id) {
                        if ((string)$member_id !== $_POST['member_id']) {
                            $member_ids[] = $member_id;
                        }
                    }
                    unset($group->member_id);
                    foreach ($member_ids as $member_id) {
                        $group->addChild('member_id', $member_id);
                    }
                    $groups->asXML('xmls/groups.xml');
                }
            }
            header('Location: index.php');
            exit;

        case 'add_coadmin':
            if (isset($_POST['group_id'], $_POST['coadmin_id'])) {
                $group = $groups->xpath("//group[id='" . $_POST['group_id'] . "']")[0];
                if ($group && (string)$group->admin_id === $user_id) {
                    $coadmins = explode(',', (string)$group->coadmins);
                    if (!in_array($_POST['coadmin_id'], $coadmins)) {
                        $coadmins[] = $_POST['coadmin_id'];
                        $group->coadmins = implode(',', $coadmins);
                        $groups->asXML('xmls/groups.xml');
                    }
                }
            }
            header('Location: index.php');
            exit;

        case 'leave_group':
            if (isset($_GET['group_id'])) {
                $group = $groups->xpath("//group[id='" . $_GET['group_id'] . "']")[0];
                if ($group) {
                    $member_ids = [];
                    foreach ($group->member_id as $member_id) {
                        if ((string)$member_id !== $user_id) {
                            $member_ids[] = $member_id;
                        }
                    }
                    unset($group->member_id);
                    foreach ($member_ids as $member_id) {
                        $group->addChild('member_id', $member_id);
                    }
                    if ((string)$group->admin_id === $user_id) {
                        $group->admin_id = $member_ids[0] ?? '';
                        unset($group->coadmins);
                    }
                    $groups->asXML('xmls/groups.xml');
                }
            }
            header('Location: index.php');
            exit;

        case 'toggle_notifications':
            if (isset($_GET['group_id'])) {
                $muted_groups = $_SESSION['muted_groups'] ?? '';
                $muted_array = explode(',', $muted_groups);
                $group_id = $_GET['group_id'];
                if (in_array($group_id, $muted_array)) {
                    $muted_array = array_diff($muted_array, [$group_id]);
                } else {
                    $muted_array[] = $group_id;
                }
                $_SESSION['muted_groups'] = implode(',', array_filter($muted_array));
            }
            header('Location: index.php');
            exit;

        case 'delete_message':
            if (isset($_GET['message_id'], $_GET['group_id'])) {
                $message = $messages->xpath("//message[id='" . $_GET['message_id'] . "']")[0];
                $group = $groups->xpath("//group[id='" . $_GET['group_id'] . "']")[0];
                if ($message && $group && (string)$group->admin_id === $user_id) {
                    $dom = dom_import_simplexml($message);
                    $dom->parentNode->removeChild($dom);
                    $messages->asXML('xmls/messages.xml');
                }
            }
            header('Location: views/view.php?conversation=group:' . urlencode($_GET['group_id']));
            exit;

        case 'list_members':
            if (isset($_GET['group_id'])) {
                $group = $groups->xpath("//group[id='" . $_GET['group_id'] . "']")[0];
                if ($group && (string)$group->admin_id === $user_id) {
                    echo "<h2>Membres du groupe : " . htmlspecialchars($group->name) . "</h2>";
                    echo "<ul>";
                    foreach ($group->member_id as $member_id) {
                        $member = $users->xpath("//user[id='$member_id']")[0];
                        echo "<li>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</li>";
                    }
                    echo "</ul>";
                    exit;
                }
            }
            header('Location: index.php');
            exit;
    }
}
?>