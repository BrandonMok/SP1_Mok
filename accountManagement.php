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
                if($_SESSION["role"] == "admin"){
                    // ADMIN
                    // Check if both ID and Action were passed = edit and delete processes can continue
                    if(managementEditDeleteCheck()){
                        if($_GET["action"] == "edit"){
                            // EDIT
                            $id = $_GET['id'];          // In the URL to retrieve the id
                            $user = $db->getUser($_GET['id'])[0]; 

                            echo "<h2 class='section-heading'>Edit</h2>";

                            // Store original values to compare to on POST
                            // Don't want to keep querying same object when doing post logic
                            $originalValues = array(
                                "name" => $user->getName(),
                                "password" => $user->getPassword(),
                                "role" => $user->getRole()
                            );
                            $originalValues = json_encode($originalValues);

                            $editForm = "<div class='edit-add-form-container' >
                                            <form id='user-edit-form' name='user-edit-form' action='./accountManagement.php?id={$user->getIdAttendee()}&action=edit' method='POST'>
                                                <div id='user-edit-labels'>
                                                    <label>ID</label>
                                                    <label>Name</label>
                                                    <label>Password</label>
                                                    <label>Role</label>                                                   
                                                </div>
                                                <div id='user-edit-inputs'>
                                                    <input type='text' name='id' value='{$user->getIdAttendee()}' readonly='readonly'>
                                                    <input type='text' name='name' value='{$user->getName()}'>
                                                    <input type='text' name='password'>";
            
                            
                            // Don't let superadmin to change roles -> NEED to have one SUPERADMIN account
                            if($user->getRole() == "1" && $user->getIdAttendee() == 1){
                                $editForm .= "<input type='text' name='role' value='{$user->getRole()}' readonly='readonly'></div>";
                            }
                            else{
                                $editForm .= "<input type='text' name='role' value='{$user->getRole()}'></div>";
                            }
            
                            $editForm .= "<input type='hidden' name='originalValues' value='{$originalValues}'><br/>";
                            $editForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                            echo $editForm;
                        }
                        else if($_GET["action"] == "delete"){
                            // DELETE
                            $id = $_GET['id']; // ID of account from URL

                            // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                            if(isset($_GET["confirm"]) && !empty($_GET["confirm"])){
                                // Use reusable DELETE action
                                $dataFields = array();
                                $dataFields["area"] = "user";
                                $dataFields["fields"] = array(
                                    "id" => $id,
                                );
                                $dataFields["method"] = array(
                                    "delete" => "deleteUser"
                                );
                                deleteAction($dataFields);
                            }

                            // Get user now to display delete information
                            $specificUser = $db->getUser($id)[0];

                            // DELETE USER HTML
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
                        else {
                            // REDIRECT: something else besides edit or delete was passed
                            redirect("admin");
                        }
                    }// end if edit/delete allowed
                    else if(managementAddCheck()){
                        if($_GET["action"] == "add"){
                            $data = array();
                            $data["area"] = "User";
                            $data["formAction"] = "./accountManagement.php?&action=add";
                            $data["labels"] = array("ID", "Name", "Password", "Role");
                            $data["input"] = array(
                                "id" => array(
                                    "name" => "id",
                                    "readonly" => "readonly",
                                    "placeholder" => "Auto-increment"
                                ),
                                "name" => array(
                                    "name" => "name"
                                ),
                                "password" => array(
                                    "name" => "password"
                                ),
                                "role" => array(
                                    "name" => "role"
                                )
                            );
                            addActionHTML($data);
                        }// end if action was the only set
                        else{
                            // REDIRECT: Action is something else
                            redirect("admin");
                        }
                    }
                    else{
                        // REDIRECT: something else besides edit or delete was passed
                        redirect("admin");
                    }
                }// end if admin
                else {
                    // REDIRECT: User isn't an admin
                    redirect("events");
                }
            }// end of if loggedin
            else {
                // REDIRECT: User not logged in
                redirect("login");
            }

            


             /** -------------------- POST LOGIC --------------------*/
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                if(isset($_GET["action"]) && !empty($_GET["action"])){
                    if($_GET["action"] == "edit"){
                        $id = $_GET["id"];  // ID on POST isnt' editable, so just use from URL
                        $name = sanitizeString($_POST["name"]);
                        $password = hash("sha256", sanitizeString($_POST["password"]));
                        $role = sanitizeString($_POST["role"]);
                        $originalValues = json_decode($_POST["originalValues"], true); 

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
                            // Perform EDIT POST REQUEST Proccessing
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
                    }// end if EDIT
                    else if($_GET["action"] == "add") {
                        $name = sanitizeString($_POST["name"]);
                        $password = hash("sha256", sanitizeString($_POST["password"]));
                        $role = sanitizeString($_POST["role"]);     // Role is ALLOWED to be null

                        // CHECK: if all inputs were given a value
                        if(isset($name) && isset($password)){
                            // Go through role assignment for right role value
                            // If role is null (not supplied), then default to attendee
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
                                default: 
                                    $role = 3;
                                    break;
                            }
    
                            // Use reusable addPOST processing function
                            $dataFields = array();
                            $dataFields["area"] = "user";
                            $dataFields["fields"]["name"] = array("type" => "s", "value" => $name);
                            $dataFields["fields"]["password"] = array("type" => "sn", "value" => $password);    // passwords when hashed have letters + numbers
                            $dataFields["fields"]["role"] = array("type" => "i", "value" => $role);
                            $dataFields["method"] = array(
                                "add" => "insertUser"
                            );
                            addPost($dataFields);
                        }
                        else {
                            // ERROR: No values supplied and/or field missing a value
                            echo "<p class='form-error-text'>** Invalid inputs!</p>";
                        }
                    }// end if ADD
                }// end if ACTION
            }// end if POST
        ?>
    </body>
</html>       