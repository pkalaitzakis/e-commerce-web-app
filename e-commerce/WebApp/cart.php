<?php

session_start();

if (isset($_SESSION["user_id"])) {
    $user_id=$_SESSION["user_id"];
    $token = $_SESSION["access_token"];
    echo "<script type=\"text/javascript\"> user_id='$user_id' </script>";
    echo "<script type=\"text/javascript\"> let access_token='$token';</script>";
}
else
    header("Location: index.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Merchant's Product Management Page</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" defer></script>
    <script src="js/cart.js" defer></script>
</head>
<body style="background-color:#FDB15D;">
<header onmouseleave="$('.site-nav').fadeOut();">
    <img onmouseover="$('.site-nav').fadeIn();" src="assets/menu.png" alt="menu_logo" class="menu_img">
    <nav class="site-nav" id="menu">
        <ul>
            <li><a href="welcome.php"> Home </a></li>
            <li><a href="products.php"> Browse Products </a></li>
            <li><a href="administration.php">Administration</a></li>
            <li><a href="sellers.php">Merchants</a></li>
            <li><a href="logout.php">Log out</a></li>
        </ul>
    </nav>
</header>
<table id="cart_items">
    <thead>
        <tr>
            <th>Preview</th>
            <th>Item Name</th>
            <th>Date Added</th>
            <th>Price</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr id="empty_cart">
            <td colspan="5" style="text-align: center">Your Cart is Empty!</td>
        </tr>
        <tr id="total_sum">
            <th colspan="3" style="text-align: right">Total:</th>
            <td id="sum">0</td>
            <th>€</th>
        </tr>
    </tbody>
</table>
</body>
</html>
