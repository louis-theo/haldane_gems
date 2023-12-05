

<?php

// display_time_remaining:
// Helper function to help figure out what time to display
function display_time_remaining($interval)
{

  if ($interval->days == 0 && $interval->h == 0) {
    // Less than one hour remaining: print mins + seconds:
    $time_remaining = $interval->format('%im %Ss');
  } else if ($interval->days == 0) {
    // Less than one day remaining: print hrs + mins:
    $time_remaining = $interval->format('%hh %im');
  } else {
    // At least one day remaining: print days + hrs:
    $time_remaining = $interval->format('%ad %hh');
  }

  return $time_remaining;
}

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($auction_id, $title, $desc, $price, $num_bids, $end_time)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  } else {
    $bid = ' bids';
  }

  // Calculate time to auction end
  $now = new DateTime();


  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
  } else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }

  // Print HTML
  echo ('
  <a class="item-link" href="listing.php?auction_id=' . $auction_id . '">
  <li class="auction-item">
    <div class="item-descr">
      <h5 class="blue-text">' . $title . '</h5>' . $desc_shortened . '
    </div>
    <div class="info-item-container">
      <div class="current-bid">
        <p class="listing-label"> Current bid: </p>
          <span style="font-size: 1.5em" class="blue-text">£' . number_format($price, 2) . '</span>
      </div>
      <div class="listing-num-container">
        <p class="listing-info"><span class="blue-text">' . $num_bids . '</span> ' . $bid . '</p>
        <p class="listing-num-container">' . $time_remaining . '</p>
      </div>
    </div>
  </li>
  </a>'
  );
}

//function to print single bid by user
function print_bid_li($auction_id, $title, $desc, $price, $num_bids, $end_time, $user_bids_list)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  } else {
    $bid = ' bids';
  }

  // Calculate time to auction end
  $now = new DateTime();
  $end_time = new DateTime($end_time);

  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
  } else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }

  // Print HTML
  echo ('
  <a class="item-link" href="listing.php?auction_id=' . $auction_id . '">
  <li class="auction-item">
    <div class="item-descr"><h5 class="blue-text">' . $title . '</h5>' . $desc_shortened . '</div>
    <div class="info-bids-container">
      <div class="current-bid">
        <p class="listing-label"> Current bid: </p><span style="font-size: 1.5em" class="blue-text">£' . number_format($price, 2) . '</span>
      </div>
      <div class="listing-num-container">
        <p class="listing-info"><span class="blue-text"> ' . $num_bids . '</span> ' . $bid . '</p>
        <p class="listing-num-container">' . $time_remaining . '</p>
      </div>
      <div class="all-recent-bids">
        <span  class="blue-text"> Your Recent Bids</span>
        <div class="recent-bids-container">');
  for ($i = 0; $i < count($user_bids_list); $i++) {
    echo ('<span class="recent-bids"> £' . $user_bids_list[$i] . '</span>');
  };
  echo '</div></div></li></a>';
}


// functions for listing with winner
function print_winner_li($auction_id, $title, $desc, $price, $num_bids, $end_time, $winner)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  } else {
    $bid = ' bids';
  }

  // Calculate time to auction end
  $now = new DateTime();
  // $end_time = new DateTime($end_time);

  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
  } else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }

  // Print HTML
  echo ('
  <a class="item-link" href="listing.php?auction_id=' . $auction_id . '">
  <li class="auction-item">
    <div class="item-descr"><h5 class="blue-text">' . $title . '</h5>' . $desc_shortened . '</div>
    <div class="info-bids-container">
      <div class="current-bid">
        <p class="listing-label"> Current bid: </p><span style="font-size: 1.5em" class="blue-text">£' . number_format($price, 2) . '</span>
      </div>
      <div class="listing-num-container-with-win">
        <p class="listing-info"><span class="blue-text"> ' . $num_bids . '</span> ' . $bid . '</p>
        <p class="listing-num-container">' . $time_remaining . '</p>
      </div>
      <div class="listing-num-container-win">
      <p class="listing-info"><b> Winner: </b></p>
      <p class="listing-num-container"><span class="blue-text">' . $winner . '</span></p>
    </div>
      
      </div></li></a>');
}
