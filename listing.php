<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require("place_bid_view.inc.php") ?>


<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// var_dump($_SESSION);

// CONNECTING TO DB
$server_name = "localhost";
$user_name = "root";
$password = "";
$db_name = "auctiondatabase";

// Create connection
$connection = mysqli_connect($server_name, $user_name, $password, $db_name);

// Check if the connection was successful
if (mysqli_connect_errno()) {
  echo "Failed to Connect: " . mysqli_connect_error();
  exit();
  // Remove this from final code (or make message non-visible to user)
}

?>


<?php

// Get info from the URL:
$auction_id = $_GET['auction_id'];
if (isset($_SESSION["id"])) {
  $user_id = $_SESSION["id"];
};


// delete auction 
if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['delete_auction'])) {
  $auctionID_to_delete = $_POST["auction_id"];
  $delete_sql = "DELETE FROM auction WHERE AuctionID = $auctionID_to_delete";
  if ($connection->query($delete_sql)) {
    echo ("<p class='not-found'>Auction deleted</p>");

    // Refresh page
    header("refresh:1, url=mylistings.php ");
  } else {
    echo "Error deleting your comment:" . mysqli_error($connection);
  }
};

// QUERY THE DB FOR AUCTION INFO
$auction_info_query = "SELECT * FROM Auction WHERE AuctionID = $auction_id";
$auction_info_result = mysqli_query($connection, $auction_info_query)
  or die('Error making select users query' . mysqli_error($connection));

if (mysqli_num_rows($auction_info_result) > 0) {
  // Fetch data from database.
  $row = mysqli_fetch_assoc($auction_info_result);
  $product_name = $row['ProductName'];
  $seller_id = $row['SellerID'];
  $description = $row['Description'];
  $start_price = $row['StartPrice'];
  $reserve_price = $row['ReservePrice'];
  $start_date = $row['StartDate'];
  $end_date = $row['EndDate'];
  $current_bid = $row['CurrentBid'];
  $product_condition = $row['ProductCondition'];
  $no_watches = $row['Watchlist'];

  /// MAKE SURE YOU DO NOT DELETE MY PRECIOUS WORK WHEN MERGING
  /// THANK YOU
  /// STACY XOXO
  $_SESSION["current_bid"] = floatval($current_bid);
  $_SESSION["starting_price"] = floatval($start_price);
  $_SESSION["auction_id"] = intval($auction_id);

  // Fetch rating for the seller
  $seller_rating_sql = "SELECT AVG(RatingScore) as average FROM ratings WHERE SellerID = $seller_id";
  $seller_rating_result = $connection->query($seller_rating_sql);
  $seller_rating_fetch = $seller_rating_result->fetch_assoc();
  $seller_rating = round($seller_rating_fetch["average"], 1);
  $seller_rating_result->free();

  // Fetch rating for the seller
  $seller_username_sql = "SELECT Username FROM users WHERE UserID = $seller_id";
  $seller_username_result = $connection->query($seller_username_sql);
  $seller_username_fetch = $seller_username_result->fetch_assoc();
  $seller_username = $seller_username_fetch["Username"];
  $seller_username_result->free();
} else {
  if (!$_SERVER["REQUEST_METHOD"] === 'POST' && !isset($_POST['delete_auction'])) {
    echo '<p class="not-found">No auction with ID number (' . $auction_id . ') was found.</p>';
  };
  exit;
}


// DETERMINE AUCTION OUTCOME:
//    Query AuctionID DB row to determine outcome of auction (active/sold/expired). 
//    Will be dependant on the $start_date, $end_date, $current_bid.
//      If $current_bid = 0 and $now > $end_date then auction expired
//      If current_bid > 0 and $now > $end_date then auction sold
//      If $now < $end_date then auction live
$now = new DateTime();
$end_date = new DateTime($end_date);

// Fetch updated status
if ((($current_bid == $start_price) || ($current_bid < $reserve_price)) && $now > $end_date) {
  $status_update_query = "UPDATE Auction SET Status = 'Expired' WHERE AuctionID = $auction_id";
} else if ($current_bid > $start_price && $now > $end_date) {
  $status_update_query = "UPDATE Auction SET Status = 'Sold' WHERE AuctionID = $auction_id";
} else if ($now < $end_date) {
  $status_update_query = "UPDATE Auction SET Status = 'Active' WHERE AuctionID = $auction_id";
}

