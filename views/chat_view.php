<div class="w-2/3 flex flex-col">
    <?php
    $current_conversation = $_GET['conversation'] ?? '';
    $messages_to_show = [];
    if ($current_conversation) {
        list($type, $id) = explode(':', $current_conversation);
        if ($type === 'contact') {
            $messages_to_show = $messages->xpath("//message[(sender_id='$user_id' and recipient='$id') or (sender_id='$id' and recipient='$user_id')]");
        } elseif ($type === 'group') {
            $messages_to_show = $messages->xpath("//message[recipient_group='$id']");
        }
    }
    ?>
    
    <div id="chat-container" class="flex-1 p-4 overflow-y-auto">
        <?php if (empty($messages_to_show)) { ?>
            <div class="system-message">
                <?php if ($current_conversation) { ?>
                    Aucun message dans cette conversation. Commencez à discuter !
                <?php } else { ?>
                    Sélectionnez un contact ou un groupe pour commencer à discuter.
                <?php } ?>
            </div>
        <?php } ?>
        
        <?php foreach ($messages_to_show as $message) { ?>
            <div class="message <?php echo $message->sender_id == $user_id ? 'sent' : 'received'; ?>">
                <?php $sender = $users->xpath("//user[id='{$message->sender_id}']")[0]; ?>
                
                <div class="message-header">
                    <?php if ($sender->profile_photo && $sender->profile_photo != 'default.jpg') { ?>
                        <img src="uploads/<?php echo htmlspecialchars($sender->profile_photo); ?>" alt="Profile" class="message-avatar">
                    <?php } else { ?>
                        <div class="message-avatar bg-gray-300">
                            <?php echo strtoupper(substr($sender->firstname, 0, 1)); ?>
                        </div>
                    <?php } ?>
                    
                    <span class="font-semibold"><?php echo htmlspecialchars($sender->firstname . ' ' . $sender->lastname); ?></span>
                    <span class="text-xs"><?php echo date('d/m/Y H:i', strtotime($message['timestamp'] ?? 'now')); ?></span>
                </div>
                
                <p><?php echo htmlspecialchars($message->content); ?></p>
                
                <?php if ($message->file) { ?>
                    <a href="uploads/<?php echo $message->file; ?>" download class="file-download">
                        📎 Télécharger fichier
                    </a>
                <?php } ?>
                
                <?php 
                if ($current_conversation && strpos($current_conversation, 'group:') === 0) {
                    $is_admin = in_array($user_id, explode(',', $groups->xpath("//group[id='$id']")[0]->admin_id . ',' . ($groups->xpath("//group[id='$id']")[0]->coadmins ?? '')));
                    if ($is_admin && $message->id) { ?>
                        <a href="api.php?action=delete_message&message_id=<?php echo $message->id; ?>&group_id=<?php echo $id; ?>" class="text-red-500 ml-2">Supprimer</a>
                    <?php }
                } ?>
            </div>
        <?php } ?>
    </div>
    
    <?php if ($current_conversation && strpos($current_conversation, 'group:') === 0) { ?>
        <form action="api.php" method="post" enctype="multipart/form-data" class="p-4 bg-white border-t">
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($id); ?>">
            <input type="hidden" name="recipient_type" value="group">
            <div class="flex space-x-2">
                <textarea name="message" class="flex-1" placeholder="Votre message..." rows="2"></textarea>
                <input type="file" name="file" accept="image/*,video/*,application/*">
                <button type="submit">Envoyer</button>
            </div>
        </form>
    <?php } elseif ($current_conversation) { ?>
        <form action="api.php" method="post" enctype="multipart/form-data" class="p-4 bg-white border-t">
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($id); ?>">
            <input type="hidden" name="recipient_type" value="contact">
            <div class="flex space-x-2">
                <textarea name="message" class="flex-1" placeholder="Votre message..." rows="2"></textarea>
                <input type="file" name="file" accept="image/*,video/*,application/*">
                <button type="submit">Envoyer</button>
            </div>
        </form>
    <?php } ?>
</div>
