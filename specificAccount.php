<?php
    session_name();
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SpecificAccount</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            // ADMINS only
            if(isset($_SESSION['userLoggedIn']) && isset($_SESSION['role'])){
                // ISSUE WITH SESSION VARIABLES HERE
                // KEEP GETTING REDIRECTED TO EVENTS

                if($_SESSION['role'] == 'admin'){
                    if(isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['action']) && !empty($_GET['action'])) {
                        $id = $_GET['id'];          // In the URL to retrieve the id
                        $action = $_GET['action'];  // In the URL to retrieve the action

                        // Make a request for this specific person to display data
                        if($action == 'edit'){

                        }
                        else if($action == "delete"){

                        }
                    }
                    else{
                        // if user is logged in and an admin, but the ID and ACTION aren't in the URL, redirect back to admin page
                        // header("Location: admin.php");
                        // exit;
                    }
                    
                }
                else {
                  //  header("Location: events.php");
                  //  exit;
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