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
                // $data = array();
                $stmt = $this->db->prepare("SELECT * FROM attendee WHERE name = :name AND password = :password");

                $stmt->execute(array(
                    ":name" => $username,
                    ":password" => $password
                ));
                // $data = $stmt->fetchAll();

                // Check to see if row was returned, if not then login failed
                if($stmt->rowCount() > 0){
                    return $stmt->rowCount();
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
    }

