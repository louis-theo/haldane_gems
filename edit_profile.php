    <?php include_once("header.php") ?>
    <?php require("utilities.php") ?>
    <?php require_once "config.php" ?>



    <!-- edit profile modal -->
    <div>
        <div>
            <div class="modal-content" style="border: none;">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Edit profile</h4>
                </div>
                <?php
                $userId = $_SESSION['id'];


                // query general user info
                $general_info_sql = "SELECT * FROM users WHERE UserID = $userId";
                $general_info_result = $mysqli->query($general_info_sql);
                $info = $general_info_result->fetch_assoc();

                $username = $info["Username"];
                $email = $info["Email"];
                $address = $info["Address"];

                $general_info_result->free();
                $formDisplayed = true;

                // submit changes into db
                if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['submit_changes'])) {


                    // asign the POST form to variables
                    $new_username = trim($_POST["username"]);
                    $new_email = trim($_POST["email"]);
                    $new_address = trim($_POST["address"]);

                    $error = false;

                    if (empty($new_username)) {
                        echo "<small> The username field can not be empty </small>";
                        $error = true;
                    };
                    // VALIDATION TO CHECK IF THE USERNAME IS TAKEN
                    // if ($new_username != $username && is_username_taken($mysqli, $new_username)) {
                    //     echo "<small> Username is taken. Please choose another one </small>";
                    // };



                    if (empty($new_email)) {
                        echo "<small> The email field can not be empty </small>";
                        $error = true;
                    };

                    // VALIDATION TO CHECK IF THE EMAIL IS TAKEN
                    // if ($new_email != $username && is_email_registered($mysqli, $new_email)) {
                    //     echo "<small> Username is taken. Please choose another one </small>";
                    // };

                    if (empty($new_address)) {
                        echo "<small> The address field can not be empty </small>";
                        $error = true;
                    };

                    if (!$error) {
                        $sql_update = "UPDATE users
                                        SET Username = '$new_username',
                                        Email = '$new_email',
                                        Address = '$new_address'
                                        WHERE UserID = $userId;";
                        if ($mysqli->query($sql_update)) {
                            $username = " ";
                            $email = " ";
                            $address = " ";
                            echo "<p style='margin: 1rem auto'>Profile updated successfully<p>";
                            $formDisplayed = false;
                            // Redirect to profile page
                            header("refresh:1;url=profile.php");
                        } else {
                            echo "Error updating record: " . $mysqli->error;
                        }

                        // Close the database connection
                        $mysqli->close();
                    }
                };

                if ($formDisplayed) {
                    echo "
                <div class='modal-body' style= 'width: 40rem; margin: 0 auto;'>
                    <form method='POST' action=''>
                    
                        <div class='form-group'>
                            <label for='username'>Username</label>
                            <input type='text' class='form-control' name='username' id='username' value='" . $username . "'>";



                    echo
                    "</div>
                        <div class='form-group'>
                            <label for='email'>Email</label>
                            <input type='text' class='form-control' name='email' id='email' value='" . $email . "'>";


                    echo
                    " </div>
                        <div class='form-group'>
                            <label for='address'>Address</label>
                            <input type='text' class='form-control' name='address' id='address' value='" . $address . "'> ";


                    echo "</div>
        
                        <button type='submit' name='submit_changes'class='btn btn-primary form-control'>Submit changes</button>
                
                    </form>";
                    "

                </div>

            </div>
        </div>
    </div> ";
                }

                ?>