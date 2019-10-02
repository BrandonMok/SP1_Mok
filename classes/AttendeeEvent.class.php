<?php
    class AttendeeEvent {
        private $event;
        private $attendee;
        private $paid;

        function getEvent(){return $this->event;}
        function getAttendee(){return $this->attendee;}
        function getPaid(){return $this->paid;} // I like this one

        function setEvent($value){
            $this->event = $value;
        }
        function setAttendee($value){
            $this->attendee = $value;
        }
        function setGetPaid($value){
            $this->paid = $value;
        }
    }
?>