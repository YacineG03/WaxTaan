# WaxTaan - Application de Messagerie

Une application de messagerie moderne développée en PHP avec stockage XML.

## 🚀 Fonctionnalités

### ✅ Fonctionnalités de base
- **Authentification** : Inscription et connexion des utilisateurs
- **Gestion des contacts** : Ajout, suppression et affichage des contacts
- **Création de groupes** : Création de groupes avec plusieurs membres
- **Messagerie** : Envoi et réception de messages texte et fichiers
- **Profil utilisateur** : Modification des informations personnelles et photo de profil


WaxTaan/
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



## 🚀 Installation

1. **Prérequis** : Serveur web avec PHP (XAMPP recommandé)
2. **Placement** : Copier le projet dans le dossier `htdocs`
3. **Accès** : Ouvrir `http://localhost/Projet_xmll/`

## 📱 Utilisation

1. **Inscription/Connexion** : Créer un compte ou se connecter
2. **Ajouter des contacts** : Ajouter des contacts par numéro de télételephone
3. **Créer des groupes** : Créer des groupes avec plusieurs contacts
4. **Envoyer des messages** : Cliquer sur "Chat" pour discuter
5. **Voir les nouveaux messages** : Les contacts avec nouveaux messages sont mis en évidence
