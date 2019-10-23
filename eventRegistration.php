<?php
    session_name("Mok_Project1");
    session_start();

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
             * Under events section of application, used to show available sessions for that event and sign up for both
             */
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                /**
                 * CHECK: First check for all queries are set -> This is the processing for signing up 
                 */
                if(isset($_GET["event"])&& isset($_GET["session"]) && isset($_GET["action"])){
                    if($_GET["action"] == "signup" || $_GET["action"] == "signuppay"){
                        // CHECK: to see if user has already signed up for this event 
                        $attendeeEvents = $db->getAllAttendeeEvents($_GET["event"], $_SESSION["id"]);

                        if(isset($attendeeEvents) && !empty($attendeeEvents)){
                            // CASE: attendee_event object ALREADY EXISTS

                            // CHECK: attendee_session doesn't exist yet
                            $attendeeSessions = $db->getAttendeeSessionBySessionAttendee($_GET["session"], $_SESSION["id"]);
                            if(isset($attendeeSessions) && !empty($attendeeSessions)){
                                // Session already signed up for
                                echo "<p class='form-error-text'>** You've already registered for this session!</p>";
                            }
                            else {
                                // CASE: User signed up for event already, but not the session
                                $attendeeSessionData = array(
                                    "session" => $_GET["session"],
                                    "attendee" => $_SESSION["id"]
                                );
                                $addAttendeeSession = $db->addAttendeeSession($attendeeSessionData);

                                redirect("events");
                            }
                        }
                        else {
                            // CASE: User hasn't signed up for any evenyts; therefore no sessions signed up either
                            $paid = 0;
                            switch($_GET["action"]){
                                case "signup":
                                    $paid = 0;
                                    break;
                                case "signuppay":
                                    $paid = 1;
                                    break;
                            }

                            // Make attendee_event object
                            $attendeeEventData = array(
                                "event" => $_GET["event"],
                                "attendee" => $_SESSION["id"],
                                "paid" => $paid
                            );
                            $addAttendeeEvent = $db->addAttendeeEvent($attendeeEventData);

                            if(count($addAttendeeEvent) > 0){
                                $attendeeSessionData = array(
                                    "session" => $_GET["session"],
                                    "attendee" => $_SESSION["id"]
                                );
                                $addAttendeeSession = $db->addAttendeeSession($attendeeSessionData);

                                redirect("events");
                            }
                        }//end else
                    }// end if
                    else {
                        // REDIRECT: Some other action was passed
                        redirect("events");
                    }
                }
                else if(isset($_GET["id"])){ 
                    // ID of event wanting to register for
                    $eventID = $_GET["id"];
                    $event = $db->getEvent($eventID);    // only need event's name for the section heading

                    echo "<p class='section-heading'>{$event->getName()}</p>";

                    $sessionsPerEvent = $db->getAllSessions(0,$eventID);
                    if(count($sessionsPerEvent) > 0){
                        echo "<p class='section-heading'>Available Sessions</p>";

                        $eventSessionContainer = "<div id='event-session-container'>";
                        foreach($sessionsPerEvent as $session){
                            $eventSessionContainer .= "<div class='event-registration-session-info'>
                                                            <p class='event-headings'>{$session->getName()}</p>
                                                            <p>Number Allowed: {$session->getNumberAllowed()}</p>
                                                            <p class='event-timings'>{$session->getDate()}</p>
                                                            <a href='./eventRegistration.php?event={$eventID}&session={$session->getIdSession()}&action=signup'>
                                                                <div class='sign-up-btns'>Sign up</div>
                                                            </a>
                                                            <a href='./eventRegistration.php?event={$eventID}&session={$session->getIdSession()}&action=signuppay'>
                                                                <div class='sign-up-btns'>Sign up and pay</div>
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