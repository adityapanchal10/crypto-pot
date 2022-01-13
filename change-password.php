<?php

session_start();

include "db_connect.php";

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

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    generate_token();
    if ($stmt = $con->prepare('SELECT userid, remaining_balance, isVerified, is_KYC_request_sent FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();
        $notifications = 0;
        $notification = "";
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
            echo '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="utf-8" />
                <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
                <link rel="icon" type="image/png" href="./assets/img/favicon.ico">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
                <title>Change Password</title>
                <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no"
                    name="viewport" />
                <!--     Fonts and icons     -->
                <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
                <!-- CSS Files -->
                <link href="./assets/vendor/bootstrap_dash/bootstrap.min.css" rel="stylesheet" />
                <link href="./assets/css/light-bootstrap-dashboard.css" rel="stylesheet" />
                <link href="./assets/css/search.css" rel="stylesheet" />
                <script src="assets/js/validator.js"></script>

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
                            <a class="navbar-brand" href="#"> Change Password </a>
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
                                            <a class="dropdown-item active" href="change-password.php">Change Password</a>
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
                                <div class="row d-flex justify-content-center">
                                    <div class="col-md-10">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">Change Password</h4>
                                            </div>
                                            <div class="card-body">
                                                <form id="change-pass" method="POST" onsubmit="return changePasswordValidator()">
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>Old Password</label>
                                                                <input type="password" class="form-control" placeholder="Old Password" name="oldPassword" id="old-password">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>New Password</label>
                                                                <input type="password" class="form-control" placeholder="New Password" name="newPassword" id="new-password">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>Confirm New Password</label>
                                                                <input type="password" class="form-control" placeholder="Confirm New Password" name="confirmPassword" id="password-verify">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" id="csrf-token" min="0" class="form-control" required name="csrf_token" value="'.$_SESSION['csrf_token'].'">
                                                    <button type="submit" class="btn btn-info btn-fill pull-right" name="submit">Change password</button>
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
            
            </html>';
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    if (empty($_POST['oldPassword']) || empty($_POST['newPassword']) || empty($_POST['confirmPassword'])) {
        exit('<script>alert("Please fill in all the fields");  window.location = "change-password.php"</script>');
    }
    if ($_POST['newPassword'] != $_POST['confirmPassword']) {
        exit('<script>alert("New password and confirm password do not match");  window.location = "change-password.php"</script>');
    }
    if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
    }
    if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        if (!check_token()) {
            exit('<script>alert("Invalid token");  window.location = "change-password.php"</script>');
        }
        if (preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/', $_POST['newPassword']) == 0) {
            echo '<script>alert("Password must have at least 8 characters, 1 uppercase, 1 lowercase and 1 number or special character"); window.location = "change-password.php"</script>';
            exit;
        }
    }    
    if ($stmt = $con->prepare('SELECT password FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();
        $notifications = 0;
        $notification = "";
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($password);
            $stmt->fetch();
            $oldPassword = hash('sha256', $_POST['oldPassword']);
            if ($oldPassword == $password) {
                $newPassword = hash('sha256', $_POST['newPassword']);
                if ($stmt = $con->prepare('UPDATE usermaster SET password = ? WHERE email_id = ?')) {
                    $stmt->bind_param('ss', $newPassword, $_SESSION['email']);
                    if(!$stmt->execute()){
                        echo('<script>alert("Please try again.");  window.location = "change-password.php"</script>');
                        exit;
                    }
                    echo('<script>alert("Password updated successfully.");  window.location = "dashboard.php"</script>');
                }
            } else {
                exit('<script>alert("Old password is incorrect");  window.location = "change-password.php"</script>');
            }
        }
    }
}
?>