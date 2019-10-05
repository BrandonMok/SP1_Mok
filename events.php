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
            if(isset($_SESSION['userLoggedIn']) && isset($_SESSION['role'])){
                // ALL roles are allowed to have acess to this page
                // Using check as an extra precaution
                if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'event_manager' || $_SESSION['role'] == 'attendee'){
                    $allEvents = $db->getAllEvents();

                    echo "<p class='section-heading'>Events</p>";

                    // Check to see the # of events retrieved
                    if(count($allEvents) > 0){
                        $eventContainer = "<div id='event-container'>";
                        foreach($allEvents as $event){
                            $eventContainer .= "<a href='./registrations.php'>
                                                    <div class='events'>
                                                        <p class='event-headings'>{$event->getName()}</p>
                                                        <p class='event-timings'>{$event->getDateStart()} - {$event->getDateEnd()}</p>
                                                        <p>Venue: {$event->getVenue()}</p>
                                                        <p>Total Allowed: {$event->getNumberAllowed()}</p>
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

            
            // FOOTER
            reusableFooter();
        ?>       
    </body>
</html>