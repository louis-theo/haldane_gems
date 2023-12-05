<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require_once "config.php" ?>

<div class="container">

  <h2 class="browse-header pad">My Watchlist</h2>



  <?php
  // PAGINATION

  //user id from session 
  $userId = $_SESSION["id"];

  // Pagination 
  $results_per_page = 10;

  // Current page or set it to 1
  $curr_page = isset($_GET['page']) ? $_GET['page'] : 1;

  // query to get total elements
  if (!isset($_POST['watchlist_filter_option'])) {
    // echo "The form has not been submitted yet." . "<br>";
    $query = "SELECT COUNT(*) as total FROM Auction AS a
              INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
              WHERE w.BuyerID = $userId;";
  } else {
    $filterOption = $_POST['watchlist_filter_option'];

    // Execute the corresponding SQL query based on the selected option
    switch ($filterOption) {
      case 'all_watchlist':
        $query = "SELECT COUNT(*) as total FROM Auction AS a
        INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
        WHERE w.BuyerID = $userId;";
        break;
      case 'active_watchlist':
        $query = "SELECT COUNT(*) as total FROM Auction AS a
        INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
        WHERE w.BuyerID = $userId AND a.Status = 'Active';";
        break;
      case 'sold_watchlist':
        $query = "SELECT COUNT(*) as total FROM Auction AS a
        INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
        WHERE w.BuyerID = $userId AND a.Status = 'Sold' OR a.Status = 'Expired';";
        break;
      default:
        break;
    };
  }

  $result_count = $mysqli->query($query);
  $row_count = $result_count->fetch_assoc();
  $total_results = $row_count['total'];
  $result_count->free();

  // Calculate the total number of pages
  $max_page = ceil($total_results / $results_per_page);
  $offset = ($curr_page - 1) * $results_per_page;



  ?>

  <body>
    <div class="container">
      <form method="post" action="watchlist.php" id="filter_form">
        <div class="row">
          <div class="col-md-9">
            <div class="form-inline">
              <label class="mx-2" for="watchlist_filter_option">Sort by:</label>
              <select class="form-control" id="watchlist_filter_option" name="watchlist_filter_option">
                <option value="all_watchlist" <?php echo (isset($_POST['watchlist_filter_option']) && $_POST['watchlist_filter_option'] == 'all_watchlist') ? 'selected' : ''; ?>>All Auctions</option>
                <option value="active_watchlist" <?php echo (isset($_POST['watchlist_filter_option']) && $_POST['watchlist_filter_option'] == 'active_watchlist') ? 'selected' : ''; ?>>Active Auctions</option>
                <option value="sold_watchlist" <?php echo (isset($_POST['watchlist_filter_option']) && $_POST['watchlist_filter_option'] == 'sold_watchlist') ? 'selected' : ''; ?>>Ended Auctions</option>
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

  <div class="container mt-5">
    <ul class="list-group">


      <?php

      if (isset($userId)) {

        if (!isset($_POST['watchlist_filter_option'])) {
          // echo "The form has not been submitted yet." . "<br>";
          $query = "SELECT * FROM Auction AS a
              INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
              WHERE w.BuyerID = $userId ORDER BY a.EndDate ASC LIMIT $offset, $results_per_page;";
        } else {
          $filterOption = $_POST['watchlist_filter_option'];

          // Execute the corresponding SQL query based on the selected option
          switch ($filterOption) {
            case 'all_watchlist':
              $query = "SELECT * FROM Auction AS a
                INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
                WHERE w.BuyerID = $userId ";
              break;
            case 'active_watchlist':
              $query = "SELECT * FROM Auction AS a
              INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
              WHERE w.BuyerID = $userId AND a.Status = 'Active'";
              break;
            case 'sold_watchlist':
              $query = "SELECT * FROM Auction AS a
              INNER JOIN Watchlist AS w ON w.AuctionID = a.AuctionID
              WHERE w.BuyerID = $userId AND a.Status = 'Sold' OR a.Status = 'Expired'";
              break;
            default:
              break;
          };
          $query .= "ORDER BY a.EndDate ASC LIMIT $offset, $results_per_page;";
        }

        $result = $mysqli->query($query);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $auction_id = $row["AuctionID"];
            $title = $row["ProductName"];
            $description = $row["Description"];
            $current_price = $row["CurrentBid"];
            $end_date = $row["EndDate"];
            $end_date = new DateTime($end_date);


            //number of bids query
            $sql_num_bid = "SELECT COUNT(*) AS count FROM bid WHERE AuctionID = $auction_id";
            $result_bids = $mysqli->query($sql_num_bid);
            $row_num_bids = $result_bids->fetch_assoc();
            $num_bids = $row_num_bids["count"];
            $result_bids->free();

            //list of bids on an item
            $sql_bids_arr = "SELECT b.BidAmount, b.BidTime FROM bid AS b WHERE AuctionID = $auction_id AND BuyerID= $userId ORDER BY b.BidTime DESC;";
            $result_bids_arr = $mysqli->query($sql_bids_arr);


            //highest bid for an auction
            $sql_highest_bid = "SELECT MAX(BidAmount) AS max FROM bid WHERE AuctionID = $auction_id";
            $result_highest_bid = $mysqli->query($sql_highest_bid);
            $row_highest_bid = $result_highest_bid->fetch_assoc();
            $highest_bid = $row_highest_bid["max"];

            print_listing_li($auction_id, $title, $description, $current_price, $num_bids,  $end_date);
          }
        } else {
          echo ("<p class='not-found'> No auctions found. </p>");
        };

        $result->free();
      };


      ?>

    </ul>


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
        <a class="page-link" href="mybids.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
        <a class="page-link" href="mybids.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
      </li>');
        }

        if ($curr_page != $max_page) {
          echo ('
      <li class="page-item">
        <a class="page-link" href="mybids.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
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