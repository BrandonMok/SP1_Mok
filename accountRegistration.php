<?php 
    session_name("Mok_Project1");
    session_start();

    require_once("utilities.php");
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
        <h1 class="section-heading">Register <br/><i class="fas fa-address-book"></i></h1>
        


        <div id="account-register-container">
            <form name="acountRegisterForm" id="account-register-form" action="./accountRegistration.php" method="POST">
                <label>Name:</label>
                <input type="text" name="registerName" maxlength="50" placeholder="Name"/><br/>
                <label>Password:</label>
                <input type="password" name="registerPassword" placeholder="Password"/><br />
                <input name="submit" id="submit-btn" type="submit" value="Submit"/>
            </form>

            <?php
                // Don't allow an already logged in user to make another account
                if(isset($_SERVER["userLoggedIn"])){
                    header('Location: events.php');
                    exit;
                }

                if($_SERVER["REQUEST_METHOD"] == "POST"){
                    if(!empty($_POST["registerName"]) && !empty($_POST["registerPassword"])){
                        if(isset($_POST["registerName"]) && isset($_POST["registerPassword"])){
                            $uName = sanitizeString($_POST["registerName"]);                        // sanitize
                            $password = hash('sha256', sanitizeString($_POST["registerPassword"])); // Sanitize + hash password!

                            // Search if user exists - in case of registration, don't want the account to exist in order to create it
                            // verify user (-1 if not found or 1 if found)
                            $userCheck = $db->verifyUser($uName, $password);   
        
                            
                            if($userCheck > 0){
                                // Display account exists MSG
                                errorDisplay("You already have an account!");
                            }
                            else{
                                // Able to make a new account
                                $newUser = array();
                                $newUser["name"] = $uName;
                                $newUser["password"] = $password;
                                $newUser["role"] = 3; // set to attendee, only allow admin to change user account roles

                                // $id = $db->insertUser($uName, $password);
                                $id = $db->insertUser($newUser);

                                if($id > 0){
                                    // make custom full screen to display account created successfully?
                                    // white rounded box
                                    // green check mark
                                    // sleep()

                                    echo "<p class='form-success-text'>Succesfully registered<i class='far fa-thumbs-up'></i></p>";

                                    header("Location: login.php");
                                    exit;
                                }
                                else{
                                    // ERROR: failed to insert new user
                                    errorDisplay("Failed to create account!");
                                }
                            }
                        }
                        else{
                            // ERROR Need all inputs to have value
                            errorDisplay("Please enter a valid name and password!");
                        }
                    }
                    else {
                        // ERROR - Require user inputs
                        errorDisplay("Please enter a valid name and password!");
                    }
                }// end if
            ?>

            <div id="back-arrow-container">
                <a href="login.php">
                    <i class="fas fa-long-arrow-alt-left fontawesome-icons"></i>
                </a>
            </div>
        </div>
    </body>
</html>