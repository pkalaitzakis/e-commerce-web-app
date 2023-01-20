<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" defer></script>
    <link rel="stylesheet" href="styles.css">
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
    <p style="z-index: -1; display: flex; position: absolute; right: 30px; padding-left: 75%; font : italic bold 20px Calbiri; color: #E75A5F;">
        <?php echo $_SESSION['username']?> (<?php echo $_SESSION['role']?>)
    </p>
</header>

<div style="text-align: center; font : italic bold 75px Calbiri; color: #E75A5F;"> Welcome </div>

<div style="background-color: #FAFADC; display: flex; padding: 20px; border-radius: 2em; margin: auto; width: 800px;">
    <p> Dear reader, <br> <br> The domain you're currently browsing is a simulation of a simple
        e-commerce website designed specifically for COMP-513 semester project at the Technical University of Crete Electrical and Computer Engineering department.
        <br> Please keep in mind that any product or user information you might come across was auto-generated with the help of <a href="https://mockaroo.com/">Mockaroo.com</a>
        and free data-sets found online which associate random product images to fictional names.
        <br> <br> There are three roles in this hypothetical e-commerce store:<br><br>
        - Users: Able to browse through the product listings and add the ones they like to their cart ,
        provided of course the aforementioned item is currently available.
        <br>
        <br>
        - Merchants: Able to manage their existing product listings by either deleting an item,
        or updating the modifiable info fields of a product. Additionally, merchants are also able to
        create new item listings and add them to their current selection.
        <br>
        <br>
        - Administators: Able to alter every user's modifiable information, such as one's first or last
        name, username, role or current status (enabled or not).
    </p>
</div>

</body>
</html>