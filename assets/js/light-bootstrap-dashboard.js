// =========================================================
//  Light Bootstrap Dashboard - v2.0.1
// =========================================================

var searchVisible = 0;
var transparent = true;

var transparentDemo = true;
var fixedTop = false;

var navbar_initialized = false;
var mobile_menu_visible = 0,
	mobile_menu_initialized = false,
	toggle_initialized = false,
	bootstrap_nav_initialized = false,
	$sidebar,
	isWindows;

$(document).ready(function () {
	window_width = $(window).width();

	// check if there is an image set for the sidebar's background
	lbd.checkSidebarImage();

	// Init navigation toggle for small screens
	if (window_width <= 991) {
		lbd.initRightMenu();
	}

	//  Activate the tooltips
	$('[rel="tooltip"]').tooltip();

	//      Activate regular switches
	if ($("[data-toggle='switch']").length != 0) {
		$("[data-toggle='switch']").bootstrapSwitch();
	}

	$(".form-control")
		.on("focus", function () {
			$(this).parent(".input-group").addClass("input-group-focus");
		})
		.on("blur", function () {
			$(this).parent(".input-group").removeClass("input-group-focus");
		});

	// Fixes sub-nav not working as expected on IOS
	$("body").on("touchstart.dropdown", ".dropdown-menu", function (e) {
		e.stopPropagation();
	});
});

// activate collapse right menu when the windows is resized
$(window).resize(function () {
	if ($(window).width() <= 991) {
		lbd.initRightMenu();
	}
});

lbd = {
	misc: {
		navbar_menu_visible: 0,
	},
	checkSidebarImage: function () {
		$sidebar = $(".sidebar");
		image_src = $sidebar.data("image");

		if (image_src !== undefined) {
			sidebar_container =
				'<div class="sidebar-background" style="background-image: url(' +
				image_src +
				') "/>';
			$sidebar.append(sidebar_container);
		} else if (mobile_menu_initialized == true) {
			// reset all the additions that we made for the sidebar wrapper only if the screen is bigger than 991px
			$sidebar_wrapper.find(".navbar-form").remove();
			$sidebar_wrapper.find(".nav-mobile-menu").remove();

			mobile_menu_initialized = false;
		}
	},

	initRightMenu: function () {
		$sidebar_wrapper = $(".sidebar-wrapper");

		if (!mobile_menu_initialized) {
			$navbar = $("nav").find(".navbar-collapse").first().clone(true);

			nav_content = "";
			mobile_menu_content = "";

			//add the content from the regular header to the mobile menu
			$navbar.children("ul").each(function () {
				content_buff = $(this).html();
				nav_content = nav_content + content_buff;
			});

			nav_content = '<ul class="nav nav-mobile-menu">' + nav_content + "</ul>";

			$navbar_form = $("nav").find(".navbar-form").clone(true);

			$sidebar_nav = $sidebar_wrapper.find(" > .nav");

			// insert the navbar form before the sidebar list
			$nav_content = $(nav_content);
			$nav_content.insertBefore($sidebar_nav);
			$navbar_form.insertBefore($nav_content);

			$(".sidebar-wrapper .dropdown .dropdown-menu > li > a").click(function (
				event
			) {
				event.stopPropagation();
			});

			mobile_menu_initialized = true;
		} else {
			console.log("window with:" + $(window).width());
			if ($(window).width() > 991) {
				// reset all the additions that we made for the sidebar wrapper only if the screen is bigger than 991px
				$sidebar_wrapper.find(".navbar-form").remove();
				$sidebar_wrapper.find(".nav-mobile-menu").remove();

				mobile_menu_initialized = false;
			}
		}

		if (!toggle_initialized) {
			$toggle = $(".navbar-toggler");

			$toggle.click(function () {
				if (mobile_menu_visible == 1) {
					$("html").removeClass("nav-open");

					$(".close-layer").remove();
					setTimeout(function () {
						$toggle.removeClass("toggled");
					}, 400);

					mobile_menu_visible = 0;
				} else {
					setTimeout(function () {
						$toggle.addClass("toggled");
					}, 430);

					main_panel_height = $(".main-panel")[0].scrollHeight;
					$layer = $('<div class="close-layer"></div>');
					$layer.css("height", main_panel_height + "px");
					$layer.appendTo(".main-panel");

					setTimeout(function () {
						$layer.addClass("visible");
					}, 100);

					$layer.click(function () {
						$("html").removeClass("nav-open");
						mobile_menu_visible = 0;

						$layer.removeClass("visible");

						setTimeout(function () {
							$layer.remove();
							$toggle.removeClass("toggled");
						}, 400);
					});

					$("html").addClass("nav-open");
					mobile_menu_visible = 1;
				}
			});

			toggle_initialized = true;
		}
	},
};

// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.

function debounce(func, wait, immediate) {
	var timeout;
	return function () {
		var context = this,
			args = arguments;
		clearTimeout(timeout);
		timeout = setTimeout(function () {
			timeout = null;
			if (!immediate) func.apply(context, args);
		}, wait);
		if (immediate && !timeout) func.apply(context, args);
	};
}

// Crypto Price list

//https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,dogecoin,matic-network,cardano,solana,ripple,binancecoin,polkadot,uniswap,litecoin,monero,eos&order=market_cap_desc&per_page=100&page=1&sparkline=false

https: var list = {
	url:
		"https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,dogecoin,matic-network,tether,shiba-inu,cardano,solana,ripple,binancecoin,polkadot,terra-luna,uniswap,litecoin,monero,eos,avalanche-2,chainlink&order=market_cap_desc&per_page=100&page=1&sparkline=false&price_change_percentage=24h",
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
		$("#dataList").append(listItem);
	}
});

// Submit button dashboard
$(".submit-btn").click(function () {
	var $this = $(this);

	$this.addClass("btn-contract");

	setTimeout(function () {
		$this.removeClass("btn-contract");
	}, 2200);
});

// Transfer to a friend
$(document).ready(function () {
	$("#transfer").click(function () {
		$("#transactionH").removeClass("show");
		$("#transactionH").addClass("fadeout");
		$("#transactionH").addClass("hidden");
		$("#transaction").removeClass("show");
		$("#transaction").addClass("fadeout");
		$("#transaction").addClass("hidden");
		$("#transferFH").removeClass("hidden");
		$("#transferFH").addClass("fadeout");
		$("#transferFH").addClass("show");
		$("#transferF").removeClass("hidden");
		$("#transferF").addClass("fadeout");
		$("#transferF").addClass("show");
	});

	$("#backToTransaction").click(function () {
		$("#transferFH").removeClass("show");
		$("#transferFH").addClass("fadeout");
		$("#transferFH").addClass("hidden");
		$("#transferF").removeClass("show");
		$("#transferF").addClass("fadeout");
		$("#transferF").addClass("hidden");
		$("#transactionH").removeClass("hidden");
		$("#transactionH").addClass("fadeout");
		$("#transactionH").addClass("show");
		$("#transaction").removeClass("hidden");
		$("#transaction").addClass("fadeout");
		$("#transaction").addClass("show");
	});
});
