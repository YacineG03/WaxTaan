<div class="p-4">
    <h2 class="font-semibold">Contacts</h2>
    <form action="api.php" method="post" class="space-y-2">
        <input type="hidden" name="action" value="add_contact">
        <input type="text" name="contact_name" class="w-full p-2 border rounded" placeholder="Nom du contact">
        <input type="text" name="contact_phone" class="w-full p-2 border rounded" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres">
        <button type="submit" class="w-full p-2 bg-blue-500 text-white rounded">Ajouter</button>
    </form>
    <ul class="mt-2">
        <?php foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) { ?>
            <li class="p-2 flex items-center">
                <?php
                $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
                if ($contact_user->profile_photo) { ?>
                    <img src="uploads/<?php echo htmlspecialchars($contact_user->profile_photo); ?>" alt="Photo" class="contact-item">
                <?php } else { ?>
                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-2">?</div>
                <?php } ?>
                <a href="?conversation=contact:<?php echo urlencode($contact->contact_phone); ?>" class="hover:underline"><span class="mr-2"><?php echo htmlspecialchars($contact->contact_name); ?></span></a>
                <a href="api.php?action=delete_contact&contact_id=<?php echo $contact->id; ?>" class="text-red-500 ml-2">Supprimer</a>
            </li>
        <?php } ?>
    </ul>
</div>