<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require "connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../php/PHPMailer/src/Exception.php';
require '../php/PHPMailer/src/PHPMailer.php';
require '../php/PHPMailer/src/SMTP.php';

if (!isset($_GET['email']) || !filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Invalid email address.'); window.history.back();</script>";
    exit;
}

$email = $_GET['email'];
$new_code = rand(100000, 999999);

// Update verification code in the database
$stmt = $conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
$stmt->bind_param("ss", $new_code, $email);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    // Fetch user's name for the email
    $stmt_user = $conn->prepare("SELECT name FROM users WHERE email = ?");
    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $stmt_user->bind_result($name);
    $stmt_user->fetch();
    $stmt_user->close();

    // Send email with new code
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shopbee800@gmail.com';
        $mail->Password   = 'fqjhitqjtddbxqvz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('shopbee800@gmail.com', 'Daily Grind');
        $mail->addAddress($email, $name);
        $mail->addBCC('shopbee800@gmail.com');
        $mail->isHTML(true);
        $mail->Subject = 'Resent Verification Code';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h2>Email Verification (Resent)</h2>
                <p>Dear $name,</p>
                <p>You requested a new verification code. Use the code below to verify your email:</p>
                <p style='background-color: #f0f0f0; padding: 10px; border-radius: 5px;'>
                    <strong style='font-size: 24px; color: #6c91c2;'>$new_code</strong>
                </p>
                <p>If you did not request this, you can safely ignore it.</p>
                <p>Best Regards,<br>Daily Grind Team</p>
            </div>
        ";
        $mail->send();

        $type = isset($_GET['type']) ? $_GET['type'] : '';

        echo "<script>
            alert('A new code has been sent to your email.');
            window.location.href='email-verification.php?email=" . urlencode($email) . "&type=" . urlencode($type) . "';
        </script>";
    } catch (Exception $e) {
        echo "<script>alert('Failed to send email. Error: {$mail->ErrorInfo}'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Could not update verification code.'); window.history.back();</script>";
}
?>