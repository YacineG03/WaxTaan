<div class="p-4">
    <h2 class="font-semibold">Groupes</h2>
    <form action="api.php" method="post" enctype="multipart/form-data" class="space-y-2">
        <input type="hidden" name="action" value="create_group">
        <input type="text" name="group_name" class="w-full p-2 border rounded" placeholder="Nom du groupe" required>
        <input type="file" name="group_photo" class="w-full p-2 border rounded" accept="image/*">
        <div class="space-y-2 max-h-40 overflow-y-auto border rounded p-2">
            <?php
            foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) {
                $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                if ($contact_user) {
                    echo "<label class='flex items-center'>";
                    echo "<input type='checkbox' name='member_ids[]' value='" . htmlspecialchars($contact_user->id) . "' class='mr-2'>";
                    echo htmlspecialchars($contact->contact_name);
                    echo "</label>";
                }
            }
            ?>
        </div>
        <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded">Créer</button>
    </form>
    <ul class="mt-2">
        <?php foreach ($groups->xpath("//group[member_id='$user_id']") as $group) { ?>
            <li class="p-2">
                <?php $is_admin = (string)$group->admin_id === $user_id; ?>
                <a href="?conversation=group:<?php echo $group->id; ?>" class="hover:underline">
                    <?php if ($group->group_photo) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($group->group_photo); ?>" alt="Group Photo" class="w-10 h-10 rounded-full mr-2 inline-block">
                    <?php } else { ?>
                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-2 inline-block">?</div>
                    <?php } ?>
                    <?php echo htmlspecialchars($group->name); ?>
                </a>
                <?php if ($is_admin) { ?>
                    <a href="api.php?action=delete_group&group_id=<?php echo $group->id; ?>" class="text-red-500 ml-2">Supprimer</a>
                    <!-- <a href="api.php?action=list_members&group_id=<?php echo $group->id; ?>" class="text-blue-500 ml-2">Lister membres</a> -->
                    <form action="api.php" method="post" style="display:inline;" class="ml-2">
                        <input type="hidden" name="action" value="remove_member">
                        <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                        <select name="member_id" required>
                            <?php foreach ($group->member_id as $member_id) {
                                $member = $users->xpath("//user[id='$member_id']")[0];
                                $contact_name = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='{$member->phone}']")[0]->contact_name ?? $member->firstname . ' ' . $member->lastname;
                                if ($member_id != $user_id) {
                                    echo "<option value='{$member_id}'>" . htmlspecialchars($contact_name) . "</option>";
                                }
                            } ?>
                        </select>
                        <button type="submit" class="p-1 bg-red-500 text-white rounded">Retirer</button>
                    </form>
                    <form action="api.php" method="post" style="display:inline;" class="ml-2">
                        <input type="hidden" name="action" value="add_coadmin">
                        <input type="hidden" name="group_id" value="<?php echo $group->id; ?>">
                        <select name="coadmin_id" required>
                            <?php foreach ($group->member_id as $member_id) {
                                $member = $users->xpath("//user[id='$member_id']")[0];
                                $contact_name = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='{$member->phone}']")[0]->contact_name ?? $member->firstname . ' ' . $member->lastname;
                                if ($member_id != $user_id && !in_array($member_id, explode(',', $group->coadmins ?? ''))) {
                                    echo "<option value='{$member_id}'>" . htmlspecialchars($contact_name) . "</option>";
                                }
                            } ?>
                        </select>
                        <button type="submit" class="p-1 bg-green-500 text-white rounded">Ajouter Co-Admin</button>
                    </form>
                <?php } ?>
                <a href="api.php?action=leave_group&group_id=<?php echo $group->id; ?>" class="text-yellow-500 ml-2">Quitter</a>
                <!-- <a href="api.php?action=toggle_notifications&group_id=<?php echo $group->id; ?>" class="text-purple-500 ml-2">
                     <?php //echo in_array($group->id, explode(',', $_SESSION['muted_groups'] ?? '')) ? 'Activer notifications' : 'Désactiver notifications'; ?> 
                </a> -->
                <!-- Liste des participants -->
                <div class="mt-2">
                    <h3 class="font-medium">Participants :</h3>
                    <ul class="ml-4">
                        <?php foreach ($group->member_id as $member_id) {
                            $member = $users->xpath("//user[id='$member_id']")[0];
                            $contact_name = $contacts->xpath("//contact[user_id='$user_id' and contact_phone='{$member->phone}']")[0]->contact_name ?? $member->firstname . ' ' . $member->lastname;
                            echo "<li>" . htmlspecialchars($contact_name) . "</li>";
                        } ?>
                    </ul>
                </div>
            </li>
        <?php } ?>
    </ul>
</div>