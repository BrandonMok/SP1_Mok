<?php 
    require_once('DB.class.php');

    $db = new DB(); // One DB object to use 

    /**
     * reusableLinks
     * Reusable tags to common links (i.e. css, fontawesome, fonts, etc..)
     */
    function reusableLinks() {
        $headLinks = '<link rel="stylesheet" href="./assets/css/styles.css">
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" 
        integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">';
        echo $headLinks;
    }


    /**
     * reusableHeader
     */
    function reusableHeader(){
        $headerSTR = '<header></header>';
        echo $headerSTR;
    }
    /**
     * reusableHeader2
     * Contains a logout option once logged int
     */
    function reusableHeader2(){
        $headerSTR = '<header>
                        <ul class="nav">
                            <li>
                                <a href="logout.php">Logout  <i class="fas fa-sign-out-alt"></i></a>
                            </li>
                        </ul>
                    </header>';
        echo $headerSTR;
    }

    /**
     * reusableFooter
     */
    function reusableFooter(){
        $footerSTR = '<footer></footer>';
        echo $footerSTR;
    }






    /**
     * End the session
     */
    function endSession(){
        unset($_SESSION['userLoggedIn']);
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