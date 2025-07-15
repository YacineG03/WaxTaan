<div class="profile-section">
    <h2>Mes Discussions</h2>
    <p style="color: var(--text-muted); margin-bottom: 16px;">Consultez vos conversations avec vos contacts et groupes</p>
</div>
<div class="search-bar">
    <input type="text" id="searchDiscussions" placeholder="Rechercher une discussion...">
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchDiscussions');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = searchInput.value.toLowerCase();
            document.querySelectorAll('.discussion-item').forEach(function(item) {
                const name = item.textContent.toLowerCase();
                item.style.display = name.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>
<div class="modern-list">
<?php
// Récupérer tous les contacts avec leurs messages
$user_contacts = $contacts->xpath("//contact[user_id='$user_id']");
$discussions = [];
// Discussions avec contacts
foreach ($user_contacts as $contact) {
    $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
    if ($contact_user) {
        $unread_count = getUnreadMessageCount($messages, $current_user->phone, $contact->contact_phone);
        $contact_user_id = getUserIDByPhone($users, $contact->contact_phone);
        $conversation_messages = $messages->xpath("//message[(sender_id='$user_id' and recipient='$contact->contact_phone') or (sender_id='$contact_user_id' and recipient='$current_user->phone')]");
        if (!empty($conversation_messages)) {
            $latest_message = end($conversation_messages);
            $discussions[] = [
                'type' => 'contact',
                'contact' => $contact,
                'contact_user' => $contact_user,
                'unread_count' => $unread_count,
                'latest_message' => $latest_message,
                'message_count' => count($conversation_messages)
            ];
        } else {
            $discussions[] = [
                'type' => 'contact',
                'contact' => $contact,
                'contact_user' => $contact_user,
                'unread_count' => 0,
                'latest_message' => null,
                'message_count' => 0
            ];
        }
    }
}
// Discussions de groupes
$user_groups = [];
foreach ($groups->group as $group) {
    // Vérifie si l'utilisateur est admin, coadmin ou membre
    $is_admin = ((string)$group->admin_id === $user_id);
    $is_coadmin = false;
    if (isset($group->coadmin_id)) {
        foreach ($group->coadmin_id as $coadmin_id) {
            if ((string)$coadmin_id === $user_id) {
                $is_coadmin = true;
                break;
            }
        }
    }
    $is_member = false;
    foreach ($group->member_id as $member_id) {
        if ((string)$member_id === $user_id) {
            $is_member = true;
            break;
        }
    }
    if ($is_admin || $is_coadmin || $is_member) {
        $user_groups[] = $group;
    }
}
foreach ($user_groups as $group) {
    $group_messages = $messages->xpath("//message[recipient_group='{$group->id}']");
    if (!empty($group_messages)) {
        $latest_message = end($group_messages);
        $discussions[] = [
            'type' => 'group',
            'group' => $group,
            'unread_count' => 0, 
            'latest_message' => $latest_message,
            'message_count' => count($group_messages)
        ];
    } else {
        $discussions[] = [
            'type' => 'group',
            'group' => $group,
            'unread_count' => 0,
            'latest_message' => null,
            'message_count' => 0
        ];
    }
}
// Trier les discussions par date du dernier message (plus récent en premier)
usort($discussions, function($a, $b) {
    if ($a['latest_message'] && $b['latest_message']) {
        return strtotime($b['latest_message']['timestamp']) - strtotime($a['latest_message']['timestamp']);
    } elseif ($a['latest_message']) {
        return -1;
    } elseif ($b['latest_message']) {
        return 1;
    }
    return 0;
});
foreach ($discussions as $discussion) {
    if ($discussion['type'] === 'contact') {
        $contact = $discussion['contact'];
        $contact_user = $discussion['contact_user'];
        $unread_count = $discussion['unread_count'];
        $latest_message = $discussion['latest_message'];
        $message_count = $discussion['message_count'];
?>
    <div class="list-item discussion-item">
        <div class="item-avatar">
            <?php if ($contact_user->profile_photo && $contact_user->profile_photo != 'default.jpg') { ?>
                <img src="../uploads/<?php echo htmlspecialchars($contact_user->profile_photo); ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php } else { ?>
                <?php echo strtoupper(substr($contact->contact_name, 0, 1)); ?>
            <?php } ?>
        </div>
        <div class="item-content">
            <div class="item-name">
                <?php echo htmlspecialchars($contact->contact_name); ?>
                <?php if ($unread_count > 0) { ?>
                    <span class="unread-badge"><?php echo $unread_count; ?></span>
                <?php } ?>
            </div>
            <div class="item-meta">
                <?php if ($latest_message) { ?>
                    <?php 
                    $sender = $users->xpath("//user[id='{$latest_message->sender_id}']")[0];
                    $is_sent_by_me = $latest_message->sender_id == $user_id;
                    ?>
                    <span class="message-preview">
                        <?php echo $is_sent_by_me ? 'Vous: ' : ''; ?>
                        <?php echo htmlspecialchars(substr($latest_message->content, 0, 50)); ?>
                        <?php if (strlen($latest_message->content) > 50) echo '...'; ?>
                    </span>
                    <span class="message-time">
                        <?php echo date('d/m H:i', strtotime($latest_message['timestamp'] ?? 'now')); ?>
                    </span>
                <?php } else { ?>
                    <span class="no-messages">Aucun message</span>
                <?php } ?>
            </div>
        </div>
        <div class="item-actions">
            <a href="?conversation=contact:<?php echo urlencode($contact->contact_phone); ?>&tab=discussions" class="modern-btn btn-primary btn-small">
                💬 Ouvrir
            </a>
        </div>
    </div>
<?php } else { // Type group
        $group = $discussion['group'];
        $unread_count = $discussion['unread_count'];
        $latest_message = $discussion['latest_message'];
        $message_count = $discussion['message_count'];
?>
    <div class="list-item discussion-item">
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
                <span style="background: var(--info-gradient); color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 8px;">Groupe</span>
                <?php if ($unread_count > 0) { ?>
                    <span class="unread-badge"><?php echo $unread_count; ?></span>
                <?php } ?>
            </div>
            <div class="item-meta">
                <?php if ($latest_message) { ?>
                    <?php 
                    $sender = $users->xpath("//user[id='{$latest_message->sender_id}']")[0];
                    $is_sent_by_me = $latest_message->sender_id == $user_id;
                    ?>
                    <span class="message-preview">
                        <?php echo $is_sent_by_me ? 'Vous: ' : ($sender ? htmlspecialchars($sender->firstname . ': ') : ''); ?>
                        <?php echo htmlspecialchars(substr($latest_message->content, 0, 50)); ?>
                        <?php if (strlen($latest_message->content) > 50) echo '...'; ?>
                    </span>
                    <span class="message-time">
                        <?php echo date('d/m H:i', strtotime($latest_message['timestamp'] ?? 'now')); ?>
                    </span>
                <?php } else { ?>
                    <span class="no-messages">Aucun message</span>
                <?php } ?>
            </div>
        </div>
        <div class="item-actions">
            <a href="?conversation=group:<?php echo urlencode($group->id); ?>&tab=discussions" class="modern-btn btn-primary btn-small">
                💬 Ouvrir
            </a>
        </div>
    </div>
<?php } ?>
<?php } ?>
<?php if (empty($discussions)) { ?>
    <div class="empty-state">
        <div class="empty-icon">💬</div>
        <h3>Aucune discussion</h3>
        <p>Commencez à discuter avec vos contacts ou groupes pour voir vos conversations ici.</p>
    </div>
<?php } ?>
</div> 