<?php

session_start(); // ip + machine fingerprinting IP + Machine Fingerprint screensize, canvas etc. screensize, canvas etc.

function generate_token() {
    if( isset( $_SESSION[ 'csrf_token' ] ) ) {
		destroySessionToken();
	}
	$_SESSION[ 'csrf_token' ] = md5( uniqid() );
}

function check_token() {
    if( !isset( $_SESSION[ 'csrf_token' ] ) || !isset( $_POST[ 'csrf_token' ] ) || $_SESSION[ 'csrf_token' ] !== $_POST[ 'csrf_token' ] ) {
        return false;
    }
    return true;
}

function destroySessionToken() {
    unset( $_SESSION[ 'csrf_token' ] );
}

include "db_connect.php";

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($stmt = $con->prepare('SELECT userid, first_name, last_name, email_id, country, mobile, timezone, remaining_balance, isVerified, is_KYC_request_sent FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();
        $notifications = 0;
        $notification = "";
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid, $first_name, $last_name, $email_id, $country, $mobile, $timezone, $balance, $isVerified, $is_KYC_request_sent);
            $stmt->fetch();
            if ($isVerified == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
            }
            if ($is_KYC_request_sent == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
            }
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
                setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
                $change_security_level = '<a class="dropdown-item security" href="security.php?level=high" style="color: red; margin-top: 10px;">Decrease Security Level</a>';
                $security_level = 'Maximum';
            } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
                $change_security_level = '<a class="dropdown-item security" href="security.php?level=max" style="color: green; margin-top: 10px;">Increase Security Level</a> <a class="dropdown-item security" href="security.php?level=low" style="color: red; margin-top: 0;">Decrease Security Level</a>';
                $security_level = 'Medium';
            } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
                setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
                $change_security_level = '<a class="dropdown-item security" href="security.php?level=high" style="color: green; margin-top: 10px;">Increase Security Level</a>';
                $security_level = 'Low';
            }
            echo '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="utf-8" />
                <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
                <link rel="icon" type="image/png" href="./assets/img/favicon.ico">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
                <title>User Profile</title>
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
                            <a class="navbar-brand" href="#"> Profile </a>
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
                                            <a class="dropdown-item active d-item" href="profile.php">Profile</a>
                                            <a class="dropdown-item d-item" href="change-password.php">Change Password</a>
                                            <a class="dropdown-item d-item" href="kyc.php">View KYC Status</a>
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
                                <div class="row d-flex justify-content-center">
                                    <div class="col-md-10">
                                        <div class="card">
                                            <div class="card-header" style="padding-bottom: 15px;">
                                                <h4 class="card-title">Edit Profile</h4>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST">
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group" style="padding-bottom: 10px;">
                                                                <label style="top: 5px;">First Name</label>
                                                                <input type="text" class="form-control" placeholder="First Name" value="'.$first_name.'" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pl-1">
                                                            <div class="form-group" style="padding-bottom: 10px;">
                                                                <label style="top: 5px;">Last Name</label>
                                                                <input type="text" class="form-control" placeholder="Last Name" value="'.$last_name.'" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group" style="padding-bottom: 10px;">
                                                                <label for="exampleInputEmail1" style="top: 5px;">Email address</label>
                                                                <input type="email" class="form-control" placeholder="Email" value="'.$email_id.'" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pl-1">
                                                            <div class="form-group" style="padding-bottom: 10px;">
                                                                <label style="top: 5px;">Mobile</label>
                                                                <input type="number" class="form-control" placeholder="Mobile" value="'.$mobile.'" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group" style="padding-bottom: 10px;">
                                                                <label style="top: 5px;">Country</label>
                                                                <input type="text" class="form-control" placeholder="Country" value="'.$country.'" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pl-1">
                                                            <div class="form-group" style="padding-bottom: 10px;">
                                                                <label style="top: 5px;">Timezone</label>
                                                                <input type="text" class="form-control" placeholder="Timezone" value="'.$timezone.'" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label class="security" style="font-size: 16px; font-weight: 500; color: black">Current Security Level: '.$security_level.'</label>
                                                                '.$change_security_level.'
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-info btn-fill pull-right" name="edit-profile">Edit Profile</button>
                                                    <a href="dashboard.php">Back to Dashboard</a>
                                                    <div class="clearfix"></div>
                                                </form>
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
                                        <li>
                                            <a href="#">
                                                Blog
                                            </a>
                                        </li>
                                    </ul>
                                    <p class="copyright text-center">
                                        ©
                                        <script>
                                            document.write(new Date().getFullYear())
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
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['edit-profile'])) {
    generate_token();
    if ($stmt = $con->prepare('SELECT userid, first_name, last_name, email_id, country, mobile, timezone, remaining_balance, isVerified, is_KYC_request_sent FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();
        $notifications = 0;
        $notification = "";
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid, $first_name, $last_name, $email_id, $country, $mobile, $timezone, $balance, $isVerified, $is_KYC_request_sent);
            $stmt->fetch();
            if ($isVerified == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
            }
            if ($is_KYC_request_sent == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
            }
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
                setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
                $change_security_level = '<a class="dropdown-item" href="security.php?level=low">Decrease Security Level</a>';
                $security_level = 'Maximum';
            } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
                $change_security_level = '<a class="dropdown-item" href="security.php?level=high">Increase Security Level</a> <a class="dropdown-item" href="security.php?level=low">Decrease Security Level</a>';
                $security_level = 'High';
            } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
                setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
                $change_security_level = '<a class="dropdown-item" href="security.php?level=max">Increase Security Level</a>';
                $security_level = 'Low';
            }
            echo '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="utf-8" />
                <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
                <link rel="icon" type="image/png" href="./assets/img/favicon.ico">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
                <title>User Profile</title>
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
                            <a class="navbar-brand" href="#"> Profile </a>
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
                                            <a class="dropdown-item active d-item" href="profile.php">Profile</a>
                                            <a class="dropdown-item d-item" href="change-password.php">Change Password</a>
                                            <a class="dropdown-item d-item" href="kyc.php">View KYC Status</a>
                                            <div class="divider"></div>
                                            <a class="dropdown-item d-item" href="login-history.php">Login History</a>
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
                                <div class="row d-flex justify-content-center">
                                    <div class="col-md-10">
                                        <div class="card">
                                            <div class="card-header" style="padding-bottom: 15px;>
                                                <h4 class="card-title">Edit Profile</h4>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST">
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>First Name</label>
                                                                <input type="text" class="form-control" placeholder="First Name" value="'.$first_name.'" name="fname">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pl-1">
                                                            <div class="form-group">
                                                                <label>Last Name</label>
                                                                <input type="text" class="form-control" placeholder="Last Name" value="'.$last_name.'" name="lname">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label for="exampleInputEmail1">Email address</label>
                                                                <input type="email" class="form-control" placeholder="Email" value="'.$email_id.'" name="email">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pl-1">
                                                            <div class="form-group">
                                                                <label>Mobile</label>
                                                                <input type="number" class="form-control" placeholder="Mobile" value="'.$mobile.'" name="mobile">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>Country</label>
                                                                <input type="text" class="form-control" placeholder="Country" value="'.$country.'" name="country">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pl-1">
                                                            <div class="form-group">
                                                                <label>Timezone</label>
                                                                <input type="text" class="form-control" placeholder="Timezone" value="'.$timezone.'" name="timezone">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--<div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>Current Scurity Level:'.$security_level.'</label>
                                                                '.$change_security_level.'
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                    <input type="hidden" id="csrf-token" min="0" class="form-control" required name="csrf_token" value="'.$_SESSION['csrf_token'].'">
                                                    <button type="submit" class="btn btn-info btn-fill pull-right" name="submit">Update Profile</button>
                                                    <button type="submit" class="btn btn-danger btn-fill pull-right" style="margin-right:10px" name="delete-account">Delete Account</button>
                                                    <a href="dashboard.php">Back to Dashboard</a>
                                                    <div class="clearfix"></div>
                                                </form>
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
                                        <li>
                                            <a href="#">
                                                Blog
                                            </a>
                                        </li>
                                    </ul>
                                    <p class="copyright text-center">
                                        ©
                                        <script>
                                            document.write(new Date().getFullYear())
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
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    if (empty($_POST['fname']) || empty($_POST['lname']) || empty($_POST['email']) || empty($_POST['mobile']) || empty($_POST['country']) || empty($_POST['timezone'])) {
        exit('<script>alert("Please fill in all the fields");  window.location = "profile.php"</script>');
    }
    
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
    if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        if (!check_token()) {
            exit('<script>alert("Invalid token");  window.location = "change-password.php"</script>');
        }
    }    
    if ($stmt = $con->prepare('UPDATE userMaster SET first_name = ?, last_name = ?, email_id = ?, country = ?, mobile = ?, timezone = ? WHERE email_id = ?')) {
        $stmt->bind_param('sssssss', $_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['country'], $_POST['mobile'], $_POST['timezone'], $email);
        if(!$stmt->execute()){
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
                setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
            } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
            } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
                setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
            }
            echo('<script>alert("Please try again.");  window.location = "profile.php"</script>');
            exit;
        }
        $_SESSION['email'] = $email;
        if ($_COOKIE['fnz_cookie_val'] == 'no') {
            setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
        } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
            setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
        } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
            setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
        }
        echo('<script>alert("Profile updated successfully.");  window.location = "profile.php"</script>');

    }
    
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['delete-account'])) {
    if ($stmt = $con->prepare('UPDATE userMaster SET isDeleted = 1 WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        if(!$stmt->execute()){
            echo('<script>alert("Please try again.");  window.location = "profile.php"</script>');
            exit;
        }
        echo('<script>alert("Account deleted successfully.");  window.location = "index.php"</script>');
    }
    if ($_COOKIE['email'] != $_SESSION['email']) {
        setcookie("email", $_SESSION['email'], time() + (86400 * 30), "/");
    }
}
?>