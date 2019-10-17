<?php
    session_name("Mok_Project1");
    session_start();
    
    require_once("DB.class.php");
    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Registrations Management</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();
            
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                // EXTRA check to make sure user is allowed to access page
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager" || $_SESSION["role"] == "attendee"){
                    
                    /**
                     * Put something in URL - each specific registration
                     */

                }
            }
            else{
                // REDIRECT - User not logged in
                redirect("login");
            }
        ?>
    </body>
</html>