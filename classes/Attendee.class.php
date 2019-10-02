<?php
    class Attendee {
        private $idattendee;
        private $name;
        private $password;
        private $role;

        function getIdAttendee(){ return $this->idattendee; }
        function getName(){ return $this->name; }
        function getPassword(){ return $this->password; }
        function getRole(){ return $this->role; }

        function setIdAttendee($value){
            $this->idattendee = $value;
        }
        function setName($value){
            $this->name = $value;
        }
        function setPassword($value){
            $this->password = $value;
        }
        function setRole($value){
            $this->role = $value;
        }
    }
?>