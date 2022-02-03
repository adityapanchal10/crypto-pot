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

function generate_string() {
    $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < 6; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
 
    return $random_string;
}

function destroySessionToken() {
    unset( $_SESSION[ 'csrf_token' ] );
}

if (!isset($_GET['email'])) {
    header('Location: login.php');
} else {
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
        generate_token();
        if ($stmt = $con->prepare('SELECT email_verfication_code FROM userMaster WHERE email_id = ?')) {

            $stmt->bind_param('s', $_GET['email']);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($email_verfication_code);
                $stmt->fetch();
                if ($_POST['email-verification-code'] == $email_verfication_code) {
                    if ($stmt = $con->prepare('UPDATE userMaster SET isVerified = 1 WHERE email_id = ?')) {

                        $stmt->bind_param('s', $_GET['email']);                        
                        if($stmt->execute()){
                            //header('Refresh:5; url=dashboard.php');
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
                            echo '<!DOCTYPE html>
                            <html lang="en">
                            
                            <head>
                            <meta charset="UTF-8">

                            <!-- Favicons -->
                            <link href="./assets/img/crypto.png" rel="icon">
                            <link href="./assets/img/crypto.png" rel="apple-touch-icon">

                            <title>Forgot Password</title>
                            
                            <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
                            <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
                            <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
                            
                            <link rel="stylesheet" href="assets/css/signup.css">
                            
                            <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
                                crossorigin="anonymous"></script>
                            <script src="assets/js/validator.js"></script>

                            </head>
                            
                            <body>
                            '.$error.'
                            <div class="center">
                                <div class="container">
                                <div id="carouselExampleCaptions" class="text_login carousel slide carousel-fade" data-bs-ride="carousel">
                                    <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"
                                        aria-current="true" aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"
                                        aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"
                                        aria-label="Slide 3"></button>
                                    </div>
                                    <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="assets/img/CityGrid.png" class="" alt="...">
                                        <div class="carousel-caption d-none d-md-block">
                                        <h2 style="font-size: 300%; font-weight: 500;">The Easiest Way To Invest</h2>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="assets/img/peakpx.jpg" class="" alt="...">
                                        <div class="carousel-caption d-none d-md-block">
                                        <h2 style="font-size: 300%; font-weight: 500;">Trade among five different asset classes from one
                                            convenient account</h2>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="assets/img/peakpx(1).jpg" class="" alt="...">
                                        <div class="carousel-caption d-none d-md-block">
                                        <h2 style="font-size: 300%; font-weight: 500;">You can choose from cryptos, metals, equities, currencies
                                            and more.</h2>
                                        </div>
                                    </div>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            
                                <div class="login-content">
                            
                                    <form class="loginform l4" action="forgot-password.php?email='.$_GET['email'].'" method="POST" onsubmit="return forgotPasswordValidator()">
                                        <img src="">
                                        <h2 class="title">Change your password here.</h2>
                                        <div class="input-div pass">
                                            <div class="inc">
                                            <i class="pass"></i>
                                            </div>
                                            <div class="div">
                                            <h5>New Password</h5>
                                            <input type="password" class="input" name="password" id="new-password">
                                            </div>
                                        </div>
                                        <p id="password-message" style="font-size:75% ; color:#999"> Must have at least 8 characters, 1 uppercase, 1 lowercase and 1 number
                                        or special character</p>
                                        <div class="input-div pass">
                                            <div class="inc">
                                            <i class="pass"></i>
                                            </div>
                                            <div class="div">
                                            <h5>Confirm Password</h5>
                                            <input type="password" class="input" name="password" id="password-verify">
                                            </div>
                                        </div>
                                        <a href="#" id="backTol3" class="forgot">Go back</a>
                                        <input type="hidden" id="csrf-token" min="0" class="form-control" required name="csrf_token" value="'.$_SESSION['csrf_token'].'">
                                        <input id="passchanged" type="submit" class="btn" value="Change Password" name="change-pass">
                                    </form>
                            
                                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true"
                                    style="position: absolute; top: 0; right: 0;">
                                    <div class="toast-header">
                                        <strong class="me-auto">Password Change Successful</strong>
                                        <small>Just now</small>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close">
                                        </button>
                                    </div>
                                    <div class="toast-body">
                                        Your new password has been set, proceed to <a href="login.html">login</a> now
                                    </div>
                                    </div>
                            
                                </div>
                            
                            
                                </div>
                            </div>
                            
                            
                            <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
                            
                            <script>
                                const inputs = document.querySelectorAll(".input");
                                function addcl() {
                                let parent = this.parentNode.parentNode;
                                parent.classList.add("focus");
                                }
                                function remcl() {
                                let parent = this.parentNode.parentNode;
                                if (this.value == "") {
                                    parent.classList.remove("focus");
                                }
                                }
                                inputs.forEach((input) => {
                                input.addEventListener("focus", addcl);
                                input.addEventListener("blur", remcl);
                                });
                            
                                $(document).ready(function () {
                                $("#forgotPwd").click(function () {
                                    $(".l1").removeClass("show");
                                    $(".l1").addClass("fadeout");
                                    $(".l1").addClass("hidden");
                                    $(".l2").removeClass("hidden");
                                    $(".l2").addClass("fadeout");
                                    $(".l2").addClass("show");
                                });
                            
                                $("#backTol1").click(function () {
                                    $(".l2").removeClass("show");
                                    $(".l2").addClass("fadeout");
                                    $(".l2").addClass("hidden");
                                    $(".l1").removeClass("hidden");
                                    $(".l1").addClass("fadeout");
                                    $(".l1").addClass("show");
                                });
                            
                                $("#tol3").click(function () {
                                    $(".l2").removeClass("show");
                                    $(".l2").addClass("fadeout");
                                    $(".l2").addClass("hidden");
                                    $(".l3").removeClass("hidden");
                                    $(".l3").addClass("fadeout");
                                    $(".l3").addClass("show");
                                });
                            
                                $("#tol4").click(function () {
                                    $(".l3").removeClass("show");
                                    $(".l3").addClass("fadeout");
                                    $(".l3").addClass("hidden");
                                    $(".l4").removeClass("hidden");
                                    $(".l4").addClass("fadeout");
                                    $(".l4").addClass("show");
                                });
                            
                                $("#backTol3").click(function () {
                                    $(".l4").removeClass("show");
                                    $(".l4").addClass("fadeout");
                                    $(".l4").addClass("hidden");
                                    $(".l3").removeClass("hidden");
                                    $(".l3").addClass("fadeout");
                                    $(".l3").addClass("show");
                                });
                            
                                $(".l4").submit(function () {
                                    console.log("nada");
                                    $(".toast").toast()
                                    $(".toast").toast("show")
                                })
                                });
                                $(document).ready(function(){
                                    $("#errorModal").modal("show");
                                });
                            </script>
                            </body>
                            
                            </html>';
                        }

                    /* $sql = "UPDATE userMaster SET isVerified = 1 where email_id='".$_SESSION['email']."'";
                    if (!$mysqli_query($con, $sql)) {
                        echo mysqli_error($con);
                    } else {
                        exit('<script>alert("Email verified successfully!")</script>');
                        header('Location: dashboard.php');
                    } */
                    }
                }
            }

        }
    } elseif ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['change-pass'])) {
        if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
            setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
        }
        if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
            if (!check_token()) {
                $_SESSION['error'] = 'Please fill in all the fields';
                header('Location: forgot-password.php');
                exit;
                // exit('<script>alert("Invalid token");  window.location = "forgot-password.php"</script>');
            }
        }    
        $password = $_POST['password'];
        $password_verify = $_POST['password-verify'];
        if ($password != $password_verify) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: forgot-password.php');
            exit;
            // echo '<script>alert("Passwords do not match!"); window.location="forgot-password.php"</script>';
        }
        if (preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/', $_POST['password']) == 0) {
            $_SESSION['error'] = 'Password must have at least 8 characters, 1 uppercase, 1 lowercase and 1 number or special character';
            header('Location: forgot-password.php');
            exit;
            // echo '<script>alert("Password must have at least 8 characters, 1 uppercase, 1 lowercase and 1 number or special character"); window.location = "forgot-password.php"</script>';
            // header('Location: signup.php');
        }
        if ($stmt = $con->prepare('UPDATE userMaster SET password = ? WHERE email_id = ?')) {
            $password_hash = hash('sha256', $_POST['password']);
            $stmt->bind_param('ss', $password_hash, $_GET['email']);                        
            if($stmt->execute()){
                //header('Refresh:5; url=dashboard.php');
                $_SESSION['error'] = 'Password changed successfully';
                header('Location: login.php');
                exit;
                // exit('<script>alert("Password Changed successfully!"); window.location="login.php"</script>');
            }
        }
    } else {
        $email = $_GET['email'];
        if ($stmt = $con->prepare('SELECT first_name  FROM userMaster WHERE email_id = ?')) {

            $stmt->bind_param('s', $email);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
    
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($first_name);
                $stmt->fetch();
                $code = generate_string();
                /* $sql = "UPDATE userMaster SET email_verfication_code ='" .$code. "' where email_id='".$_SESSION['email']."'";
    
                if (!$mysqli_query($con, $sql)) {
                    echo mysqli_error($con);
                    exit();
                } */
                if ($stmt = $con->prepare('UPDATE userMaster SET email_verfication_code = ? WHERE email_id = ?')) {

                    $stmt->bind_param('ss', $code, $_GET['email']);
                    // echo $code;
                    if($stmt->execute()){
                        require_once('phpmailer/PHPMailer.php');
                        require_once('phpmailer/SMTP.php');
                        require_once('phpmailer/Exception.php');
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                        $mail->IsSMTP();
                        $mail->Host = 'email-smtp.us-east-1.amazonaws.com';
                        $mail->SMTPAuth = true;                  
                        $mail->Username = 'AKIA3PI2ICWSJUQFO3VN';
                        $mail->Password = 'BNmIR3t6FlHavtU4wHqTKSSsFoR1lbE8+E/Ml0qs9hB0';
                        $mail->SMTPSecure = 'tls';//PHPMailer::ENCRYPTION_SMTPS;  
                        $mail->Port = 587;

                        $mail->setFrom('no-reply@forenzythreatlabs.com', 'Crypto-HoneyPot');
                        $mail->addAddress($_GET['email']);
                        $mail->addCustomHeader('X-SES-CONFIGURATION-SET','X-FNZ-THREATLABS-HDR');
                        $mail->Subject  =  'Reset your password';
                        $mail->IsHTML(true);
                        $mail->Body    = 'Your password reset code is '.$code.'';
                        if($mail->Send()) {
                            // echo "Check Your Email box and Click on the email verification link.";
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
                            echo '<!DOCTYPE html>
                            <html lang="en">
                            
                            <head>
                            <meta charset="UTF-8">
                            <title>Forgot Password</title>
                            
                            <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
                            <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
                            <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
                            
                            <link rel="stylesheet" href="assets/css/signup.css">
                            
                            <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
                                crossorigin="anonymous"></script>
                            
                            </head>
                            
                            <body>
                            '.$error.'
                            <div class="center">
                                <div class="container">
                                <div id="carouselExampleCaptions" class="text_login carousel slide carousel-fade" data-bs-ride="carousel">
                                    <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"
                                        aria-current="true" aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"
                                        aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"
                                        aria-label="Slide 3"></button>
                                    </div>
                                    <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="assets/img/CityGrid.png" class="" alt="...">
                                        <div class="carousel-caption d-none d-md-block">
                                        <h2 style="font-size: 300%; font-weight: 500;">The Easiest Way To Invest</h2>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="assets/img/peakpx.jpg" class="" alt="...">
                                        <div class="carousel-caption d-none d-md-block">
                                        <h2 style="font-size: 300%; font-weight: 500;">Trade among five different asset classes from one
                                            convenient account</h2>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="assets/img/peakpx(1).jpg" class="" alt="...">
                                        <div class="carousel-caption d-none d-md-block">
                                        <h2 style="font-size: 300%; font-weight: 500;">You can choose from cryptos, metals, equities, currencies
                                            and more.</h2>
                                        </div>
                                    </div>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            
                                <div class="login-content">
                            
                                <form class="loginform l4" action="forgot-password.php?email='.$_GET['email'].'" method="POST">
                                <img src="">
                                    <h2 class="title">Please enter the code you recieved here.</h2>
                                    <div class="input-div one">
                                        <div class="inc">
                                        <i class="usr"></i>
                                        </div>
                                        <div class="div">
                                        <h5>Code</h5>
                                        <input type="text" class="input" name="email-verification-code">
                                        </div>
                                    </div>
                                    <a href="login.php" id="backTol2" class="forgot">Go back</a>
                                    <input type="submit" id="submit" class="btn" value="Submit" name="submit">
                                    </form>
                            
                                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true"
                                    style="position: absolute; top: 0; right: 0;">
                                    <div class="toast-header">
                                        <strong class="me-auto">Password Change Successful</strong>
                                        <small>Just now</small>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close">
                                        </button>
                                    </div>
                                    <div class="toast-body">
                                        Your new password has been set, proceed to <a href="login.html">login</a> now
                                    </div>
                                    </div>
                            
                                </div>
                            
                            
                                </div>
                            </div>
                            
                            
                            <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
                            
                            <script>
                                const inputs = document.querySelectorAll(".input");
                                function addcl() {
                                let parent = this.parentNode.parentNode;
                                parent.classList.add("focus");
                                }
                                function remcl() {
                                let parent = this.parentNode.parentNode;
                                if (this.value == "") {
                                    parent.classList.remove("focus");
                                }
                                }
                                inputs.forEach((input) => {
                                input.addEventListener("focus", addcl);
                                input.addEventListener("blur", remcl);
                                });
                            
                                $(document).ready(function () {
                                $("#forgotPwd").click(function () {
                                    $(".l1").removeClass("show");
                                    $(".l1").addClass("fadeout");
                                    $(".l1").addClass("hidden");
                                    $(".l2").removeClass("hidden");
                                    $(".l2").addClass("fadeout");
                                    $(".l2").addClass("show");
                                });
                            
                                $("#backTol1").click(function () {
                                    $(".l2").removeClass("show");
                                    $(".l2").addClass("fadeout");
                                    $(".l2").addClass("hidden");
                                    $(".l1").removeClass("hidden");
                                    $(".l1").addClass("fadeout");
                                    $(".l1").addClass("show");
                                });
                            
                                $("#tol3").click(function () {
                                    $(".l2").removeClass("show");
                                    $(".l2").addClass("fadeout");
                                    $(".l2").addClass("hidden");
                                    $(".l3").removeClass("hidden");
                                    $(".l3").addClass("fadeout");
                                    $(".l3").addClass("show");
                                });
                            
                                $("#tol4").click(function () {
                                    $(".l3").removeClass("show");
                                    $(".l3").addClass("fadeout");
                                    $(".l3").addClass("hidden");
                                    $(".l4").removeClass("hidden");
                                    $(".l4").addClass("fadeout");
                                    $(".l4").addClass("show");
                                });
                            
                                $("#backTol3").click(function () {
                                    $(".l4").removeClass("show");
                                    $(".l4").addClass("fadeout");
                                    $(".l4").addClass("hidden");
                                    $(".l3").removeClass("hidden");
                                    $(".l3").addClass("fadeout");
                                    $(".l3").addClass("show");
                                });
                            
                                $(".l4").submit(function () {
                                    console.log("nada");
                                    $(".toast").toast()
                                    $(".toast").toast("show")
                                })
                                });
                                $(document).ready(function(){
                                    $("#errorModal").modal("show");
                                });
                            </script>
                            </body>
                            
                            </html>';
                        }
                        else {
                            // echo "Mail Error - >".$mail->ErrorInfo;
                            exit('<script>'.$mail->ErrorInfo.'</script>');
                            header('Location: login.php');
                        }

                    }
                }
            }
    
        }
        
    }
}
?>