<?php
header('Content-Type: application/json; charset=UTF-8');
require '../config/db.php';
require '../includes/session-check.php';

$stmt = $pdo->prepare("SELECT n.id, n.type, n.article_id, n.is_read, n.created_at,
                              u.prenom, u.nom
                       FROM notifications n
                       JOIN users u ON u.id = n.actor_id
                       WHERE n.user_id = ?
                       ORDER BY n.created_at DESC
                       LIMIT 30");
$stmt->execute([$currentUser['id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($notifications as &$notification) {
    $notification['is_read'] = (bool) $notification['is_read'];
}
unset($notification);

$unreadStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE');
$unreadStmt->execute([$currentUser['id']]);

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => (int) $unreadStmt->fetchColumn(),
]);