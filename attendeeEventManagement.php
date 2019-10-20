<?php
    session_name("Mok_Project1");
    session_start();

    require_once("DB.class.php");
    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Attendee Event Management</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            /**
             * Admins & event managers
             * Under admin section of application, this page processes edit/delete of attendee_event objects
             */
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager"){
                    $userRole = $_SESSION["role"];

                    if(isset($_GET["id"]) && isset($_GET["event"]) && isset($_GET["action"])){
                        if($_GET["action"] == "edit"){

                        }
                        else if($_GET["action"] == "delete"){
                            if($userRole == "admin"){
                                $attendeeEvent = $db->getAttendeeEventByEventAttendee($_GET["event"],$_GET["id"]);
                            }
                            else if ($userRole = "event_manager"){
                                $managerEvents = $db->getAllManagerEventsOBJ($_SESSION["id"]); // get all this manager's events

                                // make sure the attendee event object's event is tied to manager's event
                                if(count($managerEvents) > 0){
                                    foreach($managerEvents as $mEvents){
                                        if($mEvents->getEvent() == $_GET["event"]){
                                            $attendeeEvent = $db->getAttendeeEventByEventAttendee($_GET["event"],$_GET["id"]);
                                            break;
                                        }
                                    }

                                    // CASE: Attendee_event not found or the event's event isn't owned by the event manager!
                                    if(!isset($attendeeEvent)){
                                        redirect("admin");
                                    }
                                }//end if count
                                else {
                                    // REDIRECT: Manager doesn't have any events -> so don't have permission to alter others besides THEIR OWN
                                    redirect("admin");
                                }
                            }


                            // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                            if(isset($_GET["confirm"]) && !empty($_GET["confirm"])){
                                if($_GET["confirm"] == "yes"){
                                    $dataFields = array();
                                    $dataFields["area"] = "attendee_event";
                                    $dataFields["fields"] = array(
                                        "id" => $_GET["event"],
                                        "attendee" => $_GET["id"]
                                    );
                                    $dataFields["method"] = array(
                                        "delete" => "deleteAttendeeEventObject" 
                                    );
                                    $delete = deleteAction($dataFields);
    
                                    redirect("admin");
                                }
                                else {
                                    redirect("admin");
                                }
                            }


                            // event SPECIFIC TABLE W/btns
                            echo "<h2 class='section-heading'>Delete Attendee Event</h2>";
                            $deleteInfo = "<div class='admin-table-container'>
                                            <table class='admin-table'>
                                                <tr>
                                                    <th>Event</th>
                                                    <th>Attendee</th>
                                                    <th>Paid</th>
                                                </tr>
                                                <tr>
                                                    <td>{$attendeeEvent->getEvent()}</td>
                                                    <td>{$attendeeEvent->getAttendee()}</td>
                                                    <td>{$attendeeEvent->getPaid()}</td>
                                                </tr>
                                            </table>
                                        </div>";
                            echo $deleteInfo;

                            // Yes & no options to delete action
                            echo "<h2 class='section-heading'>Are you sure you want to delete the selected event?</h2><br/>";
                            $optionDiv = "<div id='confirm-delete-container' class='center-element'>
                                                <a href='./attendeeEventManagement.php?id={$attendeeEvent->getEvent()}&action=delete&confirm=yes'>
                                                    <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                                                </a>
                                                <a href='./attendeeEventManagement.php?id={$attendeeEvent->getEvent()}&action=delete&confirm=no'>
                                                    <div class='delete-btn' id='deny-delete-btn'>No</div>
                                                </a>
                                            </div>";
                            echo $optionDiv;
                        }// end elseif
                    }
                    else if(isset($_GET["action"])){
                        // CASE: Only add provides the action
                        if(managementAddCheck()){
                            // if($_GET["action"] == "add"){


                            // }
                        }
                    }
                }// end if role is admin or event manager
                else {
                    // REDIRECT: User isn't an admin
                    redirect("events");
                }
            }
            else {
                // REDIRECT: User not logged in
                redirect("login");
            }
        ?>
    </body>
</html>