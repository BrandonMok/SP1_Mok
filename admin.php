<?php
    session_name("Mok_Project1");
    session_start();

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Admin</title>
        <link rel="stylesheet" href="./assets/css/styles.css">
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    </head>
    <body>
        <?php 
            if(isset($_SERVER['userLoggedIn'])){

            }
            else{
                // REDIRECT - User not logged in
                header('Location: login.php');
                exit;
            }
        ?>  
    </body>
</html>