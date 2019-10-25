<?php
    session_name("Mok_Project1");
    session_start();
    
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
                    $allRegistrations = $db->getAllAttendeeEvents(0, $_SESSION["id"]);             // attendee_event
                    $allRegistrationSessions = $db->getAttendeeSessions(0, $_SESSION["id"]);    // attendee_session

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
                                                                    <p class='event-headings'>{$registrationEvent->getName()}</p>
                                                                    <p>{$registrationEvent->getDate()}</p>
                                                                    <p>{$venue->getName()}</p>
                                                                    <p>Number Allowed: {$registrationEvent->getNumberAllowed()}</p>
                                                                </div>
                                                                <div class='registration-btns'>
                                                                    <a href='./registrationsManagement.php?event={$registrationEvent->getIdEvent()}&action=delete'>
                                                                        <i class='fas fa-times'></i>
                                                                    </a><br/>
                                                                    <a href='./eventRegistration.php?id={$registrationEvent->getIdEvent()}'>
                                                                        <i class='fas fa-plus'></i>
                                                                    </a>
                                                                </div>
                                                            </div>";

                            // Cycle through all the attendee's sessions they signed up for
                            // Display the associated sessions for that event that the attendee signed up for
                            if(count($allRegistrationSessions) > 0){
                                foreach($allRegistrationSessions as $session){
                                    $sessionObj = $db->getAllSessions($session->getSession());  // actual session object
                                    if(count($sessionObj) > 0 && $sessionObj->getEvent() == $registrationEvent->getIdEvent()){
                                        $registrationContainer .= "<hr/>
                                                                    <div class='registration-sessions'>
                                                                        <div class='registration-session-info'>
                                                                            <p class='event-headings'>{$sessionObj->getName()}</p>
                                                                            <p>{$registrationEvent->getDate()}</p>
                                                                        </div>
                                                                        <div class='registration-btns'>
                                                                            <a href='./registrationsManagement.php?session={$sessionObj->getIdSession()}&action=delete'>
                                                                                <i class='fas fa-times'></i>
                                                                            </a><br/>
                                                                        </div>
                                                                    </div>";
                                    }
                                }
                                $registrationContainer .= "</div>"; // close registration div
                            }
                            else {
                                $registrationContainer .= "</div>"; // close registration div
                            }
                        }// end foreach
                        $registrationContainer .= "</div>"; // close container div
                        echo $registrationContainer;
                    }
                    else {
                        // CASE: No registrations for this user
                        echo "<h2 class='section-heading'>No registered events!</h2>";
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