// Execute update query
if (isset($status_update_query)) {
  $status = mysqli_query($connection, $status_update_query);
}
if (!$status) {
  die('Error updating status: ' . mysqli_error($connection));
}

// Fetch updated status
$status_query = "SELECT Status FROM Auction WHERE AuctionID = $auction_id";
$status_result = mysqli_query($connection, $status_query);

if (!$status_result) {
  die('Error fetching status: ' . mysqli_error($connection));
}

// Store outcome in $status variable
$status_row = mysqli_fetch_assoc($status_result);
$status = $status_row['Status'];


//Calculate time until auction end and display time remaining:
if ($now < $end_date) {
  $time_till_end = $now->diff($end_date);
  $time_remaining = ' (in ' . display_time_remaining($time_till_end) . ')';
}


// Count current watches in Watchlist table
$count_current_watches = "SELECT COUNT(*) AS WatchlistCount FROM Watchlist WHERE AuctionID = $auction_id";
$count_current_watches_result = mysqli_query($connection, $count_current_watches);

// Update Watchlist column in Auction table based on Watchlist table count
if ($count_current_watches_result) {
  $count_watches_row = mysqli_fetch_assoc($count_current_watches_result);
  $count_watches = $count_watches_row["WatchlistCount"];
  $count_watches_update_query = "UPDATE Auction SET Watchlist = $count_watches WHERE AuctionID = $auction_id";
}

// Execute update query
if (isset($count_watches_update_query)) {
  $no_watches = mysqli_query($connection, $count_watches_update_query);
}
if (!$no_watches) {
  die('Error updating status: ' . mysqli_error($connection));
}

// Fetch updated number of watches
$no_watches_query = "SELECT Watchlist FROM Auction WHERE AuctionID = $auction_id";
$no_watches_result = mysqli_query($connection, $no_watches_query);

if (!$no_watches_result) {
  die('Error fetching status: ' . mysqli_error($connection));
}

// Store outcome in $no_watches variable
$no_watches_row = mysqli_fetch_assoc($no_watches_result);
$no_watches = $no_watches_row['Watchlist'];


// Function to count number of bids in given auction
function bid_count($auction_id_funct, $connection_funct)
{
  $bid_count_query = "SELECT COUNT(*) AS count FROM Bid WHERE AuctionID = $auction_id_funct";
  $result = mysqli_query($connection_funct, $bid_count_query);

  if ($result) {
    $row = mysqli_fetch_assoc($result);

    if ($row == null) {
      $bid_count = 0;
    } else {
      $bid_count = $row['count'];
      return $bid_count;
    }
  } else {
    return "Error fetching bid count";
  }
}


// Function to censor Username for list of all bids
function censor_user($username_funct, $censor_char = '*')
{
  $length = strlen($username_funct);

  // If username <3 chars, return original string (usernames should not be this short though)
  if ($length < 3) {
    return $username_funct;
  }

  // Get first and last chars
  $first_char = $username_funct[0];
  $last_char = $username_funct[$length - 1];

  // Replace all other chars with *
  $censored_user = $first_char . str_repeat($censor_char, $length - 2) . $last_char;

  return $censored_user;
}

// Function to display previous bids
function displayPreviousBids($auction_id_funct, $connection_funct)
{
  $bid_info_query = "SELECT BuyerID, BidAmount, BidTime FROM Bid WHERE AuctionID = $auction_id_funct";
  $bid_info_result = mysqli_query($connection_funct, $bid_info_query) or die('Error making select bids query' . mysqli_error($connection_funct));
  // Iterate over bid rows
  while ($row = mysqli_fetch_assoc($bid_info_result)) {
    $bid_time_db = $row['BidTime'];
    $bid_time_non_format = DateTime::createFromFormat('Y-m-d H:i:s', $bid_time_db);
    $bid_time = $bid_time_non_format->format('d.m.y H:i');
    $buyer_id = $row['BuyerID'];
    $bid_amount = $row['BidAmount'];

    // Get username of bidder
    $username_query = "SELECT Username FROM Users WHERE UserID = $buyer_id";
    $username_result = mysqli_query($connection_funct, $username_query) or die('Error making select users query' . mysqli_error($connection_funct));

    if ($username_row = mysqli_fetch_assoc($username_result)) {
      $username = $username_row['Username'];

      // Display the bid information
      echo "<li class='prev-bid'><p>" . $bid_time . "</p><p>" . censor_user($username) . "</p><p> £" . $bid_amount . "</p>";
    } else {
      echo 'No User was found.';
      exit;
    }
  }
}

