<?php
header('Content-Type: application/json');
require '../config/db.php';

// 1. Lire les données envoyées en JSON par le frontend
$data = json_decode(file_get_contents('php://input'), true);

$nom = trim($data['nom'] ?? '');
$prenom = trim($data['prenom'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// 2. Valider AVANT de toucher la base
if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email invalide']);
    exit;
}

// 3. Vérifier que l'email n'est pas déjà pris
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Cet email est déjà utilisé']);
    exit;
}

// 4. Hasher le mot de passe, jamais le stocker en clair
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// 5. Insérer
$stmt = $pdo->prepare('INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)');
$stmt->execute([$nom, $prenom, $email, $hashedPassword]);
require '../config/mail-config.php';
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = MAIL_PORT;

    $mail->setFrom('noreply@reseausocial.com', 'Réseau Social');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Bienvenue sur Réseau Social';
    $mail->Body = "<div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto;'>
        <h2 style='color: #1877f2;'>Bienvenue, $prenom !</h2>
        <p>Ton compte a été créé avec succès. Tu peux dès maintenant te connecter et commencer à partager.</p>
    </div>";
    $mail->send();
} catch (Exception $e) {
    error_log('Erreur envoi email confirmation: ' . $mail->ErrorInfo);
}

echo json_encode(['success' => true, 'message' => 'Inscription réussie']);?>