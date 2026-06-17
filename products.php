<?php
session_start();
include 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------------- SEARCH ---------------- */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

/* ---------------- SORTING ---------------- */
$allowedSort = ['name', 'price'];

$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : '';
$dir  = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'desc' : 'asc';

if ($sort === 'name') {
    $orderBy = "product_name " . $dir;
} elseif ($sort === 'price') {
    $orderBy = "price " . $dir;
} else {
    // Default: latest record first
    $orderBy = "product_id DESC";
}

// Next direction to use when the arrow is clicked again (toggle)
$nameNextDir  = ($sort === 'name'  && $dir === 'asc') ? 'desc' : 'asc';
$priceNextDir = ($sort === 'price' && $dir === 'asc') ? 'desc' : 'asc';

// Show only ONE arrow, for whichever column is currently active
function sortArrow($column, $currentSort, $currentDir) {
    if ($currentSort !== $column) {
        return "";
    }
    return $currentDir === 'asc' ? "&#9650;" : "&#9660;"; // ▲ or ▼
}

$nameArrow  = sortArrow('name', $sort, $dir);
$priceArrow = sortArrow('price', $sort, $dir);

/* ---------------- PAGINATION ---------------- */
$perPage = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

/* ---------------- BUILD WHERE CLAUSE (user + search) ---------------- */
// user_id is always an int from the session, so it's safe to use directly.
// The search term comes from the user, so it MUST be escaped before use.
$whereSql = "WHERE user_id = $user_id";

if ($search !== '') {
    $searchEscaped = mysqli_real_escape_string($conn, $search);

    // Search across product_name, price, and quantity at once.
    // CAST(... AS CHAR) lets numeric columns (price, quantity) be matched
    // against partial text typed by the user (e.g. typing "45" matches 45.00).
    $whereSql .= " AND (
        product_name LIKE '%$searchEscaped%'
        OR CAST(price AS CHAR) LIKE '%$searchEscaped%'
        OR CAST(quantity AS CHAR) LIKE '%$searchEscaped%'
    )";
}

$countResult = mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM products $whereSql"
);
$totalProducts = mysqli_fetch_assoc($countResult)['total'];
$totalPages = max(1, ceil($totalProducts / $perPage));

if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

$result = mysqli_query($conn,
    "SELECT * FROM products
     $whereSql
     ORDER BY $orderBy
     LIMIT $perPage OFFSET $offset"
);

// Helper to build a link that keeps page/sort/dir/search params consistent
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

    <!-- Show logged in user -->
    <div class="current-user-box">
        Logged in as:
        <strong><?php echo $_SESSION['user_name']; ?></strong>
        &nbsp;|&nbsp;
        Your User ID: <span class="user-badge">#<?php echo $_SESSION['user_id']; ?></span>
    </div>

    <a class="btn" href="add_product.php">+ Add Product</a>
    <a class="btn" href="dashboard.php">Dashboard</a>

    <!-- Search bar: top right of the table -->
    <div class="table-toolbar">
        <form method="GET" action="" class="search-form">
            <!-- Preserve current sort when a new search is run -->
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($dir); ?>">

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search name, price, quantity..."
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit" class="btn-search">Search</button>

            <?php if ($search !== '') { ?>
                <a class="btn-clear" href="<?php echo buildLink(['sort' => $sort, 'dir' => $dir]); ?>">Clear</a>
            <?php } ?>
        </form>
    </div>

    <br>

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

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>
            <td>
                <?php if (!empty($row['image'])) { ?>
                    <img src="crud-images/<?php echo $row['image']; ?>"
                         class="table-image"
                         alt="Product Image">
                <?php } else { ?>
                    <div class="no-image-round">No img</div>
                <?php } ?>
            </td>
            <td><?php echo $row['product_name']; ?></td>
            <td>₹<?php echo number_format($row['price'], 2); ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td>
                <a class="btn-edit" href="edit_product.php?id=<?php echo $row['product_id']; ?>">Edit</a>
                <a class="btn-delete"
                   href="delete_product.php?id=<?php echo $row['product_id']; ?>"
                   onclick="return confirm('Delete this product?')">Delete</a>
            </td>
        </tr>

        <?php } ?>

    </table>

    <!-- Pagination -->
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

</body>
</html>