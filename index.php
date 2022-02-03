<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();

include "db_connect.php";

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['email'])) {
	$login = '<a href="login.php" class="login-btn scrollto">Login</a>';
  $signup = '<a href="signup.php" class="signup-btn animate__animated animate__fadeInUp scrollto">Register Now</a>';
} else {
  $login = '<a href="dashboard.php" class="login-btn scrollto">Dashboard</a>';
  $signup = '<a href="dashboard.php" class="signup-btn animate__animated animate__fadeInUp scrollto">Go to Dashboard/a>';

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
  <link href="assets/img/crypto.png" rel="icon">
  <link href="assets/img/crypto.png" rel="apple-touch-icon">

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
          <li><a class="nav-link scrollto" href="#title">Get Started</a></li>
          <li><a class="nav-link scrollto" href="#invest">Invest</a></li>
          <li><a class="nav-link scrollto" href="#about">About</a></li>
          <li><a class="nav-link scrollto" href="#market">Trends</a></li>
          <li><a class="nav-link scrollto" href="#prices">Why Us?</a></li>
          <li><a class="nav-link scrollto" href="#crypto">Cryptocurrencies</a></li>
          <!-- <li><a class="nav-link scrollto" href="#wallet">Wallet</a></li> -->
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

  <main id="main">

    <!-- ======= Title Section ======= -->
    <section id="title">
      <div class="container">
        <div class="title-section">
          <h1>Buy and Sell <span>Crypto</span> in minutes</h1>
          <h5>Trade between cryptocurrencies, equities and national currencies in one single step.</h5>
        </div>
        <div class="title-btn">
          <button class="signup-btn">
            '.$signup.'
          </button>
        </div>
        <div class="slider">
          <div class="slider-item" style="display: none;">
            <h7 class="slider-title"></h7>
            <p class="slider-price"></p>
            <p class="slider-price-change"></p>
          </div>
          <!-- <div class="slider-item">
            <div class="slider-item-content">
              <h2>Buy and Sell Crypto</h2>
              <p>Trade between cryptocurrencies, equities and national currencies in one single step.</p>
            </div>
          </div>
          <div class="slider-item">
            <div class="slider-item-content">
              <h2>Buy and Sell Crypto</h2>
              <p>Trade between cryptocurrencies, equities and national currencies in one single step.</p>
            </div>
          </div>
          <div class="slider-item">
            <div class="slider-item-content">
              <h2>Buy and Sell Crypto</h2>
              <p>Trade between cryptocurrencies, equities and national currencies in one single step.</p>
            </div>
          </div> -->
        </div>
      </div>
    </section>

    <!-- ======= Invest Section ======= -->
    <section id="invest" class="about" style="background: #fff;">
      <div class="container-fluid">

        <div class="row">

          <div class="col-lg-6 d-flex flex-column justify-content-center align-items-stretch">

            <div class="content">
              <h3 style="margin-bottom: 20px;"><strong>Invest & Grow your <br />cryptocurrency porfolio</strong></h3>
              <p style="font-size: 20px; margin-bottom: 40px;">
                Reasons to invest in cryptocurrencies in 2021
              </p>

              <ul style="font-size: 20px;">
                <li style="margin-bottom: 40px;">
                  <i class="bx bx-check-double"></i> <strong>Buying Bitcoin is legal</strong>
                  <p class="fst-italic" style="font-size: 16px;">
                    The Supreme Court judgement turned in favour of Indian<br />
                    investors making investing in Cryptocurrencies legal.
                  </p>
                </li>
                <li style="margin-bottom: 40px;">
                  <i class="bx bx-check-double"></i> <strong>Growing alternate asset class</strong>
                  <p class="fst-italic" style="font-size: 16px;">
                    Trusted by 70M+ traders, major banks, institutional and<br />
                    renowned business investors and hedge funds.
                  </p>
                </li>
                <li style="margin-bottom: 40px;">
                  <i class="bx bx-check-double"></i> <strong>High return potential</strong>
                  <p class="fst-italic" style="font-size: 16px;">
                    Bitcoin became the most lucrative choice of investment<br />
                    delivering a whopping 300%+ returns in 2020.
                  </p>
                </li>
              </ul>
            </div>

          </div>

          <div class="col-lg-6 align-items-center video-box" style=\'background-image: url("assets/img/pf.png");\'>
          </div>

        </div>

      </div>
    </section>
    <!-- End Invest Section -->

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
      <div class="container-fluid">

        <div class="row">

          <div class="col-lg-5 align-items-stretch video-box" style=\'background-image: url("assets/img/about.png");\'
            style="margin-right: 40px;">
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="venobox play-btn mb-4" data-vbtype="video"
              data-autoplay="true"></a>
          </div>

          <div class="col-lg-7 d-flex flex-column justify-content-center align-items-stretch">

            <div class="content zzz">
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

    <!-- ======= Market Section ======= -->
    <section id="market" class="about" style="background: #fff;">
      <div class="container section-title">
        <h2><span>Market</span> Trend</h2>
      </div>
      <div class="container-fluid">

        <div class="row">

          <div class="col-lg-12 d-flex flex-column justify-content-center align-items-flex-start">

            <div class="content mrkt">

              <div class="scrollable-content">
                <div class="row0">
                  <div class="logo0"></div>
                  <div class="title0" style="margin-left: 85px;">Currency</div>
                  <!-- <div class="symbol0">ID</div> -->
                  <div class="amount0" style="margin-left: 50px;">Last Price</div>
                  <div class="change0">24h change</div>
                  <div class="graph0" style="margin-left:25px;">Market</div>
                </div>
                <ul id="dataList" style="list-style-type:none;"></ul>
                <div class="templates">
                  <div id="listItem">
                    <div class="row1">
                      <img class="img-fluid logo" src="./assets/img/transparent.png" width="0" height="0">
                      <div class="symbol"></div>
                      <div class="title"></div>
                      <div class="amount"></div>
                      <div class="change"></div>
                      <img class="img-fluid graph" src="./assets/img/transparent.png" width="0" height="0">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Market Section -->

    <!-- ======= Why Us Section ======= -->
    <section id="prices" class="why-us" style="background: #fffaf3;">
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
    <!-- End Why Us Section -->

    <!-- ======= Crypto Section ======= -->
    <section id="crypto" class="menu">
      <div class="container">
        <div class="row">
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
    <section id="contact" class="contact" style="background: #fffaf3;">
      <div class="container">

        <div class="section-title">
          <h2><span>Contact</span> Us</h2>
          <p>We would love to hear your feedback.</p>
        </div>
      </div>

      <div class="map" style="text-align: center;">
        <iframe width="80%" height="350" style="border:0" loading="lazy" frameborder="0" allowfullscreen
          src="https://www.google.com/maps/embed/v1/place?q=place_id:ChIJcdSOFk3PXzkRmVCXOQLjW8k&key=AIzaSyBs5HExJTvuIn2TReNZK8n6gHpR_DDGwrg"></iframe>
      </div>

      <div class="container mt-5">

        <div class="info-wrap">
          <div class="row">
            <div class="col-lg-4 col-md-6 info" style="background: #fffaf3;">
              <i class="bi bi-geo-alt"></i>
              <h4>Location:</h4>
              <p>Sayajigunj<br>Vadodara, GJ 390002</p>
            </div>

            <div class="col-lg-4 col-md-6 info mt-4 mt-lg-0" style="background: #fffaf3;">
              <i class="bi bi-envelope"></i>
              <h4>Email:</h4>
              <p>info@example.com<br>contact@example.com</p>
            </div>

            <div class="col-lg-4 col-md-6 info mt-4 mt-lg-0" style="background: #fffaf3;">
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
      <div class="links">
        <a href="#">Privacy Policy</a>
        <a href="#">Cookie Policy</a>
        <a href="#contact">Feedback</a>
        <a href="#contact">Report a Problem</a>
      </div>
    </div>
  </footer>
  <!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="./assets/vendor/core/jquery.3.2.1.min.js" type="text/javascript"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>
  <script>
    https: var list = {
      url:
        "https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,dogecoin,matic-network,ripple,terra-luna,litecoin,monero,tether,solana,shiba-inu,uniswap&order=market_cap_desc&per_page=100&page=1&sparkline=true&price_change_percentage=24h",
      method: "GET",
      timeout: 0,
    };

    $.ajax(list).done(function (response) {
      console.log(response);
      var dataObject = response;
      var listItemString = $("#listItem").html();

      dataObject.forEach(buildNewList);

      function buildNewList(item, index) {
        var listItem = $("<li>" + listItemString + "</li>");
        // console.log(item.logo_url);
        var logo = $(".logo", listItem);
        logo.attr("src", item.image);
        var listItemTitle = $(".title", listItem);
        listItemTitle.html(item.name);
        // console.log(typeof item.current_price);
        var listItemAmount = $(".amount", listItem);
        listItemAmount.html("$ " + parseFloat(item.current_price).toFixed(2));
        var listItemDesc = $(".symbol", listItem);
        listItemDesc.html(item.symbol.toString().toUpperCase());
        var listItemChange = $(".change", listItem);
        listItemChange.html(
          parseFloat(item.price_change_percentage_24h_in_currency).toFixed(2) + "%"
        );
        var listItemGraph = $(".graph", listItem);
        var data = item.sparkline_in_7d.price;
        data = data.toString();
        var src = "https://quickchart.io/chart?bkg=white&c={type:\'sparkline\',data:{datasets:[{backgroundColor:\'rgba(255,0,0,0.2)\',borderColor:\'red\',data:[" + data + "]}]}}"
        listItemGraph.attr("src", src);
        // console.log(src);
        $("#dataList").append(listItem);
      }
    });


    var crpytoList = ["bitcoin", "ethereum", "dogecoin", "matic-network", "ripple", "terra-luna", "litecoin", "monero", "tether", "solana", "shiba-inu", "uniswap"];

    var random = crpytoList.sort(() => .5 - Math.random()).slice(0, 5);

    random = random.join(",");

    // console.log(random);

    /* https: var list2 = {
      url:
        "https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,dogecoin,ripple,solana&order=market_cap_desc&per_page=100&page=1&sparkline=false&price_change_percentage=24h",
      method: "GET",
      timeout: 0,
    }; */

    // Use the following for random cryptos
    https: var list2 = {
      url:
        "https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=" + random + "&order=market_cap_desc&per_page=100&page=1&sparkline=false&price_change_percentage=24h",
      method: "GET",
      timeout: 0,
    };

    $.ajax(list2).done(function (response) {
      console.log(response);
      var dataObject = response;
      var listItemString = $(".slider-item").html();

      dataObject.forEach(buildNewSlider);

      var cls = "class=\"slider-item\"";

      function buildNewSlider(item, index) {
        var listItem = $("<div>" + listItemString + "</div>");
        // console.log(item.logo_url);
        // var logo = $(".logo", listItem);
        // logo.attr("src", item.image);
        var listItemTitle = $(".slider-title", listItem);
        listItemTitle.html(item.symbol.toString().toUpperCase() + "/USDT");
        // console.log(typeof item.current_price);
        var listItemAmount = $(".slider-price", listItem);
        listItemAmount.html("$ " + parseFloat(item.current_price).toFixed(2));
        //var listItemDesc = $(".symbol", listItem);
        //listItemDesc.html(item.symbol.toString().toUpperCase());
        var listItemChange = $(".slider-price-change", listItem);
        listItemChange.html(
          parseFloat(item.price_change_percentage_24h_in_currency).toFixed(2) + "%"
        );
        if (parseFloat(item.price_change_percentage_24h_in_currency).toFixed(2) > 0) {
          listItemChange.css("color", "green");
        } else {
          listItemChange.css("color", "red");
        }
        listItem.addClass("slider-item");
        $(".slider").append(listItem);
      }
    });

  </script>
</body>

</html>';

?>