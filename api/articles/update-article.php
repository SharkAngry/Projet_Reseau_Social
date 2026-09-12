<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';

$data = json_decode(file_get_contents('php://input'), true);
$articleId = (int) ($data['id'] ?? 0);
$description = trim($data['description'] ?? '');

if (!$articleId || $description === '' || mb_strlen($description) > 5000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Publication invalide.']);
    exit;
}

$stmt = $pdo->prepare('UPDATE articles SET description = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$description, $articleId, $currentUser['id']]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Publication introuvable ou non autorisée.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Publication modifiée.']);