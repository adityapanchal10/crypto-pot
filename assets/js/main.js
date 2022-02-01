(function () {
	"use strict";

	/**
	 * Easy selector helper function
	 */
	const select = (el, all = false) => {
		el = el.trim();
		if (all) {
			return [...document.querySelectorAll(el)];
		} else {
			return document.querySelector(el);
		}
	};

	/**
	 * Easy event listener function
	 */
	const on = (type, el, listener, all = false) => {
		let selectEl = select(el, all);
		if (selectEl) {
			if (all) {
				selectEl.forEach((e) => e.addEventListener(type, listener));
			} else {
				selectEl.addEventListener(type, listener);
			}
		}
	};

	/**
	 * Easy on scroll event listener
	 */
	const onscroll = (el, listener) => {
		el.addEventListener("scroll", listener);
	};

	/**
	 * Navbar links active state on scroll
	 */
	let navbarlinks = select("#navbar .scrollto", true);
	const navbarlinksActive = () => {
		let position = window.scrollY + 200;
		navbarlinks.forEach((navbarlink) => {
			if (!navbarlink.hash) return;
			let section = select(navbarlink.hash);
			if (!section) return;
			if (
				position >= section.offsetTop &&
				position <= section.offsetTop + section.offsetHeight
			) {
				navbarlink.classList.add("active");
			} else {
				navbarlink.classList.remove("active");
			}
		});
	};
	window.addEventListener("load", navbarlinksActive);
	onscroll(document, navbarlinksActive);

	/**
	 * Scrolls to an element with header offset
	 */
	const scrollto = (el) => {
		let header = select("#header");
		let offset = header.offsetHeight;

		let elementPos = select(el).offsetTop;
		window.scrollTo({
			top: elementPos - offset,
			behavior: "smooth",
		});
	};

	/**
	 * Toggle .header-scrolled class to #header when page is scrolled
	 */
	let selectHeader = select("#header");
	let selectTopbar = select("#topbar");
	if (selectHeader) {
		const headerScrolled = () => {
			if (window.scrollY > 100) {
				selectHeader.classList.add("header-scrolled");
				if (selectTopbar) {
					selectTopbar.classList.add("topbar-scrolled");
				}
			} else {
				selectHeader.classList.remove("header-scrolled");
				if (selectTopbar) {
					selectTopbar.classList.remove("topbar-scrolled");
				}
			}
		};
		window.addEventListener("load", headerScrolled);
		onscroll(document, headerScrolled);
	}

	/**
	 * Back to top button
	 */
	let backtotop = select(".back-to-top");
	if (backtotop) {
		const toggleBacktotop = () => {
			if (window.scrollY > 100) {
				backtotop.classList.add("active");
			} else {
				backtotop.classList.remove("active");
			}
		};
		window.addEventListener("load", toggleBacktotop);
		onscroll(document, toggleBacktotop);
	}

	/**
	 * Mobile nav toggle
	 */
	on("click", ".mobile-nav-toggle", function (e) {
		select("#navbar").classList.toggle("navbar-mobile");
		this.classList.toggle("bi-list");
		this.classList.toggle("bi-x");
	});

	/**
	 * Mobile nav dropdowns activate
	 */
	on(
		"click",
		".navbar .dropdown > a",
		function (e) {
			if (select("#navbar").classList.contains("navbar-mobile")) {
				e.preventDefault();
				this.nextElementSibling.classList.toggle("dropdown-active");
			}
		},
		true
	);

	/**
	 * Scrool with ofset on links with a class name .scrollto
	 */
	on(
		"click",
		".scrollto",
		function (e) {
			if (select(this.hash)) {
				e.preventDefault();

				let navbar = select("#navbar");
				if (navbar.classList.contains("navbar-mobile")) {
					navbar.classList.remove("navbar-mobile");
					let navbarToggle = select(".mobile-nav-toggle");
					navbarToggle.classList.toggle("bi-list");
					navbarToggle.classList.toggle("bi-x");
				}
				scrollto(this.hash);
			}
		},
		true
	);

	/**
	 * Scroll with ofset on page load with hash links in the url
	 */
	window.addEventListener("load", () => {
		if (window.location.hash) {
			if (select(window.location.hash)) {
				scrollto(window.location.hash);
			}
		}
	});

	/**
	 * Hero carousel indicators
	 */
	let heroCarouselIndicators = select("#hero-carousel-indicators");
	let heroCarouselItems = select("#heroCarousel .carousel-item", true);

	heroCarouselItems.forEach((item, index) => {
		index === 0
			? (heroCarouselIndicators.innerHTML +=
					"<li data-bs-target='#heroCarousel' data-bs-slide-to='" +
					index +
					"' class='active'></li>")
			: (heroCarouselIndicators.innerHTML +=
					"<li data-bs-target='#heroCarousel' data-bs-slide-to='" +
					index +
					"'></li>");
	});

	// Submit button dashboard
	$(".submit-btn").click(function () {
		var $this = $(this);

		$this.addClass("btn-contract");

		setTimeout(function () {
			$this.removeClass("btn-contract");
		}, 2200);
	});

	//Chart

	/*const labels = [
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
	];
	const data = {
		labels: labels,
		datasets: [
			{
				label: "Crypto Price",
				backgroundColor: "rgb(255, 99, 132)",
				borderColor: "rgb(255, 99, 132)",
				data: [0, 10, 5, 2, 20, 30, 45, 30, 25, 10, 35],
			},
		],
	};

	const config = {
		type: "line",
		data: data,
		options: {
			plugins: {
				legend: {
					display: false,
				},
			},

			scales: {
				x: {
					display: false,
				},
				y: {
					display: false,
				},
			},
		},
	};

	var myChart = new Chart(document.getElementById("myChart"), config);
	*/

	// Crypto Price list
	/*var list = {
		url:
			"https://api.nomics.com/v1/currencies/ticker?key=f1e32917e66702d7c7c609a36eadd0f8e0bbf836&ids=BTC,ETH,XRP,EOS,LTC,XMR,ADA,BNB,SOL,DOT,DOGE,UNI,LUNA&convert=USD&per-page=100&page=1",
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
			logo.attr("src", item.logo_url);
			var listItemTitle = $(".title", listItem);
			listItemTitle.html(item.name);
			// console.log(typeof item.price);
			var listItemAmount = $(".amount", listItem);
			listItemAmount.html("$ " + parseFloat(item.price).toFixed(2));
			var listItemDesc = $(".symbol", listItem);
			listItemDesc.html(item.symbol);
			$("#dataList").append(listItem);
		}
	});

	// Chart with d3.js and dynamic values
	// Chart with c3.js and dynamic values
	const monthNames = [
		"Jan",
		"Feb",
		"Mar",
		"Apr",
		"May",
		"Jun",
		"Jul",
		"Aug",
		"Sep",
		"Oct",
		"Nov",
		"Dec",
	];

	var url =
		"https://min-api.cryptocompare.com/data/histoday?fsym=BTC&tsym=USD&limit=30&aggregate=2&e=CCCAGG";

	*/

	/*
	d3.json(url).get(function (error, d) {
		var data = d.Data;
		data.forEach(function (d) {
			d.time = new Date(d.time * 1000);
		});

		if (error) throw error;

		var svg = d3.select("svg"),
			margin = { top: 20, right: 20, bottom: 30, left: 50 },
			width = +svg.attr("width") - margin.left - margin.right,
			height = +svg.attr("height") - margin.top - margin.bottom,
			g = svg
				.append("g")
				.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		var x = d3.scaleTime().range([0, width]);

		var y = d3.scaleLinear().range([height, 0]);

		var line = d3
			.line()
			.x(function (d) {
				console.log(monthNames[d.time.getMonth()]);
				return x(d.time);
			})
			.y(function (d) {
				return y(d.close);
			});

		x.domain(
			d3.extent(data, function (d) {
				return d.time;
			})
		);
		y.domain(
			d3.extent(data, function (d) {
				return d.close;
			})
		);

		g.append("g")
			.attr("transform", "translate(0," + height + ")")
			.call(d3.axisBottom(x))
			.attr("stroke-width", 2)
			.attr("fill", "none")
			.style("font-size", ".5em");

		g.append("g")
			.call(d3.axisLeft(y))
			.attr("stroke-width", 2)
			.style("font-size", ".5em")
			.append("text")
			.attr("fill", "#000")
			.attr("transform", "rotate(-90)")
			.attr("y", 20)
			.attr("text-anchor", "end")
			.attr("font-size", "1.2em")
			.text("Price ($)");

		g.append("path")
			.datum(data)
			.attr("fill", "none")
			.attr("stroke", "#ffeaa7")
			.attr("stroke-linejoin", "round")
			.attr("stroke-linecap", "round")
			.attr("stroke-width", 2)
			.attr("d", line);
	});
	*/
	/* var portfolio_data = [];
	var x = [];
	portfolio_data[0] = 'BTC';
	x[0] = 'x';
	var worth = {
		url:
			"https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=usd&days=10&interval=daily",
		method: "GET",
		timeout: 0,
	};

	$.ajax(worth).done(function (response) {
		var dataObject = response.prices;
		var i = 1;
		dataObject.forEach((item) => {
			portfolio_data[i] = parseFloat(item[1]).toFixed(2);
			var d = new Date(item[0]);
			x[i] = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
			i++;
		});
		console.log(x);

		var chart = c3.generate({
			bindto: '#portfolio-chart',
			data: {
				x: 'x',
				columns: [
					x,
					portfolio_data
				],
				type: 'spline'
			},
			legend: {
				show: false
			},
			axis: {
				x: {
					show: false,
					type: 'timeseries',
					tick: {
						// this also works for non timeseries data
						values: ['2021-09-19', '2021-09-29']
					}
				},
				y: {
					show: false,
				}
			},
			size: {
				width: 400,
				height: 300
			}
		});
	});*/
})();
