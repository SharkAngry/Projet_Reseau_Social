<?php
header('Content-Type: application/json');
require '../config/db.php';
require '../config/mail-config.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email requis']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nom, prenom FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
    $stmt->execute([$token, $expires, $user['id']]);

    $resetLink = "http://localhost/projet-reseau-social/vues/clients/reset-password.html?token=$token";

    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; border: 1px solid #ddd; border-radius: 8px; padding: 20px;'>
        <h2 style='color: #1877f2;'>Réinitialisation du mot de passe</h2>
        <p>Bonjour {$user['prenom']},</p>
        <p>Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
        <p style='text-align: center;'>
            <a href='$resetLink' style='background-color: #1877f2; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Réinitialiser mon mot de passe</a>
        </p>
        <p style='color: #777; font-size: 13px;'>Ce lien expire dans 1 heure. Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
    </div>";

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
        $mail->Subject = 'Réinitialisation de votre mot de passe';
        $mail->Body = $htmlBody;

        $mail->send();
    } catch (Exception $e) {
        error_log('Erreur envoi email: ' . $mail->ErrorInfo);
    }
}

echo json_encode(['success' => true, 'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé']);