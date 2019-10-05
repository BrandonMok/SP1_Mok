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

                        // Make a request for this specific person to display data
                        if($action == 'edit'){
                            // make a a prefilled form w/users values
                            // allow fields to be editable so user can make changes
                            // ** Make sure that values were changed before doing the update 

                            
                            // GET SPECIFIC USER FIRST

                            // $editForm = "<form id='user-edit-form' name='user-edit-form' action='./accountManagement.php' method='POST'>
                            //                 <label>ID</label>
                            //                 <input type='text'>  
                            //                 <label>Name</label>
                            //                 <input type='text'>
                            //                 <label>Password</label>
                            //                 <input type='text'>
                            //                 <label>Role</label>
                            //                 <input type='text'>
                            //             </form>";




                        }
                        else if($action == "delete"){




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

            reusableFooter();
        ?>
    </body>
</html>       