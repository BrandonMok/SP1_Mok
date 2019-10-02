<?php
    session_name("Mok_Project1");
    session_start();
    
    require_once('DB.class.php');
    require_once('utilities.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Registration</title>
        <?php
            reusableLinks();
        ?>
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