<div class="profile-section">
    <div class="section-header">
        <h2>Mes Groupes</h2>
    </div>
    <div class="section-actions">
        <button type="button" onclick="afficherFormulaireCreationGroupe()" class="modern-btn btn-primary btn-large">
            <span>➕</span>
            Créer un Groupe
        </button>
    </div>
    <!-- Formulaire de création caché -->
    <div id="formulaireCreationGroupe" style="display: none;">
        <form action="../api.php" method="post" enctype="multipart/form-data" class="modern-form">
            <input type="hidden" name="action" value="creer_groupe">
            <div class="form-group">
                <label class="form-label">Nom du groupe</label>
                <input type="text" name="nom_groupe" class="form-input" placeholder="Nom du groupe" required>
            </div>
            <div class="form-group">
                <label class="form-label">Photo du groupe</label>
                <input type="file" name="photo_groupe" class="form-input" accept="image/*">
            </div>
            <div class="form-group">
                <label class="form-label">Sélectionner les membres</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px;">
                    <?php
                    foreach ($contacts->xpath("//contact[user_id='$id_utilisateur']") as $contact) {
                        $utilisateur_contact = $utilisateurs->xpath("//user[telephone='{$contact->contact_telephone}']")[0];
                        if ($utilisateur_contact) {
                            echo "<label style='display: flex; align-items: center; gap: 8px; padding: 8px; cursor: pointer; border-radius: 6px; transition: background 0.3s ease;' onmouseover='this.style.background=\"var(--bg-secondary)\"' onmouseout='this.style.background=\"transparent\"'>";
                            echo "<input type='checkbox' name='ids_membres[]' value='" . htmlspecialchars($utilisateur_contact->id) . "' style='margin: 0;'>";
                            echo "<span>" . htmlspecialchars($contact->contact_name) . "</span>";
                            echo "</label>";
                        }
                    }
                    ?>
                </div>
                <small class="form-help">Sélectionnez au moins 2 contacts pour créer un groupe</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="modern-btn btn-primary">
                    <span>🏠</span>
                    Créer le Groupe
                </button>
                <button type="button" onclick="cacherFormulaireCreationGroupe()" class="modern-btn btn-secondary">
                    <span>❌</span>
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
  <div class="search-bar">
    <input type="text" id="rechercheGroupes" placeholder="Rechercher un groupe...">
  </div>
