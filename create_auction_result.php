<?php include_once("header.php"); ?>

<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>

<div class="container my-5">

<?php
$server_name = "localhost";
$user_name = "root";
$password = "";
$db_name = "auctiondatabase";

$conn = mysqli_connect($server_name, $user_name, $password, $db_name);

if (mysqli_connect_errno()) {
    echo "Failed to Connect: " . mysqli_connect_error();
    exit();
}
echo "Connection Success!";
// ...

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];
    $productName = $_POST["productName"];
    $endDate = $_POST["endDate"];
    $currentBid = floatval(0);
    $reservePrice = $_POST["reservePrice"];
    $description = $_POST["description"];
    $productCondition = $_POST["productCondition"];
    $startPrice = $_POST["startPrice"];
    $categoryNames = $_POST["CategoryName"];

    // Perform basic input validation
    if (empty($productName) || empty($endDate) || empty($startPrice) || empty($categoryNames)) {
        echo "Please fill in all required fields.";
    } else {
        // Format dates to match the expected format (YYYY-MM-DDTHH:MM:SS)
        $formattedEndDate = date("Y-m-d\TH:i:s", strtotime($endDate));

        $insert_query = "INSERT INTO Auction (ProductName, SellerID, EndDate, CurrentBid, ReservePrice, Description, ProductCondition, StartPrice)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($insert_query)) {
            $stmt->bind_param("sissdssi", $productName, $user_id, $formattedEndDate, $currentBid, $reservePrice, $description, $productCondition, $startPrice);
            if ($stmt->execute()) {
                // Auction created successfully, get the auction ID
                $auctionID = $stmt->insert_id;

                // If the user is a buyer, change their account type to buyer_seller
                if ($_SESSION["account_type"] == "buyer") {
                    $_SESSION["account_type"] = "buyer_seller";
                }

                // Insert the auction ID and category names into the CategoryAuction table
                $insert_category_query = "INSERT INTO CategoryAuction (AuctionID, CategoryID) VALUES (?, ?)";
                foreach ($categoryNames as $categoryName) {
                    // Here, retrieve the CategoryID based on CategoryName from your Category table
                    $get_category_id_query = "SELECT CategoryID FROM Category WHERE CategoryName = ?";
                    if ($stmt_category_id = $conn->prepare($get_category_id_query)) {
                        $stmt_category_id->bind_param("s", $categoryName);
                        $stmt_category_id->execute();
                        $stmt_category_id->bind_result($categoryID);
                        $stmt_category_id->fetch();
                        $stmt_category_id->close();

                        // Insert into CategoryAuction
                        if (isset($categoryID)) {
                            if ($stmt_category = $conn->prepare($insert_category_query)) {
                                $stmt_category->bind_param("ii", $auctionID, $categoryID);
                                if ($stmt_category->execute()) {
                                    // Auction-category association created successfully
                                } else {
                                    echo "Error inserting into CategoryAuction: " . $stmt_category->error;
                                }
                                $stmt_category->close();
                            } else {
                                echo "Error preparing category insertion query: " . $conn->error;
                            }
                        }
                    } else {
                        echo "Error preparing category ID query: " . $conn->error;
                    }
                }
                
                header("Location: mylistings.php");
                exit();
            } else {
                echo "Error inserting into Auction: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing auction insertion query: " . $conn->error;
        }
    }
}
$conn->close();

// how is this suposed to work :crying:face:emoji:
echo('<div class="text-center">Auction successfully created! <a href="mylistings.php">View your new listing.</a></div>'); 
?>

</div>

<?php include_once("footer.php"); ?>
