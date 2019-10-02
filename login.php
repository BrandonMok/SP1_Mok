<?php
    session_name("Mok_Project1");
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');


    // User logged in already, just redirect to events
    if(!empty($_SESSION['userLoggedIn'])){
        header('Location: events.php');
        exit;
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
            // Header
            reusableHeader();
        ?>
        <h1 class="section-heading">Login</h1>

        <!-- Login form -->
        <div id="login-form-container">
            <form id="login-form" name="loginForm" action="./login.php" method="POST">
                <label>Name:</label>
                <input type="text" name="name" maxlength="50" placeholder="Name"/><br/>
                <label>Password:</label>
                <input type="text" name="password" placeholder="Password"/><br />
                <input name="submit" id="submit-btn" type="submit" value="Submit"/>
            </form>

            <div id="new-user-register">
                <a href="./accountRegistration.php">New User?</a>
            </div>
            <?php
                // When user submits form - verify user logged in correclty
                if($_SERVER["REQUEST_METHOD"] == "POST"){
                    if(!empty($_POST['name']) && !empty($_POST['password'])){
                        if(isset($_POST['name']) && isset($_POST['password'])){
                            $uName = sanitizeString($_POST['name']);                        // sanitize
                            $password = hash('sha256', sanitizeString($_POST['password'])); // Sanitize + hash password!

                            $userCheck = $db->verifyUser($uName, $password);    // verify user (-1 if not found or 1 if found)

                            if($userCheck > 0){
                                $_SESSION['userLoggedIn'] = true;  // set session variable

                                header('Location: events.php');
                                exit;
                            }
                            else{
                                // Invalid Login
                                echo "<p class='form-error-text'>**Invalid credentials. Please try again!</p>";
                            }
                        }
                        else{
                            // Invalid login
                            echo "<p class='form-error-text'>**Invalid credentials. Please try again!</p>";
                        }
                    }
                    else {
                        // Invalid Login - empty input
                        echo "<p class='form-error-text'>* Please enter valid credentials!</p>";
                    }
                }// end if
            ?>
        </div>


        <?php
            // Footer
            reusableFooter();
        ?>  
    </body>
</html>