// WATCHLIST BUTTON FUNCTIONALITY
// Check if user has session
$has_session = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true;
$watch_result = null;


if ($has_session) {
  $buyer_id = $_SESSION['id'];
  // Query database to see if item is watched
  $watch_query = "SELECT * FROM Watchlist WHERE BuyerID = $buyer_id AND AuctionID = $auction_id";
  // Fetch query
  $watch_result = mysqli_query($connection, $watch_query);
  if (!$watch_result) {
    die('Watchlist Query Error: ' . mysqli_error($connection));
  }
}
// If user watching item $watching = true, else $watching = false
$watching = ($watch_result !== null && mysqli_num_rows($watch_result) > 0);

?>



<div class="container">


  <div class="header-container">
    <h2 class="watch-header blue-text"><?php echo ($product_name); ?></h2>
    <div class='buttons-container'>
      <?php
      // Watchlist button functionality:
      if ($status == "Active" && $has_session) {
        if (!$watching) {
          echo ("<form method='post' action=''>
          <input type='hidden' name='auction_id' value=' " . $auction_id . " ' /> 
          <button type='submit' name='add_to_watchlist' class='btn btn-outline-secondary btn-sm'>+ Add to watchlist</button></form>");
        };
        if ($watching) {
          echo (
            "<div class='watch-wrap'>
            <p class='watching-success'>Watching</p>
            <form method='post' action='' class='btn-wide btn-margin'>
            <input type='hidden' name='auction_id_remove' value=' " . $auction_id . " ' /> 
              <button type='submit' name='remove_from_watchlist' class='btn btn-outline-secondary btn-sm'>Remove watch</button>
            </form>
            </div>");
        };
      };

      // DELETE AUCTION BUTTON 
      if (isset($_SESSION["id"]) && $seller_id == $user_id) {
        echo ("<form method='post' action='' class='btn-margin'>
      <input type='hidden' name='auction_id' value=" . $auction_id . " />
      <button type='submit' name='delete_auction' class='btn btn-outline-primary btn-sm btn-delete'>Delete Auction</button>
      </form>");
      };  ?>
    </div>
  </div>
  <?php
  // Query to add item to users watchlist when "Add to watchlist" button clicked.
  if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['add_to_watchlist'])) {
    if (isset($_SESSION['id'])) {
      $buyer_id = $_SESSION['id'];
      $add_watch_query = "INSERT INTO Watchlist (BuyerID, AuctionID) VALUES ('$buyer_id', '$auction_id')";
    }
    if (isset($add_watch_query)) {
      $add_watch = mysqli_query($connection, $add_watch_query);
    }
    if (!$add_watch) {
      die('Error adding to watchlist: ' . mysqli_error($connection));
    }
    echo '<script type="text/javascript">window.location.href = window.location.href;</script>';
  }

  // Query to remove item from users watchlist when "remove from watchlist" button clicked.
  if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['remove_from_watchlist'])) {
    if (isset($_SESSION['id'])) {
      $buyer_id = $_SESSION['id'];
      $remove_watch_query = "DELETE FROM Watchlist WHERE  BuyerID = $buyer_id AND AuctionID = $auction_id";
    }
    if (isset($remove_watch_query)) {
      $remove_watch = mysqli_query($connection, $remove_watch_query);
    }
    if (!$remove_watch) {
      die('Error adding to watchlist: ' . mysqli_error($connection));
    }
    echo '<script type="text/javascript">window.location.href = window.location.href;</script>';
  }

  ?>


  <div class="row">
    <div class="col-sm-8">

      <div class="itemDescription">
        <div class="item-info-wrap">
          <h6 class="info-labels">Product Condition:</h6>
          <p class="item-info"><?php echo ($product_condition); ?> </p>
        </div>
        <div class="item-info-wrap">
          <h6 class="info-labels">Description:</h6>
          <p class="item-info"><?php echo ($description); ?></p>
        </div>
      </div>
      <?php
      if ($has_session) {
        echo (
          '<div class="item-info-wrap">
              <h6 class="info-labels">Seller:</h6>
              <p class="item-info">
                <a class= "item-profile-link" href="profile.php?seller_id=' . $seller_id . '">' . $seller_username .
          '</a>
              </p>
            </div>
          <div class="item-info-wrap">
            <h6 class="info-labels">Seller rating:</h6>
            <p class="item-info"><span class="blue-text"><b>' . $seller_rating . '</b></span>/5</p>
          </div>');
      }
      ?>

      <?php
      // For Ended auctions:
      if ($now > $end_date) : ?>

        <div class="item-info-wrap">
          <h6 class="info-labels">Status:</h6>
          <p class="item-info"> <?php echo $status; ?></p>
        </div>
        <div class="item-info-wrap">
          <h6 class="info-labels">Ended on:</h6>
          <p class="item-info"><?php echo (date_format($end_date, 'j M H:i')) ?></p>
        </div>
        <div class="item-info-wrap">
          <h6 class="info-labels"> <?php if ($status == "Sold") {
                                      echo "Sold for:";
                                    } else {
                                      echo "Last bid:";
                                    } ?></h6>
          <p class="lead current-bid blue-text">£<?php echo (number_format($current_bid, 2)) ?></p>
        </div>
        <!-- TODO add winning BuyerID -->

      <?php
      // For active auctions:
      else : ?>
        <div class="item-info-wrap">
          <h6 class="info-labels">Number of bids: </h6>
          <p class="item-info"><?php echo bid_count($auction_id, $connection) ?></p>
        </div>
        <div class="item-info-wrap">
          <h6 class="info-labels">Status: </h6>
          <p class="item-info"><?php echo $status; ?></p>
        </div>
        <div class="item-info-wrap">
          <h6 class="info-labels">Auction ends: </h6>
          <p class="item-info"><?php echo (date_format($end_date, 'j M H:i') . $time_remaining) ?></p>
        </div>
        <div class="item-info-wrap">
          <h6 class="info-labels">Number of people watching: </h6>
          <p class="item-info"><?php echo $no_watches ?></p>
        </div>
      <?php endif; ?>
      <!-- TODO add current bid BuyerID -->

      <?php require("place_bid_model.inc.php") ?>


    </div>
    <div class="col-sm-4">

      <!-- Right col with bidding info -->
      <!-- Bidding form -->
      <?php if ($has_session && ($now < $end_date)) { ?>
        <div class="item-info-wrap">
          <h6 class="lead current-bid">Starting Price: </h6>
          <p class="lead current-bid blue-text"><b>£<?php echo (number_format($start_price, 2)) ?></b></p>
        </div>

      <?php }
      if ($has_session && ($now < $end_date) && ($current_bid < $reserve_price)) { ?>
        <div class="item-info-wrap">
          <h6 class="lead current-bid">Reserve price not met </h6>
        </div>

      <?php }
      if (($now < $end_date)) { ?>
        <div class="item-info-wrap">
          <h6 class="lead current-bid">Current Bid: </h6>
          <p class="lead current-bid blue-text "><b>£<?php echo (number_format($current_bid, 2)) ?></b></p>
        </div>

        <?php
        if ($has_session && $_SESSION["id"] != $seller_id) { ?>
          <form method="POST" action="place_bid.php" class="bid-form">
            <?php
            check_bid_error();
            ?>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" step="any" class="form-control" name="bidamount" id="bid">
            </div>
            <button type="submit" class="btn btn-primary form-control">Place bid</button>
          </form>
      <?php
        };
      }
      ?>

      <div class="prev-bids-container">
        <h6 class="prev-bid-label">Previous bids: </h6>
        <ul class="prev-bids-table">
          <li class='prev-bid'>
            <p class="wide-prev-bid bold-bid"> Bid time </p>
            <p class="bold-bid"> User </p>
            <p class="bold-bid"> Bid amount</p>
            <?php echo displayPreviousBids($auction_id, $connection); ?>
        </ul>
      </div>
    </div> <!-- End of right col with bidding info -->
  </div> <!-- End of row #2 -->



  <?php include_once("footer.php") ?>