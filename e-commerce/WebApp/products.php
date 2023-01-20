<?php
session_start();

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $token = $_SESSION['access_token'];
    echo "<script type=\"text/javascript\"> let user_id='$user_id';</script>";
    echo "<script type=\"text/javascript\"> let access_token='$token';</script>";
}
else
    header("Location: index.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <link rel="stylesheet" href="styles.css">
    <script id="pages_computed" src="js/dynamic_pagination.js" defer></script>
    <script src="js/product.js" defer></script>
    <title>Homepage</title>
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
    <a href="cart.php"><img src="assets/cart.png" alt=""></a>
    <div id="search-bar-products">
        <div id="search-by-container">
            <label for="search-by">By</label>
            <select name="by" id="search-by">
                <option value="name">Product Name</option>
                <option value="seller">Seller Username</option>
                <option value="category">Category</option>
                <option value="date">Withdrawal Date</option>
                <option value="price">Price Range</option>
            </select>
        </div>
        <div id="search-tag">
            <label for="product_search_parameter"></label>
            <input type="search" id="product_search_parameter" data-search>
        </div>
        <div class="range-date">
            <label for="start_date">From:</label>
            <input type="date" id="start_date">
            <label for="end_date">To:</label>
            <input type="date" id="end_date">
        </div>
        <div class="range-price">
            <label for="priceRange"></label>
            <input type="text" id="priceRange" readonly>
            <div id="price-range" class="slider"></div>
        </div>
        <img src="assets/search.png" alt="" id="do_search">
</header>
    <div id="pagination_container">
        <div id="pagination"></div>
    </div>
    <div class="product-view">
        <div class="product_grid" data-product-grid>
            <template data-product-card-template>
                <div class="product_card">
                    <div class="image_view">
                        <img src="" alt="">
                    </div>
                    <div class="details-view">
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
                    <div class="purchase_field">
                        <img src="assets/add_to_cart.png" alt="" class="add_to_cart" id="">
                        <div class="price_wrapper">
                            <p class="price_tag" data-product-price></p>
                            <label class="price_tag" for="price_tag" id="euro">€</label>
                        </div>
                        <div class="withdrawal_wrapper">
                            <label class="product_withdrawal_date" for="product_withdrawal_date" id="withdrawn">Sold Out On: </label>
                            <p class="product_withdrawal_date" data-product-withdrawal></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</body>
</html>
