<?php
header('Content-Type: application/json');
require '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$token = trim($data['token'] ?? '');
$newPassword = $data['new_password'] ?? '';

if (empty($token) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token et nouveau mot de passe requis']);
    exit;
}

if (strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, reset_token_expires FROM users WHERE reset_token = ?');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user || strtotime($user['reset_token_expires']) < time()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Lien invalide ou expiré']);
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?');
$stmt->execute([$hashedPassword, $user['id']]);

echo json_encode(['success' => true, 'message' => 'Mot de passe réinitialisé avec succès']);