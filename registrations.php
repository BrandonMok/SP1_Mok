<?php
    session_name("Mok_Project1");
    session_start();
    
    require_once("DB.class.php");
    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Registrations</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            /**
             * Displays the events that a user signed up for!
             */
            
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                // EXTRA check to make sure user is allowed to access page
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager" || $_SESSION["role"] == "attendee"){
                    /**
                     * get Attendee_event objects
                     * And sessions too?
                     */
                    $allRegistration;

                }
            }
            else{
                // REDIRECT - User not logged in
                redirect("login");
            }
        ?>
    </body>
</html>