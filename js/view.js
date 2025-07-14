// ========================================
// SCRIPT PRINCIPAL POUR VIEW.PHP
// ========================================

// Gestion des onglets
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            // Retirer la classe active de tous les onglets
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            
            // Ajouter la classe active à l'onglet cliqué
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab + '-panel').classList.add('active');
        });
    });

    // Maintenir l'onglet actif selon l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        // Retirer la classe active de tous les onglets
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        
        // Ajouter la classe active à l'onglet spécifié
        const targetTab = document.querySelector(`[data-tab="${activeTab}"]`);
        const targetPanel = document.getElementById(activeTab + '-panel');
        
        if (targetTab && targetPanel) {
            targetTab.classList.add('active');
            targetPanel.classList.add('active');
        }
    }

    // Fermer le modal des actions de groupe en cliquant à l'extérieur
    const groupActionsModal = document.getElementById('groupActionsModal');
    if (groupActionsModal) {
        groupActionsModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeGroupActionsModal();
            }
        });
    }

    // Fermer le modal en cliquant à l'extérieur
    const imageModal = document.getElementById('imageModal');
    if (imageModal) {
        imageModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    }

    // Validation du formulaire d'ajout de contact
    const addContactForm = document.getElementById('addContactForm');
    if (addContactForm) {
        addContactForm.addEventListener('submit', function(e) {
            const contactName = this.querySelector('input[name="contact_name"]').value.trim();
            const contactPhone = this.querySelector('input[name="contact_phone"]').value.trim();
            
            // Vérifier que le nom n'est pas vide
            if (contactName.length < 2) {
                e.preventDefault();
                alert('Le nom du contact doit contenir au moins 2 caractères.');
                return false;
            }
            
            // Vérifier le format du numéro de téléphone
            const phonePattern = /^(77|70|78|76)[0-9]{7}$/;
            if (!phonePattern.test(contactPhone)) {
                e.preventDefault();
                alert('Le numéro de téléphone doit commencer par 77, 70, 78 ou 76 suivi de 7 chiffres.');
                return false;
            }
            
            // Vérifier que l'utilisateur ne s'ajoute pas lui-même
            const currentUserPhone = document.querySelector('input[name="current_user_phone"]')?.value || '';
            if (contactPhone === currentUserPhone) {
                e.preventDefault();
                alert('Vous ne pouvez pas vous ajouter vous-même comme contact.');
                return false;
            }
        });
    }

    // Auto-scroll du chat
    const chatContainer = document.getElementById('chat-container');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Auto-resize du textarea
    const messageInput = document.querySelector('.message-input');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    // Notification pour les erreurs et succès
    setTimeout(() => {
        const errorNotif = document.querySelector('[style*="position: fixed"]');
        if (errorNotif) {
            errorNotif.style.transform = 'translateX(400px)';
            errorNotif.style.opacity = '0';
            setTimeout(() => errorNotif.remove(), 300);
        }
    }, 5000);
});

// ========================================
// FONCTIONS POUR LES CONTACTS
// ========================================

// Fonction de confirmation pour la suppression de contact
function confirmDeleteContact(contactId, contactName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le contact "${contactName}" ?\n\nCette action est irréversible.`)) {
        document.getElementById('contactIdToDelete').value = contactId;
        document.getElementById('deleteContactForm').submit();
    }
}

// Fonctions pour l'ajout de contact
function showAddContactForm() {
    document.getElementById('addContactForm').style.display = 'block';
    document.querySelector('#addContactForm input[name="contact_name"]').focus();
}

function hideAddContactForm() {
    document.getElementById('addContactForm').style.display = 'none';
    document.getElementById('addContactForm').querySelector('form').reset();
}

// Fonction pour l'édition de contact (à implémenter plus tard)
function editContact(contactId, contactName, contactPhone) {
    alert(`Édition du contact "${contactName}" (${contactPhone})\n\nCette fonctionnalité sera implémentée prochainement.`);
}

// ========================================
// FONCTIONS POUR LES GROUPES
// ========================================

// Fonctions pour la gestion des groupes
function showCreateGroupForm() {
    document.getElementById('createGroupForm').style.display = 'block';
    document.querySelector('#createGroupForm input[name="group_name"]').focus();
}

function hideCreateGroupForm() {
    document.getElementById('createGroupForm').style.display = 'none';
    document.getElementById('createGroupForm').querySelector('form').reset();
}

function confirmDeleteGroup(groupId, groupName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le groupe "${groupName}" ?\n\nCette action est irréversible et supprimera définitivement le groupe.`)) {
        document.getElementById('groupIdToDelete').value = groupId;
        document.getElementById('deleteGroupForm').submit();
    }
}

function confirmLeaveGroup(groupId, groupName) {
    if (confirm(`Êtes-vous sûr de vouloir quitter le groupe "${groupName}" ?\n\nVous ne pourrez plus accéder aux messages de ce groupe.`)) {
        document.getElementById('groupIdToLeave').value = groupId;
        document.getElementById('leaveGroupForm').submit();
    }
}

