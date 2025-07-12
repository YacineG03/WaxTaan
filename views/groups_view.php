<div class="p-4 groups-section">
    <h2>Groupes</h2>
    
    <form action="api.php" method="post" enctype="multipart/form-data" class="space-y-2">
        <input type="hidden" name="action" value="create_group">
        <input type="text" name="group_name" placeholder="Nom du groupe" required>
        <div class="file-upload">
            <input type="file" name="group_photo" accept="image/*" id="group_photo">
            <label for="group_photo" class="file-upload-label">
                📷 Photo du groupe
            </label>
        </div>
        
        <div class="max-h-40 overflow-y-auto">
            <h3>Sélectionner les membres :</h3>
            <?php
            foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
                $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                if ($contact_user) {
                    echo "<label class='member-select'>";
                    echo "<input type='checkbox' name='member_ids[]' value='" . htmlspecialchars($contact_user->id) . "'>";
                    echo "<span>" . htmlspecialchars($contact->contact_name) . "</span>";
                    echo "</label>";
                }
            }
            ?>
        </div>
        <button type="submit">Créer le Groupe</button>
    </form>
    
    <ul class="mt-4">
        <?php foreach ($groups->xpath("//group[member_id='$user_id']") as $group) { ?>
            <li class="group-item">
                <div class="group-header">
                    <a href="?conversation=group:<?php echo $group->id; ?>" class="group-link">
                        <?php if ($group->group_photo && $group->group_photo != 'default.jpg') { ?>
                            <img src="uploads/<?php echo htmlspecialchars($group->group_photo); ?>" alt="Group Photo" class="group-photo">
                        <?php } else { ?>
                            <div class="group-photo bg-gray-300">
                                <?php echo strtoupper(substr($group->name, 0, 1)); ?>
                            </div>
                        <?php } ?>
                        <span class="group-name"><?php echo htmlspecialchars($group->name); ?></span>
                    </a>
                    
                    <?php $is_admin = (string)$group->admin_id === $user_id; ?>
                    <?php if ($is_admin) { ?>
                        <span class="admin-badge">Admin</span>
                    <?php } ?>
                </div>
                
                <?php if ($is_admin) { ?>
                    <div class="admin-actions">
                        <a href="api.php?action=delete_group&group_id=<?php echo $group->id; ?>" class="text-red-500">Supprimer</a>
                        
                        <form action="api.php" method="post" style="display:inline;">
                            <input type="hidden" name="action" value="remove_member">
                            <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                            <select name="member_id" required>
                                <option value="">Retirer un membre</option>
                                <?php foreach ($group->member_id as $member_id) {
                                    $member = $users->xpath("//user[id='$member_id']")[0];
                                    $contact_name = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='{$member->phone}']")[0]->contact_name ?? $member->firstname . ' ' . $member->lastname;
                                    if ($member_id != $user_id) {
                                        echo "<option value='{$member_id}'>" . htmlspecialchars($contact_name) . "</option>";
                                    }
                                } ?>
                            </select>
                            <button type="submit" class="bg-red-500">Retirer</button>
                        </form>
                        
                        <form action="api.php" method="post" style="display:inline;">
                            <input type="hidden" name="action" value="add_coadmin">
                            <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                            <select name="coadmin_id" required>
                                <option value="">Ajouter Co-Admin</option>
                                <?php foreach ($group->member_id as $member_id) {
                                    $member = $users->xpath("//user[id='$member_id']")[0];
                                    $contact_name = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='{$member->phone}']")[0]->contact_name ?? $member->firstname . ' ' . $member->lastname;
                                    if ($member_id != $user_id && !in_array($member_id, explode(',', $group->coadmins ?? ''))) {
                                        echo "<option value='{$member_id}'>" . htmlspecialchars($contact_name) . "</option>";
                                    }
                                } ?>
                            </select>
                            <button type="submit" class="bg-green-500">Ajouter</button>
                        </form>
                    </div>
                <?php } ?>
                
                <a href="api.php?action=leave_group&group_id=<?php echo $group->id; ?>" class="text-yellow-500">Quitter</a>
                
                <div class="participants-list">
                    <h3>Participants :</h3>
                    <ul>
                        <?php foreach ($group->member_id as $member_id) {
                            $member = $users->xpath("//user[id='$member_id']")[0];
                            $contact_name = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='{$member->phone}']")[0]->contact_name ?? $member->firstname . ' ' . $member->lastname;
                            echo "<li>" . htmlspecialchars($contact_name);
                            if ($member_id == $group->admin_id) echo " (Admin)";
                            echo "</li>";
                        } ?>
                    </ul>
                    <div class="member-count"><?php echo count($group->member_id); ?> membres</div>
                </div>
            </li>
        <?php } ?>
    </ul>
</div>
