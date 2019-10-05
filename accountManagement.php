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
                    if(isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['action']) && !empty($_GET['action'])) {
                        $id = $_GET['id'];          // In the URL to retrieve the id
                        $action = $_GET['action'];  // In the URL to retrieve the action

                        $specificUser = $db->getUser($id)[0]; // get USER

                        if(!empty($specificUser) && count($specificUser) > 0){
                                // Make a request for this specific person to display data
                            if($action == 'edit'){
                                // make a a prefilled form w/users values
                                // allow fields to be editable so user can make changes
                                // ** Make sure that values were changed before doing the update 

                                echo "<h2 class='section-heading'>Edit</h2>";

                                $editForm = "<form id='user-edit-form' name='user-edit-form' action='./accountManagement.php' method='POST'>
                                                <label>ID</label>
                                                <input type='text' name='id' placeholder='{$specificUser->getIdAttendee()}'>  
                                                <label>Name</label>
                                                <input type='text' name='name' placeholder='{$specificUser->getName()}'>
                                                <label>Password</label>
                                                <input type='text' name='password' placeholder='{$specificUser->getPassword()}'>
                                                <label>Role</label>";

                                
                                // Don't let admin to change roles -> NEED to have a SUPERADMIN account
                                if($specificUser->getRole() == "1"){
                                    $editForm .= "<input type='text' name='role' placeholder='{$specificUser->getRole()}' readonly='readonly'>";
                                }
                                else{
                                    $editForm .= "<input type='text' name='role' placeholder='{$specificUser->getRole()}'>";
                                }

                                $editForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form>";
                                                
                                echo $editForm;
                            }
                            else if($action == "delete"){




                            }
                        }
                        else {
                            // No user found
                            echo "<h2>ERROR: User now found!</h2>";
                        }
                    }
                    else{
                        // if user is logged in and an admin, but the ID and ACTION aren't in the URL, redirect back to admin page
                        header("Location: admin.php");
                        exit;
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

            


            // CATCH THE EDIT POST REQUEST
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                // Need to take care of roles 
                // Should allow both a number and string role to be entered
                // Catch it and on update, use the number



                
            }

            reusableFooter();
        ?>
    </body>
</html>       