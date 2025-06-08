<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require "connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
require './PHPMailer/src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);

    $update = mysqli_query($conn, "UPDATE users SET verification_code = '$verification_code' WHERE email = '$email'");

    if ($update) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'shopbee800@gmail.com';
            $mail->Password = 'fqjhitqjtddbxqvz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('shopbee800@gmail.com', 'Daily Grind');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification Code';
            $mail->Body    = "<h3>Email Verification for Daily Grind Account</h3>
            <p>Dear User,</p>
            <p>Thank you for registering with Daily Grind! To ensure that your email address is valid, we just need you to verify it by entering the following code:</p>
            <p style='font-size: 2em; font-weight: bold;'>$verification_code</p>
            <p>This code is valid for 1 hour. If you did not request an account, please disregard this email.</p>
            <p>Thank you for your cooperation!</p>
            <p>Best regards,</p>
            <p>The Daily Grind Team</p>";
            
            $mail->send();

            header("Location: email-verification.php?email=" . urlencode($email));
            exit();
        } catch (Exception $e) {
            echo "<script>alert('Mailer Error: {$mail->ErrorInfo}'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Failed to generate verification code.'); window.history.back();</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
    exit();
}
?>