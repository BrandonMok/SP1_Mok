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




                            }// end if DELETE
                            else {
                                // REDIRECT: something else besides edit or delete was passed
                                redirect("admin");
                            }
                        }// end if edit/delete allowed
                        else if(managementAddCheck()){
                            // Add 
                            if($_GET["action"] == "add"){
                                 // Add 
                                if($_GET["action"] == "add"){
                                    echo "<h2 class='section-heading'>Add Event</h2>";
                                    $addForm = "<div class='edit-add-form-container'>
                                                    <form id='user-edit-form' name='user-edit-form' action='./eventManagement.php?&action=add' method='POST'>
                                                        <div id='user-edit-labels'>
                                                            <label>ID</label>
                                                            <label>Name</label>
                                                            <label>Date Start</label>
                                                            <label>Date End</label>
                                                            <label>Number Allowed</label>
                                                            <label>Venue</label>   
                                                        </div>
                                                        <div id='user-edit-inputs'>
                                                            <input type='text' name='id' readonly='readonly' placeholder='Auto-increment'>
                                                            <input type='text' name='name'>
                                                            <input type='text' name='datestart' placeholder='yyyy-mm-dd hh:mm:ss'>
                                                            <input type='text' name='dateend' placeholder='yyyy-mm-dd hh:mm:ss'>
                                                            <input type='text' name='numberallowed'>
                                                            <input type='text' name='venue'>
                                                        </div><br/>
                                                        <input name='submit' id='submit-btn' type='submit' value='Submit'/>
                                                    </form>
                                                </div>";
                                    echo $addForm;
                                }
                                else{
                                    // REDIRECT: Action is something else
                                    redirect("admin");
                                }
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
                        // validate date!
                        // Validate that venue exists that user is trying to associate with!
                        $name = sanitizeString($_POST["name"]);
                        $datestart = sanitizeString($_POST["datestart"]);
                        $dateend = sanitizeString($_POST["dateend"]);
                        $numberAllowed = sanitizeString($_POST["numberallowed"]);  
                        $venue = sanitizeString($_POST["venue"]);    

                        // CHECK: if all inputs were given a value
                        if(isset($name) && isset($datestart) && isset($dateend) && isset($name) && isset($numberAllowed) && isset($venue)){
                            $venueExists = true; // Flag used to test if specific validation passed/failed

                            if(is_numeric($venue)){
                                $findVenue = $db->getVenue(intval($venue));
                                if($findVenue == 0 || $findVenue == -1 ){
                                    $venueExists = false;
                                }
                            }
                            else {
                                $venueExists = false;
                            }


                            // Only if the inputs passed SPECIFIC validations
                            if($venueExists){
                                // Perform ADD POST REQUEST Processing
                                $dataFields = array();
                                $dataFields["area"] = "event";
                                $dataFields["fields"]["name"] = array("type" => "s", "value" => $name);
                                $dataFields["fields"]["datestart"] = array("type" => "date", "value" => $datestart);
                                $dataFields["fields"]["dateend"] = array("type" => "date", "value" => $dateend);
                                $dataFields["fields"]["numberallowed"] = array("type" => "i", "value" => $numberAllowed);
                                $dataFields["fields"]["venue"] = array("type" => "i", "value" => $venue);
                                $dataFields["method"] = array(
                                    "add" => "addEvent"
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
                        
                       





                    }
                }// end if ACTION is present
            }// end if POST
        ?>
    </body>
</html>