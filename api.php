<?php
session_start();

// Gestion robuste de la session pour AJAX
if (!isset($_SESSION['id_utilisateur']) || empty($_SESSION['id_utilisateur'])) {
    if (isset($_GET['action']) && in_array($_GET['action'], ['lister_membres', 'obtenir_membres_groupe'])) {
        http_response_code(401);
        echo "<p style='color:red;'>Session expirée ou utilisateur non connecté.</p>";
        exit;
    }
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    // var_dump($_POST); // Pour débogage
    // var_dump($_POST['ids_membres']);
    // var_dump($utilisateurs->xpath("//user[id='$id_utilisateur']"));

    switch ($action) {
        case 'mettre_a_jour_profil':
            // Mettre à jour le profil
            if (isset($_POST['prenom'], $_POST['nom'], $_POST['sexe'], $_POST['age'], $_POST['telephone'])) {
                $nouveau_telephone = htmlspecialchars($_POST['telephone']);
                $ancien_telephone = (string)$utilisateur_courant->telephone;
                
                // Vérifier si le nouveau numéro de téléphone n'est pas déjà utilisé par un autre utilisateur
                if ($nouveau_telephone !== $ancien_telephone) {
                    $utilisateur_existe = $utilisateurs->xpath("//user[telephone='$nouveau_telephone' and id!='$id_utilisateur']")[0];
                    if ($utilisateur_existe) {
                        header('Location: views/view.php?error=telephone_already_used');
                        exit;
                    }
                }
                
                $utilisateur_courant->prenom = htmlspecialchars($_POST['prenom']);
                $utilisateur_courant->nom = htmlspecialchars($_POST['nom']);
                $utilisateur_courant->sexe = htmlspecialchars($_POST['sexe']);
                $utilisateur_courant->age = htmlspecialchars($_POST['age']);
                $utilisateur_courant->telephone = $nouveau_telephone;
                
                // Gestion de la photo de profil
                if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $nom_fichier = uniqid() . '_' . basename($_FILES['photo_profil']['name']);
                    $fichier_cible = $upload_dir . $nom_fichier;
                    if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $fichier_cible)) {
                        $utilisateur_courant->profile_photo = $nom_fichier;
                    }
                }
                
                // Sauvegarder les modifications
                $resultat = $utilisateurs->asXML('xmls/users.xml');
                
                if ($resultat) {
                    header('Location: views/view.php?success=profile_updated');
                } else {
                    header('Location: views/view.php?error=update_failed');
                }
            } else {
                header('Location: views/view.php?error=missing_profile_data');
            }
            exit;

        case 'ajouter_contact':
            // Ajouter un contact
            if (isset($_POST['nom_contact'], $_POST['telephone_contact'])) {
                $nom_contact = htmlspecialchars($_POST['nom_contact']);
                $telephone_contact = htmlspecialchars($_POST['telephone_contact']);
                
                // Vérifier si le contact existe déjà pour cet utilisateur
                $contact_existant = $contacts->xpath("//contact[user_id='$id_utilisateur' and contact_telephone='$telephone_contact']")[0];
                
                if ($contact_existant) {
                    // Le contact existe déjà
                    header('Location: views/view.php?error=contact_already_exists');
                    exit;
                }
                
                // Vérifier si le numéro de téléphone correspond à un utilisateur existant
                $utilisateur_existe = $utilisateurs->xpath("//user[telephone='$telephone_contact']")[0];
                if (!$utilisateur_existe) {
                    // L'utilisateur n'existe pas
                    header('Location: views/view.php?error=user_not_found');
                    exit;
                }
                
                // Vérifier que l'utilisateur ne s'ajoute pas lui-même
                if ($telephone_contact === $utilisateur_courant->telephone) {
                    header('Location: views/view.php?error=cannot_add_self');
                    exit;
                }
                
                // Ajouter le contact
                $contact = $contacts->addChild('contact');
                $contact->addChild('id', uniqid());
                $contact->addChild('user_id', $id_utilisateur);
                $contact->addChild('contact_name', $nom_contact);
                $contact->addChild('contact_telephone', $telephone_contact);
                
                // Sauvegarder le fichier
                $resultat = $contacts->asXML('xmls/contacts.xml');
                
                if ($resultat) {
                    header('Location: views/view.php?success=contact_added');
                } else {
                    header('Location: views/view.php?error=add_failed');
                }
            } else {
                header('Location: views/view.php?error=missing_contact_data');
            }
            exit;

        case 'creer_groupe':
            // Créer un groupe
            if (!isset($_POST['ids_membres']) || count($_POST['ids_membres']) < 2) {
                            header('Location: views/view.php?error=minimum_two_members');
            exit;
            }
            $groupe = $groupes->addChild('group');
            $groupe->addChild('id', uniqid());
            $groupe->addChild('name', htmlspecialchars($_POST['nom_groupe']));
            $groupe->addChild('id_admin', $id_utilisateur);
            if (isset($_FILES['photo_groupe']) && $_FILES['photo_groupe']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $nom_fichier = uniqid() . '_' . basename($_FILES['photo_groupe']['name']);
                $fichier_cible = $upload_dir . $nom_fichier;
                if (move_uploaded_file($_FILES['photo_groupe']['tmp_name'], $fichier_cible)) {
                    $groupe->addChild('group_photo', $nom_fichier);
                }
            }
            foreach ($_POST['ids_membres'] as $id_membre) {
                $groupe->addChild('member_id', htmlspecialchars($id_membre));
            }
            $resultat = $groupes->asXML('xmls/groups.xml');
            
            if ($resultat) {
                header('Location: views/view.php?success=group_created');
            } else {
                header('Location: views/view.php?error=group_creation_failed');
            }
            exit;

        case 'envoyer_message':
            if (isset($_POST['destinataire'], $_POST['message'], $_POST['type_destinataire'])) {
                $message = $messages->addChild('message');
                $message->addChild('id', uniqid());
                $message->addChild('sender_id', $id_utilisateur);
                if ($_POST['type_destinataire'] === 'contact') {
                    $message->addChild('recipient', htmlspecialchars($_POST['destinataire']));
                } elseif ($_POST['type_destinataire'] === 'groupe') {
                    $message->addChild('recipient_group', htmlspecialchars($_POST['destinataire']));
                }
                $message->addChild('content', htmlspecialchars($_POST['message']));
                if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $nom_fichier = uniqid() . '_' . basename($_FILES['fichier']['name']);
                    $fichier_cible = $upload_dir . $nom_fichier;
                    if (move_uploaded_file($_FILES['fichier']['tmp_name'], $fichier_cible)) {
                        $message->addChild('file', $nom_fichier);
                    }
                }
                $message->addChild('read_by', '');
                $message->addAttribute('timestamp', date('Y-m-d\TH:i:s'));
                $messages->asXML('xmls/messages.xml');
            }
            header('Location: views/view.php?conversation=' . ($_POST['type_destinataire'] === 'groupe' ? 'groupe:' : 'contact:') . urlencode($_POST['destinataire']));
            exit;

        case 'supprimer_contact':
            // Supprimer un contact
            if (isset($_POST['id_contact'])) {
                $id_contact = htmlspecialchars($_POST['id_contact']);
                
                // Vérifier que le contact existe
                $contact = $contacts->xpath("//contact[id='$id_contact']")[0];
                
                if ($contact) {
                    // Vérifier que l'utilisateur connecté est le propriétaire du contact
                    if ((string)$contact->user_id === $id_utilisateur) {
                        // Supprimer le contact
                    $dom = dom_import_simplexml($contact);
                    $dom->parentNode->removeChild($dom);
                        
                        // Sauvegarder le fichier
                        $resultat = $contacts->asXML('xmls/contacts.xml');
                        
                        if ($resultat) {
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

        case 'editer_contact':
            // Éditer un contact
            if (isset($_POST['id_contact'], $_POST['nom_contact'])) {
                $id_contact = htmlspecialchars($_POST['id_contact']);
                $nouveau_nom = htmlspecialchars($_POST['nom_contact']);
                
                // Vérifier que le contact existe
                $contact = $contacts->xpath("//contact[id='$id_contact']")[0];
                
                if ($contact) {
                    // Vérifier que l'utilisateur connecté est le propriétaire du contact
                    if ((string)$contact->user_id === $id_utilisateur) {
                        // Modifier le nom du contact
                        $contact->contact_name = $nouveau_nom;
                        
                        // Sauvegarder le fichier
                        $resultat = $contacts->asXML('xmls/contacts.xml');
                        
                        if ($resultat) {
                            header('Location: views/view.php?success=contact_updated');
                        } else {
                            header('Location: views/view.php?error=update_failed');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized');
                    }
                } else {
                    header('Location: views/view.php?error=contact_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_contact_data');
            }
            exit;

        case 'supprimer_groupe':
            if (isset($_POST['id_groupe'])) {
                $id_groupe = htmlspecialchars($_POST['id_groupe']);
                
                // Vérifier que le groupe existe
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                
                if ($groupe) {
                    // Vérifier que l'utilisateur connecté est l'admin du groupe
                    if ((string)$groupe->id_admin === $id_utilisateur) {
                        // Supprimer le groupe
                    $dom = dom_import_simplexml($groupe);
                    $dom->parentNode->removeChild($dom);
                        
                        // Sauvegarder le fichier
                        $resultat = $groupes->asXML('xmls/groups.xml');
                        
                        if ($resultat) {
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

        case 'retirer_membre':
            if (isset($_POST['id_groupe'], $_POST['id_membre'])) {
                $id_groupe = htmlspecialchars($_POST['id_groupe']);
                $id_membre = htmlspecialchars($_POST['id_membre']);
                
                // Vérifier que le groupe existe
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                
                if ($groupe) {
                    // Vérifier que l'utilisateur connecté est admin ou coadmin
                    $est_admin = (string)$groupe->id_admin === $id_utilisateur;
                    $coadmins = isset($groupe->coadmins) ? explode(',', (string)$groupe->coadmins) : [];
                    $est_coadmin = in_array($id_utilisateur, $coadmins);
                    
                    if ($est_admin || $est_coadmin) {
                        // Vérifier que le membre existe dans le groupe
                        $membre_existe = false;
                        foreach ($groupe->member_id as $id_membre_groupe) {
                            if ((string)$id_membre_groupe === $id_membre) {
                                $membre_existe = true;
                                break;
                            }
                        }
                        
                        if ($membre_existe) {
                            // Retirer le membre du groupe (version sûre)
                            $new_member_ids = [];
                            foreach ($groupe->member_id as $id_membre_groupe) {
                                if ((string)$id_membre_groupe !== $id_membre) {
                                    $new_member_ids[] = (string)$id_membre_groupe;
                                }
                            }
                            // Supprimer tous les <member_id>
                            unset($groupe->member_id);
                            // Réajouter les membres restants
                            foreach ($new_member_ids as $mid) {
                                $groupe->addChild('member_id', $mid);
                            }
                            // Si le membre retiré était admin, transférer l'admin à un autre membre
                            if ((string)$groupe->id_admin === $id_membre) {
                                if (!empty($new_member_ids)) {
                                    $groupe->id_admin = $new_member_ids[0];
                                    // Supprimer les coadmins si l'admin est retiré
                                    unset($groupe->coadmins);
                                }
                            }
                            // Retirer le membre des coadmins s'il l'était
                            if (isset($groupe->coadmins)) {
                                $coadmin_list = explode(',', (string)$groupe->coadmins);
                                $coadmin_list = array_diff($coadmin_list, [$id_membre]);
                                if (!empty($coadmin_list)) {
                                    $groupe->coadmins = implode(',', $coadmin_list);
                                } else {
                                    unset($groupe->coadmins);
                                }
                            }
                            // Sauvegarder le fichier
                            $resultat = $groupes->asXML('xmls/groups.xml');
                            if ($resultat) {
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

        case 'ajouter_coadmin':
            if (isset($_POST['id_groupe'], $_POST['id_coadmin'])) {
                $id_groupe = htmlspecialchars($_POST['id_groupe']);
                $id_coadmin = htmlspecialchars($_POST['id_coadmin']);
                
                // Vérifier que le groupe existe
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                
                if ($groupe) {
                    // Vérifier que l'utilisateur connecté est admin
                    if ((string)$groupe->id_admin === $id_utilisateur) {
                        // Vérifier que le coadmin est membre du groupe
                        $est_membre = false;
                        foreach ($groupe->member_id as $id_membre) {
                            if ((string)$id_membre === $id_coadmin) {
                                $est_membre = true;
                                break;
                            }
                        }
                        
                        if ($est_membre) {
                            // Ajouter le coadmin
                            $coadmins = isset($groupe->coadmins) ? explode(',', (string)$groupe->coadmins) : [];
                            if (!in_array($id_coadmin, $coadmins)) {
                                $coadmins[] = $id_coadmin;
                                $groupe->coadmins = implode(',', $coadmins);
                                
                                // Sauvegarder le fichier
                                $resultat = $groupes->asXML('xmls/groups.xml');
                                
                                if ($resultat) {
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

        case 'retirer_coadmin':
            if (isset($_POST['id_groupe'], $_POST['id_coadmin'])) {
                $id_groupe = htmlspecialchars($_POST['id_groupe']);
                $id_coadmin = htmlspecialchars($_POST['id_coadmin']);
                
                // Vérifier que le groupe existe
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                
                if ($groupe) {
                    // Vérifier que l'utilisateur connecté est admin
                    if ((string)$groupe->id_admin === $id_utilisateur) {
                        // Retirer le coadmin
                        if (isset($groupe->coadmins)) {
                    $coadmins = explode(',', (string)$groupe->coadmins);
                            $coadmins = array_diff($coadmins, [$id_coadmin]);
                            if (!empty($coadmins)) {
                        $groupe->coadmins = implode(',', $coadmins);
                            } else {
                                unset($groupe->coadmins);
                            }
                            
                            // Sauvegarder le fichier
                            $resultat = $groupes->asXML('xmls/groups.xml');
                            
                            if ($resultat) {
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

        case 'quitter_groupe':
            if (isset($_POST['id_groupe'])) {
                $id_groupe = htmlspecialchars($_POST['id_groupe']);
                // Vérifier que le groupe existe
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                if ($groupe) {
                    // Vérifier que l'utilisateur est membre du groupe
                    $est_membre = false;
                    foreach ($groupe->member_id as $id_membre_groupe) {
                        if ((string)$id_membre_groupe === $id_utilisateur) {
                            $est_membre = true;
                            break;
                        }
                    }
                    if ($est_membre) {
                        // Retirer l'utilisateur du groupe (sans créer de <member_id/> vide)
                        $member_ids = [];
                        foreach ($groupe->member_id as $id_membre_groupe) {
                            if ((string)$id_membre_groupe !== $id_utilisateur && trim((string)$id_membre_groupe) !== '') {
                                $member_ids[] = (string)$id_membre_groupe;
                            }
                        }
                        unset($groupe->member_id);
                        foreach ($member_ids as $id_membre_groupe) {
                            $groupe->addChild('member_id', $id_membre_groupe);
                        }
                        // Si l'utilisateur était admin, transférer l'admin à un autre membre
                        if ((string)$groupe->id_admin === $id_utilisateur) {
                            if (!empty($member_ids)) {
                                $groupe->id_admin = $member_ids[0];
                                unset($groupe->coadmins);
                            } else {
                                // Si plus aucun membre, on peut supprimer le groupe ou laisser l'admin seul (ici on laisse le groupe)
                            }
                        }
                        // Retirer l'utilisateur des coadmins s'il l'était
                        if (isset($groupe->coadmins)) {
                            $coadmin_list = explode(',', (string)$groupe->coadmins);
                            $coadmin_list = array_diff($coadmin_list, [$id_utilisateur]);
                            if (!empty($coadmin_list)) {
                                $groupe->coadmins = implode(',', $coadmin_list);
                            } else {
                                unset($groupe->coadmins);
                            }
                        }
                        $resultat = $groupes->asXML('xmls/groups.xml');
                        if ($resultat) {
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

        case 'lister_membres':
            if (isset($_GET['id_groupe'])) {
                $id_groupe = htmlspecialchars($_GET['id_groupe']);
                
                // Vérifier que le groupe existe
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                
                if ($groupe) {
                    // Vérifier que l'utilisateur est membre du groupe
                    $est_membre = false;
                    foreach ($groupe->member_id as $id_membre_groupe) {
                        if ((string)$id_membre_groupe === $id_utilisateur) {
                            $est_membre = true;
                            break;
                        }
                    }
                    
                    if ($est_membre) {
                        echo "<div style='padding: 10px;'>";
                        echo "<h4>Membres du groupe : " . htmlspecialchars($groupe->name) . "</h4>";
                        echo "<div style='max-height: 250px; overflow-y: auto;'>";
                        
                        foreach ($groupe->member_id as $id_membre_groupe) {
                            $membre = $utilisateurs->xpath("//user[id='$id_membre_groupe']")[0];
                            if ($membre) {
                                $est_admin = (string)$groupe->id_admin === $id_membre_groupe;
                                $coadmins = isset($groupe->coadmins) ? explode(',', (string)$groupe->coadmins) : [];
                                $est_coadmin = in_array($id_membre_groupe, $coadmins);
                                
                                echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                echo "<div>";
                                echo "<strong>" . htmlspecialchars($membre->prenom . ' ' . $membre->nom) . "</strong>";
                                echo "<br><small>" . htmlspecialchars($membre->telephone) . "</small>";
                                echo "</div>";
                                echo "<div>";
                                if ($est_admin) {
                                    echo "<span style='background: #28a745; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;'>Admin</span>";
                                } elseif ($est_coadmin) {
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

        case 'obtenir_membres_groupe':
            if (isset($_GET['id_groupe'])) {
                $id_groupe = htmlspecialchars($_GET['id_groupe']);
                
                // Vérifier que le groupe existe
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                
                if ($groupe) {
                    // Vérifier que l'utilisateur est admin ou coadmin
                    $est_admin = (string)$groupe->id_admin === $id_utilisateur;
                    $coadmins = isset($groupe->coadmins) ? explode(',', (string)$groupe->coadmins) : [];
                    $est_coadmin = in_array($id_utilisateur, $coadmins);
                    
                    if ($est_admin || $est_coadmin) {
                        echo "<div style='padding: 10px;'>";
                        
                        // Pour la gestion des co-admins
                        if (isset($_GET['action_type']) && $_GET['action_type'] === 'coadmin') {
                            echo "<h4>Gérer les co-admins</h4>";
                            echo "<div style='max-height: 300px; overflow-y: auto;'>";
                            
                            foreach ($groupe->member_id as $id_membre) {
                                if ((string)$id_membre !== $id_utilisateur) { // Ne pas afficher l'admin principal
                                    $membre = $utilisateurs->xpath("//user[id='$id_membre']")[0];
                                    if ($membre) {
                                        $est_coadmin = in_array($id_membre, $coadmins);
                                        
                                        echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                        echo "<div>";
                                        echo "<strong>" . htmlspecialchars($membre->prenom . ' ' . $membre->nom) . "</strong>";
                                        echo "<br><small>" . htmlspecialchars($membre->telephone) . "</small>";
                                        echo "</div>";
                                        echo "<div>";
                                        if ($est_coadmin) {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='retirer_coadmin'>";
                                            echo "<input type='hidden' name='id_groupe' value='" . htmlspecialchars($id_groupe) . "'>";
                                            echo "<input type='hidden' name='id_coadmin' value='" . htmlspecialchars($id_membre) . "'>";
                                            echo "<button type='submit' style='background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Retirer co-admin</button>";
                                            echo "</form>";
                                        } else {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='ajouter_coadmin'>";
                                            echo "<input type='hidden' name='id_groupe' value='" . htmlspecialchars($id_groupe) . "'>";
                                            echo "<input type='hidden' name='id_coadmin' value='" . htmlspecialchars($id_membre) . "'>";
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
                            
                            foreach ($groupe->member_id as $id_membre) {
                                if ((string)$id_membre !== $id_utilisateur) { // Ne pas pouvoir se retirer soi-même
                                    $membre = $utilisateurs->xpath("//user[id='$id_membre']")[0];
                                    if ($membre) {
                                        $est_admin = (string)$groupe->id_admin === $id_membre;
                                        
                                        echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                        echo "<div>";
                                        echo "<strong>" . htmlspecialchars($membre->prenom . ' ' . $membre->nom) . "</strong>";
                                        echo "<br><small>" . htmlspecialchars($membre->telephone) . "</small>";
                                        if ($est_admin) {
                                            echo "<br><small style='color: #dc3545;'>⚠️ Admin principal - ne peut pas être retiré</small>";
                                        }
                                        echo "</div>";
                                        echo "<div>";
                                        if (!$est_admin) {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='retirer_membre'>";
                                            echo "<input type='hidden' name='id_groupe' value='" . htmlspecialchars($id_groupe) . "'>";
                                            echo "<input type='hidden' name='id_membre' value='" . htmlspecialchars($id_membre) . "'>";
                                            echo "<button type='submit' onclick='return confirm(\"Retirer " . htmlspecialchars($membre->prenom . ' ' . $membre->nom) . " du groupe ?\")' style='background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Retirer</button>";
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
                            
                            foreach ($groupe->member_id as $id_membre) {
                                if ((string)$id_membre !== $id_utilisateur) { // Ne pas afficher l'admin principal
                                    $membre = $utilisateurs->xpath("//user[id='$id_membre']")[0];
                                    if ($membre) {
                                        $est_coadmin = in_array($id_membre, $coadmins);
                                        
                                        echo "<div style='display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee;'>";
                                        echo "<div>";
                                        echo "<strong>" . htmlspecialchars($membre->prenom . ' ' . $membre->nom) . "</strong>";
                                        echo "<br><small>" . htmlspecialchars($membre->telephone) . "</small>";
                                        echo "</div>";
                                        echo "<div>";
                                        if ($est_coadmin) {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='retirer_coadmin'>";
                                            echo "<input type='hidden' name='id_groupe' value='" . htmlspecialchars($id_groupe) . "'>";
                                            echo "<input type='hidden' name='id_coadmin' value='" . htmlspecialchars($id_membre) . "'>";
                                            echo "<button type='submit' style='background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Retirer co-admin</button>";
                                            echo "</form>";
                                        } else {
                                            echo "<form method='post' action='../api.php' style='display: inline;'>";
                                            echo "<input type='hidden' name='action' value='ajouter_coadmin'>";
                                            echo "<input type='hidden' name='id_groupe' value='" . htmlspecialchars($id_groupe) . "'>";
                                            echo "<input type='hidden' name='id_coadmin' value='" . htmlspecialchars($id_membre) . "'>";
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

        case 'ajouter_membre':
            if (isset($_POST['id_groupe'], $_POST['id_nouveau_membre'])) {
                $id_groupe = htmlspecialchars($_POST['id_groupe']);
                $id_nouveau_membre = htmlspecialchars($_POST['id_nouveau_membre']);
                $groupe = $groupes->xpath("//group[id='$id_groupe']")[0];
                if ($groupe) {
                    // Vérifier que l'utilisateur connecté est admin ou coadmin
                    $est_admin = (string)$groupe->id_admin === $id_utilisateur;
                    $coadmins = isset($groupe->coadmins) ? explode(',', (string)$groupe->coadmins) : [];
                    $est_coadmin = in_array($id_utilisateur, $coadmins);
                    if ($est_admin || $est_coadmin) {
                        // Vérifier que le membre n'est pas déjà dans le groupe
                        $already_member = false;
                        foreach ($groupe->member_id as $id_membre_groupe) {
                            if ((string)$id_membre_groupe === $id_nouveau_membre) {
                                $already_member = true;
                                break;
                            }
                        }
                        if (!$already_member && (string)$groupe->id_admin !== $id_nouveau_membre) {
                            $groupe->addChild('member_id', $id_nouveau_membre);
                            $groupes->asXML('xmls/groups.xml');
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

        case 'modifier_contact':
            if (isset($_POST['id_contact'], $_POST['nom_contact'])) {
                $id_contact = htmlspecialchars($_POST['id_contact']);
                $nouveau_nom = htmlspecialchars($_POST['nom_contact']);
                // Vérifier que le contact existe
                $contact = $contacts->xpath("//contact[id='$id_contact']")[0];
                if ($contact) {
                    // Vérifier que l'utilisateur connecté est le propriétaire du contact
                    if ((string)$contact->user_id === $id_utilisateur) {
                        $contact->contact_name = $nouveau_nom;
                        $resultat = $contacts->asXML('xmls/contacts.xml');
                        if ($resultat) {
                            header('Location: views/view.php?success=contact_edited');
                        } else {
                            header('Location: views/view.php?error=edit_failed');
                        }
                    } else {
                        header('Location: views/view.php?error=unauthorized');
                    }
                } else {
                    header('Location: views/view.php?error=contact_not_found');
                }
            } else {
                header('Location: views/view.php?error=missing_contact_data');
            }
            exit;

        case 'send_message':
            if (isset($_POST['message'], $_POST['recipient'], $_POST['recipient_type'])) {
                $message_content = htmlspecialchars($_POST['message']);
                $recipient = htmlspecialchars($_POST['recipient']);
                $recipient_type = htmlspecialchars($_POST['recipient_type']);
                
                // Créer un nouveau message
                $message = $messages->addChild('message');
                $message->addChild('id', uniqid());
                $message->addChild('sender_id', $id_utilisateur);
                $message->addChild('content', $message_content);
                $message->addChild('timestamp', date('Y-m-d\TH:i:s'));
                $message->addChild('read_by', '');
                
                if ($recipient_type === 'contact') {
                    // Message vers un contact (utilise le numéro de téléphone)
                    $message->addChild('recipient', $recipient);
                } elseif ($recipient_type === 'groupe') {
                    // Message vers un groupe
                    $message->addChild('recipient_group', $recipient);
                }
                
                // Gestion des fichiers
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $nom_fichier = uniqid() . '_' . basename($_FILES['file']['name']);
                    $fichier_cible = $upload_dir . $nom_fichier;
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $fichier_cible)) {
                        $message->addChild('file', $nom_fichier);
                    }
                }
                
                // Sauvegarder le message
                $resultat = $messages->asXML('xmls/messages.xml');
                
                if ($resultat) {
                    // Rediriger vers la conversation
                    if ($recipient_type === 'contact') {
                        // Pour les contacts, récupérer l'ID du contact à partir du numéro de téléphone
                        $contact_info = $contacts->xpath("//contact[contact_telephone='$recipient' and user_id='$id_utilisateur']")[0];
                        if ($contact_info) {
                            $redirect_url = 'views/view.php?conversation=contact:' . urlencode($contact_info->id) . '&tab=discussions';
                        } else {
                            $redirect_url = 'views/view.php?tab=discussions';
                        }
                    } else {
                        // Pour les groupes, utiliser l'ID du groupe
                        $redirect_url = 'views/view.php?conversation=groupe:' . urlencode($recipient) . '&tab=discussions';
                    }
                    header('Location: ' . $redirect_url);
                } else {
                    header('Location: views/view.php?error=message_send_failed');
                }
            } else {
                header('Location: views/view.php?error=missing_message_data');
            }
            exit;
    }
}
?>