<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<div class="container">
  <h2 class="browse-header">Browse auctions</h2>
  <div id="searchSpecs">
    <form method="get" action="browse.php">
      <div class="row">
        <div class="col-md-5 pr-0">
          <div class="form-group">
            <label for="keyword" class="sr-only">Search keyword:</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-transparent pr-0 text-muted">
                  <i class="fa fa-search"></i>
                </span>
              </div>
              <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search">
            </div>
          </div>
        </div>
        <div class="col-md-3 pr-0">
          <div class="form-group">
            <label for="category" class="sr-only">Search within:</label>
            <select class="form-control" id="category" name="category">
              <option selected value="all" <?php echo (!isset($_GET['category']) || $_GET['category'] == 'all') ? 'selected' : ''; ?>>All categories</option>
              <option value="Silver" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Silver') ? 'selected' : ''; ?>>Silver</option>
              <option value="Bronze" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Bronze') ? 'selected' : ''; ?>>Bronze</option>
              <option value="Platinum" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Platinum') ? 'selected' : ''; ?>>Platinum</option><!-- Add other category options similarly -->
              <option value="Rose Gold" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Rose Gold') ? 'selected' : ''; ?>>Rose Gold</option>
              <option value="Gemstone" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Gemstone') ? 'selected' : ''; ?>>Gemstone</option>
              <option value="Male" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Female') ? 'selected' : ''; ?>>Female</option>
              <option value="Unisex" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Unisex') ? 'selected' : ''; ?>>Unisex</option>
              <option value="Watches" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Watches') ? 'selected' : ''; ?>>Watches</option>
              <option value="Rings" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Rings') ? 'selected' : ''; ?>>Rings</option>
              <option value="Bracelets" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Bracelets') ? 'selected' : ''; ?>>Bracelets</option>
              <option value="Earrings" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Earrings') ? 'selected' : ''; ?>>Earrings</option>
              <option value="Necklaces" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Necklaces') ? 'selected' : ''; ?>>Necklaces</option>
            </select>
          </div>
        </div>
        <div class="col-md-3 pr-0">
          <div class="form-inline">
            <label class="mx-2" for="order_by">Sort by:</label>
            <select class="form-control" id="order_by" name="order_by">
              <option value="pricelow" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'pricelow') ? 'selected' : ''; ?>>Price (low to high)</option>
              <option value="pricehigh" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'pricehigh') ? 'selected' : ''; ?>>Price (high to low)</option>
              <option value="date" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'date') ? 'selected' : ''; ?>>Soonest expiry</option>
            </select>
          </div>
        </div>
        <div class="col-md-1 px-0">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </div>
  </div>

  <div class="form-inline">
    <label class="condition" for="condition">Condition:</label>
    <select class="form-control" id="condition" name="condition">
      <option value="all" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'all') ? 'selected' : ''; ?>>All</option>
      <option value="new" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'new') ? 'selected' : ''; ?>>New</option>
      <option value="used" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'used') ? 'selected' : ''; ?>>Used</option>
    </select>

    </form>
  </div>

</div>

<?php

// Retrieve these from the URL
if (!isset($_GET['keyword'])) {
  $keyword = '';
} else {
  $keyword = $_GET['keyword'];
}
$searchTerm = isset($keyword) ? '%' . $keyword . '%' : '%';

if (!isset($_GET['category'])) {
  $category = 'all';
} else {
  $category = $_GET['category'];
}

if (!isset($_GET['order_by'])) {
  $ordering = 'date';
} else {
  $ordering = $_GET['order_by'];
}

if (!isset($_GET['page'])) {
  $curr_page = 1;
} else {
  $curr_page = $_GET['page'];
}

// Establish a database connection
$server_name = "localhost";
$user_name = "root";
$password = "";
$db_name = "auctiondatabase";

$conn = new mysqli($server_name, $user_name, $password, $db_name);

// Check the connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Define the search term and category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;
$sortingOption = isset($_GET['sort']) ? $_GET['sort'] : 'endDate';
$productCondition = isset($_GET['condition']) ? $_GET['condition'] : 'all';


// PAGINATION 
$results_per_page = 10;

// Current page or set it to 1
$curr_page = isset($_GET['page']) ? $_GET['page'] : 1;
// Query to get the total number of results
// Define the query
$query = "SELECT DISTINCT a.*
          FROM Auction a
          LEFT JOIN CategoryAuction ca ON a.AuctionID = ca.AuctionID
          LEFT JOIN Category c ON ca.CategoryID = c.CategoryID
          WHERE a.ProductName LIKE ? AND a.EndDate > NOW()";

