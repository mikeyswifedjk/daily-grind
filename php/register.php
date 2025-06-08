<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../php/PHPMailer/src/Exception.php';
require '../php/PHPMailer/src/PHPMailer.php';
require '../php/PHPMailer/src/SMTP.php';

// DB connection
$conn = new mysqli("localhost", "root", "", "daily_grind");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function isEmailUnique($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows === 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
    $name         = $_POST["name"];
    $email        = $_POST["email"];
    $password     = $_POST["password"];
    $cpassword    = $_POST["confirm_password"];
    $phone        = $_POST["contact_number"];
    $address      = $_POST["address"];
    $fname        = $_POST["first_name"];
    $mname        = $_POST["middle_name"];
    $lname        = $_POST["last_name"];
    $image        = '';

    if (!isEmailUnique($conn, $email)) {
        echo "<script>alert('Email already exists. Please choose a different email.'); window.history.back();</script>";
        exit;
    }

    if ($password !== $cpassword) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit;
    }

    $verification_code = substr(strval(rand(100000, 999999)), 0, 6);
    $hashed_password   = password_hash($password, PASSWORD_DEFAULT);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shopbee800@gmail.com';
        $mail->Password   = 'fqjhitqjtddbxqvz'; // Consider securing with env variable
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('shopbee800@gmail.com', 'Daily Grind');
        $mail->addReplyTo('shopbee800@gmail.com', 'Daily Grind');
        $mail->addBCC('shopbee800@gmail.com');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h2>Welcome to Daily Grind!</h2>
                <p>Dear $name,</p>
                <p>Thank you for joining Daily Grind. We are thrilled to have you with us. To complete your registration, please use the verification code below:</p>
                <p style='background-color: #f0f0f0; padding: 10px; border-radius: 5px;'>
                    <strong style='font-size: 24px; color: #6c91c2;'>$verification_code</strong>
                </p>
                <p>If you did not register for a Daily Grind account, please disregard this email.</p>
                <p>Best Regards,<br>Daily Grind Team</p>
            </div>
        ";
        $mail->send();

        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, verification_code, email_verified_at, attempts, contact_number, address, first_name, middle_name, last_name, image_path)
            VALUES (?, ?, ?, ?, NULL, 0, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssssssss", $name, $email, $hashed_password, $verification_code, $phone, $address, $fname, $mname, $lname, $image);
        $stmt->execute();

        header("Location: http://localhost/vincere-de-floret/php/email-verification.php?email=" . urlencode($email));
        exit;
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration</title>
  <link rel="icon" type="image/png" href="../assets/logo/logo2.png" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/register.css" />
</head>
<body>
  <div class="container">
    <h3>Create Your Account</h3>

    <form method="POST" onsubmit="return validateForm();">
      <div class="form-group">
        <input type="text" id="first_name" name="first_name" placeholder="First Name" required />
        <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" required />
      </div>

      <div class="form-group">
        <input type="text" id="last_name" name="last_name" placeholder="Last Name" required />
        <input type="tel" id="contact_number" name="contact_number" placeholder="Contact Number" required />
      </div>

      <input type="text" id="address" name="address" placeholder="Address" required class="full-width" />
      <input type="text" name="name" placeholder="Username" required class="full-width" />
      <input type="email" name="email" placeholder="Email Address" required class="full-width" />
      <input type="password" name="password" id="password" placeholder="Password" required class="full-width" />
      <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required class="full-width" />

      <div class="checkbox-container">
        <input type="checkbox" name="word" id="word" required />
        <label for="word">
          I agree to the <a href="#">Terms of Service</a> & <a href="#">Privacy Policy</a>.
        </label>
      </div>

      <input type="submit" name="register" value="REGISTER" />

      <p class="login">
        Already have an account? <a href="login.php">Login</a>
      </p>
    </form>
  </div>

  <script>
    function validateForm() {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      if (password !== confirmPassword) {
        alert('Password and Confirm Password do not match.');
        return false;
      }
      return true;
    }
  </script>
</body>
</html>