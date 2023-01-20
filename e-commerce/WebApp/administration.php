<?php
session_start();

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"]!="Admin")
        header("Location: invalid_permission.php");
}
else
    header("Location: index.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <script src="/js/users.js" defer></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" defer></script>
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
    <script id="pages_computed" src="js/dynamic_pagination.js" defer></script>
    <title>Administration Page</title>
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
    <div id="search-bar">
        <img src="assets/search.png" id="search_img" alt="">
        <input type="search" id="search_input" data-search>
        <label for="search_input"></label>
    </div>
</header>
    <div id="pagination_container">
        <div id="pagination"></div>
    </div>
    <!-- Basic Structure used for representing user information-->
    <div class="user-cards" data-user-cards-container></div>
        <!-- Dynamically generated content based on how many users are returned from backend-->
        <template data-user-template>

            <!-- Entire user-card scope -->
            <div class="card">

                <!-- User Identification Div - Immutable information -->
                <div class="userid">
                    <div class="email" data-email> </div>
                    <div class="id" data-id></div>
                </div>

                <!-- Invisible Edit Form - Change Full Name (Separate Fields), Username, Role Info -->
                <form class="edit_form" style="display: none;" method="post" action="update_user_info.php">
                    <input type="hidden" name="hidden-id" id="hidden-id">
                    <div class="fullname_edit">
                        <div class="fname_edit">
                            <label for="input_fname"></label>
                            <input type="text" size="10" name="input_fname" id="input_fname">
                        </div>

                        <div class="lname_edit">
                            <label for="input_lname"></label>
                            <input type="text" size="10" name="input_lname" id="input_lname">
                        </div>
                    </div>

                    <div class="username_edit">
                        <label for="input_username"></label>
                        <input type="text" size="10" name="input_username" id="input_username">
                    </div>

                    <div class="role_edit">
                        <label for="input_role"></label>
                        <select name="input_role" id="input_role">
                            <option value="Admin" id="admin">Administrator</option>
                            <option value="Merchant" id="merch">Merchant</option>
                            <option value="User" id="plain">User</option>
                        </select>
                    </div>

                    <div class="confirmed_edit">
                        <input type="radio" id="opt1" name="status" value="1" />
                        <label for="opt1">Confirmed</label>

                        <input type="radio" id="opt2" name="status" value="0" />
                        <label for="opt2">Unconfirmed</label>
                    </div>

                </form>

                <div class="fullname">
                    <div class="fname" data-fname></div>
                    <div class="lname" data-lname></div>
                </div>

                <div class="username" data-username></div>

                <div class="role" data-role></div>

                <div class="confirmed" data-confirmed></div>

                <div class="button_type">
                    <img src="assets/delete.png" class="delete" alt="" onclick="deleteForm()">
                    <img src="assets/edit.png" class="edit" alt="" onclick="editForm()">
                    <img src="assets/save.png" class="save" alt="" onclick="save_form()">
                </div>
            </div>
    </template>
</body>
</html>
