<?php

declare(strict_types=1);

function bid_empty(float $bidamount) {
    if (empty($bidamount)) {
        return true;
    } else {
        return false;
    }
}

function bid_low(float $bidamount, float $current_bid) {
    if ($bidamount <= $current_bid && !empty($bidamount)) {
        return true;
    } else {
        return false;
    }
}

function bid_low_SP(float $bidamount, float $starting_price) {
    if ($bidamount <= $starting_price) {
        return true;
    } else {
        return false;
    }
}

function bid_exists(object $pdo, int $userid, int $auction_id, float $bidamount) {
    $query = "SELECT COUNT(*) as count FROM Bid WHERE BuyerID = :userid AND AuctionID = :auction_id AND BidAmount = :bidamount";

    $stmt = $pdo->prepare($query);

    $stmt->bindParam(":userid", $userid);
    $stmt->bindParam(":auction_id", $auction_id);
    $stmt->bindParam(":bidamount", $bidamount);

    try {
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }

    // Check the count and return true if the row exists, false otherwise
    return $result['count'] > 0;
}