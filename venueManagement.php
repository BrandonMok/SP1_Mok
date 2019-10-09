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
            if(isset($_SESSION['userLoggedIn']) && isset($_SESSION['role'])){
                if($_SESSION['role'] == 'admin'){
                    if(isset($_GET['id']) && !empty($_GET['id'])) {
                        if(isset($_GET['action']) && !empty($_GET['action'])){
                            $id = $_GET["id"];              // ID of venue passed in URL
                            $action = $_GET["action"];      // Action passed in URL

                            if($action == "edit"){
                                echo "<h2 class='section-heading'>Edit Venue</h2>";
                                $venue = $db->getVenue($id)[0];   // venue object

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
                                                    </div><br/>";

                                $editForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                echo $editForm;
                            }
                            else if($action == "delete"){
                                // Process confirm query from URL only when after clicking "yes" or "no" button
                                if(isset($_GET['confirm']) && !empty($_GET['confirm'])){
                                    $decision = $_GET['confirm'];

                                    if($decision == 'yes'){
                                        $delete = $db->deleteVenue($venue->getIdVenue());

                                        if($delete > 0){ // if rowcount wasn't 0 -> delete user
                                            header('Location: admin.php');
                                            exit;  
                                        }
                                        else{
                                            // ERROR w/the delete occured
                                            echo "<h2>Deleting selected venue failed!</h2>";
                                        }
                                    }
                                    else{
                                        // user chose NO to deleting user
                                        header("Location: admin.php");
                                        exit;
                                    }
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
                            }// elseif delete
                        }
                    }
                    else if(isset($_GET['action']) && !empty($_GET['action'])) {    // ADD option, no ID in URL
                         if($_GET['action'] == "add"){
                            echo "<h2 class='section-heading'>Add</h2>";


                            
                        }
                        else {
                            // Redirect back to admin page if id wasn't set and action doesn't = add
                            header("Location: admin.php");
                            exit;
                        }
                    }
                }
                else {
                    header("Location: events.php");
                    exit;
                }
            }
            else {
                header("Location: login.php");
                exit;
            }





             // CATCH THE POST REQUESTS
             if($_SERVER['REQUEST_METHOD'] == 'POST'){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){
                        $name = sanitizeString($_POST["name"]);
                        $capacity = sanitizeString($_POST["capacity"]);
                        $changesArray = array();

                        if(numbers($capacity)){  // check to see if number, if not don't proceed
                            $venue = $db->getVenue($_GET["id"])[0]; 
                    
                            if(!empty($name) && isset($name) && $name != $venue->getName()){
                                $changesArray["name"] = $name;
                            }
                            if(!empty($capacity) && isset($capacity) && $capacity != $venue->getCapacity()){
                                $changesArray["capacity"] = $capacity;
                            }

                        
                            // If changes were made!
                            if(!empty($changesArray)){
                                $changesArray["id"] = $venue->getIdVenue();
                                $rowCount = $db->updateVenue($changesArray);

                                if($rowCount > 0){
                                    header("Location: admin.php");
                                    exit;
                                }
                                else{
                                    echo "<p class='form-error-text '>** Editing venue failed!</p>";
                                }
                            }
                            else{
                                echo "<p class='form-error-text '>** No changes made to venue!</p>";
                            }
                        }
                        else {
                            // not a number 
                            echo "<p class='form-error-text'>** Invalid Capacity Value!</p>";
                        }
                    }
                    else if($_GET["action"] == "add") {
                        // addFormPOST();
                    }
                }
            }
        ?>
    </body>
</html>