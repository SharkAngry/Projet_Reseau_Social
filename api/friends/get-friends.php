<?php
require_once '../config/db.php';
require_once '../includes/session-check.php';

header("Content-Type: application/json");
$current_id = $_SESSION['user_id'];

try {
    $sql = "SELECT u.id, u.nom, u.prenom, u.avatar FROM friendships f
            JOIN users u ON (f.user_id_1 = u.id OR f.user_id_2 = u.id)
            WHERE (f.user_id_1 = :current_id OR f.user_id_2 = :current_id)
            AND f.status = 'accepte' AND u.id != :current_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':current_id', $current_id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
}