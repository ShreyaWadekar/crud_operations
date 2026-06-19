<?php
session_start();
include 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['id'])) {
    die("Invalid Request");
}

$id = $_POST['id'];

mysqli_query($conn, "delete from products where product_id='$id' and user_id='$user_id'");

header("Location: products.php");
exit();
?>