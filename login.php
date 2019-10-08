<?php
    session_name("Mok_Project1");
    session_start();

    require_once("DB.class.php");
    require_once("utilities.php");


    // User logged in already, just redirect to events
    if(!empty($_SESSION['userLoggedIn'])){
        if($_SESSION['userLoggedIn']  == false){
            endSession(); // call function to end the session
        }
        else{
            header('Location: events.php');
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            reusableHeader();

        ?>
        <h1 class="section-heading">Login</h1>

        <!-- Login form -->
        <div id="login-form-container">
            <form id="login-form" name="loginForm" action="./login.php" method="POST">
                <label>Name:</label>
                <input type="text" name="name" maxlength="50" placeholder="Name"/><br/>
                <label>Password:</label>
                <input type="password" name="password" placeholder="Password"/><br />
                <input name="submit" id="submit-btn" type="submit" value="Submit"/>
            </form>

            <div id="new-user-register">
                <a href="./accountRegistration.php">New User?</a>
            </div>


            <?php
                // When user submits form - verify user logged in correclty
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    if(!empty($_POST['name']) && !empty($_POST['password'])){
                        if(isset($_POST['name']) && isset($_POST['password'])){
                            $uName = sanitizeString($_POST['name']);                        // sanitize
                            $password = hash('sha256', sanitizeString($_POST['password'])); // Sanitize + hash password!

                            $userCheck = $db->verifyUser($uName, $password);    // verify user (-1 if not found or 1 if found)

                            $rows = $userCheck['rowCount'];             // rowcount
                            $role = $userCheck['currentUser']['role'];  // role
                            $name = $userCheck['currentUser']['name'];  // name


                            // Only proceed if rows returned (aka found the user)
                            if($rows > 0){
                                $_SESSION['userLoggedIn'] = true;  // set session variable
                                $_SESSION['currentUSR'] = $name;
                                
                                // Switch to determine role -> store as session variable
                                switch($role){
                                    case '1':
                                        $_SESSION['role'] = 'admin';
                                        break;
                                    case '2':
                                        $_SESSION['role'] = 'event_manager';
                                        break;
                                    case '3':
                                        $_SESSION['role'] = 'attendee';
                                        break;
                                    default:    
                                        $_SESSION['role'] = 'attendee'; 
                                        break;
                                }


                               header("Location: events.php");
                               exit;
                            }
                            else{
                                // Invalid Login
                                echo "<p class='form-error-text'>**Invalid Login. Please try again!</p>";
                            }
                        }
                        else{
                            // Invalid login
                            echo "<p class='form-error-text'>**Invalid Login. Please try again!</p>";
                        }
                    }
                    else {
                        // Invalid Login - empty input
                        echo "<p class='form-error-text'>* Please enter valid Login!</p>";
                    }
                }// end if
            ?>
        </div>
    </body>
</html>