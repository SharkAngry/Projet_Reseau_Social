<?php
require __DIR__ . '/../config/db.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Token manquant']);
    exit;
}

$token = substr($authHeader, 7);

$stmt = $pdo->prepare('SELECT id, nom, prenom, email, photo_profil FROM users WHERE session_token = ?');
$stmt->execute([$token]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session invalide ou expirée']);
    exit;
}