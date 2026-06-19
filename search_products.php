<?php
include 'db/connection.php';

$search = mysqli_real_escape_string(
    $conn,
    $_POST['search']
);

$view = $_POST['view'];
$sql = "select products.*, users.name from products join users on products.user_id = users.id
        where product_name like '%$search%'
        or users.name like '%$search%'
        order by product_id DESC";

$result = mysqli_query($conn, $sql);

if($view == "table")
{
    while ($row = mysqli_fetch_assoc($result))
    {
?>
<tr>

    <td>
        <?php if (!empty($row['image'])) { ?>
            <img src="crud-images/<?php echo $row['image']; ?>"
                 class="table-image previewImg">
        <?php } else { ?>
            <div class="no-image-round">No img</div>
        <?php } ?>
    </td>

    <td><?php echo $row['product_name']; ?></td>

    <td>₹<?php echo number_format($row['price'], 2); ?></td>

    <td><?php echo $row['quantity']; ?></td>

    <td>

        <form method="POST"
              action="edit_product.php"
              style="display:inline;">

            <input type="hidden"
                   name="id"
                   value="<?php echo $row['product_id']; ?>">

            <button type="submit" class="btn-edit">
                Edit
            </button>

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

<?php
    }

    if(mysqli_num_rows($result) == 0)
    {
?>
<tr>
    <td colspan="5">No products found.</td>
</tr>
<?php
    }
}

if($view == "card")
{
    if(mysqli_num_rows($result) > 0)
    {
        while($row = mysqli_fetch_assoc($result))
        {
?>

<div class="product-card">

    <?php if(!empty($row['image'])) { ?>

        <img src="crud-images/<?php echo $row['image']; ?>"
             class="product-image">

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

<?php
        }
    }
    else
    {
        echo "<h3>No products found.</h3>";
    }
}