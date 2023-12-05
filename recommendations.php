<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require("connection.inc.php") ?>

<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// var_dump($_SESSION);
?>

<div class="container">

  <h2 class="browse-header pad">Recommendations for you</h2>

  <?php
  // This page is for showing a buyer recommended items based on their bid 
  // history. It will be pretty similar to browse.php, except there is no 
  // search bar. This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

  /////// pagination code borrowed from Yulia
  $results_per_page = 10;
  // Current page or set it to 1
  $curr_page = isset($_GET['page']) ? $_GET['page'] : 1;
  ///////

  $userID = $_SESSION["id"];
  $total_recommended_auctions = 10;

  /////// borrowed from Yulia
  // Calculate the total number of pages
  $max_page = ceil($total_recommended_auctions / $results_per_page);
  $offset = ($curr_page - 1) * $results_per_page;
  ///////

  // //// number of actioinis a buyer bid on
  // $query = "SELECT COUNT(DISTINCT AuctionID) FROM Bid WHERE BuyerID = $userID;";
  // $stmt = $pdo->prepare($query);
  // $auctionData = $stmt->execute();
  // $auctionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // $_SESSION["auctionData"] = $auctionData;
  // $stmt->closeCursor();
  // var_dump($_SESSION);
  // echo "<br>";

  // //// total number of aucctions
  // $query = "SELECT COUNT(*) FROM Auction";
  // $stmt = $pdo->prepare($query);
  // $auctionData = $stmt->execute();
  // $auctionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // $_SESSION["auctionData"] = $auctionData;
  // $stmt->closeCursor();
  // var_dump($_SESSION);


  // filter bidhistory by buyerid 
  // Take auctionsid of bidhistory and inner join with auction table
  // And select the categories the bidder bid on (array of categories)
  // For recommendations display all the auctions that have the categories from the arrays

  $query = "SELECT DISTINCT A.AuctionID, A.ProductName, A.Description, A.CurrentBid, (SELECT COUNT(*) FROM Bid WHERE AuctionID = A.AuctionID) AS BidsNumber, A.EndDate FROM Auction A
JOIN CategoryAuction CA ON A.AuctionID = CA.AuctionID
JOIN Category C ON CA.CategoryID = C.CategoryID
WHERE A.AuctionID NOT IN (
    SELECT DISTINCT B.AuctionID
    FROM Bid B
    WHERE B.BuyerID = $userID
) AND C.CategoryName IN (
  SELECT DISTINCT C2.CategoryName
    FROM Bid B
    JOIN CategoryAuction CA2 ON B.AuctionID = CA2.AuctionID
    JOIN Category C2 ON CA2.CategoryID = C2.CategoryID
    WHERE B.BuyerID = $userID
) LIMIT 10";

  // TODO: Perform a query to pull up auctions they might be interested in.
  // $query = "SELECT Auction.AuctionID, Auction.ProductName, Auction.Description, Auction.CurrentBid, (SELECT COUNT(*) FROM Bid WHERE AuctionID = Auction.AuctionID) AS BidsNumber, Auction.EndDate FROM Auction
  // JOIN BidHistory ON Auction.AuctionID = BidHistory.AuctionID
  // WHERE BidHistory.BuyerID = $userID AND Auction.AuctionID NOT IN (SELECT AuctionID FROM BidHistory WHERE BuyerID = $userID)
  // ORDER BY BidHistory.BidAmount DESC LIMIT 10";

  // TODO: Loop through results and print them out as list items.
  $stmt = $pdo->prepare($query);
  $auctionData = $stmt->execute();
  $auctionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor();

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

        print_listing_li($auction_id, $title, $description, $current_price, $num_bids, $end_date);
      }
      echo '</ul></div>';
    } catch (PDOException $e) {
      die("Query failed: " . $e->getMessage());
    }
  } else {

    echo ("<p class='not-found'> No auctions found. </p>");
  }

  echo "<br>";

  ?>


<?php include_once("footer.php") ?>