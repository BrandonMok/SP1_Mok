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
<html>
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
        <form name="loginForm" action="./login.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name">
            <label for="password">Password:</label>
            <input type="text" name="password">
            <input name="submit"  type="submit" value="submit" />
        </form>
    </body>
</html>