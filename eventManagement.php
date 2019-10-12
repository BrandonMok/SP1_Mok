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
            // ONLY ADMIN and EVENT MANAGER 
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager"){
                    // Distinguish which user role to allow specific functions
                    if($_SESSION["role"] == "admin"){
                        // Check if both ID and Action were passed = edit and delete processes can continue
                        if(managementEditDeleteCheck()){
                            if($_GET["action"] == "edit"){
                                // EDIT

                            }
                            else if($_GET["action"] == "delete"){
                                // DELETE

                            }
                            else {
                                // REDIRECT: something else besides edit or delete was passed
                                redirect("admin");
                            }
                        }// end if edit/delete allowed
                        else if(managementAddCheck()){
                            // Add 
                            if($_GET["action"] == "add"){




                            }
                            else{
                                // REDIRECT: Action is something else
                                redirect("admin");
                            }
                        }// end if action was the only set
                        else{
                            // Something other action passed
                            redirect("admin");
                        }
                    }
                    else if($_SESSION["role"] == "event_manager"){
                        // EVENT MANAGER

                        

                    }
                }
                else {
                    // REDIRECT: User is an attendee
                    redirect("events");
                }
            }// end if logged in
            else {
                // REDIRECT: User not logged in
                redirect("login");
            }
        ?>
    </body>
</html>