<?php
    class DB {
        private $db;

        /**
         * Constructor
         */
        function __construct(){
            try{
                // open a connection
                $this->db = new PDO("mysql:host={$_SERVER['DB_SERVER']};dbname={$_SERVER['DB']}",
                    $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD']);

                // change error reporting
                $this->db->setAttribute(PDO::ATTR_ERRMODE,
                                        PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e){
                die("ERROR: Failed to connect");
            } 
        }
        



        /** -------------------- USERS -------------------- */
        /**
         * VerifyUser
         * Takes username and password from login form and checks with records already in db
         * @param $username
         * @param $password
         */
        function verifyUser($username, $password){
            try{
                include_once("./classes/Attendee.class.php");
                $data = array();
                $stmt = $this->db->prepare("SELECT * FROM attendee WHERE name = :name AND password = :password");

                $stmt->execute(array(
                    ":name" => $username,
                    ":password" => $password
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "Attendee");
                $data = $stmt->fetchAll();

                // Check to see if row was returned, if not then login failed
                if($stmt->rowCount() > 0){
                    $id = $data[0]->getIdAttendee();
                    $name = $data[0]->getName();
                    $role = $data[0]->getRole();

                    // Array to pass info needed on login page
                    $responseArr = array(
                        "rowCount" => $stmt->rowCount(),
                        "currentUser" => array(    
                            "id" => $id,        
                            "name" => $name,  
                            "role" => $role
                        )
                    );

                    return $responseArr;
                }
                else{
                    return -1; // no match found
                }
            }
            catch(PDOException $e){
                die("There was a problem logging user in!");
            } 
        }

        /**
         * getALlUsers
         * Returns all users - primarily for the admin
         */
        function getAllUsers(){
            try{
                include_once("./classes/Attendee.class.php"); // include the attendee class file
                $data = array();
                $query = "SELECT * FROM attendee";
                $stmt = $this->db->prepare($query);
                $stmt->execute();

                $stmt->setFetchMode(PDO::FETCH_CLASS, "Attendee");
                $data = $stmt->fetchAll();

                return $data;
            }
            catch(PDOException $e){
                die("There was a problem getting all users!");
            } 
        }

        /**
         * getUser
         * @param $id
         * Retrieves a specific user from a given ID
         * Different from verifyUser as will return entire attendee obj
         */
        function getUser($id){
            try{
                include_once("./classes/Attendee.class.php");
                $data = array();
                $query = "SELECT * FROM attendee WHERE idattendee = :id";
                $stmt = $this->db->prepare($query);
                $stmt->setFetchMode(PDO::FETCH_CLASS, "Attendee");
                $stmt->execute(array(
                    ":id" => $id
                ));
                $data = $stmt->fetchAll(); // retrieves the user

                return $data;
            }
            catch(PDOException $e){
                die("There was a problem getting the user!");
            }
        }

        /**
         * insertUser
         * @param $data
         * $DATA contains necessary fields to add a user
         * Inserts a new user 
         */
        function insertUser($data){
            try{
                $query = "INSERT INTO attendee (name,password,role) 
                            VALUES (:name, :password, :role)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":name" => $data["name"],
                    ":password" => $data["password"],
                    ":role" => $data["role"]
                ));

                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem inserting user!");
            } 
        }

        /**
         * updateUser
         * @param $data
         * $data = array("key" => value, "key" => value);
         * Updates a user by a provided array of field & values
         */
        function updateUser($data){
            try{
                $query = "UPDATE attendee SET ";
                $updateId = 0; 
                $updateArr = array();

                foreach($data as $k => $v){
                    switch($k){
                        case "name":
                            $query .= "name = :name,";
                            $updateArr[":name"] = $v;
                            break;
                        case "password":
                            $query .= "password = :password,";
                            $updateArr[":password"] = $v;
                            break;
                        case "role":
                            $query .= "role = :role,";
                            $updateArr[":role"] = $v;
                            break;
                        case "id":    
                            $updateId = intval($v);
                            break;
                    }
                }
                $query = trim($query, ",");
                $query .= " WHERE idattendee = :id";
                $updateArr[":id"] = $updateId;

                $stmt = $this->db->prepare($query);

                // Bind all params 
                foreach($updateArr as $k => $v){
                    $stmt->bindParam($k, $v);
                }
                $stmt->execute($updateArr);
                return $stmt->rowCount(); // return the # of rows affected
            }
            catch(PDOException $e){
                die("There was a problem updating user!");
            } 
        }

        /**
         * deleteUser
         * @param $data
         * $data[0] = $id
         * $data is an array with only one value of ID
         * Deletes a user by id
         */
        function deleteUser($data){ 
            try{
                // Make sure the superadmin account CANNOT be deleted
                if($data[0] == 1){ // if id = superadmin's
                    return 0;
                }
                else{
                    // UserID isn't the superadmin's
                    $query = "DELETE FROM attendee WHERE idattendee = :idattendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":idattendee" => $data[0]
                    ));
                    return $stmt->rowCount();
             }
            }
            catch(PDOException $e){
                die("There was a problem deleting user!");
            } 
        }

        
 

        /** -------------------- EVENTS -------------------- */
        /**
         * getAllEvents
         * Retrieves all available events
         */
        function getAllEvents(){
            try{
                include_once("./classes/Event.class.php");   

                $data = array();
                $query = "SELECT * FROM event";
                $stmt = $this->db->prepare($query);
                $stmt->execute();

                $stmt->setFetchMode(PDO::FETCH_CLASS, "Event");
                $data = $stmt->fetchAll(); // fetch all the events

                return $data;
            }
            catch(PDOException $e){
                die("There was a problem getting all events!");
            } 
        }

        /**
         * getEvent
         * @param $id
         */
        function getEvent($id){
            try{
                include_once("./classes/Event.class.php");
                $data = array();
                $query = "SELECT * FROM event WHERE idevent = :idevent";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":idevent" => $id
                ));
                
                $stmt->setFetchMode(PDO::FETCH_CLASS, "Event");
                return $stmt->fetch();
            }
            catch(PDOException $e){
                die("There was a problem getting all events!");
            } 
        }

        /**
         * getManagerEvent
         * @param $managerID
         * Retreive MULTIPLE manager_event obj (eventID + managerID) based on MANAGERID
         */
        function getAllManagerEventsOBJ($managerID){
            try{
                include_once("./classes/ManagerEvent.class.php");
                $query = "SELECT * FROM manager_event WHERE manager = :managerID";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":managerID" => $managerID
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "ManagerEvent");
                return $stmt->fetchAll();
            }
            catch(PDOException $e){
                die("There was a problem retrieving events!");
            } 
        }

        /**
         * getManagerEvent
         * @param $eventID
         * Retrieves the manager_event object by the EVENTID
         */
        function getManagerEventOBJ($eventID){
            try{
                include_once("./classes/ManagerEvent.class.php");
                $query = "SELECT * FROM manager_event WHERE event = :eventID";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":eventID" => $eventID
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "ManagerEvent");
                return $stmt->fetchAll();
            }
            catch(PDOException $e){
                die("There was a problem retrieving events!");
            } 
        }

        /**
         * getAllAttendeeEvents
         * @param $attendeeID
         * Retrieves all ATTENDEE_EVENT objects
         */
        function getAllAttendeeEvents(){
            try{
                include_once("./classes/AttendeeEvent.class.php");
                $query = "SELECT * FROM attendee_event";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                $data = $stmt->fetchAll();
                return $data;
            }
            catch(PDOException $e){
                die("There was a problem retrieving all attendees attending events!");
            } 
        }

        /**
         * getAllAttendeeEventsById
         * @param $attendeeID
         * Retrieves all ATTENDEE_EVENT objects for attendee based on attendeeID
         */
        function getAllAttendeeEventsById($attendeeID){
            try{
                include_once("./classes/AttendeeEvent.class.php");
                $query = "SELECT * FROM attendee_event WHERE attendee = :attendeeID";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":attendeeID" => $attendeeID
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                $data = $stmt->fetchAll();
                return $data;
            }
            catch(PDOException $e){
                die("There was a problem retrieving attendee events!");
            } 
        }

        /**
         * getAttendeeEventByEventAttendee
         * @param $eventID, $attendeeID
         * ESSENTIALLY VERIFIES IF THE USER IS ASSOCIATED WITH THE EVENT
         * Gets the attendee_event using given eventID and attendeeID to see if they already signed up
         */
        function getAttendeeEventByEventAttendee($eventID, $attendeeID){
            try{
                include_once("./classes/AttendeeEvent.class.php");
                $query = "SELECT * FROM attendee_event WHERE event = :event AND attendee = :attendee";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":event" => $eventID,
                    ":attendee" => $attendeeID
                )); 
                $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                $data = $stmt->fetch();
                return $data;
            }
            catch(PDOException $e){
                die("There was a problem verifying registration!");
            } 
        }

        /**
         * addEvent
         * @param $data
         */
        function addEvent($data){
            try{
                $query = "INSERT INTO event (name, datestart, dateend, numberallowed, venue)
                            VALUES (:name, :datestart, :dateend, :numberallowed, :venue)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":name" => $data["name"],
                    ":datestart" => $data["datestart"],
                    ":dateend" => $data["dateend"],
                    ":numberallowed" => $data["numberallowed"],
                    ":venue" => $data["venue"]
                ));
                return $this->db->lastInsertId();
                // return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem adding event!");
            } 
        }

        /**
         * addManagerEvent
         * @param $data
         * Adds a manager_event OBJECT when an event manager makes an event
         */
        function addManagerEvent($data){
            try{
                $query = "INSERT INTO manager_event (event, manager)
                            VALUES (:event, :manager)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":event" => $data["event"],
                    ":manager" => $data["manager"]
                )); 
                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem adding manager event!");
            } 
        }

        /**
         * addAttendeeEvent
         * @param $data
         * Adds a attendee_event OBJECT when user (admin,event_manager,attendee) signs up for an event
         */
        function addAttendeeEvent($data){
            try{
                $query = "INSERT INTO attendee_event (event, attendee, paid)
                            VALUES (:event, :attendee, :paid)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":event" => $data["event"],
                    ":attendee" => $data["attendee"],
                    ":paid" => $data["paid"]
                ));
                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem adding attendee event!");
            } 
        }

        /**
         * updateEvent
         * @param $data
         * $data = array();
         * $data contains all fields necessary to do the update
         */
        function updateEvent($data){
            try{
                $query = "UPDATE event SET ";
                $updateId = 0; 
                $updateArr = array();

                foreach($data as $k => $v){
                    switch($k){
                        case "name":
                            $query .= "name = :name,";
                            $updateArr[":name"] = $v;
                            break;
                        case "datestart":
                            $query .= "datestart = :datestart,";
                            $updateArr[":datestart"] = intval($v);
                            break;
                        case "dateend":
                            $query .= "dateend = :dateend,";
                            $updateArr[":dateend"] = intval($v);
                            break;
                        case "numberallowed":
                            $query .= "numberallowed = :numberallowed,";
                            $updateArr[":numberallowed"] = intval($v);
                            break;
                        case "venue":
                            $query .= "venue = :venue,";
                            $updateArr[":venue"] = intval($v);
                            break;
                        case "id":    
                            $updateId = intval($v);
                            break;
                    }
                }
                $query = trim($query, ",");
                $query .= " WHERE idevent = :id";
                $updateArr[":id"] = $updateId;

                $stmt = $this->db->prepare($query);

                // Bind all params 
                foreach($updateArr as $k => $v){
                    $stmt->bindParam($k, $v);
                }

                $stmt->execute($updateArr);

                return $stmt->rowCount(); // return the # of rows affected
            }
            catch(PDOException $e){
                die("There was a problem updating the event!");
            } 
        }

        /**
         * deleteEvent
         * @param $data
         * $data["id"] = id 
         */
        function deleteEvent($eventID){
            try{
                // delete event
                $query = "DELETE FROM event WHERE idevent = :idevent";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":idevent" => $eventID
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem getting all events!");
            } 
        }

        /**
         * deleteManagerEvent
         * @param $eventID 
         * Delete manager event
         */
        function deleteManagerEvent($eventID){
            try{
                $query = "DELETE FROM manager_event WHERE event = :event";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":event" => $eventID
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting manager event!");
            } 
        }

        /**
         * deleteAttendeeEvent
         * @param $eventID 
         * Deletes the attendeEvent records 
         */
        function deleteAttendeeEvent($eventID){
            try{
                $query = "DELETE FROM attendee_event WHERE event = :event";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":event" => $eventID
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting attendee event!");
            } 
        }

        /**
         * deleteEventAndSession
         * @param $data
         * Function to delete an event, the attendee & manager events tied to that event, and attendee sessions tied to event
         */
        function deleteEventAndSession($eventID){
            try{
                // Array to hold results (value # of rows affected)
                $sumResults = array();

                // Deleting events
                $deleteAttendeeEvent = $this->deleteAttendeeEvent($eventID); // delete attendee object if exists
                $deleteManagerEvent = $this->deleteManagerEvent($eventID);   // delete manager event object if exists
                $deleteEvent = $this->deleteEvent($eventID);                 // delete entire event

                // Deleting session
                // Need to delete ATTENDEE_SESSIONS too! BUT... Need sessionIDs for those sessions associated w/deleted Event
                $allSessionsPerEvent = $this->getAllSessionsPerEvent($eventID); // retrieve all relevent sessions from event
                foreach($allSessionsPerEvent as $k => $v){
                    $deleteAttendeeSession = $this->deleteAttendeeSessions($v->getIdSession()); // delete attendee_sesion objects
                    $deleteManagerSession = $this->deleteManagerSessions($v->getIdSession());   // delete manager_session objects
                    $sumResults[] = $deleteAttendeeSession;
                    $sumResults[] = $deleteManagerSession;
                }   
                $deleteAllSessions = $this->deleteSessionsPerEvent($eventID);    // delete SESSION objects that exist for the event

                // Add all number value of rows affected into array
                $sumResults[] = $deleteAttendeeEvent;
                $sumResults[] = $deleteManagerEvent;
                $sumResults[] = $deleteEvent;
                $sumResults[] = $deleteAllSessions;
                $sum = array_sum($sumResults);

                // return number affected
                if($sum > 0){
                    return $sum;
                }
                else{
                    return 0;
                }
            }
            catch(PDOException $e){
                die("There was a deleting the event and its associated sessions!");
            } 
        }
        



        /* -------------------- SESSIONS -------------------- */
        /**
         * getAllSessions
         * Retrieves all sessions for all events
         */
        function getAllSessions(){
            try{
                include_once("./classes/Session.class.php");
                $data = array();
                $query = "SELECT * FROM session";
                $stmt = $this->db->prepare($query);
                $stmt->execute();

                $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");
                $data = $stmt->fetchAll();

                return $data;
            }
            catch(PDOException $e){
                die("There was a problem getting all sessions!");
            }
        }

        /**
         * getSesssion
         * @param $data
         * Retrieves a session by its ID, so only 1 or 0 retrieved
         */
        function getSession($sessionID){
            try{
                include_once("./classes/Session.class.php");
                $query = "SELECT * FROM session WHERE idsession = :idsession";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":idsession" => $sessionID
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");
                return $stmt->fetch();
            }
            catch(PDOException $e){
                die("There was a problem retrieving session!");
            }
        }

        /**
         * getAllSessionsPerEvent
         * @param $eventID
         * Retrieves all sessions associated with a given eventID
         */
        function getAllSessionsPerEvent($eventID){
            try{
                include_once("./classes/Session.class.php");
                $query = "SELECT * FROM session WHERE event = :event";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":event" => $eventID
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");
                $results = $stmt->fetchAll();
                return $results;
            }
            catch(PDOException $e){
                die("There was a problem retrieving sessions for the event!");
            }
        }


        /**
         * getManagerSessions
         * @param $managerID
         * NOTE: there's a table added 
         * Retrieves all the sessions a manager owns 
         */
        function getAllManagerSessionsObj($managerID){
            try{
                include_once("./classes/ManagerSession.class.php");
                $query = "SELECT * FROM manager_session WHERE manager = :managerID";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":managerID" => $managerID
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "ManagerSession");
                $data = $stmt->fetchAll();
                return $data;
            }
            catch(PDOException $e){
                die("There was a problem retrieving manager sessions!");
            }
        }

        /**
         * getAllAttendeeSessionsById
         * @param $attendeeID
         * Retrieves all ATTENDEE_SESSION objects by the attendee's ID
         */
        function getAllAttendeeSessionsById($attendeeID){
            try{
                include_once("./classes/AttendeeSession.class.php");
                $query = "SELECT * FROM attendee_session WHERE attendee = :attendeeID";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":attendeeID" => $attendeeID
                ));
                $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeSession");
                $data = $stmt->fetchAll();
                return $data;
            }
            catch(PDOException $e){
                die("There was a problem retrieving attendee sessions!");
            }
        }

        /**
         * getAttendeeEventBySessionAttendee
         * @param $eventID, $attendeeID
         * ESSENTIALLY VERIFIES IF THE USER IS ASSOCIATED WITH THE SESSION
         * Gets the attendee_session using given sessionID and attendeeID to see if they already signed up
         */
        function getAttendeeEventBySessionAttendee($session, $attendeeID){
            try{
                include_once("./classes/AttendeeEvent.class.php");
                $query = "SELECT * FROM attendee_session WHERE session = :session AND attendee = :attendee";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":session" => $session,
                    ":attendee" => $attendeeID
                )); 
                $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                $data = $stmt->fetch();
                return $data;
            }
            catch(PDOException $e){
                die("There was a problem verifying registration!");
            } 
        }

        /**
         * addManagerSession
         * @param $data
         */
        function addManagerSession($data){
            try{
                $query = "INSERT INTO manager_session (session, manager)
                            VALUES (:session, :manager)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":session" => $data["session"],
                    ":manager" => $data["manager"]
                ));
                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem retrieving manager session!");
            }
        }

        /**
         * addSession
         * @param $data
         * $data = array()
         * Adds a new session based on provided data
         */
        function addSession($data){
            try{
                $query = "INSERT INTO session (name, numberallowed, event, startdate, enddate) 
                            VALUES (:name, :numberallowed, :event, :startdate, :enddate)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":name" => $data["name"],
                    ":numberallowed" => $data["numberallowed"],
                    ":event" => $data["event"],
                    ":startdate" => $data["datestart"],
                    ":enddate" => $data["dateend"]
                ));
                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem adding the session!");
            }
        }

        /**
         * updateSession
         * @param $data
         * $data = array("key" => value, "key" => value);
         * Updates a user by a provided array of field & values
         */
        function updateSession($data){
            try{
                $query = "UPDATE session SET ";
                $updateId = 0; 
                $updateArr = array();

                foreach($data as $k => $v){
                    switch($k){
                        case "name":
                            $query .= "name = :name,";
                            $updateArr[":name"] = $v;
                            break;
                        case "numberallowed":
                            $query .= "numberallowed = :numberallowed,";
                            $updateArr[":numberallowed"] = intval($v);
                            break;
                        case "event":
                            $query .= "event = :event,";
                            $updateArr[":event"] = intval($v);
                            break;
                        case "datestart":
                            $query .= "datestart = :datestart,";
                            $updateArr[":datestart"] = intval($v);
                            break;
                        case "dateend":
                            $query .= "dateend = :dateend,";
                            $updateArr[":dateend"] = intval($v);
                            break;
                        case "id":    
                            $updateId = intval($v);
                            break;
                    }
                }
                $query = trim($query, ",");
                $query .= " WHERE idsession = :id";
                $updateArr[":id"] = $updateId;

                $stmt = $this->db->prepare($query);

                // Bind all params 
                foreach($updateArr as $k => $v){
                    $stmt->bindParam($k, $v);
                }

                $stmt->execute($updateArr);

                return $stmt->rowCount(); // return the # of rows affected
            }
            catch(PDOException $e){
                die("There was a problem updating session!");
            }
        }

        /**
         * deleteSession
         * @param $sessionID
         * Deletes A SESSION based on its SESSIONID
         */
        function deleteSession($sessionID){
            try{
                // Delete session 
                $query = "DELETE FROM session WHERE idsession = :idsession";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":idsession" => $sessionID
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting sessions!");
            }
        }

        /**
         * deleteSessionsPerEvent
         * @param $eventID
         * Deletes SESSIONS associated with an EVENT based on the EVENTID
         */
        function deleteSessionsPerEvent($eventID){
            try{
                $query = "DELETE FROM session WHERE event = :event";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":event" => $eventID
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting all of the event's sessions!");
            }
        }

        /**
         * deleteAttendeeSessions
         * Deletes ATTENDEE_SESSION records based on sessionID
         */
        function deleteAttendeeSessions($sessionID){
            try{
                $query = "DELETE FROM attendee_session WHERE session = :session";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":session" => $sessionID
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting attendee session!");
            } 
        }

        /**
         * deleteManagerSessions
         * @param $sessionID
         * Deletes MANAGER_SESSION records based on sessionID
         */
        function deleteManagerSessions($sessionID){
            try {
                $query = "DELETE FROM manager_session WHERE session = :sessionID";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":sessionID" => $sessionID
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting manager's session!");
            } 
        }



        
        /* -------------------- VENUES -------------------- */
        /**
         * getAllVenues
         * Retrieves all available venues
         */
        function getAllVenues(){
            try{
                include_once("./classes/Venue.class.php");

                $data = array();
                $query = "SELECT * FROM venue";
                $stmt = $this->db->prepare($query);
                $stmt->execute();

                $stmt->setFetchMode(PDO::FETCH_CLASS, "Venue");
                $data = $stmt->fetchAll();

                return $data;
            }
            catch(PDOException $e){
                die("There was a problem getting all venues!");
            } 
        }

        /**
         * getVenue
         * @param $id
         * Retrieves a single venue
         */
        function getVenue($id){
            try{
                include_once("./classes/Venue.class.php");
                $data = array();
                $query = "SELECT * FROM venue WHERE idvenue = :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":id" => $id
                ));

                $stmt->setFetchMode(PDO::FETCH_CLASS, "Venue");
                $data = $stmt->fetch();
                return $data;
            }
            catch(PDOException $e){
                die("There was a problem getting venue!");
            } 
        }

         /**
         * addVenue()
         * $data = array();
         * $data only contains the name and capacity values to use
         * Adds a new venue
         */
        function addVenue($data){
            try{
                $query = "INSERT INTO venue (name, capacity)
                            VALUES (:name, :capacity)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":name" => $data["name"],
                    ":capacity" => $data["capacity"]
                ));
                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem adding venue!");
            } 
        }

        /**
         * editVenue
         * @param $data
         * $data = array()
         * Edits/Updates a venue given an array of data
         */
        function updateVenue($data){
            try{
                $query = "UPDATE venue SET ";
                $updateId = 0; 
                $updateArr = array();

                foreach($data as $k => $v){
                    switch($k){
                        case "name":
                            $query .= "name = :name,";
                            $updateArr[":name"] = $v;
                            break;
                        case "capacity":
                            $query .= "capacity = :capacity,";
                            $updateArr[":capacity"] = intval($v);
                            break;
                        case "id":    
                            $updateId = intval($v);
                            break;
                    }
                }
                $query = trim($query, ",");
                $query .= " WHERE idvenue = :id";
                $updateArr[":id"] = $updateId;

                $stmt = $this->db->prepare($query);

                // Bind all params 
                foreach($updateArr as $k => $v){
                    $stmt->bindParam($k, $v);
                }
                $stmt->execute($updateArr);
                return $stmt->rowCount(); // return the # of rows affected
            }
            catch(PDOException $e){
                die("There was a problem updating venue!");
            } 
        }
        
        /**
         * deleteVenue
         * @param $id
         * Deletes a venue by ID
         */
        function deleteVenue($id){
            try{
                $query = "DELETE FROM venue WHERE idvenue = :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":id" => $id
                ));
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting venue!");
            } 
        }
    }// end class

