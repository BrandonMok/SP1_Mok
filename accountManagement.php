<?php
    session_name("Mok_Project1");
    session_start();

    require_once("utilities.php");
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
                    // Check if both ID and Action were passed = edit and delete processes can continue
                    if(managementEditDeleteCheck()){
                        if($_GET["action"] == "edit"){
                            // EDIT
                            $id = $_GET['id'];          // In the URL to retrieve the id
                            $user = $db->getUser($_GET['id']); 

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
                                                    <input type='password' name='password' placeholder='******'>";   // Password exists, not showing for privacy + security 
            
                            
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
                            $id = $_GET["id"]; // ID of account from URL

                            // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                            if(isset($_GET["confirm"]) && !empty($_GET["confirm"])){
                                // Use reusable DELETE action
                                $dataFields = array();
                                $dataFields["area"] = "user";
                                $dataFields["fields"] = array(
                                    "id" => $id,
                                );
                                $dataFields["method"] = array(
                                    "delete" => "deleteAllUserInfo"
                                );
                                $delete = deleteAction($dataFields);

                                redirect("admin");
                            }

                            // User object
                            $specificUser = $db->getUser($id);
                            $deleteData = array();
                            $deleteData["area"] = "User";
                            $deleteData["th"] = array("ID", "Name", "Password", "Role");
                            $deleteData["td"] = array(
                                $specificUser->getIdAttendee(),
                                $specificUser->getName(),
                                $specificUser->getPassword(),
                                $specificUser->getRole()
                            );
                            $deleteData["choices"]["confirm"] = "./accountManagement.php?id={$specificUser->getIdAttendee()}&action=delete&confirm=yes";
                            $deleteData["choices"]["cancel"] = "./accountManagement.php?id={$specificUser->getIdAttendee()}&action=delete&confirm=no";
                            confirmDeleteHtml($deleteData);
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
                        $password = sanitizeString($_POST["password"]);
                        $role = sanitizeString($_POST["role"]);
                        $originalValues = json_decode($_POST["originalValues"], true); 

                        // Check entered role for new user to have
                        $enteredRole = roleCheck($role);

                        // CHECK: Make sure entered fields aren't empty or not isset
                        $data = array($id, $name);
                        $validity = notIssetEmptyCheck($data);

                        // case when input isn't in range after switch
                        if($enteredRole == -1 || $enteredRole <= 0){
                            errorDisplay("Invalid: Entered role isn't an available option");
                        }
                        else if($validity){
                            // Special case: don't have to change password (empty on input) on edit
                            if(empty($password)){
                                $password = $dataFields["originalValues"]["password"];  
                            }

                            // Perform EDIT POST REQUEST Proccessing
                            $dataFields = array();
                            $dataFields["area"] = "user";
                            $dataFields["fields"] = array(
                                "id" => $id,
                                "name" => $name,
                                "password" => hash("sha256", $password), 
                                "role" => $role
                            );
                            $dataFields["method"] = array(
                                "update" => "updateUser"
                            );
                            $dataFields["originalValues"] = $originalValues;
                            editPost($dataFields);
                        }
                        else {
                            // ERROR: No values supplied and/or field missing a value
                            errorDisplay("Invalid: Inputs invalid and/or empty field(s)!");
                        }
                    }// end if EDIT
                    else if($_GET["action"] == "add") {
                        $name = sanitizeString($_POST["name"]);
                        $password = sanitizeString($_POST["password"]);
                        $role = sanitizeString($_POST["role"]);     // Role is ALLOWED to be null

                        // CHECK: Make sure entered fields aren't empty or not isset
                        $data = array($name, $password);
                        $validity = notIssetEmptyCheck($data);

                        // CHECK: if all inputs were given a value
                        if($validity){
                            // Check that trying to edit role is a valid role!
                            $enteredRole = roleCheck($role);
                            if($enteredRole == -1 || $enteredRole <= 0){
                                errorDisplay("Invalid: Entered role isn't an available option");
                            }
                            else {
                                 // Use reusable addPOST processing function
                                $dataFields = array();
                                $dataFields["area"] = "user";
                                $dataFields["fields"]["name"] = array("type" => "s", "value" => $name);
                                $dataFields["fields"]["password"] = array("type" => "sn", "value" => hash("sha256",$password));    // passwords when hashed have letters + numbers
                                $dataFields["fields"]["role"] = array("type" => "i", "value" => $enteredRole);
                                $dataFields["method"] = array(
                                    "add" => "insertUser"
                                );
                                addPost($dataFields);

                                // After making necessary objects, redirect
                                redirect("admin");
                            }
                        }
                        else {
                            // ERROR: No values supplied and/or field missing a value
                            errorDisplay("Invalid: Inputs invalid and/or empty field(s)!");
                        }
                    }// end if ADD
                }// end if ACTION
            }// end if POST
        ?>
    </body>
</html>       