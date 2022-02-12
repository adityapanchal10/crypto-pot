<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();

include "db_connect.php";

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['email']) || isset($_SESSION['isVerified'])) {
	header('Location: login.php');
	exit();
} else if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ipaddress']) {
    session_unset();
    session_destroy();
    header('Location: login.php');
} else if ($_SERVER['HTTP_USER_AGENT'] != $_SESSION['useragent']) {
    session_unset();
    session_destroy();
    header('Location: login.php');
} else if (time() > ($_SESSION['lastaccess'] + 3600)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
} else if (!isset($_COOKIE['fnz_id'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
} else if (($_COOKIE['fnz_id'] != hash('sha256', $_COOKIE['v_id'] + $_SESSION['visitor_gen_time'])) && (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == 'no')) {
    session_unset();
    session_destroy();
    header('Location: login.php'); 
} else if ($_COOKIE['fnz_cookie_val'] == 'low' && !isset($_COOKIE['fnz_id'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
} else {
    if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
    }

    if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '' || !isset($_COOKIE['email'])) {
        $email = $_SESSION['email'];
    } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
        $email = base64_decode($_COOKIE['email']);
    } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
        $email = $_COOKIE['email'];
    }
    if ($stmt = $con->prepare('SELECT userid, remaining_balance, isVerified, is_KYC_request_sent FROM userMaster WHERE email_id = ?')) {
        $notification = '';
        $notifications = 0;

        $stmt->bind_param('s', $email);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid, $balance, $isVerified, $is_KYC_request_sent);
            $stmt->fetch();
            if ($isVerified == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
            }
            if ($is_KYC_request_sent == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
            }
            if (!isset($_GET['page'])) {
                $page = 1;
            } else {
                $page = $_GET['page'];
            }
            // $page_nav = '';
            // $buy_page_nav = '';
            // $sell_page_nav = '';
            // $trade_page_nav = '';
            // $transfer_page_nav = '';
            // if (!isset($_GET['type']) || $_GET['type'] == 'all') {
            //     if ($stmt = $con->prepare('SELECT transaction_id, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount, isTransactionApproved, isTransactionBlocked FROM transactionMaster WHERE userid = ?  ORDER BY transaction_id DESC')) {
            //         $stmt->bind_param('i', $userid);
            //         $stmt->execute();
            //         $stmt->store_result();
            //         if ($stmt->num_rows > 20) {
            //             $i = 0;
            //             $limit = $stmt->num_rows / 20;
            //             $limit = ceil($limit);
            //             if ($page_no > $limit) {
            //                 $page_no = $limit;
            //             }
            //             $page_nav .= '<nav aria-label="...">
            //             <ul class="pagination">';

            //             $next_page_no = $page_no + 1;
            //             $prev_page_no = $page_no - 1;
            //             if ($page_no == 1) {
            //                 $page_nav .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
            //             } else {
            //                 $page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($prev_page_no) . '" tabindex="-1">Previous</a></li>';
            //             }
            //             while ($i < $limit) {
            //                 if ($page_no == $i + 1) {
            //                     $page_nav .= '<li class="page-item active"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '  <span class="sr-only">(current)</span></a></li>';
            //                 } else {
            //                     $page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '</a></li>';
            //                 }
            //                 ++$i;
            //             }
            //             if ($page_no == $limit) {
            //                 $page_nav .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
            //             } else {
            //                 $page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($next_page_no) . '">Next</a></li>';
            //             }
            //             $page_nav .= '
            //                 </ul>
            //             </nav>';
            //         }
            //     }
            //     $type_css = '#trade, #sell, #buy, #transfer {
            //         display: none;
            //     }';
            // } else if ($_GET['type'] == 'buy') {
            //     if ($stmt = $con->prepare('SELECT transaction_id, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount, isTransactionApproved, isTransactionBlocked FROM transactionMaster WHERE userid = ? AND fromWallet = \'USD\' ORDER BY transaction_id DESC')) {
            //         $stmt->bind_param('i', $userid);
            //         $stmt->execute();
            //         $stmt->store_result();
            //         if ($stmt->num_rows > 20) {
            //             $i = 0;
            //             $limit = $stmt->num_rows / 20;
            //             $limit = ceil($limit);
            //             if ($page_no > $limit) {
            //                 $page_no = $limit;
            //             }
            //             $buy_page_nav .= '<nav aria-label="...">
            //             <ul class="pagination">';

            //             $next_page_no = $page_no + 1;
            //             $prev_page_no = $page_no - 1;
            //             if ($page_no == 1) {
            //                 $buy_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
            //             } else {
            //                 $buy_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($prev_page_no) . '" tabindex="-1">Previous</a></li>';
            //             }
            //             while ($i < $limit) {
            //                 if ($page_no == $i + 1) {
            //                     $buy_page_nav .= '<li class="page-item active"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '  <span class="sr-only">(current)</span></a></li>';
            //                 } else {
            //                     $buy_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '</a></li>';
            //                 }
            //                 ++$i;
            //             }
            //             if ($page_no == $limit) {
            //                 $buy_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
            //             } else {
            //                 $buy_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($next_page_no) . '">Next</a></li>';
            //             }
            //             $buy_page_nav .= '
            //                 </ul>
            //             </nav>';
            //         }
            //     }
            //     $type_css = '#all, #sell, #trade, #transfer {
            //         display: none;
            //     }';
            // } else if ($_GET['type'] == 'sell') {
            //     if ($stmt = $con->prepare('SELECT transaction_id, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount, isTransactionApproved, isTransactionBlocked FROM transactionMaster WHERE userid = ? AND toWallet = \'USD\' ORDER BY transaction_id DESC')) {
            //         $stmt->bind_param('i', $userid);
            //         $stmt->execute();
            //         $stmt->store_result();
            //         if ($stmt->num_rows > 20) {
            //             $i = 0;
            //             $limit = $stmt->num_rows / 20;
            //             $limit = ceil($limit);
            //             if ($page_no > $limit) {
            //                 $page_no = $limit;
            //             }
            //             $sell_page_nav .= '<nav aria-label="...">
            //             <ul class="pagination">';

            //             $next_page_no = $page_no + 1;
            //             $prev_page_no = $page_no - 1;
            //             if ($page_no == 1) {
            //                 $sell_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
            //             } else {
            //                 $sell_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($prev_page_no) . '" tabindex="-1">Previous</a></li>';
            //             }
            //             while ($i < $limit) {
            //                 if ($page_no == $i + 1) {
            //                     $sell_page_nav .= '<li class="page-item active"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '  <span class="sr-only">(current)</span></a></li>';
            //                 } else {
            //                     $sell_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '</a></li>';
            //                 }
            //                 ++$i;
            //             }
            //             if ($page_no == $limit) {
            //                 $sell_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
            //             } else {
            //                 $sell_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($next_page_no) . '">Next</a></li>';
            //             }
            //             $sell_page_nav .= '
            //                 </ul>
            //             </nav>';
            //         }
            //     }
            //     $type_css = '#all, #buy, #trade, #transfer {
            //         display: none;
            //     }';
            // } else if ($_GET['type'] == 'trade') {
            //     if ($stmt = $con->prepare('SELECT transaction_id, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount, isTransactionApproved, isTransactionBlocked FROM transactionMaster WHERE userid = ? AND fromWallet <> \'USD\' AND toWallet <> \'USD\' ORDER BY transaction_id DESC')) {
            //         $stmt->bind_param('i', $userid);
            //         $stmt->execute();
            //         $stmt->store_result();
            //         if ($stmt->num_rows > 20) {
            //             $i = 0;
            //             $limit = $stmt->num_rows / 20;
            //             $limit = ceil($limit);
            //             if ($page_no > $limit) {
            //                 $page_no = $limit;
            //             }
            //             $trade_page_nav .= '<nav aria-label="...">
            //             <ul class="pagination">';

            //             $next_page_no = $page_no + 1;
            //             $prev_page_no = $page_no - 1;
            //             if ($page_no == 1) {
            //                 $trade_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
            //             } else {
            //                 $trade_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($prev_page_no) . '" tabindex="-1">Previous</a></li>';
            //             }
            //             while ($i < $limit) {
            //                 if ($page_no == $i + 1) {
            //                     $trade_page_nav .= '<li class="page-item active"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '  <span class="sr-only">(current)</span></a></li>';
            //                 } else {
            //                     $trade_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '</a></li>';
            //                 }
            //                 ++$i;
            //             }
            //             if ($page_no == $limit) {
            //                 $trade_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
            //             } else {
            //                 $trade_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($next_page_no) . '">Next</a></li>';
            //             }
            //             $trade_page_nav .= '
            //                 </ul>
            //             </nav>';
            //         }
            //     }
            //     $type_css = '#all, #buy, #sell, #transfer {
            //         display: none;
            //     }';
            // } else if ($_GET['type'] == 'transfer') {
            //     if ($stmt = $con->prepare('SELECT transfer_id, currency_id, transfer_amount, fromWallet, toWallet, remaining_balance, transfer_amount_recieved, isTransferApproved, isTranferBlocked FROM transferMaster WHERE userid = ? ORDER BY transfer_id DESC')) {
            //         $stmt->bind_param('i', $userid);
            //         $stmt->execute();
            //         $stmt->store_result();
            //         if ($stmt->num_rows > 20) {
            //             $i = 0;
            //             $limit = $stmt->num_rows / 20;
            //             $limit = ceil($limit);
            //             if ($page_no > $limit) {
            //                 $page_no = $limit;
            //             }
            //             $transfer_page_nav .= '<nav aria-label="...">
            //             <ul class="pagination">';

            //             $next_page_no = $page_no + 1;
            //             $prev_page_no = $page_no - 1;
            //             if ($page_no == 1) {
            //                 $transfer_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
            //             } else {
            //                 $transfer_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($prev_page_no) . '" tabindex="-1">Previous</a></li>';
            //             }
            //             while ($i < $limit) {
            //                 if ($page_no == $i + 1) {
            //                     $transfer_page_nav .= '<li class="page-item active"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '  <span class="sr-only">(current)</span></a></li>';
            //                 } else {
            //                     $transfer_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '</a></li>';
            //                 }
            //                 ++$i;
            //             }
            //             if ($page_no == $limit) {
            //                 $transfer_page_nav .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
            //             } else {
            //                 $transfer_page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($next_page_no) . '">Next</a></li>';
            //             }
            //             $transfer_page_nav .= '
            //                 </ul>
            //             </nav>';
            //         }
            //     }
            //     $type_css = '#all, #buy, #trade, #sell {
            //         display: none;
            //     }';
            // } else {
            //     if ($stmt = $con->prepare('SELECT transaction_id, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount, isTransactionApproved, isTransactionBlocked FROM transactionMaster WHERE userid = ?  ORDER BY transaction_id DESC')) {
            //         $stmt->bind_param('i', $userid);
            //         $stmt->execute();
            //         $stmt->store_result();
            //         if ($stmt->num_rows > 20) {
            //             $i = 0;
            //             $limit = $stmt->num_rows / 20;
            //             $limit = ceil($limit);
            //             if ($page_no > $limit) {
            //                 $page_no = $limit;
            //             }
            //             $page_nav .= '<nav aria-label="...">
            //             <ul class="pagination">';

            //             $next_page_no = $page_no + 1;
            //             $prev_page_no = $page_no - 1;
            //             if ($page_no == 1) {
            //                 $page_nav .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
            //             } else {
            //                 $page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($prev_page_no) . '" tabindex="-1">Previous</a></li>';
            //             }
            //             while ($i < $limit) {
            //                 if ($page_no == $i + 1) {
            //                     $page_nav .= '<li class="page-item active"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '  <span class="sr-only">(current)</span></a></li>';
            //                 } else {
            //                     $page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($i + 1) . '">' . ($i + 1) . '</a></li>';
            //                 }
            //                 ++$i;
            //             }
            //             if ($page_no == $limit) {
            //                 $page_nav .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
            //             } else {
            //                 $page_nav .= '<li class="page-item"><a class="page-link" href="transactions.php?page=' . ($next_page_no) . '">Next</a></li>';
            //             }
            //             $page_nav .= '
            //                 </ul>
            //             </nav>';
            //         }
            //     }
            //     $type_css = '#buy, #sell, #trade, #transfer {
            //         display: none;
            //     }';
            // }
            if ($stmt = $con->prepare('SELECT transaction_id, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount, isTransactionApproved, isTransactionBlocked FROM transactionMaster WHERE userid = ?  ORDER BY transaction_id DESC')) {
                $stmt->bind_param('i', $userid);
                $stmt->execute();
                $stmt->store_result();
                $buy_table = '<table class="rwd-table" id="buy">
                <tr>
                    <th>Transaction Id</th>
                    <th>Cryptocurrency Amount Received</th>
                    <th>Purchased Cryptocurrency</th>
                    <th>Remaining Balance</th>
                    <th>Amount Used</th>
                    <th>Transaction Approved</th>
                </tr>';
                $sell_table = '<table class="rwd-table" id="sell">
                <tr>
                    <th>Transaction Id</th>
                    <th>Amount Received(USD)</th>
                    <th>Sold Cryptocurrency</th>
                    <th>Remaining Cryptocurrency</th>
                    <th>Amount Used</th>
                    <th>Transaction Approved</th>
                </tr>';
                $trade_table = '<table class="rwd-table" id="trade">
                <tr>
                    <th>Transaction Id</th>
                    <th>Amount Received</th>
                    <th>From Wallet</th>
                    <th>To Wallet</th>
                    <th>Remaining Balance</th>
                    <th>Amount Used</th>
                    <th>Transaction Approved</th>
                </tr>';
                $table = '<table class="rwd-table" id="all">
                <tr>
                    <th>Transaction Id</th>
                    <th>Transaction Type</th>
                    <th>Currency Purchase Amount</th>
                    <th>From Wallet</th>
                    <th>To Wallet</th>
                    <th>Remaining Balance</th>
                    <th>Transaction Amount</th>
                    <th>Transaction Approved</th>
                </tr>';
                
                //--------------- TRANSFER SECTION --------------------------------
                
                
                $transfer_table = '<table class="rwd-table" id="transfer">
                    <tr>
                        <th>Transfer Id</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Transfer Amount</th>
                        <th>From Wallet</th>
                        <th>To Wallet</th>
                        <th>Remaining Balance</th>
                        <th>Transfer Amount Recieved</th>
                        <th>Transfer Approved</th>
                    </tr>';


                if ($stmt_2 = $con->prepare('SELECT transfer_id, userid, to_userid, currency_id, transfer_amount, fromWallet, toWallet, toWalletAddress, remaining_balance, transfer_amount_recieved, isTransferApproved, isTransferBlocked FROM transferMaster WHERE userid = ? OR to_userid = ? ORDER BY transfer_id DESC')) {
                    $stmt_2->bind_param('ii', $userid, $userid);
                    $stmt_2->execute();
                    $stmt_2->store_result();

                    if ($stmt_2->num_rows > 0) {
                        $stmt_2->bind_result($transfer_id, $from_userid, $to_userid, $tr_currency_id, $transfer_amount, $tr_fromWallet, $tr_toWallet, $to_wallet_address, $tr_remaining_balance, $transfer_amount_recieved, $isTransferApproved, $isTransferBlocked);

                        while ($stmt_2->fetch()) {
                            $stmt_3 = $con->prepare('SELECT first_name, last_name FROM userMaster WHERE userid = ?');
                            $stmt_3->bind_param('i', $from_userid);
                            $stmt_3->execute();
                            $stmt_3->store_result();
                            $stmt_3->bind_result($from_first_name, $from_last_name);
                            $stmt_3->fetch();
                            $stmt_3->close();

                            $stmt_4 = $con->prepare('SELECT first_name, last_name FROM userMaster WHERE userid = ?');
                            $stmt_4->bind_param('i', $to_userid);
                            $stmt_4->execute();
                            $stmt_4->store_result();
                            $stmt_4->bind_result($to_first_name, $to_last_name);
                            $stmt_4->fetch();
                            $stmt_4->close();

                            $from_name = $from_first_name . ' ' . $from_last_name;
                            $to_name = $to_first_name . ' ' . $to_last_name;

                            if ($userid == $to_userid) {
                                $stmt_5 = $con->prepare('SELECT wallet_balance FROM walletMappingMaster WHERE wallet_address = ?');
                                $stmt_5->bind_param('s', $to_wallet_address);
                                $stmt_5->execute();
                                $stmt_5->store_result();
                                $stmt_5->bind_result($tr_remaining_balance);
                                $stmt_5->fetch();
                                $stmt_5->close();
                            }

                            $transfer_type = 'Transfer';
                            $transfer_table .= '<tr>
                            <td data-th="Login Date">' . $transfer_id . '</td>
                            <td data-th="Transaction Type">' . $from_name . '</td>
                            <td data-th="Transaction Type">' . $to_name . '</td>
                            <td data-th="Login IPv6">' . $transfer_amount . '</td>
                            <td data-th="Login User Agent">' . $tr_fromWallet . '</td>
                            <td data-th="Login User Agent">' . $tr_toWallet . '</td>
                            <td data-th="Login User Agent">' . $tr_remaining_balance . '</td>
                            <td data-th="Login User Agent">' . $transfer_amount_recieved . '</td>';
                            if ($isTransferApproved == 0 && $isTransferBlocked == 0) {
                                $transfer_table .= '<td data-th="Login User Agent">Pending</td>';
                            } else if ($isTransferBlocked == 1) {
                                $transfer_table .= '<td data-th="Login User Agent">Blocked</td>';
                            } else {
                                $transfer_table .= '<td data-th="Login User Agent">Approved</td>';
                            }
                            $transfer_table .= '</tr>';
                        }
                    }
                }
                //------------------------------------------------------

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($transaction_id, $currency_id, $currency_purchase_amount, $fromWallet, $toWallet, $remaining_balance, $transaction_amount, $isTransactionApproved, $isTransactionBlocked);


                    while ($stmt->fetch()) {
                        if ($fromWallet == 'USD') {
                            $transaction_type = 'Buy';
                        } else if ($toWallet == 'USD') {
                            $transaction_type = 'Sell';
                        } else {
                            $transaction_type = 'Trade';
                        }
                        $table .= '<tr>
                            <td data-th="Login Date">' . $transaction_id . '</td>
                            <td data-th="Transaction Type">' . $transaction_type . '</td>
                            <td data-th="Login IPv6">' . $currency_purchase_amount . '</td>
                            <td data-th="Login User Agent">' . $fromWallet . '</td>
                            <td data-th="Login User Agent">' . $toWallet . '</td>
                            <td data-th="Login User Agent">' . $remaining_balance . '</td>
                            <td data-th="Login User Agent">' . $transaction_amount . '</td>';
                        if ($isTransactionBlocked == 1) {
                            $table .= '<td data-th="Login User Agent">Blocked</td>';
                        } else if ($isTransactionApproved == 0) {
                            $table .= '<td data-th="Login User Agent">Pending</td>';
                        } else {
                            $table .= '<td data-th="Login User Agent">Approved</td>';
                        }
                        if ($fromWallet == 'USD') {
                            $buy_table .= '<tr>
                            <td data-th="Login Date">' . $transaction_id . '</td>
                            <td>' . $currency_purchase_amount . '</td>
                            <td>' . $toWallet . '</td>
                            <td>' . $remaining_balance . '</td>
                            <td>' . $transaction_amount . '</td>';
                            if ($isTransactionApproved == 0) {
                                $buy_table .= '<td data-th="Login User Agent">Pending</td>';
                            } else if ($isTransactionBlocked == 1) {
                                $buy_table .= '<td data-th="Login User Agent">Blocked</td>';
                            } else {
                                $buy_table .= '<td data-th="Login User Agent">Approved</td>';
                            }
                            $buy_table .= '</tr>';
                        } else if ($toWallet == 'USD') {
                            $sell_table .= '<tr>
                            <td data-th="Login Date">' . $transaction_id . '</td>
                            <td>' . $currency_purchase_amount . '</td>
                            <td>' . $fromWallet . '</td>
                            <td>' . $remaining_balance . '</td>
                            <td>' . $transaction_amount . '</td>';
                            if ($isTransactionApproved == 0) {
                                $sell_table .= '<td data-th="Login User Agent">Pending</td>';
                            } else if ($isTransactionBlocked == 1) {
                                $sell_table .= '<td data-th="Login User Agent">Blocked</td>';
                            } else {
                                $sell_table .= '<td data-th="Login User Agent">Approved</td>';
                            }
                            $sell_table .= '</tr>';
                        } else {
                            $trade_table .= '<tr>
                            <td data-th="Login Date">' . $transaction_id . '</td>
                            <td data-th="Login IPv6">' . $currency_purchase_amount . '</td>
                            <td data-th="Login User Agent">' . $fromWallet . '</td>
                            <td data-th="Login User Agent">' . $toWallet . '</td>
                            <td data-th="Login User Agent">' . $remaining_balance . '</td>
                            <td data-th="Login User Agent">' . $transaction_amount . '</td>';
                            if ($isTransactionApproved == 0) {
                                $trade_table .= '<td data-th="Login User Agent">Pending</td>';
                            } else if ($isTransactionBlocked == 1) {
                                $trade_table .= '<td data-th="Login User Agent">Blocked</td>';
                            } else {
                                $trade_table .= '<td data-th="Login User Agent">Approved</td>';
                            }
                            $trade_table .= '</tr>';
                        }
                        $table .= '</tr>';
                    }
                }



                $table .= '
                </table>';
                $trade_table .= '
                </table>';
                $buy_table .= '
                </table>';
                $sell_table .= '
                </table>';
                $transfer_table .= '
                </table>';

                if ($_COOKIE['fnz_cookie_val'] == 'no') {
                    setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
                } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                    setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
                } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
                    setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
                }
                echo '
