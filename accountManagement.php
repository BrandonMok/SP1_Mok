<?php
    session_name("Mok_Project1");
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');
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
                                    // ** Make sure that values were changed before doing the update 

                                    echo "<h2 class='section-heading'>Edit</h2>";

                                    $editForm = "<div id='account-form-container'>
                                                    <form id='user-edit-form' name='user-edit-form' action='./accountManagement.php' method='POST'>
                                                        <label>ID</label>
                                                        <input type='text' name='id' value='{$specificUser->getIdAttendee()}'><br/>
                                                        <label>Name</label>
                                                        <input type='text' name='name' value='{$specificUser->getName()}'><br/>
                                                        <label>Password</label>
                                                        <input type='text' name='password' value='{$specificUser->getPassword()}'><br/>
                                                        <label>Role</label>";

                                    
                                    // Don't let admin to change roles -> NEED to have a SUPERADMIN account
                                    if($specificUser->getRole() == "1"){
                                        $editForm .= "<input type='text' name='role' value='{$specificUser->getRole()}' readonly='readonly'><br/>";
                                    }
                                    else{
                                        $editForm .= "<input type='text' name='role' value='{$specificUser->getRole()}'><br/>";
                                    }

                                    $editForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                                    
                                    echo $editForm;
                                }
                                else if($action == "delete"){
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
                            
                        }
                        else {
                            // Redirect back to admin page if id wasn't set and action doesn't = add
                            header("Location: admin.php");
                            exit;
                        }
                    }
                } // end of admin role check
                else {
                   header("Location: events.php");
                   exit;
                }
            }
            else {
                header("Location: login.php");
                exit;
            }

            


            // // CATCH THE EDIT POST REQUEST
            // if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //     // Need to take care of roles 
            //     // Should allow both a number and string role to be entered
            //     // Catch it and on update, use the number

                    // have a catch to $_GET on the url for confirm = value (yes/no)
                    // then redirect back to admin.php


                
            // }

            reusableFooter();
        ?>
    </body>
</html>       