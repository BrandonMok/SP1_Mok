<?php
    class ManagerEvent {
        private $event;
        private $manager;

        function getEvent(){return $this->event;}
        function getManager(){return $this->manager;}
        
        function setEvent($value){
            $this->event = $value;
        }
        function setManager($value){
            $this->manager = $value;
        }
    }
?>