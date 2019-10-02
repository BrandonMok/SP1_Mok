<?php
    session_name("Mok_Project1");
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Events</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            // Header
            reusableHeader();

            // Verify User logged in before allowing any actions
            if(isset($_SESSION['userLoggedIn'])){
                echo "HERE THEN";
            }
            else{
                // REDIRECT - User not logged in
                header('Location: login.php');
                exit;
            }
        ?>


        <?php
            reusableFooter();
        ?>       
    </body>
</html>