<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php

/// set areport all errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/// retriving the data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $password = $_POST["password"];
    $passwordConfirmation = $_POST["passwordConfirmation"];

    try {
        require_once "connection.inc.php";
        require_once "signup_contr.inc.php";
        require_once "signup_model.inc.php";

        /// validating data + error handlers

        $error_messages = [];

        if (is_input_empty($username, $password, $email, $address)) {
            $error_messages["empty_input"] = "Fill in all fields.";
        }

        if (is_email_invalid($email)) {
            $error_messages["invalid_email"] = "Invalid email. Provide a different email.";
        }

        if (is_username_taken($pdo, $username)) {
            $error_messages["username_taken"] = "User already exists. Please Login instead.";
        }

        if (is_email_registered($pdo, $email)) {
            $error_messages["email_used"] = "Email already registered.";
        }

        if (password_match($password, $passwordConfirmation)) {
            $error_messages["password_match"] = "The passwords do not match.";
        }

        /// setting up a session 

        if ($error_messages) {
            $_SESSION["errors_signup"] = $error_messages;

            $signupData = ["username" => $username,
            "email" => $email, "address" => $address];

            $_SESSION["signup_data"] = $signupData;

            header("Location: register.php");
            die();
        }

        /// creating a user 

        create_user($pdo, $username, $password, $email, $address);
        create_buyer($pdo, $username);

        header("Location: register.php?signup=success");
        unset($_SESSION['signup_data']);

        $pdo = null;
        $stmt = null;

        die();

    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    } 
} else {
    header("Location: index.php");
    die();
}