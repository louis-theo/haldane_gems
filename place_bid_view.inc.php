<?php

declare(strict_types=1);

function check_bid_error() {
  if (isset($_SESSION['errors_bid'])) {
      $errors = $_SESSION["errors_bid"];

      echo '<br>';

      foreach ($errors as $error) {
          echo '<p class="form-error">' . $error . '</p>';
      }

      unset($_SESSION['errors_bid']);

  } else if (isset($_GET['bid']) && $_GET['bid'] == 'success') {
    // $auction_id = $_SESSION["auction_id"];
      unset($_SESSION['bid_data']);
      echo '<br>';
      echo '<p class="form-success" id="success-message">Bid success!</p>';
  }

}
      ?>