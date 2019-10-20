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
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager"){
                    $userRole = $_SESSION["role"]; // store user's role to determine which sections are available

                    /* -------------------- Users -------------------- */
                    if($userRole == "admin"){
                        echo "<p class='section-heading'>Admin</p>";

                        // Call reusable function to create the add btn
                        adminAddBtns(array(
                            "url" => "./accountManagement.php?action=add",
                            "area" => "User"
                        ));
                            

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
                        else{
                            echo "<h2 class='center-element'>No users found!</h2>";
                        }
                    }// end if admin access
                    



                    /* -------------------- VENUES -------------------- */
                    if($userRole == "admin"){
                        echo "<p class='section-heading'>Venues</p>";

                        // Call reusable function to create the add btn
                        adminAddBtns(array(
                            "url" => "./venueManagement.php?action=add",
                            "area" => "Venue"
                        ));

                        $allVenues = $db->getAllVenues();   // get all venues
                        if(count($allVenues) > 0){
                            // Build venue table
                            $venueTable = "<div class='admin-table-container'>
                                            <table class='admin-table'>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Capacity</th>
                                                    <th>Edit</th>
                                                    <th>Delete</th>
                                                </tr>";

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
                        }// end if count venues 
                        else {
                            echo "<h2 class='center-element'>No venues available!</h2>";
                        }
                    }
                    
                        
                    

                    /* -------------------- Events -------------------- */
                    echo "<p class='section-heading'>Events</p>";
                    // Call reusable function to create the add btn
                    adminAddBtns(array(
                        "url" => "./eventManagement.php?action=add",
                        "area" => "Event"
                    ));

                    // Determine which info is shown! 
                    // Admins - EVERYTHING
                    // EventManager - only THEIRS
                    if($userRole == "admin"){
                        $allEvents = $db->getAllEvents();  
                    }
                    else if($userRole == "event_manager"){
                        // Retrieve events that this event manager OWNS!
                        $allEvents = array();
                        $allManagerEventObjs = $db->getAllManagerEventsOBJ($_SESSION["id"]);

                        // Foreach manager_event object, cycle through and get the actual event and store in array
                        foreach($allManagerEventObjs as $v){
                            $allEvents[] = $db->getEvent($v->getEvent());
                        }
                    }

                    if(count($allEvents) > 0){
                        // Build event table
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

                            // If event_manager, need to get venues since it wouldn't have been retrieved beforehand through venues
                            if($userRole == "event_manager"){
                                $allVenues = $db->getAllVenues();   // get all venues
                            }
                            if(count($allVenues) > 0){
                                $eventWithVenueName = "";
                                foreach($allVenues as $venue){
                                    // Find event whose EventID equals VenueID
                                    if($venue->getIdVenue() == $v->getVenue()){
                                        $eventWithVenueName = "<td>{$venue->getName()}</td>";   // if found, use venue name for display
                                        break;
                                    }
                                    else {
                                        $eventWithVenueName = "<td>TBD</td>";   // otherwise just use a default TBD
                                    }
                                }// end foreach
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
                        echo "<h2 class='center-element'>No events available!</h2>";
                    }




                    /* -------------------- Sessions -------------------- */
                    echo "<p class='section-heading'>Sessions</p>";
                    // Call reusable function to create the add btn
                    adminAddBtns(array(
                        "url" => "./sessionManagement.php?action=add",
                        "area" => "Session"
                    ));

                    // Determine which role to know which data to use
                    if($userRole == "admin"){
                        $allSessions = $db->getAllSessions();  
                    }
                    else if($userRole == "event_manager"){
                        // Retrieve sessions that this event manager OWNS!
                        if(count($allEvents) > 0){
                            // If the event manager has events, then grab sessions for that event
                            // $allEVents contains eventObjects - use to find session object
                            $allSessions = array();
                            foreach($allEvents as $event){
                                $temp =  $db->getAllSessionsPerEvent($event->getIdEvent());
                                if(count($temp) > 0){
                                    $allSessions[] = $temp[0];
                                }
                            }
                        }
                        else {
                            $allSessions = -1;
                        }
                    }

                    if(count($allSessions) > 0){
                        // Build event table
                        $sessionTable = "<div class='admin-table-container'>
                                        <table class='admin-table'>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Number Allowed</th>
                                                <th>Event</th>   
                                                <th>Start Date</th>
                                                <th>End Date</th>  
                                                <th>Edit</th>    
                                                <th>Delete</th>             
                                            </tr>";
                        
                        foreach($allSessions as $session){
                            $sessionTable .= "<tr>
                                                <td>{$session->getIdSession()}</td>
                                                <td>{$session->getName()}</td>
                                                <td>{$session->getNumberAllowed()}</td>";
                            if(count($allEvents) > 0){
                                $sessionWithEventName = "";
                                foreach($allEvents as $event){
                                    if($event->getIdEvent() == $session->getEvent()){
                                        $sessionWithEventName = "<td>{$event->getName()}</td>";
                                        break;
                                    }
                                    else {
                                        $sessionWithEventName = "<td>TBD</td>";
                                    }
                                }// end foreach
                                $sessionTable .= $sessionWithEventName;
                            }
                            else {
                                $sessionTable .= "<td>TBD</td>";
                            }
                            // Finish creation of rest of event table
                            $sessionTable .= "<td>{$session->getStartDate()}</td>
                                                <td>{$session->getEndDate()}</td>
                                                <td><a href='./sessionManagement.php?id={$session->getIdSession()}&action=edit'>Edit</a></td>
                                                <td><a href='./sessionManagement.php?id={$session->getIdSession()}&action=delete'>Delete</a></td>
                                            </tr>";
                        }// end foreach session
                        $sessionTable .= "</table></div>";
                        echo $sessionTable;
                    }
                    else{
                        echo "<h2 class='center-element'>No sessions available!</h2>";
                    }



                    /** -------------------- Attendees for events --------------------*/
                    /** Retrieve all the attendees for an event */
                    echo "<p class='section-heading'>Attendees</p>";

                    // Call reusable function to create the add btn
                    adminAddBtns(array(
                        "url" => "./attendeeEventManagement.php?action=add",
                        "area" => "attendee"
                    ));

                    if($userRole == "admin"){
                        $attendeeEvents = $db->getAllAttendeeEvents();
                    }
                    else if($userRole == "event_manager"){
                        $attendeeEvents = $db->getAllAttendeeEventsById($_SESSION["role"]);
                    }

                    if(count($attendeeEvents) > 0){
                        $attendeeEventTable = "<div class='admin-table-container'>
                                                    <table class='admin-table'>
                                                        <tr>
                                                            <th>Event</th>
                                                            <th>Attendee</th>
                                                            <th>Paid</th>
                                                            <th>Edit</th>
                                                            <th>Delete</th>
                                                        </tr>";
                        foreach($attendeeEvents as $aEvent){
                            // Used to get name of event - for convenience 
                            $eventName = $db->getEvent($aEvent->getEvent());
                            $eventName = $eventName->getName();
                                                    
                            // Get the attendee object to use their name to display alongside their ID - for convenience
                            $attendee = $db->getUser($aEvent->getAttendee());
                            $attendee = $attendee->getName();

                            $attendeeEventTable .= "<tr>
                                                        <td>{$aEvent->getEvent()} - {$eventName}</td>";

                            $attendeeEventTable .=  "   <td>{$aEvent->getAttendee()} - {$attendee}</td>
                                                        <td>{$aEvent->getPaid()}</td>
                                                        <td><a href='./attendeeEventManagement.php?id={$aEvent->getAttendee()}&event={$aEvent->getEvent()}&action=edit'>Edit</a></td>
                                                        <td><a href='./attendeeEventManagement.php?id={$aEvent->getAttendee()}&event={$aEvent->getEvent()}&action=delete'>Delete</a></td>
                                                    </tr>";
                        }
                        $attendeeEventTable .= "</table></div>";
                        echo $attendeeEventTable;
                    }
                    else {
                        // No attendees attending events or sessions
                        echo "<p class='section-heading'>No attendees are registered for events!</p>";
                    }
                }// end if admin
                else{
                    // REDIRECT: User is an attendee
                    redirect("events");
                }
            }// end if logged in
            else{
                // REDIRECT - User not logged in
                redirect("login");
            }
        ?>  
    </body>
</html>