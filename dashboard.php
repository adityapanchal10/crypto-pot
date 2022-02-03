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
            $_SESSION['error'] = "Error!! Please try again";
            header('Location: dashboard.php');
            exit();
            // echo '<script>alert("Error!! Please try again."); window.location = "login.php";</script>';
        }
    } else {
        $userid = $_SESSION['id'];
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
        $_SESSION['error'] = "Error!! Please try again";
        header('Location: dashboard.php');
        exit();
        // echo '<script>alert("Error!! Please try again."); window.location = "dashboard.php";</script>';
    }
    if ($stmt = $con->prepare('SELECT remaining_balance, isVerified, is_KYC_request_sent, isKYCverified FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        $stmt->bind_result($balance, $isVerified, $is_KYC_request_sent, $isKYCverified);
        $stmt->fetch();
        $stmt->close();
        $graph = '
            <div id="chartPreferences" class="ct-chart ct-perfect-fourth"></div>
            <div class="legend">
                <!--<i class="fa fa-circle pie1"></i> USD-->
                '.$legends.'
            </div>
            <hr>
            <div class="stats">
                <i class="fa fa-clock-o"></i> Campaign sent 2 days ago
            </div>';
        if ($isVerified == 0) {
            $notifications += 1;
            $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
            $graph = '<img src="assets/img/graph_greyed.png" alt="profile-statistics" style="width: 100%;">';
        }
        if ($is_KYC_request_sent == 0) {
            $notifications += 1;
            $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
            $graph = '<img src="assets/img/graph_greyed.png" alt="profile-statistics" style="width: 100%;">';
            $kyc_status = 'Pending';
            $kyc_tooltip = 'KYC documents are not uploaded yet, please upload your KYC documents to recieve $100000 and start trading';
        } else {
            $kyc_status = 'Requested';
            $kyc_tooltip = 'You have sent your verification request. Your KYC will be verified soon.';
            if ($isKYCverified == 1) {
                $kyc_status = 'Verified';
                $kyc_tooltip = 'Your KYC is already verified.';
            }
        }
    } else {
        $_SESSION['error'] = "Error!! Please try again";
        header('Location: dashboard.php');
        exit();
        // echo '<script>alert("Error!! Please try again."); window.location = "login.php";</script>';
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
                        <div class="title" style="width:100px;">'.$fromWallet.'</div>
                        <div class="symbol" style="width:100px;">'.$toWallet.'</div>
                        <div class="amount" style="width:150px;">'.$transaction_amount.'</div>
                        <div class="change" style="width:150px;">'.$currency_purchase_amount.'</div>
                    </div>
                </li>';
            }
        }
    } else {
        $_SESSION['error'] = 'Error!! Please try again.';
        header('Location: dashboard.php');
        exit;
        // echo '<script>alert("Error!! Please try again."); window.location = "dashboard.php";</script>';
    }
    if (isset($_SESSION['error'])) {
        $error = '
        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="false">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Error!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                '.$_SESSION['error'].'
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
            </div>
        </div>';
        // $error = '<p id="password-message" style="font-size:75% ; color: #f00;">'.$_SESSION['error'].'</p>';
        unset($_SESSION['error']);
      } else {
        $error = '';
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
    '.$error.'
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
                            <li class="nav-item">
                                <a class="nav-link" href="kyc.php" data-toggle="tooltip" data-placement="bottom" title="'.$kyc_tooltip.'">
                                    <span class="no-icon">KYC Status: '.$kyc_status.'</span>
                                </a>
                            </li>
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
                        <div class="col-md-4">
                            <div class="card ">
                                <div class="card-header ">
                                    <h4 class="card-title">Portfolio Statistics</h4>
                                    <p class="card-category">Your portfolio diversity</p>
                                </div>
                                <div class="card-body" style="min-height: 412px;">
                                    '.$graph.'
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 graphy">
                            <div class="card graphy">
                                <div class="card-header ">
                                    <div class="row" id="graphs-header">
                                        <h4 class="card-title">Currency Behavior</h4>

                                        <div class="graph-input-wrapper">
                                            <select id="graphs" class="form-control" onchange="updateGraph()">
                                                <option selected value="0">Bitcoin</option>
                                                <option value="1">Ethereum</option>
                                                <option value="2">Doge</option>
                                                <option value="3">Matic</option>
                                                <option value="4">Shiba Inu</option>
                                                <option value="5">USD Tether</option>
                                                <option value="6">Solana</option>
                                                <option value="7">Cardano</option>
                                                <option value="8">Monero</option>
                                                <option value="9">Uniswap</option>
                                            </select>
                                        </div>
                                        <!-- <p class="card-category" id="weekly_perf">weekly</p>
                                        <p class="card-category" id="daily_perf">daily</p> -->
                                        <div class="graph-input-wrapper">
                                            <select id="time-dur" class="form-control" onchange="updateGraph()">
                                                <option selected value="6">weekly</option>
                                                <option value="1">daily</option>
                                                <option value="30">monthly</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <svg>
                                    <defs>
                                        <linearGradient id="gradient" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="rgba(128, 105, 242, 1)" stop-opacity="0.3" />
                                            <stop offset="100%" stop-color="rgba(128, 105, 242, 1)" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="card-body">
                                    <div id="chartHours" class="ct-chart"></div>
                                </div>
                                <div class="card-footer ">
                                    <hr>
                                    <div class="stats">
                                        <i class="fa fa-history"></i> Updated 3 minutes ago
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card" style="height: 420px;">
                                <div class="card-header ">
                                    <h4 class="card-title">Trending Markets</h4>
                                    <p class="card-category">All products including Taxes</p>
                                </div>
                                <div class="scrollable-content">
                                    <div class="row0">
                                        <div class="logo0"></div>
                                        <div class="title0" align="center">Currency</div>
                                        <div class="symbol0" align="center">ID</div>
                                        <div class="amount0" align="center">Current Price</div>
                                        <div class="change0" align="center">24h change</div>
                                    </div>
                                    <ul id="dataList" style="list-style-type:none;"></ul>
                                    <div class="templates">
                                        <div id="listItem">
                                            <div class="row1">
                                                <img class="img-fluid logo" src="./assets/img/transparent.png" width="0"
                                                    height="0">
                                                <div class="title"></div>
                                                <div class="symbol"></div>
                                                <div class="amount"></div>
                                                <div class="change"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card" style="height: 420px;">
                                <div class="card-header ">
                                    <h4 class="card-title">Recent Transactions</h4>
                                    <p class="card-category">Recent 5 transactions from your account.</p>
                                </div>
                                <div class="scrollable-content">
                                    <div class="row0">
                                        <div class="logo0"></div>
                                        <div class="title0" align="center" style="width:100px;">From</div>
                                        <div class="symbol0" align="center" style="width:100px;">To</div>
                                        <div class="amount0" align="center" style="width:150px;">From Volume</div>
                                        <div class="change0" align="center" style="width:150px;">To Volume</div>
                                    </div>
                                    <ul style="list-style-type:none;">
                                        '.$transactions.'
                                    </ul>
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
<script src="./assets/vendor/plugins/bootstrap-switch.js"></script>
<!--  Chartist Plugin  -->
<script src="./assets/vendor/plugins/chartist.min.js"></script>
<!--  Notifications Plugin    -->
<script src="./assets/vendor/plugins/bootstrap-notify.js"></script>
<!-- Control Center for Light Bootstrap Dashboard: scripts for the example pages etc -->
<script src="./assets/js/light-bootstrap-dashboard.js" type="text/javascript"></script>
<!-- Charts -->
<script>
    var chart1 = new Chartist.Pie(
        "#chartPreferences",
        {
            series: '.$series.',
            labels: '.$labels.',
        },
        {
            donut: true,
            showLabel: false,
        }
    );

    chart1.on("draw", function (data) {
        if (data.type === "slice") {
            // Get the total path length in order to use for dash array animation
            var pathLength = data.element._node.getTotalLength();

            // Set a dasharray that matches the path length as prerequisite to animate dashoffset
            data.element.attr({
                "stroke-dasharray": pathLength + "px " + pathLength + "px",
            });

            // Create animation definition while also assigning an ID to the animation for later sync usage
            var animationDefinition = {
                "stroke-dashoffset": {
                    id: "anim" + data.index,
                    dur: 1000,
                    from: -pathLength + "px",
                    to: "0px",
                    easing: Chartist.Svg.Easing.easeOutQuint,
                    // We need to use `fill: \'freeze\'` otherwise our animation will fall back to initial (not visible)
                    fill: "freeze",
                },
            };

            // If this was not the first slice, we need to time the animation so that it uses the end sync event of the previous animation
            if (data.index !== 0) {
                animationDefinition["stroke-dashoffset"].begin =
                    "anim" + (data.index - 1) + ".end";
            }

            // We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
            data.element.attr({
                "stroke-dashoffset": -pathLength + "px",
            });

            // We can\'t use guided mode as the animations need to rely on setting begin manually
            // See http://gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
            data.element.animate(animationDefinition, false);
        }
    });
    function updateGraph() {
        coin_list = ["bitcoin", "ethereum", "dogecoin", "matic-network", "shiba-inu", "tether", "solana", "cardano", "ripple", "uniswap"]
        time_duration = $("#time-dur :selected").val();
        if (time_duration == 1)
            time_interval = "hourly"
        else
            time_interval = "daily"

        var coin_index = $("#graphs :selected").val();

        var weekly = {
            url:
                "https://api.coingecko.com/api/v3/coins/" + coin_list[coin_index] + "/market_chart?vs_currency=usd&days=" + time_duration + "&interval=" + time_interval,
            method: "GET",
            timeout: 0,
        };

        var price = [];
        var time = [];
        // portfolio_data[0] = "BTC";
        // x[0] = "x";

        $.ajax(weekly).done(function (response) {
            var dataObject = response.prices;
            // console.log(dataObject);
            var i = 0;
            dataObject.forEach((item) => {
                price[i] = parseFloat(item[1]);
                var d = new Date(item[0]);
                // x[i] = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
                if (time_duration == 1)
                    time[i] = d.getHours();
                else if (time_duration == 6)
                    time[i] = d.toDateString();
                else if (time_duration == 30)
                    time[i] = d.getDate();

                // console.log("-----------------//----------");
                // console.log(time[i]);
                i++;
            });
            // console.log(x);
            // console.log("-----------------//----------");
            // console.log(portfolio_data);

            chart2 = new Chartist.Line(
                "#chartHours",
                {
                    labels: time,
                    series: [price],
                },
                {
                    height: "300px",
                    low: Math.min(...price) * 0.9,
                    showArea: true,
                    fullwidth: true,
                    /* axisY: {
                        onlyInteger: true,
                        scaleMinSpace: 50,
                        offset: 20,
                        labelInterpolationFnc: function (value) {
                            return (value / 1000).toFixed(2) + "k";
                        },
                    }, */
                    axisX: {
                        showGrid: false,
                        showLabel: true,
                        labelInterpolationFnc: function (value, index) {
                            //if (index === 0) 
                            //    return null;
                            if (time_duration == 1)
                                return index % 3 == 0 ? value + ":00" : null;
                            else if (time_duration == 6)
                                return value;
                            else if (time_duration == 30)
                                return index % 3 == 0 ? value + "th" : null;
                        },
                    },
                    axisY: {
                        showGrid: false,
                        showLabel: true,
                        offset: 60,
                        // The label interpolation function enables you to modify the values
                        // used for the labels on each axis. Here we are converting the
                        // values into million pound.
                        labelInterpolationFnc: function (value, index) {
                            return index == 0 ? null : "$" + value;
                        },
                    },
                }
            );
            chart2.on("draw", function (data) {
                if (data.type === "label" && data.axis === "X") {
                    var textHtml = ["<p>", data.text, "</p>"].join("");
                    var multilineText = Chartist.Svg("svg").foreignObject(textHtml, data.x, data.y - 80, data.space, 80, "ct-label");

                    data.element.replace(multilineText);
                }
            });
        });
    }
    updateGraph();
    
    $(document).ready(function(){
        $("#errorModal").modal("show");
        if (document.getElementsByClassName("dropdown-menu")[0].childElementCount == 0) {
            document.getElementsByClassName("dropdown-menu")[0].innerHTML = "<center style=\'padding:5px; margin:5px; color: ##818181\'>No notifications</center>";
        }
    });
    $(function () {
        $(\'[data-toggle="tooltip"]\').tooltip()
      })
</script>
<script src="./assets/js/search.js"></script>

</html>
    ';
}
?>
