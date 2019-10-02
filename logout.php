<?php
    session_name("Mok_Project1");
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Logging out...</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            // Header
            reusableHeader();

            // Verify User logged in before allowing any actions
            if(!isset($_SESSION['userLoggedIn'])){
                // Logout page meant to unset session variables
                // DON'T want someone logged in to directly go to this page
                header('Location: login.php');
                exit;
            }
        ?>
        <div id="logout-screen">
            <span id="logout-textbox">Logging Out...</span>
        </div>


        <?php
            endSession(); // End the Session!

            usleep(2000000);

            header('Location: login.php');
            exit;

            reusableFooter();
        ?>       
    </body>
</html>