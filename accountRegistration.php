<?php 
    session_name("Mok_Project1");
    session_start();

    require_once('DB.class.php');
    require_once('utilities.php');

    // $db = new DB(); // DB object

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Account Registration</title>
        <?php
            reusableLinks();
        ?>
    </head>
    <body>
        <?php 
            // Header
            reusableHeader(); 
        ?>
        <h1 class="section-heading">Register</h1>

        <div id="account-register-container">
            <form name="acountRegisterForm" id="account-register-form" action="./accountRegistration.php" method="POST">
                <label>Name:</label>
                <input type="text" name="registerName" maxlength="50" placeholder="Name"/><br/>
                <label>Password:</label>
                <input type="text" name="registerPassword" placeholder="Password"/><br />
                <input name="submit" id="submit-btn" type="submit" value="Submit"/>
            </form>

            <?php
                // Don't allow an already logged in user to make another account
                if(isset($_SERVER['userLoggedIn'])){
                    header('Location: events.php');
                    exit;
                }

                if($_SERVER["REQUEST_METHOD"] == "POST"){
                    if(!empty($_POST['registerName']) && !empty($_POST['registerPassword'])){
                        if(isset($_POST['registerName']) && isset($_POST['registerPassword'])){
                            $uName = sanitizeString($_POST['registerName']);                        // sanitize
                            $password = hash('sha256', sanitizeString($_POST['registerPassword'])); // Sanitize + hash password!

                            // Search if user exists - in case of registration, don't want the account to exist in order to create it
                            // verify user (-1 if not found or 1 if found)
                            $userCheck = $db->verifyUser($uName, $password);   
        
                            
                            if($userCheck > 0){
                                // Display account exists MSG
                                echo "<p class='form-error-text'>**You already have an account!</p>";
                            }
                            else{
                                // Able to make a new account
                                $id = $db->insertUser($uName, $password);
                                if($id > 0){
                                    // successfully made a new account
                                    // display succesfull MSG
                                    
                                    // make custom full screen to display account created successfully?
                                    // white rounded box
                                    // green check mark
                                    // txt underneath
                                    // sleep()

                                    echo "<p class='form-success-text'>Succesfully registered<i class='far fa-thumbs-up'></i></p>";
                                    

                                    header('Location: login.php');
                                    exit;
                                }
                                else{
                                    // ERROR: failed to insert new user
                                    echo "<p class='form-error-text'>**Failed to create account!</p>";
                                }
                            }
                        }
                        else{
                            // ERROR Need all inputs to have value
                            echo "<p class='form-error-text'>*Please enter a valid name and password!</p>";
                        }
                    }
                    else {
                        // ERROR - Require user inputs
                        echo "<p class='form-error-text'>* Please enter a valid name and password!</p>";
                    }
                }// end if
            ?>

            <div id="back-arrow-container">
                <a href="login.php">
                    <i class="fas fa-long-arrow-alt-left fontawesome-icons"></i>
                </a>
            </div>
        </div>

        <?php
            // Footer
            reusableFooter();
        ?>  
    </body>
</html>