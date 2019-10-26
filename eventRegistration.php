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
                            $attendeeSessions = $db->getAttendeeSessions($_GET["session"], $_SESSION["id"]);
                            if(isset($attendeeSessions) && !empty($attendeeSessions)){
                                // Session already signed up for
                                errorDisplay("Session is already registered!");
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
                    $eventID = $_GET["id"];              // ID of event wanting to register for
                    $event = $db->getEvent($eventID);    // only need event's name for the section heading

                    echo "<p class='section-heading'>{$event->getName()}</p>";

                    $sessionsPerEvent = $db->getAllSessions(0,$eventID);    // get all Session objects per an event
                    if(count($sessionsPerEvent) > 0){
                        echo "<p class='section-heading'>Available Sessions</p>";

                        $eventSessionContainer = "<div id='event-session-container'>";  // container to hold everything
                        foreach($sessionsPerEvent as $session){
                            $registered = false;    // var if user registered - reset to false at beginning for each session object

                            // Determine if user is already registered for this event
                            // if so, then do changes to html string to not allow signup again
                            $attendeeSession = $db->getAttendeeSessions($session->getIdSession(), $_SESSION["id"]);
                            if(isset($attendeeSession) && !empty($attendeeSession)){
                                $registered = true;
                            }                            

                            // HTML container
                            $eventSessionContainer .= "<div class='event-registration-session-info'>";
                            if($registered){
                                $eventSessionContainer .= "<p class='event-headings'>{$session->getName()} - <i class='fas fa-check'></i> Registered</p>";
                            }
                            else{
                                $eventSessionContainer .= "<p class='event-headings'>{$session->getName()}</p>";
                            }

                            $eventSessionContainer .= "<p>Number Allowed: {$session->getNumberAllowed()}</p>
                                                        <p class='event-timings'>{$session->getDate()}</p>";

                            // Whether user signed up or not
                            // Prevent signing up for a session already signed up for!
                            if($registered){
                                $eventSessionContainer .= "<div class='disabled-btns'>Sign up</div>
                                                            <div class='disabled-btns'>Sign up and pay</div>";
                            }
                            else {
                                $eventSessionContainer .= "<a href='./eventRegistration.php?event={$eventID}&session={$session->getIdSession()}&action=signup'>
                                                                <div class='sign-up-btns'>Sign up</div>
                                                            </a>
                                                            <a href='./eventRegistration.php?event={$eventID}&session={$session->getIdSession()}&action=signuppay'>
                                                                <div class='sign-up-btns'>Sign up and pay</div>
                                                            </a>";
                            }
                            $eventSessionContainer .= "</div>";
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