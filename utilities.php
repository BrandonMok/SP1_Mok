<?php 
    require_once("DB.class.php");
    $db = new DB(); // One DB object to use 


    /**
     * reusableLinks
     * Reusable tags to common links (i.e. css, fontawesome, fonts, etc..)
     */
    function reusableLinks() {
        $headLinks = "<link rel='stylesheet' href='./assets/css/styles.css'>
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
        if(isset($_SESSION['role']) && !empty($_SESSION['role'])){
            $role = $_SESSION['role'];
            if($role == "admin" || $role == "event_manager"){
                // ADMIN + EVENT_MANAGER have access to the admin page w/all other pages
                // EVENT_MANAGER has limited actions on the admin page though
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



    function userManagementForm(){
        global $db;

        if(isset($_GET['action'])){
            if($_GET['action'] == "edit"){
                $user = $db->getUser($_GET['id'])[0]; 
                $editForm = "<div id='account-form-container'>
                                <form id='user-edit-form' name='user-edit-form' action='./accountManagement.php?id={$user->getIdAttendee()}&action=edit' method='POST'>
                                    <div id='user-edit-labels'>
                                        <label>ID</label><br/>
                                        <label>Name</label><br/>
                                        <label>Password</label><br/>
                                        <label>Role</label><br/>                                                   
                                    </div>
                                    <div id='user-edit-inputs'>
                                        <input type='text' name='id' value='{$user->getIdAttendee()}' readonly='readonly'><br/>
                                        <input type='text' name='name' value='{$user->getName()}'><br/>
                                        <input type='text' name='password'><br/>";

                
                // Don't let admin to change roles -> NEED to have a SUPERADMIN account
                if($user->getRole() == "1"){
                    $editForm .= "<input type='text' name='role' value='{$user->getRole()}' readonly='readonly'><br/></div>";
                }
                else{
                    $editForm .= "<input type='text' name='role' value='{$user->getRole()}'><br/></div><br/>";
                }


                $editForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                
                echo $editForm;
            }
            else if($_GET['action'] == "add"){
                $addForm = "<div id='account-form-container'>
                            <form id='user-edit-form' name='user-edit-form' action='./accountManagement.php?&action=add' method='POST'>
                                    <div id='user-edit-labels'>
                                        <label>ID</label><br/>
                                        <label>Name</label><br/>
                                        <label>Password</label><br/>
                                        <label>Role</label><br/>                                                   
                                    </div>
                                    <div id='user-edit-inputs'>
                                        <input type='text' name='id' readonly='readonly' placeholder='Auto-increment'><br/>
                                        <input type='text' name='name'><br/>
                                        <input type='text' name='password'><br/>
                                        <input type='text' name='role'><br/>
                                    </div><br/>";

                $addForm .= "<input name='submit' id='submit-btn' type='submit' value='Submit'/></form></div>";
                                
                echo $addForm;
            }
        }
    }

    /** 
     * editFormPOST()
     * POST processing after clicking submit on the edit page
     */
    function editFormPOST(){
        global $db;
        $name = sanitizeString($_POST["name"]);
        $password = sanitizeString($_POST["password"]);
        $role = sanitizeString($_POST["role"]);

        $specificUser = $db->getUser($_GET["id"])[0]; 
        
        $changesArray = array();
    
        if(!empty($name) && isset($name) && $name != $specificUser->getName()){
            $changesArray["name"] = $name;
        }
        if(!empty($password) && isset($password) && $password != ""){
            $password = hash('sha256', $password);
            $changesArray["password"] = $password;
        }
        if(!empty($role) && isset($role) && $role != $specificUser->getRole()){
            if($role != $specificUser->getRole()){
                switch($role){
                    case 1: 
                    case "admin":
                        $changesArray["role"] = 1;
                        break;
                    case 2:
                    case "event_manager":
                    case "event manager":
                        $changesArray["role"] = 2;
                        break;
                    case 3:
                    case "attendee":
                        $changesArray["role"] = 3;
                        break;
                }
            }
        }

        // If changes were made!
        if(!empty($changesArray)){
            $changesArray["id"] = $specificUser->getIdAttendee();
            $rowCount = $db->updateUser($changesArray);

            if($rowCount > 0){
                header("Location: admin.php");
                exit;
            }
            else{
                echo "<p class='form-error-text '>** Editing user failed!</p>";
            }
        }
        else{
            echo "<p class='form-error-text '>** No changes made to user!</p>";
        }
    }


    /**
     * addFormPOST()
     * POST processing after clicking submit on ADD user form
     */
    function addFormPOST(){
        global $db;
        $name = sanitizeString($_POST["name"]);
        $password = sanitizeString($_POST["password"]);
        $role = sanitizeString($_POST["role"]);

        $changesArray = array();
    
        if(!empty($name) && isset($name)){
            $changesArray["name"] = $name;
        }
        if(!empty($password) && isset($password) && $password != ""){
            $password = hash('sha256', $password);
            $changesArray["password"] = $password;
        }
        if(!empty($role) && isset($role)){
            switch($role){
                case 1: 
                case "admin":
                    $changesArray["role"] = 1;
                    break;
                case 2:
                case "event_manager":
                case "event manager":
                    $changesArray["role"] = 2;
                    break;
                case 3:
                case "attendee":
                    $changesArray["role"] = 3;
                    break;
            }
        }

        // If changes were made!
        if(!empty($changesArray)){
            $rowCount = $db->insertUser($changesArray["name"], $changesArray["password"], $changesArray["role"]);

            if($rowCount > 0){
                header("Location: admin.php");
                exit;
            }
            else{
                echo "<p class='form-error-text '>** Editing user failed!</p>";
            }
        }
        else{
            echo "<p class='form-error-text '>** No changes made to user!</p>";
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