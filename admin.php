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
                    // ADD/EDIT/DELETE/VIEW USERS

                    echo "<p class='section-heading'>Admin</p>";

                    echo "<a href='./accountManagement.php?action=add'>
                                <div id='add-user-btn'>Add User</div>
                            </a>";

                    $allUsers = $db->getAllUsers();
                    if(count($allUsers) > 0){
                        // display table
                        $tableSTR = "<div id='user-table-container'>
                                        <table id='all-users-table'>
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

            // reusableFooter();
        ?>  
    </body>
</html>