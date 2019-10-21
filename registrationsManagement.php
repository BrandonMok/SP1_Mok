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
            
            /**
             * Management to handle registrations logic
             */
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                // EXTRA check to make sure user is allowed to access page
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager" || $_SESSION["role"] == "attendee"){
                    if($_GET["action"] == "edit"){

                    }
                    else if($_GET["action"] == "delete"){
                        // EVENT
                        if(isset($_GET["event"])){
                            // If deleting event, need to also delete the attendee_sessions!
                            
                        }
                        else if(isset($_GET["session"])){ // SESSION
                            $deleteAttendeeSession = $db->deleteAttendeeSessionBySessionAttendee($_GET["session"], $_SESSION["id"]);
                            if($deleteAttendeeSession > 0){
                                redirect("registrations");
                            }
                            else {
                                // DELETE FAILED
                                echo "<p class='form-error-text'>Deleting session for event failed!</p>";
                            }
                        }
                        else {
                            // REDIRECT: event nor session was supplied
                            redirect("registrations");
                        }
                    }
                }
            }
            else{
                // REDIRECT - User not logged in
                redirect("login");
            }
        ?>
    </body>
</html>