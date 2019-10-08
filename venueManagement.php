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
                            $venue = $db->getVenue($id)[0];   // 

                            if(isset($venue) && !empty($venue)){
                                if($action == "add"){


                                }
                                else if($action == "edit"){


                                }
                                else if($action == "delete"){


                                }
                            }
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
        ?>
    </body>
</html>