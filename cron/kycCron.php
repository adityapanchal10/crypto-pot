<?php

include "../db_connect.php";

function generate_key($con) {
    $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < 40; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    $stmt = $con->prepare('SELECT wallet_id FROM walletMappingMaster WHERE wallet_private_key = ?');
    $stmt->bind_param('s', $random_string);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $random_string = generate_key();
    }
    return $random_string;
}

function generate_address($con) {
    $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < 30; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    $stmt = $con->prepare('SELECT wallet_id FROM walletMappingMaster WHERE wallet_address = ?');
    $stmt->bind_param('s', $random_string);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $random_string = generate_address();
    }
    return $random_string;
}


function create_wallet_mapping($user_id, $walletid, $currency, $con) {
    $stmt = $con->prepare('SELECT currency_id FROM priceMaster WHERE currency_name = ?');
    $stmt->bind_param('s', $currency);
    if (!$stmt->execute()) {
        return false;
    }
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($currency_id);
        $stmt->fetch();
        $address = generate_address($con);
        $key = generate_key($con);
        if ($currency == 'USD') {
            $balance = 100000;
        } else {
            $balance = 0;
        }
        $stmt = $con->prepare('INSERT INTO walletMappingMaster (userid, wallet_id, currency_id, wallet_address, wallet_private_key, wallet_balance, isWalletActive) VALUES (?, ?, ?, ?, ?, ?, 1)');
        $stmt->bind_param('iiissi', $user_id, $walletid, $currency_id, $address, $key, $balance);
        if (!$stmt->execute()) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function create_wallets($user_id, $con) {
    $stmt = $con->prepare('INSERT INTO walletMaster (wallet_type, wallet_name) VALUES (?, ?)');
    $currency = 'USD';
    $name = 'UnitedStates Dollar';
    $stmt->bind_param('ss', $name, $currency);
    $stmt->execute();
    $stmt->close();
    create_wallet_mapping($user_id, $con->insert_id, 'USD', $con);
    $currencies = [
    'BTC' => 'Bitcoin',
    'ETH' => 'Ethereum',
    'USDT' => 'Tether',
    'DOGE' => 'Dogecoin',
    'MATIC' => 'Polygon',
    'UNI' => 'Uniswap',
    'XRP' => 'Ripple',
    'EOS' => 'EOS',
    'SHIB' => 'Shiba Inu',
    'SOL' => 'Solana',
    ];
    foreach ($currencies as $currency => $name) {
        $stmt = $con->prepare('INSERT INTO walletMaster (wallet_type, wallet_name) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $currency);
        $stmt->execute();
        create_wallet_mapping($user_id, $con->insert_id, $currency, $con);
    }
    $stmt->close();
    return true;
}

function approve_kyc($con) {
    $stmt = $con->prepare('SELECT userid FROM userMaster WHERE is_KYC_request_sent = 1 AND isKYCverified = 0');
    $stmt->execute();
    // Store the result so we can check if the account exists in the database.
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userid);
        while ($stmt->fetch()) {
            if (create_wallets($userid, $con)) {
                $stmt = $con->prepare('UPDATE userMaster SET isKYCverified = 1 WHERE userid = ?');
                $stmt->bind_param('s', $userid);
                $stmt->execute();
            }
        }
    }
}

approve_kyc($con);

?>