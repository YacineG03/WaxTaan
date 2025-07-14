<div class="profile-section">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
        <div style="width: 64px; height: 64px; border-radius: 50%; overflow: hidden; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
            <?php if ($current_user->profile_photo && $current_user->profile_photo != 'default.jpg') { ?>
                <img src="../uploads/<?php echo htmlspecialchars($current_user->profile_photo); ?>" alt="Photo de profil" style="width: 100%; height: 100%; object-fit: cover;">
            <?php } else { ?>
                <?php echo strtoupper(substr($current_user->firstname, 0, 1)); ?>
            <?php } ?>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">
                <?php echo htmlspecialchars($current_user->firstname . ' ' . $current_user->lastname); ?>
            </h2>
            <div style="color: var(--text-muted); font-size: 1rem;">
                <?php echo htmlspecialchars($current_user->phone); ?>
            </div>
        </div>
    </div>
    <button type="button" onclick="document.getElementById('profileEditForm').style.display='block'; this.style.display='none';" class="modern-btn btn-primary" id="showProfileEditBtn">
        <span>✏️</span> Modifier le profil
    </button>
    <form id="profileEditForm" action="../api.php" method="post" enctype="multipart/form-data" class="modern-form" style="display: none; margin-top: 24px;">
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
            <span>💾</span> Mettre à jour
        </button>
        <button type="button" onclick="document.getElementById('profileEditForm').style.display='none'; document.getElementById('showProfileEditBtn').style.display='inline-block';" class="modern-btn btn-secondary" style="margin-left: 8px;">
            <span>❌</span> Annuler
        </button>
    </form>
</div> 