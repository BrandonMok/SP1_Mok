<?php
    session_name("Mok_Project1");
    session_start();
    
    require_once("DB.class.php");
    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Registrations</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            /**
             * Displays the events that a user signed up for!
             */
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                // EXTRA check to make sure user is allowed to access page
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager" || $_SESSION["role"] == "attendee"){
                    echo "<p class='section-heading'>Your Registrations</p>";

                    // Retrieve all registrations for THIS ATTENDEE 
                    $allRegistrations = $db->getAllAttendeeEvents($_SESSION["id"]);             // attendee_event
                    $allRegistrationSessions = $db->getAllAttendeeSessions($_SESSION["id"]);    // attendee_session

                    if(count($allRegistrations) > 0){
                        $registrationContainer = "<div id='event-container'>";

                        // Cycle through all their registrations
                        foreach($allRegistrations as $registration){
                            $registrationEventID = $registration->getEvent();           // ATTENDEE_EVENT's EventID its associated with
                            $registrationEvent = $db->getEvent($registrationEventID);   // EVENT OBJ
                            $venue = $db->getVenue($registrationEvent->getVenue())[0];     // VENUE for the event object

                            // Create each HTML event + session div
                            $registrationContainer .= "<div class='registration-events'>
                                                            <p class='event-headings'>{$registrationEvent->getName()}</p>
                                                            <p>{$registrationEvent->getDate()}</p>
                                                            <p>{$venue->getName()}</p>
                                                            <p>Number Allowed: {$registrationEvent->getNumberAllowed()}</p>
                                                            <div class='registration-buttons'>
                                                                <a href=''><div>Edit</div></a>
                                                                <a href=''><div>Delete</div></a>
                                                            </div>
                                                        </div>";

                            // Cycle through all the attendee's sessions they signed up for
                            // Display the associated sessions for that event that the attendee signed up for
                            if(count($allRegistrationSessions) > 0){
                                foreach($allRegistrationSessions as $session){
                                    $sessionObj = $db->getSession($session->getIdSession());
                                    if(count($sessionObj) > 0 && $sessionObj->getEvent() == $event->getIdEvent()){
                                        $registrationContainer .= "<hr/>
                                                                    <div class='registration-sessions'>
                                                                        <p>{$session->getName()}</p>
                                                                        <p>{$registrationEvent->getDateStart()} " - " {$registrationEvent->getDateEnd()}</p>
                                                                        <div class='registration-buttons'>
                                                                            <a href=''><div>Add</div></a>
                                                                            <a href=''><div>Edit</div></a>
                                                                            <a href=''><div>Delete</div></a>
                                                                        </div>
                                                                    </div>";
                                    }
                                }
                            }
                            $registrationContainer .= "</div>"; // Close the main div whether sessions was added or not to close div
                            
                            /**
                             * Allowing add/edit/delete here, so will probably redirect to the registrationManagementPage they see
                             * when they originally signed up - may de something where if they're already signed up to color out the buttons
                             */
                            
                        }
                        echo $registrationContainer;
                    }
                    else {
                        // CASE: No registrations for this user
                        echo "<h2 class='section-heading'>No events registered</h2>";
                    }

                }
            }
            else{
                // REDIRECT - User not logged in
                redirect("login");
            }
        ?>
    </body>
</html>