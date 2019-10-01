<?php
    // validate inputs!!!!!!!!!!!!!!!

    session_name("Mok_Project1");
    session_start();

    require_once('./utilities.php');
    resuableHeader(); // header


    if(!empty($_SESSION['userLoggedIn'])){
        header('Location: events.php');
        exit;
    }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="assets/css/styles.css">
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    </head>
    <body>
        <?php 
            // When user submits form - verify user logged in correclty
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                // do db stuff to get password
                // hash sha256 provided password and compare with that hashed in the db
                // Set session variable!!



            }
        ?>

        <!-- Login form -->
        <div id="login-form-container">
            <form id="login-form" name="loginForm" action="./login.php" method="POST">
                <label class="loginFormItem">Name:</label>
                <input type="text" name="name" class="loginFormItem" /><br/>
                <label class="loginFormItem">Password:</label>
                <input type="text" name="password" class="loginFormItem" />
                <input name="submit" type="submit" value="submit" />
            </form>
        </div>
    </body>
</html>