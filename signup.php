<?php

include "db_connect.php";
require_once 'recaptcha/src/autoload.php';

function getClientIP() {       
  if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
    return  $_SERVER["HTTP_X_FORWARDED_FOR"];  
  }else if (array_key_exists('REMOTE_ADDR', $_SERVER)) { 
    return $_SERVER["REMOTE_ADDR"]; 
  }else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
    return $_SERVER["HTTP_CLIENT_IP"]; 
  } 
}

function getClientLocation($ip) {
  $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
  $location = "{$details->city}, {$details->region}, {$details->country}";
  return $details->city;
}

function getClientTimezone($ip) {
  $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
  $timezone = $details->timezone;
  return $timezone;
}

function getClientCountry($ip) {
  $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
  $country = $details->country;
  return $country;
}

// We need to use sessions, so you should always start sessions using the below code.
session_start();

// If the user is not logged in redirect to the login page...
if (isset($_SESSION['email'])) {
	header('Location: dashboard.php');
	exit;
} else {

    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['signup'])) {   

        // Now we check if the data was submitted, isset() function will check if the data exists.
        if (!isset($_POST['email'], $_POST['password'])) {
            // Could not get the data that should have been sent.
            $_SESSION['error'] = "Please complete the registration form";
            echo('<script>window.location = "login.php";</script>');
            exit;
            // header('Location: signup.php');

        }
        // Make sure the submitted registration values are not empty.
        if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['fname']) || empty($_POST['lname']) || empty($_POST['mobile_no'])) {
            // One or more values are empty.
            $_SESSION['error'] = "Please complete the registration form";
            echo('<script>window.location = "signup.php";</script>');
            exit;
            // header('Location: signup.php');
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
          $_SESSION['error'] = "Invalid e-mail address";
          echo('<script>window.location = "signup.php";</script>');
          exit;
          // header('Location: signup.php');
        }

        if (preg_match('/[A-Za-z]{2,}/', $_POST['fname']) == 0) {
            $_SESSION['error'] = "Invalid first name";
            echo('<script>window.location = "signup.php";</script>');
            exit;
            // header('Location: signup.php');
        }

        if (preg_match('/[A-Za-z]{2,}/', $_POST['lname']) == 0) {
            $_SESSION['error'] = "Invalid last name";
            echo('<script>window.location = "signup.php";</script>');
            exit;
            // header('Location: signup.php');
        }

        if (preg_match('/[0-9]{10}/', $_POST['mobile_no']) == 0) {
            $_SESSION['error'] = "Invalid mobile number";
            echo('<script>window.location = "signup.php";</script>');
            exit;
            // header('Location: signup.php');
        }

        if (preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/', $_POST['password']) == 0) {
            $_SESSION['error'] = "Password must have at least 8 characters, 1 uppercase, 1 lowercase and 1 number and special character";
            echo('<script>window.location = "signup.php";</script>');
            exit;
            // header('Location: signup.php');
        }
        
        if ($_POST['password'] != $_POST['password-verify']) {
            $_SESSION['error'] = "Password fields do not match";
            echo('<script>window.location = "signup.php";</script>');
            exit;
            // header('Location: signup.php');
        }
        
        $secret = '6Lfv_cEcAAAAAKAB_TEZdFFYrPqlUWMKy4dH25mr';
        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $remoteIp = getClientIP();
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->setExpectedHostname('crypto-honeypot.forenzythreatlabs.com')->verify($gRecaptchaResponse, $remoteIp);

        if ($resp->isSuccess()) {
          // Verified!
        } else {
          $errors = $resp->getErrorCodes();
          // echo $errors;
          $_SESSION['error'] = "Please complete the captcha!";
          echo('<script>window.location = "signup.php";</script>');
          exit;
        }
        if ($stmt = $con->prepare('SELECT userid, password FROM userMaster WHERE mobile = ?')) {
          $stmt->bind_param('s', $_POST['mobile_no']);
            $stmt->execute();
            $stmt->store_result();
            // Store the result so we can check if the account exists in the database.
            if ($stmt->num_rows > 0) {
                // email already exists
                // echo 'Email exists, please choose another!';
                $_SESSION['error'] = "The provided phone number is already in use!";
                echo('<script>window.location = "login.php";</script>');
                exit;
            }
        }
        if ($stmt = $con->prepare('SELECT userid, password FROM userMaster WHERE email_id = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
            $stmt->bind_param('s', $_POST['email']);
            $stmt->execute();
            $stmt->store_result();
            // Store the result so we can check if the account exists in the database.
            if ($stmt->num_rows > 0) {
                // email already exists
                // echo 'Email exists, please choose another!';
                $_SESSION['error'] = "Account already exists!";
                echo('<script>window.location = "login.php";</script>');
                exit;
            } else {
                if ($stmt = $con->prepare('INSERT INTO userMaster (first_name, last_name, email_id, country, mobile, password, sign_up_date, recovery_code, init_account_balance, remaining_balance, lastLogin, lastLogin_http_user_agent, timezone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')) {
                    $country = getClientCountry($remoteIp);
                    $timezone = getClientTimezone($remoteIp);
                    // $mobile = '+0000000001';
                    $recovery_code = 'RECOVER SOME CODE ACCOUNT CODE ABOUT TWELVE WORDS SHOULD BE ENOUGH RIGHT';
                    $init_account_balance = 100000.00;
                    $remaining_balance = 100000.00;
                    $date = date('Y/m/d H:i:s');
                    $password_sha256 = hash('sha256', $_POST['password']);
                    // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
                    // $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt->bind_param('ssssssssiisss', $_POST['fname'], $_POST['lname'], $_POST['email'], $country, $_POST['mobile_no'], $password_sha256, $date, $recovery_code, $init_account_balance, $remaining_balance, $date, $_SERVER['HTTP_USER_AGENT'], $timezone);
                    if(!$stmt->execute()){
                      $_SESSION['error'] = "An error occurred please try again!";
                      echo('<script>window.location = "login.php";</script>');
                      exit;
                    } else {
                      $user_id = $stmt->insert_id;
                      if ($stmt_3 = $con->prepare('INSERT INTO logMaster (userid, loginDatetime, loginIPv4, loginIPv6, login_location, login_http_user_agent) VALUES (?, ?, ?, ?, ?, ?)')) {
                        $ip = getClientIP();
                        $location = getClientLocation($ip);
                        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                          $ipv6 = '0:0:0:0:0:0:0:0';
                          $stmt_3->bind_param('isssss', $user_id, $date, $ip, $ipv6, $location, $_SERVER['HTTP_USER_AGENT']);
                        } else {
                          $ipv4 = '0.0.0.0';
                          $stmt_3->bind_param('isssss', $user_id, $date, $ipv4, $ip, $location, $_SERVER['HTTP_USER_AGENT']);
                        }
                        if (!$stmt_3->execute()){
                          $_SESSION['error'] = "Please try again later!";
                          echo('<script>window.location = "signup.php";</script>');
                          exit;
                        } else {
                          // header('Location: dashboard.php');
                          $visitor_gen_time = $_SESSION['visitor_gen_time'];
                          session_regenerate_id();
                          // $_SESSION['loggedin'] = TRUE;
                          $_SESSION['email'] = $_POST['email'];
                          $_SESSION['id'] = $user_id;
                          $_SESSION['ipaddress'] = $_SERVER['REMOTE_ADDR'];
                          $_SESSION['useragent'] = $_SERVER['HTTP_USER_AGENT'];
                          $_SESSION['lastaccess'] = time();
                          $_SESSION['visitor_gen_time'] = $visitor_gen_time;
                          if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
                            setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
                          }
                          if ($_COOKIE['fnz_cookie_val'] == 'no') {
                            setcookie('email', md5($_SESSION['email']), time() + (86400 * 30), "/");
                          } else if ($_COOKIE['fnz_cookie_val'] == 'low') {
                            setcookie('email', base64_encode($_SESSION['email']), time() + (86400 * 30), "/");
                          } else {
                            setcookie('email', $_SESSION['email'], time() + (86400 * 30), "/");
                          }
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
                          $mail->addAddress($_SESSION['email']);
                          $mail->addCustomHeader('X-SES-CONFIGURATION-SET','X-FNZ-THREATLABS-HDR');
                          $mail->Subject  =  'Welcome to Crypo-Honeypot!!';
                          $mail->IsHTML(true);
                          $mail->Body    = 'Welcome to our portal! Thank you for choosing crypto honeypot as your cryptocurrency trading platform. You can trade among five different asset classes from one convenient account. Please verify your email and KYC to gain full access to the platform.';
                          $mail->Send();
                          $_SESSION['isVerified'] = 0;
                          header('Location: verify-email.php');
                          // echo('<script>window.location = "verify-email.php";</script>');
                          exit;
                        }
                      }
                      // echo 'You have successfully registered, you can now login!';
                      
                    }
                } else {
                    // Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
                    $_SESSION['error'] = "Please try again later!";
                    echo('<script>window.location = "signup.php";</script>');
                    exit;
                }
            }
            $stmt->close();
        } else {
            // Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
            // echo 'Could not prepare statement!';
            $_SESSION['error'] = "Please try again later!";
            echo('<script>window.location = "signup.php";</script>');
            exit;
        }
        $con->close();
    }
    if (isset($_SESSION['error'])) {
      $error = '<p id="password-message" style="font-size:75% ; color: #f00;">'.$_SESSION['error'].'</p>';
      unset($_SESSION['error']);
    } else {
      $error = '';
    }
    echo '<!DOCTYPE html>
        <html lang="en">
        
        <head>
          <meta charset="UTF-8">
          <title>Signup</title>

          <!-- Favicons -->
          <link href="./assets/img/crypto.png" rel="icon">
          <link href="./assets/img/crypto.png" rel="apple-touch-icon">
        
          <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
          <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
          <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
          
          <link rel="stylesheet" href="assets/css/signup.css">
          <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
          <script src="https://www.google.com/recaptcha/api.js"></script>
          <script src="assets/js/validator.js"></script>
        </head>
        
        <body>
          <div class="center">
            <div class="container">
              <div id="carouselExampleCaptions" class="text_login carousel slide carousel-fade" data-bs-ride="carousel">
                <div class="carousel-indicators">
                  <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                  <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
                  <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner">
                  <div class="carousel-item active">
                    <img src="assets/img/CityGrid.jpg" class="" alt="...">
                    <div class="carousel-caption d-none d-md-block">
                      <h2 style="font-size: 300%; font-weight: 500;">The Easiest Way To Invest</h2>
                    </div>
                  </div>
                  <div class="carousel-item">
                    <img src="assets/img/peakpx.jpg" class="" alt="...">
                    <div class="carousel-caption d-none d-md-block">
                      <h2 style="font-size: 300%; font-weight: 500;">Trade among five different asset classes from one convenient account</h2>
                    </div>
                  </div>
                  <div class="carousel-item">
                    <img src="assets/img/peakpx(1).jpg" class="" alt="...">
                    <div class="carousel-caption d-none d-md-block">
                      <h2 style="font-size: 300%; font-weight: 500;">You can choose from cryptos, metals, equities, currencies and more.</h2>
                    </div>
                  </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Next</span>
                </button>
              </div>
                
                
                
                <!-- <span>
                  <h2 style="font-size:200%;">THE EASIEST WAY TO INVEST</h2><br>
                  <p style="font-size:150%;">trade between five different asset classes from one convient account.
                    you can choose from cryptos,metala, equities, currenciesand more. More than 100 assets are now less than a
                    minute away.
                </span> -->
              
        
        
              <div class="login-content">
              <form id="reg-form" action="signup.php" method="POST" onsubmit="return signupValidator()">
              <h2 class="title">Your crypto journey begins here</h2>
                  <div class="input-div one">
                    <div class="inc">
                      <i class="usr"></i>
                    </div>
                    <div class="div">
                      <h5>Email</h5>
                      <input type="email" class="input" name="email" id="email">
                    </div>
                  </div>
                  <div class="row">
                    <div class="input-div two">
                      <div class="inc">
                        <i class="name"></i>
                      </div>
                      <div class="div">
                        <h5>First Name</h5>
                        <input type="text" class="input" name="fname" id="fname">
                      </div>
                    </div>
                    <div class="input-div three">
                      <div class="inc">
                        <i class="name"></i>
                      </div>
                      <div class="div">
                        <h5>Last Name</h5>
                        <input type="text" class="input" name="lname" id="lname">
                      </div>
                    </div>
                  </div>
                  <div class="input-div four">
                    <div class="inc">
                      <i class="conf-pass"></i>
                    </div>
                    <div class="div">
                      <h5>Mobile</h5>
                      <input type="text" class="input" name="mobile_no" id="mobile">
                    </div>
                  </div>
                  <div class="input-div pass">
                    <div class="inc">
                      <i class="pass"></i>
                    </div>
                    <div class="div">
                      <h5>Password</h5>
                      <input type="password" class="input" name="password" id="password">
                    </div>
                  </div>
                  <p id="password-message" style="font-size:75% ; color:#999"> Must have at least 8 characters, 1 uppercase, 1 lowercase and 1 number
                    and special character</p>
                  <div class="input-div conf-pass">
                    <div class="inc">
                      <i class="conf-pass"></i>
                    </div>
                    <div class="div">
                      <h5>Confirm Password</h5>
                      <input type="password" class="input" name="password-verify" id="password-verify">
                    </div>
                  </div>
                  '.$error.'
                  <div style="display: flex; justify-content: center;" class="div g-recaptcha" data-sitekey="6Lfv_cEcAAAAAHjezfbopIsXDtuGNMHzFTO1mbIE"></div>
                  <button id="reg-btn" class="btn" value="Signup" name="signup" type="submit">Signup</button>
                  Already a member?&nbsp<a href="login.php" class="sign-up">login here</a>
                </form>
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
            function checkcl() {
              let parent = this.parentNode.parentNode;
              if (this.value == "") {
                parent.classList.remove("focus");
              } else {
                parent.classList.add("focus");
              }
            }
            function remcl() {
              let parent = this.parentNode.parentNode;
              if (this.value == "") {
                parent.classList.remove("focus");
              }
            }
            inputs.forEach((input) => {
              input.addEventListener("focus", addcl);
              input.addEventListener("change", checkcl);
              input.addEventListener("blur", remcl);
            });
            
            const fpPromise = import(\'https://openfpcdn.io/fingerprintjs/v3\')
              .then(FingerprintJS => FingerprintJS.load())
          
            // Get the visitor identifier when you need it.
            fpPromise
              .then(fp => fp.get())
              .then(result => {
                // This is the visitor identifier:
                const visitorId = result.visitorId
                $.get(`visitor-id.php?id=${visitorId}`, function(data, status){
                  console.log("Data: " + data + "\nStatus: " + status);
                });
                // console.log(visitorId)
              })
          </script>
        </body>
        
        </html>
        ';
}
?>