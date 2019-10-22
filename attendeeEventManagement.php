<?php
    session_name("Mok_Project1");
    session_start();

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
                            $attendeeID = $_GET["id"];  //attendeeID
                            $eventID = $_GET["event"]; // eventID


                            if($userRole == "admin"){
                                $attendeeEvent = $db->getAllAttendeeEvents($eventID, $attendeeID); 

                            }
                            else if($userRole == "event_manager"){
                                // Make sure this event is the event_managers to be safe!
                                $eventManagerEvents = $db->getManagerEventOBJ($eventID);
                                if(count($eventManagerEvents) > 0){
                                    // This is an event_manager owned event
                                    $attendeeEvent = $db->getAllAttendeeEvents($eventID, $attendeeID); 
                                }
                                
                                if(!isset($attendeeEvent)){
                                    // REDIRECT: Not event_manager's event
                                    redirect("admin");
                                }
                            }

                            // Store original values to compare to on POST
                            // Don't want to keep querying same object when doing post logic
                            $originalValues = array(
                                "event" => $attendeeEvent->getEvent(),
                                "attendee" => $attendeeEvent->getAttendee(),
                                "paid" => $attendeeEvent->getPaid(), 
                            );
                            $originalValues = json_encode($originalValues);

                            // EDIT Event SECTION
                            echo "<h2 class='section-heading'>Edit Attendee Event</h2>";
                            $eventEditTable = "<div class='edit-add-form-container'>
                                                    <form id='user-edit-form' name='user-edit-form' action='./attendeeEventManagement.php?id={$attendeeEvent->getAttendee()}&event={$attendeeEvent->getEvent()}&action=edit' method='POST'>
                                                        <div id='user-edit-labels'>
                                                            <label>Event</label>
                                                            <label>Attendee</label>
                                                            <label>Paid</label>
                                                        </div>
                                                        <div id='user-edit-inputs'>
                                                            <input type='text' name='event' value='{$attendeeEvent->getEvent()}'>
                                                            <input type='text' name='attendee' value='{$attendeeEvent->getAttendee()}'>
                                                            <input type='text' name='paid' value='{$attendeeEvent->getPaid()}'>
                                                        </div>
                                                        <input type='hidden' name='originalValues' value='{$originalValues}'><br/>
                                                        <input name='submit' id='submit-btn' type='submit' value='Submit'/>
                                                    </form>
                                                </div>";
                            echo $eventEditTable;
                        }
                        else if($_GET["action"] == "delete"){
                            if($userRole == "admin"){
                                $attendeeEvent = $db->getAllAttendeeEvents($_GET["event"],$_GET["id"]);

                            }
                            else if ($userRole = "event_manager"){
                                $managerEvents = $db->getAllManagerEventsOBJ($_SESSION["id"]); // get all this manager's events

                                // make sure the attendee event object's event is tied to manager's event
                                if(count($managerEvents) > 0){
                                    foreach($managerEvents as $mEvents){
                                        if($mEvents->getEvent() == $_GET["event"]){
                                            $attendeeEvent = $db->getAllAttendeeEvents($_GET["event"],$_GET["id"]);
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
                                $dataFields = array();
                                $dataFields["area"] = "attendee event";
                                $dataFields["fields"] = array(
                                    "id" => $_GET["event"],
                                    "attendee" => $_GET["id"]
                                );
                                $dataFields["method"] = array(
                                    "delete" => "deleteAttendeeEvent" 
                                );
                                $delete = deleteAction($dataFields);


                                if($delete > 0){
                                    // DELETE attendee_session object too if it exists
                                    $attendeeSessions = $db->getAllAttendeeSessionsById($_GET["id"]); // all attendee_session objects
                                    if(count($attendeeSessions) > 0){
                                        foreach($attendeeSessions as $session){
                                            // GET the session object
                                            $sessionObj = $db->getSession($session->getSession());    // get actual session object
                                            
                                            /**
                                             * CHECK: if the session object for the attendee_session object event is the one
                                             *  whose eventID is the one deleting from the attendee_event, then delete the attendee_session too
                                             *  for this user
                                             */
                                            if($sessionObj->getEvent() == $_GET["event"]){  
                                                // DELETE attendee_session too!
                                                $deleteAttendeeSession = $db->deleteAttendeeSession($session->getSession());
                                            }
                                        }   
                                    }
                                }

                                redirect("admin");
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
                            echo "<h2 class='section-heading'>Are you sure you want to delete the selected attendee event?</h2><br/>";
                            $optionDiv = "<div id='confirm-delete-container' class='center-element'>
                                                <a href='./attendeeEventManagement.php?id={$attendeeEvent->getAttendee()}&event={$attendeeEvent->getEvent()}&action=delete&confirm=yes'>
                                                    <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                                                </a>
                                                <a href='./attendeeEventManagement.php?id={$attendeeEvent->getAttendee()}&event={$attendeeEvent->getEvent()}&action=delete&confirm=no'>
                                                    <div class='delete-btn' id='deny-delete-btn'>No</div>
                                                </a>
                                            </div>";
                            echo $optionDiv;
                        }// end elseif
                    }
                    else if(isset($_GET["action"])){
                        // CASE: Only add provides the action
                        if(managementAddCheck()){
                            if($_GET["action"] == "add"){
                                $data = array();
                                $data["area"] = "Attendee Event";
                                $data["formAction"] = "./attendeeEventManagement.php?&action=add";
                                $data["labels"] = array("Event", "Attendee", "Paid");
                                $data["input"] = array(
                                    "event" => array(
                                        "name" => "event",
                                    ),
                                    "attendee" => array(
                                        "name" => "attendee"
                                    ),
                                    "paid" => array(
                                        "name" => "paid",
                                    )
                                );
                                addActionHTML($data);
                            }
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






            /** -------------------- POST LOGIC --------------------*/
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){
                        $event = $_GET["event"];
                        $attendee = sanitizeString($_POST["attendee"]);
                        $paid = sanitizeString($_POST["paid"]);
                        $originalValues = json_decode($_POST["originalValues"]);      

                        // Perform EDIT POST REQUEST Processing
                        $dataFields = array();
                        $dataFields["area"] = "event";
                        $dataFields["fields"] = array(
                            "event" => $event,
                            "attendee" => $attendee,
                            "paid" => $paid,
                        );
                        $dataFields["method"] = array(
                            // "update" => "updateEvent"
                        );
                        $dataFields["originalValues"] = $originalValues;
                        editPost($dataFields);
                    }// end EDIT post processing
                    else if($_GET["action"] == "add") {
                        // Grab & sanitize inputs
                        $event = sanitizeString($_POST["event"]);
                        $attendee = sanitizeString($_POST["attendee"]);
                        $paid = sanitizeString($_POST["paid"]);

                        // CHECK: if all inputs were given a value
                        if(isset($event) && isset($attendee) && isset($paid)){
                            $eventObj = $db->getEvent($event);
                            $attendeeObj = $db->getUser($attendee);


                            // Make sure both exist
                            if(isset($eventObj) && !empty($eventObj) && isset($attendeeObj) && !empty($attendeeObj)){
                                // Event Manager check to handle only allowing them adding to their events!
                                if($userRole == "event_manager"){
                                    $allManagerEvents = $db->getAllManagerEventsOBJ($_SESSION["role"]);
                                    foreach($allManagerEvents as $mEvents){
                                        if($mEvents->getEvent() == $eventObj->getIdEvent()){
                                            $managerOwnership = true;
                                            break;
                                        }
                                    }

                                    if($managerOwnership == false){
                                        // REDIRECT: Manager doesn't own that event
                                        redirect("admin");
                                    }
                                }

                                // Set paid to 0 if nothing supplied
                                if(empty($paid) || !isset($paid)){
                                    $paid = 0;
                                }

                                $dataFields = array();
                                $dataFields["area"] = "attendee event";
                                $dataFields["fields"]["event"] = array("type" => "i", "value" => $event);                    
                                $dataFields["fields"]["attendee"] = array("type" => "i", "value" => $attendee);
                                $dataFields["fields"]["paid"] = array("type" => "i", "value" => $paid);
                                $dataFields["method"] = array(
                                    "add" => "addAttendeeEvent"
                                );
                                $lastID = addPost($dataFields);

                                redirect("admin");
                            }
                            else {
                                // REDIRECT: Event & user now found
                                echo "<p class='form-error-text'>** Invalid inputs: Event or attendee doesn't exist!</p>";
                            }
                        }// end if isset
                    }// end action ADD processing
                }// end if ACTION is present
            }// end if POST
        ?>
    </body>
</html>