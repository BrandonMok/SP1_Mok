<?php
    class Role {
        private $idrole;
        private $name;

        function getIdRole(){ return $this->idrole; }
        function getName(){ return $this->name; }

        function setIdRole($value){
            $this->idrole = $value;
        }
        function setName($value){
            $this->name = $value;
        }
    }
?>