if (!empty($category) && $category !== 'all') {
  $query .= " AND c.CategoryName = ?";
}
if ($productCondition !== 'all') {
  $query .= " AND a.ProductCondition = ?";
}

switch ($ordering) {
  case 'pricelow':
    $query .= " ORDER BY a.CurrentBid ASC";
    break;
  case 'pricehigh':
    $query .= " ORDER BY a.CurrentBid DESC";
    break;
  case 'date':
  default:
    $query .= " ORDER BY a.EndDate ASC";
    break;
}

$stmt = $conn->prepare($query);

if ($stmt === false) {
  die('Error during prepare: ' . $conn->error);
}

#$stmt->bind_param("s", $searchTerm);

// Bind parameters if category is not empty
if ($category !== 'all' && $productCondition !== 'all') {
  $stmt->bind_param("sss", $searchTerm, $category, $productCondition);
} elseif ($category !== 'all') {
  $stmt->bind_param("ss", $searchTerm, $category);
} elseif ($productCondition !== 'all') {
  $stmt->bind_param("ss", $searchTerm, $productCondition);
} else {
  $stmt->bind_param("s", $searchTerm);
}


$stmt->execute();

// Get the result set
$result = $stmt->get_result();
$total_results = $result->num_rows;

// Calculate the total number of pages
$max_page = ceil($total_results / $results_per_page);
$offset = ($curr_page - 1) * $results_per_page;





// Define the query
$query = "SELECT DISTINCT a.*
          FROM Auction a
          LEFT JOIN CategoryAuction ca ON a.AuctionID = ca.AuctionID
          LEFT JOIN Category c ON ca.CategoryID = c.CategoryID
          WHERE a.ProductName LIKE ? AND a.EndDate > NOW()";

if (!empty($category) && $category !== 'all') {
  $query .= " AND c.CategoryName = ?";
}
if ($productCondition !== 'all') {
  $query .= " AND a.ProductCondition = ?";
}

switch ($ordering) {
  case 'pricelow':
    $query .= " ORDER BY a.CurrentBid ASC";
    break;
  case 'pricehigh':
    $query .= " ORDER BY a.CurrentBid DESC";
    break;
  case 'date':
  default:
    $query .= " ORDER BY a.EndDate ASC";
    break;
}
$query .= " LIMIT $offset, $results_per_page;";

$stmt = $conn->prepare($query);

if ($stmt === false) {
  die('Error during prepare: ' . $conn->error);
}

#$stmt->bind_param("s", $searchTerm);

// Bind parameters if category is not empty
if ($category !== 'all' && $productCondition !== 'all') {
  $stmt->bind_param("sss", $searchTerm, $category, $productCondition);
} elseif ($category !== 'all') {
  $stmt->bind_param("ss", $searchTerm, $category);
} elseif ($productCondition !== 'all') {
  $stmt->bind_param("ss", $searchTerm, $productCondition);
} else {
  $stmt->bind_param("s", $searchTerm);
}


$stmt->execute();

// Get the result set
$result = $stmt->get_result();
$total_results = $result->num_rows;

if ($result->num_rows > 0) {
  echo '<div class="container mt-5">';
  echo '<ul class="list-group">';

  // Fetch each row from the result
  while ($row = $result->fetch_assoc()) {
    $price = isset($row['CurrentBid']) && $row['CurrentBid'] != 0 ? $row['CurrentBid'] : $row['StartPrice'];
    $auction_id = $row["AuctionID"];

    // //number of bids query
    $sql_num_bid = "SELECT COUNT(*) AS count FROM bid WHERE AuctionID = $auction_id";
    $result_bids = $conn->query($sql_num_bid);
    $row_num_bids = $result_bids->fetch_assoc();
    $num_bids = $row_num_bids["count"];
    $result_bids->free();


    // Display each auction listing using the print_listing_li function
    print_listing_li($row['AuctionID'], $row['ProductName'], $row['Description'], $price, $num_bids, new DateTime($row['EndDate']));
  }

  echo '</ul>';
} else {
  echo '<p class="not-found">No listings found based on the specified criteria.</p>';
};

$stmt->close();
$conn->close();
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
    }

    if ($curr_page != $max_page) {
      echo ('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
    }
    ?>

  </ul>
</nav>

<?php include_once("footer.php") ?>