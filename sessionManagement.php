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
                    // Divide up available functionality based on role
                    if($_SESSION["role"] == "admin"){
                        if(managementEditDeleteCheck()){
                            if($_GET["action"] == "edit"){
                                $id = $_GET["id"];
                                $session = $db->getSession($id)[0]; // get the selected event

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
                                $id = $_GET["id"];              // ID of venue passed in URL

                                // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                                if(isset($_GET['confirm']) && !empty($_GET['confirm'])){
                                    $dataFields = array();
                                    $dataFields["area"] = "event";
                                    $dataFields["fields"] = array(
                                        "id" => $id,
                                    );
                                    $dataFields["method"] = array(
                                        "delete" => "deleteSession"   // Special case for events -> need to delete everythin associated with the deleted event
                                    );
                                    deleteAction($dataFields);
                                }

                                $session = $db->getSession($id)[0];   // event object
                                // event SPECIFIC TABLE W/btns
                                echo "<h2 class='section-heading'>Delete event</h2>";
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
                    else if($_SESSION["role"] == "event_manager"){

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
                        if(is_numeric($event)){
                            $findEvent = $db->getEvent(intval($event));
                            if(count($findEvent) == 0 || empty($findEvent)){
                                $flag = false;
                            }
                        }
                        else {
                            $flag = false;
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
                            echo "<p class='form-error-text'>** Invalid inputs</p>";
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
                                addPost($dataFields);
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
