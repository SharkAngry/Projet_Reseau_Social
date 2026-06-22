<?php
header('Content-Type: application/json');
require '../includes/session-check.php';

$data = json_decode(file_get_contents('php://input'), true);

$oldPassword = $data['old_password'] ?? '';
$newPassword = $data['new_password'] ?? '';

if (empty($oldPassword) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ancien et nouveau mot de passe requis']);
    exit;
}

if (strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Le nouveau mot de passe doit contenir au moins 8 caractères']);
    exit;
}

$stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
$stmt->execute([$currentUser['id']]);
$user = $stmt->fetch();

if (!password_verify($oldPassword, $user['password'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Ancien mot de passe incorrect']);
    exit;
}

$hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([$hashedNewPassword, $currentUser['id']]);

echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès']);