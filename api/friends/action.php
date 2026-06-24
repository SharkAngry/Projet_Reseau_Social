<?php
require_once '../config/db.php';
require_once '../includes/session-check.php';

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));
$current_id = $_SESSION['user_id'];

if (!empty($data->action) && !empty($data->target_id)) {
    $target_id = intval($data->target_id);
    $action = $data->action;
    $u1 = min($current_id, $target_id);
    $u2 = max($current_id, $target_id);

    try {
        if ($action === 'send') {
            $sql = "INSERT INTO friendships (user_id_1, user_id_2, status, action_user_id) 
                    VALUES (:u1, :u2, 'en_attente', :action_id)
                    ON DUPLICATE KEY UPDATE status='en_attente', action_user_id=:action_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':action_id', $current_id, PDO::PARAM_INT);
        } elseif ($action === 'accept') {
            $sql = "UPDATE friendships SET status = 'accepte' WHERE user_id_1 = :u1 AND user_id_2 = :u2";
            $stmt = $pdo->prepare($sql);
        } elseif ($action === 'decline' || $action === 'remove') {
            $sql = "DELETE FROM friendships WHERE user_id_1 = :u1 AND user_id_2 = :u2";
            $stmt = $pdo->prepare($sql);
        }
        $stmt->bindParam(':u1', $u1, PDO::PARAM_INT);
        $stmt->bindParam(':u2', $u2, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "Action validée."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Requête incomplète."]);
}