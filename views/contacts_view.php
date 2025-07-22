<div class="profile-section">
    <div class="section-header">
        <h2>Mes Contacts</h2>
    </div>
    <div class="section-actions">
        <button type="button" onclick="afficherFormulaireAjoutContact()" class="modern-btn btn-primary btn-large">
            <span>➕</span>
            Ajouter un Contact
        </button>
    </div>
    
    <!-- Formulaire d'ajout caché -->
    <div id="formulaireAjoutContact" style="display: none;">
        <form action="../api.php" method="post" class="modern-form">
            <input type="hidden" name="action" value="ajouter_contact">
            
            <div class="form-group">
                <label class="form-label">Nom du contact</label>
                <input type="text" name="nom_contact" class="form-input" placeholder="Nom du contact" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Numéro de téléphone</label>
                <input type="text" name="telephone_contact" class="form-input" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres" placeholder="ex: 771234567" required>
                <small class="form-help">Le numéro doit correspondre à un utilisateur existant</small>
            </div>
            
            <div class="form-actions">
                <div class="button-group">
                <button type="submit" class="modern-btn btn-primary">
                    <span>➕</span>
                    Ajouter Contact
                </button>
                    <button type="button" onclick="cacherFormulaireAjoutContact()" class="modern-btn btn-secondary">
                    <span>❌</span>
                    Annuler
                </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Formulaire d'édition caché -->
<div id="formulaireEditionContact" style="display: none;">
    <form action="../api.php" method="post" class="modern-form">
        <input type="hidden" name="action" value="editer_contact">
        <input type="hidden" name="id_contact" id="idEditionContact">
        <div class="form-group">
            <label class="form-label">Nouveau nom du contact</label>
            <input type="text" name="nom_contact" id="nomEditionContact" class="form-input" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="modern-btn btn-primary">
                <span>✏️</span> Modifier
            </button>
            <button type="button" onclick="cacherFormulaireEditionContact()" class="modern-btn btn-secondary">
                <span>❌</span>
                Annuler
            </button>
        </div>
    </form>
</div>

<div class="search-bar">
    <input type="text" id="rechercheContacts" placeholder="Rechercher un contact...">
</div>

<div class="modern-list">
    <?php foreach ($contacts->xpath("//contact[id_utilisateur='$id_utilisateur']") as $contact) { ?>
        <?php
        $utilisateur_contact_result = $utilisateurs->xpath("//user[telephone='{$contact->telephone_contact}']");
        $utilisateur_contact = !empty($utilisateur_contact_result) ? $utilisateur_contact_result[0] : null;
        if ($utilisateur_contact) {
        ?>
            <div class="list-item contact-item">
                <div class="item-avatar">
                    <?php if ($utilisateur_contact->profile_photo && $utilisateur_contact->profile_photo != 'default.jpg') { ?>
                        <img src="../uploads/<?php echo htmlspecialchars($utilisateur_contact->profile_photo); ?>" alt="Photo">
                    <?php } else { ?>
                        <?php echo strtoupper(substr($contact->nom_contact, 0, 1)); ?>
                    <?php } ?>
                </div>
                
                <div class="item-content">
                    <div class="item-name"><?php echo htmlspecialchars($contact->nom_contact); ?></div>
                    <div class="item-meta"><?php echo htmlspecialchars($contact->telephone_contact); ?></div>
                </div>
                
                <div class="item-actions">
                    <button type="button" onclick="afficherFormulaireEditionContact('<?php echo $contact->id; ?>', '<?php echo htmlspecialchars($contact->nom_contact); ?>')" class="modern-btn btn-secondary btn-small">
                        ✏️
                    </button>
                    <button type="button" onclick="confirmerSuppressionContact('<?php echo $contact->id; ?>', '<?php echo htmlspecialchars($contact->nom_contact); ?>')" class="modern-btn btn-danger btn-small">
                        🗑️
                    </button>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
    
    <?php if (empty($contacts->xpath("//contact[id_utilisateur='$id_utilisateur']"))) { ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3>Aucun contact</h3>
            <p>Ajoutez votre premier contact pour commencer à discuter.</p>
        </div>
    <?php } ?>
</div>
<script src="../js/global.js"></script>