<?php 
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once 'config_session.inc.php';
require_once 'signup_view.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
  <?php include_once("header.php") ?>
</head>

<body>
<div class="container">
  <h2 class="my-3">Register new account</h2>

  <?php 
  check_signup_error();
  ?>

  <!-- Create auction form -->
  <form method="POST" action="process_registration2.php">
 
    <?php
    signup_inputs();
    ?>

  <button type="submit" class="btn btn-primary form-control">Register</button>
  </form>

  <div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

</div>
</body>
</html>

<?php include_once("footer.php") ?>

