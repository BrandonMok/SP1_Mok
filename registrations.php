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
                    $allRegistrations = $db->getAllAttendeeEventsById($_SESSION["id"]);             // attendee_event
                    $allRegistrationSessions = $db->getAllAttendeeSessionsById($_SESSION["id"]);    // attendee_session

                    if(count($allRegistrations) > 0){
                        $registrationContainer = "<div id='registration-container'>";

                        // Cycle through all their registrations
                        foreach($allRegistrations as $registration){
                            $registrationEventID = $registration->getEvent();           // ATTENDEE_EVENT's EventID its associated with
                            $registrationEvent = $db->getEvent($registrationEventID);   // EVENT OBJ
                            $venue = $db->getVenue($registrationEvent->getVenue());     // VENUE for the event object


                            // Create each HTML event + session div
                            $registrationContainer .= "<div class='registration'>
                                                            <div class='registration-events'>
                                                                <div class='registration-event-info'>
                                                                    <p>{$registrationEvent->getName()}</p>
                                                                    <p>{$registrationEvent->getDate()}</p>
                                                                    <p>{$venue->getName()}</p>
                                                                    <p>Number Allowed: {$registrationEvent->getNumberAllowed()}</p>
                                                                </div>
                                                                <div class='registration-btns'>
                                                                    <a href=''><i class='fas fa-times'></i></a>
                                                                    <a href=''><div class='registration-edit-btn'>Edit</div></a>
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
                                                                        <div class='registration-session-info'>
                                                                            <p>{$session->getName()}</p>
                                                                            <p>{$registrationEvent->getDate()}</p>
                                                                        </div>
                                                                        <div class='registration-btns'>
                                                                            <a href=''><i class='fas fa-times'></i></a>
                                                                            <a href=''><div class='registration-btn'>Add</div></a>
                                                                            <a href=''><div class='registration-btn'>Edit</div></a>
                                                                        </div>
                                                                    </div>
                                                            </div>";
                                    }
                                }
                            }
                            else {
                                $registrationContainer .= "</div>"; // Close the main div whether sessions was added or not to close div
                            }
                            
                            /**
                             * Allowing add/edit/delete here, so will probably redirect to the registrationManagementPage they see
                             * when they originally signed up - may de something where if they're already signed up to color out the buttons
                             */
                            
                        }
                        $registrationContainer .= "</div>";

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