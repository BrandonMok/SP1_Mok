<?php
    session_name("Mok_Project1");
    session_start();

    require_once("DB.class.php");
    require_once("utilities.php");
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
            if(isset($_SESSION['userLoggedIn']) && isset($_SESSION['role'])){
                if($_SESSION['role'] == 'admin'){
                    // ADMIN ONLY
                    echo "<p class='section-heading'>Admin</p>";

                    /* -------------------- Users -------------------- */
                    echo "<a href='./accountManagement.php?action=add'>
                                <div class='add-btn'>Add User</div>
                            </a>";

                    $allUsers = $db->getAllUsers();
                    if(count($allUsers) > 0){
                        // display table
                        $tableSTR = "<div class='admin-table-container'>
                                        <table class='admin-table'>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>";

                        foreach($allUsers as $v){
                            $tableSTR .= "<tr>
                                            <td>{$v->getIdAttendee()}</td>
                                            <td>{$v->getName()}</td>
                                            <td>{$v->getRole()}</td>
                                            <td><a href='./accountManagement.php?id={$v->getIdAttendee()}&action=edit'>Edit</a></td>";          
                                   
                            // Check if user is an admin -> Don't allow admin account to be deleted
                            if($v->getRole() == 1){
                                $tableSTR .= "<td></td></tr>";
                            }
                            else{
                                $tableSTR .= " <td><a href='./accountManagement.php?id={$v->getIdAttendee()}&action=delete'>Delete</a></td></tr>";
                            }
                        }            

                        $tableSTR .= "</table></div>";
                        echo $tableSTR;



                        /* -------------------- VENUES -------------------- */
                        // include_once("./classes/Venue.class.php");
                        // echo adminTables(array(
                        //     "th" => array("ID", "Name", "Capacity", "Edit", "Delete"),
                        //     "data" => $db->getAllVenues(),
                        //     "dataMethods" => get_class_methods("Venue"),
                        //     "editURL" => "./venueManagement.php?id={}&action=edit",
                        //     "deleteURL" => "./venueManagement.php?id={}&action=delete"
                        // ));

                        $allVenues = $db->getAllVenues();   // get all venues

                        echo "<p class='section-heading'>Venues</p>";
                        echo "<a href='./venueManagement.php?action=add'>
                                    <div class='add-btn'>Add Venue</div>
                                </a>";

                        $venueTable = "<div class='admin-table-container'>
                                        <table class='admin-table'>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Capacity</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>";
                                            

                        if(count($allVenues) > 0){
                            foreach($allVenues as $v){
                                $venueTable .= "<tr>    
                                                    <td>{$v->getIdVenue()}</td>
                                                    <td>{$v->getName()}</td>
                                                    <td>{$v->getCapacity()}</td>
                                                    <td><a href='./venueManagement.php?id={$v->getIdVenue()}&action=edit'>Edit</a></td>
                                                    <td><a href='./venueManagement.php?id={$v->getIdVenue()}&action=delete'>Delete</a></td>
                                                </tr>";
                            }
                            $venueTable .= "</table></div>";
                            echo $venueTable;
                        }
                        else{
                            echo "<h2>No venues available!</h2>";
                        }
                    }                    
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
        ?>  
    </body>
</html>