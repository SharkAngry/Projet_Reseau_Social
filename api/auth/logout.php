<?php
header('Content-Type: application/json; charset=UTF-8');
require '../includes/session-check.php';

$stmt = $pdo->prepare('UPDATE users SET session_token = NULL, session_token_expires = NULL WHERE id = ?');
$stmt->execute([$currentUser['id']]);

echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);