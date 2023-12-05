<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// var_dump($_SESSION);

// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_SESSION["id"])) {
        echo 'You are not logged in.';
        header("refresh:4;url=browse.php");   
    }

    $userid = $_SESSION["id"]; 
    $auction_id = $_SESSION["auction_id"]; 
    $bidamount = $_POST["bidamount"];
    $current_bid = $_SESSION["current_bid"];
    $starting_price = $_SESSION["starting_price"];

    $bidamount = floatval($bidamount);

    var_dump($_SESSION) . "<br>";

    try {
        require_once "place_bid_model.inc.php";
        require_once "place_bid_contr.inc.php";
        require_once "connection.inc.php";

        // validating data + error handlers
        
        $error_messages = [];

        if (bid_empty($bidamount)) {
            $error_messages["empty_bid"] = "You did not place a bid.";
        }

        if (bid_low($bidamount, $current_bid)) {
            $error_messages["low_bid"] = "The bid is less than the current bid.";
        }

        if (bid_low_SP($bidamount, $starting_price)) {
            $error_messages["lower_than_SP"] = "The bid is less than the starting price";
        }

        if (bid_exists($pdo, $userid, $auction_id, $bidamount)) {
            $error_messages["bid_exists"] = "The bid already exists.";
        }

        if ($error_messages) {
            $_SESSION["errors_bid"] = $error_messages;
            $bidData = ["bidamount" => $bidamount]; 

            $_SESSION["bid_data"] = $bidData;
            header("Location: listing.php?auction_id=" . $auction_id);
            die();
        }

        /// creating a bid 

        var_dump($_SESSION) . "<br>";
        // echo gettype($bidamount) . "<br>";

        create_bid($pdo, $userid, $auction_id, $bidamount);
        update_currentbid($pdo, $auction_id, $bidamount);
        header("Location: listing.php?auction_id=" . $auction_id . "&bid=success");
        unset($_SESSION['bid_data']);

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

?>