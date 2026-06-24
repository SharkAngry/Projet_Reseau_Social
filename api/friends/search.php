<?php
require_once '../config/db.php';
require_once '../includes/session-check.php';

header("Content-Type: application/json");
$current_id = $_SESSION['user_id'];
$query_search = isset($_GET['query']) ? trim($_GET['query']) : '';

try {
    if (!empty($query_search)) {
        $sql = "SELECT id, nom, prenom, avatar FROM users 
                WHERE id != :current_id AND (nom LIKE :q OR prenom LIKE :q)
                AND id NOT IN (
                    SELECT user_id_1 FROM friendships WHERE user_id_2 = :current_id
                    UNION
                    SELECT user_id_2 FROM friendships WHERE user_id_1 = :current_id
                ) LIMIT 15";
        $stmt = $pdo->prepare($sql);
        $search_param = "%" . $query_search . "%";
        $stmt->bindParam(':q', $search_param);
    } else {
        $sql = "SELECT id, nom, prenom, avatar FROM users 
                WHERE id != :current_id 
                AND id NOT IN (
                    SELECT user_id_1 FROM friendships WHERE user_id_2 = :current_id
                    UNION
                    SELECT user_id_2 FROM friendships WHERE user_id_1 = :current_id
                ) ORDER BY id DESC LIMIT 10";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->bindParam(':current_id', $current_id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
}