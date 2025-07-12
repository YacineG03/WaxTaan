<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion/login.php');
    exit;
}

// Définir le chemin absolu de base
// $basePath = 'C:/xampp/htdocs/Projet_xmll/xmls/';
$basePath = __DIR__ . '/xmls/';

// Charger les données XML avec vérification
$users = @simplexml_load_file($basePath . 'users.xml');
if ($users === false) {
    die('Erreur : Impossible de charger users.xml. Vérifiez le fichier ou le chemin.');
}

$contacts = @simplexml_load_file($basePath . 'contacts.xml');
if ($contacts === false) {
    die('Erreur : Impossible de charger contacts.xml. Vérifiez le fichier ou le chemin.');
}

$groups = @simplexml_load_file($basePath . 'groups.xml');
if ($groups === false) {
    die('Erreur : Impossible de charger groups.xml. Vérifiez le fichier ou le chemin.');
}

$messages = @simplexml_load_file($basePath . 'messages.xml');
if ($messages === false) {
    die('Erreur : Impossible de charger messages.xml. Vérifiez le fichier ou le chemin.');
}

// var_dump($users);
// var_dump($contacts);
// var_dump($groups);
// var_dump($messages);
?>
