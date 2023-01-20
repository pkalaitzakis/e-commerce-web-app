<?php

$is_invalid = false;
$is_pending= false;

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        // initialize a curl session
        $request = curl_init();

        // specify AppLogic's link domain
        curl_setopt($request, CURLOPT_URL, "http://172.18.1.8/keyrock_idm.php");

        // set http verb for current request (POST)
        curl_setopt($request, CURLOPT_POST, true);

        // include user's login credentials to the body of post request
        curl_setopt($request, CURLOPT_POSTFIELDS,
            http_build_query([
                'action' => "login",
                'email' => $_POST['login_username'],
                'password' => $_POST['login_password']
            ])
        );

        // accept response from AppLogic
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        // execute the request, and terminate curl session
        $response = curl_exec($request);
        curl_close($request);
        $response  = json_decode($response);
        if(!isset($response->access_token)) {
            $is_invalid = true;
        } elseif(!isset($response->role)) {
            $is_pending = true;
        } else {
            session_start();
            session_regenerate_id();
            $_SESSION['access_token'] = $response->access_token;
            $_SESSION['user_id'] = $response->user_id;
            $_SESSION['role'] = $response->role;
            $_SESSION['username'] = $response->username;
            header("Location: welcome.php");
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
        <link rel="stylesheet" href="styles.css">
        <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
        <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
        <script src="js/registration.js" defer></script>
        <title>Login</title>
    </head>
    <body style="margin: auto; background-color: #FAFADC; color:#E75A5F;">
        <h1 style="color:#E75A5F">Login</h1>
        <?php if ($is_invalid): ?>
            <em>Invalid login</em>
        <?php endif; ?>
        <?php if ($is_pending): ?>
            <em>Confirmation Pending</em>
        <?php endif; ?>

        <form method ="post" id="login_form">
            <div>
                <label for="login_username">Username</label>
                <input type="text" name="login_username" id="login_username">
            </div>
            <div>
                <label for="login_password">Password</label>
                <input type="password" name="login_password" id="login_password">
            </div>
            <button type="submit">Log in</button>
            <button type="button" id="signup_btn" onclick="$('#cancel_signup_btn').show();
                                                           $(this).hide();
                                                           $('#signup_form').show();
                                                           $('#login_form').hide();
                                                           $('h1')[0].innerHTML='Register';">Register</button>
        </form>

        <form action="http://172.18.1.8/keyrock_idm.php" method="post" id="signup_form" style="display:none";>
            <div>
                <input type="hidden" id="action" name="action" value="register">
            </div>
            <div>
                <label for="name">First Name</label>
                <input type="text" id="name" name="name">
            </div>

            <div>
                <label for="surname">Last Name</label>
                <input type="text" id="surname" name="surname">
            </div>

            <div>
                <label for="email">E-mail Address:</label>
                <input type="email" id="email" name="email">
            </div>

            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username">
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>

            <div>
                <label for="password_confirmation">Re-enter Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation">
            </div>

            <fieldset>
                <legend>Select an account type:</legend>
                <div>
                    <input type="radio" id="opt1" checked="checked" name="role" value="User" />
                    <label for="opt1">User</label>

                    <input type="radio" id="opt2" name="role" value="Merchant" />
                    <label for="opt2">Merchant</label>

                    <input type="radio" id="opt3" name="role" value="Admin" />
                    <label for="opt3">Admin</label>
                </div>
            </fieldset>
            <button type="submit">Submit</button>
            <button type="button" id="cancel_signup_btn" style="display: none" onclick="$('#signup_btn').show();
                                                                          $(this).hide();
                                                                          $('#signup_form').hide();
                                                                          $('#login_form').show();
                                                                          $('h1')[0].innerHTML='Login';">Cancel</button>
        </form>
    </body>
</html>
