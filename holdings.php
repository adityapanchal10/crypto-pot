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
            if ($stmt = $con->prepare('SELECT currency_id, wallet_balance, wallet_address FROM walletMappingMaster WHERE userid = ?')) {
                $stmt->bind_param('i', $userid);
                $stmt->execute();
                $stmt->store_result();
                $table = '<table class="rwd-table">
                <tr>
                    <th>Symbol</th>
                    <th>Currency</th>
                    <th>Balance</th>
                    <th>Est. Value in USD</th>
                    <th>24h Change</th>
                    <th>Wallet Address</th>
                </tr>';
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($currency_id, $wallet_balance, $wallet_address);
                    while ($stmt->fetch()) {
                        //add currency symbol
                        if ($stmt_2 = $con->prepare('SELECT currency_name, currency_price, change_24hr FROM priceMaster WHERE currency_id = ?')) {
                            $stmt_2->bind_param('i', $currency_id);
                            $stmt_2->execute();
                            $stmt_2->store_result();
                            $stmt_2->bind_result($currency_name, $currency_price, $change_24hr);
                            $stmt_2->fetch();
                            $wallet_balance_usd = $wallet_balance * $currency_price;
                            $table .= '<tr>
                                <td data-th="Login Date">'.$currency_name.'</td>
                                <td data-th="Login IPv4">'.$currency_name.'</td>
                                <td data-th="Login IPv6">'.$wallet_balance.'</td>
                                <td data-th="Login User Agent">'.$wallet_balance_usd.'</td>
                                <td data-th="Login User Agent">'.$change_24hr.'</td>
                                <td data-th="Login User Agent">'.$wallet_address.'</td>
                            </tr>';
                        }
                        
                    }
                }
                $table .= '
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
        <title>Holdings</title>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
        <!--     Fonts and icons     -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
        <!-- CSS Files -->
        <link href="./assets/vendor/bootstrap_dash/bootstrap.min.css" rel="stylesheet" />
        <link href="./assets/css/light-bootstrap-dashboard.css" rel="stylesheet" />
        <link href="assets/css/login-history.css" rel="stylesheet">
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
                        <li class="nav-item active">
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
                        <a class="navbar-brand" href="#"> Holdings </a>
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
                    <div class="row d-flex align-items-center justify-content-between animate__animated animate__fadeInUp">
                      <div class="col-lg-12">
                        '.$table.'
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