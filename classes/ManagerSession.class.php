<?php
    class ManagerSession {
        private $session;
        private $manager;

        function getSession(){return $this->session;}
        function getManager(){return $this->manager;}

        function setSession($value){
            $this->session = $value;
        }
        function setManager($value){
            $this->manager = $value;
        }
    }
?>