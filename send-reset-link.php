<?php
require 'db.php'; // Inclure la connexion à la base de données
require 'mail/PHPMailer/src/PHPMailer.php';
require 'mail/PHPMailer/src/SMTP.php';
require 'mail/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50)); // Générer un token unique

    // Vérifier si l'e-mail existe dans la base de données
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Insérer le token dans la base de données
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();

        // Envoyer l'e-mail de réinitialisation
        $resetLink = "http://localhost/Job-Portal/reset-password.php?token=" . $token;
        $subject = "Réinitialisation de mot de passe";
        $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $resetLink;

        $mail = new PHPMailer(true);
        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'votre-email@gmail.com'; // Remplacez par votre adresse Gmail
            $mail->Password = 'votre-mot-de-passe'; // Remplacez par votre mot de passe Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Destinataire
            $mail->setFrom('votre-email@gmail.com', 'Job Portal');
            $mail->addAddress($email);

            // Contenu de l'e-mail
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            echo "Un e-mail de réinitialisation a été envoyé à votre adresse e-mail.";
        } catch (Exception $e) {
            echo "Échec de l'envoi de l'e-mail. Erreur: {$mail->ErrorInfo}";
        }
    } else {
        echo "Aucun compte trouvé avec cette adresse e-mail.";
    }
}
?>