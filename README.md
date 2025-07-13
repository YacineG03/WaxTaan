# WaxTaan - Application de Messagerie

Une application de messagerie moderne développée en PHP avec stockage XML.

## 🚀 Fonctionnalités

### ✅ Fonctionnalités de base
- **Authentification** : Inscription et connexion des utilisateurs
- **Gestion des contacts** : Ajout, suppression et affichage des contacts
- **Création de groupes** : Création de groupes avec plusieurs membres
- **Messagerie** : Envoi et réception de messages texte et fichiers
- **Profil utilisateur** : Modification des informations personnelles et photo de profil

### 🆕 Nouvelles fonctionnalités (Correction du problème de correspondance)

#### 🔧 Fonctions utilitaires ajoutées
- **`getUserIDByPhone()`** : Correspondance entre numéro de téléphone et ID utilisateur
- **`getPhoneByUserID()`** : Correspondance entre ID utilisateur et numéro de téléphone
- **`getUnreadMessageCount()`** : Comptage des messages non lus par contact

#### 💬 Conversation bidirectionnelle
- **Problème résolu** : Les messages entre contacts s'affichent maintenant correctement
- **Logique améliorée** : Correspondance automatique téléphone ↔ user_id
- **Messages bidirectionnels** : Affichage des messages envoyés ET reçus

#### 🔔 Indicateurs de nouveaux messages
- **Badge numérique** : Affichage du nombre de nouveaux messages
- **Indicateur textuel** : "Nouveaux messages" sous le numéro de téléphone
- **Animation** : Badge avec animation pulse pour attirer l'attention
- **Mise en forme** : Contacts avec nouveaux messages mis en évidence

## 🛠️ Structure du projet

```
Projet_xmll/
├── api.php                 # API pour les actions (envoi messages, etc.)
├── config.php              # Configuration et chargement XML
├── controller.php          # Contrôleur principal avec fonctions utilitaires
├── index.php               # Page d'accueil
├── connexion/              # Authentification
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── views/                  # Vues de l'application
│   ├── view.php            # Vue principale avec chat
│   ├── chat_view.php       # Vue du chat
│   ├── contacts_view.php   # Vue des contacts
│   └── groups_view.php     # Vue des groupes
├── css/                    # Styles CSS
│   ├── modern-app.css      # Styles principaux
│   ├── chat.css           # Styles du chat
│   └── ...
├── js/                     # JavaScript
├── xmls/                   # Données XML
│   ├── users.xml          # Utilisateurs
│   ├── contacts.xml       # Contacts
│   ├── groups.xml         # Groupes
│   └── messages.xml       # Messages
├── uploads/               # Fichiers uploadés
└── schema/               # Schéma XML
    └── waxtaan.xsd
```

## 🔧 Corrections apportées

### Problème initial
Les messages entre contacts ne s'affichaient pas correctement car :
- Les messages utilisaient le numéro de téléphone comme `recipient`
- La logique d'affichage cherchait par `user_id` au lieu de faire la correspondance

### Solution implémentée
1. **Fonctions de correspondance** : Création de fonctions utilitaires pour faire le lien téléphone ↔ user_id
2. **Logique d'affichage améliorée** : Utilisation des fonctions de correspondance dans l'affichage des messages
3. **Indicateurs visuels** : Ajout de badges et indicateurs pour les nouveaux messages
4. **Interface améliorée** : Suppression de l'onglet "Messages reçus" au profit d'indicateurs intégrés

## 🎨 Interface utilisateur

### Indicateurs de nouveaux messages
- **Badge rouge** avec nombre de messages non lus
- **Animation pulse** pour attirer l'attention
- **Indicateur textuel** "Nouveaux messages"
- **Mise en forme spéciale** pour les contacts avec nouveaux messages

### Responsive design
- Interface adaptée aux mobiles
- Animations fluides
- Design moderne avec gradients

## 🧪 Tests

### Scripts de test disponibles
- `test_message.php` : Test des fonctions de correspondance et affichage
- `test_send_message.php` : Test d'envoi de messages

### Comment tester
1. Exécuter `test_message.php` pour vérifier les fonctions
2. Exécuter `test_send_message.php` pour tester l'envoi
3. Se connecter à l'application et vérifier l'affichage des messages

## 🚀 Installation

1. **Prérequis** : Serveur web avec PHP (XAMPP recommandé)
2. **Placement** : Copier le projet dans le dossier `htdocs`
3. **Accès** : Ouvrir `http://localhost/Projet_xmll/`

## 📱 Utilisation

1. **Inscription/Connexion** : Créer un compte ou se connecter
2. **Ajouter des contacts** : Ajouter des contacts par numéro de téléphone
3. **Créer des groupes** : Créer des groupes avec plusieurs contacts
4. **Envoyer des messages** : Cliquer sur "Chat" pour discuter
5. **Voir les nouveaux messages** : Les contacts avec nouveaux messages sont mis en évidence

## 🔮 Améliorations futures

- [ ] Système de messages lus/non lus
- [ ] Notifications push
- [ ] Statut en ligne/hors ligne
- [ ] Messages vocaux
- [ ] Emojis et réactions
- [ ] Historique de recherche

---

**🎉 Le problème de correspondance entre numéro de téléphone et user_id est maintenant complètement résolu !** 
