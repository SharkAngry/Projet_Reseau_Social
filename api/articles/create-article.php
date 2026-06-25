<?php
// api/articles/create-article.php

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
require '../config/db.php';

// Récupération de l'ID utilisateur connecté via les en-têtes HTTP
$headers = getallheaders();
$current_user_id = isset($headers['X-User-Id']) ? intval($headers['X-User-Id']) : null;

if (!$current_user_id) {
    // Valeur de secours pour tes tests locaux si l'auth n'est pas encore branchée
    $current_user_id = 1; 
}

// Récupération de la description (qui arrive via FormData ou $_POST standard en cas d'upload de fichier)
$description = trim($_POST['description'] ?? '');

if (empty($description)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le texte de la publication ne peut pas être vide.']);
    exit;
}

$image_name = null;

// Gestion de l'image optionnelle
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Extensions autorisées
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($fileExtension, $allowedExtensions)) {
        // Renommer le fichier de manière unique : ex post_1_1719330000.png
        $image_name = 'post_' . $current_user_id . '_' . time() . '.' . $fileExtension;
        
        // Dossier de destination
        $uploadFileDir = '../../assets/images/posts/';
        
        // Créer le dossier s'il n'existe pas encore
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        $dest_path = $uploadFileDir . $image_name;
        
        if (!move_uploaded_file($fileTmpPath, $dest_path)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement de l\'image sur le serveur.']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format d\'image non supporté (uniquement JPG, PNG, GIF).']);
        exit;
    }
}

try {
    // Insertion en base de données
    $query = "INSERT INTO articles (user_id, description, image) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$current_user_id, $description, $image_name]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Publication partagée avec succès !'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'enregistrement de l\'article : ' . $e->getMessage()
    ]);
}