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
                    $responseArr = array(
                        "rowCount" => $stmt->rowCount(),
                        // "currentUser" => $data
                        "currentUser" => array(            
                            "name" => $data[0]->getName(),  
                            "role" => $data[0]->getRole()
                        )
                    );


                    // might want to return entire obj so can change the role for defaults
                    // OR check here if it's null, then set it?
                    // if(!isset($data[0]->getRole())){
                    //     // DO AN UPDATE TO THE USER FOR THEIR ROLE!
                    // }

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
         * insertUser
         * Inserts a new attendee account user
         */
        function insertUser($name, $password){
            try{
                $query = "INSERT INTO attendee (name,password) 
                            VALUES (:name, :password)";
                $stmt = $this->db->prepare($query);
                $stmt->execute(array(
                    ":name" => $name,
                    "password" => $password
                ));

                return $this->db->lastInsertId();
            }
            catch(PDOException $e){
                die("There was a problem inserting user!");
            } 
        }

        /**
         * updateUser
         */
        // function updateUser($data){
        //     try{
        //         $query = "UPDATE attendee SET ";
        //         // $items = array();
        //         // $types = "";
        //         $updateId = 0; 
        //         // $numRows = 0;

        //         // foreach($data as $k => $v){
        //         //     switch($k){
        //         //         case "name":
        //         //             $query .= "name = ?,";
        //         //             $items[] = &$v;
        //         //             $types .= "s";
        //         //             break;
        //         //         case "password":
        //         //             $query .= "password = ?,";
        //         //             $items[] = &$v;
        //         //             $types .= "s";
        //         //             break;
        //         //         case "role":
        //         //             $query .= "role = ?,";
        //         //             $items[] = intval($v);
        //         //             $types .= "i";
        //         //             break;
        //         //         case "id":                      // going to pass in id too
        //         //             $updateId = intval($k);
        //         //             break;
        //         //     }
        //         // }

        //         // $query = trim($query, ",");
        //         // $query .= " WHERE idattendee = ?";
        //         // $types .= "i"; // for the above where question mark 
        //         // $items[] = &$updateId;
    
        //         // if($stmt = $this->dbh->prepare($query)){
        //         //     // marge items and types
        //         //     $refArr = array_merge(array($types), $items);
        //         //     $ref = new ReflectionClass('mysqli_stmt');
        //         //     $method = $ref->getMethod("bind_param");
        //         //     $method->invokeArgs($stmt, $refArr);
    
        //         //     $stmt->execute();
        //         //     $stmt->fetchAll();
        //         //     $numRows = $stmt->affected_rows;
        //         // }            
    
        //         // return $numRows;


        //         $updateArr = array();
        //         foreach($data as $k => $v){
        //             switch($k){
        //                 case "name":
        //                     $query .= "name = ?,";
        //                     $updateArr[] = array("name" => &$v);
        //                     break;
        //                 case "password":
        //                     $query .= "password = ?,";
        //                     $updateArr[] = array("password" => &$v);
        //                     break;
        //                 case "role":
        //                     $query .= "role = ?,";
        //                     $updateArr[] = array("role" => &$v);
        //                     break;
        //                 case "id":    
        //                     $updateId = intval($k);
        //                     break;
        //             }
        //         }
        //         $query = trim($query, ",");
        //         $query .= " WHERE idattendee = ?";
        //         $updateArr[] = array("id" => $updateId);


        //         $stmt = $this->db->prepare($query); 
        //         $stmt->execute($updateArr); // array w/values to binds

        //         return $stmt->affected_rows; // return the # of rows affected
        //     }
        //     catch(PDOException $e){
        //         die("There was a problem updating user!");
        //     } 
        // }


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



        /** EVENTS */
        /**
         * getAllEvents
         * Retrieves all available events
         */
        function getAllEvents(){
            try{
                include_once("./classes/Event.class.php");

                $data = array();
                $query = 'SELECT * FROM event';
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
    }

