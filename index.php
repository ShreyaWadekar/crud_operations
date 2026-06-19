<?php
include 'db/connection.php';

$countResult = mysqli_query(
    $conn, "select COUNT(*) as total from products"
);

$totalProducts = mysqli_fetch_assoc($countResult)['total'];

$search = isset($_GET['search'])
          ? mysqli_real_escape_string($conn, $_GET['search'])
          : '';

$sql = "select products.*, users.name from products join users on products.user_id = users.id";

if($search != '')
{
    $sql .= " where product_name like '%$search%' or users.name like '%$search%'";
}

$sql .= " order by product_id DESC";

$result = mysqli_query($conn, $sql);

 ?>           

<!DOCTYPE html>
<html>
<head>
    <title>All Products</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<div class="full-container">

    <h2>All Products</h2>
<form class="index-toolbar">

    <input type="text"
           id="search"
           class="search-input"
           placeholder="Search Product or Owner">

    <button type="button"
            id="searchBtn"
            class="btn-search">
        Search
    </button>

</form>
  <div class="products-container" id="productsContainer">

<?php while($row = mysqli_fetch_assoc($result)) { ?>

    <div class="product-card">

        <?php if(!empty($row['image'])) { ?>

            <img src="crud-images/<?php echo $row['image']; ?>"
                 class="product-image"
                 alt="Product Image">

        <?php } else { ?>

            <div class="no-image-box">
                No Image
            </div>

        <?php } ?>

        <div class="card-body">

            <h3><?php echo $row['product_name']; ?></h3>

            <p>
                <strong>Price:</strong>
                ₹<?php echo number_format($row['price'], 2); ?>
            </p>

            <p>
                <strong>Quantity:</strong>
                <?php echo $row['quantity']; ?>
            </p>

            <p>
                <strong>Owner:</strong>
                <?php echo $row['name']; ?>
            </p>

        </div>
    </div>

<?php } ?>
</div>
<p class="total-products">
    Total Products:
    <strong><?php echo $totalProducts; ?></strong>
</p>
</div>
<script>

document.getElementById("searchBtn")
.addEventListener("click", function(){

    let search =
    document.getElementById("search").value;

    let formData = new FormData();

    formData.append(
        "search",
        search
    );
    formData.append(
    "view",
    "card"
);
    fetch(
        "search_products.php",
        {
            method: "POST",
            body: formData
        }
    )
    .then(response => response.text())
    .then(data => {

        document.getElementById(
            "productsContainer"
        ).innerHTML = data;

    });
});

</script>
</body>
</html>
