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
                                // Make sure this event is owned by the event_managera
                                $eventManagerEvents = $db->getAllManagerEvents($eventID, $_SESSION["id"]);  // gets a specific manager_event object

                                if(!isset($eventManagerEvents) || empty($eventManagerEvents)){
                                    redirect("admin");
                                }
                                else {
                                    // Manager owns the event! So get the attendee_event object
                                    $attendeeEvent = $db->getAllAttendeeEvents($eventID, $attendeeID); 
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
                                $managerEvents = $db->getAllManagerEvents($_GET["event"], $_SESSION["id"]); // get all this manager's events

                                if(!isset($managerEvents) || empty($managerEvents)){
                                    redirect("admin");
                                }
                                else {
                                    // Event Manager owns this event!
                                    $attendeeEvent = $db->getAllAttendeeEvents($_GET["event"],$_GET["id"]);
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
                                    $attendeeSessions = $db->getAttendeeSessions(0,$_GET["id"]); // all attendee_session objects
                                    if(count($attendeeSessions) > 0){
                                        foreach($attendeeSessions as $session){
                                            $sessionObj = $db->getAllSessions($session->getSession());    // get actual session object
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

                             // Data to use reusable confirmDeleteHtmL function
                             $deleteData = array();
                             $deleteData["area"] = "Attendee Event";
                             $deleteData["th"] = array("Event", "Attendee", "Paid");
                             $deleteData["td"] = array(
                                $attendeeEvent->getEvent(),
                                $attendeeEvent->getAttendee(),
                                $attendeeEvent->getPaid()
                             );
                             $deleteData["choices"]["confirm"] = "./attendeeEventManagement.php?id={$attendeeEvent->getAttendee()}&event={$attendeeEvent->getEvent()}&action=delete&confirm=yes";
                             $deleteData["choices"]["cancel"] = "./attendeeEventManagement.php?id={$attendeeEvent->getAttendee()}&event={$attendeeEvent->getEvent()}&action=delete&confirm=no";
                             confirmDeleteHtml($deleteData);
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

                        // CHECK: Make sure entered fields aren't empty or not isset
                        $data = array($event, $attendee, $paid);
                        $validity = notIssetEmptyCheck($data);

                        // CHECK: if all inputs were given a value
                        if($validity){
                            $eventObj = $db->getEvent($event);      // EVENT 
                            $attendeeObj = $db->getUser($attendee); // User 


                            // Make sure both exist
                            if(isset($eventObj) && !empty($eventObj) && isset($attendeeObj) && !empty($attendeeObj)){
                                // Event Manager check to handle only allowing them adding to their events!
                                // If not their event, redirect!
                                if($userRole == "event_manager"){
                                    $allManagerEvents = $db->getAllManagerEvents($event, $_SESSION["id"]);

                                    if(!isset($allManagerEvents) || empty($allManagerEvents)){
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
                                errorDisplay("Invalid inputs: Event and/or attendee doesn't exist!");
                            }
                        }// end if isset
                        else {
                            // ERROR: No values supplied and/or field missing a value
                            errorDisplay("Invalid: Inputs invalid and/or empty field(s)!");
                        }
                    }// end action ADD processing
                }// end if ACTION is present
            }// end if POST
        ?>
    </body>
</html>