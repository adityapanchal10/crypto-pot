<?php

session_start();

include "db_connect.php";

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
} else {
    if ($stmt = $con->prepare('SELECT userid, isVerified, is_KYC_request_sent FROM userMaster WHERE email_id = ?')) {
      $notification = '';
      $notifications = 0;
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid, $isVerified, $is_KYC_request_sent);
            $stmt->fetch();
            if ($isVerified == 0) {
              $notifications += 1;
              $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
            }
            if ($is_KYC_request_sent == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
            }
            $stmt->fetch();
            if ($stmt = $con->prepare('SELECT loginDatetime, loginIPv4, loginIPv6, login_http_user_agent FROM logMaster WHERE userid = ?')) {
                $stmt->bind_param('i', $userid);
                $stmt->execute();
                $stmt->store_result();
                $table = '<table class="rwd-table">
                <tr>
                    <th>Login Date</th>
                    <th>Login IPv4</th>
                    <th>Login IPv6</th>
                    <th>Login User Agent</th>
                </tr>';
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($loginDatetime, $loginIPv4, $loginIPv6, $login_http_user_agent);
                    while ($stmt->fetch()) {
                        $table .= '<tr>
                            <td data-th="Login Date">'.$loginDatetime.'</td>
                            <td data-th="Login IPv4">'.$loginIPv4.'</td>
                            <td data-th="Login IPv6">'.$loginIPv6.'</td>
                            <td data-th="Login User Agent">'.$login_http_user_agent.'</td>
                        </tr>';
                    }
                }
                $table .= '
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
                                <ul class="nav">
                                    <li class="nav-item">
                                        <a class="nav-link" href="dashboard.php">
                                            <i class="nc-icon nc-chart-pie-35"></i>
                                            <p>Dashboard</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="nav-link" href="profile.php">
                                            <i class="nc-icon nc-circle-09"></i>
                                            <p>User Profile</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="nav-link" href="transactions.php">
                                            <i class="nc-icon nc-notes"></i>
                                            <p>Transaction List</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="nav-link" href="kyc.php">
                                            <i class="nc-icon nc-circle-09"></i>
                                            <p>Verify KYC</p>
                                        </a>
                                    </li>
                                    <li class="nav-item active">
                                        <a class="nav-link" href="login-history.php">
                                            <i class="nc-icon nc-notes"></i>
                                            <p>Login History</p>
                                        </a>
                                    </li>
                                    <!-- 
                                    <li>
                                        <a class="nav-link" href="./maps.html">
                                            <i class="nc-icon nc-pin-3"></i>
                                            <p>Maps</p>
                                        </a>
                                    </li> -->
                                    <li>
                                        <a class="nav-link" href="./notifications.html">
                                            <i class="nc-icon nc-bell-55"></i>
                                            <p>Notifications</p>
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
                                            <li class="nav-item">
                                                <a href="#" class="nav-link" data-toggle="dropdown">
                                                    <i class="nc-icon nc-palette"></i>
                                                    <span class="d-lg-none">Dashboard</span>
                                                </a>
                                            </li>
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
                                                <a href="#" class="nav-link">
                                                    <i class="nc-icon nc-zoom-split"></i>
                                                    <span class="d-lg-block">&nbsp;Search</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <ul class="navbar-nav ml-auto">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#pablo">
                                                    <span class="no-icon">Account</span>
                                                </a>
                                            </li>
                                            <li class="nav-item dropdown">
                                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="no-icon">Dropdown</span>
                                                </a>
                                                <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                                    <a class="dropdown-item" href="#">Action</a>
                                                    <a class="dropdown-item" href="#">Another action</a>
                                                    <a class="dropdown-item" href="#">Something</a>
                                                    <a class="dropdown-item" href="#">Something else here</a>
                                                    <div class="divider"></div>
                                                    <a class="dropdown-item" href="#">Separated link</a>
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
                                  <div class="col-lg-8">
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
</html>';
            }
        }
    }
}
?>