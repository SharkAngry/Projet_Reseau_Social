<?php
// api/articles/add-comment.php

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
require '../config/db.php';

$headers = getallheaders();
$current_user_id = isset($headers['X-User-Id']) ? intval($headers['X-User-Id']) : null;

if (!$current_user_id) {
    $current_user_id = 1; // ID de test local
}

$data = json_decode(file_get_contents('php://input'), true);
$article_id = isset($data['article_id']) ? intval($data['article_id']) : null;
$contenu = trim($data['contenu'] ?? '');

if (!$article_id || empty($contenu)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}

try {
    // 1. Insertion du commentaire
    $query = "INSERT INTO comments (article_id, user_id, contenu) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$article_id, $current_user_id, $contenu]);
    $comment_id = $pdo->lastInsertId();

    // 2. Récupérer le commentaire tout juste créé avec les infos de l'auteur pour l'affichage immédiat
    $selectQuery = "
        SELECT c.id, c.contenu, c.created_at, u.nom, u.prenom, u.photo_profil 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ";
    $selectStmt = $pdo->prepare($selectQuery);
    $selectStmt->execute([$comment_id]);
    $newComment = $selectStmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Commentaire ajouté !',
        'comment' => $newComment
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout : ' . $e->getMessage()]);
}