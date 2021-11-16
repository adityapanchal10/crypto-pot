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

function generate_sso_token() {
    $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < 100; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
 
    return $random_string;
}

session_start();

if (isset($_SESSION['email'])) {
	header('Location: dashboard.php');
	exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['email'])) {
        if (empty($_POST['email'])) {
            echo('empty');
        } else {
            $email = $_POST['email'];
            if ($stmt = $con->prepare('SELECT userid, first_name  FROM userMaster WHERE email_id = ?')) {

                $stmt->bind_param('s', $email);
                $stmt->execute();
                // Store the result so we can check if the account exists in the database.
                $stmt->store_result();
        
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($userid, $first_name);
                    $stmt->fetch();
                    $code = generate_sso_token();
                    if ($stmt = $con->prepare('INSERT INTO ssoMaster (userid, email_id, sso_token, sso_token_expiry) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))')) {
    
                        $stmt->bind_param('iss', $userid, $email, $code);
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
                            $mail->addAddress($email);
                            $mail->addCustomHeader('X-SES-CONFIGURATION-SET','X-FNZ-THREATLABS-HDR');
                            $mail->Subject  =  'SSO Link';
                            $mail->IsHTML(true);
                            $mail->Body = 'Your SSO link is which is valid for the next 10 minutes: https://crypto-honeypot.forenzythreatlabs.com/staging/sso.php?email='.$email.'&token='.$code;
                            if($mail->Send()) {
                                echo 'success';
                            }
                            else {
                                exit($mail->ErrorInfo);
                                header('Location: login.php');
                            }
    
                        } else {
                            if ($stmt->errno == 1062) {
                                if ($stmt = $con->prepare('SELECT sso_token, sso_token_expiry FROM ssoMaster WHERE userid = ?')) {
                                    $stmt->bind_param('i', $userid);
                                    if($stmt->execute()){
                                        $stmt->store_result();
                                        if ($stmt->num_rows > 0) {
                                            $stmt->bind_result($sso_token, $sso_token_expiry);
                                            $stmt->fetch();
                                            if ($sso_token_expiry > date('Y-m-d H:i:s')) {
                                                echo('exists');
                                            } else {
                                                if ($stmt = $con->prepare('UPDATE ssoMaster SET sso_token = ?, sso_token_expiry = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE userid = ?')) {
                                                    $stmt->bind_param('si', $code, $userid);
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
                                                        $mail->addAddress($email);
                                                        $mail->addCustomHeader('X-SES-CONFIGURATION-SET','X-FNZ-THREATLABS-HDR');
                                                        $mail->Subject  =  'SSO Link';
                                                        $mail->IsHTML(true);
                                                        $mail->Body = 'Your SSO link is which is valid for the next 10 minutes: https://crypto-honeypot.forenzythreatlabs.com/staging/sso.php?email='.$email.'&token='.$code;
                                                        if($mail->Send()) {
                                                            echo 'success';
                                                        }
                                                        else {
                                                            // echo "Mail Error - >".$mail->ErrorInfo;
                                                            exit($mail->ErrorInfo);
                                                            header('Location: login.php');
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['email']) && isset($_GET['token'])) {
        $email = $_GET['email'];
        $token = $_GET['token'];
        if ($stmt = $con->prepare('SELECT userid FROM userMaster WHERE email_id = ?')) {

            $stmt->bind_param('s', $email);
            $stmt->execute();
            // Store the result so we can check if the account exists in the database.
            $stmt->store_result();
    
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userid);
                $stmt->fetch();
                
                if ($stmt = $con->prepare('SELECT sso_token, sso_token_expiry FROM ssoMaster WHERE userid = ?')) {

                    $stmt->bind_param('i', $userid);
                    if($stmt->execute()){
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($sso_token, $sso_token_expiry);
                            $stmt->fetch();
                            if ($sso_token == $token) {
                                if ($sso_token_expiry > date('Y-m-d H:i:s')) {
                                    if ($stmt_2 = $con->prepare('UPDATE userMaster SET lastLogin = ?, lastLogin_http_user_agent = ? WHERE email_id = ?')) {
                                        $lastLogin = date('Y/m/d H:i:s');
                                        $stmt_2->bind_param('sss', $lastLogin, $_SERVER['HTTP_USER_AGENT'], $email);
                                        if(!$stmt_2->execute()){
                                          echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                                          exit;
                                        } else {
                                          if ($stmt_3 = $con->prepare('INSERT INTO logMaster (userid, loginDatetime, loginIPv4, loginIPv6, login_http_user_agent) VALUES (?, ?, ?, ?, ?)')) {
                                            $ip = getClientIP();
                                            if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                                              $ipv6 = '0:0:0:0:0:0:0:0';
                                              $stmt_3->bind_param('issss', $userid, $lastLogin, $ip, $ipv6, $_SERVER['HTTP_USER_AGENT']);
                                            } else {
                                              $ipv4 = '0.0.0.0';
                                              $stmt_3->bind_param('issss', $userid, $lastLogin, $ipv4, $ip, $_SERVER['HTTP_USER_AGENT']);
                                            }
                                            if (!$stmt_3->execute()){
                                              echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                                              exit;
                                            } else {
                                              // header('Location: dashboard.php');
                                              session_regenerate_id();
                                              // $_SESSION['loggedin'] = TRUE;
                                                $_SESSION['id'] = $userid;
                                                $_SESSION['email'] = $email;
                                                header('Location: dashboard.php');
                                            }
                                          }
                                        }
                                      } else {
                                        echo('<script>alert("Please try again"); window.location = "login.php";</script>');
                                        exit;
                                      }
                                } else {
                                    echo('<script>alert("SSO link has expired"); window.location = "login.php";</script>');
                                }
                            } else {
                                echo('<script>alert("Invalid SSO link"); window.location = "login.php";</script>');
                            }
                        } else {
                            echo('<script>alert("Invalid SSO link"); window.location = "login.php";</script>');
                        }
                    }
                }
            } else {
                echo('<script>alert("Email not found"); window.location = "login.php";</script>');
            }
        }
    } else {
        header('Location: login.php');
        exit;
    }
}
?>