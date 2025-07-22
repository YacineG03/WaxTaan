<?php
require_once 'config.php';

// Récupérer l'utilisateur connecté
$id_utilisateur = $_SESSION['id_utilisateur'];
$utilisateur_courant = $utilisateurs->xpath("//user[id='$id_utilisateur']")[0];

// Fonction utilitaire pour obtenir l'ID utilisateur à partir du téléphone
function obtenirIdUtilisateurParTelephone($utilisateurs, $telephone) {
    $utilisateur = $utilisateurs->xpath("//user[telephone='$telephone']")[0];
    return $utilisateur ? (string)$utilisateur->id : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    exit;
}

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
                if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $nom_fichier = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
                    $fichier_cible = $upload_dir . $nom_fichier;
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $fichier_cible)) {
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
                $contact_existant = $contacts->xpath("//contact[id_utilisateur='$id_utilisateur' and telephone_contact='$telephone_contact']")[0];
                
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
                $contact->addChild('id_utilisateur', $id_utilisateur);
                $contact->addChild('nom_contact', $nom_contact);
                $contact->addChild('telephone_contact', $telephone_contact);
                
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
            // Ajouter coadmins si fournis (optionnel)
            if (isset($_POST['coadmins']) && !empty($_POST['coadmins'])) {
                $groupe->addChild('coadmins', htmlspecialchars($_POST['coadmins']));
            }
            // Ajouter la photo de groupe si fournie (optionnel)
            if (isset($_FILES['photo_groupe']) && $_FILES['photo_groupe']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $nom_fichier = uniqid() . '_' . basename($_FILES['photo_groupe']['name']);
                $fichier_cible = $upload_dir . $nom_fichier;
                if (move_uploaded_file($_FILES['photo_groupe']['tmp_name'], $fichier_cible)) {
                    $groupe->addChild('photo_groupe', $nom_fichier);
                }
            }
            // Ajouter les membres
            foreach ($_POST['ids_membres'] as $id_membre) {
                $groupe->addChild('id_membre', htmlspecialchars($id_membre));
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
                $message->addChild('id_expediteur', $id_utilisateur);
                if ($_POST['type_destinataire'] === 'contact') {
                    $message->addChild('destinataire', htmlspecialchars($_POST['destinataire']));
                } elseif ($_POST['type_destinataire'] === 'groupe') {
                    $message->addChild('groupe_destinataire', htmlspecialchars($_POST['destinataire']));
                }
                $message->addChild('contenu', htmlspecialchars($_POST['message']));
                if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $nom_fichier = uniqid() . '_' . basename($_FILES['fichier']['name']);
                    $fichier_cible = $upload_dir . $nom_fichier;
                    if (move_uploaded_file($_FILES['fichier']['tmp_name'], $fichier_cible)) {
                        $message->addChild('fichier', $nom_fichier);
                    }
                }
                $message->addChild('lus_par', '');
                $message->addChild('date_heure', date('Y-m-d\TH:i:s'));
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
                    if ((string)$contact->id_utilisateur === $id_utilisateur) {
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
                    if ((string)$contact->id_utilisateur === $id_utilisateur) {
                        // Modifier le nom du contact
                        $contact->nom_contact = $nouveau_nom;
                        
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
                        foreach ($groupe->id_membre as $id_membre_groupe) {
                            if ((string)$id_membre_groupe === $id_membre) {
                                $membre_existe = true;
                                break;
                            }
                        }
                        
                        if ($membre_existe) {
                            // Retirer le membre du groupe (version sûre)
                            $new_member_ids = [];
                            foreach ($groupe->id_membre as $id_membre_groupe) {
                                if ((string)$id_membre_groupe !== $id_membre) {
                                    $new_member_ids[] = (string)$id_membre_groupe;
                                }
                            }
                            // Supprimer tous les <id_membre>
                            unset($groupe->id_membre);
                            // Réajouter les membres restants
                            foreach ($new_member_ids as $mid) {
                                $groupe->addChild('id_membre', $mid);
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
                        foreach ($groupe->id_membre as $id_membre) {
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
                    foreach ($groupe->id_membre as $id_membre_groupe) {
                        if ((string)$id_membre_groupe === $id_utilisateur) {
                            $est_membre = true;
                            break;
                        }
                    }
                    if ($est_membre) {
                        // Retirer l'utilisateur du groupe (sans créer de <id_membre/> vide)
                        $member_ids = [];
                        foreach ($groupe->id_membre as $id_membre_groupe) {
                            if ((string)$id_membre_groupe !== $id_utilisateur && trim((string)$id_membre_groupe) !== '') {
                                $member_ids[] = (string)$id_membre_groupe;
                            }
                        }
                        unset($groupe->id_membre);
                        foreach ($member_ids as $id_membre_groupe) {
                            $groupe->addChild('id_membre', $id_membre_groupe);
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
                    foreach ($groupe->id_membre as $id_membre_groupe) {
                        if ((string)$id_membre_groupe === $id_utilisateur) {
                            $est_membre = true;
                            break;
                        }
                    }
                    
                    if ($est_membre) {
                        echo "<div style='padding: 10px;'>";
                        echo "<h4>Membres du groupe : " . htmlspecialchars($groupe->name) . "</h4>";
                        echo "<div style='max-height: 250px; overflow-y: auto;'>";
                        
                        foreach ($groupe->id_membre as $id_membre_groupe) {
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
                            
                            foreach ($groupe->id_membre as $id_membre) {
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
                            
                            foreach ($groupe->id_membre as $id_membre) {
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
                            
                            foreach ($groupe->id_membre as $id_membre) {
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
                        foreach ($groupe->id_membre as $id_membre_groupe) {
                            if ((string)$id_membre_groupe === $id_nouveau_membre) {
                                $already_member = true;
                                break;
                            }
                        }
                        if (!$already_member && (string)$groupe->id_admin !== $id_nouveau_membre) {
                            $groupe->addChild('id_membre', $id_nouveau_membre);
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
                    if ((string)$contact->id_utilisateur === $id_utilisateur) {
                        $contact->nom_contact = $nouveau_nom;
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
                
                // Créer un nouveau message dans l'ordre du XSD
                $message = $messages->addChild('message');
                $message->addChild('id', uniqid());
                $message->addChild('id_expediteur', $id_utilisateur);
                if ($recipient_type === 'contact') {
                    $message->addChild('destinataire', $recipient);
                } elseif ($recipient_type === 'groupe') {
                    $message->addChild('groupe_destinataire', $recipient);
                }
                $message->addChild('contenu', $message_content);
                // Gestion des fichiers
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    $nom_fichier = uniqid() . '_' . basename($_FILES['file']['name']);
                    $fichier_cible = $upload_dir . $nom_fichier;
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $fichier_cible)) {
                        $message->addChild('fichier', $nom_fichier);
                    }
                }
                $message->addChild('lus_par', '');
                $message->addChild('date_heure', date('Y-m-d\TH:i:s'));
                
                // Sauvegarder le message
                $resultat = $messages->asXML('xmls/messages.xml');
                
                if ($resultat) {
                    // Rediriger vers la conversation
                    if ($recipient_type === 'contact') {
                        // Pour les contacts, récupérer l'ID du contact à partir du numéro de téléphone
                        $contact_info = $contacts->xpath("//contact[telephone_contact='$recipient' and id_utilisateur='$id_utilisateur']")[0];
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
            
        case 'charger_nouvelle_discussion':
            // Récupérer les contacts sans messages
            $contacts_sans_messages = [];
            $contacts_utilisateur = $contacts->xpath("//contact[id_utilisateur='$id_utilisateur']");
            
            foreach ($contacts_utilisateur as $contact) {
                $utilisateur_contact = $utilisateurs->xpath("//user[telephone='{$contact->telephone_contact}']")[0];
                if ($utilisateur_contact) {
                    $id_utilisateur_contact = obtenirIdUtilisateurParTelephone($utilisateurs, $contact->telephone_contact);
                    $messages_conversation = $messages->xpath("//message[(id_expediteur='$id_utilisateur' and destinataire='$contact->telephone_contact') or (id_expediteur='$id_utilisateur_contact' and destinataire='$utilisateur_courant->telephone')]");
                    
                    if (empty($messages_conversation)) {
                        $contacts_sans_messages[] = [
                            'id' => $contact->id,
                            'nom' => $contact->nom_contact,
                            'telephone' => $contact->telephone_contact,
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
                foreach ($groupe->id_membre as $id_membre) {
                    if ((string)$id_membre === $id_utilisateur) {
                        $est_membre = true;
                        break;
                    }
                }
                
                if ($est_admin || $est_coadmin || $est_membre) {
                    $messages_groupe = $messages->xpath("//message[groupe_destinataire='{$groupe->id}']");
                    if (empty($messages_groupe)) {
                        $groupes_sans_messages[] = [
                            'id' => $groupe->id,
                            'nom' => $groupe->name,
                            'photo' => $groupe->photo_groupe
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

        case 'supprimer_discussion':
            $type = $_POST['type'] ?? '';
            $id = $_POST['id'] ?? '';
            $scope = $_POST['scope'] ?? 'self'; // 'self' ou 'all'
            $modif = false;

            if ($type === 'contact' && $id) {
                // Récupérer le téléphone du contact
                $contact = $contacts->xpath("//contact[id='$id']")[0];
                if ($contact) {
                    $tel_contact = (string)$contact->telephone_contact;
                    // Supprimer tous les messages entre l'utilisateur courant et ce contact
                    foreach ($messages->message as $msg) {
                        if (
                            ($msg->id_expediteur == $id_utilisateur && $msg->destinataire == $tel_contact) ||
                            ($msg->id_expediteur == obtenirIdUtilisateurParTelephone($utilisateurs, $tel_contact) && $msg->destinataire == $utilisateur_courant->telephone)
                        ) {
                            $dom = dom_import_simplexml($msg);
                            $dom->parentNode->removeChild($dom);
                            $modif = true;
                        }
                    }
                }
            } elseif ($type === 'groupe' && $id) {
                // Vérifier si l'utilisateur est admin du groupe
                $groupe = $groupes->xpath("//group[id='$id']")[0];
                $is_admin = $groupe && ((string)$groupe->id_admin === $id_utilisateur);
                foreach ($messages->message as $msg) {
                    if (isset($msg->groupe_destinataire) && $msg->groupe_destinataire == $id) {
                        if ($scope === 'all' && $is_admin) {
                            // Admin : suppression pour tout le monde
                            $dom = dom_import_simplexml($msg);
                            $dom->parentNode->removeChild($dom);
                            $modif = true;
                        } elseif ($scope === 'self' || !$is_admin) {
                            // Suppression pour soi : on ajoute l'utilisateur courant à lus_par (ou on retire le message de l'affichage côté vue)
                            // Ici, on supprime le message du XML pour l'utilisateur courant uniquement si tu veux une vraie suppression (sinon, il faut gérer côté affichage)
                            // Pour la simplicité, on supprime du XML pour l'utilisateur courant
                            if ($msg->id_expediteur == $id_utilisateur) {
                                $dom = dom_import_simplexml($msg);
                                $dom->parentNode->removeChild($dom);
                                $modif = true;
                            } else {
                                // Pour les messages reçus, on peut ajouter l'utilisateur à lus_par pour ne plus les afficher
                                $lus_par = isset($msg->lus_par) ? (string)$msg->lus_par : '';
                                $lus_arr = $lus_par ? explode(',', $lus_par) : [];
                                if (!in_array($id_utilisateur, $lus_arr)) {
                                    $lus_arr[] = $id_utilisateur;
                                    $msg->lus_par = implode(',', array_unique($lus_arr));
                                    $modif = true;
                                }
                            }
                        }
                    }
                }
            }
            if ($modif) {
                $messages->asXML('xmls/messages.xml');
                header('Location: views/view.php?tab=discussions&success=discussion_deleted');
            } else {
                header('Location: views/view.php?tab=discussions&error=discussion_delete_failed');
            }
            exit;
}
?>