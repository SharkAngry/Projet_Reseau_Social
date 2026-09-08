<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';

$current_user_id = $currentUser['id'];
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = min(20, max(1, (int) ($_GET['per_page'] ?? 10)));
$offset = ($page - 1) * $perPage;

try {
    $sql = "SELECT a.id, a.description, a.image, a.created_at,
                   u.nom, u.prenom, u.photo_profil,
                   (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND type = 'like') AS likes_count,
                   (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND type = 'dislike') AS dislikes_count,
                   (SELECT type FROM reactions WHERE article_id = a.id AND user_id = :uid) AS my_reaction
            FROM articles a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC, a.id DESC
            LIMIT :per_page OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':uid', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'articles' => $articles,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => count($articles) === $perPage,
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Erreur get-articles: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}