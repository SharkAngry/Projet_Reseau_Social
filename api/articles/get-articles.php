<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';

$current_user_id = $currentUser['id'];

try {
    $sql = "SELECT a.id, a.description, a.image, a.created_at,
                   u.nom, u.prenom, u.photo_profil,
                   (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND type = 'like') AS likes_count,
                   (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND type = 'dislike') AS dislikes_count,
                   (SELECT type FROM reactions WHERE article_id = a.id AND user_id = :uid) AS my_reaction
            FROM articles a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $current_user_id]);
    echo json_encode(['success' => true, 'articles' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur get-articles: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}