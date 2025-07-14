<div class="profile-section">
    <div class="section-header">
        <h2>Mes Groupes</h2>
        <button type="button" onclick="showCreateGroupForm()" class="modern-btn btn-primary">
            <span>➕</span>
            Créer un Groupe
        </button>
    </div>
    <!-- Formulaire de création caché -->
    <div id="createGroupForm" style="display: none;">
        <form action="../api.php" method="post" enctype="multipart/form-data" class="modern-form">
            <input type="hidden" name="action" value="create_group">
            <div class="form-group">
                <label class="form-label">Nom du groupe</label>
                <input type="text" name="group_name" class="form-input" placeholder="Nom du groupe" required>
            </div>
            <div class="form-group">
                <label class="form-label">Photo du groupe</label>
                <input type="file" name="group_photo" class="form-input" accept="image/*">
            </div>
            <div class="form-group">
                <label class="form-label">Sélectionner les membres</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px;">
                    <?php
                    foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
                        $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                        if ($contact_user) {
                            echo "<label style='display: flex; align-items: center; gap: 8px; padding: 8px; cursor: pointer; border-radius: 6px; transition: background 0.3s ease;' onmouseover='this.style.background=\"var(--bg-secondary)\"' onmouseout='this.style.background=\"transparent\"'>";
                            echo "<input type='checkbox' name='member_ids[]' value='" . htmlspecialchars($contact_user->id) . "' style='margin: 0;'>";
                            echo "<span>" . htmlspecialchars($contact->contact_name) . "</span>";
                            echo "</label>";
                        }
                    }
                    ?>
                </div>
                <small class="form-help">Sélectionnez au moins 2 contacts pour créer un groupe</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="modern-btn btn-primary">
                    <span>🏠</span>
                    Créer le Groupe
                </button>
                <button type="button" onclick="hideCreateGroupForm()" class="modern-btn btn-secondary">
                    <span>❌</span>
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
<div class="modern-list">
<?php 
// Afficher tous les groupes où l'utilisateur est membre OU admin
foreach ($groups->group as $group) {
    $is_member = false;
    foreach ($group->member_id as $mid) {
        if (trim((string)$mid) === trim((string)$user_id)) {
            $is_member = true;
            break;
        }
    }
    $is_admin = trim((string)$group->admin_id) === trim((string)$user_id);
    if (!$is_member && !$is_admin) continue;
    $coadmins = isset($group->coadmins) ? explode(',', (string)$group->coadmins) : [];
    $is_coadmin = in_array(trim((string)$user_id), array_map('trim', $coadmins));
    $can_manage = $is_admin || $is_coadmin;
    $member_ids = [];
    foreach ($group->member_id as $mid) {
        $member_ids[] = trim((string)$mid);
    }
    $admin_id = trim((string)$group->admin_id);
    $all_ids = $member_ids;
    $all_ids[] = $admin_id;
    $unique_ids = array_unique($all_ids);
    $member_count = count($unique_ids);
?>
<div class="list-item">
    <div class="item-avatar">
        <?php if ($group->group_photo && $group->group_photo != 'default.jpg') { ?>
            <img src="../uploads/<?php echo htmlspecialchars($group->group_photo); ?>" alt="Group Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        <?php } else { ?>
            <?php echo strtoupper(substr($group->name, 0, 1)); ?>
        <?php } ?>
    </div>
    <div class="item-content">
        <div class="item-name">
            <?php echo htmlspecialchars($group->name); ?>
            <?php if ($is_admin) { ?>
                <span class="badge badge-success">Admin</span>
            <?php } elseif ($is_coadmin) { ?>
                <span class="badge badge-warning">Co-Admin</span>
            <?php } ?>
        </div>
        <div class="item-meta"><?php echo $member_count; ?> membres</div>
    </div>
    <div class="item-actions">
        <select class="modern-btn btn-secondary btn-small" onchange="handleGroupActionSelect(this, '<?php echo $group->id; ?>')">
            <option value="">⚙️ Actions</option>
            <option value="open_conversation">💬 Ouvrir la conversation</option>
            <option value="list_members">👥 Lister les membres</option>
            <?php if ($can_manage) { ?>
                <option value="manage_coadmins">👑 Gérer les co-admins</option>
                <option value="remove_member">➖ Retirer un membre</option>
                <option value="add_member">➕ Ajouter un membre</option>
            <?php } ?>
            <?php if ($is_admin) { ?>
                <option value="delete_group">🗑️ Supprimer le groupe</option>
            <?php } else { ?>
                <option value="leave_group">🚪 Quitter le groupe</option>
            <?php } ?>
        </select>
    </div>
</div>
<!-- 🔒 Modals -->
<!-- Liste des membres -->
<div id="members-list-<?php echo $group->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Membres du groupe : <?php echo htmlspecialchars($group->name); ?></h3>
        <ul>
            <?php
            foreach ($unique_ids as $member_id) {
                $member = $users->xpath("//user[id='$member_id']")[0];
                if ($member) {
                    $is_admin_member = ($admin_id === $member_id);
                    $coadmins_member = isset($group->coadmins) ? explode(',', (string)$group->coadmins) : [];
                    $is_coadmin_member = in_array($member_id, $coadmins_member);
                    echo "<li><strong>" . htmlspecialchars($member->firstname . ' ' . $member->lastname) . "</strong> ";
                    echo "<small>(" . htmlspecialchars($member->phone) . ")</small> ";
                    if ($is_admin_member) echo "<span style='color:green;'>[Admin]</span>";
                    elseif ($is_coadmin_member) echo "<span style='color:orange;'>[Co-Admin]</span>";
                    else echo "<span style='color:gray;'>[Membre]</span>";
                    echo "</li>";
                }
            }
            ?>
        </ul>
        <button onclick="document.getElementById('members-list-<?php echo $group->id; ?>').style.display='none'" class="modern-btn btn-secondary">Fermer</button>
    </div>
</div>
<!-- Gérer les co-admins -->
<div id="coadmins-modal-<?php echo $group->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Gérer les co-admins : <?php echo htmlspecialchars($group->name); ?></h3>
        <ul>
            <?php foreach ($group->member_id as $member_id) {
                if ($member_id == $group->admin_id) continue;
                $member = $users->xpath("//user[id='$member_id']")[0];
                if ($member) {
                    $is_coadmin_member = isset($group->coadmins) && in_array($member_id, explode(',', (string)$group->coadmins));
                    echo "<li>" . htmlspecialchars($member->firstname . ' ' . $member->lastname);
                    if ($is_coadmin_member) {
                        echo " <form method='post' action='../api.php' style='display:inline;'><input type='hidden' name='action' value='remove_coadmin'><input type='hidden' name='group_id' value='".htmlspecialchars($group->id)."'><input type='hidden' name='coadmin_id' value='".htmlspecialchars($member_id)."'><button type='submit' class='modern-btn btn-danger btn-small'>Retirer co-admin</button></form>";
                    } else {
                        echo " <form method='post' action='../api.php' style='display:inline;'><input type='hidden' name='action' value='add_coadmin'><input type='hidden' name='group_id' value='".htmlspecialchars($group->id)."'><input type='hidden' name='coadmin_id' value='".htmlspecialchars($member_id)."'><button type='submit' class='modern-btn btn-primary btn-small'>Ajouter co-admin</button></form>";
                    }
                    echo "</li>";
                }
            } ?>
        </ul>
        <button onclick="document.getElementById('coadmins-modal-<?php echo $group->id; ?>').style.display='none'" class="modern-btn btn-secondary">Fermer</button>
    </div>
</div>
<!-- Retirer un membre -->
<div id="remove-member-modal-<?php echo $group->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Retirer un membre du groupe : <?php echo htmlspecialchars($group->name); ?></h3>
        <ul>
            <?php foreach ($group->member_id as $member_id) {
                if ($member_id == $group->admin_id || $member_id == $user_id) continue;
                $member = $users->xpath("//user[id='$member_id']")[0];
                if ($member) {
                    echo "<li>" . htmlspecialchars($member->firstname . ' ' . $member->lastname);
                    echo " <form method='post' action='../api.php' style='display:inline;'><input type='hidden' name='action' value='remove_member'><input type='hidden' name='group_id' value='".htmlspecialchars($group->id)."'><input type='hidden' name='member_id' value='".htmlspecialchars($member_id)."'><button type='submit' class='modern-btn btn-danger btn-small'>Retirer</button></form>";
                    echo "</li>";
                }
            } ?>
        </ul>
        <button onclick="document.getElementById('remove-member-modal-<?php echo $group->id; ?>').style.display='none'" class="modern-btn btn-secondary">Fermer</button>
    </div>
</div>
<!-- Supprimer le groupe -->
<div id="delete-group-modal-<?php echo $group->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Supprimer le groupe "<?php echo htmlspecialchars($group->name); ?>" ?</h3>
        <p>Cette action est irréversible.</p>
        <form method="post" action="../api.php">
            <input type="hidden" name="action" value="delete_group">
            <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group->id); ?>">
            <button type="submit" class="modern-btn btn-danger">Confirmer la suppression</button>
            <button type="button" onclick="document.getElementById('delete-group-modal-<?php echo $group->id; ?>').style.display='none'" class="modern-btn btn-secondary">Annuler</button>
        </form>
    </div>
