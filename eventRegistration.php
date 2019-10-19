<?php
    session_name("Mok_Project1");
    session_start();

    require_once("DB.class.php");
    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Event Registration</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            /**
             * Under events section of application, used to show available sessions for that event
             * and sign up for both
             */
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                /**
                 * CHECK: First check for all queries are set -> This is the processing for signing up 
                 */
                if(isset($_GET["id"]) && isset($_GET["session"]) && isset($_GET["action"])){
                    if($_GET["action"] == "signup"){
                        // CHECK: to see if user has already signed up for this event
                        // CHECK: to see if user has already signed up for this session
                        $signedUpForEvent = false;
                        $signedUpForSession = false;

                        $attendeeEvents = $db->getAllAttendeeEventsById($_SESSION["id"]);
                        if(count($attendeeEvents) > 0){
                            foreach($attendeeEvents as $aEvent){
                                if($aEvent->getEvent() == $_GET["id"]){
                                    $signedUpForEvent = true;
                                    break;
                                }
                            }

                            // CASE: User has already signed up for the event
                            // So just do checks on the attendee_session to make sure they aren't already signed up for a session
                            if($signedUpForEvent){
                                // Already have a attendee_event object created
                                // CHECK: attendee_session doesn't exist alread

                                $attendeeSessions = $db->getAllAttendeeSessionsById($_SESSION["id"]);
                                if(count($attendeeSessions) > 0){
                                    foreach($attendeeSessions as $aSession){
                                        if($aSession->getIdSession() == $_GET["session"]){
                                            $signedUpForSession = true;
                                            break;
                                        }
                                    }

                                    if($signedUpForSession){

                                    }

                                }
                                else {
                                    // User hasn't signed up for a session under this event
                                }
                            }
                            else {
                                // Make attendee_event object
                                // Make attendee_session object



                            }
                        }
                        else {
                            // User has not signed up for any events/sessions
                            // Just make the objects!
                            
                            // $attendeeEventData = array(
                            //     "event" => $_GET["id"],
                            //     "attendee" => $_SESSION["id"],
                            //     "paid" => 
                            // );
                            // $addAttendeeEvent = $db->addAttendeeEvent();


                        }
                    }
                    else {
                        // REDIRECT: Some other action was passed
                        redirect("events");
                    }
                }
                else if(isset($_GET["id"])){ 
                    // ID of event wanting to register for
                    $eventID = $_GET["id"];
                    $event = $db->getEvent($id);    // only need event's name for the section heading

                    echo "<p class='section-heading'>{$event->getName()}</p>";

                    $sessionsPerEvent = $db->getAllSessionsPerEvent($eventID);
                    if(count($sessionsPerEvent) > 0){
                        echo "<p class='section-heading'>Available Sessions</p>";

                        $eventSessionContainer = "<div id='event-session-container'>";
                        foreach($sessionsPerEvent as $session){
                            $eventSessionContainer .= "<div class='event-session-info'>
                                                            <p>{$session->getName()}</p>
                                                            <p>{$session->getNumberAllowed()}</p>
                                                            <p>{$session->getDate()}</p>
                                                            <a href='./eventRegistration.php?id={$id}&session={$session->getIdSession()}&action=signup'>
                                                                <div>Sign up</div>
                                                            </a>
                                                        </div>";
                        }
                        $eventSessionContainer .= "</div>";
                        echo $eventSessionContainer;
                    }
                    else {
                        // No sessions available for the event
                        echo "<p class='section-heading'>There are no sessions available for the selected event!</p>";
                    }
                }
                else {
                    // REDIRECT: ID of event wasn't passed
                    redirect("events");
                }
            }
            else {
                // REDIRECT: User not logged in
                redirect("login");
            }
        ?>
    </body>
</html>