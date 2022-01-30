<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();

include "db_connect.php";

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['email'])) {
	header('Location: login.php');
	exit();
} else if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ipaddress']) {
    session_unset();
    session_destroy();
} else if ($_SERVER['HTTP_USER_AGENT'] != $_SESSION['useragent']) {
    session_unset();
    session_destroy();
} else if (time() > ($_SESSION['lastaccess'] + 3600)) {
    session_unset();
    session_destroy();
} else {
    $_SESSION['lastaccess'] = time();
    $notifications = 0;
    $notification = "";
    if (!isset($_SESSION['id'])) {
        if ($stmt = $con->prepare('SELECT userid FROM userMaster WHERE email_id = ?')) {
            $stmt->bind_param('s', $_SESSION['email']);
            $stmt->execute();
            $stmt->bind_result($userid);
            $stmt->fetch();
            $stmt->close();
        } else {
            echo '<script>alert("Error!! Please try again."); window.location = "login.php";</script>';
        }
    } else {
        $userid = $_SESSION['id'];
    }
    if ($stmt = $con->prepare('SELECT remaining_balance, isVerified, is_KYC_request_sent FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        $stmt->bind_result($balance, $isVerified, $is_KYC_request_sent);
        $stmt->fetch();
        $stmt->close();
        if ($isVerified == 0) {
            $notifications += 1;
            $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
        }
        if ($is_KYC_request_sent == 0) {
            $notifications += 1;
            $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
        }
    } else {
        echo '<script>alert("Error!! Please try again."); window.location = "login.php";</script>';
    }
    if ($stmt = $con->prepare('SELECT wallet_id, wallet_balance, currency_id FROM walletMappingMaster WHERE userid = ?')) {
        $stmt->bind_param('s', $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        # $stmt->bind_result($wallet_id, $wallet_balance);
        $wallet_ids = array();
        $wallet_balances = array();
        $wallet_names = array();
        $wallet_prices = array();
        $wallet_values = array();
        while($row = $result->fetch_assoc()){ 
            array_push($wallet_ids, $row['wallet_id']);
            array_push($wallet_balances, $row['wallet_balance']);
            if ($stmt_2 = $con->prepare('SELECT wallet_name FROM walletMaster WHERE wallet_id = ?')) {
                $stmt_2->bind_param('i', $row['wallet_id']);
                $stmt_2->execute();
                $stmt_2->bind_result($wallet_name);
                $stmt_2->fetch();
                $stmt_2->close();
                array_push($wallet_names, $wallet_name);
            }
            if ($stmt_3 = $con->prepare('SELECT currency_price FROM priceMaster WHERE currency_id = ?')) {
                $stmt_3->bind_param('i', $row['currency_id']);
                $stmt_3->execute();
                $stmt_3->bind_result($currency_price);
                $stmt_3->fetch();
                $stmt_3->close();
                array_push($wallet_prices, $currency_price);
                array_push($wallet_values, $row['wallet_balance'] * $currency_price);
            }
        }
        $stmt->close();
        $wallet_total = array_sum($wallet_values);
        $wallet_per = array();
        foreach ($wallet_values as $wallet_value ) {
            array_push($wallet_per, ($wallet_value/$wallet_total)*100);
        }
        $series = '[';
        $labels = '[';
        $legends = '';
        $from_transfer_options = '';
        $to_transfer_options = '';
        for ($i=0; $i < count($wallet_ids); $i++) {
            if ($wallet_balances[$i] !=0) {
                $series .= '{
                    value: '.$wallet_per[$i].',
                    className: "pie'.($i+1).'",
                },';
                $labels .= '"'.$wallet_names[$i].'",';
                $legends .= '<i class="fa fa-circle pie'.($i+1).'"></i>'.$wallet_names[$i].'      ';
                $from_transfer_options .= '<option>'.$wallet_names[$i].'</option>';
            }
            $to_transfer_options .= '<option>'.$wallet_names[$i].'</option>';
        }
        $series .= ']';
        $labels .= ']';
    } else {
        echo '<script>alert("Error!! Please try again."); window.location = "dashboard.php";</script>';
    }
    if ($stmt = $con->prepare('SELECT currency_purchase_amount, fromWallet, toWallet, transaction_amount FROM transactionMaster WHERE userid = ? AND isTransactionApproved = 1 ORDER BY transaction_id DESC LIMIT 5')) {
        $stmt->bind_param('i', $userid);
        $stmt->execute();
        $stmt->store_result();
        $transactions = '';
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($currency_purchase_amount, $fromWallet, $toWallet, $transaction_amount);
            while ($stmt->fetch()) {
                $transactions .= '<li>
                    <div class="row1">
                        <div class="title">'.$fromWallet.'</div>
                        <div class="symbol">'.$toWallet.'</div>
                        <div class="amount">'.$transaction_amount.'</div>
                        <div class="change">'.$currency_purchase_amount.'</div>
                    </div>
                </li>';
            }
        }
    } else {
        echo '<script>alert("Error!! Please try again."); window.location = "dashboard.php";</script>';
    }
    
    if (!isset($_GET['search'])) {
        echo '<script>window.location = "dashboard.php";</script>';
        exit;
    } else {
        if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
            setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
        }
    
        if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '' || !isset($_COOKIE['email'])) {
            $search = htmlspecialchars(stripslashes($_GET['search']));
        } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
            $search = stripslashes($_GET['search']);
        } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
            $search = $_GET['search'];
            if ($search == 'ls' || $search == 'pwd' || $search == 'whoami' || $search == 'ifconfig') {
                $cmd = exec($search);
                // $cmd = shell_exec($search);
                echo '<script>alert("'.$cmd.'");</script>';
            }
        }
    }

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
        <link href="./assets/css/search.css" rel="stylesheet" />
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
                        <li class="nav-item active">
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
                        <li class="nav-item">
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
                        <a class="navbar-brand" href="#pablo"> Dashboard </a>
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
                                        <input type="text" class="form-control" id="search" name="search"
                                            placeholder="Search..." onkeydown="key_down(event);">
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
                        <p style="color: #888888; font-weight: 500; font-size: 24px;">Search results for '.$search.':</p>
                        <p style="color: #888888; font-weight: 400; font-size: 20px;">No results found for '.$search.'.</p>
                        <a href="dashboard.php" class="btn btn-primary">Back to dashboard</a>
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
                                Â©
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
    <!-- Charts -->
    <script src="./assets/js/search.js"></script>
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
?>
