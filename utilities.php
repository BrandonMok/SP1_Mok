<?php 
    require_once("DB.class.php");
    $db = new DB(); // One DB object to use 

    /**
     * reusableLinks
     * Reusable tags to common links (i.e. css, fontawesome, fonts, etc..)
     */
    function reusableLinks() {
        $headLinks = "<meta charset='UTF-8'>
                        <meta name='google' content='notranslate'>
                        <meta http-equiv='Content-Language' content='en'>
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

    
    // function adminTables($data){
    //     echo "<p class='section-heading'>{$data["class"]}</p>";
    //     echo "<a href={$data['addURL']}>
    //             <div class='add-btn'>Add {$data["class"]}</div>
    //         </a>";

    //     // $data will be an array w/alot of necessary data
    //     $table = "<div class='admin-table-container'>
    //                 <table class='admin-table'>
    //                     <tr>";
    //     foreach($data["th"] as $v){
    //         $table .= "<th>{$v}</th>";
    //     }
    //     $table .= "</tr>";

    //     $class = $data["class"];
    //     include_once("./classes/{$class}.class.php");


    //     foreach($data['data'][0] as $v){
    //         $table .= "<tr>";
    //                 foreach($data["dataMethods"] as $method){
    //                     $methodResults = call_user_func_array(array($class, $method), array());
    //                     $table .= "<td>{$methodResults}</td>";
    //                 }
    //         $table .= "<td><a href='{$data['editURL']}'>Edit</a></td>
    //                     <td><a href='{$data['deleteURL']}'>Delete</a></td>
    //                 </tr>";
                 
    //     }
    //     $table .= "</table></div>";
    //     echo $table;
    // }

    
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
                        header("Location: admin.php");
                        exit;
                    }
                    else{
                        echo "<p class='form-error-text '>** Editing {$fields['area']} failed!</p>";
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
                        }
                    }
                    else {
                        if($type == "i"){
                            if(is_numeric($value) && intval($value) >= 0){  // check if is numeric and is greater than 0
                                $paramArr[$k] = intval($value);
                            }
                            else {
                                $flag = false;
                                $msg = "<p class='form-error-text center-element'>Invalid input!</p>";
                            }
                        }
                        else if($type == "s"){
                            if(alphaNumeric($value)){
                                $paramArr[$k] = $value;
                            }
                            else {
                                $flag = false;
                                $msg = "<p class='form-error-text center-element'>Invalid input!</p>";
                            }
                        }
                    }
                }// end foreach
            }// end foreach


            if(!empty($paramArr) && $flag == true){
                $rowCount = call_user_func_array(array($db, $fields["method"]["add"]), array($paramArr));

                if($rowCount > 0){
                    header("Location: admin.php");
                    exit;
                }
                else{
                    echo "<p class='form-error-text'>** Adding {$fields['area']} failed!</p>";
                }
            }
            else {
                // error occured with formats
                echo $msg;
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
                    $delete = call_user_func_array(array($db, $data["method"]["delete"]), array($data["fields"]["id"]));


                    if($delete > 0){ // if rowcount wasn't 0 -> delete user
                        header("Location: admin.php");
                        exit;  
                    }
                    else{
                        // ERROR w/the delete occured
                        echo "<h2>Deleting selected {$data["area"]} failed!</h2>";
                    }
                }
                else{
                    // user chose NO to deleting user
                    header("Location: admin.php");
                    exit;
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
            case "login":
                header("Location: login.php");
                exit;
        }
    }



    /**
     * End the session
     */
    function endSession(){
        unset($_SESSION['userLoggedIn']);   // unset login session variable
        unset($_SESSION['role']);           // unset role variable
        unset($_SESSION['currentUSR']);     // unset current user's name
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