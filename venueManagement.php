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
        <title>Venue Management</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            // ADMINS only to interact with venues
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager"){
                    // Distinguish which user role to allow specific functions
                    if($_SESSION["role"] == "admin"){
                        // Check if both ID and Action were passed = edit and delete processes can continue
                        if(managementEditDeleteCheck()){
                            if($_GET["action"] == "edit"){
                                // EDIT
                                $id = $_GET["id"];                  // ID of venue passed in URL
                                $venue = $db->getVenue($id)[0];   // venue object

                                // Store original values to compare to on POST
                                // Don't want to keep querying same object when doing post logic
                                $originalValues = array(
                                    "name" => $venue->getName(),
                                    "capacity" => $venue->getCapacity()
                                );
                                $originalValues = json_encode($originalValues);

                                // EDIT VENUE SECTION
                                echo "<h2 class='section-heading'>Edit Venue</h2>";

                                // Edit form
                                $editForm = "<div id='account-form-container'>
                                                <form id='user-edit-form' name='user-edit-form' action='./venueManagement.php?id={$venue->getIdVenue()}&action=edit' method='POST'>
                                                    <div id='user-edit-labels'>
                                                        <label>ID</label><br/>
                                                        <label>Name</label><br/>
                                                        <label>Capacity</label><br/>
                                                    </div>
                                                    <div id='user-edit-inputs'>
                                                        <input type='text' name='id' value='{$venue->getIdVenue()}' readonly='readonly'><br/>
                                                        <input type='text' name='name' value='{$venue->getName()}'><br/>
                                                        <input type='text' name='capacity' value='{$venue->getCapacity()}'><br/>
                                                    </div><br/>
                                                    <input type='hidden' name='originalValues' value='{$originalValues}'>";
                                                    
                                $editForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                echo $editForm;
                            }
                            else if($_GET["action"] == "delete"){
                                // DELETE
                                $id = $_GET["id"];              // ID of venue passed in URL

                                // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                                if(isset($_GET['confirm']) && !empty($_GET['confirm'])){
                                    $dataFields = array();
                                    $dataFields["area"] = "venue";
                                    $dataFields["fields"] = array(
                                        "id" => $id,
                                    );
                                    $dataFields["method"] = array(
                                        "delete" => "deleteVenue"
                                    );
                                    deleteAction($dataFields);
                                }

                                $venue = $db->getVenue($id)[0];   // venue object
                                
                                // VENUE SPECIFIC TABLE W/btns
                                echo "<h2 class='section-heading'>Delete Venue</h2>";
                                $deleteInfo = "<div class='admin-table-container'>
                                                <table class='admin-table'>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Capacity</th>
                                                    </tr>
                                                    <tr>
                                                        <td>{$venue->getIdVenue()}</td>
                                                        <td>{$venue->getName()}</td>
                                                        <td>{$venue->getCapacity()}</td>
                                                    </tr>
                                                </table>
                                            </div>";
                                echo $deleteInfo;

                                // Yes & no options to delete action
                                echo "<h2 class='section-heading'>Are you sure you want to delete the selected venue?</h2><br/>";
                                $optionDiv = "<div id='confirm-delete-container' class='center-element'>
                                                    <a href='./venueManagement.php?id={$venue->getIdVenue()}&action=delete&confirm=yes'>
                                                        <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                                                    </a>
                                                    <a href='./venueManagement.php?id={$venue->getIdVenue()}&action=delete&confirm=no'>
                                                        <div class='delete-btn' id='deny-delete-btn'>No</div>
                                                    </a>
                                                </div>";
                                echo $optionDiv;

                            }
                            else {
                                // action is something else
                                header("Location: admin.php");
                                exit;
                            }
                        }// end if edit/delete allowed
                        else if(managementAddCheck()){
                            // Add 
                            if($_GET["action"] == "add"){
                                echo "<h2 class='section-heading'>Add Venue</h2>";
                                $addForm = "<div id='account-form-container'>
                                                <form class='user-edit-form' name='add-form' action='./venueManagement.php?&action=add' method='POST'>
                                                    <div id='user-edit-labels'>
                                                        <label>ID</label><br/>
                                                        <label>Name</label><br/>
                                                        <label>Capacity</label><br/>
                                                    </div>
                                                    <div id='user-edit-inputs'>
                                                        <input type='text' name='id' readonly='readonly' placeholder='Auto-increment'><br/>
                                                        <input type='text' name='name'><br/>
                                                        <input type='text' name='capacity'><br/>
                                                    </div><br/>";
    
                                $addForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                echo $addForm;
                            }
                            else{
                                // REDIRECT: Action is something else
                                header("Location: admin.php");
                                exit;
                            }
                        }// end if action was the only set
                        else{
                             // REDIRECT: something else besides edit or delete was passed
                            header("Location: admin.php");
                            exit;
                        }
                    }
                    else if($_SESSION["role"] == "event_manager"){
                        // EVENT MANAGER



                    }
                }// end if admin or event manager
                else {
                    // REDIRECT: User is an attendee
                    header("Location: events.php");
                    exit;
                }
            }// end if logged in
            else {
                header("Location: login.php");
                exit;
            }


            

            /** -------------------- POST LOGIC --------------------*/
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){
                        $id = $_GET["id"];
                        $name = sanitizeString($_POST["name"]);
                        $capacity = sanitizeString($_POST["capacity"]);
                        $originalValues = json_decode($_POST["originalValues"], true); 

                        // Specific extra validation before passing into reusable edit post check
                        if(alphabetic($name) == 0 || numbers($name) == 1){
                            // ERROR: No letters and has numbers
                            echo "<p class='form-error-text'>** Please enter a valid name!</p>";
                        }
                        if($capacity == "" || empty($capacity)){ // if capacity is empty, simply set to 0
                            $capacity = 0;
                        }

                        // Perform EDIT POST REQUEST Processing
                        $dataFields = array();
                        $dataFields["area"] = "venue";
                        $dataFields["fields"] = array(
                            "id" => $id,
                            "name" => $name,
                            "capacity" => $capacity
                        );
                        $dataFields["method"] = array(
                            "update" => "updateVenue"
                        );
                        $dataFields["originalValues"] = $originalValues;
                        editPost($dataFields);
                    }
                    else if($_GET["action"] == "add") {
                        $name = sanitizeString($_POST["name"]);
                        $capacity = sanitizeString($_POST["capacity"]);

                        // Special case for CAPACITY (allowed to be null) -> will set to 0 if null was passed though
                        if($capacity == "" || empty($capacity)){
                            $capacity = 0;
                        }

                        // Perform ADD POST REQUEST Processing
                        $dataFields = array();
                        $dataFields["area"] = "venue";
                        $dataFields["fields"]["name"] = array("type" => "s", "value" => $name);
                        $dataFields["fields"]["capacity"] = array("type" => "i", "value" => $capacity);
                        $dataFields["method"] = array(
                            "add" => "addVenue"
                        );
                        addPost($dataFields);
                    }
                }
            }
        ?>
    </body>
</html>