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
                                $id = $_GET["id"];
                                $event = $db->getEvent($id)[0]; // get the selected event

                                // Store original values to compare to on POST
                                // Don't want to keep querying same object when doing post logic
                                $originalValues = array(
                                    "name" => $event->getName(),
                                    "datestart" => $event->getDateStart(),
                                    "dateend" => $event->getDateEnd(), 
                                    "numberallowed" => $event->getNumberAllowed(),
                                    "venue" => $event->getVenue()
                                );
                                $originalValues = json_encode($originalValues);

                                // EDIT VENUE SECTION
                                echo "<h2 class='section-heading'>Edit Venue</h2>";

                                $eventEditTable = "<div id='account-form-container'>
                                                    <form id='user-edit-form' name='user-edit-form' action='./eventManagement.php?id={$event->getIdEvent()}&action=edit' method='POST'>
                                                        <div id='user-edit-labels'>
                                                            <label>ID</label>
                                                            <label>Name</label>
                                                            <label>Date Start</label>
                                                            <label>Date End</label>
                                                            <label>Number Allowed</label>
                                                            <label>Venue</label>   
                                                        </div>
                                                        <div id='user-edit-inputs'>
                                                            <input type='text' name='id' value='{$event->getIdEvent()}' readonly='readonly'><br/>
                                                            <input type='text' name='name' value='{$event->getName()}'><br/>
                                                            <input type='text' name='datestart' value='{$event->getDateStart()}'><br/>
                                                            <input type='text' name='dateend' value='{$event->getDateEnd()}'><br/>
                                                            <input type='text' name='numberallowed' value='{$event->getNumberAllowed()}'><br/>
                                                            <input type='text' name='venue' value='{$event->getVenue()}'><br/>
                                                        </div><br/>
                                                        <input type='hidden' name='originalValues' value='{$originalValues}'>";
                                                    
                                $eventEditTable .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                echo $eventEditTable;





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

                                // validate date using validation functions


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











            /** -------------------- POST LOGIC --------------------*/
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){

                    }
                    else if($_GET["action"] == "add") {
                        
                    }
                }
            }// end if POST
        ?>
    </body>
</html>