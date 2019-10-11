<?php
    session_name("Mok_Project1");
    session_start();

    require_once("DB.class.php");
    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Event Management</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            // Verify User logged in before allowing any actions
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                // admin processes - all add/edit/delete
                if($_SESSION["role"] == "admin"){
                    // Check if both ID and Action were passed = edit and delete processes can continue
                    if(managementEditDeleteCheck()){
                        // Edit and Delete

                        if($_GET["action"] == "edit"){

                        }
                        else if($_GET["action"] == "delete"){

                        }
                    }
                    else if(managementAddCheck()){
                        // Add 
                    }

                }
                else if($_SESSION["role"] == "event_manager"){
                    // Event Manager can only add/edit/delete THEIR OWN events

                }
                else {
                    // Case when user is an attendee - redirect
                    Location("Location: events.php");
                    exit;
                }
            }// end if logged in
            else {
                // User not logged in - redirect
                header("Location: login.php");
                exit;
            }
        ?>
    </body>
</html>