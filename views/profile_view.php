<div class="profile-section">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php if ($utilisateur_courant->photo_profil && $utilisateur_courant->photo_profil != 'default.jpg') { ?>
                <img src="../uploads/<?php echo htmlspecialchars($utilisateur_courant->photo_profil); ?>" alt="Photo de profil">
            <?php } else { ?>
                <?php echo strtoupper(substr($utilisateur_courant->prenom, 0, 1)); ?>
            <?php } ?>
        </div>
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($utilisateur_courant->prenom . ' ' . $utilisateur_courant->nom); ?></h2>
            <div class="profile-telephone"><?php echo htmlspecialchars($utilisateur_courant->telephone); ?></div>
        </div>
    </div>
    <button type="button" onclick="afficherFormulaireEditionProfil()" class="modern-btn btn-primary" id="afficherBoutonEditionProfil">
        <span>✏️</span> Modifier le profil
    </button>
    <form id="formulaireEditionProfil" action="../api.php" method="post" enctype="multipart/form-data" class="modern-form profile-edit-form">
        <input type="hidden" name="action" value="mettre_a_jour_profil">
        <div class="form-group">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" value="<?php echo htmlspecialchars($utilisateur_courant->prenom); ?>" class="form-input" placeholder="Votre prénom">
        </div>
        <div class="form-group">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($utilisateur_courant->nom); ?>" class="form-input" placeholder="Votre nom">
        </div>
        <div class="form-group">
            <label class="form-label">Sexe</label>
            <select name="sexe" class="form-input">
                <option value="M" <?php echo $utilisateur_courant->sexe == 'M' ? 'selected' : ''; ?>>Masculin</option>
                <option value="F" <?php echo $utilisateur_courant->sexe == 'F' ? 'selected' : ''; ?>>Féminin</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Âge</label>
            <input type="number" name="age" value="<?php echo htmlspecialchars($utilisateur_courant->age); ?>" class="form-input" placeholder="Votre âge">
        </div>
        <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="text" name="telephone" value="<?php echo htmlspecialchars($utilisateur_courant->telephone); ?>" class="form-input" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres">
        </div>
        <div class="form-group">
            <label class="form-label">Photo de profil</label>
            <input type="file" name="photo_profil" class="form-input" accept="image/*">
        </div>
        <button type="submit" class="modern-btn btn-primary">
            <span>💾</span> Mettre à jour
        </button>
        <button type="button" onclick="cacherFormulaireEditionProfil()" class="modern-btn btn-secondary">
            <span>❌</span> Annuler
        </button>
    </form>
</div>
<script src="../js/global.js"></script>