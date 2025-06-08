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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["next"])) {
    $email = $_POST["email"];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Email not found.'); window.history.back();</script>";
        exit;
    }

    // Generate token and verification code
    $reset_token = bin2hex(random_bytes(16));
    $expiration_time = date("Y-m-d H:i:s", strtotime("+1 hour"));
    $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);

    // Update user record
    $update_stmt = $conn->prepare("
        UPDATE users 
        SET reset_token = ?, reset_token_expiration = ?, verification_code = ? 
        WHERE email = ?
    ");
    $update_stmt->bind_param("ssss", $reset_token, $expiration_time, $verification_code, $email);
    $update_stmt->execute();

    // Send verification code via email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'vinceredefloret@gmail.com';
        $mail->Password   = 'ossmyxegmiivobzm'; // Consider storing this securely
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('vinceredefloret@gmail.com', 'Vincere De Floret');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';
        $mail->Body = "
            <p>Dear User,</p>
            <p>We have received your request to reset your password. Please use the verification code below to reset your password:</p>
            <p style='font-size: 24px; font-weight: bold; background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;'>$verification_code</p>
            <p>If you did not request for a password reset, please disregard this email.</p>
            <p>Thank you for using our services.</p>
            <p>Best regards,</p>
            <p>Vincere De Floret Team</p>
        ";
        $mail->send();

        // Redirect to verification
        header("Location: email-verification.php?email=" . urlencode($email) . "&type=password");
        exit;

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="icon" type="image/png" href="../assets/logo/logo2.png" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/forgotpassword.css">
</head>
<body>

  <div class="container-content">
    <p class="forgot-label">Forgot Password?</p>
    <p class="email-label">Enter your email address</p>
    <form method="POST">
      <input type="email" name="email" placeholder="info@gmail.com" required />
      <input type="submit" name="next" value="Next">
    </form>
    <a href="login.php"><button class="back">Back</button></a>
  </div>

</body>
</html>
