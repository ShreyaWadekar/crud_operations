<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Dashboard</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="dashboard">

<h2>
Welcome <?php echo $_SESSION['user_name']; ?>
</h2>

<a class="btn"
href="add_product.php">
Add Product
</a>

<a class="btn"
href="products.php">
View Products
</a>

<a class="btn"
href="logout.php">
Logout
</a>

</div>

</body>
</html>