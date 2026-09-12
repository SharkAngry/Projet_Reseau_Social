<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';

$stmt = $pdo->prepare('UPDATE notifications SET is_read = TRUE WHERE user_id = ?');
$stmt->execute([$currentUser['id']]);

echo json_encode(['success' => true]);