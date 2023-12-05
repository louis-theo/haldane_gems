<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require("connection.inc.php") ?>

<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// print_r($_POST);
?>

<div class="container">

  <h2 class="browse-header pad">My Listings</h2>

  <!-- drop-down filter -->
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Filter Auctions</title>
  </head>

  <body>
    <div class="container">
      <form method="post" action="mylistings.php" id="filter_form">
        <div class="row">
          <div class="col-md-9">
            <div class="form-inline">
              <label class="mx-2" for="filter_option">Sort by:</label>
              <select class="form-control" id="filter_option" name="filter_option">
                <option value="all_auctions" <?php echo (isset($_POST['filter_option']) && $_POST['filter_option'] == 'all_auctions') ? 'selected' : ''; ?>>All Auctions</option>
                <option value="active_auctions" <?php echo (isset($_POST['filter_option']) && $_POST['filter_option'] == 'active_auctions') ? 'selected' : ''; ?>>Active Auctions</option>
                <option value="sold_auctions" <?php echo (isset($_POST['filter_option']) && $_POST['filter_option'] == 'sold_auctions') ? 'selected' : ''; ?>>Sold Auctions</option>
              </select>
            </div>
          </div>
          <div class="col-md-3 text-right">
            <button type="submit" class="btn btn-primary">Search</button>
          </div>
        </div>
      </form>
    </div>
  </body>

  <?php

  // This page is for showing a user the auction listings they've made.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

  /////// pagination code borrowed from Yulia
  $results_per_page = 10;
  // Current page or set it to 1
  $curr_page = isset($_GET['page']) ? $_GET['page'] : 1;

  $userID = $_SESSION["id"];

  ///////
  if (!isset($_POST['filter_option'])) {
    // echo "The form has not been submitted yet." . "<br>";
    $total_num_auction = "SELECT COUNT(*) AS NumberOfAuctions FROM Auction WHERE SellerID = $userID;";

    $stmt = $pdo->prepare($total_num_auction);
    // $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $row_count = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    $total_results = $row_count[0]['NumberOfAuctions'];

    $max_page = ceil($total_results / $results_per_page);
    $offset = ($curr_page - 1) * $results_per_page;

    ///////

    $query = "SELECT AuctionID, ProductName, Description, CurrentBid, EndDate, Status,
                (SELECT COUNT(*) FROM Bid WHERE AuctionID = Auction.AuctionID) AS BidsNumber
                FROM Auction WHERE SellerID = $userID ORDER BY Auction.EndDate ASC LIMIT $offset, $results_per_page;";

    $stmt = $pdo->prepare($query);
    // $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $auctionData = $stmt->execute();
    $auctionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (!empty($auctionData)) {
      try {
        echo '<div class="container mt-5"><ul class="list-group">';
        foreach ($auctionData as $auction) {
          $auction_id = $auction["AuctionID"];
          $title = $auction["ProductName"];
          $description = $auction["Description"];
          $current_price = $auction["CurrentBid"];
          $num_bids = $auction["BidsNumber"];
          $status = $auction["Status"];
          $end_time = $auction["EndDate"];
          $end_date = new DateTime($end_time);

          if ($status == "Sold") {
            $sold_auction_id = $auction_id;

            $sql_max_price = "SELECT MAX(BidAmount) as max_bid, BuyerID FROM bid WHERE AuctionID = $auction_id;";


            $stmt = $pdo->prepare($sql_max_price);
            $max_row = $stmt->execute();
            $max_row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();


            $highest_bid = $max_row[0]["max_bid"];
            $winner_id = $max_row[0]["BuyerID"];



            $winner_query = "SELECT
            Username
            FROM
                Users
            WHERE
                UserID = $winner_id";


            $stmt = $pdo->prepare($winner_query);
            $winnerData = $stmt->execute();
            $winnerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $winner = $winnerData[0]["Username"];
            print_winner_li($auction_id, $title, $description, $current_price, $num_bids, $end_date, $winner);
          } else {
            print_listing_li($auction_id, $title, $description, $current_price, $num_bids, $end_date);
          }
        }
        echo '</ul></div>';
      } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
      }
    } else {
      echo "<br>";
      echo ("<p class='not-found'> No auctions found. </p>");
    }
  } else {
    // echo "The form has been submitted!" . "<br>";

    $filterOption = $_POST['filter_option'];

    // Execute the corresponding SQL query based on the selected option
    switch ($filterOption) {
      case 'all_auctions':
        $total_num_auction = "SELECT COUNT(*) AS NumberOfAuctions FROM Auction WHERE SellerID = $userID;";

        $stmt = $pdo->prepare($total_num_auction);
        // $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $row_count = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $total_results = $row_count[0]['NumberOfAuctions'];

        $max_page = ceil($total_results / $results_per_page);
        $offset = ($curr_page - 1) * $results_per_page;

        $query = "SELECT AuctionID, ProductName, Description, CurrentBid, EndDate, Status,
            (SELECT COUNT(*) FROM Bid WHERE AuctionID = Auction.AuctionID) AS BidsNumber
            FROM Auction WHERE SellerID = $userID ORDER BY Auction.EndDate ASC LIMIT $offset, $results_per_page";

        $stmt = $pdo->prepare($query);
        // $stmt->bindParam(':userID', $userID, ':offset', $offset, ':results_per_page', $results_per_page, PDO::PARAM_INT);
        $auctionData = $stmt->execute();
        $auctionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Loop through results and print them out as list items.
        $_SESSION["auctionData"] = $auctionData;
        $stmt->closeCursor();

        break;
        ///////
      case 'active_auctions':
        $total_num_auction = "SELECT COUNT(*) AS NumberOfAuctions FROM Auction WHERE SellerID = $userID AND Status = 'Active';";

        $stmt = $pdo->prepare($total_num_auction);
        // $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $row_count = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_results = $row_count[0]['NumberOfAuctions'];
        $stmt->closeCursor();

        $max_page = ceil($total_results / $results_per_page);
        $offset = ($curr_page - 1) * $results_per_page;

        $query = "SELECT AuctionID, ProductName, Description, CurrentBid, EndDate, Status,
            (SELECT COUNT(*) FROM Bid WHERE AuctionID = Auction.AuctionID) AS BidsNumber 
            FROM Auction WHERE SellerID = $userID AND Status = 'Active' ORDER BY Auction.EndDate ASC LIMIT $offset, $results_per_page";

        $stmt = $pdo->prepare($query);
        // $stmt->bindParam(':userID', $userID, ':offset', $offset, ':results_per_page', $results_per_page, PDO::PARAM_INT);
        $auctionData = $stmt->execute();
        $auctionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Loop through results and print them out as list items.
        $_SESSION["auctionData"] = $auctionData;
        $stmt->closeCursor();

        break;
        ////////
      case 'sold_auctions':
        $total_num_auction = "SELECT COUNT(*) AS NumberOfAuctions FROM Auction WHERE SellerID = $userID AND Status = 'Sold';";

        $stmt = $pdo->prepare($total_num_auction);
        // $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $row_count = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_results = $row_count[0]['NumberOfAuctions'];
        $stmt->closeCursor();

        $max_page = ceil($total_results / $results_per_page);
        $offset = ($curr_page - 1) * $results_per_page;

        $query = "SELECT AuctionID, ProductName, Description, CurrentBid, EndDate, Status,
            (SELECT COUNT(*) FROM Bid WHERE AuctionID = Auction.AuctionID) AS BidsNumber 
            FROM Auction WHERE SellerID = $userID AND Status = 'Sold' ORDER BY Auction.EndDate ASC LIMIT $offset, $results_per_page";

        $stmt = $pdo->prepare($query);
        // $stmt->bindParam(':userID', $userID, ':offset', $offset, ':results_per_page', $results_per_page, PDO::PARAM_INT);
        $auctionData = $stmt->execute();
        $auctionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Loop through results and print them out as list items.
        $_SESSION["auctionData"] = $auctionData;
        $stmt->closeCursor();

        break;
      default:
        break;
    }

    if (!empty($auctionData)) {
      echo '<div class="container mt-5"><ul class="list-group">';

      try {
        foreach ($auctionData as $auction) {
          $auction_id = $auction["AuctionID"];
          $title = $auction["ProductName"];
          $description = $auction["Description"];
          $current_price = $auction["CurrentBid"];
          $num_bids = $auction["BidsNumber"];
          $end_date = $auction["EndDate"];
          $end_date = new DateTime($end_date);
          $status = $auction["Status"];


          if ($status == "Sold") {
            $sold_auction_id = $auction_id;

            $sql_max_price = "SELECT MAX(BidAmount) as max_bid, BuyerID FROM bid WHERE AuctionID = $auction_id;";


            $stmt = $pdo->prepare($sql_max_price);
            $max_row = $stmt->execute();
            $max_row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();


            $highest_bid = $max_row[0]["max_bid"];
            $winner_id = $max_row[0]["BuyerID"];



            $winner_query = "SELECT
            Username
            FROM
                Users
            WHERE
                UserID = $winner_id";


            $stmt = $pdo->prepare($winner_query);
            $winnerData = $stmt->execute();
            $winnerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $winner = $winnerData[0]["Username"];
            print_winner_li($auction_id, $title, $description, $current_price, $num_bids, $end_date, $winner);
          } else {
            print_listing_li($auction_id, $title, $description, $current_price, $num_bids, $end_date);
          }
        }
        echo '</ul></div>';
      } catch (PDOException $e) {

        die("Query failed: " . $e->getMessage());
      }
    } else {
      echo ("<p class='not-found'> No auctions found. </p>");
    }
  }

  unset($_SESSION['auctionData']);



  ?>

  <!-- Pagination for results listings -->
  <nav aria-label="Search results pages" class="mt-5">
    <ul class="pagination justify-content-center">

      <?php

      // Copy any currently-set GET variables to the URL.
      $querystring = "";
      foreach ($_GET as $key => $value) {
        if ($key != "page") {
          $querystring .= "$key=$value&amp;";
        }
      }

      $high_page_boost = max(3 - $curr_page, 0);
      $low_page_boost = max(2 - ($max_page - $curr_page), 0);
      $low_page = max(1, $curr_page - 2 - $low_page_boost);
      $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

      if ($curr_page != 1) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="mylistings.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
      }

      for ($i = $low_page; $i <= $high_page; $i++) {
        if ($i == $curr_page) {
          // Highlight the link
          echo ('
    <li class="page-item active">');
        } else {
          // Non-highlighted link
          echo ('
    <li class="page-item">');
        }

        // Do this in any case
        echo ('
      <a class="page-link" href="mylistings.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
      }

      if ($curr_page != $max_page) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="mylistings.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
      }
      ?>

    </ul>
  </nav>


</div>

<?php include_once("footer.php") ?>