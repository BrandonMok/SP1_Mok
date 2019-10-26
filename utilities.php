<?php 
    require_once("DB.class.php");
    require_once("validations.php");
    $db = new DB(); // One DB object to use 

    /**
     * reusableLinks
     * Reusable tags to common links (i.e. css, fontawesome, fonts, etc..)
     */
    function reusableLinks() {
        $headLinks = "<meta charset='UTF-8'>
                        <meta name='google' content='notranslate'>
                        <link rel='stylesheet' href='./assets/css/styles.css'>
                        <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap' rel='stylesheet'>
                        <link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.8.1/css/all.css' 
                        integrity='sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf' crossorigin='anonymous'>";
        echo $headLinks;
    }


    /**
     * reusableHeader
     * Based on role to display available pages
     */
    function reusableHeader(){
        // Based on roles
        if(isset($_SESSION["role"]) && !empty($_SESSION["role"])){
            $role = $_SESSION["role"];
            if($role == "admin" || $role == "event_manager"){
                $headerSTR = "<header>
                                <span class='user-welcome'>Hi ".$_SESSION['currentUSR']."</span>
                                <ul class='nav'>
                                    <li>
                                        <a href='admin.php'>Admin</a>
                                    </li>
                                    <li>
                                        <a href='events.php'>Events</a>
                                    </li>
                                    <li>
                                        <a href='registrations.php'>Registrations</a>
                                    </li>
                                    <li>
                                        <a href='logout.php'>Logout  <i class='fas fa-sign-out-alt'></i></a>
                                    </li>
                                </ul>
                            </header>"; 
                echo $headerSTR;
            }
            else {
                // attendee header
                $headerSTR = "<header>
                                <span class='user-welcome'>Hi ".$_SESSION['currentUSR']."</span>
                                <ul class='nav'>
                                    <li>
                                        <a href='events.php'>Events</a>
                                    </li>
                                    <li>
                                        <a href='registrations.php'>Registrations</a>
                                    </li>
                                    <li>
                                        <a href='logout.php'>Logout  <i class='fas fa-sign-out-alt'></i></a>
                                    </li>
                                </ul>
                            </header>";             
                echo $headerSTR;
            }
        }
        else{
            // no role, so in login or registration page
            $headerSTR = "<header></header>";
            echo $headerSTR;
        }
    }


    /**
     * reusableAddActionHTML
     * @param $data
     * $data = array();
     * $data['area'] = area
     * $data['formAction'] = URL string
     * $data['label'] = array(lbl1,lbl2,lbl3,...)
     * $data["input"] = array("key" => array("name" => value, "readonly" => readonly, etc..), "key" => array(), ...)
     * Reusable function to produce the code for the ADD action for admin tables
     */
    function addActionHTML($data){
        if($_GET["action"] == "add"){
            echo "<h2 class='section-heading'>Add {$data['area']}</h2>";
            $addForm = "<div class='edit-add-form-container'>
                            <form id='user-edit-form' name='user-edit-form' action={$data['formAction']} method='POST'>
                            <div id='user-edit-labels'>";
            // Create table headers
            foreach($data["labels"] as $v){
                $addForm .= "<label>{$v}</label>";
            }
            $addForm .= "</div>
                        <div id='user-edit-inputs'>";

            // Create table inputs
            foreach($data["input"] as $key => $value){
                if($key == "id"){
                    $addForm .= "<input type='text' name={$value["name"]} readonly={$value["readonly"]} placeholder='{$value["placeholder"]}'>";
                }
                else if ($key == "datestart" || $key == "dateend"){
                    $addForm .= "<input type='text' name={$value["name"]} placeholder='{$value["placeholder"]}'>";
                }
                else {
                    $addForm .= "<input type='text' name={$value["name"]}>";
                }
            }// end foreach
            $addForm .= "</div><br/>
                        <input name='submit' id='submit-btn' type='submit' value='Submit'/>
                        </form>
                    </div>";
            echo $addForm;
        }// end if ADD
    }// end function

    /**
     * confirmDeleteHtml
     * @param $data
     * Reusable function for delete action in management files
     * Displays the HTML relevant for the object to delete
     */
    function confirmDeleteHtml($data){
        $container = "<h2 class='section-heading'>Delete {$data['area']}</h2>";
        $container .= "<div class='admin-table-container'> 
                            <table class='admin-table'>
                                <tr>";
        foreach($data["th"] as $th){
            $container .= "<th>{$th}</th>";
        }
        $container .= "</tr>
                        <tr>";

        foreach($data["td"] as $td){
            $container .= "<td>{$td}</td>";
        }
        $container .= "</tr></table></div>";
        $container .= "<h2 class='section-heading'>Are you sure you want to delete the selected ". strtolower($data["area"]) ."?</h2><br/>";
        $container .= "<div id='confirm-delete-container' class='center-element'>
                            <a href='{$data['choices']['confirm']}'>
                                <div class='delete-btn' id='confirm-delete-btn'>Yes</div>
                            </a>
                            <a href='{$data['choices']['cancel']}'>
                                <div class='delete-btn' id='deny-delete-btn'>No</div>
                            </a>
                        </div>";
        echo $container;
    }

    
    /**
     * editPost
     * @param $fields
     * $fields = array();
     * $fields['area'] = string
     * $fields['fields'] = array(str, str, str, ...); 
     * $fields['method'] = array(string of method name);
     * $fields['originalValues'] = array(obj as associative array);
     * Reusuable POST handling for admin form submits
     */
    function editPost($fields){
        global $db;
        if($_GET["action"] == "edit"){
            $changesArray = array();

            foreach($fields["fields"] as $k => $v){
                foreach($fields["originalValues"] as $key => $value){
                    if($k == $key){
                        if(!empty($v) && isset($v) && $v != $value){
                            $changesArray[$k] = $v;
                        }
                    }
                }
            }

            $changesArray["id"] = $fields["fields"]["id"]; // set ID for where cause

            if(!empty($changesArray)){
                $rowCount = call_user_func_array(array($db, $fields["method"]["update"] ), array($changesArray));

                if($rowCount > 0){
                    redirect("admin");
                }
                else{
                    // echo "<p class='form-error-text '>** Editing {$fields['area']} failed!</p>";
                    errorDisplay("Editing {$fields['area']} failed!");
                }
            }
        }
    }


    /**
     * addPost
     * @param $fields
     * $fields = array();
     * $fields['area'] = string
     * $fields['fields'] = array("key" => array("type" => value, "value" => value), "key" => array(), ...) 
     * $fields['method'] = array(string of method name);
     */
    function addPost($fields){
        global $db;
        if($_GET["action"] == "add"){
            $type = "";
            $flag = true;
            $msg = "";
            $paramArr = array();

            /**
             * Key:
             * "i" = Integer
             * "s" = String
             * "sn" = String w/numbers
             * "date" = date
             */

            foreach($fields["fields"] as $k => $v){
                foreach($fields["fields"][$k] as $key => $value){
                    // if the key == type
                    if($key == "type"){
                        switch($value){
                            case "i":
                                $type = "i";
                                break;
                            case "s":
                                $type = "s";
                                break;
                            case "sn":
                                $type = "sn";
                                break;
                            case "date":
                                $type = "date";
                                break;
                        }
                    }
                    else {
                        if($type == "i"){
                            if(is_numeric($value) && intval($value) >= 0){  // check if is numeric and is greater than 0
                                $paramArr[$k] = intval($value);
                            }
                            else {
                                $flag = false;
                                $msg = "Invalid input!";
                            }
                        }
                        else if($type == "sn"){
                            // SN = String Number (String w/numbers are allowed)
                            if(alphaNumeric($value) || alphaNumericSpace($value)){
                                $paramArr[$k] = $value;
                            }
                            else {
                                $flag = false;
                                $msg = "Invalid input!";
                            }
                        }
                        else if($type == "s"){      
                            // s = String
                            if(alphabetic($value) || alphabeticSpace($value)){
                                $paramArr[$k] = $value;
                            }
                            else {
                                $flag = false;
                                $msg = "Invalid input!";
                            }
                        }
                        else if($type == "date"){
                            // Only accepting date-time format
                            if(date3($value)){
                                $paramArr[$k] = $value;
                            }
                            else{
                                $flag = false;
                                $msg = "Invalid input!";
                            }
                        }
                    }
                }// end foreach
            }// end foreach


            // Perform add/insert if values were added and flag wasn't flipped
            if(!empty($paramArr) && $flag == true){
                $lastID = call_user_func_array(array($db, $fields["method"]["add"]), array($paramArr));

                if($lastID > 0){
                    return $lastID;
                }
                else{
                    // echo "<p class='form-error-text'>** Adding {$fields['area']} failed!</p>";
                    errorDisplay("Adding {$fields['area']} failed!");
                }
            }
            else {
                // error occured with formats
                echo errorDisplay($msg);
            }
        }
    }


    /**
     * deleteAction
     * @param $data
     * $data = array();
     * $data["area"] = string of area
     * $data["fields"] = array("key" => "value")
     * $data["method"] = array();
     */
    function deleteAction($data){
        global $db;
        if(isset($_GET["action"]) && $_GET["action"] == "delete"){
            if(isset($_GET["confirm"]) && !empty($_GET["confirm"])){
                $decision = $_GET["confirm"];

                if($decision == "yes"){
                    if(isset($data["fields"]["attendee"])){
                        // CASE: For attendeeEvent delete need both fields
                        $delete = call_user_func_array(array($db, $data["method"]["delete"]), array($data["fields"]["id"], $data["fields"]["attendee"]));
                    }
                    else {
                        $delete = call_user_func_array(array($db, $data["method"]["delete"]), array($data["fields"]["id"]));
                    }

                    if($delete > 0){ // if rowcount wasn't 0 -> delete user
                        return $delete;
                    }
                    else{
                        // ERROR w/the delete occured
                        echo "<h2>Deleting selected {$data["area"]} failed!</h2>";
                    }
                }
                else{
                    // user chose NO to deleting user
                    redirect("admin");
                }
            }
        }
    }

    /**
     * managementAddCheck
     * Checks to see if the action in URL is set - used to double check for add action
     */
    function managementAddCheck(){
        if(isset($_GET["action"]) && !empty($_GET["action"])){
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * managementEditDeleteCheck
     * Checks to see if id and action were set - EDIT and DELETE require both
     */
    function managementEditDeleteCheck(){
        if(isset($_GET["id"]) && !empty($_GET["id"]) && isset($_GET["action"]) && !empty($_GET["action"])){
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * eventManagerEventCheck
     * @param $eventID
     * Checks to see if the event trying to associate the event manager exists
     */
    function eventManagerEventCheck($eventID){
        global $db;
        // CHECK: If event exists
        if(count($db->getEvent($eventID)) > 0){
            // If event exists as the last ID passed is greated than 0
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * redirect
     * @param $page
     * Reusable redirect function that accepts the page name to redirect to
     */
    function redirect($page){
        switch($page){
            case "admin":
                header("Location: admin.php");
                exit;
            case "events":
                header("Location: events.php");
                exit;
            case "registrations":
                header("Location: registrations.php");
                exit;
            case "login":
                header("Location: login.php");
                exit;
        }
    }

    /**
     * adminAddBtns
     * @param $data
     * $data['url'] = url
     * $data['area'] = area
     * Reusable function to create the "ADD {insert area here}" button to create new object
     */
    function adminAddBtns($data){
        echo "<a href={$data['url']}>
                <div class='add-btn'>Add {$data['area']}</div>
            </a>";
    }

    /**
     * errorDisplay
     * @param $msg
     * Reusable function to display error messages to user (i.e. invalid input)
     */
    function errorDisplay($msg){
        echo "<p class='form-error-text'>** {$msg}</p>";
    }

    /**
     * notIssetEmptyCheck
     * @param $data
     * Reusable function to check if entered fields are empty or !isset
     */
    function notIssetEmptyCheck($data){
        $valid = true;
        foreach($data as $value){
            if(!isset($value) || empty($value)){
                $valid = false;
                break;
            }
        }
        return $valid;
    }

    /**
     * roleCheck
     * @param $role
     * Check the role of a user allowing both text & number to be entered
     */
    function roleCheck($role){
        $assignedRole = "";

        switch($role){
            case 1: 
            case "admin":
                $assignedRole = 1;
                break;
            case 2:
            case "event_manager":
            case "event manager":
                $assignedRole = 2;
                break;
            case 3:
            case "attendee":
                $assignedRole = 3;
                break;
        }//end switch

        if(!isset($assignedRole) || empty($assignedRole)){
            $assignedRole = -1;
        }
        return $assignedRole;
    }


    /**
     * End the session
     */
    function endSession(){
        foreach($_SESSION as $sessionVar){
            unset($sessionVar);
        }
        unset($_SESSION);               
        
        if(isset($_COOKIE[session_name()])){
            unset($_COOKIE[session_name()]);        // unset session cookie
            setcookie(session_name(), '', 1, '/');  // set session cookie to expire by browser
        }
        session_destroy();
    }

    /**
     * sanitizeString
     * Sanitizes user inpur - used in login form
     */
    function sanitizeString($value){
		$value = trim($value);
        $value = stripslashes($value);
        $value = strip_tags($value);
		$value = htmlentities($value);
		return $value;
    }
?>