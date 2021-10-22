var chart1 = new Chartist.Pie(
	"#chartPreferences",
	{
		series: [
			{
				value: 53,
				className: "pie1",
			},
			{
				value: 36,
				className: "pie2",
			},
			{
				value: 11,
				className: "pie3",
			},
		],
		labels: ["53%", "36%", "11%"],
	},
	{
		donut: true,
		showLabel: true,
	}
);

chart1.on("draw", function (data) {
	if (data.type === "slice") {
		// Get the total path length in order to use for dash array animation
		var pathLength = data.element._node.getTotalLength();

		// Set a dasharray that matches the path length as prerequisite to animate dashoffset
		data.element.attr({
			"stroke-dasharray": pathLength + "px " + pathLength + "px",
		});

		// Create animation definition while also assigning an ID to the animation for later sync usage
		var animationDefinition = {
			"stroke-dashoffset": {
				id: "anim" + data.index,
				dur: 1000,
				from: -pathLength + "px",
				to: "0px",
				easing: Chartist.Svg.Easing.easeOutQuint,
				// We need to use `fill: 'freeze'` otherwise our animation will fall back to initial (not visible)
				fill: "freeze",
			},
		};

		// If this was not the first slice, we need to time the animation so that it uses the end sync event of the previous animation
		if (data.index !== 0) {
			animationDefinition["stroke-dashoffset"].begin =
				"anim" + (data.index - 1) + ".end";
		}

		// We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
		data.element.attr({
			"stroke-dashoffset": -pathLength + "px",
		});

		// We can't use guided mode as the animations need to rely on setting begin manually
		// See http://gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
		data.element.animate(animationDefinition, false);
	}
});

// For the sake of the example we update the chart1 every time it's created with a delay of 8 seconds
chart1.on("created", function () {
	if (window.__anim21278907124) {
		clearTimeout(window.__anim21278907124);
		window.__anim21278907124 = null;
	}
	window.__anim21278907124 = setTimeout(chart1.update.bind(chart1), 10000);
});

var weekly = {
	url:
		"https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=usd&days=6&interval=daily",
	method: "GET",
	timeout: 0,
};

var price = [];
var time = [];
// portfolio_data[0] = "BTC";
// x[0] = "x";

$.ajax(weekly).done(function (response) {
	var dataObject = response.prices;
	console.log(dataObject);
	var i = 0;
	dataObject.forEach((item) => {
		price[i] = parseFloat(item[1]);
		var d = new Date(item[0]);
		// x[i] = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
		time[i] = d.toDateString();
		i++;
	});
	// console.log(x);
	// console.log("-----------------//----------");
	// console.log(portfolio_data);

	chart2 = new Chartist.Line(
		"#chartHours",
		{
			labels: time,
			series: [price],
		},
		{
			height: "300px",
			low: 40000,
			showArea: true,
			fullwidth: true,
			axisX: {
				showGrid: false,
			},
			axisY: {
				showGrid: false,
				showLabel: true,
				offset: 60,
				// The label interpolation function enables you to modify the values
				// used for the labels on each axis. Here we are converting the
				// values into million pound.
				labelInterpolationFnc: function (value) {
					return "$" + String(value)[0] + "." + String(value)[1] + "k";
				},
			},
		}
	);
});
