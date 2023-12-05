<?php

declare(strict_types=1);

function signup_inputs()
{

  if (isset($_SESSION['signup_data']['username']) && !(isset($_SESSION['errors_signup']['username_taken']))) {
    echo '
      <div class="form-group row">
      <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="username" placeholder="Username" value="' . $_SESSION["signup_data"]["username"] . '">
        <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>';
  } else {
    echo '
      <div class="form-group row">
      <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="username" placeholder="Username">
        <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>';
  }

  if (isset($_SESSION['signup_data']['email']) && !(isset($_SESSION['errors_signup']['email_used'])) && !(isset($_SESSION['errors_signup']['invalid_email']))) {
    echo '    
      <div class="form-group row">
      <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="email" placeholder="Email" value="' . $_SESSION["signup_data"]["email"] . '">
        <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>';
  } else {
    echo '    
      <div class="form-group row">
      <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="email" placeholder="Email">
        <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>';
  }

  echo '
      <div class="form-group row">
      <label for="address" class="col-sm-2 col-form-label text-right">Address</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="address" placeholder="Address" value="' . $_SESSION["signup_data"]["address"] . '">
        <small id="addressHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>
      <div class="form-group row">
      <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
      <div class="col-sm-10">
        <input type="password" class="form-control" name="password" placeholder="Password">
        <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>
    <div class="form-group row">
      <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
      <div class="col-sm-10">
        <input type="password" class="form-control" name ="passwordConfirmation" placeholder="Enter password again">
        <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>';
}


function check_signup_error()
{
  if (isset($_SESSION['errors_signup'])) {
    $errors = $_SESSION["errors_signup"];

    echo '<br>';

    foreach ($errors as $error) {
      echo '<p class="form-error">' . $error . '</p>';
    }

    unset($_SESSION['errors_signup']);
  } else if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    unset($_SESSION['signup_data']);
    echo '<br>';
    echo ("<p class='not-found'>Signup success! Please log in</p>");
    header("refresh:2;url=browse.php");
  }
}
