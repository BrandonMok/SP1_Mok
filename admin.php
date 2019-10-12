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
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                if($_SESSION["role"] == "admin"){
                    // ADMIN ONLY

                    /* -------------------- Users -------------------- */
                    echo "<p class='section-heading'>Admin</p>";
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
                            if($v->getRole() == 1 && $v->getIdAttendee() == 1){
                                $tableSTR .= "<td></td></tr>";
                            }
                            else{
                                $tableSTR .= " <td><a href='./accountManagement.php?id={$v->getIdAttendee()}&action=delete'>Delete</a></td></tr>";
                            }
                        }// end foreach            

                        $tableSTR .= "</table></div>";
                        echo $tableSTR;
                    }// end of count users


                    /* -------------------- VENUES -------------------- */
                    // include_once("./classes/Venue.class.php");
                    // $venueTable = array();
                    // $venueTable["class"] = "Venue";
                    // $venutTable["area"] = "Venues";
                    // $venueTable["data"] = $db->getAllVenues();
                    // $venueTable["th"] = array("ID", "Name", "Capacity", "Edit", "Delete");
                    // $venueTable["dataMethods"] = array("getIdVenue", "getName", "getCapacity");
                    // $venueTable["addURL"] = "./venueManagement.php?action=add";
                    // $venueTable["editURL"] = "./venueManagement.php?id={}&action=edit";      // DO SOMETHING WITH SETTING ID // MAYBE PRESET THAN DO A REPLACE with the ID
                    // $venueTable["deleteURL"] = "./venueManagement.php?id={}&action=delete";
                    // adminTables($venueTable);

                    $allVenues = $db->getAllVenues();   // get all venues
                    if(count($allVenues) > 0){
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
                    }// end venues
                        
                    

                    /* -------------------- Events -------------------- */
                    $allEvents = $db->getAllEvents();  
                    if(count($allEvents) > 0){
                        echo "<p class='section-heading'>Events</p>";
                        echo "<a href='./eventManagement.php?action=add'>
                                    <div class='add-btn'>Add Event</div>
                                </a>";

                        $eventTable = "<div class='admin-table-container'>
                                        <table class='admin-table'>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Date Start</th>
                                                <th>Date End</th>
                                                <th>Number Allowed</th>
                                                <th>Venue</th>   
                                                <th>Edit</th>
                                                <th>Delete</th>               
                                            </tr>";

                        // Cycle through all events to display
                        foreach($allEvents as $v){
                            $eventTable .= "<tr>    
                                                <td>{$v->getIdEvent()}</td>
                                                <td>{$v->getName()}</td>
                                                <td>{$v->getDateStart()}</td>
                                                <td>{$v->getDateEnd()}</td>
                                                <td>{$v->getNumberAllowed()}</td>";

                            // If venues, match the venue names to event venue ID
                            if(count($allVenues) > 0){
                                $eventWithVenueName = "";
                                foreach($allVenues as $venue){
                                    // Find event whose EventID equals VenueID
                                    if($venue->getIdVenue() == $v->getIdEvent()){
                                        $eventWithVenueName = "<td>{$venue->getName()}</td>";   // if found, use venue name for display
                                        break;
                                    }
                                    else {
                                        $eventWithVenueName = "<td>TBD</td>";   // otherwise just use a default TBD
                                    }
                                }

                                $eventTable .= $eventWithVenueName; // take found name or TBD to table
                            }
                            else {
                                $eventTable .= "<td>TBD</td>";  // display default TBD if no venues
                            }

                            // Finish creation of rest of event table
                            $eventTable .= "<td><a href='./eventManagement.php?id={$v->getIdEvent()}&action=edit'>Edit</a></td>
                                            <td><a href='./eventManagement.php?id={$v->getIdEvent()}&action=delete'>Delete</a></td>
                                        </tr>";
                        }// end foreach
                        $eventTable .= "</table></div>";
                        echo $eventTable;
                    }
                    else{
                        echo "<h2>No events available!</h2>";
                    }



                }// end if admin
                else if($_SESSION["role"] == "event_manager"){
                    // EVENT MANAGER ONLY





                }
                else{
                    // User is an attendee - redirect
                    header("Location: events.php");
                    exit;
                }
            }// end if logged in
            else{
                // REDIRECT - User not logged in
                header("Location: login.php");
                exit;
            }
        ?>  
    </body>
</html>