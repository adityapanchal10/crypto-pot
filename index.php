<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();

include "db_connect.php";

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['email'])) {
	$login = '<a href="login.php" class="login-btn scrollto">Login</a>';
} else {
  $login = '<a href="dashboard.php" class="login-btn scrollto">Dashboard</a>';
}
echo '
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Crypto - Index</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css?family=Poppins:300,300i,400,400i,600,600i,700,700i|Satisfy|Comic+Neue:300,300i,400,400i,700,700i"
    rel="stylesheet">

  <link href="https://fonts.googleapis.com/css2?family=Metrophobic&display=swap" rel="stylesheet">

  <link href="https://fonts.googleapis.com/css2?family=Metrophobic&family=Spectral&display=swap" rel="stylesheet">

  <link href="https://fonts.googleapis.com/css2?family=Exo&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

</head>

<body>

  <!-- ======= Top Bar ======= 
  <section id="topbar" class="d-flex align-items-center fixed-top topbar-transparent">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-center justify-content-lg-start">
      <i class="bi bi-phone d-flex align-items-center"><span>+1 5589 55488 55</span></i>
      <i class="bi bi-clock ms-4 d-none d-lg-flex align-items-center"><span>Mon-Sat: 11:00 AM - 23:00 PM</span></i>
    </div>
  </section> -->

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top d-flex align-items-center header-transparent">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

      <div class="logo me-auto">
        <h1><a href="index.html">Crypto</a></h1>
        <!-- Uncomment below if you prefer to use an image logo -->
        <!-- <a href="index.html"><img src="assets/img/logo.png" alt="" class="img-fluid"></a>-->
      </div>

      <nav id="navbar" class="navbar order-last order-lg-0">
        <ul>
          <li><a class="nav-link scrollto" href="#hero">Get Started</a></li>
          <li><a class="nav-link scrollto" href="#about">About</a></li>
          <li><a class="nav-link scrollto" href="#prices">Prices</a></li>
          <li><a class="nav-link scrollto" href="#crypto">Cryptocurrencies</a></li>
          <li><a class="nav-link scrollto" href="#wallet">Wallet</a></li>
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
          <li><a class="nav-link scrollto" href="#contact">Contact</a></li>
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

      '.$login.'

    </div>
  </header>
  <!-- End Header -->

  <!-- ======= Hero Section ======= -->
  <section id="hero">
    <div class="hero-container">
      <div id="heroCarousel" data-bs-interval="5000" class="carousel slide carousel-fade" data-bs-ride="carousel">

        <ol class="carousel-indicators" id="hero-carousel-indicators"></ol>

        <div class="carousel-inner" role="listbox">

          <!-- Slide 1 -->
          <div class="carousel-item active"
            style="background: url(assets/img/slide/slide-1.svg); background-repeat: no-repeat; background-size: cover;">
            <div class="carousel-container">
              <div class="carousel-content">
                <h2 class="animate__animated animate__fadeInDown"><span>Anything</span> To Anything</h2>
                <p class="animate__animated animate__fadeInUp">Trade between cryptocurrencies, equities and national
                  currencies in one single step.</p>
                <div>
                  <a href="signup.php" class="signup-btn animate__animated animate__fadeInUp scrollto">Signup Now</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Slide 2 -->
          <div class="carousel-item"
            style="background: url(assets/img/slide/slide-2.svg); background-repeat: no-repeat; background-size: cover;">
            <div class="carousel-container">
              <div class="carousel-content">
                <h2 class="animate__animated animate__fadeInDown"><span>Anything</span> To Anything</h2>
                <p class="animate__animated animate__fadeInUp">Trade between cryptocurrencies, equities and national
                  currencies in one single step.</p>
                <div>
                  <a href="signup.php" class="signup-btn animate__animated animate__fadeInUp scrollto">Signup Now</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Slide 3 -->
          <div class="carousel-item"
            style="background: url(assets/img/slide/slide-3.svg); background-repeat: no-repeat; background-size: cover;">
            <div class="carousel-container">
              <div class="carousel-content">
                <h2 class="animate__animated animate__fadeInDown"><span>Anything</span> To Anything</h2>
                <p class="animate__animated animate__fadeInUp">Trade between cryptocurrencies, equities and national
                  currencies in one single step.</p>
                <div>
                  <a href="signup.php" class="signup-btn animate__animated animate__fadeInUp scrollto">Signup Now</a>
                </div>
              </div>
            </div>
          </div>

        </div>

        <a class="carousel-control-prev" href="#heroCarousel" role="button" data-bs-slide="prev">
          <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
        </a>

        <a class="carousel-control-next" href="#heroCarousel" role="button" data-bs-slide="next">
          <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
        </a>

      </div>
    </div>
  </section>
  <!-- End Hero -->

  <main id="main">

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
      <div class="container-fluid">

        <div class="row">

          <div class="col-lg-5 align-items-stretch video-box" style=\'background-image: url("assets/img/about.png");\'>
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="venobox play-btn mb-4" data-vbtype="video"
              data-autoplay="true"></a>
          </div>

          <div class="col-lg-7 d-flex flex-column justify-content-center align-items-stretch">

            <div class="content">
              <h3>About our <strong>Crypto Exchange</strong></h3>
              <p>
                Simply the best place to buy digital currencies
              </p>
              <p class="fst-italic">
                Cheaper. Faster. Better.
              </p>
              <ul>
                <li><i class="bx bx-check-double"></i> One of a kind.</li>
                <li><i class="bx bx-check-double"></i> Vulnerablility free :)</li>
                <li><i class="bx bx-check-double"></i> Trusted & Transparent</li>
              </ul>
            </div>

          </div>

        </div>

      </div>
    </section>
    <!-- End About Section -->

    <!-- ======= Why Us Section ======= -->
    <section id="prices" class="why-us">
      <div class="container">

        <div class="section-title">
          <h2>Why choose <span>Our Crypto Exchange</span></h2>
          <p>A more versatile home for your financial life</p>
        </div>

        <div class="row">

          <div class="col-lg-3">
            <div class="box">
              <span>01</span>
              <h4>Anything to Anything</h4>
              <p>Unique, one-step trading capability, e.g., DASH to XRP, USD to NANO.</p>
            </div>
          </div>

          <div class="col-lg-3 mt-3 mt-lg-0">
            <div class="box">
              <span>02</span>
              <h4>Multi-Asset Platform</h4>
              <p>Cryptocurrencies, precious metals, U.S. equities, and national currencies.</p>
            </div>
          </div>

          <div class="col-lg-3 mt-3 mt-lg-0">
            <div class="box">
              <span>03</span>
              <h4>Cheap forex</h4>
              <p>Access some of the cheapest Forex rates on major currencies, including USD, EUR and GBP</p>
            </div>
          </div>

          <div class="col-lg-3 mt-3 mt-lg-0">
            <div class="box">
              <span>04</span>
              <h4>Blockchain integration</h4>
              <p>Withdraw funds to bank accounts in various countries, or to private wallets on crypto networks -
                instantly and
                fee-free.</p>
            </div>
          </div>

        </div>

      </div>
    </section>
    <!-- End Whu Us Section -->

    <!-- ======= Crypto Section ======= -->
    <section id="crypto" class="menu">
      <div class="container">

        <div class="section-title">
          <h2><span></span></h2>
        </div>

        <div class="row">

          <!-- <ul id="menu-flters">
              <li data-filter="*" class="filter-active">Show All</li>
              <li data-filter=".filter-starters">Starters</li>
              <li data-filter=".filter-salads">Salads</li>
              <li data-filter=".filter-specialty">Specialty</li>
            </ul> -->

          <div class="col-lg-3 mt-3 mt-lg-0" align-items="center">
            <div class="box">
              <h2 class="animate__animated animate__fadeInDown" align="center"><span>27</span></h2>
              <p class="animate__animated animate__fadeInUp" align="center">National Currencies</p>
            </div>
          </div>
          <div class="col-lg-3 mt-3 mt-lg-0">
            <div class="box">
              <h2 class="animate__animated animate__fadeInDown" align="center"><span>55</span></h2>
              <p class="animate__animated animate__fadeInUp" align="center">Cryptocurrencies</p>
            </div>
          </div>
          <div class="col-lg-3 mt-3 mt-lg-0">
            <div class="box">
              <h2 class="animate__animated animate__fadeInDown" align="center"><span>3</span></h2>
              <p class="animate__animated animate__fadeInUp" align="center">Precious Metals</p>
            </div>
          </div>
          <div class="col-lg-3 mt-3 mt-lg-0">
            <div class="box">
              <h2 class="animate__animated animate__fadeInDown" align="center"><span>35</span></h2>
              <p class="animate__animated animate__fadeInUp" align="center">Equities</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Crypto Section -->


    <!-- ======= Contact Section ======= -->
    <section id="contact" class="contact">
      <div class="container">

        <div class="section-title">
          <h2><span>Contact</span> Us</h2>
          <p>We would love to hear your feedback.</p>
        </div>
      </div>

      <div class="map">
        <iframe width="100%" height="350" style="border:0" loading="lazy" frameborder="0" allowfullscreen
          src="https://www.google.com/maps/embed/v1/place?q=place_id:ChIJcdSOFk3PXzkRmVCXOQLjW8k&key=AIzaSyBs5HExJTvuIn2TReNZK8n6gHpR_DDGwrg"></iframe>
      </div>

      <div class="container mt-5">

        <div class="info-wrap">
          <div class="row">
            <div class="col-lg-4 col-md-6 info">
              <i class="bi bi-geo-alt"></i>
              <h4>Location:</h4>
              <p>Sayajigunj<br>Vadodara, GJ 390002</p>
            </div>

            <div class="col-lg-4 col-md-6 info mt-4 mt-lg-0">
              <i class="bi bi-envelope"></i>
              <h4>Email:</h4>
              <p>info@example.com<br>contact@example.com</p>
            </div>

            <div class="col-lg-4 col-md-6 info mt-4 mt-lg-0">
              <i class="bi bi-phone"></i>
              <h4>Call:</h4>
              <p>+91 55555 55555<br>+91 55555 55556</p>
            </div>
          </div>
        </div>

        <form action="forms/contact.php" method="post" role="form" class="php-email-form">
          <div class="row">
            <div class="col-md-6 form-group">
              <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required>
            </div>
            <div class="col-md-6 form-group mt-3 mt-md-0">
              <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" required>
            </div>
          </div>
          <div class="form-group mt-3">
            <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" required>
          </div>
          <div class="form-group mt-3">
            <textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea>
          </div>
          <div class="my-3">
            <div class="loading">Loading</div>
            <div class="error-message"></div>
            <div class="sent-message">Your feedback has been recieved. Thank you!</div>
          </div>
          <div class="text-center"><button type="submit">Send Feedback</button></div>
        </form>

      </div>
    </section>
    <!-- End Contact Section -->

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

?>