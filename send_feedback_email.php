<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// customer email values
$customerEmail = $_POST['email']; 

// Gmail SMTP settings
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'nehansuriyaarachchi50@gmail.com';  
    $mail->Password = 'umhcdnwlcetgpved';     //gmail app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // senders email
    $mail->setFrom('nehansuriyaarachchi50@gmail.com', 'PPA Garment Factory');
    $mail->addAddress($customerEmail);

    // email message
    $mail->isHTML(true);
    $mail->Subject = "We value your feedback!";
    $mail->Body = "
        <p>Dear Customer,</p>
        <p>Thank you for your recent order. We would love to hear your feedback.</p>
        <p>Please click the link below to submit your feedback:</p>
        <p><a href='http://localhost/The_Debugger_PPA/customer_feedback.html'>Submit Feedback</a></p>
        <p>Best regards,<br>PPA Garment Factory</p>
    ";

    $mail->send();
    echo "✅ Feedback request sent successfully!";
} catch (Exception $e) {
    echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