<!DOCTYPE html>

    <html lang="en">

    <head>
        <meta charset="utf-8" />

        <!-- Favicons -->
        <link href="./assets/img/wallet.png" rel="icon">
        <link href="./assets/img/wallet.png" rel="apple-touch-icon">

        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>Transactions</title>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
        <!--     Fonts and icons     -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
        <!-- CSS Files -->
        <link href="./assets/vendor/bootstrap_dash/bootstrap.min.css" rel="stylesheet" />
        <link href="./assets/css/light-bootstrap-dashboard.css" rel="stylesheet" />
        <link href="assets/css/login-history.css" rel="stylesheet">
        <link href="./assets/css/search.css" rel="stylesheet" />
        <style>
            ' . $type_css . '
        </style>

    </head>

    <body>
        <div class="wrapper">
            <div class="sidebar" data-image="./assets/img/sidebar-5.jpg">
                <!--
            Tip 1: You can change the color of the sidebar using: data-color="purple | blue | green | orange | red"

            Tip 2: you can also add an image using data-image tag
        -->
                <div class="sidebar-wrapper">
                    <div class="logo">
                        <a href="#" class="simple-text">
                            Crypto
                        </a>
                    </div>
                    <div class="logo">
                        <span style="color: #FFFFFF; opacity: .86; border-radius: 4px; display: block; padding: 10px 15px;">USD Balance: ' . $balance . '</span>
                    </div>
                    <ul class="nav" style="border-bottom: 1px solid rgba(255, 255, 255, 0.2);">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="nc-icon nc-chart-pie-35"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="trade.php">
                                <i class="nc-icon nc-circle-09"></i>
                                <p>Trade</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="holdings.php">
                                <i class="nc-icon nc-circle-09"></i>
                                <p>Holdings</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="transactions.php">
                                <i class="nc-icon nc-notes"></i>
                                <p>Transaction List</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">
                                <i class="nc-icon nc-chart-pie-35"></i>
                                <p>Contact Us</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="main-panel">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg " color-on-scroll="500">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="#"> Transaction History </a>
                        <button href="" class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
                            aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-bar burger-lines"></span>
                            <span class="navbar-toggler-bar burger-lines"></span>
                            <span class="navbar-toggler-bar burger-lines"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navigation">
                            <ul class="nav navbar-nav mr-auto">
                                <li class="dropdown nav-item">
                                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                        <i class="nc-icon nc-planet"></i>
                                        <span class="notification">' . $notifications . '</span>
                                        <span class="d-lg-none">Notification</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        ' . $notification . '
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <div class="form-group has-search">
                                        <span class="fa fa-search form-control-feedback" onclick="search_func()"></span>
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Search..." onkeydown="key_down(event);">
                                    </div>
                                </li>
                            </ul>
                            <ul class="navbar-nav ml-auto">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="no-icon">Account</span>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item" href="profile.php">Profile</a>
                                        <a class="dropdown-item" href="change-password.php">Change Password</a>
                                        <a class="dropdown-item" href="kyc.php">View KYC Status</a>
                                        <div class="divider"></div>
                                        <a class="dropdown-item" href="login-history.php">Login History</a>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="logout.php">
                                        <span class="no-icon">Log out</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                <!-- End Navbar -->
                <div class="content">
                  <div class="container-fluid">
                    <div class="col-lg-3">
                        <select id="tables" class="form-control" onchange="updateTable()">
                            <option selected="" value="0">All</option>
                            <option value="1">Buy</option>
                            <option value="2">Sell</option>
                            <option value="3">Trade</option>
                            <option value="4">Transfer</option>
                        </select>
                    </div>
                    <div class="row d-flex align-items-center justify-content-between animate__animated animate__fadeInUp">
                      <div class="col-lg-12">
                        ' . $table . '
                        ' . $buy_table . '
                        ' . $sell_table . '
                        ' . $trade_table . '
                        ' . $transfer_table . '
                      </div>
                    </div>
                  </div>
                  <!--' . $page_nav . '
                  ' . $buy_page_nav . '
                  ' . $sell_page_nav . '
                  ' . $trade_page_nav . '
                  ' . $transfer_page_nav . '-->
                </div>
                <footer class="footer">
                    <div class="container-fluid">
                        <nav>
                            <ul class="footer-menu">
                                <li>
                                    <a href="#">
                                        Home
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Company
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Portfolio
                                    </a>
                                </li>
                            </ul>
                            <p class="copyright text-center">
                                ©
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>

                            </p>
                        </nav>
                    </div>
                </footer>
            </div>
        </div>
        <!--   -->

    </body>
    <!--   Core JS Files   -->
    <script src="./assets/vendor/core/jquery.3.2.1.min.js" type="text/javascript"></script>
    <script src="./assets/vendor/core/popper.min.js" type="text/javascript"></script>
    <script src="./assets/vendor/bootstrap_dash/bootstrap.min.js" type="text/javascript"></script>
    <!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
    <script src="./assets/vendor/plugins/bootstrap-switch.js"></script>
    <!--  Google Maps Plugin    -->
    <!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script> -->
    <!--  Chartist Plugin  -->
    <script src="./assets/vendor/plugins/chartist.min.js"></script>
    <!--  Notifications Plugin    -->
    <script src="./assets/vendor/plugins/bootstrap-notify.js"></script>
    <!-- Control Center for Light Bootstrap Dashboard: scripts for the example pages etc -->
    <script src="./assets/js/light-bootstrap-dashboard.js" type="text/javascript"></script>
    <script src="./assets/js/search.js"></script>
    <script>
        function updateTable() {
            var table_index = $("#tables :selected").val();
            if (table_index == 0) {
                $("#all").show();
                $("#buy").hide();
                $("#sell").hide();
                $("#trade").hide();
                $("#transfer").hide();
            } else if (table_index == 1) {
                $("#all").hide();
                $("#buy").show();
                $("#sell").hide();
                $("#trade").hide();
                $("#transfer").hide();
            } else if (table_index == 2) {
                $("#all").hide();
                $("#buy").hide();
                $("#sell").show();
                $("#trade").hide();
                $("#transfer").hide();
            } else if (table_index == 4) {
                $("#all").hide();
                $("#buy").hide();
                $("#sell").hide();
                $("#trade").hide();
                $("#transfer").show();
            } else {
                $("#all").hide();
                $("#buy").hide();
                $("#sell").hide();
                $("#trade").show();
                $("#transfer").hide();
            }
        }
    </script>
    <script>
        $(document).ready(function(){
            $("#errorModal").modal("show");
            if (document.getElementsByClassName("dropdown-menu")[0].childElementCount == 0) {
                document.getElementsByClassName("dropdown-menu")[0].innerHTML = "<center style=\'padding:5px; margin:5px; color: ##818181\'>No notifications</center>";
            }
        });
    </script>
</html>
';
            }
        }
    }
}
?>