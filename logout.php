<?php
    session_name("Mok_Project1");
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');

    // Verify User logged in before allowing any actions
    if(!isset($_SESSION['userLoggedIn'])){
        // Logout page meant to unset session variables
        // DON'T want someone logged in to directly go to this page
        header('Location: login.php');
        exit;
    }
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
        <div id="logout-screen">
            <div id="logout-textbox"><span>Logging Out... <br/><i class="fas fa-spinner"></i></span></div>
        </div>


        <?php
            endSession(); // End the Session!

            header('Location: login.php');
            exit;
        ?>       
    </body>
</html>