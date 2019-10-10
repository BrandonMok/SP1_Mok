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
        <title>Account Management</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            // ADMINS only
            if(isset($_SESSION['userLoggedIn']) && isset($_SESSION['role'])){
                if($_SESSION['role'] == 'admin'){
                    if(isset($_GET['id']) && !empty($_GET['id'])) {
                        if(isset($_GET['action']) && !empty($_GET['action'])){
                            $id = $_GET['id'];          // In the URL to retrieve the id
                            $action = $_GET['action'];  // In the URL to retrieve the action

                            if($action == 'edit'){ 
                                echo "<h2 class='section-heading'>Edit</h2>";

                                $user = $db->getUser($_GET['id'])[0]; 
                                $originalValues = array(
                                    "name" => $user->getName(),
                                    "password" => $user->getPassword(),
                                    "role" => $user->getRole()
                                );
                                $originalValues = json_encode($originalValues);

                                $editForm = "<div id='account-form-container'>
                                                <form id='user-edit-form' name='user-edit-form' action='./accountManagement.php?id={$user->getIdAttendee()}&action=edit' method='POST'>
                                                    <div id='user-edit-labels'>
                                                        <label>ID</label><br/>
                                                        <label>Name</label><br/>
                                                        <label>Password</label><br/>
                                                        <label>Role</label><br/>                                                   
                                                    </div>
                                                    <div id='user-edit-inputs'>
                                                        <input type='text' name='id' value='{$user->getIdAttendee()}' readonly='readonly'><br/>
                                                        <input type='text' name='name' value='{$user->getName()}'><br/>
                                                        <input type='text' name='password'><br/>";
                
                                
                                // Don't let admin to change roles -> NEED to have a SUPERADMIN account
                                if($user->getRole() == "1"){
                                    $editForm .= "<input type='text' name='role' value='{$user->getRole()}' readonly='readonly'><br/></div>";
                                }
                                else{
                                    $editForm .= "<input type='text' name='role' value='{$user->getRole()}'><br/></div><br/>";
                                }
                
                                $editForm .= "<input type='hidden' name='originalValues' value='{$originalValues}'>";
                                $editForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                                
                                echo $editForm;
                            }
                            else if($action == "delete"){ 
                                // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                                if(isset($_GET['confirm']) && !empty($_GET['confirm'])){
                                    $decision = $_GET['confirm'];

                                    if($decision == 'yes'){
                                        $delete = $db->deleteUser($_GET["id"]);

                                        if($delete > 0){ // if rowcount wasn't 0 -> delete user
                                            header('Location: admin.php');
                                            exit;  
                                        }
                                        else{
                                            // ERROR w/the delete occured
                                            echo "<h2>Deleting selected user failed!</h2>";
                                        }
                                    }
                                    else{
                                        // user chose NO to deleting user
                                        header("Location: admin.php");
                                        exit;
                                    }
                                }

                                // Get user now to display delete information
                                $specificUser = $db->getUser($id)[0];

                                echo "<h2 class='section-heading'>Delete User</h2>";
                                $deleteUserTable = "<div class='admin-table-container'> 
                                                        <table class='admin-table'>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Name</th>
                                                                <th>Password</th>
                                                                <th>Role</th>
                                                            </tr>
                                                            <tr>
                                                                <td>{$specificUser->getIdAttendee()}</td>
                                                                <td>{$specificUser->getName()}</td>
                                                                <td>{$specificUser->getPassword()}</td>
                                                                <td>{$specificUser->getRole()}</td>
                                                            </tr>
                                                        </table>
                                                    </div>";

                                echo $deleteUserTable;


                                // Yes & no options to delete action
                                echo "<h2 class='section-heading'>Are you sure you want to delete the selected account?</h2><br/>";
                                $optionDiv = "<div id='confirm-delete-container' class='center-element'>
                                                    <a href='./accountManagement.php?id={$specificUser->getIdAttendee()}&action=delete&confirm=yes'>
                                                        <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                                                    </a>
                                                    <a href='./accountManagement.php?id={$specificUser->getIdAttendee()}&action=delete&confirm=no'>
                                                        <div class='delete-btn' id='deny-delete-btn'>No</div>
                                                    </a>
                                                </div>";
                                echo $optionDiv;
                            }
                        }
                    }
                    else if(isset($_GET['action']) && !empty($_GET['action'])) {
                        // If no ID was passed, but an action was -> new user button was clicked
                        if($_GET['action'] == "add"){
                            echo "<h2 class='section-heading'>Add User</h2>";

                            $addForm = "<div id='account-form-container'>
                                        <form id='user-edit-form' name='user-edit-form' action='./accountManagement.php?&action=add' method='POST'>
                                                <div id='user-edit-labels'>
                                                    <label>ID</label><br/>
                                                    <label>Name</label><br/>
                                                    <label>Password</label><br/>
                                                    <label>Role</label><br/>                                                   
                                                </div>
                                                <div id='user-edit-inputs'>
                                                    <input type='text' name='id' readonly='readonly' placeholder='Auto-increment'><br/>
                                                    <input type='text' name='name'><br/>
                                                    <input type='text' name='password'><br/>
                                                    <input type='text' name='role'><br/>
                                                </div><br/>";

                            $addForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                            
                            echo $addForm;
                        }
                        else {
                            // Redirect back to admin page if id wasn't set and action doesn't = add
                            header("Location: admin.php");
                            exit;
                        }
                    }
                }// end of admin role check
                else {
                   header("Location: events.php");
                   exit;
                }
            }// end of if loggedin
            else {
                header("Location: login.php");
                exit;
            }

            


            // CATCH THE POST REQUESTS
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){
                        $id = $_GET["id"];  // ID on POST isnt' editable, so just use from URL
                        $name = sanitizeString($_POST["name"]);
                        $password = sanitizeString($_POST["password"]);
                        $role = sanitizeString($_POST["role"]);
                        $originalValues = json_decode($_POST["originalValues"], true); 

                        // Hash password to pass into editPost function
                        if(!empty($password) && isset($password) && $password != ""){
                            $password = hash('sha256', $password);
                        }

                        // Switch to allow the right assignment
                        // Can pass in either the number or text equilavent
                        // Either way, sets it as a number from text input
                        switch($role){
                            case 1: 
                            case "admin":
                                $role = 1;
                                break;
                            case 2:
                            case "event_manager":
                            case "event manager":
                                $role = 2;
                                break;
                            case 3:
                            case "attendee":
                                $role = 3;
                                break;
                        }//end switch


                        // case when input isn't in range after switch
                        // If not in range or any of options, defaults to role of 0
                        if($role < 1 || $role > 3){
                            // ERROR - not in range
                            echo "<p class='form-error-text'>** Please enter a valid role!</p>";
                        }
                        else if(alphabetic($name) == 0){
                            // ERROR - name had numbers
                            echo "<p class='form-error-text'>** Please enter a valid name!</p>";
                        }
                        else {
                            $dataFields = array();
                            $dataFields["area"] = "user";
                            $dataFields["fields"] = array(
                                "id" => $id,
                                "name" => $name,
                                "password" => $password,
                                "role" => $role
                            );
                            $dataFields["method"] = array(
                                "update" => "updateUser"
                            );
                            $dataFields["originalValues"] = $originalValues;
                            editPost($dataFields);
                        }
                    }
                    else if($_GET["action"] == "add") {
                        // addFormPOST();
                        $name = sanitizeString($_POST["name"]);
                        $password = sanitizeString($_POST["password"]);
                        $role = sanitizeString($_POST["role"]);

                        $changesArray = array();
                    
                        if(!empty($name) && isset($name)){
                            $changesArray["name"] = $name;
                        }
                        if(!empty($password) && isset($password) && $password != ""){
                            $password = hash('sha256', $password);
                            $changesArray["password"] = $password;
                        }
                        if(!empty($role) && isset($role)){
                            switch($role){
                                case 1: 
                                case "admin":
                                    $changesArray["role"] = 1;
                                    break;
                                case 2:
                                case "event_manager":
                                case "event manager":
                                    $changesArray["role"] = 2;
                                    break;
                                case 3:
                                case "attendee":
                                    $changesArray["role"] = 3;
                                    break;
                            }
                        }

                        // If changes were made!
                        if(!empty($changesArray)){
                            $rowCount = $db->insertUser($changesArray["name"], $changesArray["password"], $changesArray["role"]);

                            if($rowCount > 0){
                                header("Location: admin.php");
                                exit;
                            }
                            else{
                                echo "<p class='form-error-text '>** Editing user failed!</p>";
                            }
                        }
                        else{
                            echo "<p class='form-error-text '>** No changes made to user!</p>";
                        }
                    }
                }
            }
        ?>
    </body>
</html>       