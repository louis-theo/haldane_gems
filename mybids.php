<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require_once "config.php" ?>




<div class="container">
  <h2 class="browse-header pad">My bids</h2>

  <?php
  //user id from session 
  $userId = $_SESSION["id"];

  // Pagination 
  $results_per_page = 10;

  // Current page or set it to 1
  $curr_page = isset($_GET['page']) ? $_GET['page'] : 1;


  // query to get total elements
  if (!isset($_POST['filter_option'])) {
    // echo "The form has not been submitted yet." . "<br>";
    $query = "SELECT COUNT(DISTINCT b.AuctionID) AS total
              FROM bid AS b 
              INNER JOIN auction AS a 
              ON b.AuctionID = a.AuctionID
              WHERE BuyerID = $userId";
  } else {

    $filterOption = $_POST['filter_option'];

    // Execute the corresponding SQL query based on the selected option
    switch ($filterOption) {
      case 'active':
        $query = "SELECT COUNT(DISTINCT b.AuctionID) AS total 
        FROM bid AS b 
        INNER JOIN auction AS a 
        ON b.AuctionID = a.AuctionID
         WHERE BuyerID = $userId AND a.Status = 'Active'";;
        break;
      case 'won':
        // $total_results = 0;
        $query = "SELECT b.BidID, b.BuyerID, b.AuctionID, b.BidAmount, b.BidTime, a.ProductName, a.Description, a.EndDate, a.Status
        FROM  bid AS b 
        INNER JOIN auction AS a ON b.AuctionID = a.AuctionID
        WHERE BuyerID = $userId and a.Status = 'Sold'
        GROUP BY b.AuctionID
        ORDER BY b.BidTime DESC";
        break;
      case 'all':
        $query = "SELECT COUNT(DISTINCT b.AuctionID) AS total
        FROM bid AS b 
        INNER JOIN auction AS a 
        ON b.AuctionID = a.AuctionID
        WHERE BuyerID = $userId";
        break;
      default:
        break;
    };
  }


  if (isset($_POST['filter_option']) && $_POST['filter_option'] == "won") {
    $total_results = 0;

    $won_result = $mysqli->query($query);
    while ($row = $won_result->fetch_assoc()) {
      $auction_id = $row["AuctionID"];

      // max price per auction
      $sql_max_price = "SELECT MAX(BidAmount) as max_bid, BuyerID FROM bid WHERE AuctionID = $auction_id;";
      $max_result = $mysqli->query($sql_max_price);


      if ($max_result) {
        $max_row = $max_result->fetch_assoc();
        // check if the max bid was made by current user - ie won
        if ($max_row["BuyerID"] == $userId) {
          $total_results += 1;
        };
      };
    }
  } else {
    $result_count = $mysqli->query($query);
    $row_count = $result_count->fetch_assoc();
    $total_results = $row_count['total'];
    $result_count->free();
  };


  // Calculate the total number of pages
  $max_page = ceil($total_results / $results_per_page);
  $offset = ($curr_page - 1) * $results_per_page;
  ?>

  <body>
    <div class="container mt-5">
      <form method="post" action="mybids.php" id="filter_form" style="padding-bottom: 2rem">
        <div class="row">
          <div class="col-md-9">
            <div class="form-inline">
              <label class="mx-2" for="filter_option">Sort by:</label>
              <select class="form-control" name="filter_option" id="filter_option">
                <option value="all" <?php echo (isset($_POST['filter_option']) && $_POST['filter_option'] == 'all') ? 'selected' : ''; ?>>All auctions</option>
                <option value="won" <?php echo (isset($_POST['filter_option']) && $_POST['filter_option'] == 'won') ? 'selected' : ''; ?>>Won auctions</option>
                <option value="active" <?php echo (isset($_POST['filter_option']) && $_POST['filter_option'] == 'active') ? 'selected' : ''; ?>>Active auctions</option>

              </select>
            </div>
          </div>
          <div class="col-md-3 text-right">
            <button type="submit" class="btn btn-primary">Search</button>
          </div>
        </div>
      </form>
  </body>

  <ul class="list-group">
    <?php






    if (isset($userId)) {
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_option'])) {
        if ($_POST['filter_option'] == "won") {
          // previous one
          $sql_data_won = "SELECT b.BidID, b.BuyerID, b.AuctionID, b.BidAmount, b.BidTime, a.ProductName, a.Description, a.EndDate, a.Status
         FROM  bid AS b 
         INNER JOIN auction AS a ON b.AuctionID = a.AuctionID
         WHERE BuyerID = $userId and a.Status = 'Sold'
         GROUP BY b.AuctionID
         ORDER BY b.BidTime DESC 
         LIMIT $offset, $results_per_page;";

          $won_result = $mysqli->query($sql_data_won);

          if ($won_result->num_rows > 0) {

            while ($row = $won_result->fetch_assoc()) {
              $auction_id = $row["AuctionID"];

              // max price per auction
              $sql_max_price = "SELECT MAX(BidAmount) as max_bid, BuyerID FROM bid WHERE AuctionID = $auction_id;";
              $max_result = $mysqli->query($sql_max_price);


              if ($max_result) {
                $max_row = $max_result->fetch_assoc();
                // check if the max bid was made by current user - ie won
                if ($max_row["BuyerID"] == $userId) {
                  $highest_bid = $max_row["max_bid"];
                  $bid_id = $row["BidID"];
                  $bid_amount = $row["BidAmount"];
                  $bid_time = $row["BidTime"];
                  $title = $row["ProductName"];
                  $description = $row["Description"];
                  $end_date = $row["EndDate"];
                  $status = $row["Status"];


                  // //number of bids query
                  $sql_num_bid = "SELECT COUNT(*) AS count FROM bid WHERE AuctionID = $auction_id";
                  $result_bids = $mysqli->query($sql_num_bid);
                  $row_num_bids = $result_bids->fetch_assoc();
                  $num_bids = $row_num_bids["count"];
                  $result_bids->free();

                  //list of bids on an item
                  $sql_bids_arr = "SELECT b.BidAmount, b.BidTime FROM bid AS b WHERE AuctionID = $auction_id AND BuyerID = $userId ORDER BY b.BidTime DESC;";
                  $result_bids_arr = $mysqli->query($sql_bids_arr);

                  if ($result_bids_arr) {
                    $bids_arr = array();

                    while ($row_bids_arr = $result_bids_arr->fetch_assoc()) {
                      $bids_arr[] = $row_bids_arr["BidAmount"];
                    }

                    $result_bids_arr->free();
                  } else {
                    echo "Error: " . $sql_bids_arr . "<br>" . $mysqli->error;
                  }


                  print_bid_li($auction_id, $title, $description, $highest_bid, $num_bids, $end_date, $bids_arr);
                } else {
                  echo ("<p class='not-found'> No Bids to display </p>");
                }
              }
            }
          } else {
            echo ("<p class='not-found'> No Bids to display </p>");
          };
        };
        if ($_POST['filter_option'] == "active") {
          $sql_data_won = "SELECT MAX(BidAmount) as max_bid, b.BidID, b.BuyerID, b.AuctionID, b.BidAmount, b.BidTime, a.ProductName, a.Description, a.EndDate, a.Status
          FROM  bid AS b 
          INNER JOIN auction AS a ON b.AuctionID = a.AuctionID
          WHERE BuyerID = $userId AND a.Status = 'Active'
          GROUP BY b.AuctionID
          ORDER BY b.BidTime DESC 
          LIMIT $offset, $results_per_page;";

          $won_result = $mysqli->query($sql_data_won);

          if ($won_result->num_rows > 0) {
            while ($row = $won_result->fetch_assoc()) {
              $auction_id = $row["AuctionID"];

              $highest_bid = $row["max_bid"];
              $bid_id = $row["BidID"];
              $bid_amount = $row["BidAmount"];
              $bid_time = $row["BidTime"];
              $title = $row["ProductName"];
              $description = $row["Description"];
              $end_date = $row["EndDate"];
              $status = $row["Status"];


              // //number of bids query
              $sql_num_bid = "SELECT COUNT(*) AS count FROM bid WHERE AuctionID = $auction_id";
              $result_bids = $mysqli->query($sql_num_bid);
              $row_num_bids = $result_bids->fetch_assoc();
              $num_bids = $row_num_bids["count"];
              $result_bids->free();

              //list of bids on an item
              $sql_bids_arr = "SELECT b.BidAmount, b.BidTime FROM bid AS b WHERE AuctionID = $auction_id AND BuyerID = $userId ORDER BY b.BidTime DESC;";
              $result_bids_arr = $mysqli->query($sql_bids_arr);

              if ($result_bids_arr) {
                $bids_arr = array();

                while ($row_bids_arr = $result_bids_arr->fetch_assoc()) {
                  $bids_arr[] = $row_bids_arr["BidAmount"];
                }

                $result_bids_arr->free();
              } else {
                echo "Error: " . $sql_bids_arr . "<br>" . $mysqli->error;
              };
              print_bid_li($auction_id, $title, $description, $highest_bid, $num_bids, $end_date, $bids_arr);
            }
          } else {
            echo ("<p class='not-found'> No Bids to display </p>");
          }
        }
      }
      if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_option']) && $_POST['filter_option'] == "all") || $_SERVER['REQUEST_METHOD'] !== 'POST') {

        $sql_data = "SELECT b.BidID, b.BuyerID, b.AuctionID, b.BidAmount, b.BidTime, a.ProductName, a.Description, a.EndDate, a.Status
                      FROM  bid AS b 
                      INNER JOIN auction AS a ON b.AuctionID = a.AuctionID
                      WHERE BuyerID = $userId
                      GROUP BY b.AuctionID
                      ORDER BY b.BidTime DESC 
                      LIMIT $offset, $results_per_page;";

        $result = $mysqli->query($sql_data);
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $auction_id = $row["AuctionID"];
            $bid_id = $row["BidID"];
            $bid_amount = $row["BidAmount"];
            $bid_time = $row["BidTime"];
            $title = $row["ProductName"];
            $description = $row["Description"];
            $end_date = $row["EndDate"];
            $status = $row["Status"];


            //number of bids query
            $sql_num_bid = "SELECT COUNT(*) AS count FROM bid WHERE AuctionID = $auction_id";
            $result_bids = $mysqli->query($sql_num_bid);
            $row_num_bids = $result_bids->fetch_assoc();
            $num_bids = $row_num_bids["count"];
            $result_bids->free();

            //list of bids on an item
            $sql_bids_arr = "SELECT b.BidAmount, b.BidTime FROM bid AS b WHERE AuctionID = $auction_id AND BuyerID= $userId ORDER BY b.BidTime DESC;";
            $result_bids_arr = $mysqli->query($sql_bids_arr);


            if ($result_bids_arr) {
              $bids_arr = array();

              while ($row_bids_arr = $result_bids_arr->fetch_assoc()) {
                $bids_arr[] = $row_bids_arr["BidAmount"];
              }

              $result_bids_arr->free();
            } else {
              echo "Error: " . $sql_bids_arr . "<br>" . $mysqli->error;
            }

            //highest bid for an auction
            $sql_highest_bid = "SELECT MAX(BidAmount) AS max FROM bid WHERE AuctionID = $auction_id";
            $result_highest_bid = $mysqli->query($sql_highest_bid);
            $row_highest_bid = $result_highest_bid->fetch_assoc();
            $highest_bid = $row_highest_bid["max"];

            print_bid_li($auction_id, $title, $description, $highest_bid, $num_bids, $end_date, $bids_arr);
          }
        } else {
          echo ("<p class='not-found'> No Bids to display </p>");
        };

        $result->free();
      }
    }

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