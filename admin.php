<?php
    session_name("Mok_Project1");
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Admin</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            // Admins and event managers only!
            // BUT Admins do everything
            // Event managers only specific things, so check roles
            if(isset($_SESSION['userLoggedIn']) && isset($_SESSION['role'])){
                if($_SESSION['role'] == 'admin'){
                    // ADMIN ONLY
                }
                else if($_SESSION['role'] == 'event_manager'){
                    // EVENT MANAGER ONLY
                }
            }
            else{
                // REDIRECT - User not logged in
                header('Location: login.php');
                exit;
            }

            reusableFooter();
        ?>  
    </body>
</html>