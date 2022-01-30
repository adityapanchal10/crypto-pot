<?php

session_start();

include "db_connect.php";

if (!isset($_SESSION['email'])) {
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
            // if (isset($_GET['page-no'])) {
            //     $page_no = $_GET['page-no'];
            //     $next_page_no = $page_no + 1;
            //     $prev_page_no = $page_no - 1;
            //     if ($page_no == 1) {
            //         $nav = '<a href="login-history.php?page-no='.$next_page_no.'">Next</a>';
            //     } else {
            //         $nav = '<a href="login-history.php?page-no='.$prev_page_no.'">Previous</a>
            //         <a href="login-history.php?page-no='.$next_page_no.'">Next</a>';
            //     }
            // } else {
            //     $page_no = 1;
            //     $nav = '<a href="login-history.php?page-no=2">Next</a>';
            // }
            if (isset($_GET['page-no'])) {
                $page_no = $_GET['page-no'];
            } else {
                $page_no = 1;
            }
            $page_nav = '';
            if ($stmt = $con->prepare('SELECT loginDatetime, loginIPv4, loginIPv6, login_location, login_http_user_agent FROM logMaster WHERE userid = ?;')) {
                $stmt->bind_param('i', $userid);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 20) {
                    $i = 0;
                    $limit = $stmt->num_rows / 20;
                    $limit = ceil($limit);
                    if ($page_no > $limit) {
                        $page_no = $limit;
                    }
                    $page_nav .= '<nav aria-label="...">
                        <ul class="pagination">';
                    
                    $next_page_no = $page_no + 1;
                    $prev_page_no = $page_no - 1;
                    if ($page_no == 1) {
                        $page_nav .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
                    } else {
                        $page_nav .= '<li class="page-item"><a class="page-link" href="login-history.php?page-no='.($prev_page_no).'" tabindex="-1">Previous</a></li>';
                    }
                    while ($i < $limit) {
                        if ($page_no == $i + 1) {
                            $page_nav .= '<li class="page-item active"><a class="page-link" href="login-history.php?page-no='.($i + 1).'">'.($i + 1).'  <span class="sr-only">(current)</span></a></li>';
                        } else {
                            $page_nav .= '<li class="page-item"><a class="page-link" href="login-history.php?page-no='.($i + 1).'">'.($i + 1).'</a></li>';
                        }
                        ++$i;
                    }
                    if ($page_no == $limit) {
                        $page_nav .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
                    } else {
                        $page_nav .= '<li class="page-item"><a class="page-link" href="login-history.php?page-no='.($next_page_no).'">Next</a></li>';
                    }
                    $page_nav .= '
                        </ul>
                    </nav>';
                }
            }
            $offset = ($page_no - 1) * 20;
            if ($stmt = $con->prepare('SELECT loginDatetime, loginIPv4, loginIPv6, login_location, login_http_user_agent FROM logMaster WHERE userid = ? ORDER BY loginDatetime DESC LIMIT ?, 20;')) {
                $stmt->bind_param('ii', $userid, $offset);
                $stmt->execute();
                $stmt->store_result();
                $table = '<table class="rwd-table">
                <tr>
                    <th>Login Date/Time</th>
                    <th>Login IP Address</th>
                    <th>Login Location</th>
                    <th>Browser</th>
                </tr>';
                if ($stmt->num_rows > 0) {
                    
                    $stmt->bind_result($loginDatetime, $loginIPv4, $loginIPv6, $location, $login_http_user_agent);
                    while ($stmt->fetch()) {
                        if ($loginIPv4 == '0.0.0.0' and $loginIPv6 != '0:0:0:0:0:0:0:0') {
                            $IP = $loginIPv6;
                        } elseif ($loginIPv4 != '0.0.0.0' and $loginIPv6 == '0:0:0:0:0:0:0:0') {
                            $IP = $loginIPv4;
                        }
                        $table .= '<tr>
                            <td data-th="Login Date">'.$loginDatetime.'</td>
                            <td data-th="Login IP">'.$IP.'</td>
                            <td data-th="Login Location">'.$location.'</td>
                            <td data-th="Login Browser">'.$login_http_user_agent.'</td>
                        </tr>';
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
                                <li>
                                    <a class="nav-link" href="trade.php">
                                        <i class="nc-icon nc-circle-09"></i>
                                        <p>Trade</p>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link" href="holdings.php">
                                        <i class="nc-icon nc-circle-09"></i>
                                        <p>Holdings</p>
                                    </a>
                                </li>
                                <li>
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
                                <a class="navbar-brand" href="#"> Login History </a>
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
                                                <a class="dropdown-item active" href="profile.php">Profile</a>
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
                              '.$page_nav.'
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
              </html>';
            }
        }
    }
}
?>