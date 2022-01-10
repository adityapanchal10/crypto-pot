<?php

session_start();

include "db_connect.php";

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
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
    }
    if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '' || !isset($_COOKIE['email']) || !isset($_COOKIE['id'])) {
        $email = $_SESSION['email'];
        $id = $_SESSION['id'];
    } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
        $email = base64_decode($_COOKIE['email']);
        $id = base64_decode($_COOKIE['id']);
    } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
        $email = $_COOKIE['email'];
        $id = $_COOKIE['id'];
    }
    if ($stmt = $con->prepare('SELECT remaining_balance, isKYCverified FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($balance, $kyc_verified);
            $stmt->fetch();

            if ($kyc_verified == 1) {
                if (isset($_POST['from-wallet'], $_POST['to-wallet'], $_POST['amount'])) {
                    $from_wallet = $_POST['from-wallet'];
                    $to_wallet = $_POST['to-wallet'];
					if ($from_wallet == $to_wallet) {
						echo '<script>alert("From and to wallets cannot be the same"); window.location="dashboard.php"</script>';
					}
                    $amount = $_POST['amount'];
                    if ($stmt = $con->prepare('SELECT currency_id, currency_price FROM priceMaster WHERE currency_name = ?')) {
                        $stmt->bind_param('s', $from_wallet);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($from_currency_id, $from_currency_price);
                        $stmt->fetch();
                        $stmt->close();
                        if ($stmt = $con->prepare('SELECT wallet_id, wallet_balance FROM walletMappingMaster WHERE currency_id = ? AND userid = ?')) {
                          $stmt->bind_param('ii', $from_currency_id, $id);
                          $stmt->execute();
                          $stmt->store_result();
                          $stmt->bind_result($from_wallet_id, $from_wallet_balance);
                          $stmt->fetch();
                          $stmt->close();
                        } else {
                            echo $stmt->error;
                            exit();
                        }
                    } else {
                        echo 'Error fetching from-currency price';
                        exit();
                    }

                    if ($stmt = $con->prepare('SELECT currency_id, currency_price FROM priceMaster WHERE currency_name = ?')) {
                        $stmt->bind_param('s', $to_wallet);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($to_currency_id, $to_currency_price);
                        $stmt->fetch();
                        $stmt->close();
                        if ($stmt = $con->prepare('SELECT wallet_id, wallet_balance FROM walletMappingMaster WHERE currency_id = ? AND userid = ?')) {
                            $stmt->bind_param('ii', $to_currency_id, $id);
                            $stmt->execute();
                            $stmt->store_result();
                            $stmt->bind_result($to_wallet_id, $to_wallet_balance);
                            $stmt->fetch();
                            $stmt->close();
                        } else {
                            echo 'Error fetching to-wallet balance';
                            exit();
                        }
                    } else {
                        echo 'Error fetching to-currency price';
                        exit();
                    }
                    
                    if ($amount > $from_wallet_balance) {
                        echo 'Insufficient balance';
                        exit();
                    } else {
                        $from_wallet_balance = $from_wallet_balance - $amount;
                        if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
                            setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
                        }
                        if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '' || !isset($_COOKIE['email'])) {
                            $purchase_amount = $amount * ($from_currency_price / $to_currency_price);
                        } else {
                            $purchase_amount = $_POST['buy-amount'];
                        }
                        $to_wallet_balance = $to_wallet_balance + $purchase_amount;
                        if ($stmt = $con->prepare('INSERT INTO transactionMaster (userid, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount, transaction_time) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())')) {
                            $stmt->bind_param('iiissii', $id, $to_currency_id, $purchase_amount, $from_wallet, $to_wallet, $from_wallet_balance, $amount);
                            if ($stmt->execute()) {
                                echo '<script>alert("Transaction queued"); window.location="transactions.php"</script>';
                            } else {
                                echo '<script>alert("Error in transaction"); window.location="dashboard.php"</script>';
                            }
                            $stmt->close();
                            exit();
                        } else {
                            echo '<script>alert("Please try again."); window.location="transactions.php"</script>';
                            exit();
                        }
                        // if ($stmt = $con->prepare('UPDATE walletMappingMaster SET wallet_balance = ? WHERE wallet_id = ?')) {
                        //     $stmt->bind_param('ii', $from_wallet_balance, $from_wallet_id);
                        //     $stmt->execute();
                        //     $stmt->close();
                        //     if ($stmt = $con->prepare('UPDATE walletMappingMaster SET wallet_balance = ? WHERE wallet_id = ?')) {
                        //         $stmt->bind_param('ii', $to_wallet_balance, $to_wallet_id);
                        //         $stmt->execute();
                        //         $stmt->close();
                        //         if ($stmt = $con->prepare('INSERT INTO transactionMaster (userid, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount) VALUES (?, ?, ?, ?, ?, ?, ?)')) {
                        //             $stmt->bind_param('iiissii', $_SESSION['id'], $to_currency_id, $purchase_amount, $from_wallet, $to_wallet, $from_wallet_balance, $amount);
                        //             $stmt->execute();
                        //             $stmt->close();
                        //             echo '<script>alert("Transaction successfull"); window.location="dashboard.php"</script>';
                        //         }
                        //     } else {
                        //         echo 'Error updating to-wallet balance';
                        //         exit();
                        //     }
                        // } else {
                        //     echo 'Error updating from-wallet balance';
                        //     exit();
                        // }
                    }                

                } else if (iset($_POST['from'], $_POST['wallet-address'], $_POST['amount'])) {
                    $from_wallet = $_POST['from'];
                    $to_wallet = $_POST['wallet-address'];
                    $amount = $_POST['amount'];
                    echo '<script>alert("This type of transaction is not yet supported."); window.location="dashboard.php"</script>';
                } else {
                    echo '<script>alert("Please fill all the fields"); window.location="dashboard.php"</script>';
                }

            } else {
                echo '<script>alert("Please verify your KYC first!"); window.location="kyc.php"</script>';
            }
        }
    }
} else {
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
        $wallet_types = array();
        while($row = $result->fetch_assoc()){ 
            array_push($wallet_ids, $row['wallet_id']);
            array_push($wallet_balances, $row['wallet_balance']);
            if ($stmt_2 = $con->prepare('SELECT wallet_type, wallet_name FROM walletMaster WHERE wallet_id = ?')) {
                $stmt_2->bind_param('i', $row['wallet_id']);
                $stmt_2->execute();
                $stmt_2->bind_result($wallet_type, $wallet_name);
                $stmt_2->fetch();
                $stmt_2->close();
                array_push($wallet_names, $wallet_name);
                array_push($wallet_types, $wallet_type);
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
        $series = '{';
        $labels = '{';
        $legends = '';
        $prices = '{';
        $from_transfer_options = '';
        //$from_transfer_options .= '<option value="">USD</option>';
        $to_transfer_options = '';
        //$to_transfer_options .= '<option value="">USD</option>';
        for ($i=0; $i < count($wallet_ids); $i++) {
            if ($wallet_balances[$i] !=0) {
                $series .= '{
                    value: '.$wallet_per[$i].',
                    className: "pie'.($i+1).'",
                },';
                $labels .= '"'.$wallet_names[$i].'",';
                $legends .= '<i class="fa fa-circle pie'.($i+1).'"></i>'.$wallet_names[$i].'      ';
                $from_transfer_options .= '<option value='.$wallet_names[$i].'>'.$wallet_types[$i].' ('.$wallet_names[$i].')</option>';
            }
            $to_transfer_options .= '<option value='.$wallet_names[$i].'>'.$wallet_types[$i].' ('.$wallet_names[$i].')</option>';
            $prices .= '"'.$wallet_names[$i].'": "'.$wallet_prices[$i].'",';
        }
        $series .= ']';
        $labels .= ']';
        $prices .= '}';
    } else {
        echo '<script>alert("Error!! Please try again."); window.location = "dashboard.php";</script>';
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
    <style>
        .transactionSection {
            height: 450px;
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
                    <li class="nav-item active">
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
                    <div class="row">
                        <div class="col-md-6" class="transactionSection">
                            <div class="card card-tasks">
                                <div class="card-header h">
                                    <h4 class="card-title show" id="transactionH">Transaction</h4>
                                </div>
                                <div class="card-header">
                                    <p class="card-category">Easy transactions on the go.</p>
                                </div>
                                <div class="trade t">
                                    <form class="dasht show" action="trade.php" method="POST" id="transaction">
                                        <div class="input-wrapper">
                                            <select id="inputFrom" style="height: 60px;" class="form-control" name="from-wallet" onchange="updateAmount();">
                                                '.$from_transfer_options.'
                                            </select>
                                            <label for="inputFrom">From</label>
                                        </div>
                                        <div class="input-wrapper">
                                            <select id="inputTo" style="height: 60px;" class="form-control" name="to-wallet" onchange="updateAmount();">
                                                '.$to_transfer_options.'
                                            </select>
                                            <label for="inputTo">To</label>
                                        </div>
                                        <div class="input-wrapper">
                                            <input type="number" id="amount" min="0" class="transferTo" required name="amount" onchange="updateAmount();" value="0">
                                            <label for="transferTo">Enter amount</label>
                                        </div>
                                        <input type="hidden" id="buy-amount" min="0" class="transferTo" required name="buy-amount" value="0">
                                        <p class="card-category">You\'ll receive approximately <span id="rec-amount" style="text-decoration-line: underline; text-decoration-style: dotted;">0.0</span> units.<p>
                                        <button class="submit-btn" type="submit" name="submit">Proceed</button>

                                    </form>
                                </div>
                                <div class="card-footer ">
                                    <hr>
                                    <div class="stats">
                                        <i class="now-ui-icons loader_refresh spin"></i> Secure
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" class="transactionSection">
                            <div class="card card-tasks">
                                <div class="card-header h">
                                    <h4 class="card-title show" id="transactionH">Transfer To A Friend</h4>
                                </div>
                                <div class="card-header">
                                    <p class="card-category">Transfer to any wallet using its wallet address.</p>
                                </div>
                                <div class="trade t">
                                    <form class="dasht" action="trade.php" method="POST" id="transferF">
                                        <div class="input-wrapper">
                                            <select id="inputFrom" style="height: 60px;" class="form-control" name="">
                                                <option selected>Choose...</option>
                                                '.$from_transfer_options.'
                                            </select>
                                            <label for="inputFrom">From</label>
                                        </div>
                                        <div class="input-wrapper">
                                            <input type="text" id="transferTo" class="transferTo" required>
                                            <label for="transferTo">Enter friend\'s wallet address</label>
                                        </div>
                                        <div class="input-wrapper">
                                            <input type="number" id="amount" min="0" class="transferTo" required name="amount" onchange="updateAmount();" value="0">
                                            <label for="transferTo">Enter amount</label>
                                        </div>
                                        <button class="submit-btn" type="submit" name="submit">Proceed</button>

                                    </form>
                                </div>
                                <div class="card-footer ">
                                    <hr>
                                    <div class="stats">
                                        <i class="now-ui-icons loader_refresh spin"></i> Secure
                                    </div>
                                </div>
                            </div>
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
var prices = '.$prices.';
function updateAmount() {
    var from = document.getElementById("inputFrom").value;
    var to = document.getElementById("inputTo").value;
    var amount = document.getElementById("amount").value;
    var rec_amount = document.getElementById("rec-amount");
    var conversion_rate = prices[from] / prices[to];
    rec_amount.innerHTML = amount * conversion_rate;
    var buy_amount = document.getElementById("buy-amount");
    buy_amount.value = rec_amount.innerHTML;
}
</script>
</html>
    ';
}
?>