<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require_once "config.php" ?>

<div>
    <div>
        <div class="modal-content" style="border: none;">



            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Add comment</h4>
            </div>
            <?php
            // commentator id
            $userId = $_SESSION['id'];
            $comment_profile_id = $_SESSION['comment_profile_id'];


            $formDisplayed = true;

            // submit changes into db
            if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['submit_changes'])) {
                // asign the POST form to variables
                $new_comment = trim($_POST["comment"]);

                $error = false;

                if (empty($new_comment)) {
                    echo "<small class='not-found'> The field can not be empty </small>";
                    $error = true;
                };

                if (!$error) {
                    $sql_comment_insert = "INSERT INTO comment (SellerID, ComentatorID, CommentText)
                                        VALUES ($comment_profile_id, $userId, '$new_comment')";
                    if ($mysqli->query($sql_comment_insert)) {
                        echo "<p class='not-found'>Comment added <p>";
                        $formDisplayed = false;
                        // Redirect to profile page
                        header("refresh:1;url=profile.php?seller_id=$comment_profile_id");
                    } else {
                        echo "Error adding a comment: " . $mysqli->error;
                    }

                    // Close the database connection
                    $mysqli->close();
                }
            };

            if ($formDisplayed) {
                echo "
                <div class='modal-body' style= 'width: 40rem; margin: 0 auto;'>
                    <form method='POST' action=''>
                    
                        <div class='form-group'>
                            <label for='comment'><b>Comment</b></label>
                            <textarea class='form-control' name='comment' id='comment' rows='5' style='resize: none;' placeholder='Write your comment here'></textarea>
                        </div>
        
                        <button type='submit' name='submit_changes'class='btn btn-primary form-control'>Submit your comment</button>
                
                    </form>
                </div>
            </div>
        </div>
    </div> ";
            }

            ?>