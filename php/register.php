<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../php/PHPMailer/src/Exception.php';
require '../php/PHPMailer/src/PHPMailer.php';
require '../php/PHPMailer/src/SMTP.php';

// Function to check if an email already exists in the database
function isEmailUnique($conn, $email) {
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    return (mysqli_num_rows($result) == 0);
}

// Check if a form parameter named "register" has been submitted via the HTTP POST method.
if (isset($_POST["register"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $cpassword = $_POST["confirm_password"];
    $phone_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $fname = $_POST['first_name'];
    $mname = $_POST['middle_name'];
    $lname = $_POST['last_name'];

    // Check if the email is unique (not already in the database)
    $conn = mysqli_connect("localhost:3306", "root", "", "vincere_de_floret");

    if (!isEmailUnique($conn, $email)) {
        echo "<script>alert('Email already exists. Please choose a different email.'); window.history.back();</script>";
    } else {
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            // Enable verbose debug output
            $mail->SMTPDebug = 0; // SMTP::DEBUG_SERVER;
            // Send using SMTP
            $mail->isSMTP();
            // Set the SMTP server to send through
            $mail->Host = 'smtp.gmail.com';
            // Enable SMTP authentication
            $mail->SMTPAuth = true;
            // SMTP username
            $mail->Username = 'vinceredefloret@gmail.com'; // email that will be host
            // SMTP password
            $mail->Password = 'ossmyxegmiivobzm'; // app name password
            // Enable TLS encryption;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            $mail->Port = 587;
            // Sender
            $mail->setFrom('vinceredefloret@gmail.com', 'Vincere De Floret');
            // Add a recipient
            $mail->addAddress($email, $name);
            // Set email format to HTML
            $mail->isHTML(true);
            $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
            $mail->Subject = 'Email verification';
            $mail->Body = '<p>Thank you for creating a Vincere De Floret account. We are excited to have you with us! To complete your registration, please verify your email address using the verification code provided below. Your verification code is: <b style="font-size: 30px;">' . $verification_code . '</b></p><p>If you did not create this account, please ignore this email or contact support.</p>';
            // send function to email
            $mail->send();

            // Insert the user into the database
            $encrypted_password = password_hash($password, PASSWORD_DEFAULT);
            // insert in users table
            $sql = "INSERT INTO users (name, email, password, verification_code, email_verified_at, attempts, contact_number, address, first_name, middle_name, last_name, image_path) VALUES ('$name', '$email', '$encrypted_password', '$verification_code', NULL, 0, '$phone_number', '$address', '$fname', '$mname', '$lname', '$image')";

            // Execute the SQL query only if it's not an admin account
            mysqli_query($conn, $sql);

            // Redirect to the email verification page
            header("Location:http://localhost/vincere-de-floret/php/email-verification.php?email=" . $email);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" type="image/png" href="../assets/logo/logo2.png"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/register.css">
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
          I agree to the
          <a href="#">Terms of Service</a> & <a href="#">Privacy Policy</a>.
        </label>
      </div>

      <input type="submit" name="register" value="REGISTER" />

      <p class="login">Already have an account? <a href="login.php">Login</a></p>
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



