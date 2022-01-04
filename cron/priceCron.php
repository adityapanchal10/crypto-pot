<?php

include "../db_connect.php";

$details = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin%2Cethereum%2Ctether%2Cdogecoin%2Cmatic-network%2Cuniswap%2Cripple%2Ceos%2Cshiba-inu%2Csolana&vs_currencies=usd&include_24hr_change=true');
$details = stripslashes(html_entity_decode($details));
$details = json_decode($details, true);
foreach($details as $currency => $price) {
  $change_24hr = sprintf("%.6f", $price["usd_24h_change"]);
  $price = sprintf("%.6f", $price["usd"]);
  // echo $currency . ": " . $price . ":" . $change_24hr . "<br>";
  if ($stmt = $con->prepare('UPDATE priceMaster SET currency_last_price = currency_price, currency_price = ?, change_24hr = ?, currency_price_update_timestamp = CURDATE() WHERE curr_alt = ?')) {
    $stmt->bind_param('dds', $price, $change_24hr, $currency);
    if ($stmt->execute()) {
      // echo "Successfully updated price";
    } else {
      // echo 'Failed to update price.';
      // echo $stmt->error;
    }
  } else {
    // echo "Failed to prepare statement";
    // echo $con->error;
  }
}

?>