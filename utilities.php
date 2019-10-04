<?php 
    require_once("DB.class.php");
    $db = new DB(); // One DB object to use 
    
    // DON'T WANT USERS TO GO TO THIS PAGE!!
    // if(isset($_SESSION['userLoggedIn'])){
    //     header("Location: events.php");
    //     exit;
    // }
    // else{
    //     header("Location: login.php");
    //     exit;
    // }



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
     * Pass in value for regular header
     * Don't pass in value, use logged in header            // WILL NEED TO ADJUST WHEN FIGURE OUT REQS FOR NAVIGATION (i.e. show all or only show some)
     */
    function reusableHeader($classname = ""){
        if(isset($classname) && !empty($classname)){
            $headerSTR = "<header></header>";
            echo $headerSTR;
        }
        else{
            $headerSTR = "<header>
                            <span class='user-welcome'>Hi ".$_SESSION['currentUSR']."</span>
                            <ul class='nav'>
                                <li>
                                    <a href='logout.php'>Logout  <i class='fas fa-sign-out-alt'></i></a>
                                </li>
                            </ul>
                        </header>";             
            echo $headerSTR;
        }
    }

    /**
     * reusableFooter
     * Pass in a value to use login specific footer
     * Don't pass value, use regular footer and its styling
     */
    function reusableFooter($classname = ""){ 
        if(isset($classname) && !empty($classname)){              
            $footerSTR = "<footer class='footer-login'></footer>";
            echo $footerSTR;
        }
        else{
            $footerSTR = "<footer></footer>";
            echo $footerSTR;
        }
    }




    /**
     * End the session
     */
    function endSession(){
        unset($_SESSION['userLoggedIn']); // unset login session variable
        unset($_SESSION['role']);       // unset role variable
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