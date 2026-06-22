<?php
header('Content-Type: application/json');
require '../includes/session-check.php';

echo json_encode(['success' => true, 'user' => $currentUser]);