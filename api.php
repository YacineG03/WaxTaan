<?php
session_start();

// Gestion robuste de la session pour AJAX
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    if (isset($_GET['action']) && in_array($_GET['action'], ['list_members', 'get_group_members'])) {
        http_response_code(401);
        echo "<p style='color:red;'>Session expirée ou utilisateur non connecté.</p>";
        exit;
    }
    header('Location: connexion/login.php');
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
            $result = $groups->asXML('xmls/groups.xml');
            
            if ($result) {
                header('Location: views/view.php?success=group_created');
            } else {
                header('Location: views/view.php?error=group_creation_failed');
            }
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
            if (isset($_POST['group_id'])) {
                $group_id = htmlspecialchars($_POST['group_id']);
                
                // Vérifier que le groupe existe
                $group = $groups->xpath("//group[id='$group_id']")[0];
                
                if ($group) {
                    // Vérifier que l'utilisateur connecté est l'admin du groupe
                    if ((string)$group->admin_id === $user_id) {
                        // Supprimer le groupe
                    $dom = dom_import_simplexml($group);
                    $dom->parentNode->removeChild($dom);
                        
                        // Sauvegarder le fichier
                        $result = $groups->asXML('xmls/groups.xml');
                        
                        if ($result) {
                            header('Location: views/view.php?success=group_deleted');
                        } else {
                            header('Location: views/view.php?error=group_delete_failed');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized_group_action');
                    }
                } else {
                    header('Location: views/view.php?error=group_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_group_id');
            }
            exit;

        case 'remove_member':
            if (isset($_POST['group_id'], $_POST['member_id'])) {
                $group_id = htmlspecialchars($_POST['group_id']);
                $member_id = htmlspecialchars($_POST['member_id']);
                
                // Vérifier que le groupe existe
                $group = $groups->xpath("//group[id='$group_id']")[0];
                
                if ($group) {
                    // Vérifier que l'utilisateur connecté est admin ou coadmin
                    $is_admin = (string)$group->admin_id === $user_id;
                    $coadmins = isset($group->coadmins) ? explode(',', (string)$group->coadmins) : [];
                    $is_coadmin = in_array($user_id, $coadmins);
                    
                    if ($is_admin || $is_coadmin) {
                        // Vérifier que le membre existe dans le groupe
                        $member_exists = false;
                        foreach ($group->member_id as $group_member_id) {
                            if ((string)$group_member_id === $member_id) {
                                $member_exists = true;
                                break;
                            }
                        }
                        
                        if ($member_exists) {
                            // Retirer le membre du groupe (version sûre)
                            $new_member_ids = [];
                            foreach ($group->member_id as $group_member_id) {
                                if ((string)$group_member_id !== $member_id) {
                                    $new_member_ids[] = (string)$group_member_id;
                                }
                            }
                            // Supprimer tous les <member_id>
                            unset($group->member_id);
                            // Réajouter les membres restants
                            foreach ($new_member_ids as $mid) {
                                $group->addChild('member_id', $mid);
                            }
                            // Si le membre retiré était admin, transférer l'admin à un autre membre
                            if ((string)$group->admin_id === $member_id) {
                                if (!empty($new_member_ids)) {
                                    $group->admin_id = $new_member_ids[0];
                                    // Supprimer les coadmins si l'admin est retiré
                                    unset($group->coadmins);
                                }
                            }
                            // Retirer le membre des coadmins s'il l'était
                            if (isset($group->coadmins)) {
                                $coadmin_list = explode(',', (string)$group->coadmins);
                                $coadmin_list = array_diff($coadmin_list, [$member_id]);
                                if (!empty($coadmin_list)) {
                                    $group->coadmins = implode(',', $coadmin_list);
                                } else {
                                    unset($group->coadmins);
                                }
                            }
                            // Sauvegarder le fichier
                            $result = $groups->asXML('xmls/groups.xml');
                            if ($result) {
                                header('Location: views/view.php?success=member_removed');
                            } else {
                                header('Location: views/view.php?error=member_remove_failed');
                            }
                        } else {
                            header('Location: views/view.php?error=member_not_found');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized_group_action');
                    }
                } else {
                    header('Location: views/view.php?error=group_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_group_data');
            }
            exit;

        case 'add_coadmin':
            if (isset($_POST['group_id'], $_POST['coadmin_id'])) {
                $group_id = htmlspecialchars($_POST['group_id']);
                $coadmin_id = htmlspecialchars($_POST['coadmin_id']);
                
                // Vérifier que le groupe existe
                $group = $groups->xpath("//group[id='$group_id']")[0];
                
                if ($group) {
                    // Vérifier que l'utilisateur connecté est admin
                    if ((string)$group->admin_id === $user_id) {
                        // Vérifier que le coadmin est membre du groupe
                        $is_member = false;
                        foreach ($group->member_id as $member_id) {
                            if ((string)$member_id === $coadmin_id) {
                                $is_member = true;
                                break;
                            }
                        }
                        
                        if ($is_member) {
                            // Ajouter le coadmin
                            $coadmins = isset($group->coadmins) ? explode(',', (string)$group->coadmins) : [];
                            if (!in_array($coadmin_id, $coadmins)) {
                                $coadmins[] = $coadmin_id;
                                $group->coadmins = implode(',', $coadmins);
                                
                                // Sauvegarder le fichier
                                $result = $groups->asXML('xmls/groups.xml');
                                
                                if ($result) {
                                    header('Location: views/view.php?success=coadmin_added');
                                } else {
                                    header('Location: views/view.php?error=coadmin_manage_failed');
                                }
                            } else {
                                header('Location: views/view.php?error=coadmin_already_exists');
                            }
                        } else {
                            header('Location: views/view.php?error=member_not_found');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized_group_action');
                    }
                } else {
                    header('Location: views/view.php?error=group_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_group_data');
            }
            exit;

        case 'remove_coadmin':
            if (isset($_POST['group_id'], $_POST['coadmin_id'])) {
                $group_id = htmlspecialchars($_POST['group_id']);
                $coadmin_id = htmlspecialchars($_POST['coadmin_id']);
                
                // Vérifier que le groupe existe
                $group = $groups->xpath("//group[id='$group_id']")[0];
                
                if ($group) {
                    // Vérifier que l'utilisateur connecté est admin
                    if ((string)$group->admin_id === $user_id) {
                        // Retirer le coadmin
                        if (isset($group->coadmins)) {
                    $coadmins = explode(',', (string)$group->coadmins);
                            $coadmins = array_diff($coadmins, [$coadmin_id]);
                            if (!empty($coadmins)) {
                        $group->coadmins = implode(',', $coadmins);
                            } else {
                                unset($group->coadmins);
                            }
                            
                            // Sauvegarder le fichier
                            $result = $groups->asXML('xmls/groups.xml');
                            
                            if ($result) {
                                header('Location: views/view.php?success=coadmin_removed');
                            } else {
                                header('Location: views/view.php?error=coadmin_manage_failed');
                            }
                        } else {
                            header('Location: views/view.php?error=coadmin_not_found');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized_group_action');
                    }
                } else {
                    header('Location: views/view.php?error=group_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_group_data');
            }
            exit;

        case 'leave_group':
            if (isset($_POST['group_id'])) {
                $group_id = htmlspecialchars($_POST['group_id']);
                // Vérifier que le groupe existe
                $group = $groups->xpath("//group[id='$group_id']")[0];
                if ($group) {
                    // Vérifier que l'utilisateur est membre du groupe
                    $is_member = false;
                    foreach ($group->member_id as $member_id) {
                        if ((string)$member_id === $user_id) {
                            $is_member = true;
                            break;
                        }
                    }
                    if ($is_member) {
                        // Retirer l'utilisateur du groupe (sans créer de <member_id/> vide)
                        $member_ids = [];
                        foreach ($group->member_id as $member_id) {
                            if ((string)$member_id !== $user_id && trim((string)$member_id) !== '') {
                                $member_ids[] = (string)$member_id;
                            }
                        }
                        unset($group->member_id);
                        foreach ($member_ids as $member_id) {
                            $group->addChild('member_id', $member_id);
                        }
                        // Si l'utilisateur était admin, transférer l'admin à un autre membre
                        if ((string)$group->admin_id === $user_id) {
                            if (!empty($member_ids)) {
                                $group->admin_id = $member_ids[0];
                                unset($group->coadmins);
                            } else {
                                // Si plus aucun membre, on peut supprimer le groupe ou laisser l'admin seul (ici on laisse le groupe)
                            }
                        }
                        // Retirer l'utilisateur des coadmins s'il l'était
                        if (isset($group->coadmins)) {
                            $coadmin_list = explode(',', (string)$group->coadmins);
                            $coadmin_list = array_diff($coadmin_list, [$user_id]);
                            if (!empty($coadmin_list)) {
                                $group->coadmins = implode(',', $coadmin_list);
                            } else {
                                unset($group->coadmins);
                            }
                        }
                        $result = $groups->asXML('xmls/groups.xml');
                        if ($result) {
                            header('Location: views/view.php?success=group_left');
                        } else {
                            header('Location: views/view.php?error=group_leave_failed');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized_group_action');
                    }
                } else {
                    header('Location: views/view.php?error=group_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_group_id');
            }
            exit;

        case 'list_members':
            if (isset($_GET['group_id'])) {
                $group_id = htmlspecialchars($_GET['group_id']);
                
                // Vérifier que le groupe existe
                $group = $groups->xpath("//group[id='$group_id']")[0];
                
                if ($group) {
                    // Vérifier que l'utilisateur est membre du groupe
                    $is_member = false;
                    foreach ($group->member_id as $member_id) {
                        if ((string)$member_id === $user_id) {
                            $is_member = true;
                            break;
                        }
                    }
                    
                    if ($is_member) {
                        echo "<div style='padding: 10px;'>";
                        echo "<h4>Membres du groupe : " . htmlspecialchars($group->name) . "</h4>";
                        echo "<div style='max-height: 250px; overflow-y: auto;'>";
                        
                        foreach ($group->member_id as $member_id) {
                            $member = $users->xpath("//user[id='$member_id']")[0];
                            if ($member) {
                                $is_admin = (string)$group->admin_id === $member_id;
                                $coadmins = isset($group->coadmins) ? explode(',', (string)$group->coadmins) : [];
                                $is_coadmin = in_array($member_id, $coadmins);
                                
                                echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                echo "<div>";
                                echo "<strong>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</strong>";
                                echo "<br><small>" . htmlspecialchars($member->phone) . "</small>";
                                echo "</div>";
                                echo "<div>";
                                if ($is_admin) {
                                    echo "<span style='background: #28a745; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;'>Admin</span>";
                                } elseif ($is_coadmin) {
                                    echo "<span style='background: #ffc107; color: black; font-size: 10px; padding: 2px 6px; border-radius: 10px;'>Co-Admin</span>";
                                } else {
                                    echo "<span style='background: #6c757d; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;'>Membre</span>";
                                }
                                echo "</div>";
                                echo "</div>";
                            }
                        }
                        
                        echo "</div>";
                        echo "</div>";
                    } else {
                        echo "<p>Vous n'êtes pas membre de ce groupe.</p>";
                    }
                } else {
                    echo "<p>Groupe introuvable.</p>";
                }
            } else {
                echo "<p>ID du groupe manquant.</p>";
            }
            exit;

        case 'get_group_members':
            if (isset($_GET['group_id'])) {
                $group_id = htmlspecialchars($_GET['group_id']);
                
                // Vérifier que le groupe existe
                $group = $groups->xpath("//group[id='$group_id']")[0];
                
                if ($group) {
                    // Vérifier que l'utilisateur est admin ou coadmin
                    $is_admin = (string)$group->admin_id === $user_id;
                    $coadmins = isset($group->coadmins) ? explode(',', (string)$group->coadmins) : [];
                    $is_coadmin = in_array($user_id, $coadmins);
                    
                    if ($is_admin || $is_coadmin) {
                        echo "<div style='padding: 10px;'>";
                        
                        // Pour la gestion des co-admins
                        if (isset($_GET['action_type']) && $_GET['action_type'] === 'coadmin') {
                            echo "<h4>Gérer les co-admins</h4>";
                            echo "<div style='max-height: 300px; overflow-y: auto;'>";
                            
                            foreach ($group->member_id as $member_id) {
                                if ((string)$member_id !== $user_id) { // Ne pas afficher l'admin principal
                                    $member = $users->xpath("//user[id='$member_id']")[0];
                                    if ($member) {
                                        $is_coadmin = in_array($member_id, $coadmins);
                                        
                                        echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                        echo "<div>";
                                        echo "<strong>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</strong>";
                                        echo "<br><small>" . htmlspecialchars($member->phone) . "</small>";
                                        echo "</div>";
                                        echo "<div>";
                                        if ($is_coadmin) {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='remove_coadmin'>";
                                            echo "<input type='hidden' name='group_id' value='" . htmlspecialchars($group_id) . "'>";
                                            echo "<input type='hidden' name='coadmin_id' value='" . htmlspecialchars($member_id) . "'>";
                                            echo "<button type='submit' style='background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Retirer co-admin</button>";
                                            echo "</form>";
                                        } else {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='add_coadmin'>";
                                            echo "<input type='hidden' name='group_id' value='" . htmlspecialchars($group_id) . "'>";
                                            echo "<input type='hidden' name='coadmin_id' value='" . htmlspecialchars($member_id) . "'>";
                                            echo "<button type='submit' style='background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Ajouter co-admin</button>";
                                            echo "</form>";
                                        }
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                }
                            }
                            
                            echo "</div>";
                        }
                        // Pour le retrait de membres
                        elseif (isset($_GET['action_type']) && $_GET['action_type'] === 'remove') {
                            echo "<h4>Sélectionner un membre à retirer</h4>";
                            echo "<div style='max-height: 300px; overflow-y: auto;'>";
                            
                            foreach ($group->member_id as $member_id) {
                                if ((string)$member_id !== $user_id) { // Ne pas pouvoir se retirer soi-même
                                    $member = $users->xpath("//user[id='$member_id']")[0];
                                    if ($member) {
                                        $is_admin = (string)$group->admin_id === $member_id;
                                        
                                        echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                        echo "<div>";
                                        echo "<strong>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</strong>";
                                        echo "<br><small>" . htmlspecialchars($member->phone) . "</small>";
                                        if ($is_admin) {
                                            echo "<br><small style='color: #dc3545;'>⚠️ Admin principal - ne peut pas être retiré</small>";
                                        }
                                        echo "</div>";
                                        echo "<div>";
                                        if (!$is_admin) {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='remove_member'>";
                                            echo "<input type='hidden' name='group_id' value='" . htmlspecialchars($group_id) . "'>";
                                            echo "<input type='hidden' name='member_id' value='" . htmlspecialchars($member_id) . "'>";
                                            echo "<button type='submit' onclick='return confirm(\"Retirer " . htmlspecialchars($member->firstname . ' ' . $member->lastname) . " du groupe ?\")' style='background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Retirer</button>";
                                            echo "</form>";
                                        }
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                }
                            }
                            
                            echo "</div>";
                        }
                        // Vue par défaut pour la gestion des co-admins
                        else {
                            echo "<h4>Gérer les co-admins</h4>";
                            echo "<div style='max-height: 300px; overflow-y: auto;'>";
                            
                            foreach ($group->member_id as $member_id) {
                                if ((string)$member_id !== $user_id) { // Ne pas afficher l'admin principal
                                    $member = $users->xpath("//user[id='$member_id']")[0];
                                    if ($member) {
                                        $is_coadmin = in_array($member_id, $coadmins);
                                        
                                        echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                        echo "<div>";
                                        echo "<strong>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</strong>";
                                        echo "<br><small>" . htmlspecialchars($member->phone) . "</small>";
                                        echo "</div>";
                                        echo "<div>";
                                        if ($is_coadmin) {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='remove_coadmin'>";
                                            echo "<input type='hidden' name='group_id' value='" . htmlspecialchars($group_id) . "'>";
                                            echo "<input type='hidden' name='coadmin_id' value='" . htmlspecialchars($member_id) . "'>";
                                            echo "<button type='submit' style='background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Retirer co-admin</button>";
                                            echo "</form>";
                                        } else {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='add_coadmin'>";
                                            echo "<input type='hidden' name='group_id' value='" . htmlspecialchars($group_id) . "'>";
                                            echo "<input type='hidden' name='coadmin_id' value='" . htmlspecialchars($member_id) . "'>";
                                            echo "<button type='submit' style='background: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Ajouter co-admin</button>";
                                            echo "</form>";
                                        }
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                }
                            }
                            
                            echo "</div>";
                        }
                        
                        echo "</div>";
                    } else {
                        echo "<p>Vous n'avez pas les permissions pour gérer ce groupe.</p>";
                    }
                } else {
                    echo "<p>Groupe introuvable.</p>";
                }
            } else {
                echo "<p>ID du groupe manquant.</p>";
            }
            exit;

        case 'add_member':
            if (isset($_POST['group_id'], $_POST['new_member_id'])) {
                $group_id = htmlspecialchars($_POST['group_id']);
                $new_member_id = htmlspecialchars($_POST['new_member_id']);
                $group = $groups->xpath("//group[id='$group_id']")[0];
                if ($group) {
                    // Vérifier que l'utilisateur connecté est admin ou coadmin
                    $is_admin = (string)$group->admin_id === $user_id;
                    $coadmins = isset($group->coadmins) ? explode(',', (string)$group->coadmins) : [];
                    $is_coadmin = in_array($user_id, $coadmins);
                    if ($is_admin || $is_coadmin) {
                        // Vérifier que le membre n'est pas déjà dans le groupe
                        $already_member = false;
                        foreach ($group->member_id as $mid) {
                            if ((string)$mid === $new_member_id) {
                                $already_member = true;
                                break;
                            }
                        }
                        if (!$already_member && (string)$group->admin_id !== $new_member_id) {
                            $group->addChild('member_id', $new_member_id);
                            $groups->asXML('xmls/groups.xml');
                            header('Location: views/view.php?success=member_added');
                        } else {
                            header('Location: views/view.php?error=member_already_in_group');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized_group_action');
                    }
                } else {
                    header('Location: views/view.php?error=group_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_group_data');
            }
            exit;
    }
}
?>