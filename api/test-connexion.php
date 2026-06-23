<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// On inclut la connexion centralisée de tes camarades
require_once '../config/db.php';
echo json_encode(['success' => true, 'message' => 'Connexion réussie']);?>