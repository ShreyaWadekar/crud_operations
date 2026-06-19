<?php
session_start();
include 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';


$allowedSort = ['name', 'price'];

$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : '';
$dir  = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'desc' : 'asc';

if ($sort === 'name') {
    $orderBy = "product_name " . $dir;
} elseif ($sort === 'price') {
    $orderBy = "price " . $dir;
} else {
    $orderBy = "product_id DESC";
}

$nameNextDir  = ($sort === 'name'  && $dir === 'asc') ? 'desc' : 'asc';
$priceNextDir = ($sort === 'price' && $dir === 'asc') ? 'desc' : 'asc';


function sortArrow($column, $currentSort, $currentDir) {
    if ($currentSort !== $column) {
        return "";
    }
    return $currentDir === 'asc' ? "&#9650;" : "&#9660;"; 
}

$nameArrow  = sortArrow('name', $sort, $dir);
$priceArrow = sortArrow('price', $sort, $dir);


$perPage = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;


$whereSql = "WHERE user_id = $user_id";

if ($search !== '') {
    $searchEscaped = mysqli_real_escape_string($conn, $search);

    $whereSql .= " and ( product_name LIKE '%$searchEscaped%' OR CAST(price AS CHAR) LIKE '%$searchEscaped%'
                   OR CAST(quantity AS CHAR) LIKE '%$searchEscaped%')";
}

$countResult = mysqli_query($conn, "select COUNT(*) as total from products $whereSql");
$totalProducts = mysqli_fetch_assoc($countResult)['total'];
$totalPages = max(1, ceil($totalProducts / $perPage));

if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

$result = mysqli_query($conn, "select * from products $whereSql order by $orderBy LIMIT $perPage OFFSET $offset");


function buildLink($params) {
    return "?" . http_build_query($params);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Products</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container" style="width:95%; max-width:1100px;">

    <h2>My Products</h2>

    <div class="current-user-box">
        Logged in as:
        <strong><?php echo $_SESSION['user_name']; ?></strong>
        &nbsp;|&nbsp;
        Your User ID: <span class="user-badge"><?php echo $_SESSION['user_id']; ?></span>
    </div>

    <form method="GET" action="" class="products-toolbar">

        <div class="toolbar-left">
            <a class="btn" href="add_product.php">+ Add Product</a>
            <a class="btn" href="dashboard.php">Dashboard</a>
        </div>

        <div class="toolbar-right">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($dir); ?>">

            <input
                type="text"
                name="search"
                id="search"
                class="search-input"
                placeholder="Search name, price, quantity..."
                value="<?php echo htmlspecialchars($search); ?>">

            <button type="button" id="searchBtn"  class="btn-search">Search</button>

            <?php if ($search !== '') { ?>
                <a class="btn-clear" href="<?php echo buildLink(['sort' => $sort, 'dir' => $dir]); ?>">Clear</a>
            <?php } ?>
        </div>

    </form>

    <?php if ($totalProducts == 0) { ?>

        <p>
            <?php if ($search !== '') { ?>
                No products found matching "<?php echo htmlspecialchars($search); ?>".
            <?php } else { ?>
                No products found.
            <?php } ?>
        </p>

    <?php } else { ?>

    <table>
        <tr>
            <th>Image</th>
            <th>
                <a class="sortable-link"
                   href="<?php echo buildLink(['sort' => 'name', 'dir' => $nameNextDir, 'page' => $page, 'search' => $search]); ?>">
                    Name <span class="sort-arrow"><?php echo $nameArrow; ?></span>
                </a>
            </th>
            <th>
                <a class="sortable-link"
                   href="<?php echo buildLink(['sort' => 'price', 'dir' => $priceNextDir, 'page' => $page, 'search' => $search]); ?>">
                    Price <span class="sort-arrow"><?php echo $priceArrow; ?></span>
                </a>
            </th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>

        <tbody id="productTable">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>
            <td>
                <?php if (!empty($row['image'])) { ?>
                   <img src="crud-images/<?php echo $row['image']; ?>"
                        class="table-image previewImg"
                        alt="Product Image">
                <?php } else { ?>
                    <div class="no-image-round">No img</div>
                <?php } ?>
            </td>
            <td><?php echo $row['product_name']; ?></td>
            <td>₹<?php echo number_format($row['price'], 2); ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td>
                <form method="POST"  action="edit_product.php" style="display:inline;">

    <input type="hidden" name="id" value="<?php echo $row['product_id']; ?>">
    <button type="submit" class="btn-edit">Edit</button>

</form>
    <form method="POST"
      action="delete_product.php"
      style="display:inline;"
      onsubmit="return confirm('Delete this product?')">

    <input type="hidden"
           name="id"
           value="<?php echo $row['product_id']; ?>">

    <button type="submit" class="btn-delete">
        Delete
    </button>

</form>
            </td>
        </tr>

        <?php } ?>
</tbody>
    </table>
    
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <a class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>"
               href="<?php echo buildLink(['sort' => $sort, 'dir' => $dir, 'page' => $i, 'search' => $search]); ?>">
                <?php echo $i; ?>
            </a>
        <?php } ?>
    </div>

    <p style="text-align:center;">
        Total Products: <strong><?php echo $totalProducts; ?></strong>
        &nbsp;|&nbsp;
        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
    </p>

    <?php } ?>

    </div>
    <div id="imageViewer" class="image-viewer">
        <span class="close-viewer">&times;</span>
        <img id="viewerImage">
    </div>
<script>

const viewer = document.getElementById("imageViewer");
const viewerImage = document.getElementById("viewerImage");

document.querySelectorAll(".previewImg").forEach(img => {

    img.addEventListener("click", function() {

        viewer.style.display = "block";
        viewerImage.src = this.src;

    });

});

document.querySelector(".close-viewer").onclick = function() {
    viewer.style.display = "none";
};

viewer.onclick = function(e){
    if(e.target === viewer){
        viewer.style.display = "none";
    }
};

</script>
<script>

document.getElementById("searchBtn")
.addEventListener("click", function(){

    let search =
    document.getElementById("search").value;

    let xhr =
    new XMLHttpRequest();

    xhr.open(
        "POST",
        "search_products.php",
        true
    );

    xhr.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
    );

    xhr.onload = function() {

        document.getElementById("productTable")
        .innerHTML = this.responseText;

    };

  xhr.send(
    "search=" +
    encodeURIComponent(search) +
    "&view=table"
);

});

</script>
</body>
</html>