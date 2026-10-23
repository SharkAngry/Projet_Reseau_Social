<?php
header('Content-Type: application/json');
require '../includes/session-check.php';

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');

if (empty($nom) || empty($prenom)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nom et prénom requis']);
    exit;
}

$photoPath = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileType = mime_content_type($_FILES['photo']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Format de fichier non autorisé']);
        exit;
    }

    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Image trop volumineuse (max 5 Mo)']);
        exit;
    }

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $extension = $extensions[$fileType];
    $fileName = 'user_' . $currentUser['id'] . '_' . time() . '.' . $extension;
    $uploadDir = __DIR__ . '/../../assets/images/profiles/';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Impossible de préparer le stockage de la photo']);
        exit;
    }

    $destination = $uploadDir . $fileName;
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Impossible d\'enregistrer la photo']);
        exit;
    }

    $photoPath = 'assets/images/profiles/' . $fileName;
}

if ($photoPath) {
    $stmt = $pdo->prepare('UPDATE users SET nom = ?, prenom = ?, photo_profil = ? WHERE id = ?');
    $stmt->execute([$nom, $prenom, $photoPath, $currentUser['id']]);
} else {
    $stmt = $pdo->prepare('UPDATE users SET nom = ?, prenom = ? WHERE id = ?');
    $stmt->execute([$nom, $prenom, $currentUser['id']]);
}

echo json_encode(['success' => true, 'message' => 'Profil mis à jour']);