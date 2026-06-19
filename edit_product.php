<?php
session_start();
include 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['id']) && !isset($_POST['update'])) {
    die("Invalid Request");
}

$id = $_POST['id'] ?? '';
$result = mysqli_query($conn, "select * from products where product_id='$id' and user_id='$user_id'");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Access Denied");
}

if (isset($_POST['update'])) {

    $id       = $_POST['id'];
    $name     = $_POST['product_name'];
    $price    = $_POST['price'];
    $quantity = $_POST['quantity'];
    $image = $product['image'];

    if (!empty($_FILES['image']['name'])) {

        $image = time() . "_" . $_FILES['image']['name'];
        $uploadPath =
        "C:/xampp/htdocs/product_management/crud-images/" . $image;

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadPath
        );
    }

    mysqli_query($conn, "update products set product_name='$name', price='$price', quantity='$quantity', image='$image'
         where product_id='$id' and user_id='$user_id'");

    header("Location: products.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="container">

    <h2>Edit Product</h2>

    <form method="POST"
          enctype="multipart/form-data"
          id="editForm">

        <input type="hidden"
               name="id"
               value="<?php echo $product['product_id']; ?>">

        <input type="text"
               name="product_name"
               id="product_name"
               value="<?php echo $product['product_name']; ?>">

        <span class="error-msg" id="nameErr"></span>

        <input type="number"
               name="price"
               id="price"
               step="0.01"
               value="<?php echo $product['price']; ?>">

        <span class="error-msg" id="priceErr"></span>

        <input type="number"
               name="quantity"
               id="quantity"
               value="<?php echo $product['quantity']; ?>">

        <span class="error-msg" id="qtyErr"></span>

        <br>

        <?php if (!empty($product['image'])) { ?>

            <img
                src="crud-images/<?php echo $product['image']; ?>"
                class="preview-image"
                alt="Product Image">

        <?php } ?>

        <input type="file"
               name="image"
               id="image">

        <span class="error-msg" id="imageErr"></span>


        <button type="submit"
                class="add"
                name="update">
            Update Product
        </button>
    </form>
    <a href="products.php">Cancel</a>
</div>

<script>

document.getElementById('editForm')
.addEventListener('submit', function(e) {

    let valid = true;

    const name =
    document.getElementById('product_name').value.trim();

    const price =
    document.getElementById('price').value;

    const qty =
    document.getElementById('quantity').value;

    document.getElementById('nameErr').textContent = '';
    document.getElementById('priceErr').textContent = '';
    document.getElementById('qtyErr').textContent = '';

    if (name === '') {
        document.getElementById('nameErr').textContent =
        'Product name is required.';
        valid = false;
    }

    if (price === '' || price <= 0) {
        document.getElementById('priceErr').textContent =
        'Enter a valid price.';
        valid = false;
    }

    if (qty === '' || qty < 0) {
        document.getElementById('qtyErr').textContent =
        'Enter a valid quantity.';
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
    }

});
</script>

</body>
</html>