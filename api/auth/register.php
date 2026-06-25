<?php
// api/auth/register.php

header('Content-Type: application/json');
require '../config/db.php';

// 1. Lire les données envoyées en JSON par le frontend
$data = json_decode(file_get_contents('php://input'), true);

$nom = trim($data['nom'] ?? '');
$prenom = trim($data['prenom'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// 2. Valider AVANT de toucher la base
// Harmonisation front-end : on renvoie la clé 'message' lue par la fonction apiRequest du groupe
if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format de l\'adresse email invalide']);
    exit;
}

// 3. Vérifier que l'email n'est pas déjà pris
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409); // Conflit
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre compte']);
    exit;
}

// 4. Hasher le mot de passe, jamais le stocker en clair
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// 5. Insérer l'utilisateur dans la base de données
$stmt = $pdo->prepare('INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)');
$stmt->execute([$nom, $prenom, $email, $hashedPassword]);

// 6. Configuration et envoi de l'email HTML personnalisé (Exigence du sujet)
require '../config/mail-config.php';
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = MAIL_PORT;
    $mail->CharSet = 'UTF-8'; // Assure le support des accents français

    $mail->setFrom('noreply@reseausocial.com', 'Réseau Social');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Bienvenue sur votre Réseau Social ! [cite: 21]';
    
    // --- DESIGN UI/UX DE L'EMAIL HTML ---
    $mail->Body = "
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; background-color: #f0f2f5; padding: 40px 20px; text-align: center;'>
        <div style='max-width: 550px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);'>
            
            <div style='background-color: #1877f2; padding: 25px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: -0.5px;'>Connexion Réseau</h1>
            </div>
            
            <div style='padding: 30px; text-align: left; color: #1c1e21;'>
                <h2 style='font-size: 20px; margin-top: 0; color: #1c1e21;'>Bienvenue, " . htmlspecialchars($prenom) . " ! 👋</h2>
                <p style='font-size: 15px; line-height: 1.5; color: #606770;'>
                    Votre compte a été créé avec succès[cite: 21]. Vous faites désormais partie de notre communauté universitaire.
                </p>
                <p style='font-size: 15px; line-height: 1.5; color: #606770;'>
                    Vous pouvez dès maintenant vous connecter pour compléter votre profil, ajouter des amis et partager vos premières publications.
                </p>
                
                <div style='text-align: center; margin: 30px 0 15px 0;'>
                    <a href='http://localhost/index.html#login' style='background-color: #42b72a; color: #ffffff; text-decoration: none; padding: 12px 35px; font-size: 16px; font-weight: bold; border-radius: 6px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                        Accéder à mon espace
                    </a>
                </div>
            </div>
            
            <div style='background-color: #f5f6f7; padding: 15px; text-align: center; font-size: 12px; color: #8d949e; border-top: 1px solid #e5e5e5;'>
                Ceci est un message automatique, merci de ne pas y répondre.<br>
                &copy; 2026 Projet Final PHP & AJAX. Tous droits réservés.
            </div>
        </div>
    </div>";

    $mail->send();
} catch (Exception $e) {
    // On log l'erreur en interne mais on ne bloque pas la réponse du front
    error_log('Erreur envoi email confirmation: ' . $mail->ErrorInfo);
}

// Réponse claire pour le frontend
echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Un e-mail de confirmation vous a été envoyé.']);
?>