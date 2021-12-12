<?php

session_start();

include "db_connect.php";

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
} else {
    if ($stmt = $con->prepare('SELECT userid, remaining_balance, isVerified, is_KYC_request_sent FROM userMaster WHERE email_id = ?')) {
      $notification = '';
      $notifications = 0;

        $stmt->bind_param('s', $_SESSION['email']);
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
                            <td data-th="Login Date">'.$transaction_id.'</td>
                            <td data-th="Transaction Type">'.$transaction_type.'</td>
                            <td data-th="Login IPv6">'.$currency_purchase_amount.'</td>
                            <td data-th="Login User Agent">'.$fromWallet.'</td>
                            <td data-th="Login User Agent">'.$toWallet.'</td>
                            <td data-th="Login User Agent">'.$remaining_balance.'</td>
                            <td data-th="Login User Agent">'.$transaction_amount.'</td>';
                            if ($isTransactionApproved == 0) {
                                $table .= '<td data-th="Login User Agent">Pending</td>';
                            } else if ($isTransactionBlocked == 1) {
                                $table .= '<td data-th="Login User Agent">Blocked</td>';
                            } else {
                                $table .= '<td data-th="Login User Agent">Approved</td>';
                            }
                        if ($fromWallet == 'USD') {
                            $buy_table .= '<tr>
                            <td data-th="Login Date">'.$transaction_id.'</td>
                            <td>'.$currency_purchase_amount.'</td>
                            <td>'.$toWallet.'</td>
                            <td>'.$remaining_balance.'</td>
                            <td>'.$transaction_amount.'</td>';
                            if ($isTransactionApproved == 0) {
                                $buy_table .= '<td data-th="Login User Agent">Pending</td>';
                            } else if ($isTransactionBlocked == 1) {
                                $buy_table .= '<td data-th="Login User Agent">Blocked</td>';
                            } else {
                                $buy_table .= '<td data-th="Login User Agent">Approved</td>';
                            }
                            $buy_table .= '</tr>';
                        }else if ($toWallet == 'USD') {
                            $sell_table .= '<tr>
                            <td data-th="Login Date">'.$transaction_id.'</td>
                            <td>'.$currency_purchase_amount.'</td>
                            <td>'.$fromWallet.'</td>
                            <td>'.$remaining_balance.'</td>
                            <td>'.$transaction_amount.'</td>';
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
                            <td data-th="Login Date">'.$transaction_id.'</td>
                            <td data-th="Login IPv6">'.$currency_purchase_amount.'</td>
                            <td data-th="Login User Agent">'.$fromWallet.'</td>
                            <td data-th="Login User Agent">'.$toWallet.'</td>
                            <td data-th="Login User Agent">'.$remaining_balance.'</td>
                            <td data-th="Login User Agent">'.$transaction_amount.'</td>';
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
                echo '
<!DOCTYPE html>

    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
        <link rel="icon" type="image/png" href="./assets/img/favicon.ico">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>Crypto Dash</title>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no"
            name="viewport" />
        <!--     Fonts and icons     -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
        <!-- CSS Files -->
        <link href="./assets/vendor/bootstrap_dash/bootstrap.min.css" rel="stylesheet" />
        <link href="./assets/css/light-bootstrap-dashboard.css" rel="stylesheet" />
        <link href="assets/css/login-history.css" rel="stylesheet">
        <link href="./assets/css/search.css" rel="stylesheet" />
        <style>
            #trade, #sell, #buy {
                display: none;
            }
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
                        <span style="color: #FFFFFF; opacity: .86; border-radius: 4px; display: block; padding: 10px 15px;">USD Balance: '.$balance.'</span>
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
                                        <span class="notification">'.$notifications.'</span>
                                        <span class="d-lg-none">Notification</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        '.$notification.'
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
                        </select>
                    </div>
                    <div class="row d-flex align-items-center justify-content-between animate__animated animate__fadeInUp">
                      <div class="col-lg-12">
                        '.$table.'
                        '.$buy_table.'
                        '.$sell_table.'
                        '.$trade_table.'
                      </div>
                    </div>
                  </div>
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
            } else if (table_index == 1) {
                $("#all").hide();
                $("#buy").show();
                $("#sell").hide();
                $("#trade").hide();
            } else if (table_index == 2) {
                $("#all").hide();
                $("#buy").hide();
                $("#sell").show();
                $("#trade").hide();
            } else {
                $("#all").hide();
                $("#buy").hide();
                $("#sell").hide();
                $("#trade").show();
            }
        }
    </script>
</html>
';
            }
        }
    }
}
?>