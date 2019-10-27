<?php
    session_name("Mok_Project1");
    session_start();

    require_once("utilities.php");
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
                if($_SESSION["role"] == "admin"){
                    if(managementEditDeleteCheck()){
                        if($_GET["action"] == "edit"){
                            // EDIT
                            $id = $_GET["id"];                  // ID of venue passed in URL
                            $venue = $db->getVenue($id);        // venue object

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
                            $editForm = "<div class='edit-add-form-container' >
                                            <form id='user-edit-form' name='user-edit-form' action='./venueManagement.php?id={$venue->getIdVenue()}&action=edit' method='POST'>
                                                <div id='user-edit-labels'>
                                                    <label>ID</label>
                                                    <label>Name</label>
                                                    <label>Capacity</label>
                                                </div>
                                                <div id='user-edit-inputs'>
                                                    <input type='text' name='id' value='{$venue->getIdVenue()}' readonly='readonly'>
                                                    <input type='text' name='name' value='{$venue->getName()}'>
                                                    <input type='text' name='capacity' value='{$venue->getCapacity()}'>
                                                </div>
                                                <input type='hidden' name='originalValues' value='{$originalValues}'><br/>
                                                <input name='submit' id='submit-btn' type='submit' value='Submit'/>
                                            </form>
                                        </div>";
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
                                $delete = deleteAction($dataFields);
                                redirect("admin");
                            }

                            // Venue object
                            $venue = $db->getVenue($id);   
                            $deleteData = array();
                            $deleteData["area"] = "Venue";
                            $deleteData["th"] = array("ID", "Name", "Capacity");
                            $deleteData["td"] = array(
                                $venue->getIdVenue(),
                                $venue->getName(),
                                $venue->getCapacity()
                            );
                            $deleteData["choices"]["confirm"] = "./venueManagement.php?id={$venue->getIdVenue()}&action=delete&confirm=yes";
                            $deleteData["choices"]["cancel"] = "./venueManagement.php?id={$venue->getIdVenue()}&action=delete&confirm=no";
                            confirmDeleteHtml($deleteData);
                        }
                    }
                    else if(managementAddCheck()){
                        // Add 
                        if($_GET["action"] == "add"){
                            $data = array();
                            $data["area"] = "Venue";
                            $data["formAction"] = "./venueManagement.php?&action=add";
                            $data["labels"] = array("ID", "Name", "Capacity");
                            $data["input"] = array(
                                "id" => array(
                                    "name" => "id",
                                    "readonly" => "readonly",
                                    "placeholder" => "Auto-increment"
                                ),
                                "name" => array(
                                    "name" => "name"
                                ),
                                "capacity" => array(
                                    "name" => "capacity"
                                )
                            );
                            addActionHTML($data);
                        }
                        else{
                            // REDIRECT: Action is something else
                            redirect("admin");
                        }
                    }
                    else{
                        // REDIRECT: Something other action passed
                        redirect("admin");
                    }
                }// end if admin
                else {
                    // REDIRECT: User is not a admin
                    redirect("events");
                }
            }// end if logged in
            else {
                // REDIRECT: User not logged in
                redirect("login");
            }


            

            /** -------------------- POST LOGIC --------------------*/
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){
                        $id = $_GET["id"];
                        $name = sanitizeString($_POST["name"]);
                        $capacity = sanitizeString($_POST["capacity"]);
                        $originalValues = json_decode($_POST["originalValues"], true); 

                        // CHECK: Make sure entered fields aren't empty or not isset
                        $data = array($id, $name);
                        $validity = notIssetEmptyCheck($data);

                        if($capacity == "" || empty($capacity)){ // if capacity is empty, simply set to 0
                            $capacity = 0;
                        }
                        else if($validity){
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
                        else {
                            // ERROR: No values supplied and/or field missing a value
                            errorDisplay("Invalid: Inputs invalid and/or empty field!");
                        }
                    }
                    else if($_GET["action"] == "add") {
                        // Grab & sanitize inputs
                        $name = sanitizeString($_POST["name"]);
                        $capacity = sanitizeString($_POST["capacity"]); // allowed to be null!

                        // CHECK: Make sure entered fields aren't empty or not isset
                        $data = array($name);
                        $validity = notIssetEmptyCheck($data);

                        // CHECK: if all inputs were given a value
                        if($validity){
                            // Special case for CAPACITY (allowed to be null) -> will set to 0 if null was passed though
                            if($capacity == "" || empty($capacity)){
                                $capacity = 0;
                            }

                            // Perform ADD POST REQUEST Processing
                            $dataFields = array();
                            $dataFields["area"] = "venue";
                            $dataFields["fields"]["name"] = array("type" => "sn", "value" => $name);        // Venue names can have numbers
                            $dataFields["fields"]["capacity"] = array("type" => "i", "value" => $capacity);
                            $dataFields["method"] = array(
                                "add" => "addVenue"
                            );
                            addPost($dataFields);

                            // After making necessary objects, redirect
                            redirect("admin");
                        }
                        else{
                            // ERROR: No values supplied and/or field missing a value
                            errorDisplay("Invalid: Inputs invalid and/or empty field!");
                        }
                    }// end action ADD processing
                }// end if ACTION is present
            }// end if POST
        ?>
    </body>
</html>