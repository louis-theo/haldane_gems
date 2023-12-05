<?php

declare(strict_types=1);

function create_bid(object $pdo, int $userid, int $auction_id, float $bidamount) {
    $query = "INSERT INTO Bid (BuyerID, AuctionID, BidAmount) VALUES (:userid, :auction_id, :bidamount)";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(":userid", $userid);
    $stmt->bindParam(":auction_id", $auction_id);
    $stmt->bindParam(":bidamount", $bidamount);
    $stmt->execute(); 

}

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function update_currentbid(object $pdo, int $auction_id, float $bidamount) {
    // Retrieve the auction sellers email
    $query = "SELECT U.Email FROM Auction A JOIN Users U ON A.SellerID = U.UserID WHERE A.AuctionID = :auction_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':auction_id', $auction_id, PDO::PARAM_INT);
    $stmt->execute();
    $seller_email = $stmt->fetchColumn();

    // Get user id who is bidding to find their email 
    $user_id = $_SESSION['id'];

    $query = "SELECT Email FROM Users WHERE UserID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Get the bidding user email 
    $buyer_email = $stmt->fetchColumn();

    // sending an email 
    // email to the bidder
    $mail = new PHPMAiler(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'websiteauction260@gmail.com';
    $mail->Password   = 'wyrc kqpp mzuu yryi'; 
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->setFrom('websiteauction260@gmail.com');
    $mail->isHTML(true);

    $mail->addAddress($buyer_email);
    $mail->Subject = "Bid Confirmation";
    $mail->Body  = "Thank you for placing a bid of $bidamount on Auction #$auction_id. Good luck!";
    $mail->send();

    // Sending an email 
    // Email to the seller 
    $mail = new PHPMAiler(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'websiteauction260@gmail.com';
    $mail->Password   = 'wyrc kqpp mzuu yryi'; 
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->setFrom('websiteauction260@gmail.com');
    $mail->isHTML(true);

    $mail->addAddress($seller_email);
    $mail->Subject = "New Higher Bid Alert";
    $mail->Body = "Someone has placed a higher bid of $bidamount on your Auction #$auction_id. Check it out!";
    $mail->send();

    // Retrieve the current bid for the auction
    $query = "SELECT CurrentBid FROM Auction WHERE AuctionID = :auction_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':auction_id', $auction_id, PDO::PARAM_INT);
    $stmt->execute();
    $currentbid = $stmt->fetchColumn();

    // Compare the new bid with the current bid
    if ($bidamount > $currentbid) {
        // Update the current bid in the Auction table
        $query = "UPDATE Auction SET CurrentBid = :bidamount WHERE AuctionID = :auction_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':bidamount', $bidamount, PDO::PARAM_INT);
        $stmt->bindParam(':auction_id', $auction_id, PDO::PARAM_INT);
        $stmt->execute();
        echo "Bid updated successfully!";
    } else {
        echo "Bid amount is not higher than the current bid.";
    }

}
