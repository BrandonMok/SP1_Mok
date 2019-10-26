<?php
    session_name("Mok_Project1");
    session_start();

    require_once('utilities.php');

    // Verify User logged in before allowing any actions
    if(!isset($_SESSION['userLoggedIn'])){
        // Logout page meant to unset session variables
        // DON'T want someone logged in to directly go to this page
        redirect("login");
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
        <div id="logout-screen" class="center-element">
            <div id="logout-textbox" class="center-element">
                <span>Logging Out... <br/>
                    <i class="fas fa-spinner"></i>
                </span>
            </div>
        </div>


        <?php
            endSession(); // End the Session!

            redirect("login");
        ?>       
    </body>
</html>