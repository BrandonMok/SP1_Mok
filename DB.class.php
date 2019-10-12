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
                    $name = $data[0]->getName();
                    $role = $data[0]->getRole();

                    // Array to pass info needed on login page
                    $responseArr = array(
                        "rowCount" => $stmt->rowCount(),
                        "currentUser" => array(            
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
         * @param $name
         * @param $password
         * Inserts a new user - ATTENDEE ROLE by DEFAULT unless admin changes it
         */
        function insertUser($name, $password, $role = 3){
            try{
                $query = "INSERT INTO attendee (name,password,role) 
                            VALUES (:name, :password, :role)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":name" => $name,
                    ":password" => $password,
                    ":role" => $role
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
                $data = $stmt->fetchAll();

                return $data;
            }
            catch(PDOException $e){
                die("There was a problem getting all events!");
            } 
        }

        function deleteEvent($data){
            try{
                // delete event
                // delete all sessions associated to the event
                $query = "DELETE FROM event WHERE idevent = :idevent";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":idevent" => $data["id"]
                ));


                // NOW DELETE SESSIONS
                // ALSO DELETE SESSIONS FOR USER SESSSIONS
                // DELETE ATTENDEE EVENTS
                
                return $stmt->rowCount();
            }
            catch(PDOException $e){
                die("There was a problem getting all events!");
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
         * deleteSession
         * @param $sessionID
         * Deletes one session by its ID
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
                $data = $stmt->fetchAll();

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
                return $stmt->rowCount();
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