function showGroupActions(groupId, groupName, isAdmin, isCoAdmin) {
    const modal = document.getElementById('groupActionsModal');
    const title = document.getElementById('groupActionsTitle');
    const content = document.getElementById('groupActionsContent');
    
    title.textContent = `Actions - ${groupName}`;
    
    let actionsHtml = `
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="?conversation=group:${groupId}&tab=discussions" class="modern-btn btn-primary" style="text-decoration: none; text-align: center;">
                <span>💬</span>
                Ouvrir la conversation
            </a>
    `;
    
    if (isAdmin || isCoAdmin) {
        actionsHtml += `
            <button type="button" onclick="listGroupMembers('${groupId}', '${groupName}')" class="modern-btn btn-secondary">
                <span>👥</span>
                Lister les membres
            </button>
        `;
    }
    
    if (isAdmin) {
        actionsHtml += `
            <button type="button" onclick="manageCoAdmins('${groupId}', '${groupName}')" class="modern-btn btn-secondary">
                <span>👑</span>
                Gérer les co-admins
            </button>
            <button type="button" onclick="removeGroupMember('${groupId}', '${groupName}')" class="modern-btn btn-warning">
                <span>➖</span>
                Retirer un membre
            </button>
        `;
    }
    
    actionsHtml += `
        </div>
    `;
    
    content.innerHTML = actionsHtml;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeGroupActionsModal() {
    document.getElementById('groupActionsModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function listGroupMembers(groupId, groupName) {
    // Charger les données du groupe via AJAX ou afficher dans un modal
    const modal = document.getElementById('groupActionsModal');
    const title = document.getElementById('groupActionsTitle');
    const content = document.getElementById('groupActionsContent');
    
    title.textContent = `Membres - ${groupName}`;
    
    // Simuler le chargement des membres (en réalité, on ferait un appel AJAX)
    let membersHtml = `
        <div style="max-height: 300px; overflow-y: auto;">
            <h4>Liste des membres du groupe</h4>
            <div id="membersList">
                <p>Chargement des membres...</p>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <button type="button" onclick="closeGroupActionsModal()" class="modern-btn btn-secondary">
                <span>❌</span>
                Fermer
            </button>
        </div>
    `;
    
    content.innerHTML = membersHtml;
    
    // Charger les membres via AJAX
    fetch(`../api.php?action=list_members&group_id=${groupId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('membersList').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('membersList').innerHTML = '<p>Erreur lors du chargement des membres.</p>';
        });
}

function manageCoAdmins(groupId, groupName) {
    const modal = document.getElementById('groupActionsModal');
    const title = document.getElementById('groupActionsTitle');
    const content = document.getElementById('groupActionsContent');
    
    title.textContent = `Gestion des co-admins - ${groupName}`;
    
    let coadminHtml = `
        <div style="max-height: 400px; overflow-y: auto;">
            <h4>Gérer les co-admins</h4>
            <div id="coadminList">
                <p>Chargement des membres...</p>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <button type="button" onclick="closeGroupActionsModal()" class="modern-btn btn-secondary">
                <span>❌</span>
                Fermer
            </button>
        </div>
    `;
    
    content.innerHTML = coadminHtml;
    
    // Charger les membres pour la gestion des co-admins
    fetch(`../api.php?action=get_group_members&group_id=${groupId}&action_type=coadmin`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('coadminList').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('coadminList').innerHTML = '<p>Erreur lors du chargement des membres.</p>';
        });
}

function removeGroupMember(groupId, groupName) {
    const modal = document.getElementById('groupActionsModal');
    const title = document.getElementById('groupActionsTitle');
    const content = document.getElementById('groupActionsContent');
    
    title.textContent = `Retirer un membre - ${groupName}`;
    
    let removeHtml = `
        <div style="max-height: 400px; overflow-y: auto;">
            <h4>Sélectionner un membre à retirer</h4>
            <div id="removeMemberList">
                <p>Chargement des membres...</p>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <button type="button" onclick="closeGroupActionsModal()" class="modern-btn btn-secondary">
                <span>❌</span>
                Annuler
            </button>
        </div>
    `;
    
    content.innerHTML = removeHtml;
    
    // Charger les membres pour le retrait
    fetch(`../api.php?action=get_group_members&group_id=${groupId}&action_type=remove`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('removeMemberList').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('removeMemberList').innerHTML = '<p>Erreur lors du chargement des membres.</p>';
        });
}

// ========================================
// FONCTIONS POUR LES MODALS
// ========================================

// Fonctions pour le modal d'image
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ========================================
// FONCTIONS UTILITAIRES
// ========================================

function handleGroupActionSelect(select, groupId) {
    const value = select.value;
    if (!value) return;
    // Réinitialiser le select après choix
    select.selectedIndex = 0;
    if (value === 'open_conversation') {
        window.location.href = '?conversation=group:' + groupId + '&tab=discussions';
    } else if (value === 'list_members') {
        document.getElementById('members-list-' + groupId).style.display = 'flex';
    } else if (value === 'manage_coadmins') {
        document.getElementById('coadmins-modal-' + groupId).style.display = 'flex';
    } else if (value === 'remove_member') {
        document.getElementById('remove-member-modal-' + groupId).style.display = 'flex';
    } else if (value === 'delete_group') {
        document.getElementById('delete-group-modal-' + groupId).style.display = 'flex';
    } else if (value === 'leave_group') {
        document.getElementById('leave-group-modal-' + groupId).style.display = 'flex';
    } else if (value === 'add_member') {
        document.getElementById('add-member-modal-' + groupId).style.display = 'flex';
    }
}

// Version alternative de handleGroupActionSelect (du premier script)
function handleGroupActionSelectAlt(selectEl, groupId) {
    const action = selectEl.value;
    selectEl.value = ""; // reset

    switch(action) {
        case 'open_conversation':
            window.location.href = '?conversation=group:' + groupId;
            break;
        case 'list_members':
            document.getElementById('members-list-' + groupId).style.display = 'flex';
            break;
        case 'manage_coadmins':
            document.getElementById('coadmins-modal-' + groupId).style.display = 'flex';
            break;
        case 'remove_member':
            document.getElementById('remove-member-modal-' + groupId).style.display = 'flex';
            break;
        case 'delete_group':
            document.getElementById('delete-group-modal-' + groupId).style.display = 'flex';
            break;
        case 'leave_group':
            document.getElementById('leave-group-modal-' + groupId).style.display = 'flex';
            break;
    }
} 