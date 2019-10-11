<?php
    session_name("Mok_Project1");
    session_start();

    require_once("DB.class.php");
    require_once("utilities.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Events</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

            // Verify User logged in before allowing any actions
            if(isset($_SESSION["userLoggedIn"]) && isset($_SESSION["role"])){
                // ALL roles are allowed to have acess to this page
                // Using check as an extra precaution
                if($_SESSION["role"] == "admin" || $_SESSION["role"] == "event_manager" || $_SESSION["role"] == "attendee"){
                    echo "<p class='section-heading'>Events</p>";
                    $allEvents = $db->getAllEvents();   // get all events


                    // Check to see the # of events retrieved, if none then display no events found message
                    if(count($allEvents) > 0){
                        $allVenues = $db->getAllVenues();   // get all Venues -> used to use name of venue associated with each event
                        
                        // Build events container with all events
                        $eventContainer = "<div id='event-container'>";
                        foreach($allEvents as $event){
                            $eventContainer .= "<a href='./registrations.php'>
                                                    <div class='events'>
                                                        <p class='event-headings'>{$event->getName()}</p>
                                                        <p class='event-timings'>{$event->getDateStart()} - {$event->getDateEnd()}</p>";
                                                        
                            // Make sure venues were retrieved to use its name on the events.php page
                            if(count($allVenues) > 0){
                                foreach($allVenues as $venue){
                                    if($venue->getIdVenue() == $event->getIdEvent()){
                                        $eventContainer .= "<p>{$venue->getName()}</p>"; // get the name
                                        break;
                                    }
                                }
                            }else{
                                $eventContainer .= "<p>Venue: TBD</p>"; // use the number if no name associated
                            }

                            $eventContainer .= "<p>Total Allowed: {$event->getNumberAllowed()}</p>
                                                    </div>
                                                </a>";
                        }
                        $eventContainer .= "</div>";
                        echo $eventContainer;
                    }
                    else{
                        // No events retrieved
                        echo "<h1>There are no available events!</h1>";
                    }
                }
            }
            else{
                // REDIRECT - User not logged in
                header("Location: login.php");
                exit;
            }
        ?>       
    </body>
</html>