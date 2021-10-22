<?php

session_start();

include "db_connect.php";

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
} else {
    if ($stmt = $con->prepare('SELECT userid FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid);
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
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
    crossorigin="anonymous"></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Load c3.css -->
  <link href="assets/css/c3.css" rel="stylesheet">

  <!-- Load d3.js and c3.js -->
  
  <script src="https://d3js.org/d3.v5.min.js" charset="utf-8"></script>
  <script src="assets/js/c3.js"></script>

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css?family=Poppins:300,300i,400,400i,600,600i,700,700i|Satisfy|Comic+Neue:300,300i,400,400i,700,700i"
    rel="stylesheet">

  <link href="https://fonts.googleapis.com/css2?family=Exo&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/login-history.css" rel="stylesheet">
</head>

<body class="dashbody">

  <!-- ======= Top Bar ======= 
  <section id="topbar" class="d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-center justify-content-lg-start">

    </div>
  </section> -->

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top d-flex align-items-center">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

      <div class="logo me-auto">
        <h1><a href="index.html">Crypto</a></h1>
        <!-- Uncomment below if you prefer to use an image logo -->
        <!-- <a href="index.html"><img src="assets/img/logo.png" alt="" class="img-fluid"></a>-->
      </div>

      <nav id="navbar" class="navbar order-last order-lg-0">
        <ul>
          <li><a class="nav-link scrollto" href="#">Markets</a></li>
          <li><a class="nav-link scrollto" href="#">Transactions</a></li>
          <li><a class="nav-link scrollto" href="#">Wallet</a></li>
          <!-- 
            <li class="dropdown"><a href="#"><span>Drop Down</span> <i class="bi bi-chevron-down"></i></a>
              <ul>
                <li><a href="#">Drop Down 1</a></li>
                <li class="dropdown"><a href="#"><span>Deep Drop Down</span> <i class="bi bi-chevron-right"></i></a>
                  <ul>
                    <li><a href="#">Deep Drop Down 1</a></li>
                    <li><a href="#">Deep Drop Down 2</a></li>
                    <li><a href="#">Deep Drop Down 3</a></li>
                    <li><a href="#">Deep Drop Down 4</a></li>
                    <li><a href="#">Deep Drop Down 5</a></li>
                  </ul>
                </li>
                <li><a href="#">Drop Down 2</a></li>
                <li><a href="#">Drop Down 3</a></li>
                <li><a href="#">Drop Down 4</a></li>
              </ul>
            </li>
            -->
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

      <a href="logout.php" class="login-btn scrollto">Logout</a>

    </div>
  </header>
  <!-- End Header -->
  <main id="main">

    <section id="dash" class="dash">
      <div class="container-fluid">
        <div class="row d-flex align-items-center justify-content-between animate__animated animate__fadeInUp">
            <div class="col-lg-8">
          '.$table.'
            </div>
        </div>
      </div>
    </section>

  </main>
  <!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer">
    <div class="container">
      <h3>Crypto</h3>
      <p>A more versatile home for your financial life</p>
      <div class="social-links">
        <a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
        <a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
        <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
        <a href="#" class="google-plus"><i class="bx bxl-skype"></i></a>
        <a href="#" class="linkedin"><i class="bx bxl-linkedin"></i></a>
      </div>
      <div class="copyright">
        &copy; Copyright <strong><span>Crypto</span></strong>. All Rights Reserved
      </div>
    </div>
  </footer>
  <!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>


</body>

</html>';
            }
        }
    }
}
?>