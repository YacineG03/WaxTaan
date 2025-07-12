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
        <?php foreach ($messages_to_show as $message) { ?>
            <div class="message <?php echo $message->sender_id == $user_id ? 'sent' : 'received'; ?>">
                <?php $sender = $users->xpath("//user[id='{$message->sender_id}']")[0]; ?>
                <?php if ($sender->profile_photo) { ?>
                    <img src="uploads/<?php echo htmlspecialchars($sender->profile_photo); ?>" alt="Profile" class="w-8 h-8 rounded-full inline-block mr-2">
                <?php } else { ?>
                    <div class="w-8 h-8 bg-gray-300 rounded-full inline-block mr-2 flex items-center justify-center">?</div>
                <?php } ?>
                <p class="font-semibold inline-block"><?php echo htmlspecialchars($sender->firstname . ' ' . $sender->lastname); ?></p>
                <p class="text-xs text-gray-500 inline-block ml-2"><?php echo date('d/m/Y H:i', strtotime($message['timestamp'] ?? 'now')); ?></p>
                <p class="mt-1"><?php echo htmlspecialchars($message->content); ?></p>
                <?php if ($message->file) { ?>
                    <a href="uploads/<?php echo $message->file; ?>" download class="text-blue-500">Télécharger fichier</a>
                <?php } ?>
                <?php $is_admin = in_array($user_id, explode(',', $groups->xpath("//group[id='$id']")[0]->admin_id . ',' . ($groups->xpath("//group[id='$id']")[0]->coadmins ?? ''))); ?>
                <?php if ($is_admin && $message->id) { ?>
                    <a href="api.php?action=delete_message&message_id=<?php echo $message->id; ?>&group_id=<?php echo $id; ?>" class="text-red-500 ml-2">Supprimer</a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <?php if ($current_conversation && strpos($current_conversation, 'group:') === 0) { ?>
        <form action="api.php" method="post" enctype="multipart/form-data" class="p-4 bg-white border-t">
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($id); ?>">
            <input type="hidden" name="recipient_type" value="group">
            <div class="flex space-x-2">
                <textarea name="message" class="w-full p-2 border rounded" placeholder="Votre message..."></textarea>
                <input type="file" name="file" class="p-2" accept="image/*,video/*,application/*">
                <button type="submit" class="p-2 bg-blue-500 text-white rounded">Envoyer</button>
            </div>
        </form>
    <?php } elseif ($current_conversation) { ?>
        <form action="api.php" method="post" enctype="multipart/form-data" class="p-4 bg-white border-t">
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($id); ?>">
            <input type="hidden" name="recipient_type" value="contact">
            <div class="flex space-x-2">
                <textarea name="message" class="w-full p-2 border rounded" placeholder="Votre message..."></textarea>
                <input type="file" name="file" class="p-2" accept="image/*,video/*,application/*">
                <button type="submit" class="p-2 bg-blue-500 text-white rounded">Envoyer</button>
            </div>
        </form>
    <?php } ?>
</div>