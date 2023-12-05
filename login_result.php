<?php include_once("header.php");
session_destroy(); ?>
<?php require("utilities.php") ?>

<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

//connect to db
require_once "config.php";

//initialise variables placeholders
$username = $password = "";
$username_error = $password_error = $login_err = "";

//handle request POST login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // asign the POST form to variables
    $pwd = trim($_POST["password"]);
    $user = trim($_POST["username"]);


    //validation goes here - check if username is empty
    if (empty($user)) {
        $username_error = "Please enter your username";
    } else {
        $username = $user;
    }

    // check if password is empty
    if (empty($pwd)) {
        $password_error = "Please enter your password";
    } else {
        $password = $pwd;
    }

    // Validate credentials - no errors
    if (empty($username_error) && empty($password_error)) {
        // Select from db
        $sql = "SELECT UserID, Username, Password FROM users WHERE Username = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_username);

            // Set binded parameters
            $param_username = $username;

            // Try to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if username exists, if yes then verify password
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    // uncomment when every fake password is hashed
                    $stmt->bind_result($userId, $username, $hashed_password);
                    // $stmt->bind_result($userId, $username, $userpassword);
                    $stmt->bind_result($userId, $username, $hashed_password);
                    // $stmt->bind_result($userId, $username, $userpassword);

                    if ($stmt->fetch()) {
                        // verify pwd
                        // uncomment when every fake password is hashed
                        if (password_verify($password, $hashed_password)) {
                        // if ($password == $userpassword) {

                            ///////////////// THIS BREAKS THE CODE WHY????????? //////////
                            session_start();
                            // // Store data in session variables
                            $_SESSION["logged_in"] = true;
                            $_SESSION["id"] = $userId;
                            $_SESSION["username"] = $username;

                            // need to set the account_type by checking the role of the user from userrole table
                            $rolessql = "SELECT roles.rolename FROM userrole INNER JOIN roles ON userrole.RoleID = roles.RoleID WHERE userrole.UserID = $userId;";
                            $result = $mysqli->query($rolessql);
                            if ($result) {
                                // Check if any rows were returned
                                if ($result->num_rows > 0) {
                                    $roles = array();

                                    //Get the results into array
                                    while ($row = $result->fetch_assoc()) {
                                        $roles[] = $row;
                                    }

                                    //check what roles it is and assign the right session if it's one it is just a buyer if 2 it is both
                                    if (count($roles) == 1) {
                                        $_SESSION['account_type'] = "buyer";
                                    } else {
                                        $_SESSION['account_type'] = "buyer_seller";
                                    }
                                }
                                // clean up the result
                                $result->free();
                            }


                            echo ('<p class="not-found">You are now logged in! You will be redirected shortly.</p>');

                            // Redirect to index after 5 seconds
                            header("refresh:1.5;url=index.php");
                        } else {
                            // if password is incorrect -> display an error 
                            $login_err = "Invalid password.";
                        }
                    }
                } else {
                    // Username doesn't exist -> error
                    $login_err = "Username doesn't exist";
                }
            } else {
                print("Oops! Something went wrong. Please try again later.");
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
};

if (!empty($login_err) || !empty($username_error) || !empty($password_error)) {
    echo '
    <div class="modal-body" style="max-width: 35rem; margin: auto;">
          <form method="POST" action="login_result.php">
          <div class="text-center">' . $login_err . '</div>
            <div class="form-group">
              <label for="email">Username</label>
              <input type="text" class="form-control" name="username" id="email" placeholder="Email" value="' . $username . '">
              <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">' . $username_error . '</span></small>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" name="password" class="form-control" id="password" placeholder="Password">
              <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">' . $password_error . '</span></small>
            </div>
            <button type="submit" class="btn btn-primary form-control">Sign in</button>
          </form>
          <div class="text-center">or <a href="register.php">create an account</a></div>
        </div>';
}
