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
                    $userRole = $_SESSION["role"];

                    // Check if both ID and Action were passed = edit and delete processes can continue
                    if(managementEditDeleteCheck()){
                        if($_GET["action"] == "edit"){
                            $id = $_GET["id"];  // id of event

                            // Determine user's specific role to know which data to use
                            if($userRole == "admin"){
                                $event = $db->getEvent($id); // get the selected event
                            }
                            else if($userRole == "event_manager"){
                                // Make sure this event is the event_managers to be safe!
                                $managerEvent = $db->getManagerEvent($id);
                                if(count($managerEvent) > 0 && $managerEvent[0]->getManager() == $_SESSION['id']){
                                    // The manager owns this event if the eventID is under the manager_event event field
                                    $event = $db->getEvent($id); 
                                }
                                else{
                                    // REDIRECT: User doesn't have access to this event OR event doesn't exist
                                    redirect("admin");
                                }
                            }

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
                            $id = $_GET["id"];              

                            // Determine user's specific role to know which data to use
                            if($userRole == "admin"){
                                $event = $db->getEvent($id); // get the selected event
                            }
                            else if($userRole == "event_manager"){
                                // Make sure this event is the event_managers to be safe!
                                $managerEvent = $db->getManagerEvent($id);
                                if(count($managerEvent) > 0 && $managerEvent[0]->getManager() == $_SESSION['id']){
                                    // The manager owns this event if the eventID is under the manager_event event field
                                    $event = $db->getEvent($id); 
                                }
                                else{
                                    // REDIRECT: User doesn't have access to this event OR event doesn't exist
                                    redirect("admin");
                                }
                            }

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
                                                    <td>{$event->getIdEvent()}</td>
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
                                                <a href='./eventManagement.php?id={$event->getIdEvent()}&action=delete&confirm=yes'>
                                                    <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                                                </a>
                                                <a href='./eventManagement.php?id={$event->getIdEvent()}&action=delete&confirm=no'>
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

                        echo var_dump(date3($_POST["datestart"]));

                        if(!date3($datestart) || !date3($dateend)){
                            $flag = false;
                            echo "<p class='form-error-text'>** Invalid date format!</p>";
                        }
                        // if(!alphaNumeric($name) && alphaNumericSpace($name)){
                        //     $flag = false;
                        //     echo "<p class='form-error-text'>** Invalid: Invalid name!</p>";
                        // }
                        if(!is_numeric($numberAllowed)){
                            $flag = false;
                            echo "<p class='form-error-text'>** Invalid: Number allowed isn't a valid value!</p>";
                        }
                        $findVenue = $db->getVenue(intval($venue));
                        if(count($findVenue) <= 0){
                            $flag = false;
                            echo "<p class='form-error-text'>** Invalid: Venue doesn't exist!</p>";
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

                                        $managerEventObjID = $db->addManagerEvent($managerEventData); // call to make object

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