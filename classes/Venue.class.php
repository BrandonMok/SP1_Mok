<?php
    class Venue {
        private $idvenue;
        private $name;
        private $capacity;

        function getIdVenue(){return $this->idvenue;}
        function getName(){return $this->name;}
        function getCapacity(){return $this->capacity;}
        
        function setIdVenue($value){
            $this->idvenue = $value;
        }
        function setName($value){
            $this->name = $value;
        }
        function setCapacity($value){
            $this->capacity = $value;
        }
    }
?>