<?php
    session_name("Mok_Project1");
    session_start();

    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Session Management</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            // CHECK: User is logged in & is an admin or event manager
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager"){
                    $userRole = $_SESSION["role"]; // user's role

                    if(managementEditDeleteCheck()){
                        if($_GET["action"] == "edit"){
                            $id = $_GET["id"];  // SessionID

                            // Determine user's specific role to know which data to use
                            if($userRole == "admin"){
                                $session = $db->getAllSessions($id); // get the selected event
                            }
                            else if($userRole == "event_manager"){
                                $managerSessions = $db->getAllManagerSessionsObj($_SESSION["id"]); // manager_session Objects!

                                if(count($managerSessions) > 0){
                                    foreach($managerSessions as $mSession){
                                        if($mSession->getSession() == $id){
                                            $session = $db->getAllSessions($id);
                                            break;
                                        }
                                    }

                                    if(!isset($session)){
                                        // REDIRECT: Session trying to edit wasn't one that manager has!
                                        redirect("admin");
                                    }
                                }
                                else {
                                    // REDIRECT: Either no manager sessions exist to compare with or not their session
                                    redirect("admin");
                                }
                            }

                            // Store original values to compare to on POST
                            // Don't want to keep querying same object when doing post logic
                            $originalValues = array(
                                "name" => $session->getName(),
                                "numberallowed" => $session->getNumberAllowed(),
                                "event" => $session->getEvent(),
                                "startdate" => $session->getStartDate(),
                                "enddate" => $session->getEndDate()
                            );
                            $originalValues = json_encode($originalValues);

                            // EDIT Session SECTION
                            echo "<h2 class='section-heading'>Edit Session</h2>";
                            $sessionEditTable = "<div class='edit-add-form-container'>
                                                    <form id='user-edit-form' name='user-edit-form' action='./sessionManagement.php?id={$session->getIdSession()}&action=edit' method='POST'>
                                                        <div id='user-edit-labels'>
                                                            <label>ID</label>
                                                            <label>Name</label>
                                                            <label>Number Allowed</label>
                                                            <label>Event</label>   
                                                            <label>Start Date</label>
                                                            <label>End Date</label>   
                                                        </div>
                                                        <div id='user-edit-inputs'>
                                                            <input type='text' name='id' value='{$session->getIdSession()}' readonly='readonly'>
                                                            <input type='text' name='name' value='{$session->getName()}'>
                                                            <input type='text' name='numberallowed' value='{$session->getNumberAllowed()}'>
                                                            <input type='text' name='event' value='{$session->getEvent()}'>
                                                            <input type='text' name='datestart' value='{$session->getStartDate()}'>
                                                            <input type='text' name='dateend' value='{$session->getEndDate()}'>
                                                        </div>
                                                        <input type='hidden' name='originalValues' value='{$originalValues}'><br/>
                                                        <input name='submit' id='submit-btn' type='submit' value='Submit'/>
                                                    </form>
                                                </div>";
                            echo $sessionEditTable;
                        }
                        else if($_GET["action"] == "delete"){
                            // DELETE
                            $id = $_GET["id"];  // sessionID

                            // Determine user's specific role to know which data to use
                            if($userRole == "admin"){
                                $session = $db->getAllSessions($id); // get the selected session
                            }
                            else if($userRole == "event_manager"){
                                // get all the sessions related to this event manager!
                                $eventManagerSessions = $db->getAllManagerSessionsObj($_SESSION["id"]); // Manager_Session Objects
                                if(count($eventManagerSessions) > 0){
                                    foreach($eventManagerSessions as $v){
                                        if($v->getSession() == $id){
                                            $session = $db->getAllSessions($id);
                                            break;
                                        }
                                    }

                                    if(!isset($session)){
                                        // ERROR: Specific session not found 
                                        redirect("admin");
                                    }
                                }
                                else {
                                    // ERROR: No sessions not found for a event manager's event
                                    redirect("admin");
                                }
                            }


                            // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                            if(isset($_GET['confirm']) && !empty($_GET['confirm'])){
                                $dataFields = array();
                                $dataFields["area"] = "session";
                                $dataFields["fields"] = array(
                                    "id" => $id
                                );
                                $dataFields["method"] = array(
                                    "delete" => "deleteAllSessions"   // Special case for events -> need to delete everythin associated with the deleted event
                                );
                                $delete = deleteAction($dataFields);

                                if(count($delete) > 0){
                                    /**
                                     * Event Managers also need to delete their manager_session obj when deleting entire session
                                     */
                                    if($userRole == "event_manager"){
                                        $db->deleteManagerSession($id);
                                    }
                                }

                                redirect("admin");
                            }

                            // event SPECIFIC TABLE W/btns
                            echo "<h2 class='section-heading'>Delete Session</h2>";
                            $deleteInfo = "<div class='admin-table-container'>
                                            <table class='admin-table'>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Number Allowed</th>
                                                    <th>Event</th>   
                                                    <th>Start Date</th>
                                                    <th>End Date</th>   
                                                </tr>
                                                <tr>
                                                    <td>{$session->getIdSession()}</td>
                                                    <td>{$session->getName()}</td>
                                                    <td>{$session->getNumberAllowed()}</td>
                                                    <td>{$session->getEvent()}</td>
                                                    <td>{$session->getStartDate()}</td>
                                                    <td>{$session->getEndDate()}</td>
                                                </tr>
                                            </table>
                                        </div>";
                            echo $deleteInfo;

                            // Yes & no options to delete action
                            echo "<h2 class='section-heading'>Are you sure you want to delete the selected session?</h2><br/>";
                            $optionDiv = "<div id='confirm-delete-container' class='center-element'>
                                                <a href='./sessionManagement.php?id={$session->getIdSession()}&action=delete&confirm=yes'>
                                                    <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                                                </a>
                                                <a href='./sessionManagement.php?id={$session->getIdSession()}&action=delete&confirm=no'>
                                                    <div class='delete-btn' id='deny-delete-btn'>No</div>
                                                </a>
                                            </div>";
                            echo $optionDiv;
                        }
                    }
                    else if(managementAddCheck()){
                        if($_GET["action"] == "add"){
                            $data = array();
                            $data["area"] = "Session";
                            $data["formAction"] = "./sessionManagement.php?&action=add";
                            $data["labels"] = array("ID", "Name", "Number Allowed", "Event", "Start Date", "End Date");
                            $data["input"] = array(
                                "id" => array(
                                    "name" => "id",
                                    "readonly" => "readonly",
                                    "placeholder" => "Auto-increment"
                                ),
                                "name" => array(
                                    "name" => "name"
                                ),
                                "numberallowed" => array(
                                    "name" => "numberallowed"
                                ),
                                "event" => array(
                                    "name" => "event"
                                ),
                                "datestart" => array(
                                    "name" => "datestart",
                                    "placeholder" => "yyyy-mm-dd hh:mm:ss"
                                ),
                                "dateend" => array(
                                    "name" => "dateend",
                                    "placeholder" => "yyyy-mm-dd hh:mm:ss"
                                ),
                            );
                            addActionHTML($data);
                        }
                        else{
                            // REDIRECT: Something other action passed
                            redirect("admin");
                        }
                    }
                    else{
                        // REDIRECT: Something other action passed
                        redirect("admin");
                    }
                }
                else {
                    // REDIRECT: User is an attendee
                    redirect("events");
                }
            }
            else{
                // REDIRECT - User not logged in
                redirect("login");
            }





            /** -------------------- POST LOGIC --------------------*/
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){
                        // Grab values
                        $id = $_GET["id"];
                        $name = sanitizeString($_POST["name"]);
                        $numberAllowed = sanitizeString($_POST["numberallowed"]);  
                        $event = sanitizeString($_POST["event"]);
                        $datestart = sanitizeString($_POST["datestart"]);
                        $dateend = sanitizeString($_POST["dateend"]);
                        $originalValues = json_decode($_POST["originalValues"]);   

                        $flag = true;

                        if(date3($datestart) == false || date3($dateend) == false){
                            $flag = false;
                            errorDisplay("Invalid: Date format not in yyyy-mm-dd hh:mm:ss format!");
                        }
                        if(is_numeric($numberAllowed) == false){
                            $flag = false;
                            errorDisplay("Invalid: Number allowed isn't a valid value!");
                        }
                        $findEvent = $db->getEvent(intval($event));
                        if(count($findEvent) == 0 || empty($findEvent)){
                            $flag = false; // event doesn't exist or not found!
                        }

                        // IN CASE: user is event manager, double check to make sure E.M owns the event this session is associated with!
                        if($_SESSION["role"] == "event_manager"){
                            // Make sure event_manager owns the event trying to edit!!
                            $managerEvent = $db->getAllManagerEvents($event, $_SESSION["id"]);    // retrieve manager_event object (only one will be returned)

                            if(!isset($managerEvent) || empty($managerEvent)){
                                $flag = false;
                                errorDisplay("Invalid: Event for the session doesn't belong to you!");
                            }
                        }


                        if($flag){
                            // Perform EDIT POST REQUEST Processing
                            $dataFields = array();
                            $dataFields["area"] = "session";
                            $dataFields["fields"] = array(
                                "id" => $id,
                                "name" => $name,
                                "numberallowed" => $numberAllowed,
                                "event" => $event,
                                "datestart" => $datestart,
                                "dateend" => $dateend
                            );
                            $dataFields["method"] = array(
                                "update" => "updateSession"
                            );
                            $dataFields["originalValues"] = $originalValues;
                            editPost($dataFields);
                        }
                        else {
                            errorDisplay("Invalid inputs");
                        }
                    }// end EDIT post processing
                    else if($_GET["action"] == "add") {
                        // Grab & sanitize inputs
                        $name = sanitizeString($_POST["name"]);
                        $numberAllowed = sanitizeString($_POST["numberallowed"]);  
                        $event = sanitizeString($_POST["event"]);
                        $datestart = sanitizeString($_POST["datestart"]);
                        $dateend = sanitizeString($_POST["dateend"]);

                        // CHECK: if all inputs were given a value
                        if(isset($name) && isset($numberAllowed) && isset($event) && isset($datestart) && isset($dateend)){
                            // CHECK: if the event trying to associate with exists! 
                            if(!empty($db->getEvent(intval($event)))){
                                // Perform ADD POST REQUEST Processing
                                // addPost() will handle making sure names are alphabetic, dates follow format, and numberallowed/venue are > 0
                                $dataFields = array();
                                $dataFields["area"] = "event";
                                $dataFields["fields"]["name"] = array("type" => "sn", "value" => $name);                // event names can have numbers     
                                $dataFields["fields"]["datestart"] = array("type" => "date", "value" => $datestart);
                                $dataFields["fields"]["dateend"] = array("type" => "date", "value" => $dateend);
                                $dataFields["fields"]["numberallowed"] = array("type" => "i", "value" => $numberAllowed);
                                $dataFields["fields"]["event"] = array("type" => "i", "value" => $event);
                                $dataFields["method"] = array(
                                    "add" => "addSession"
                                );
                                $lastID = addPost($dataFields);

                                // Event Managers also need to make a manager_session object to keep track of their created sessions
                                if($_SESSION["role"] == "event_manager"){
                                    $lastCreatedSession = $db->getAllSessions($lastID);
                                    $eventManagerEvent = $db->getAllManagerEvents($lastCreatedSession->getEvent(), $_SESSION["id"]); // returns a managereventOBJ IF they owned that event

                                    // CHECK: Session created OK & that the created session's event is owned by the event_manager!
                                    // ONLY ALLOW EVENT_MANAGERS TO ADD/EDIT/DELETE THEIR OWN EVENTS!!
                                    if(count($lastCreatedSession) > 0 && count($eventManagerEvent) > 0){
                                        // Event exists! Good to make manager_event object
                                        $managerSession = array();
                                        $managerSession["session"] = $lastID;
                                        $managerSession["manager"] = $_SESSION["id"];

                                        $managerSessionObjID = $db->addManagerSession($managerSession); // call to make object

                                        if($managerSessionObjID > 0){
                                            // If all good, redirect
                                            redirect("admin");
                                        }
                                        else {
                                            // ERROR: Making manager_session failed!
                                            errorDisplay("Creating new session failed!");
                                        }
                                    }
                                    else {
                                        // ERROR: Making manager_session  failed!
                                        errorDisplay("Creating new session failed!");
                                    }
                                }

                                // After making necessary objects, redirect
                                redirect("admin");
                            }
                            else{
                                // ERROR: Something went wrong with value of inputs
                                errorDisplay("Invalid: Event doesn't exist!");
                            }
                        }
                        else{
                            // ERROR: No values supplied and/or field missing a value
                            errorDisplay("Invalid: Invalid inputs and/or empty field(s)!");
                        }
                    }// end action ADD processing
                }// end if ACTION is present
            }// end if POST
        ?>
    </body>
</html>
