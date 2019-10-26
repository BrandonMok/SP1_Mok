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
         * getAllRoles
         * @param $roleID
         * Retrieves a role based on ID
         */
        function getAllRoles($roleID = 0){
            try{
                include_once("./classes/Role.class.php");
                $query = "SELECT * FROM role ";

                if($roleID == 0){
                    // No ID passed - get all
                    $stmt = $this->db->prepare(trim($query));
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "Role");
                    return $stmt->fetchAll();
                }
                else {
                    $query = "SELECT * FROM role WHERE idrole = :idrole";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":idrole" => $roleID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "Role");
                    return $stmt->fetch();
                }
            }
            catch(PDOException $e){
                die("There was a problem getting role name!");
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
                $data = $stmt->fetch(); // retrieves the user

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

        /**
         * deleteAllUserInfo
         * @param $data
         * Deletes a user and all their relevant info (i.e. attendee_events + attendee_sessions)
         */
        function deleteAllUserInfo($data){
            try {
                $deleteUSR = $this->deleteUser($data);

                if($deleteUSR > 0){
                    $this->deleteAttendeeEvent(0,$data[0]);
                    $this->deleteAttendeeSession(0,$data[0]);
                }
            }   
            catch(PDOException $e){
                die("There was a problem deleting user's record!");
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
         * getAllManagerEvents
         * @param $eventID, $managerID
         * Retrieves manager_events based on value(s)
         */
        function getAllManagerEvents($eventID = 0, $managerID = 0){
            try{
                include_once("./classes/ManagerEvent.class.php");
                $query = "SELECT * FROM manager_event WHERE ";

                if($eventID != 0 && $managerID != 0){
                    $query .= "event = :event AND manager = :manager";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID,
                        ":manager" => $managerID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "ManagerEvent");
                    return $stmt->fetch();
                }
                else if ($eventID != 0){
                    $query .= "event = :event";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "ManagerEvent");
                    return $stmt->fetchAll();
                }
                else if ($managerID != 0){
                    $query .= "manager = :manager";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":manager" => $managerID
                    ));      
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "ManagerEvent");
                    return $stmt->fetchAll();            
                }
            }
            catch(PDOException $e){
                die("There was a problem retrieving manager events!");
            } 
        }


        /**
         * getAllAttendeeEvents
         * @param $eventID, $attendeeID
         * Retrieves attendee_event(s) based on value(s) provided
         */
        function getAllAttendeeEvents($eventID = 0, $attendeeID = 0){
            try{
                include_once("./classes/AttendeeEvent.class.php");
                $query = "SELECT * FROM attendee_event ";

                if($eventID == 0 && $attendeeID == 0){
                    // No parameters supplied - so all attendee_event objects!
                    $query .= "ORDER BY event";
                    $stmt = $this->db->prepare(trim($query));
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                    return $stmt->fetchAll();
                }
                else if($eventID != 0 && $attendeeID != 0){
                    // Both supplied - so a specific attendee_event object!
                    $query .= "WHERE event = :event AND attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID,
                        ":attendee" => $attendeeID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                    return $stmt->fetch();
                }
                else if($eventID != 0){
                    // Can only have one attendee_event per event
                    $query .= "WHERE event = :event";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID
                    ));  
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                    return $stmt->fetch();
                }
                else if($attendeeID != 0){
                    // Can have multiple attendee_events per attendee!
                    $query .= "WHERE attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":attendee" => $attendeeID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeEvent");
                    return $stmt->fetchAll();
                }
            }
            catch(PDOException $e){
                die("There was a problem getting registrations!");
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
                return $stmt->rowCount();   // attendee_event doesn't have incremented ID
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
         * @param $eventID, $managerID
         * Deletes manager_event object from value(s) provided
         */
        function deleteManagerEvent($eventID = 0, $managerID = 0){
            try{
                $query = "DELETE FROM manager_event WHERE ";

                if($eventID != 0 && $managerID != 0){
                    $query .= "event = :event AND manager = :manager";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID,
                        ":manager" => $managerID
                    ));
                }
                else if ($eventID != 0){
                    $query .= "event = :event";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID
                    ));
                }
                else if ($managerID != 0){
                    $query .= "manager = :manager";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":manager" => $managerID
                    ));
                }

                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting manager event!");
            } 
        }

        /**
         * deleteAttendeeEvent
         * @param $eventID, $attendeeID
         * Deletes attendee_event using value(s) provided
         */
        function deleteAttendeeEvent($eventID = 0, $attendeeID = 0){
            try{
                $query = "DELETE FROM attendee_event WHERE ";

                if($eventID != 0 && $attendeeID != 0){
                    $query .= "event = :event AND attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID,
                        ":attendee" => $attendeeID
                    ));
                }
                else if ($eventID != 0){
                    $query .= "event = :event";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID
                    ));
                }
                else if ($attendeeID != 0){
                    $query .= "attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":attendee" => $attendeeID
                    ));
                }

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
                $allSessionsPerEvent = $this->getAllSessions(0,$eventID); // retrieve all relevent sessions from event
                foreach($allSessionsPerEvent as $k => $v){
                    $deleteAttendeeSession = $this->deleteAttendeeSession($v->getIdSession()); // delete attendee_sesion objects
                    $deleteManagerSession = $this->deleteManagerSession($v->getIdSession());   // delete manager_session objects
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
         * @param $sessionID, $eventID
         * Retrieves session objects based on value(s)
         */
        function getAllSessions($sessionID = 0, $eventID = 0){
            try{
                include_once("./classes/Session.class.php");
                $query = "SELECT * FROM session ";

                if($sessionID == 0 && $eventID == 0){
                    // No params passed - want all
                    $stmt = $this->db->prepare(trim($query));
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");
                    return $stmt->fetchAll();
                }
                else if($sessionID != 0 && $eventID != 0){
                    // BOth Params passed - want specific session object
                    $query .= "WHERE idsession = :idsession AND event = :event";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":idsession" => $sessionID,
                        ":event" => $eventID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");
                    return $stmt->fetch();                    
                }
                else if($sessionID != 0){
                    $query .= "WHERE idsession = :idsession";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":idsession" => $sessionID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");
                    return $stmt->fetch();                  
                }
                else if($eventID != 0){
                    $query .= "WHERE event = :event";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":event" => $eventID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");
                    return $stmt->fetchAll();
                }
            }
            catch(PDOException $e){
                die("There was a problem retrieving sessions!");
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
         * getAttendeeSessions
         * @param $sessionID, $attendeeID
         * Retrieves attendee_session based on value(s)
         */
        function getAttendeeSessions($sessionID = 0, $attendeeID = 0){
            try {
                include_once("./classes/AttendeeSession.class.php");
                $query = "SELECT * FROM attendee_session ";

                if($sessionID == 0 && $attendeeID == 0){
                    // No params passed - want all
                    $stmt = $this->db->prepare(trim($query));
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeSession");
                    return $stmt->fetchAll();
                }
                else if($sessionID != 0 && $attendeeID != 0){
                    // Both passed - want specific object
                    $query .= "WHERE session = :session AND attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":session" => $sessionID,
                        ":attendee" => $attendeeID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeSession");
                    return $stmt->fetch();
                }
                else if($sessionID != 0){
                    $query .= "WHERE session = :session";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":session" => $sessionID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeSession");
                    return $stmt->fetch();
                }
                else if($attendeeID != 0){
                    $query .= "WHERE attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":attendee" => $attendeeID
                    ));
                    $stmt->setFetchMode(PDO::FETCH_CLASS, "AttendeeSession");
                    return $stmt->fetchAll();
                }
            }
            catch(PDOException $e){
                die("There was a problem retriving attendee session!");
            } 
        }



        /**
         * addManagerSession
         * @param $data
         * Creates a new manager_session object!
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
                die("There was a problem creating manager session!");
            }
        }

         /**
         * addAttendeeSession
         * @param $data
         * Creates a new attendee_session object!
         */
        function addAttendeeSession($data){
            try{
                $query = "INSERT INTO attendee_session (session, attendee)
                            VALUES (:session, :attendee)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":session" => $data["session"],
                    ":attendee" => $data["attendee"]
                ));
                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem creating attendee session!");
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
         * deleteAllSessions
         * @param $sessionID
         * Deleting a session also requires deleting attendee_sessions & manager_sessions
         */
        function deleteAllSessions($sessionID){
            try{
                // DELETE session object
                $this->deleteSession($sessionID); 

                // DELETE attendee_sessions + manager_sessions
                $this->deleteAttendeeSession($sessionID);
                $this->deleteManagerSession($sessionID);
            }
            catch(PDOException $e){
                die("There was a problem deleting all of the event's sessions!");
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
         * deleteAttendeeSession
         * @param $sessionID, $attendeeID
         * Deletes attendee_session based on value(s)
         */
        function deleteAttendeeSession($sessionID = 0, $attendeeID = 0){
            try {
                $query = "DELETE FROM attendee_session WHERE ";
                if($sessionID != 0 && $attendeeID != 0){
                    $query .= "session = :session AND attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":session" => $sessionID,
                        ":attendee" => $attendeeID
                    ));
                }
                else if($sessionID != 0){
                    $query .= "session = :session";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":session" => $sessionID
                    ));
                }
                else if ($attendeeID != 0){
                    $query .= "attendee = :attendee";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":attendee" => $attendeeID
                    ));
                }

                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem deleting attendee session!");
            } 
        }

        /**
         * deleteManagerSession
         * @param $sessionID, $managerID
         * Deletes manager_session based on value(s)
         */
        function deleteManagerSession($sessionID = 0, $managerID = 0){
            try {
                $query = "DELETE FROM manager_session WHERE ";
                if($sessionID != 0 && $managerID != 0){
                    $query .= "session = :session AND manager = :manager";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":session" => $sessionID,
                        ":manager" => $managerID
                    ));
                }
                else if($sessionID != 0){
                    $query .= "session = :session";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":session" => $sessionID
                    ));
                }
                else if($managerID != 0){
                    $query .= "manager = :manager";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(array(
                        ":manager" => $managerID
                    ));
                }

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

