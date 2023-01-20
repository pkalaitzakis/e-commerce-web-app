<?php

session_start();

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"]!="Merchant"){
        header("Location: invalid_permission.php");
    }
    $seller_id=$_SESSION['user_id'];
    $username=$_SESSION["username"];
    $token = $_SESSION["access_token"];
    echo "<script type=\"text/javascript\"> username= '$username';</script>";
    echo "<script type=\"text/javascript\"> sellerid= '$seller_id';</script>";
    echo "<script type=\"text/javascript\"> let access_token='$token';</script>";
}
else
    header("Location: index.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" defer></script>
    <link rel="stylesheet" href="styles.css">
    <script src="js/sellers.js" defer></script>
    <title>Merchants</title>
</head>
<body>
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
<div class="seller-view">
    <div class="backwards-button">
        <img src="assets/backwards_arrow.png" alt="backwards">
    </div>
    <div class="seller_grid" data-product-grid>
        <template data-product-card-template>
            <div class="product_card">
                <div class="image_view">
                    <img src="" alt="image">
                </div>
                <div class="details-view">
                    <form class="product_edit_form">
                        <label for="edit_name">Product Name</label>
                        <input type="text" name="input_product_name" id="edit_name">
                        <label for="edit_category">Product Category</label>
                        <input type="text" name="input_product_name" id="edit_category">
                        <label for="edit_price">Product Price</label>
                        <input type="number" name="input_product_name" id="edit_price" min="0" step="0.01">
                    </form>
                    <div class="info">
                        <div class="product_name" data-product-name></div>
                        <div class="info_wrapper">
                            <label class="product_code" for="product_category">Category-></label>
                            <div class="product_category" data-product-category></div>
                        </div>
                        <div class="info_wrapper">
                            <label class="product_code" for="product_code">Item ID #</label>
                            <div class="product_code" data-product-code></div>
                        </div>
                        <div class="info_wrapper">
                            <label class="product_code" for="product_merchant">Sold by:</label>
                            <div class="product_merchant" data-product-merchant></div>
                        </div>
                    </div>
                </div>
                <div class="purchase_field">
                    <div class="seller_card_buttons_container">
                        <img src="assets/edit.png" alt="cart" class="edit_product">
                        <img src="assets/save.png" alt="cart" class="save_product" style="display: none;">
                        <img src="assets/delete.png" alt="cart" class="delete_product">
                    </div>
                    <div class="price_wrapper">
                        <p class="price_tag" data-product-price></p>
                        <label class="price_tag" for="price_tag">€</label>
                    </div>
                    <p class="product_withdrawal_date" data-product-withdrawal></p>
                </div>
            </div>
        </template>
    </div>
    <div class="forwards-button">
        <img src="assets/forwards_arrow.png" alt="backwards">
    </div>
</div>

<div class="add_product_container">
    <div class="invoke_add">
        <img onclick="showForm()" src="assets/add_new_product.png">
    </div>
    <div id="new_product_card">
        <div class="image_view">
            <input type="file" id="image_file" accept=".png, .jpg, .jpeg" onchange="loadProductImage()">
            <img src="assets/placeholder.png" alt="image" id="product_img">
        </div>
        <div class="details-view">
            <div class="product_name">
                <label for="input_product_name">Product Name</label>
                <input type="text" name="input_product_name" id="input_product_name">
            </div>
            <div class="product_category">
                <label for="input_product_category">Product Category</label>
                <input type="text" name="input_product_name" id="input_product_category">
            </div>
            <div class="product_code">
                <label for="input_product_code">Product Code</label>
                <input type="text" name="input_product_name" id="input_product_code">
            </div>
        </div>
        <div class="purchase_field_new">
            <img src="assets/save.png" alt="save" id="save_product" class="add_to_cart_image" onclick="addProduct()">
            <div id="price_field">
                <input type="text" name="input_product_name" id="input_product_price">
                <label for="input_product_price">€</label>
            </div>
        </div>
    </div>
</div>
</body>
</html>