<div class="modern-list">
<?php 
// Afficher tous les groupes où l'utilisateur est membre OU admin
foreach ($groupes->group as $groupe) {
    $est_membre = false;
    foreach ($groupe->id_membre as $id_membre) {
        if (trim((string)$id_membre) === trim((string)$id_utilisateur)) {
            $est_membre = true;
            break;
        }
    }
    $est_admin = trim((string)$groupe->id_admin) === trim((string)$id_utilisateur);
    if (!$est_membre && !$est_admin) continue;
    $coadmins = isset($groupe->coadmins) ? explode(',', (string)$groupe->coadmins) : [];
    $est_coadmin = in_array(trim((string)$id_utilisateur), array_map('trim', $coadmins));
    $peut_gerer = $est_admin || $est_coadmin;
    $ids_membres = [];
    foreach ($groupe->id_membre as $id_membre) {
        $ids_membres[] = trim((string)$id_membre);
    }
    $id_admin = trim((string)$groupe->id_admin);
    $tous_les_ids = $ids_membres;
    $tous_les_ids[] = $id_admin;
    $ids_uniques = array_unique($tous_les_ids);
    $nombre_membres = count($ids_uniques);
?>
<div class="list-item groupe-item">
    <div class="item-avatar">
        <?php if ($groupe->photo_groupe && $groupe->photo_groupe != 'default.jpg') { ?>
            <img src="../uploads/<?php echo htmlspecialchars($groupe->photo_groupe); ?>" alt="Photo Groupe" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        <?php } else { ?>
            <?php echo strtoupper(substr($groupe->name, 0, 1)); ?>
        <?php } ?>
    </div>
    <div class="item-content">
        <div class="item-name">
            <?php echo htmlspecialchars($groupe->name); ?>
            <?php if ($est_admin) { ?>
                <span class="badge badge-success">Admin</span>
            <?php } elseif ($est_coadmin) { ?>
                <span class="badge badge-warning">Co-Admin</span>
            <?php } ?>
        </div>
        <div class="item-meta"><?php echo $nombre_membres; ?> membres</div>
    </div>
    <div class="item-actions">
        <select class="modern-btn btn-secondary btn-small" onchange="gererActionGroupeSelect(this, '<?php echo $groupe->id; ?>')">
            <option value="">⚙️ Actions</option>
            <option value="ouvrir_conversation">💬 Ouvrir la conversation</option>
            <option value="lister_membres">👥 Lister les membres</option>
            <?php if ($peut_gerer) { ?>
                <option value="gerer_coadmins">👑 Gérer les co-admins</option>
                <option value="retirer_membre">➖ Retirer un membre</option>
                <option value="ajouter_membre">➕ Ajouter un membre</option>
            <?php } ?>
            <?php if ($est_admin) { ?>
                <option value="supprimer_groupe">🗑️ Supprimer le groupe</option>
            <?php } else { ?>
                <option value="quitter_groupe">🚪 Quitter le groupe</option>
            <?php } ?>
        </select>
    </div>
</div>
<!-- 🔒 Modals -->
<!-- Liste des membres -->
<div id="liste-membres-<?php echo $groupe->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Membres du groupe : <?php echo htmlspecialchars($groupe->name); ?></h3>
        <ul>
            <?php
            foreach ($ids_uniques as $id_membre) {
                $membre = $utilisateurs->xpath("//user[id='$id_membre']")[0];
                if ($membre) {
                    $est_admin_membre = ($id_admin === $id_membre);
                    $coadmins_membre = isset($groupe->coadmins) ? explode(',', (string)$groupe->coadmins) : [];
                    $est_coadmin_membre = in_array($id_membre, $coadmins_membre);
                    echo "<li><strong>" . htmlspecialchars($membre->prenom . ' ' . $membre->nom) . "</strong> ";
                    echo "<small>(" . htmlspecialchars($membre->telephone) . ")</small> ";
                    if ($est_admin_membre) echo "<span style='color:green;'>[Admin]</span>";
                    elseif ($est_coadmin_membre) echo "<span style='color:orange;'>[Co-Admin]</span>";
                    else echo "<span style='color:gray;'>[Membre]</span>";
                    echo "</li>";
                }
            }
            ?>
        </ul>
        <button onclick="document.getElementById('liste-membres-<?php echo $groupe->id; ?>').style.display='none'" class="modern-btn btn-secondary">Fermer</button>
    </div>
</div>
<!-- Gérer les co-admins -->
<div id="coadmins-modal-<?php echo $groupe->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Gérer les co-admins : <?php echo htmlspecialchars($groupe->name); ?></h3>
        <ul>
            <?php foreach ($groupe->id_membre as $id_membre) {
                if ($id_membre == $groupe->id_admin) continue;
                $membre = $utilisateurs->xpath("//user[id='$id_membre']")[0];
                if ($membre) {
                    $est_coadmin_membre = isset($groupe->coadmins) && in_array($id_membre, explode(',', (string)$groupe->coadmins));
                    echo "<li>" . htmlspecialchars($membre->prenom . ' ' . $membre->nom);
                    if ($est_coadmin_membre) {
                        echo " <form method='post' action='../api.php' style='display:inline;'><input type='hidden' name='action' value='retirer_coadmin'><input type='hidden' name='id_groupe' value='".htmlspecialchars($groupe->id)."'><input type='hidden' name='id_coadmin' value='".htmlspecialchars($id_membre)."'><button type='submit' class='modern-btn btn-danger btn-small'>Retirer co-admin</button></form>";
                    } else {
                        echo " <form method='post' action='../api.php' style='display:inline;'><input type='hidden' name='action' value='ajouter_coadmin'><input type='hidden' name='id_groupe' value='".htmlspecialchars($groupe->id)."'><input type='hidden' name='id_coadmin' value='".htmlspecialchars($id_membre)."'><button type='submit' class='modern-btn btn-primary btn-small'>Ajouter co-admin</button></form>";
                    }
                    echo "</li>";
                }
            } ?>
        </ul>
        <button onclick="document.getElementById('coadmins-modal-<?php echo $groupe->id; ?>').style.display='none'" class="modern-btn btn-secondary">Fermer</button>
    </div>
</div>
<!-- Retirer un membre -->
<div id="retirer-membre-modal-<?php echo $groupe->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Retirer un membre du groupe : <?php echo htmlspecialchars($groupe->name); ?></h3>
        <ul>
            <?php foreach ($groupe->id_membre as $id_membre) {
                if ($id_membre == $groupe->id_admin || $id_membre == $id_utilisateur) continue;
                $membre = $utilisateurs->xpath("//user[id='$id_membre']")[0];
                if ($membre) {
                    echo "<li>" . htmlspecialchars($membre->prenom . ' ' . $membre->nom);
                    echo " <form method='post' action='../api.php' style='display:inline;'><input type='hidden' name='action' value='retirer_membre'><input type='hidden' name='id_groupe' value='".htmlspecialchars($groupe->id)."'><input type='hidden' name='id_membre' value='".htmlspecialchars($id_membre)."'><button type='submit' class='modern-btn btn-danger btn-small'>Retirer</button></form>";
                    echo "</li>";
                }
            } ?>
        </ul>
        <button onclick="document.getElementById('retirer-membre-modal-<?php echo $groupe->id; ?>').style.display='none'" class="modern-btn btn-secondary">Fermer</button>
    </div>
</div>
<!-- Supprimer le groupe -->
<div id="supprimer-groupe-modal-<?php echo $groupe->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Supprimer le groupe "<?php echo htmlspecialchars($groupe->name); ?>" ?</h3>
        <p>Cette action est irréversible.</p>
        <form method="post" action="../api.php">
            <input type="hidden" name="action" value="supprimer_groupe">
            <input type="hidden" name="id_groupe" value="<?php echo htmlspecialchars($groupe->id); ?>">
            <button type="submit" class="modern-btn btn-danger">Confirmer la suppression</button>
            <button type="button" onclick="document.getElementById('supprimer-groupe-modal-<?php echo $groupe->id; ?>').style.display='none'" class="modern-btn btn-secondary">Annuler</button>
        </form>
    </div>
</div>
<!-- Quitter le groupe -->
<div id="quitter-groupe-modal-<?php echo $groupe->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Quitter le groupe "<?php echo htmlspecialchars($groupe->name); ?>" ?</h3>
        <form method="post" action="../api.php">
            <input type="hidden" name="action" value="quitter_groupe">
            <input type="hidden" name="id_groupe" value="<?php echo htmlspecialchars($groupe->id); ?>">
            <input type="hidden" name="id_utilisateur" value="<?php echo htmlspecialchars($id_utilisateur); ?>">
            <button type="submit" class="modern-btn btn-danger">Confirmer</button>
            <button type="button" onclick="document.getElementById('quitter-groupe-modal-<?php echo $groupe->id; ?>').style.display='none'" class="modern-btn btn-secondary">Annuler</button>
        </form>
    </div>
</div>
<!-- Ajouter un membre -->
<div id="ajouter-membre-modal-<?php echo $groupe->id; ?>" class="image-modal" style="display:none;">
    <div class="modal-content">
        <h3>Ajouter un membre au groupe : <?php echo htmlspecialchars($groupe->name); ?></h3>
        <form method="post" action="../api.php">
            <input type="hidden" name="action" value="ajouter_membre">
            <input type="hidden" name="id_groupe" value="<?php echo htmlspecialchars($groupe->id); ?>">
            <div class="form-group">
                <label for="id_nouveau_membre">Sélectionner un contact à ajouter :</label>
                <select name="id_nouveau_membre" id="id_nouveau_membre" required>
                    <option value="">-- Choisir un contact --</option>
                    <?php
                    foreach ($contacts->xpath("//contact[user_id='$id_utilisateur']") as $contact) {
                        $utilisateur_contact = $utilisateurs->xpath("//user[telephone='{$contact->contact_telephone}']")[0];
                        if ($utilisateur_contact && !in_array((string)$utilisateur_contact->id, $ids_uniques)) {
                            echo "<option value='" . htmlspecialchars($utilisateur_contact->id) . "'>" . htmlspecialchars($contact->contact_name) . " (" . htmlspecialchars($contact->contact_telephone) . ")</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="modern-btn btn-primary">Ajouter</button>
            <button type="button" onclick="document.getElementById('ajouter-membre-modal-<?php echo $groupe->id; ?>').style.display='none'" class="modern-btn btn-secondary">Annuler</button>
        </form>
    </div>
</div>
<?php } ?>
<?php if (empty($groupes->group)) { ?>
<div class="empty-state">
    <div class="empty-icon">🏠</div>
    <h3>Aucun groupe</h3>
    <p>Créez votre premier groupe pour commencer à discuter en équipe.</p>
</div>
<?php } ?>
</div> 
<script src="../js/global.js"></script> 