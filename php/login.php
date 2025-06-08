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

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_name'])) {
    header("Location: http://localhost/daily-grind/php/customer-dashboard.php?user=" . $_SESSION['user_name']);
    exit;
}

if (isset($_POST["login"])) {
    $identifier = $_POST["identifier"];
    $password = $_POST["password"];

    // Admin check
    $stmt_admin = mysqli_prepare($conn, "SELECT * FROM admin WHERE email = ? OR username = ?");
    mysqli_stmt_bind_param($stmt_admin, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt_admin);
    $result_admin = mysqli_stmt_get_result($stmt_admin);

    // User check
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? OR name = ?");
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Admin login flow
    if (mysqli_num_rows($result_admin) > 0) {
        $admin = mysqli_fetch_assoc($result_admin);

        if (!password_verify($password, $admin['password'])) {
            echo "<script>alert('Password is not correct for admin.'); window.history.back();</script>";
            exit;
        }

        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_fullname'] = $admin['fullname'];
        $_SESSION['admin_email'] = $admin['email'];

        header("Location: http://localhost/daily-grind/php/admin-dashboard.php");
        exit;

    } elseif (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($user['blocked'] == 1) {
            echo "<script>alert('Your account is blocked. Contact the admin for assistance.'); window.history.back();</script>";
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $email = $user['email'];
            recordLoginAttempt($conn, $email);
            $loginAttempts = getLoginAttempts($conn, $email);
            $maxAttempts = 3;

            if ($loginAttempts >= $maxAttempts) {
                $blockSql = "UPDATE users SET blocked = 1 WHERE email = '$email'";
                mysqli_query($conn, $blockSql);

                echo "<script>alert('Your account is blocked. Contact the admin for assistance.'); window.history.back();</script>";
                exit;
            }

            echo "<script>alert('Password is not correct.'); window.history.back();</script>";
            exit;
        }

        // ✅ Successful login: Reset attempts
        resetLoginAttempts($conn, $user['email']);

        $_SESSION['user_type'] = 'customer';
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        header("Location: http://localhost/daily-grind/php/customer-dashboard.php");
        exit;

    } else {
        echo "<script>alert('Email or username not found.'); window.history.back();</script>";
        exit;
    }
}

// Record a failed login attempt
function recordLoginAttempt($conn, $email){
    $email = mysqli_real_escape_string($conn, $email);
    $checkSql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkSql);

    if ($result && mysqli_num_rows($result) > 0) {
        $updateSql = "UPDATE users SET attempts = attempts + 1 WHERE email = '$email'";
        mysqli_query($conn, $updateSql);
    }
}

// Get number of attempts
function getLoginAttempts($conn, $email){
    $email = mysqli_real_escape_string($conn, $email);
    $sql = "SELECT attempts FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return (int)$row['attempts'];
    }
    return 0;
}

// ✅ Reset attempts on successful login
function resetLoginAttempts($conn, $email){
    $email = mysqli_real_escape_string($conn, $email);
    $sql = "UPDATE users SET attempts = 0 WHERE email = '$email'";
    mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincere De Floret</title>
    <link rel="icon" type="image/png" href="../assets/logo/logo2.png"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500&family=Poppins:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <div class="container">
        <h3>Login</h3>
        <form method="POST">
            <input type="text" name="identifier" placeholder="Email or Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <p class="forget"><a href="forgot-password.php">Forgot Password?</a></p>
            <input type="submit" name="login" value="Login">
            <p class="signup">Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>

</body>
</html>