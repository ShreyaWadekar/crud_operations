<?php
session_start();
include 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_POST['id'];

mysqli_query($conn, "delete from products where product_id='$id'");

header("Location: products.php");
exit();
?>