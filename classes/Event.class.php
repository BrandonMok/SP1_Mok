<?php
    class Event {
        private $idevent;
        private $name;
        private $datestart;
        private $dateend;
        private $numberallowed;
        private $venue;

        function getIdEvent(){return $this->idevent;}
        function getName(){return $this->name;}
        function getDateStart(){return $this->datestart;}
        function getDateEnd(){return $this->dateend;}
        function getNumberAllowed(){return $this->numberallowed;}
        function getVenue(){return $this->venue;}

        function setIdEvent($value){
            $this->idevent = $value;
        }
        function setName($value){
            $this->name = $value;
        }
        function setDateStart($value){
            $this->datestart = $value;
        }
        function setDateEnd($value){
            $this->dateend = $value;
        }
        function setNumberAllowed($value){
            $this->numberallowed = $value;
        }
        function setVenue($value){
            $this->venue = $value;
        }
    }
?>