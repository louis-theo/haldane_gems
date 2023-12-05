<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect('localhost', 'root', '', 'auctiondatabase');

if ($conn === false){
    die("ERROR: could not connect" . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $password = $_POST["password"];

    $query = "INSERT INTO Users (Username, Password, Email, Address) VALUES ('$username', '$password', '$email', '$address')";

    if(mysqli_query($conn, $query)){
        echo "<h3>data stored in a database successfully." . " Please browse your localhost php my admin" . " to view the updated data</h3>"; 
    
        echo nl2br("\n$username\n $password\n $email\n $address\n");

    } else {
        echo "ERROR: Unable to insert data. " . mysqli_error($conn);
    }
   
    // Close connection
    mysqli_close($conn);

}
