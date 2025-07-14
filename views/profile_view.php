<div class="profile-section">
    <h2>Modifier le Profil</h2>
    <form action="../api.php" method="post" enctype="multipart/form-data" class="modern-form">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-group">
            <label class="form-label">Prénom</label>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($current_user->firstname); ?>" class="form-input" placeholder="Votre prénom">
        </div>
        <div class="form-group">
            <label class="form-label">Nom</label>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($current_user->lastname); ?>" class="form-input" placeholder="Votre nom">
        </div>
        <div class="form-group">
            <label class="form-label">Sexe</label>
            <select name="sex" class="form-input">
                <option value="M" <?php echo $current_user->sex == 'M' ? 'selected' : ''; ?>>Masculin</option>
                <option value="F" <?php echo $current_user->sex == 'F' ? 'selected' : ''; ?>>Féminin</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Âge</label>
            <input type="number" name="age" value="<?php echo htmlspecialchars($current_user->age); ?>" class="form-input" placeholder="Votre âge">
        </div>
        <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($current_user->phone); ?>" class="form-input" pattern="(77|70|78|76)[0-9]{7}" title="Numéro doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres">
        </div>
        <div class="form-group">
            <label class="form-label">Photo de profil</label>
            <input type="file" name="profile_photo" class="form-input" accept="image/*">
        </div>
        <button type="submit" class="modern-btn btn-primary">
            <span>💾</span>
            Mettre à jour
        </button>
    </form>
</div> 