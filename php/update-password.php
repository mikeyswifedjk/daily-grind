<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require "connection.php";

    $email = $_GET['email'];
    if (isset($_POST['updatepass'])){
        $newpassword = md5($_POST['newpassword']);
        $confirmpassword = md5($_POST['confirmpassword']);

        $sql ="SELECT password FROM users WHERE email='.$email'";
        $result = mysqli_query($conn, $sql);

        if($newpassword==$confirmpassword){
            $querychange = "UPDATE users SET password='" .password_hash($_POST['newpassword'], PASSWORD_DEFAULT)."' WHERE email='" .$email."'";
            $change_result = mysqli_query($conn, $querychange);
            echo "<script>alert('Your password has been changed'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('New password doesn\'t match!');</script>";
        }  
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="icon" type="image/png" href="../assets/logo/logo2.png"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/updatepassword.css">
</head>
<body>
    <div class="content">
        <h3>Update Password</h3>
        <form method="POST">
            <label for="newpassword">New Password</label>
            <input type="password" id="newpassword" name="newpassword" placeholder="************" required />
            
            <label for="confirmpassword">Confirm Password</label>
            <input type="password" id="confirmpassword" name="confirmpassword" placeholder="************" required />

            <input type="submit" name="updatepass" value="Update Password">
        </form>
    </div>    
</body>
</html>
