<?php

session_start();

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
            $contactHistory = '';
            if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
                setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
            }
            if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '' || !isset($_COOKIE['email'])) {
                if ($stmt_2 = $con->prepare('SELECT subject, quick_comment FROM contactMaster WHERE email_addr = ?')) {
                    $stmt_2->bind_param('s', $email);
                    $stmt_2->execute();
                    $stmt_2->store_result();
                    if ($stmt_2->num_rows > 0) {
                        $stmt_2->bind_result($subject, $quick_comment);
                        $i = 1;
                        while ($stmt_2->fetch()) {
                            $subject = htmlspecialchars($subject);
                            $quick_comment = htmlspecialchars(stripslashes($quick_comment));
                            $contactHistory .= '<div class="history">
                                <a href="#contact'.$i.'" data-toggle="collapse">'.$subject.'</a>
                                <div id="contact'.$i.'" class="collapse">
                                    <p>'.$quick_comment.'</p>
                                </div>
                            </div>';
                            $i++;
                        }
                    }
                }
            } else {
                if ($_COOKIE['fnz_cookie_val'] == 'high') {
                    $email = $_COOKIE['email'];
                } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                    $email = base64_decode($_COOKIE['email']);
                }
                $query  = "SELECT subject, quick_comment FROM contactMaster WHERE email_addr =  '$email';";
                $result = mysqli_query($con, $query) or function() {
                    $_SESSION['error'] = mysqli_error($con);
                    header('Location: contact.php');
                    exit;
                };
                // Get results
                $i = 1;
                while( $row = mysqli_fetch_assoc( $result ) ) {
                    //var_dump($row);
                    // Display values
                    $subject = $row["subject"];
                    $quick_comment  = $row["quick_comment"];
                    $contactHistory .= '<div class="history">
                        <a href="#contact'.$i.'" data-toggle="collapse">'.$subject.'</a>
                        <div id="contact'.$i.'" class="collapse">
                            <p>'.$quick_comment.'</p>
                        </div>
                    </div>';
                    $i++;
                }
            }
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
                setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
            } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
            } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
                setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
            }
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
                setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
            } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
            } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
                setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
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
                <title>Contact Us</title>
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
                            <li class="nav-item active">
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
                            <a class="navbar-brand" href="#"> Contact Form </a>
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
                                <div class="row d-flex justify-content-center">
                                    <div class="col-md-10">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">Contact Form</h4>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST">
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>Name</label>
                                                                <input type="text" class="form-control" placeholder="Name" value="'.$first_name.' '.$last_name.'" name="uname">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <!-- <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label for="exampleInputEmail1">Email address</label>
                                                                <input type="email" class="form-control" placeholder="Email" value="'.$email_id.'" name="email">
                                                            </div>
                                                        </div>
                                                    </div> -->
            
                                                    <div class="row">
                                                        <div class="col-md-6 pr-1">
                                                            <div class="form-group">
                                                                <label>Subject</label>
                                                                <input type="text" class="form-control" placeholder="Enter a subject" name="subject">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12 pr-1">
                                                            <div class="form-group">
                                                                <label>Message</label>
                                                                <textarea class="form-control" placeholder="Enter your message here" value="Enter your message here" name="message"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <button type="submit" class="btn btn-info btn-fill pull-right" name="submit">Submit</button>
                                                    <a href="dashboard.php">Back to Dashboard</a>
                                                    <div class="clearfix"></div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="contactHistory">
                                            <br />
                                            <a href="#contactHistory" data-toggle="collapse">
                                            <h4 style="margin-top: 15px;">Previous Contact History.</h4>
                                            </a>
                                            <div id="contactHistory" class="collapse">
                                                '.$contactHistory.'
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

    if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
    }

    // if (empty($_POST['uname'])) {
    //     exit("Please enter your name");
    // } else if (empty($_POST['email'])) {
    //     exit("Please enter your email");
    // } else if (empty($_POST['subject'])) {
    //     exit("Please enter your subject");
    // } else if (empty($_POST['message'])) {
    //     exit("Please enter your message");
    // }
    if (empty($_POST['uname']) || empty($_POST['subject']) || empty($_POST['message'])) {
        $_SESSION['error'] = 'Please fill in all the fields';
        header('Location: contact.php');
        exit;
        // exit('<script>alert("Please fill in all the fields");  window.location = "contact.php"</script>');
    }
    if ($_COOKIE['fnz_cookie_val'] == 'high') {
        $message = $_POST['message'];       
    } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
        $message = $_POST['message'];        
        $message = stripslashes( $message );
    } 
    else {
        $message = $_POST['message'];        
        $message = stripslashes( $message );
        // $message = filter_var($message, FILTER_SANITIZE_STRING);;
        // $message = htmlspecialchars( $message );
    }
    if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '' || !isset($_COOKIE['email'])) {
        $email_id = $_SESSION['email'];
    } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
        $email_id = base64_decode($_COOKIE['email']);
    } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
        $email_id = $_COOKIE['email'];
    }
    // $email_id = $_POST['email'];
    // $email_id = filter_var($email_id, FILTER_SANITIZE_EMAIL);
    $uname = $_POST['uname'];
    $uname = filter_var($uname, FILTER_SANITIZE_STRING);
    $subject = $_POST['subject'];
    $subject = filter_var($subject, FILTER_SANITIZE_STRING);
    
    if ($stmt = $con->prepare('INSERT INTO contactMaster (first_name, email_addr, subject, quick_comment) VALUES (?, ?, ?, ?)')) {
        $stmt->bind_param('ssss', $uname, $email_id, $subject, $message);
        if(!$stmt->execute()){
            $_SESSION['error'] = 'Please try again';
            header('Location: contact.php');
            exit;
            // echo('<script>alert("Please try again.");  window.location = "contact.php"</script>');
        }
        if ($_COOKIE['fnz_cookie_val'] == 'no') {
            setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
        } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
            setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 7), "/");
        } else if ($_COOKIE['fnz_cookie_val'] == 'high') {
            setcookie('email', $_SESSION['email'], time() + (86400 * 365), "/");
        }
        $_SESSION['error'] = 'Message sent successfully.';
        header('Location: dashboard.php');
        exit;
        // echo('<script>alert("Message sent successfully.");  window.location = "dashboard.php"</script>');

    }
}
?>