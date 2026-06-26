<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';

$article_id = isset($_GET['article_id']) ? intval($_GET['article_id']) : null;

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "ID de l'article manquant."]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT c.id, c.contenu, c.created_at, u.nom, u.prenom, u.photo_profil
                            FROM comments c JOIN users u ON c.user_id = u.id
                            WHERE c.article_id = ? ORDER BY c.created_at ASC");
    $stmt->execute([$article_id]);
    echo json_encode(['success' => true, 'comments' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur get-comments: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}