</div>
<!-- Quitter le groupe -->
<div id="leave-group-modal-<?php echo $group->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Quitter le groupe "<?php echo htmlspecialchars($group->name); ?>" ?</h3>
        <form method="post" action="../api.php">
            <input type="hidden" name="action" value="leave_group">
            <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group->id); ?>">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <button type="submit" class="modern-btn btn-danger">Confirmer</button>
            <button type="button" onclick="document.getElementById('leave-group-modal-<?php echo $group->id; ?>').style.display='none'" class="modern-btn btn-secondary">Annuler</button>
        </form>
    </div>
</div>
<!-- Ajouter un membre -->
<div id="add-member-modal-<?php echo $group->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Ajouter un membre au groupe : <?php echo htmlspecialchars($group->name); ?></h3>
        <form method="post" action="../api.php">
            <input type="hidden" name="action" value="add_member">
            <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group->id); ?>">
            <div class="form-group">
                <label for="new_member_id">Sélectionner un contact à ajouter :</label>
                <select name="new_member_id" id="new_member_id" required>
                    <option value="">-- Choisir un contact --</option>
                    <?php
                    foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
                        $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                        if ($contact_user && !in_array((string)$contact_user->id, $unique_ids)) {
                            echo "<option value='" . htmlspecialchars($contact_user->id) . "'>" . htmlspecialchars($contact->contact_name) . " (" . htmlspecialchars($contact->contact_phone) . ")</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="modern-btn btn-primary">Ajouter</button>
            <button type="button" onclick="document.getElementById('add-member-modal-<?php echo $group->id; ?>').style.display='none'" class="modern-btn btn-secondary">Annuler</button>
        </form>
    </div>
</div>
<?php } ?>
<?php if (empty($groups->group)) { ?>
<div class="empty-state">
    <div class="empty-icon">🏠</div>
    <h3>Aucun groupe</h3>
    <p>Créez votre premier groupe pour commencer à discuter en équipe.</p>
</div>
<?php } ?>
</div> 