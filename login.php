<?php
//$ip = $_SERVER['REMOTE_ADDR'];
//$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
//echo $details->city; // -> "Mountain View"
session_start();

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
  return $location;
}

if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
  setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
}

if (!isset($_SESSION['email'])) {
  if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['login'])) {          
    if (!isset($_POST['email'], $_POST['password'], $_POST['g-recaptcha-response']) ) {
      echo('<script>alert("Please fill both the email and password fields!"); window.location = "login.php";</script>');
      exit;
    }

    if (empty($_POST['email']) || empty($_POST['password'])) {
      echo('<script>alert("Please fill both the email and password fields!"); window.location = "login.php";</script>');
      exit;
    }
    if (empty($_POST['g-recaptcha-response'])) {
      echo('<script>alert("Please complete the captcha!"); window.location = "login.php";</script>');
      exit;
    }
    $secret = '6Lfv_cEcAAAAAKAB_TEZdFFYrPqlUWMKy4dH25mr';
    $gRecaptchaResponse = $_POST['g-recaptcha-response'];
    $remoteIp = getClientIP();
    $recaptcha = new \ReCaptcha\ReCaptcha($secret);
    $resp = $recaptcha->setExpectedHostname('crypto-honeypot.forenzythreatlabs.com')->verify($gRecaptchaResponse, $remoteIp);
    $email = $_POST['email'];

    if ($resp->isSuccess()) {
      // Verified!
    } else {
      $errors = $resp->getErrorCodes();
      echo('<script>alert("Please complete the captcha!"); window.location = "login.php";</script>');
      exit;
    }
    if ($_COOKIE['fnz_cookie_val'] == 'no') {
      if ($stmt = $con->prepare('SELECT userid, password FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
          if ($_COOKIE['fnz_cookie_val'] == 'no') {
            echo('<script>alert('.$stmt->error.'); window.location = "login.php";</script>');
            exit;
          }
          echo('<script>alert("Error in executing query!"); window.location = "login.php";</script>');
          exit;
        }
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();
    
        if ($stmt->num_rows > 0) {
          $stmt->bind_result($user_id, $password);
          $stmt->fetch();
          // Account exists, now we verify the password.
          // Note: remember to use password_hash in your registration file to store the hashed passwords.
          $password_hash = hash('sha256', $_POST['password']);
          if ($password_hash == $password) {
            // Verification success! User has logged-in!
            // Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
            
            // echo 'Welcome ' . $_SESSION['name'] . '!';
            if ($stmt_2 = $con->prepare('UPDATE userMaster SET lastLogin = ?, lastLogin_http_user_agent = ? WHERE email_id = ?')) {
              $lastLogin = date('Y/m/d H:i:s');
              $stmt_2->bind_param('sss', $lastLogin, $_SERVER['HTTP_USER_AGENT'], $email);
              if(!$stmt_2->execute()){
                if ($_COOKIE['fnz_cookie_val'] == 'no') {
                  echo('<script>alert('.$stmt_2->error.'); window.location = "login.php";</script>');
                  exit;
                }
                echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                exit;
              } else {
                if ($stmt_3 = $con->prepare('INSERT INTO logMaster (userid, loginDatetime, loginIPv4, loginIPv6, login_location, login_http_user_agent) VALUES (?, ?, ?, ?, ?, ?)')) {
                  $ip = getClientIP();
                  $location = getClientLocation($ip);
                  if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ipv6 = '0:0:0:0:0:0:0:0';
                    $stmt_3->bind_param('isssss', $user_id, $lastLogin, $ip, $ipv6, $location, $_SERVER['HTTP_USER_AGENT']);
                  } else {
                    $ipv4 = '0.0.0.0';
                    $stmt_3->bind_param('issss', $user_id, $lastLogin, $ipv4, $ip, $location, $_SERVER['HTTP_USER_AGENT']);
                  }
                  if (!$stmt_3->execute()){
                    if ($_COOKIE['fnz_cookie_val'] == 'no') {
                      echo('<script>alert('.$stmt_3->error.'); window.location = "login.php";</script>');
                      exit;
                    }
                    echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                    exit;
                  } else {
                    // header('Location: dashboard.php');
                    session_regenerate_id();
                    // $_SESSION['loggedin'] = TRUE;
                    $_SESSION['email'] = $email;
                    $_SESSION['id'] = $user_id;
                    $_SESSION['ipaddress'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['useragent'] = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['lastaccess'] = time();
                    if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
                      setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
                    }                  
                    setcookie('email', $email, time() + (86400 * 30), "/");
                    if ($_COOKIE['fnz_cookie_val'] == 'low') {
                      setcookie('email', base64_encode($email), time() + (86400 * 30), "/");
                    }
                    echo('<script>window.location = "dashboard.php";</script>');
                  }
                } else {
                  if ($_COOKIE['fnz_cookie_val'] == 'no') {
                    echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                    exit;
                  }
                  echo('<script>alert('.$stmt_3->error.'); window.location = "login.php";</script>');
                  exit;
                }
              }
            } else {
              if ($_COOKIE['fnz_cookie_val'] == 'no') {
                echo('<script>alert("An error occured. Please try again"); window.location = "login.php";</script>');
                exit;
              }
              echo('<script>alert('.$stmt_2->error.'); window.location = "login.php";</script>');
              exit;
            }
          } else {
              // Incorrect password
              if ($_COOKIE['fnz_cookie_val'] == 'no') {
                echo('<script>alert("Incorrect email and/or password"); window.location = "login.php";</script>');
                exit;
              }
              echo('<script>alert("The password you\'ve entered is incorrect. Please try again."); window.location = "login.php";</script>');
              exit;
          }
        } else {
            // Incorrect email
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
              echo('<script>alert("Incorrect email and/or password"); window.location = "login.php";</script>');
              exit;
            }
            echo('<script>alert("The email you\'ve entered does not match our records. Please try again."); window.location = "login.php";</script>');
            exit;
        }
  
          //$stmt->close();
      } else {
        if ($_COOKIE['fnz_cookie_val'] == 'no') {
          echo('<script>alert("An error occured. Please try again"); window.location = "login.php";</script>');
          exit;
        }
        echo('<script>alert('.$stmt->error.'); window.location = "login.php";</script>');
        exit;
      }
    } else {
      $email = $_POST['email'];
      $query  = "SELECT userid, password FROM userMaster WHERE email_id = '$email';";
			$result = mysqli_query($con, $query) or die('<script>alert("' . mysqli_error($con) . '");window.location=login.php</script>');

			// Get results
			while( $row = mysqli_fetch_assoc( $result ) ) {
				// Display values
				$user_id = $row["userid"];
				$password  = $row["password"];

				// Feedback for end user
				$password_hash = hash('sha256', $_POST['password']);
        if ($password_hash == $password) {
          // Verification success! User has logged-in!
          // Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
          
          // echo 'Welcome ' . $_SESSION['name'] . '!';
          if ($stmt_2 = $con->prepare('UPDATE userMaster SET lastLogin = ?, lastLogin_http_user_agent = ? WHERE email_id = ?')) {
            $lastLogin = date('Y/m/d H:i:s');
            if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
              setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
            }
            if ($_COOKIE['fnz_cookie_val'] == 'no' || !isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
                $user_agent = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
            } else {
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
            }
            $stmt_2->bind_param('sss', $lastLogin, $user_agent, $email);
            if(!$stmt_2->execute()){
              if ($_COOKIE['fnz_cookie_val'] == 'no') {
                echo('<script>alert('.$stmt_2->error.'); window.location = "login.php";</script>');
                exit;
              }
              echo('<script>alert("Please try again"); window.location = "login.php";</script>');
              exit;
            } else {
              if ($stmt_3 = $con->prepare('INSERT INTO logMaster (userid, loginDatetime, loginIPv4, loginIPv6, login_location, login_http_user_agent) VALUES (?, ?, ?, ?, ?, ?)')) {
                $ip = getClientIP();
                $location = getClientLocation($ip);
                if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                  $ipv6 = '0:0:0:0:0:0:0:0';
                  $stmt_3->bind_param('isssss', $user_id, $lastLogin, $ip, $ipv6, $location, $_SERVER['HTTP_USER_AGENT']);
                } else {
                  $ipv4 = '0.0.0.0';
                  $stmt_3->bind_param('issss', $user_id, $lastLogin, $ipv4, $ip, $location, $_SERVER['HTTP_USER_AGENT']);
                }
                if (!$stmt_3->execute()){
                  if ($_COOKIE['fnz_cookie_val'] == 'no') {
                    echo('<script>alert('.$stmt_3->error.'); window.location = "login.php";</script>');
                    exit;
                  }
                  echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                  exit;
                } else {
                  // header('Location: dashboard.php');
                  session_regenerate_id();
                  // $_SESSION['loggedin'] = TRUE;
                  $_SESSION['email'] = $email;
                  $_SESSION['id'] = $user_id;
                  $_SESSION['ipaddress'] = $_SERVER['REMOTE_ADDR'];
                  $_SESSION['useragent'] = $_SERVER['HTTP_USER_AGENT'];
                  $_SESSION['lastaccess'] = time();
                  if (!isset($_COOKIE['fnz_cookie_val']) || $_COOKIE['fnz_cookie_val'] == '') {
                    setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
                  }                  
                  setcookie('email', $email, time() + (86400 * 30), "/");
                  if ($_COOKIE['fnz_cookie_val'] == 'low') {
                    setcookie('email', base64_encode($email), time() + (86400 * 30), "/");
                  }
                  
                  echo('<script>window.location = "dashboard.php";</script>');
                }
              } else {
                if ($_COOKIE['fnz_cookie_val'] == 'no') {
                  echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                  exit;
                }
                echo('<script>alert('.$stmt_3->error.'); window.location = "login.php";</script>');
                exit;
              }
            }
          } else {
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
              echo('<script>alert("An error occured. Please try again"); window.location = "login.php";</script>');
              exit;
            }
            echo('<script>alert('.$stmt_2->error.'); window.location = "login.php";</script>');
            exit;
          }
        } else {
            // Incorrect password
            if ($_COOKIE['fnz_cookie_val'] == 'no') {
              echo('<script>alert("Incorrect email and/or password"); window.location = "login.php";</script>');
              exit;
            }
            echo('<script>alert("The password you\'ve entered is incorrect. Please try again."); window.location = "login.php";</script>');
            exit;
        }
			}
    }

    
  
  } else {
        echo '<!DOCTYPE html>
        <html lang="en">
        
        <head>
          <meta charset="UTF-8">
          <title>Login</title>
        
          <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
          <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
          <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
        
          <link rel="stylesheet" href="assets/css/signup.css">
        
          <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
          <script src="https://www.google.com/recaptcha/api.js"></script>
          <script>
            function onSubmit(token) {
              $.post("login.php", $("#login-form").serialize() + "&login", function(data){
                // alert(data);
                console.log(data);
                jQuery.globalEval(data);
              });
            }
            /* $("#login-btn").click(function(){
              e.preventDefault();
              grecaptcha.ready(function() {
                grecaptcha.execute("6Le7_sEcAAAAAOKBLU84MM8cYoN9DvOEexSMFYSm", {action: "submit"}).then(function(token) {
                  
                });
              });
            });
            function onSubmit(token) {
              
              // document.getElementById("login-form").submit();
              // $("#login-form").submit();
              $.post("login.php", $("#login-form").serialize(), function(data) {
                alert(data);
                jQuery.globalEval(data);
              });
            }*/
          </script>
        </head>
        
        <body>
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
                <form id="login-form" class="loginform l1" action="login.php" method="POST">
                  <img src="">
                  <h2 class="title">Welcome</h2>
                  <div class="input-div one">
                    <div class="inc">
                      <i class="usr"></i>
                    </div>
                    <div class="div">
                      <h5>Email</h5>
                      <input type="email" class="input" name="email">
                    </div>
                  </div>
                  <div class="input-div pass">
                    <div class="inc">
                      <i class="pass"></i>
                    </div>
                    <div class="div">
                      <h5>Password</h5>
                      <input type="password" class="input" name="password">
                    </div>
                  </div>
                  <div style="display: flex; justify-content: center;" class="div g-recaptcha" data-sitekey="6Lfv_cEcAAAAAHjezfbopIsXDtuGNMHzFTO1mbIE"></div>
                  <a href="#" id="forgotPwd" class="forgot">Forgot Password?</a>
                  <button id="login-btn" class="btn" value="Login" name="login" type="submit">LOGIN</button>
                  <button type="button" class="btn" id="tol5">Login with SSO</button>
                  Not a member?&nbsp <a href="signup.php" class="sign-up">signup now</a>
                </form>
        
                <form class="loginform l2 hidden" action="forgot-password.php" method="GET">
                  <img src="">
                  <h2 class="title">Forgot your password, no worries</h2>
                  <h3 class="title">Enter your email and we\'ll send a code to you.</h3>
                  <div class="input-div one">
                    <div class="inc">
                      <i class="usr"></i>
                    </div>
                    <div class="div">
                      <h5>Email</h5>
                      <input type="email" class="input" name="email">
                    </div>
                  </div>
        
                  <a href="#" id="backTol1" class="forgot">Go back</a>
                  <input type="submit" id="submit" class="btn" value="submit">
                </form>
        
                <form class="loginform l3 hidden" action="" method="">
                  <img src="">
                  <h2 class="title">Please enter the code you recieved here.</h2>
                  <div class="input-div one">
                    <div class="inc">
                      <i class="usr"></i>
                    </div>
                    <div class="div">
                      <h5>Code</h5>
                      <input type="text" class="input" name="text">
                    </div>
                  </div>
                  <a href="#" id="backTol2" class="forgot">Go back</a>
                  <input id="tol4" class="btn" value="Next">
                </form>
        
                <form class="loginform l4 hidden" action="" method="">
                  <img src="">
                  <h2 class="title">Change your password here.</h2>
                  <div class="input-div pass">
                    <div class="inc">
                      <i class="pass"></i>
                    </div>
                    <div class="div">
                      <h5>New Password</h5>
                      <input type="password" class="input" name="password">
                    </div>
                  </div>
                  <div class="input-div pass">
                    <div class="inc">
                      <i class="pass"></i>
                    </div>
                    <div class="div">
                      <h5>Confirm Password</h5>
                      <input type="password" class="input" name="password">
                    </div>
                  </div>
                  <a href="#" id="backTol3" class="forgot">Go back</a>
                  <input id="passchanged" type="submit" class="btn" value="Change Password">
                </form>

                <form class="loginform l5 hidden" action="" method="">
                  <img src="">
                  <h3 class="title">Enter your email and we\'ll send a single sign-on link to you.</h3>
                  <div class="input-div one">
                    <div class="inc">
                      <i class="usr"></i>
                    </div>
                    <div class="div">
                      <h5>Email</h5>
                      <input type="email" class="input" name="email">
                    </div>
                  </div>
        
                  <a href="#" id="backTol1Froml5" class="forgot">Go back</a>
                  <button type="button" id="sso-btn" class="btn" value="SSO" onclick="sso_generate()">Login with SSO</button>
                </form>
        
                <div class="toast t1" role="alert" aria-live="assertive" aria-atomic="true"
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

                <div class="toast t2" role="alert" aria-live="assertive" aria-atomic="true"
                  style="position: absolute; top: 0; right: 0;">
                  <div class="toast-header">
                  <strong class="me-auto">Single Sign-On Link sent!</strong>
                  <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close">
                    </button>
                  </div>
                  <div class="toast-body">
                    Check your email for the login link.
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
        
              $("#backTol2").click(function () {
                $(".l3").removeClass("show");
                $(".l3").addClass("fadeout");
                $(".l3").addClass("hidden");
                $(".l2").removeClass("hidden");
                $(".l2").addClass("fadeout");
                $(".l2").addClass("show");
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
            });

            $("#tol5").click(function () {
              $(".l1").removeClass("show");
              $(".l1").addClass("fadeout");
              $(".l1").addClass("hidden");
              $(".l5").removeClass("hidden");
              $(".l5").addClass("fadeout");
              $(".l5").addClass("show");
            });

            $("#backTol1Froml5").click(function () {
              $(".l5").removeClass("show");
              $(".l5").addClass("fadeout");
              $(".l5").addClass("hidden");
              $(".l1").removeClass("hidden");
              $(".l1").addClass("fadeout");
              $(".l1").addClass("show");
            });

            function sso_generate() {
              $.ajax({
                url: "sso.php",
                type: "POST",
                data: $(".l5").serialize(),
                success: function (data) {
                  if (data.trim() == "success") {
                    $(".t2").toast();
                    $(".t2").toast("show");
                  }
                  else if (data.trim() == "empty") {
                    alert("Please enter your email.");
                  }
                  else if (data.trim() == "exists") {
                    alert("You have already requested a SSO link. Please check your email for the link.");
                  }
                },
              });
            }

          </script>
        </body>
        
        </html>
        ';
    }
} else {
    header('Location: dashboard.php');
}

?>
