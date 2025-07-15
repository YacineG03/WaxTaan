<div class="profile-section">
    <div class="section-header">
        <h2>Mes Contacts</h2>
        <button type="button" onclick="showAddContactForm()" class="modern-btn btn-primary">
            <span>➕</span>
            Ajouter un Contact
        </button>
    </div>
    
    <!-- Formulaire d'ajout caché -->
    <div id="addContactForm" style="display: none;">
        <form action="../api.php" method="post" class="modern-form">
            <input type="hidden" name="action" value="add_contact">
            
            <div class="form-group">
                <label class="form-label">Nom du contact</label>
                <input type="text" name="contact_name" class="form-input" placeholder="Nom du contact" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Numéro de téléphone</label>
                <input type="text" name="contact_phone" class="form-input" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567" required>
                <small class="form-help">Le numéro doit correspondre à un utilisateur existant</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="modern-btn btn-primary">
                    <span>➕</span>
                    Ajouter Contact
                </button>
                <button type="button" onclick="hideAddContactForm()" class="modern-btn btn-secondary">
                    <span>❌</span>
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showEditContactForm(contactId, contactName) {
    document.getElementById('editContactId').value = contactId;
    document.getElementById('editContactName').value = contactName;
    document.getElementById('editContactForm').style.display = 'block';
}
function hideEditContactForm() {
    document.getElementById('editContactForm').style.display = 'none';
}
</script>
<!-- Formulaire d'édition caché -->
<div id="editContactForm" style="display: none; margin-bottom: 16px;">
    <form action="../api.php" method="post" class="modern-form">
        <input type="hidden" name="action" value="edit_contact">
        <input type="hidden" name="contact_id" id="editContactId">
        <div class="form-group">
            <label class="form-label">Nouveau nom du contact</label>
            <input type="text" name="contact_name" id="editContactName" class="form-input" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="modern-btn btn-primary">
                <span>✏️</span> Modifier
            </button>
            <button type="button" onclick="hideEditContactForm()" class="modern-btn btn-secondary">
                <span>❌</span> Annuler
            </button>
        </div>
    </form>
</div>

<div class="search-bar">
    <input type="text" id="searchContacts" placeholder="Rechercher un contact...">
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchContacts');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = searchInput.value.toLowerCase();
            document.querySelectorAll('.contact-item').forEach(function(item) {
                const name = item.textContent.toLowerCase();
                item.style.display = name.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>

<div class="modern-list">
    <?php foreach ($contacts->xpath("//contact[user_id='$user_id']") as $contact) { ?>
        <?php
        $contact_user = $users->xpath("//user[phone='{$contact->contact_phone}']")[0];
        if ($contact_user) {
        ?>
            <div class="list-item contact-item">
                <div class="item-avatar">
                    <?php if ($contact_user->profile_photo && $contact_user->profile_photo != 'default.jpg') { ?>
                        <img src="../uploads/<?php echo htmlspecialchars($contact_user->profile_photo); ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php } else { ?>
                        <?php echo strtoupper(substr($contact->contact_name, 0, 1)); ?>
                    <?php } ?>
                </div>
                
                <div class="item-content">
                    <div class="item-name"><?php echo htmlspecialchars($contact->contact_name); ?></div>
                    <div class="item-meta"><?php echo htmlspecialchars($contact->contact_phone); ?></div>
                </div>
                
                <div class="item-actions">
                    <button type="button" onclick="showEditContactForm('<?php echo $contact->id; ?>', '<?php echo htmlspecialchars($contact->contact_name); ?>')" class="modern-btn btn-secondary btn-small">
                        ✏️
                    </button>
                    <button type="button" onclick="confirmDeleteContact('<?php echo $contact->id; ?>', '<?php echo htmlspecialchars($contact->contact_name); ?>')" class="modern-btn btn-danger btn-small">
                        🗑️
                    </button>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
    
    <?php if (empty($contacts->xpath("//contact[user_id='$user_id']"))) { ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3>Aucun contact</h3>
            <p>Ajoutez votre premier contact pour commencer à discuter.</p>
        </div>
    <?php } ?>
</div> 