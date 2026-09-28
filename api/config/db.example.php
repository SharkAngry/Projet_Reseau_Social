<?php
// Copier ce fichier en db.php et remplir les valeurs.
// db.php est ignoré par git : ne jamais y commiter de vrais identifiants.
$host = 'localhost';
$dbname = 'reseau_social';
$username = 'ton_utilisateur';
$password = 'ton_mot_de_passe';

$pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);