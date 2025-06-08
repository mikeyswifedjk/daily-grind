<?php
    //retrieving a value from the query string of a URL and storing it in a variable named $email.
    $email = $_GET['email'];
    //user is logged in
    if (isset($_POST['updatepass']))
    {
    //start changing password
    //check fields
    $newpassword = md5($_POST['newpassword']);
    $confirmpassword = md5($_POST['confirmpassword']);
    $conn = mysqli_connect("localhost:3306", "root", "", "vincere_de_floret");
    $sql ="SELECT password FROM users WHERE email='.$email'";
    $result = mysqli_query($conn, $sql);
    //check two new passwords
    if($newpassword==$confirmpassword){
    //successs
    //change password in db
    $querychange = "UPDATE users SET password='" .password_hash($_POST['newpassword'], PASSWORD_DEFAULT)."' WHERE email='" .$email."'";
    $change_result = mysqli_query($conn, $querychange);
    echo "<script>alert('Your password has been changed'); window.location.href = 'login.php';</script>";
    }
    else{
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
    <title>Vincere De Floret</title>
    <link rel="icon" type="image/png" href="../assets/logo/logo2.png"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/updatepassword.css">
</head>
<body>
    <div class="content">
    <img src="../assets/logo/logo1.png" alt="Forgot Password" class="logo">
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
