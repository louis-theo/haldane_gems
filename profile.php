<?php include_once('header.php') ?>
<?php require('utilities.php') ?>
<?php require_once 'config.php' ?>

<div class='container whole-container'>
    <div class='smaller-cont'>



        <?php

        if (isset($_SESSION['id'])) {
            $userId = $_SESSION['id'];
        };
        $commentFormDisplayed = false;

        if (isset($_GET['seller_id'])) {
            $profile_id = $_GET['seller_id'];
        } else {
            $profile_id = $userId;
        };


        // PAGINATION 
        $results_per_page = 5;

        // Current page or set it to 1
        $curr_page = isset($_GET['page']) ? $_GET['page'] : 1;

        // Query to get the total number of results
        $sql_count = "SELECT COUNT(CommentText) as total FROM comment WHERE SellerID = $profile_id";
        $result_count = $mysqli->query($sql_count);
        $row_count = $result_count->fetch_assoc();
        $total_results = $row_count['total'];
        $result_count->free();

        // Calculate the total number of pages
        $max_page = ceil($total_results / $results_per_page);
        $offset = ($curr_page - 1) * $results_per_page;


        // submit rating 

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
            $rating_value = $_POST['rating'];
            $insert_rating_sql = "INSERT INTO Ratings (SellerID, RatingScore, BuyerID)
            VALUES ($profile_id,$rating_value, $userId)";
            $mysqli->query($insert_rating_sql);
            header('refresh:1;url=profile.php?seller_id=' . $profile_id);
        };

        // query general user info
        $general_info_sql = "SELECT * FROM users WHERE UserID = $profile_id";
        $general_info_result = $mysqli->query($general_info_sql);
        $info = $general_info_result->fetch_assoc();

        $username = $info["Username"];
        $email = $info["Email"];
        $address = $info["Address"];
        $registration_date_db = $info["RegistrationDate"];
        $registration_date_non_format = DateTime::createFromFormat('Y-m-d H:i:s', $registration_date_db);
        $registration_date = $registration_date_non_format->format('d-m-Y');

        $general_info_result->free();


        // delete profile
        if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['delete_profile'])) {
            $delete_sql = "DELETE FROM users WHERE UserID = $profile_id";
            if ($mysqli->query($delete_sql)) {
                unset($_SESSION['logged_in']);
                unset($_SESSION['account_type']);
                setcookie(session_name(), "", time() - 360);
                session_destroy();

                echo ("Profile deleted. You will be logged out in a second");
                $mysqli->close();

                // Redirect to index
                header("refresh:2;url=index.php");
            } else {
                echo "Error deleting your profile:" . mysqli_error($mysqli);
            }
        };


        // delete comment 
        if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['delete_comment'])) {
            $commentID_to_delete = $_POST["comment_id"];
            $delete_sql = "DELETE FROM comment WHERE CommentID = $commentID_to_delete";
            if ($mysqli->query($delete_sql)) {
                echo ("<p class='not-found'>Comment deleted</p>");

                // Refresh page
                header("refresh:2");
            } else {
                echo "Error deleting your comment:" . mysqli_error($mysqli);
            }
        };

        if ($userId == $profile_id) {
            echo " <h2 class='browse-header'>My profile</h2>       
                <div class='buttons-wrap'>
                    <a class='btn btn-outline-primary btn-sm' style='margin-right: 1.4rem' href='edit_profile.php''>Edit Profile</a>
                    <form method='post' action=''>
                        <button type='submit' name='delete_profile' class='btn btn-outline-primary btn-sm btn-delete' >Delete Profile</button>
                    </form>
                </div>";
        }
        ?>

    </div>

    <?php
    echo "
    <div class='info'> 
        <h3 class='info-header'> Username: " . $username . "</h3>
        <div class='info_container'>
            <p class='info__text'><b>Email: </b>" . $email . " </p>
            <p class='info__text'><b>Address: </b>" . $address . " </p>
            <p class='info__text'><b>Registration Date: </b>" .  $registration_date . " </p>";


    if ($userId != $profile_id) {
        // SELLER'S RATING
        $profile_rating_sql = "SELECT AVG(RatingScore) as average FROM ratings WHERE SellerID = $profile_id";
        $profile_rating_result = $mysqli->query($profile_rating_sql);
        $profile_rating_fetch = $profile_rating_result->fetch_assoc();
        $profile_rating = round($profile_rating_fetch["average"], 1);
        $profile_rating_result->free();

        echo " 
        <p class='info__text'><b> Seller rating: </b><span style='color:#007bff; font-weight:bold'> " . $profile_rating . "</span> </p>";

        //    check if the user already rated the seller
        $profile_rating_check_sql = "SELECT RatingScore FROM ratings WHERE SellerID = $profile_id AND BuyerID=$userId";
        $profile_rating_check_result = $mysqli->query($profile_rating_check_sql);
        $profile_rating_check_fetch = $profile_rating_check_result->fetch_assoc();

        $profile_rated = $profile_rating_check_result->num_rows > 0 ? $profile_rating_check_fetch["RatingScore"] : NULL;


        if ($profile_rating_check_result->num_rows == 0) {
            echo " <form method='post' action='' class='rating-wrap'>
                <label><b>Please rate this seller:</b> </label>
                <div style='display:flex; flex-direction:row; gap:1rem'>
                    <label for='rating'><input type='radio' name='rating' value='1' /> 1 </label>
                    <label for='rating'><input type='radio' name='rating' value='2' /> 2 </label>
                    <label for='rating'><input type='radio' name='rating' value='3' /> 3 </label>
                    <label for='rating'><input type='radio' name='rating' value='4' /> 4 </label>
                    <label for='rating'><input type='radio' name='rating' value='5' /> 5 </label>
                </div>
                <button type='submit' class='btn btn-primary' style='width: 15rem;'>Rate</button>
            </form>";
        } else {
            // query the rating the user gave this seller
            echo "<p class='info__text' style='margin-top: 2rem'><b> You rated this seller: </b><span style='color:#007bff; font-weight:bold'> " . $profile_rated . "</span> out of 5 </p>";
        }
        $profile_rating_check_result->free();
    };

    echo "</div>
