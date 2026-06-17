<?php
session_start();
include 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['add'])) {
    $user_id      = $_SESSION['user_id'];
    $product_name = $_POST['product_name'];
    $price        = $_POST['price'];
    $quantity     = $_POST['quantity'];

    $image = "";

    if (!empty($_FILES['image']['name'])) {

        $image = time() . "_" . $_FILES['image']['name'];

        $uploadPath = "C:/xampp/htdocs/product_management/crud-images/" . $image;

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
    }

    $sql = "insert into products (user_id, product_name, price, quantity, image)
            values ('$user_id', '$product_name', '$price', '$quantity', '$image')";

    if (mysqli_query($conn, $sql)) {
        $message = "success";
    } else {
        $message = "error";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">

    <h2>Add Product</h2>

    <!-- Show which user is adding this product -->
    <div style="
        background: #e8f4fd;
        border-left: 4px solid #007bff;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        font-size: 14px;
    ">
        Adding as: <strong><?php echo $_SESSION['user_name']; ?></strong>
        &nbsp;|&nbsp;
        User ID: <strong style="color:#007bff;">#<?php echo $_SESSION['user_id']; ?></strong>
        <br>
        <small style="color:#888;">This User ID will be saved with your product automatically.</small>
    </div>

    <?php if ($message === "success"): ?>
        <p class="success">
            ✓ Product added successfully under User ID
            #<?php echo $_SESSION['user_id']; ?>!
        </p>
    <?php elseif ($message === "error"): ?>
        <p class="error">Failed to add product. Try again.</p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="addForm">

        <input type="text" name="product_name"
               id="product_name" placeholder="Enter Product Name">
        <span class="error-msg" id="nameErr"></span>

        <input type="number" name="price" step="0.01"
               id="price" placeholder="Enter Price (₹)">
        <span class="error-msg" id="priceErr"></span>

        <input type="number" name="quantity"
               id="quantity" placeholder="Enter Quantity">
        <span class="error-msg" id="qtyErr"></span>

        <input type="file" name="image" id="image">
        <span class="error-msg" id="imageErr"></span>

        <button type="submit" name="add">Add Product</button>

    </form>

    <br>
    <a href="products.php">View My Products</a> |
    <a href="dashboard.php">Dashboard</a>

</div>

<script>
document.getElementById('addForm').addEventListener('submit', function(e) {
    let valid = true;

    const name  = document.getElementById('product_name').value.trim();
    const price = document.getElementById('price').value;
    const qty   = document.getElementById('quantity').value;

    document.getElementById('nameErr').textContent  = '';
    document.getElementById('priceErr').textContent = '';
    document.getElementById('qtyErr').textContent   = '';

    if (name === '') {
        document.getElementById('nameErr').textContent = 'Product name is required.';
        valid = false;
    }
    if (price === '' || Number(price) <= 0) {
        document.getElementById('priceErr').textContent = 'Enter a valid price greater than 0.';
        valid = false;
    }
    if (qty === '' || Number(qty) < 0) {
        document.getElementById('qtyErr').textContent = 'Enter a valid quantity.';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>