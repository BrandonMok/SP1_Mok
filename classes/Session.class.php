<?php
    class Session {
        private $idsession;
        private $name;
        private $numberallowed;
        private $event;
        private $startdate;
        private $enddate;

        function getIdSession(){return $this->idsession;} 
        function getName(){return $this->name;}
        function getNumberAllowed(){return $this->numberallowed;}
        function getEvent(){return $this->event;}
        function getStartDate(){return $this->startdate;}
        function getEndDate(){return $this->enddate;}

        function setIdSession($value){
            $this->idsession = $value;
        }
        function setName($value){
            $this->name = $value;
        }
        function setNumberAllowed($value){
            $this->numberallowed = $value;
        }
        function setEvent($value){
            $this->event = $value;
        }
        function setStartDate($value){
            $this->startdate = $value;
        }
        function setEndDate($value){
            $this->enddate = $value;
        }
    }
?>