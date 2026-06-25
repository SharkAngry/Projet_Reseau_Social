<?php
// api/articles/get-comments.php

header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';

$article_id = isset($_GET['article_id']) ? intval($_GET['article_id']) : null;

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de l\'article manquant.']);
    exit;
}

try {
    // Requête SQL avec jointure pour avoir l'identité de l'auteur du commentaire
    $query = "
        SELECT 
            c.id, 
            c.contenu, 
            c.created_at, 
            u.nom, 
            u.prenom, 
            u.photo_profil
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.article_id = ?
        ORDER BY c.created_at ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$article_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($comments);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}