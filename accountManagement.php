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

                            $specificUser = $db->getUser($id)[0]; // get USER

                            if(!empty($specificUser) && count($specificUser) > 0){
                                if($action == 'edit'){ 

                                    echo "<h2 class='section-heading'>Edit</h2>";

                                    userManagementForm();
                                }
                                else if($action == "delete"){
                                    // if delete option was chosen, check for confirm variable in URL that's set when clicking Yes/No
                                    if(isset($_GET['confirm']) && !empty($_GET['confirm'])){
                                        $decision = $_GET['confirm'];

                                        if($decision == 'yes'){
                                            $delete = $db->deleteUser($specificUser->getIdAttendee());

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

                                    echo "<h2 class='section-heading'>Delete</h2>";

                                    $deleteUserTable = "<div id='user-table-container'> 
                                                            <table id='all-users-table'>
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
                            else {
                                // No user found
                                echo "<h2>ERROR: User now found!</h2>";
                            }
                        }
                    }
                    else if(isset($_GET['action']) && !empty($_GET['action'])) {
                        // If no ID was passed, but an action was -> new user button was clicked
                        if($_GET['action'] == "add"){
                            echo "<h2 class='section-heading'>Add</h2>";

                            userManagementForm();
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
                        editFormPOST();
                    }
                    else if($_GET["action"] == "add") {
                        addFormPOST();
                    }
                }
            }
        ?>
    </body>
</html>       