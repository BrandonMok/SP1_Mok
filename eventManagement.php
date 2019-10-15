<?php
    session_name("Mok_Project1");
    session_start();

    require_once("DB.class.php");
    require_once("utilities.php");
    require_once("validations.php");
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
                                $event = $db->getEvent($id); // get the selected event


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

                                // EDIT Event SECTION
                                echo "<h2 class='section-heading'>Edit Event</h2>";

                                $eventEditTable = "<div class='edit-add-form-container'>
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
                                                                <input type='text' name='id' value='{$event->getIdEvent()}' readonly='readonly'>
                                                                <input type='text' name='name' value='{$event->getName()}'>
                                                                <input type='text' name='datestart' value='{$event->getDateStart()}'>
                                                                <input type='text' name='dateend' value='{$event->getDateEnd()}'>
                                                                <input type='text' name='numberallowed' value='{$event->getNumberAllowed()}'>
                                                                <input type='text' name='venue' value='{$event->getVenue()}'>
                                                            </div>
                                                            <input type='hidden' name='originalValues' value='{$originalValues}'><br/>
                                                            <input name='submit' id='submit-btn' type='submit' value='Submit'/>
                                                        </form>
                                                    </div>";
                                echo $eventEditTable;
                            }// end if EDIT
                            else if($_GET["action"] == "delete"){
                                // DELETE
                                $id = $_GET["id"];              // ID of venue passed in URL

                                // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                                if(isset($_GET['confirm']) && !empty($_GET['confirm'])){
                                    $dataFields = array();
                                    $dataFields["area"] = "event";
                                    $dataFields["fields"] = array(
                                        "id" => $id,
                                    );
                                    $dataFields["method"] = array(
                                        "delete" => "deleteEventAndSession"   // Special case for events -> need to delete everythin associated with the deleted event
                                    );
                                    deleteAction($dataFields);
                                }

                                $event = $db->getevent($id);   // event object

                                // event SPECIFIC TABLE W/btns
                                echo "<h2 class='section-heading'>Delete event</h2>";
                                $deleteInfo = "<div class='admin-table-container'>
                                                <table class='admin-table'>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Date Start</th>
                                                        <th>Date End</th>
                                                        <th>Number Allowed</th>
                                                        <th>Venue</th> 
                                                    </tr>
                                                    <tr>
                                                        <td>{$event->getIdevent()}</td>
                                                        <td>{$event->getName()}</td>
                                                        <td>{$event->getDateStart()}</td>
                                                        <td>{$event->getDateEnd()}</td>
                                                        <td>{$event->getNumberAllowed()}</td>
                                                        <td>{$event->getVenue()}</td>
                                                    </tr>
                                                </table>
                                            </div>";
                                echo $deleteInfo;

                                // Yes & no options to delete action
                                echo "<h2 class='section-heading'>Are you sure you want to delete the selected event?</h2><br/>";
                                $optionDiv = "<div id='confirm-delete-container' class='center-element'>
                                                    <a href='./eventManagement.php?id={$event->getIdevent()}&action=delete&confirm=yes'>
                                                        <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                                                    </a>
                                                    <a href='./eventManagement.php?id={$event->getIdevent()}&action=delete&confirm=no'>
                                                        <div class='delete-btn' id='deny-delete-btn'>No</div>
                                                    </a>
                                                </div>";
                                echo $optionDiv;
                            }// end if DELETE
                            else {
                                // REDIRECT: something else besides edit or delete was passed
                                redirect("admin");
                            }
                        }// end if edit/delete allowed
                        else if(managementAddCheck()){
                            if($_GET["action"] == "add"){
                                $data = array();
                                $data["area"] = "Event";
                                $data["formAction"] = "./eventManagement.php?&action=add";
                                $data["labels"] = array("ID", "Name", "Date Start", "Date End", "Number Allowed", "Venue");
                                $data["input"] = array(
                                    "id" => array(
                                        "name" => "id",
                                        "readonly" => "readonly",
                                        "placeholder" => "Auto-increment"
                                    ),
                                    "name" => array(
                                        "name" => "name"
                                    ),
                                    "datestart" => array(
                                        "name" => "datestart",
                                        "placeholder" => "yyyy-mm-dd hh:mm:ss"
                                    ),
                                    "dateend" => array(
                                        "name" => "dateend",
                                        "placeholder" => "yyyy-mm-dd hh:mm:ss"
                                    ),
                                    "numberallowed" => array(
                                        "name" => "numberallowed"
                                    ),
                                    "venue" => array(
                                        "name" => "venue"
                                    )
                                );
                                addActionHTML($data);
                            }
                            else{
                                // REDIRECT: Action is something else
                                redirect("admin");
                            }
                        }// end if action was the only set
                        else{
                            // REDIRECT: Something other action passed
                            redirect("admin");
                        }
                    }
                    /** -------------------- EVENT MANAGER --------------------*/                    
                    else if($_SESSION["role"] == "event_manager"){
                        // EVENT MANAGER - ADD/EDIT/DELETE events,sessions, and attendees for THEIR EVENT
                        if(managementEditDeleteCheck()){
                            if($_GET["action"] == "edit"){
                                // // EDIT
                                // $id = $_GET["id"];
                                // $event = $db->getEvent($id); // get the selected event

                                // // Store original values to compare to on POST
                                // // Don't want to keep querying same object when doing post logic
                                // $originalValues = array(
                                //     "name" => $event->getName(),
                                //     "datestart" => $event->getDateStart(),
                                //     "dateend" => $event->getDateEnd(), 
                                //     "numberallowed" => $event->getNumberAllowed(),
                                //     "venue" => $event->getVenue()
                                // );
                                // $originalValues = json_encode($originalValues);

                                // // EDIT Event SECTION
                                // echo "<h2 class='section-heading'>Edit Event</h2>";

                                // $eventEditTable = "<div class='edit-add-form-container'>
                                //                         <form id='user-edit-form' name='user-edit-form' action='./eventManagement.php?id={$event->getIdEvent()}&action=edit' method='POST'>
                                //                             <div id='user-edit-labels'>
                                //                                 <label>ID</label>
                                //                                 <label>Name</label>
                                //                                 <label>Date Start</label>
                                //                                 <label>Date End</label>
                                //                                 <label>Number Allowed</label>
                                //                                 <label>Venue</label>   
                                //                             </div>
                                //                             <div id='user-edit-inputs'>
                                //                                 <input type='text' name='id' value='{$event->getIdEvent()}' readonly='readonly'>
                                //                                 <input type='text' name='name' value='{$event->getName()}'>
                                //                                 <input type='text' name='datestart' value='{$event->getDateStart()}'>
                                //                                 <input type='text' name='dateend' value='{$event->getDateEnd()}'>
                                //                                 <input type='text' name='numberallowed' value='{$event->getNumberAllowed()}'>
                                //                                 <input type='text' name='venue' value='{$event->getVenue()}'>
                                //                             </div>
                                //                             <input type='hidden' name='originalValues' value='{$originalValues}'><br/>
                                //                             <input name='submit' id='submit-btn' type='submit' value='Submit'/>
                                //                         </form>
                                //                     </div>";
                                // echo $eventEditTable;
                            }
                            else if($_GET["action"] == "delete"){

                            }
                        }
                        else if(managementAddCheck()){
                            if($_GET["action"] == "add"){
                                $data = array();
                                $data["area"] = "Event";
                                $data["formAction"] = "./eventManagement.php?&action=add";
                                $data["labels"] = array("ID", "Name", "Date Start", "Date End", "Number Allowed", "Venue");
                                $data["input"] = array(
                                    "id" => array(
                                        "name" => "id",
                                        "readonly" => "readonly",
                                        "placeholder" => "Auto-increment"
                                    ),
                                    "name" => array(
                                        "name" => "name"
                                    ),
                                    "datestart" => array(
                                        "name" => "datestart",
                                        "placeholder" => "yyyy-mm-dd hh:mm:ss"
                                    ),
                                    "dateend" => array(
                                        "name" => "dateend",
                                        "placeholder" => "yyyy-mm-dd hh:mm:ss"
                                    ),
                                    "numberallowed" => array(
                                        "name" => "numberallowed"
                                    ),
                                    "venue" => array(
                                        "name" => "venue"
                                    )
                                );
                                addActionHTML($data);
                            }
                        }
                        else{
                            // REDIRECT: Is an event manager, but action is not one available
                            redirect("events.php");
                        }
                    }
                    else {
                        // REDIRECT: User is an attendee
                        redirect("events");
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
                        // Grab values
                        $id = $_GET["id"];
                        $name = sanitizeString($_POST["name"]);
                        $datestart = sanitizeString($_POST["datestart"]);
                        $dateend = sanitizeString($_POST["dateend"]);
                        $numberAllowed = sanitizeString($_POST["numberallowed"]);  
                        $venue = sanitizeString($_POST["venue"]);            
                        $originalValues = json_decode($_POST["originalValues"]);      

                        $flag = true;

                        if(date3($datestart) == false || date3($dateend) == false){
                            $flag = false;
                            echo "<p class='form-error-text'>** Invalid date format!</p>";
                        }
                        if(alphabeticSpace($name) == false){
                            $flag = false;
                            echo "<p class='form-error-text'>** Invalid: Name contains non-alphabetic characters!</p>";
                        }
                        if(is_numeric($numberAllowed) == false){
                            $flag = false;
                            echo "<p class='form-error-text'>** Invalid: Number allowed isn't a valid value!</p>";
                        }
                        if(is_numeric($venue)){
                            $findVenue = $db->getVenue(intval($venue));
                            if($findVenue == 0 || $findVenue == -1 ){
                                $flag = false;
                            }
                        }
                        else {
                            $flag = false;
                        }


                        if($flag){
                            // Perform EDIT POST REQUEST Processing
                            $dataFields = array();
                            $dataFields["area"] = "event";
                            $dataFields["fields"] = array(
                                "id" => $id,
                                "name" => $name,
                                "datestart" => $datestart,
                                "dateend" => $dateend,
                                "numberallowed" => $numberAllowed,
                                "venue" => $venue
                            );
                            $dataFields["method"] = array(
                                "update" => "updateEvent"
                            );
                            $dataFields["originalValues"] = $originalValues;
                            editPost($dataFields);
                        }
                        else {
                            echo "<p class='form-error-text'>** Invalid inputs</p>";
                        }
                    }// end EDIT post processing
                    else if($_GET["action"] == "add") {
                        // Grab & sanitize inputs
                        $name = sanitizeString($_POST["name"]);
                        $datestart = sanitizeString($_POST["datestart"]);
                        $dateend = sanitizeString($_POST["dateend"]);
                        $numberAllowed = sanitizeString($_POST["numberallowed"]);  
                        $venue = sanitizeString($_POST["venue"]);    

                        // CHECK: if all inputs were given a value
                        if(isset($name) && isset($datestart) && isset($dateend) && isset($numberAllowed) && isset($venue)){
                            // Only if the venue exists to associate with, then add 
                            if($db->getVenue(intval($venue)) > 0){
                                // Perform ADD POST REQUEST Processing
                                // addPost() will handle making sure names are alphabetic, dates follow format, and numberallowed/venue are > 0
                                $dataFields = array();
                                $dataFields["area"] = "event";
                                $dataFields["fields"]["name"] = array("type" => "sn", "value" => $name);                // event names can have numbers     
                                $dataFields["fields"]["datestart"] = array("type" => "date", "value" => $datestart);
                                $dataFields["fields"]["dateend"] = array("type" => "date", "value" => $dateend);
                                $dataFields["fields"]["numberallowed"] = array("type" => "i", "value" => $numberAllowed);
                                $dataFields["fields"]["venue"] = array("type" => "i", "value" => $venue);
                                $dataFields["method"] = array(
                                    "add" => "addEvent"
                                );
                                $lastID = addPost($dataFields);


                                // In case of event manager, also make an manager_event object
                                if($_SESSION["role"] == "event_manager"){
                                    /**
                                     * Also need to make a manager_event OBJECT!
                                     */

                                    if(eventManagerEventCheck($lastID)){
                                        // Event exists! Good to make manager_event object
                                        $managerEventData = array();
                                        $managerEventData["event"] = $lastID;
                                        $managerEventData["manager"] = $_SESSION["id"];

                                        $managerEventObjID = addManagerEvent($managerEventData); // call to make object

                                        var_dump($managerEventObjID);

                                        if($managerEventObjID > 0){
                                            // If all good, redirect
                                            redirect("admin");
                                        }
                                        else {
                                            // ERROR: Making manager_event object failed!
                                            echo "<p class='form-error-text'>** Creating new event as event manager failed!</p>";
                                        }
                                    }
                                    else {
                                        // ERRRO: Event trying to associate with failed!
                                        echo "<p class='form-error-text'>** Creating new event as event manager failed!</p>";
                                    }
                                }// end if event manager


                                // After making necessary objects, redirect
                                redirect("admin");
                            }
                            else{
                                // ERROR: Something went wrong with value of inputs
                                echo "<p class='form-error-text'>** Invalid inputs!</p>";
                            }
                        }
                        else{
                            // ERROR: No values supplied and/or field missing a value
                            echo "<p class='form-error-text'>** Invalid inputs!</p>";
                        }
                    }// end action ADD processing
                }// end if ACTION is present
            }// end if POST
        ?>
    </body>
</html>