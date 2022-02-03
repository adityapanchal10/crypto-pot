<?php

session_start();

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
} else if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['type'])) {
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
    if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '' || !isset($_GET['type'])) {
        $type = 'view.php';
    } else {
        $type = $_GET['type'];
    }

    if ($type != 'view.php') {
        $type = 'kyc/'.$_GET['type'];
        readfile($type);
        exit;
    }

    // $email = $_SESSION['email'];

    include "db_connect.php";

    if ($stmt = $con->prepare('SELECT remaining_balance, isVerified, is_KYC_request_sent, isKYCverified FROM userMaster WHERE email_id = ?')) {
        $notification = '';
        $notifications = 0;
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($balance, $isVerified, $kyc_request, $kyc_verified);
            $stmt->fetch();
            $stmt->close();
            if ($isVerified == 0) {
              $notifications += 1;
              $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
            }
            if ($kyc_request == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
            }
            if ($stmt = $con->prepare('SELECT fname, mname, lname, email, gender, addressLine1, addressLine2, city, state, zipCode, documentType, document FROM kycMaster WHERE userid = ?')) {
                $stmt->bind_param('i', $_SESSION['id']);
                $stmt->execute();
                $stmt->bind_result($fname, $mname, $lname, $email, $gender, $addressLine1, $addressLine2, $city, $state, $zipCode, $documentType, $document);
                $stmt->fetch();
                $document = base64_encode($document);
                $stmt->close();
            }
            if ($kyc_verified == 1) {
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

                    <!-- Favicons --> 
                    <link href="./assets/img/kyc.png" rel="icon">
                    <link href="./assets/img/kyc.png" rel="apple-touch-icon">

                    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
                    <title>KYC</title>
                    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />

                    <!--     Fonts and icons     -->
                    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
                    <!-- CSS Files -->
                    <link href="./assets/vendor/bootstrap_dash/bootstrap.min.css" rel="stylesheet" />
                    <link href="./assets/css/light-bootstrap-kyc.css" rel="stylesheet" />
                    <link rel="stylesheet" href="assets/css/kyc.css">
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
                            <a class="navbar-brand" href="#"> View KYC Status </a>
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
                                            <a class="dropdown-item active" href="kyc.php">View KYC Status</a>
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
                            <div class="content">
                              <div class="container-fluid">
                              <form class="container" action="#" enctype="multipart/form-data">
                              <div id="inputText" class="text-content">
                      
                                  <div class="form-row">
                                      <div class="form-group col-md-4">
                                          <label for="inputEmail4">First Name</label>
                                          <input type="text" class="form-control" id="inputtext" placeholder="'.$fname.'" name="fname" disabled>
                                      </div>
                                      <div class="form-group col-md-4">
                                          <label for="inputEmail4">Middle Name</label>
                                          <input type="text" class="form-control" id="inputtext" placeholder="'.$mname.'" name="mname" disabled>
                                      </div>
                                      <div class="form-group col-md-4">
                                          <label for="inputEmail4">Last Name</label>
                                          <input type="text" class="form-control" id="inputtext" placeholder="'.$lname.'" name="lname" disabled>
                                      </div>
                                  </div>
                                  <div class="form-row">
                                      <div class="form-group col-md-6">
                                          <label for="inputEmail4">Email</label>
                                          <input type="email" class="form-control" id="email" placeholder="'.$email.'" name="email" disabled>
                                      </div>
                                  </div>
                                  <fieldset class="form-group">
                                      <div class="row">
                                          <legend class="col-form-label col-sm-2 pt-0">Gender</legend>
                                          <div class="col-auto">
                                              <div class="form-check gender">                
                                                  <label class="form-check-label radio-inline" for="gridRadios3">
                                                      <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                          id="gridRadios3" value="'.$gender.'" checked disabled>
                                                      '.$gender.'
                                                  </label>
                                              </div>
                                          </div>
                                      </div>
                                  </fieldset>
                                  <div class="form-group">
                                      <label for="inputAddress">Address Line 1</label>
                                      <input type="text" class="form-control" id="inputAddress" placeholder="'.$addressLine1.'" name="address_line_1" disabled>
                                  </div>
                                  <div class="form-group">
                                      <label for="inputAddress2">Address Line 2</label>
                                      <input type="text" class="form-control" id="inputAddress2" placeholder="'.$addressLine2.'" name="address_line_2" disabled>
                                  </div>
                      
                                  <div class="form-row">
                                      <div class="form-group col-md-6">
                                          <label for="inputCity">City</label>
                                          <input type="text" class="form-control" id="inputCity" placeholder="'.$city.'" name="city" disabled>
                                      </div>
                                      <div class="form-group col-md-4">
                                          <label for="inputState">State</label>
                                          <input type="text" class="form-control" id="inputState" placeholder="'.$state.'" name="state" disabled>
                                      </div>
                                      <div class="form-group col-md-2">
                                          <label for="inputZip">Zip</label>
                                          <input type="text" class="form-control" id="inputZip" placeholder="'.$zipCode.'" name="zipCode" disabled>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label for="kycmethod" class="col-md-5 col-form-label">Choose your document</label>
                                      <div class="col-md-7">
                                          <select id="inputState" class="form-control" name="document_type" disabled>
                                              <option selected>'.$documentType.'</option>
                                          </select>
                                      </div>
                                  </div>
                              </div>
                      
                              <!-- Upload Area -->
                              <div id="uploadArea" class="upload-area">
                                  <!-- Header -->
                                  <div class="upload-area__header">
                                      <h1 class="upload-area__title">Your KYC is already verified.</h1>
                                  </div>
                                  <!-- End Header -->
                      
                                  <!-- Drop Zoon -->
                                  <div id="dropZoon" class="upload-area__drop-zoon drop-zoon">
                                      <img src="data:image/jpeg;base64,'.$document.'" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image" draggable="false" style="display: block;">
                                  </div>
                                  <!-- End Drop Zoon -->
                      
                                  <!-- File Details -->
                                  <div id="fileDetails" class="upload-area__file-details file-details">
                                      <h3 class="file-details__title">Uploaded File</h3>
                      
                                      <div id="uploadedFile" class="uploaded-file">
                                          <div class="uploaded-file__icon-container">
                                              <i class="bx bxs-file-blank uploaded-file__icon"></i>
                                              <span class="uploaded-file__icon-text"></span> <!-- Data Will be Comes From Js -->
                                          </div>
                      
                                          <div id="uploadedFileInfo" class="uploaded-file__info">
                                              <span class="uploaded-file__name">Proejct 1</span>
                                              <span class="uploaded-file__counter">0%</span>
                                          </div>
                                      </div>
                                  </div>
                                  <!-- End File Details -->
                              </div>
                              <!-- End Upload Area -->
                          </form>
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
                <script src="assets/js/kyc.js"></script>
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
            } else if ($kyc_request == 1) {
                echo '<!DOCTYPE html>

                <html lang="en">
            
                <head>
                    <meta charset="utf-8" />

                    <!-- Favicons --> 
                    <link href="./assets/img/kyc.png" rel="icon">
                    <link href="./assets/img/kyc.png" rel="apple-touch-icon">

                    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
                    <title>KYC</title>
                    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />

                    <!--     Fonts and icons     -->
                    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
                    <!-- CSS Files -->
                    <link href="./assets/vendor/bootstrap_dash/bootstrap.min.css" rel="stylesheet" />
                    <link href="./assets/css/light-bootstrap-kyc.css" rel="stylesheet" />
                    <link rel="stylesheet" href="assets/css/kyc.css">
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
                            <a class="navbar-brand" href="#"> View KYC Status </a>
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
                                            <a class="dropdown-item active" href="kyc.php">View KYC Status</a>
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
                            <div class="content">
                              <div class="container-fluid">
                              <form class="container" action="#" enctype="multipart/form-data">
                              <div id="inputText" class="text-content">
                      
                                  <div class="form-row">
                                      <div class="form-group col-md-4">
                                          <label for="inputEmail4">First Name</label>
                                          <input type="text" class="form-control" id="inputtext" placeholder="'.$fname.'" name="fname" disabled>
                                      </div>
                                      <div class="form-group col-md-4">
                                          <label for="inputEmail4">Middle Name</label>
                                          <input type="text" class="form-control" id="inputtext" placeholder="'.$mname.'" name="mname" disabled>
                                      </div>
                                      <div class="form-group col-md-4">
                                          <label for="inputEmail4">Last Name</label>
                                          <input type="text" class="form-control" id="inputtext" placeholder="'.$lname.'" name="lname" disabled>
                                      </div>
                                  </div>
                                  <div class="form-row">
                                      <div class="form-group col-md-6">
                                          <label for="inputEmail4">Email</label>
                                          <input type="email" class="form-control" id="email" placeholder="'.$email.'" name="email" disabled>
                                      </div>
                                  </div>
                                  <fieldset class="form-group">
                                      <div class="row">
                                          <legend class="col-form-label col-sm-2 pt-0">Gender</legend>
                                          <div class="col-auto">
                                              <div class="form-check gender">                
                                                  <label class="form-check-label radio-inline" for="gridRadios3">
                                                      <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                          id="gridRadios3" value="'.$gender.'" checked disabled>
                                                      '.$gender.'
                                                  </label>
                                              </div>
                                          </div>
                                      </div>
                                  </fieldset>
                                  <div class="form-group">
                                      <label for="inputAddress">Address Line 1</label>
                                      <input type="text" class="form-control" id="inputAddress" placeholder="'.$addressLine1.'" name="address_line_1" disabled>
                                  </div>
                                  <div class="form-group">
                                      <label for="inputAddress2">Address Line 2</label>
                                      <input type="text" class="form-control" id="inputAddress2" placeholder="'.$addressLine2.'" name="address_line_2" disabled>
                                  </div>
                      
                                  <div class="form-row">
                                      <div class="form-group col-md-6">
                                          <label for="inputCity">City</label>
                                          <input type="text" class="form-control" id="inputCity" placeholder="'.$city.'" name="city" disabled>
                                      </div>
                                      <div class="form-group col-md-4">
                                          <label for="inputState">State</label>
                                          <input type="text" class="form-control" id="inputState" placeholder="'.$state.'" name="state" disabled>
                                      </div>
                                      <div class="form-group col-md-2">
                                          <label for="inputZip">Zip</label>
                                          <input type="text" class="form-control" id="inputZip" placeholder="'.$zipCode.'" name="zipCode" disabled>
                                      </div>
                                  </div>
                                  <div class="form-group row">
                                      <label for="kycmethod" class="col-md-5 col-form-label">Choose your document</label>
                                      <div class="col-md-7">
                                          <select id="inputState" class="form-control" name="document_type" disabled>
                                              <option selected>'.$documentType.'</option>
                                          </select>
                                      </div>
                                  </div>
                              </div>
                      
                              <!-- Upload Area -->
                              <div id="uploadArea" class="upload-area">
                                  <!-- Header -->
                                  <div class="upload-area__header">
                                      <h1 class="upload-area__title">KYC request already submitted.</h1>
                                  </div>
                                  <!-- End Header -->
                      
                                  <!-- Drop Zoon -->
                                  <div id="dropZoon" class="upload-area__drop-zoon drop-zoon">
                                      <img src="data:image/jpeg;base64,'.$document.'" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image" draggable="false" style="display: block;">
                                  </div>
                                  <!-- End Drop Zoon -->
                      
                                  <!-- File Details -->
                                  <div id="fileDetails" class="upload-area__file-details file-details">
                                      <h3 class="file-details__title">Uploaded File</h3>
                      
                                      <div id="uploadedFile" class="uploaded-file">
                                          <div class="uploaded-file__icon-container">
                                              <i class="bx bxs-file-blank uploaded-file__icon"></i>
                                              <span class="uploaded-file__icon-text"></span> <!-- Data Will be Comes From Js -->
                                          </div>
                      
                                          <div id="uploadedFileInfo" class="uploaded-file__info">
                                              <span class="uploaded-file__name">Proejct 1</span>
                                              <span class="uploaded-file__counter">0%</span>
                                          </div>
                                      </div>
                                  </div>
                                  <!-- End File Details -->
                              </div>
                              <!-- End Upload Area -->
                          </form>      
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
                <script src="assets/js/kyc.js"></script>
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
            } else {
                header('Location: kyc.php');
            }
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == "GET" && !isset($_GET['type'])) {
    $email = $_SESSION['email'];
    generate_token();
    include "db_connect.php";
    if ($stmt = $con->prepare('SELECT remaining_balance, isVerified, is_KYC_request_sent, isKYCverified FROM userMaster WHERE email_id = ?')) {
        $notification = '';
        $notifications = 0;
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($balance, $isVerified, $kyc_request, $kyc_verified);
            $stmt->fetch();
            $stmt->close();
            if ($isVerified == 0) {
              $notifications += 1;
              $notification .= '<a class="dropdown-item" href="verify-email.php">Please verify your account</a>';
            }
            if ($kyc_request == 0) {
                $notifications += 1;
                $notification .= '<a class="dropdown-item" href="kyc.php">Please upload your KYC documents</a>';
            }
            if ($stmt = $con->prepare('SELECT fname, mname, lname, email, gender, addressLine1, addressLine2, city, state, zipCode, documentType, document FROM kycMaster WHERE userid = ?')) {
                $stmt->bind_param('i', $_SESSION['id']);
                $stmt->execute();
                $stmt->bind_result($fname, $mname, $lname, $email, $gender, $addressLine1, $addressLine2, $city, $state, $zipCode, $documentType, $document);
                $stmt->fetch();
                $document = base64_encode($document);
                $stmt->close();
            }
            if ($kyc_verified == 1) {
                header('Location: kyc.php?type=view.php');
            } else if ($kyc_request == 1) {
                header('Location: kyc.php?type=view.php');
            } else {
                echo '
                <!DOCTYPE html>

                <html lang="en">
            
                <head>
                    <meta charset="utf-8" />

                    <!-- Favicons --> 
                    <link href="./assets/img/kyc.png" rel="icon">
                    <link href="./assets/img/kyc.png" rel="apple-touch-icon">
                    
                    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
                    <title>KYC</title>
                    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
                    
                    <!--     Fonts and icons     -->
                    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
                    <!-- CSS Files -->
                    <link href="./assets/vendor/bootstrap_dash/bootstrap.min.css" rel="stylesheet" />
                    <link href="./assets/css/light-bootstrap-kyc.css" rel="stylesheet" />
                    <link rel="stylesheet" href="assets/css/kyc.css">
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
                            <a class="navbar-brand" href="#"> View KYC Status </a>
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
                                            <a class="dropdown-item active" href="kyc.php">View KYC Status</a>
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
                            <div class="content">
                              <div class="container-fluid">
                              <form class="container" action="kyc.php" method="POST" enctype="multipart/form-data">
                        <div id="inputText" class="text-content">
                
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">First Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="John" name="fname">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Middle Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="M." name="mname">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Last Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="Doe" name="lname">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputEmail4">Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="johndoe99@email.com" name="email">
                                </div>
                            </div>
                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Gender</legend>
                                    <div class="col-auto">
                                        <div class="form-check gender">
                                            <label class="form-check-label radio-inline" for="gridRadios1">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios1" value="Male">
                                                Male
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios2">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios2" value="female">
                                                Female
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios3">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios3" value="other">
                                                Other
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="form-group">
                                <label for="inputAddress">Address Line 1</label>
                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St" name="address_line_1">
                            </div>
                            <div class="form-group">
                                <label for="inputAddress2">Address Line 2</label>
                                <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor" name="address_line_2">
                            </div>
                
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputCity">City</label>
                                    <input type="text" class="form-control" id="inputCity" name="city">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputState">State</label>
                                    <input type="text" class="form-control" id="inputState" name="state">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="inputZip">Zip</label>
                                    <input type="text" class="form-control" id="inputZip" name="zipCode">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kycmethod" class="col-md-5 col-form-label">Choose your document</label>
                                <div class="col-md-7">
                                    <select id="inputState" class="form-control" name="document_type">
                                        <option selected>Choose...</option>
                                        <option>Adhaar card</option>
                                        <option>Passport</option>
                                        <option>PAN card</option>
                                        <option>Driving license</option>
                                        <option>Voter ID</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="gridCheck" name="agree">
                                    <label class="form-check-label" for="gridCheck">
                                        I agree to the terms and conditions. I confirm that all the information provided above is true.
                                    </label>
                                </div>
                            </div>
                            <input type="hidden" id="csrf-token" min="0" class="form-control" required name="csrf_token" value="'.$_SESSION['csrf_token'].'">
                            <button type="submit" class="btn btn-primary gokyc"name="kyc-submit">Submit</button>
                        </div>
                
                        <!-- Upload Area -->
                        <div id="uploadArea" class="upload-area">
                            <!-- Header -->
                            <div class="upload-area__header">
                                <h1 class="upload-area__title">Upload your document</h1>
                                <p class="upload-area__paragraph">
                                    Supported File
                                    <strong class="upload-area__tooltip">
                                        Types
                                        <span class="upload-area__tooltip-data"></span> <!-- Data Will be Comes From Js -->
                                    </strong>
                                </p>
                            </div>
                            <!-- End Header -->
                
                            <!-- Drop Zoon -->
                            <div id="dropZoon" class="upload-area__drop-zoon drop-zoon">
                                <span class="drop-zoon__icon">
                                    <i class="bx bxs-file-image"></i>
                                </span>
                                <p class="drop-zoon__paragraph">Drop your file here or Click to browse</p>
                                <span id="loadingText" class="drop-zoon__loading-text">Please Wait</span>
                                <img src="" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image" draggable="false">
                                <input type="file" id="fileInput" class="drop-zoon__file-input" accept="image/*" name="document">
                            </div>
                            <!-- End Drop Zoon -->
                
                            <!-- File Details -->
                            <div id="fileDetails" class="upload-area__file-details file-details">
                                <h3 class="file-details__title">Uploaded File</h3>
                
                                <div id="uploadedFile" class="uploaded-file">
                                    <div class="uploaded-file__icon-container">
                                        <i class="bx bxs-file-blank uploaded-file__icon"></i>
                                        <span class="uploaded-file__icon-text"></span> <!-- Data Will be Comes From Js -->
                                    </div>
                
                                    <div id="uploadedFileInfo" class="uploaded-file__info">
                                        <span class="uploaded-file__name">Proejct 1</span>
                                        <span class="uploaded-file__counter">0%</span>
                                    </div>
                                </div>
                            </div>
                            <!-- End File Details -->
                        </div>
                        <!-- End Upload Area -->
                    </form>
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
                <script src="assets/js/kyc.js"></script>
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
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['kyc-submit'])) {
    $email = $_SESSION['email'];
    $userid = $_SESSION['id'];

    if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
    }
    if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
        if (!check_token()) {
            $_SESSION['error'] = 'Please fill in all the fields';
            header('Location: kyc.php');
            exit;
            // exit('<script>alert("Invalid token");  window.location = "change-password.php"</script>');
        }
    }    

    include "db_connect.php";
    if ($stmt = $con->prepare('SELECT is_KYC_request_sent, isKYCverified FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($kyc_request, $kyc_verified);
            $stmt->fetch();

            if ($kyc_verified == 1) {
                echo '<script>window.location = "kyc.php";</script>';
            } else if ($kyc_request == 1) {
                echo '<script>window.location = "kyc.php";</script>';
            } else if ($email != $_POST['email']) {
                $_SESSION['error'] = 'Please check your details.';
                header('Location: kyc.php');
                exit;
                // echo '<script>alert("Please check your details"); window.location = "kyc.php";</script>';
            } else {
                if (!isset($_POST['fname']) || !isset($_POST['mname']) || !isset($_POST['lname']) || !isset($email) || !isset($_POST['gridRadios']) || !isset($_POST['address_line_1']) || !isset($_POST['address_line_2']) || !isset($_POST['city']) || !isset($_POST['state']) || !isset($_POST['zipCode']) || !isset($_POST['document_type']) || !isset($imgContent)) {
                    $_SESSION['error'] = 'Please fill in all the fields';
                    header('Location: kyc.php');
                    exit;
                } else if (empty($_POST['fname']) || empty($_POST['mname']) || empty($_POST['lname']) || empty($email) || empty($_POST['gridRadios']) || empty($_POST['address_line_1']) || empty($_POST['address_line_2']) || empty($_POST['city']) || empty($_POST['state']) || empty($_POST['zipCode']) || empty($_POST['document_type']) || empty($imgContent)) {
                    $_SESSION['error'] = 'Please fill in all the fields';
                    header('Location: kyc.php');
                    exit;
                }
                if ($stmt = $con->prepare('INSERT INTO kycMaster (userid, fname, mname, lname, email, gender, addressLine1, addressLine2, city, state, zipCode, documentType, document) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')) {
                    if(!empty($_FILES['document']['name'])) { 
                        // Get file info 
                        $fileName = basename($_FILES['document']['name']); 
                        $fileType = pathinfo($fileName, PATHINFO_EXTENSION); 
                         
                        // Allow certain file formats 
                        $allowTypes = array('jpg','png','jpeg','gif'); 
                        if(in_array($fileType, $allowTypes)){ 
                            $image = $_FILES['document']['tmp_name']; 
                            $imgContent = file_get_contents($image);
                            $stmt->bind_param('isssssssssiss', $userid, $_POST['fname'], $_POST['mname'], $_POST['lname'], $email, $_POST['gridRadios'], $_POST['address_line_1'], $_POST['address_line_2'], $_POST['city'], $_POST['state'], $_POST['zipCode'], $_POST['document_type'], $imgContent);
                            if(!$stmt->execute()){
                                echo $stmt->error;
                                exit;
                            } else {
                                if ($stmt = $con->prepare('UPDATE userMaster SET is_KYC_request_sent = 1 WHERE email_id = ?')) {
                                    $stmt->bind_param('s', $email);             
                                    if ($stmt->execute()) {
                                        header('Location: kyc.php');
                                    }
                                }
                            }
                        }
                    } else {
                        $_SESSION['error'] = 'An error occurred! Please check your file';
                        header('Location: kyc.php');
                        exit;
                        // echo '<script>alert("An error occurred! Please check your file"); window.location = "kyc.php";</script>';
                    }
                }
            }
        }
    }
}
?>