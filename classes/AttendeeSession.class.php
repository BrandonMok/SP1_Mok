<?php
    class AttendeeSession {
        private $session;
        private $attendee;

        function getSession(){return $this->session;}
        function getAttendee(){return $this->attendee;}

        function setSession($value){
            $this->session = $value;
        }
        function setAttendee($value){
            $this->attendee = $value;
        }
    }
?>