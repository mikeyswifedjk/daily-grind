<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost:3306", "root", "", "daily_grind");

if (!$conn) {
    echo "Connection failed: " . mysqli_connect_error() . "\n";
    exit(1);
}
?>
