<div class="p-4 contacts-section">
    <h2>Contacts</h2>
    
    <form action="api.php" method="post" class="space-y-2">
        <input type="hidden" name="action" value="add_contact">
        <input type="text" name="contact_name" placeholder="Nom du contact" required>
        <input type="text" name="contact_phone" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567" required>
        <button type="submit">Ajouter Contact</button>
    </form>
    
    <ul class="mt-4">
        <?php foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) { ?>
            <li class="contact-item-wrapper">
                <?php
                $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                if ($contact_user) { ?>
                    <a href="?conversation=contact:<?php echo urlencode($contact->contact_phone); ?>" class="contact-link">
                        <div class="contact-info">
                            <?php if ($contact_user->profile_photo && $contact_user->profile_photo != 'default.jpg') { ?>
                                <img src="uploads/<?php echo htmlspecialchars($contact_user->profile_photo); ?>" alt="Photo" class="contact-item">
                            <?php } else { ?>
                                <div class="contact-item bg-gray-300">
                                    <?php echo strtoupper(substr($contact->contact_name, 0, 1)); ?>
                                </div>
                            <?php } ?>
                            <span class="contact-name"><?php echo htmlspecialchars($contact->contact_name); ?></span>
                        </div>
                    </a>
                    <div class="contact-actions">
                        <a href="api.php?action=delete_contact&contact_id=<?php echo $contact->id; ?>" class="text-red-500">Supprimer</a>
                    </div>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
</div>
