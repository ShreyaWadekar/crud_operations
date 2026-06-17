<?php

$conn = mysqli_connect("localhost", "root", "", "product_management");

if(!$conn)
{
    die("Connection Failed: " . mysqli_connect_error());
}

?>