</div>";





    // QUERY STATISTICS

    // AUCTIONS PARTICIPATED
    $participated_sql = "SELECT COUNT(DISTINCT AuctionID) AS total_auctions FROM bid WHERE BuyerID = $userId";
    $participated_result = $mysqli->query($participated_sql);
    $participated_count = $participated_result->fetch_assoc();
    $participated_auctions = $participated_count["total_auctions"];
    $participated_result->free();

    // WON AUCTIONS SUM OF AMOUNT SPENT 
    //auctions that ended and user participated
    $sql_data_won = "SELECT b.BidID, b.BuyerID, b.AuctionID, b.BidAmount, a.Status
    FROM  bid AS b 
    INNER JOIN auction AS a ON b.AuctionID = a.AuctionID
    WHERE BuyerID = $userId and a.Status = 'Sold'
    GROUP BY b.AuctionID;";
    $won_result = $mysqli->query($sql_data_won);
    $num_winnings = 0;
    $sum = 0;
    $won_bids_arr = [];

    while ($row = $won_result->fetch_assoc()) {
        $auction_id = $row["AuctionID"];

        // query for the max price for each auction participated
        $sql_max_price = "SELECT MAX(BidAmount) as max_bid, BuyerID FROM bid WHERE AuctionID = $auction_id;";
        $max_result = $mysqli->query($sql_max_price);

        if ($max_result) {
            $max_row = $max_result->fetch_assoc();
            // check if the max bid was made by current user - ie won
            if ($max_row["BuyerID"] == $userId) {
                $won_bids_arr[] = $max_row["max_bid"];
                $num_winnings += 1;
                $sum += $max_row["max_bid"];
            }
        }
    };
    if ($won_bids_arr) {
        $highest_spent = max($won_bids_arr);
    } else {
        $highest_spent = 0;
    }



    // EXTRA STATS FOR SELLERS
    if ($userId == $profile_id) {

        echo "<div class='statistics'>
        <h3 class='stats-header''>My Statistics</h3>
        <div class='stats-container'>
            <div style='margin-right: 20.4rem;'>
                <p class='stats__text'>Participated in <span style='color:#007bff; font-weight:bold'>" . $participated_auctions . "</span> auctions </p>
                <p class='stats__text'>Won auctions: <span style='color:#007bff; font-weight:bold'>" . $num_winnings . "</span> </p>
                <p class='stats__text'>Total spent: <span style='color:#007bff; font-weight:bold'> £" . $sum . "</span> </p>
                <p class='stats__text'>Highest bid on won auction: <span style='color:#007bff; font-weight:bold'> £" . $highest_spent . "</span> </p>
            </div>";

        if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer_seller') {

            // SOLD AUCTIONS
            $sold_sql = "SELECT COUNT(DISTINCT AuctionID) AS sold_auctions FROM auction WHERE SellerID = $userId AND Status = 'Sold'";
            $sold_result = $mysqli->query($sold_sql);
            $sold_count = $sold_result->fetch_assoc();
            $sold_auctions = $sold_count["sold_auctions"];
            $sold_result->free();

            // maybe can do sum?????
            // MONEY RECEIVED
            $money_received_sql = "SELECT DISTINCT AuctionID, CurrentBid FROM auction WHERE SellerID = $userId AND Status = 'Sold'";
            $money_received_result = $mysqli->query($money_received_sql);
            $sum_received = 0;
            while ($money_row = $money_received_result->fetch_assoc()) {
                $sum_received += $money_row["CurrentBid"];
            };
            $money_received_result->free();

            // SELLER'S RATING
            $seller_rating_sql = "SELECT AVG(RatingScore) as average FROM ratings WHERE SellerID = $userId";
            $seller_rating_result = $mysqli->query($seller_rating_sql);
            $seller_rating_fetch = $seller_rating_result->fetch_assoc();
            $seller_rating = round($seller_rating_fetch["average"], 1);
            $seller_rating_result->free();


            echo "
            <div>
                <p class='info__text'> Sold products: <span style='color:#007bff; font-weight:bold'>" . $sold_auctions . "</span>  </p>
                <p class='info__text'> Money gained: <span style='color:#007bff; font-weight:bold'> £" . $sum_received . "</span> </p>
                <p class='info__text'> Your rating: <span style='color:#007bff; font-weight:bold'> " . $seller_rating . "</span> </p>
             </div>
        </div>";
        }
    };






    // -------------------- COMMENT SECTION ---------------------

    if ($userId == $profile_id) {
        echo "<h2> Comments about you </h2>";
    } else if ($userId != $profile_id) {
        $_SESSION['comment_profile_id'] = $profile_id;

        echo "<div class='top-comment-container'>
                <h2> Comments about the seller </h2>
                <a class='btn btn-primary form-control button-add' href='add_comment.php?comment_id='" .  $profile_id . "'>+ Add comment</a>
              </div>";
    };

    echo "<div class='comments-wrap'>";
    $comments_sql = "SELECT c.CommentText, u.Username, c.CommentTime, c.ComentatorID, c.CommentID
                        FROM Comment AS c
                        INNER JOIN Users AS u ON c.ComentatorID = u.UserID
                        WHERE SellerID = $profile_id
                        ORDER BY c.CommentTime DESC
                        LIMIT $offset, $results_per_page;";

    $comments_result = $mysqli->query($comments_sql);
    while ($comment_row = $comments_result->fetch_assoc()) {
        $comentator = $comment_row["Username"];
        $comment = $comment_row["CommentText"];
        $comment_time = $comment_row["CommentTime"];
        $commentator_id = $comment_row["ComentatorID"];
        $comment_id = $comment_row["CommentID"];

        $comment_time = DateTime::createFromFormat('Y-m-d H:i:s', $comment_time);
        $comment_time_formatted = $comment_time->format('H:i d-m-Y');

        echo ('
            <li class="comment-item">
                <div class="comment-wrap">
                    <div class="comment-left-wrap">
                        <h5>' . $comentator . '</h5>
                        <p>' . $comment_time_formatted . '</p>
                    </div>
                <div class="comment-right-wrap">
                    <p>' . $comment . '</p>');

        if ($commentator_id == $userId) {
            echo ("
            <form method='post' action=''>
                <input type='hidden' name='comment_id' value=" . $comment_id . " />
                <button type='submit' name='delete_comment' class='btn btn-outline-primary btn-sm btn-delete'>Delete</button>
            </form>");
        };
        echo "</div></li>";
    };

    $comments_result->free();

    echo "</ul></div>";
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
        <a class="page-link" href="profile.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
        <a class="page-link" href="profile.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
        </li>');
            }

            if ($curr_page != $max_page) {
                echo ('
        <li class="page-item">
        <a class="page-link" href="profile.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
            <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
            <span class="sr-only">Next</span>
        </a>
        </li>');
            }
            ?>

        </ul>
    </nav>







    <?php include_once("footer.php") ?>