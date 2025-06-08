<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require "connection.php";

if (isset($_POST["verify_email"])) {
    $email = $_POST["email"];
    $verification_code = $_POST["verification_code"];

    $sql = "SELECT * FROM users WHERE email = '$email' AND verification_code = '$verification_code'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);
        if (isset($_GET['type'])) {
            $sql_update = "UPDATE users SET reset_token = '', reset_token_expiration = '' WHERE email = '$email' AND verification_code = '$verification_code'";
        } else {
            $sql_update = "UPDATE users SET email_verified_at = NOW() WHERE email = '$email' AND verification_code = '$verification_code'";
        }
        
        $result_update = mysqli_query($conn, $sql_update);
        if ($result_update && mysqli_affected_rows($conn) > 0) {
            if (isset($_GET['type'])) {
                header("Location: http://localhost/daily-grind/php/update-password.php?email=$email");
                exit();
            } else {
                echo "<script>alert('Successfully Registered!'); document.location.href = 'login.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Database error. Please try again later.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Incorrect verification code. Please try again.'); window.history.back();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="icon" type="image/png" href="../assets/logo/logo2.png"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/email-verification.css">
</head>
<body>
    <div class="content-container">
        <h3>Verify Your Email</h3>
        <form method="POST">
            <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>" required>
            <input type="text" name="verification_code" placeholder="Enter verification code" required />
            <input type="submit" name="verify_email" value="Verify Email">
        </form>
        <p class="try-again">
            Didn't receive an email?
            <a href="resend-code.php?email=<?php echo urlencode($_GET['email']); ?>&type=<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>">Try Again</a>
        </p>
    </div>
</body>
</html>