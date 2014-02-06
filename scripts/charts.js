var dayLength = 24 * 60 * 60 * 1000;
var bundleStats;
var data;
var gotData = false;
var gotAggregate = false;
var selectedData = Array();
var tip;
var helpers;
var helpersWrapper;
var helperTargets;
var helperTargetsWrapper;

function getShortTitle(title)
{
	return title.replace(/(The\sHumble\sBundle\sfor\s)/gi, "").replace(/(The\sHumble\sBundle\swith\s)/gi, "").replace(/(Humble\sBundle\swith\s)/gi, "").replace(/(The\sHumble\sBundle\s)/gi, "").replace(/(The\sHumble\s)/gi, "").replace(/(Humble\s)/gi, "").replace(/(\sBundle\sfeaturing)/gi, "").replace(/(\sBundle)/gi, "").replace(/(Bundle:\sPC\sand\s)/gi, "").replace(/(\sDebut)/gi,"");
}

function zeroPad(number)
{
	return (number >= 10 ? '' + number : '0'+ number);	
}

function getDaysBetween(from, to)
{
	return Math.round(Math.abs((from.getTime() - to.getTime())/dayLength));
}

function getSensibleDate(currentDate, withTime)
{
	var returnString = "" + currentDate.getUTCFullYear() + "-" + zeroPad(currentDate.getUTCMonth() + 1) + "-" + zeroPad(currentDate.getUTCDate());
	if (withTime)
	{
		returnString += " " + zeroPad(currentDate.getUTCHours()) + ":" + zeroPad(currentDate.getUTCMinutes());
	}
	return returnString;
}

function buildDate(string)
{
	string = string.split(/[\s:-]+/);
	newDate = new Date(string[0], string[1] - 1, string[2]);
//	newDate.setUTCFullYear(string[0]);
//	newDate.setUTCMonth(string[1] - 1);
//	newDate.setUTCDate(string[2]);
	newDate.setUTCHours(string[3]);
	newDate.setUTCMinutes(string[4]);
	newDate.setUTCSeconds(string[5]);
	return newDate
}

var removeLoadingImage = function(e)
{
	e.selectAll(".loadingImage").remove();
}

var dimIn = function(p)
{
	ps = ["Lin", "Mac", "Win"];
	ps.splice(ps.indexOf(p), 1);
	d3.selectAll(".slice" + ps.join(", .slice")).style("opacity", "0.5");
	d3.selectAll(".bar" + ps.join(", .bar") ).style("opacity", "0.5");
	d3.selectAll(".value"  + ps.join(", .value")).style("opacity", "0.5");
}

var dimOut = function (p)
{
	d3.selectAll(".slice" + ", .bar" + ", .value").style("opacity", "1");
}

var dimInPrice = function(p)
{
	ps = ["fullPriceLast", "fullPriceFirst"];
	ps.splice(ps.indexOf(p), 1);
	d3.selectAll(".slice" + ps.join(", .slice")).style("opacity", "0.5");
	d3.selectAll(".bar" + ps.join(", .bar") ).style("opacity", "0.5");
	d3.selectAll(".value"  + ps.join(", .value")).style("opacity", "0.5");
}

var showTooltip = function(x, y, text)
{
	tip.style("left", (x + 10) + "px");
	tip.style("top", (y + 10) + "px");
	tip.style("display", "block");
	tip.html(text);
}

var hideTooltip = function()
{
	tip.style("display", "none");
	tip.text("");
}

var showHelper = function(helper)
{
	var out = false;
	for (h in helpers)
	{
		if (h == helper)
		{
			if (helpers[helper].style("z-index") == "10")
			{
				out = true;
				helpers[h].style("display", "block").style("z-index", "50");
				helperTargets[helper].style("background-color", "#f8f8f8").style("padding-bottom", "0.5em");
			}
			else
			{
				helpers[h].style("display", "none").style("z-index", "10");
				helperTargets[helper].style("background-color", "#e0e0e0").style("padding-bottom", "0.25em");
			}
		}
		else
		{
			helpers[h].style("display", "none").style("z-index", "10");
			helperTargets[h].style("background-color", "#e0e0e0").style("padding-bottom", "0.25em");
		}
	}

	helpersWrapper.style("height", out ? "75%" : "0" );
	helperTargetsWrapper.style("bottom", out ? "75%" : "0" );

}

function init()
{
	helpersWrapper = d3.select("#helpers");
	helpers = { 		"help": helpersWrapper.select("#help"),
				"footnotes": helpersWrapper.select("#footnotes"),
				"sightings": helpersWrapper.select("#sightings"),
				"about": helpersWrapper.select("#about"),
				"discussion": helpersWrapper.select("#discussion") ,
				"source": helpersWrapper.select("#source") };

	helperTargetsWrapper = d3.select("#helperTargets");
	helperTargets = {	"help": helperTargetsWrapper.select("#help_target").on("click", function() { showHelper("help"); } ),
				"footnotes": helperTargetsWrapper.select("#footnotes_target").on("click", function() { showHelper("footnotes"); } ),
				"sightings": helperTargetsWrapper.select("#sightings_target").on("click", function() { showHelper("sightings"); } ),
				"about": helperTargetsWrapper.select("#about_target").on("click", function() { showHelper("about"); } ),
				"discussion": helperTargetsWrapper.select("#discussion_target").on("click", function() { showHelper("discussion"); } ),
				"source": helperTargetsWrapper.select("#source_target").on("click", function() { showHelper("source"); } ) };

	tip = d3.select("body").append("div").attr("class", "tooltip");

	aggregateData = [];
	d3.json("getdata.php?aggregate=true", function(result) {
		aggregateData = [];
		avMax = []
		i = 0;
		for (bundle in result)
		{
			if (result[bundle].firstSeen == null)
			{
				continue;
			}

			aggregateData[i] = result[bundle];
			aggregateData[i].firstSeen = buildDate(aggregateData[i].firstSeen);
			aggregateData[i].lastSeen = buildDate(aggregateData[i].lastSeen);
			aggregateData[i].pyCumulative = [];
			aggregateData[i].pyCumulative.push({'v' : parseFloat(aggregateData[i].pyMac), 'p' : "Mac", 'pc' : Math.round(parseFloat(aggregateData[i].pcMac) * 10000)/ 100 });
			aggregateData[i].pyCumulative.push({'v' : parseFloat(aggregateData[i].pyWin), 'p' : "Win", 'pc' : Math.round(parseFloat(aggregateData[i].pcWin) * 10000)/ 100 });
			aggregateData[i].pyCumulative.push({'v' : parseFloat(aggregateData[i].pyLin), 'p' : "Lin", 'pc' : Math.round(parseFloat(aggregateData[i].pcLin) * 10000)/ 100 });
			aggregateData[i].avCumulative = [];
			aggregateData[i].avCumulative.push({'v' : parseFloat(aggregateData[i].avMac), 'p' : "Mac", 'diff' : Math.round((parseFloat(aggregateData[i].avMac) - parseFloat(aggregateData[i].avAll)) * 100) / 100 });
			aggregateData[i].avCumulative.push({'v' : parseFloat(aggregateData[i].avWin), 'p' : "Win", 'diff' : Math.round((parseFloat(aggregateData[i].avWin) - parseFloat(aggregateData[i].avAll)) * 100) / 100 });
			aggregateData[i].avCumulative.push({'v' : parseFloat(aggregateData[i].avLin), 'p' : "Lin", 'diff' : Math.round((parseFloat(aggregateData[i].avLin) - parseFloat(aggregateData[i].avAll)) * 100) / 100 });
			avMax.push(d3.max(aggregateData[i].avCumulative, function(d) { return d.v; }));
			aggregateData[i].puCumulative = [];
			aggregateData[i].puCumulative.push({'v' : parseFloat(aggregateData[i].puMac), 'p' : "Mac", 'pc' : Math.round(parseFloat(aggregateData[i].puMac) / parseFloat(aggregateData[i].puTotal) * 10000 ) / 100 });
			aggregateData[i].puCumulative.push({'v' : parseFloat(aggregateData[i].puWin), 'p' : "Win", 'pc' : Math.round(parseFloat(aggregateData[i].puWin) / parseFloat(aggregateData[i].puTotal) * 10000 ) / 100 });
			aggregateData[i].puCumulative.push({'v' : parseFloat(aggregateData[i].puLin), 'p' : "Lin", 'pc' : Math.round(parseFloat(aggregateData[i].puLin) / parseFloat(aggregateData[i].puTotal) * 10000 ) / 100 });
			aggregateData[i].priceCumulative = [];
			aggregateData[i].priceCumulative.push({'v' : parseFloat(aggregateData[i].fullPriceFirst), 'p' : "fullPriceFirst", 'pc' : Math.round(parseFloat(aggregateData[i].fullPriceFirst) / parseFloat(aggregateData[i].fullPriceLast) * 100 ) / 100 });
			aggregateData[i].priceCumulative.push({'v' : parseFloat(aggregateData[i].fullPriceLast) - parseFloat(aggregateData[i].fullPriceFirst), 'p' : "fullPriceLast", 'pc' : Math.round((parseFloat(aggregateData[i].fullPriceLast) - parseFloat(aggregateData[i].fullPriceFirst)) / parseFloat(aggregateData[i].fullPriceLast) * 100 ) / 100 });
			aggregateData[i].variance = parseFloat(aggregateData[i].puTotal) - (parseFloat(aggregateData[i].puLin) + parseFloat(aggregateData[i].puMac) + parseFloat(aggregateData[i].puWin));
						
			aggregateData[i].type = "other";
			if (aggregateData[i].bundleTitle.toLowerCase().indexOf("android") >= 0)
			{
				aggregateData[i].type = "android";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("non-indie") >= 0)
			{
				aggregateData[i].type = "non-indie";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("indie") >= 0)
			{
				aggregateData[i].type = "indie";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("mojam") >= 0)
			{
				aggregateData[i].type = "mojam";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("debut") >= 0)
			{
				aggregateData[i].type = "debut";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("music") >= 0)
			{
				aggregateData[i].type = "music";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("ebook") >= 0)
			{
				aggregateData[i].type = "ebook";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("audiobook") >= 0)
			{
				aggregateData[i].type = "audiobook";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("comedy") >= 0)
			{
				aggregateData[i].type = "comedy";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("mobile") >= 0)
			{
				aggregateData[i].type = "mobile";
			}
			else if (aggregateData[i].bundleTitle.toLowerCase().indexOf("all results") >= 0)
			{
				aggregateData[i].type = "all";
			}
			i++;
		}
		
		avMax = d3.max(avMax);

		for (bundle in aggregateData)
		{
			aggregateData[bundle].avMax = avMax;
//			makeBundleStats(aggregateData[bundle]);
		}
		gotAggregate = true;
		makeBundleStats(d3.select("#aggregateAll"), aggregateData[0], "aggregateAll");
		tryCurrentStats();
	
//		aggregateData = aggregateData.reverse();
//		updateAggregateStats();
	});
	
	data = [];
	d3.json("getdata.php", function(result) {
		data = [];
		avMax = []
		i = 0;
		for (bundle in result)
		{
			data[i] = result[bundle];
			data[i].firstSeen = buildDate(data[i].firstSeen);
			data[i].lastSeen = buildDate(data[i].lastSeen);
			data[i].pyCumulative = [];
			data[i].pyCumulative.push({'v' : parseFloat(data[i].pyMac), 'p' : "Mac", 'pc' : Math.round(parseFloat(data[i].pcMac) * 10000)/ 100 });
			data[i].pyCumulative.push({'v' : parseFloat(data[i].pyWin), 'p' : "Win", 'pc' : Math.round(parseFloat(data[i].pcWin) * 10000)/ 100 });
			data[i].pyCumulative.push({'v' : parseFloat(data[i].pyLin), 'p' : "Lin", 'pc' : Math.round(parseFloat(data[i].pcLin) * 10000)/ 100 });
			data[i].avCumulative = [];
			data[i].avCumulative.push({'v' : parseFloat(data[i].avMac), 'p' : "Mac", 'diff' : Math.round((parseFloat(data[i].avMac) - parseFloat(data[i].avAll)) * 100) / 100 });
			data[i].avCumulative.push({'v' : parseFloat(data[i].avWin), 'p' : "Win", 'diff' : Math.round((parseFloat(data[i].avWin) - parseFloat(data[i].avAll)) * 100) / 100 });
			data[i].avCumulative.push({'v' : parseFloat(data[i].avLin), 'p' : "Lin", 'diff' : Math.round((parseFloat(data[i].avLin) - parseFloat(data[i].avAll)) * 100) / 100 });
			avMax.push(d3.max(data[i].avCumulative, function(d) { return d.v; }));
			data[i].puCumulative = [];
			data[i].puCumulative.push({'v' : parseFloat(data[i].puMac), 'p' : "Mac", 'pc' : Math.round(parseFloat(data[i].puMac) / parseFloat(data[i].puTotal) * 10000 ) / 100 });
			data[i].puCumulative.push({'v' : parseFloat(data[i].puWin), 'p' : "Win", 'pc' : Math.round(parseFloat(data[i].puWin) / parseFloat(data[i].puTotal) * 10000 ) / 100 });
			data[i].puCumulative.push({'v' : parseFloat(data[i].puLin), 'p' : "Lin", 'pc' : Math.round(parseFloat(data[i].puLin) / parseFloat(data[i].puTotal) * 10000 ) / 100 });
			data[i].priceCumulative = [];
			data[i].priceCumulative.push({'v' : parseFloat(data[i].fullPriceFirst), 'p' : "fullPriceFirst", 'pc' : data[i].fullPriceFirst == data[i].fullPriceLast ? 100 : Math.round(parseFloat(data[i].fullPriceFirst) / parseFloat(data[i].fullPriceLast) * 100 ) / 100 });
			data[i].priceCumulative.push({'v' : parseFloat(data[i].fullPriceLast) - parseFloat(data[i].fullPriceFirst), 'p' : "fullPriceLast", 'pc' : Math.round((parseFloat(data[i].fullPriceLast) - parseFloat(data[i].fullPriceFirst)) / parseFloat(data[i].fullPriceLast) * 100 ) / 100 });
			data[i].fullPriceExtra = parseFloat(data[i].fullPriceLast) - parseFloat(data[i].fullPriceFirst);

			data[i].variance = parseFloat(data[i].puTotal) - (parseFloat(data[i].puLin) + parseFloat(data[i].puMac) + parseFloat(data[i].puWin));
			
			data[i].type = "non-indie";
			if (data[i].bundleTitle.toLowerCase().indexOf("android") >= 0)
			{
				data[i].type = "android";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("indie") >= 0)
			{
				data[i].type = "indie";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("mojam") >= 0)
			{
				data[i].type = "mojam";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("debut") >= 0)
			{
				data[i].type = "debut";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("music") >= 0)
			{
				data[i].type = "music";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("ebook") >= 0)
			{
				data[i].type = "ebook";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("audiobook") >= 0)
			{
				data[i].type = "audiobook";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("comedy") >= 0)
			{
				data[i].type = "comedy";
			}
			else if (data[i].bundleTitle.toLowerCase().indexOf("mobile") >= 0)
			{
				data[i].type = "mobile";
			}
			
			i++;
		}
		
		avMax = d3.max(avMax);
		
		makeTimeline(data);
		
//		data = data.reverse();
		for (bundle in data)
		{
			data[bundle].avMax = avMax;
//			makeBundleStats(data[bundle]);
		}

		currentBundle = data[data.length - 1];
		gotData = true;
		makeBundleStats(d3.select("#current"), currentBundle, "current");
		tryCurrentStats();
		
		//updateBundleStats();
		
		categorySet = data.filter(function(e, i, a) { return e.type == currentBundle.type; });
		makeValueCharts(categorySet);
		makeRevenueChart(categorySet);
		makePurchaseChart(categorySet);
		makeAverageChart(categorySet);
	});
}

var sortByDate = function()
{

}

var sortByRevenue = function()
{

}

var sortByPurcahses = function()
{

}

var sortByAverage = function()
{

}

var tryCurrentStats = function()
{
	if (gotData && gotAggregate)
	{
		var currentCategory;
		for (a in aggregateData)
		{
			if (aggregateData[a].type == currentBundle.type)
			{
				currentCategory = aggregateData[a];
				break;
			}
		}
		makeBundleStats(d3.select("#currentCategory"), currentCategory, "currentCategory");
	}
}

var updateBundleStats = function(i)
{
	//TODO: Add some nice transition effect here to help identify what's disappearing/appearing
	var b = data[i];
	var i2 = selectedData.indexOf(b);

	//If the bundle is already displayed, hide it, othewise, show it.
	if (i2 >= 0)
	{
		selectedData.splice(i2, 1);
	}
	else
	{
		selectedData.push(b);
	}
	var f = d3.select("#bundleStats").selectAll(".bundle").data(selectedData, function(d) {return d.bundleTitle; });
	var e = f.enter().append("div").attr("class", "bundle").attr("id", function(d) {return getShortTitle(d.bundleTitle); });
	e.each(function(d, i) { makeBundleStats(d3.select(this), d); });
	f.exit().remove();

	selectedData.sort(function (a, b) { return a.firstSeen.getTime() > b.firstSeen.getTime(); });

	//TODO: This is super temporary and only here because I couldn't get the stacked bar chart updating properly ;_;
	var v = d3.select("#overTimeValue");
	v.selectAll("h2").remove();
	v.selectAll("svg").remove();
	v.selectAll("p").remove();
	v.append("img").attr("src", "images/humvis_loading.gif").attr("alt", "This graph shows the seperate price for each of the selected promotions.").attr("class", "loadingImage");
	makeValueCharts(selectedData);
	if (selectedData.length < 2)
	{
		v.append("p").attr("class", "warning").text("You have too few items selected in the chart playground for graphs to be correctly drawn.");
	}
	if (selectedData.filter(function(e, i, a) { return parseInt(e.fullPriceLast); }).length < selectedData.length)
	{
		v.append("p").attr("class", "warning").text("One or more of the items selected in the chart playground do not contain separate price data.");
	}
	
	v = d3.select("#overTimeRevenue");
	v.selectAll("h2").remove();
	v.selectAll("svg").remove();
	v.selectAll("p").remove();
	v.append("img").attr("src", "images/humvis_loading.gif").attr("alt", "This graph shows the revenue from each platform for the selected promotions.").attr("class", "loadingImage");
	makeRevenueChart(selectedData);
	if (selectedData.length < 2)
	{
		v.append("p").attr("class", "warning").text("You have too few items selected in the chart playground for graphs to be correctly drawn.");
	}
	if (selectedData.filter(function(e, i, a) { return e.pyLin && e.pyMac && e.pyWin; }).length < selectedData.length)
	{
		v.append("p").attr("class", "warning").text("One or more of the items selected in the chart playground do not contain revenue data for each platform, causing gaps in the lines.");
	}
	
	
	v = d3.select("#overTimePurchases");
	v.selectAll("h2").remove();
	v.selectAll("svg").remove();
	v.selectAll("p").remove();
	v.append("img").attr("src", "images/humvis_loading.gif").attr("alt", "This graph shows the purchases from each platform for the selected promotions.").attr("class", "loadingImage");
	makePurchaseChart(selectedData);
	if (selectedData.length < 2)
	{
		v.append("p").attr("class", "warning").text("You have too few items selected in the chart playground for graphs to be correctly drawn.");
	}
	if (selectedData.filter(function(e, i, a) { return e.puLin && e.puMac && e.puWin; }).length < selectedData.length)
	{
		v.append("p").attr("class", "warning").text("One or more of the items selected in the chart playground do not contain purchase data for each platform, causing gaps in the lines.");
	}

	
	v = d3.select("#overTimeAverages");
	v.selectAll("h2").remove();
	v.selectAll("svg").remove();
	v.selectAll("p").remove();
	v.append("img").attr("src", "images/humvis_loading.gif").attr("alt", "This graph shows the revenue from each platform for the selected promotions.").attr("class", "loadingImage");
	makeAverageChart(selectedData);
	if (selectedData.length < 2)
	{
		v.append("p").attr("class", "warning").text("You have too few items selected in the chart playground for graphs to be correctly drawn.");
	}
	if (selectedData.filter(function(e, i, a) { return e.avLin && e.avMac && e.avWin; }).length < selectedData.length)
	{
		v.append("p").attr("class", "warning").text("One or more of the items selected in the chart playground do not contain average data for each platform, causing gaps in the lines.");
	}

	v = null;
}

var updateAggregateStats = function()
{
	//TODO: Add some nice transition effect here to help identify what's disappearing/appearing
	data2 = aggregateData;
	var f = d3.select("#bundleStatsAg").selectAll(".bundle").data(data2, function(d) {return d.bundleTitle; });
	var m = f.enter().append("div").attr("class", "bundle").attr("id", function(d) {return getShortTitle(d.bundleTitle); });
	m.each(function(d, i) { makeBundleStats(d3.select(this), d); });
	f.exit().remove();
}

var makeTimeline = function(data)
{
	var tableTarget = d3.select("#timeline");
	var currencyFormatter = d3.format(",.2f");
	var coloursBundleType ={"non-indie":"#0000cc",
				"indie" : "#cc0000",
				"android" : "#00cc00",
				"mobile" : "#00cc00",
				"mojam" : "#eeaa00",
				"debut" : "#0000cc",
				"music" : "#cc00cc",
				"ebook" : "#cc00cc",
				"audiobook" : "#cc00cc",
				"comedy" : "#cc00cc",
				};

	removeLoadingImage(tableTarget);

	tableTarget.append("a")
		.attr("class", "chartTitle")
		.attr("href", "#timeline")
		.append("h2")
		.text("Frequency Of Humble Bundle Promotions");


	var timelineWidth = 800;
	var timelineHeight = 50;
	var timeline = tableTarget.append("svg")
		.attr("width", timelineWidth)
		.attr("height", timelineHeight)
		.attr("class", "bundleTimeline");

	var tlX = d3.time.scale.utc()
		.range([20, timelineWidth-20])
		.domain([d3.min(data, function(d) { return d.firstSeen; }), d3.max(data, function(d) { return d.firstSeen; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var tlAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
//		.ticks(20)
		.scale(tlX);
	

	timeline.append("g")
		.attr("transform", "translate(" + 0 + ", 12)")
		.selectAll(".circle")
		.data(data)
		.enter().append("circle")
			.attr("class", "timelineDot")
			.style("fill", function(d) { return coloursBundleType[d.type]; })
			.attr("cx", function(d) { return tlX(d.firstSeen); })
			.attr("r", 5)
			.attr("cy", function(d) { 5 })
	        .on("mouseover", function(d, i) {
				d3.select(this).transition()
					.duration(100)
					.attr("r", 10);
				showTooltip(d3.event.pageX, d3.event.pageY, d.bundleTitle + "<br />" + getSensibleDate(d.firstSeen));
				d3.select("._" + i ).classed("hover", true);
				})
	        .on("mouseout", function(d, i) {
				d3.select(this).transition()
					.duration(100)
					.attr("r", 5);
				hideTooltip();
				tableTarget.select("._" + i).classed("hover", false);
				})
		.on("click", function (d, i) { updateBundleStats(i); d.visible = ! d.visible; tableTarget.select("._" + i).classed("selected", d.visible);
		
				});
				
	
	timeline.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(0, 22)")
		.call(tlAxisX);

	tableTarget.append("p")
		.text("This timeline illustrates the frequency with which Humble Bundle promotions have occurred.")
		.append("sup")
			.attr("class", "footnote")
		.append("a")
			//.attr("href", "#bottom")
			.text("1")
			.on("mouseover", function (d) {
				showTooltip(d3.event.pageX, d3.event.pageY, helpersWrapper.select("#foot1").html());
				})
			.on("mouseout", function (d) {
				hideTooltip();
				})
			.on("click", function (d) { showHelper("footnotes"); return false; });
	tableTarget.append("p")
		.html("<span class = 'indicatorIndie'>Red</span> represents \"indie\" bundles, <span class = 'indicatorNonIndie'>Blue</span> represents \"non-indie\" bundles and debut promotions, <span class = 'indicatorAndroid'>Green</span> represents Android and mobile bundles, <span class = 'indicatorMojam'>Mustard</span> represents Mojam events, <span class = 'indicatorEbook'>Purple</span> represents ebook, audiobook, music and comedy bundles.");
	tableTarget.append("p")
		.text("Points are placed based on start date and do not indicate duration.");


	var table = tableTarget.append("table")
		.attr("class", "revenueTable");
	var thead = table.append("thead")
		.append("tr");
	thead.append("th").text("Visible");
	thead.append("th").text("Bundle Title");
	thead.append("th").text("First Seen");
	thead.append("th").text("Last Seen");
	thead.append("th").text("Duration");
	thead.append("th").text("Avg Revenue per Day");
	thead.append("th").text("Lead");
	thead.append("th").text("Revenue per Day of Lead");
	thead.append("th").text("Total Revenue");
	var tbody = table.append("tbody");
	
	var lastEndDate = data[0].firstSeen;
	var i = 0;
	
	for (d in data)
	{
		var duration = getDaysBetween(data[d].firstSeen, data[d].lastSeen);
		var lead = getDaysBetween(lastEndDate, data[d].firstSeen);
		lastEndDate = data[d].lastSeen;
		data[d].visible = false;
		
		
		var rowClass = "oddRow ";
		if (i % 2 > 0)
		{
			rowClass = "evenRow ";
		}
		i++;
		
		var tr = tbody.append("tr")//.data(data[d])
			.attr("class", rowClass + data[d].type + " _" + d)
			.attr("id", encodeURIComponent(data[d].bundleTitle))
			.attr("index", d)
			.on("click", function() { e = d3.select(this); i = e.attr("index"); data[i].visible = ! data[i].visible; e.classed("selected", data[i].visible); updateBundleStats(i); });
		tr.append("td").attr("class", "visibilityColumn");
		tr.append("td").text(data[d].bundleTitle).attr("class", "textColumn");
		tr.append("td").text(getSensibleDate(data[d].firstSeen));
		tr.append("td").text(getSensibleDate(data[d].lastSeen));
		tr.append("td").text(duration).attr("class", "numberColumn");
		if(duration > 0)
		{
			tr.append("td").text("$" + currencyFormatter(data[d].pyTotal / duration)).attr("class", "numberColumn");
		}
		else
		{
			tr.append("td").text("$" + currencyFormatter(data[d].pyTotal)).attr("class", "numberColumn");
		}
		tr.append("td").text(lead).attr("class", "numberColumn");
		if (lead > 0)
		{
			tr.append("td").text("$" + currencyFormatter(data[d].pyTotal / lead)).attr("class", "numberColumn");
		}
		else
		{
			tr.append("td").text("$0.00").attr("class", "numberColumn");
		}		
		tr.append("td").text("$" + currencyFormatter(data[d].pyTotal)).attr("class", "numberColumn");
	}
	
	tableTarget.append("p")
		.text("This table shows the frequency with which Humble Bundle promotions have occurred alongside the revenue raised for each.")
		.append("sup")
			.attr("class", "footnote")
		.append("a")
			//.attr("href", "#bottom")
			.text("1")
			.on("mouseover", function (d) {
				showTooltip(d3.event.pageX, d3.event.pageY, helpersWrapper.select("#foot1").html());
				})
			.on("mouseout", function (d) {
				hideTooltip();
				})
			.on("click", function (d) { showHelper("footnotes"); return false; });
	tableTarget.append("p")
		.html("<span class = 'indicatorIndie'>Red</span> represents \"indie\" bundles, <span class = 'indicatorNonIndie'>Blue</span> represents \"non-indie\" bundles and debut promotions, <span class = 'indicatorAndroid'>Green</span> represents Android and mobile bundles, <span class = 'indicatorMojam'>Mustard</span> represents Mojam events, <span class = 'indicatorEbook'>Purple</span> represents ebook, audiobook and music bundles.");

}

var makeValueCharts = function(data)
{
	var chartTarget = d3.select("#overTimeValue");
	var coloursPrice = {"fullPriceFirst":"#3D7930", "fullPriceLast" : "#A2C180"};
	var coloursPriceGrey = {"Initial":"#3D7930", "Extra" : "#A2C180"};
	var currencyFormatter = d3.format(",.2f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var d2 = d3.layout.stack()(["fullPriceFirst", "fullPriceLast"].map(function(p) { return data.map(function(d) { return {x: d.firstSeen, y: +d[p], t: getShortTitle(d.bundleTitle), c: p}; }); }));

	removeLoadingImage(chartTarget);

	chartTarget.append("a")
		.attr("class", "chartTitle")
		.attr("href", "#overTimeValue")
		.append("h2")
		.text("Separate Price Values Over Time");

	var valueChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");
		
	var valX = d3.time.scale.utc()
		.range([40, chartWidth-40])
		.domain([d3.min(data, function(d) {return d.firstSeen; }), d3.max(data, function(d) { return d.firstSeen; })]);
	
	var valAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
		.scale(valX);

	var valY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([0, d3.max(d2[d2.length - 1], function(d) { return d.y0 + d.y; })]);
	
	var valAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-60), 1)
		.ticks(5)
		.tickFormat(function (d) { return "$" + (d3.max(d2[d2.length - 1], function(d) { return d.y0 + d.y; }) - d); })
		.scale(valY);

	valueChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(5, " + (chartHeight - 1) + ")")
		.call(valAxisX)
	valueChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(35, " + (chartHeight - 1) + ")")
		.append("rect")
		.attr("x", "0")
		.attr("y", "0")
		.attr("width", chartWidth-60)
		.attr("height", 1);
	valueChart.append("g")
		.attr("class", "y axis")
		.attr("transform", "translate(" + 35 + ", 0)")
		.call(valAxisY);
		
	var prices = valueChart.selectAll("g.price")
		.data(d2)
		.enter().append("svg:g")
		.attr("class", "price")
		.attr("transform", "translate(0," + chartHeight + ")");

	var rect = prices.selectAll("rect")
		.data(Object)
		.enter().append("svg:rect")
		.attr("class", function(d) { return "bar bar" + d.c; })
		.attr("x", function(d) { return valX(d.x); })
		.attr("y", function(d) { return -valY(d.y); })
		.style("fill", function(d) { return coloursPrice[d.c]; })
		.attr("height", function(d) { return valY(d.y - d.y0); })
		.attr("width", 10)//x.rangeBand());
		.on("mouseover", function (d) { dimInPrice(d.c); showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicator" + (d.c == "fullPriceFirst" ? "Initial" : "Extra") + "Games'>" + (d.c == "fullPriceFirst" ? "Initial" : "Extra") + ": $" + (d.y - d.y0) + "</p>");})
		.on("mouseout", function (d) { dimOut(d.c); hideTooltip(); })
		
	var labels = prices.selectAll("text")
		.data(Object)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + valX(d.x) + ", 25) rotate(90)"; })
		.style("opacity", "0.5") //This is a super dodgey way of hiding that the labels are rendered twice (once for each set) >_<
		.text(function (d) { return d.t; });
		
	chartTarget.append("p").text("This graph shows the separate price value across the displayed promotions.")
		.append("sup")
			.attr("class", "footnote")
		.append("a")
			//.attr("href", "#bottom")
			.text("4")
			.on("mouseover", function (d) {
				showTooltip(d3.event.pageX, d3.event.pageY, helpersWrapper.select("#foot4").html());
				})
			.on("mouseout", function (d) {
				hideTooltip();
				})
			.on("click", function (d) { showHelper("footnotes"); return false; });
	chartTarget.append("p").html("<span class = 'indicatorInitialGames'>Dark Green</span> represents the initial separate price value at the start of a promotion, <span class = 'indicatorExtraGames'>Light Green</span> represents the final separate price value at the end of the promotion or the latest value for in-progress ones.");
	
}

var makeRevenueChart = function (data)
{

	var chartTarget = d3.select("#overTimeRevenue");
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	removeLoadingImage(chartTarget);
	
	chartTarget.append("a")
		.attr("class", "chartTitle")
		.attr("href", "#overTimeRevenue")
		.append("h2")
		.text("Revenue Over Time");

	var pyChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");
		
	var pyX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([d3.min(data, function(d) {return d.firstSeen; }), d3.max(data, function(d) { return d.firstSeen; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var pyAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
//		.ticks(20)
		.scale(pyX);

	pyMax = d3.max(data, function (d) { return d3.max([+d.pyWin, +d.pyMac, +d.pyLin]); });
	var pyY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([pyMax, 0]);
	
	var pyAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-90), 1)
		.ticks(5)
		.tickFormat(function (d) { return "$" + currencyFormatter(d); })
		.scale(pyY);

	pyChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(5, " + (chartHeight) + ")")
		.call(pyAxisX)		
		
	pyChart.append("g")
		.attr("class", "y axis")
		.attr("transform", "translate(" + 80 + ", 0)")
		.call(pyAxisY);
	var pyLineLin = d3.svg.line()
		.x(function(d) { return pyX(d.firstSeen); })
		.y(function(d,i) { return pyY(d.pyLin); })
		.defined(function(d) { if (isNaN(d.pyLin) || d.pyLin == null) { return false; } else { return true; } });
	var pyLineMac = d3.svg.line()
		.x(function(d) { return pyX(d.firstSeen); })
		.y(function(d,i) { return pyY(d.pyMac); })
		.defined(function(d) { if (isNaN(d.pyMac) || d.pyMac == null) { return false; } else { return true; } });
	var pyLineWin = d3.svg.line()
		.x(function(d) { return pyX(d.firstSeen); })
		.y(function(d,i) { return pyY(d.pyWin); })
		.defined(function(d) { if (isNaN(d.pyWin) || d.pyWin == null) { return false; } else { return true; } });
	var lines = pyChart
		.append("g")
		.attr("class", "lines");
	var pyL = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Lin"]; })
		.attr("d", pyLineLin(data));
	var pyM = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Mac"]; })
		.attr("d", pyLineMac(data));
	var pyW = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Win"]; })
		.attr("d", pyLineWin(data));

	var pyH = pyChart.append("g")
		.attr("class", "hoverTargets")

	pyH.selectAll("rect").data(data).enter().append("rect")
		.attr("x", function(d) { return pyX(d.firstSeen) - 5; })
		.attr("y", 0)
		.attr("width", 10)
		.attr("height", chartHeight)
		.on("mouseover", function(d) { showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicatorMac'>Mac: " + (!(d.pyLin + d.pyMac + d.pyWin) ? "?" : "$" + currencyFormatter(d.pyMac)) + "</p><p class = 'indicatorWin'>Win: " + (!(d.pyLin + d.pyMac + d.pyWin) ? "?" : "$" + currencyFormatter(d.pyWin)) + "</p><p class = 'indicatorLin'>Lin: " + (!(d.pyLin + d.pyMac + d.pyWin) ? "?" : "$" + currencyFormatter(d.pyLin)) + "</p>"); })
		.on("mouseout", function(d) { hideTooltip(); });

	var labels = pyChart
		.append("g")
		.attr("class", "labels");

	var label = labels.selectAll("g.py")
		.data(data)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + (pyX(d.firstSeen) - 3) + ", " + (chartHeight + 25) + ") rotate(90)"; })
		.text(function (d) { return getShortTitle(d.bundleTitle); });

	chartTarget.append("p").html("This graph shows the revenue from each platform for the selected promotions.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows.");
}

var makePurchaseChart = function (data)
{
	var chartTarget = d3.select("#overTimePurchases");
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	removeLoadingImage(chartTarget);

	chartTarget.append("a")
		.attr("class", "chartTitle")
		.attr("href", "#overTimePurchases")
		.append("h2")
		.text("Purchases Over Time");

	var puChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");
		
	var puX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([d3.min(data, function(d) {return d.firstSeen; }), d3.max(data, function(d) { return d.firstSeen; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var puAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
//		.ticks(20)
		.scale(puX);

	puMax = d3.max(data, function (d) { return d3.max([+d.puWin, +d.puMac, +d.puLin]); });
	var puY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([puMax, 0]);
	
	var puAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-90), 1)
		.ticks(5)
		.tickFormat(function (d) { return currencyFormatter(d); })
		.scale(puY);


	puChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(5, " + (chartHeight) + ")")
		.call(puAxisX);

	puChart.append("g")
		.attr("class", "y axis")
		.attr("transform", "translate(" + 80 + ", 0)")
		.call(puAxisY);
	var puLineLin = d3.svg.line()
		.x(function(d) { return puX(d.firstSeen); })
		.y(function(d,i) { return puY(d.puLin); })
		.defined(function(d) { if (isNaN(d.puLin) || d.puLin == null) { return false; } else { return true; } });
	var puLineMac = d3.svg.line()
		.x(function(d) { return puX(d.firstSeen); })
		.y(function(d,i) { return puY(d.puMac); })
		.defined(function(d) { if (isNaN(d.puMac) || d.puMac == null) { return false; } else { return true; } });
	var puLineWin = d3.svg.line()
		.x(function(d) { return puX(d.firstSeen); })
		.y(function(d,i) { return puY(d.puWin); })
		.defined(function(d) { if (isNaN(d.puWin) || d.puWin == null) { return false; } else { return true; } });
	var lines = puChart
		.append("g")
		.attr("class", "lines");
	var puL = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Lin"]; })
		.attr("d", puLineLin(data));
	var puM = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Mac"]; })
		.attr("d", puLineMac(data));
	var puW = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Win"]; })
		.attr("d", puLineWin(data));

	var puH = puChart.append("g")
		.attr("class", "hoverTargets")

	puH.selectAll("rect").data(data).enter().append("rect")
		.attr("x", function(d) { return puX(d.firstSeen) - 5; })
		.attr("y", 0)
		.attr("width", 10)
		.attr("height", chartHeight)

		.on("mouseover", function(d) { showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicatorMac'>Mac: " + (!(d.puLin + d.puMac + d.puWin) ? "?" : "$" + currencyFormatter(d.puMac)) + "</p><p class = 'indicatorWin'>Win: " + (!(d.puLin + d.puMac + d.puWin) ? "?" : "$" + currencyFormatter(d.puWin)) + "</p><p class = 'indicatorLin'>Lin: " + (!(d.puLin + d.puMac + d.puWin) ? "?" : "$" + currencyFormatter(d.puLin)) + "</p>"); })
		.on("mouseout", function(d) { hideTooltip(); });

	var labels = puChart
		.append("g")
		.attr("class", "labels");

	var label = labels.selectAll("g.pu")
		.data(data)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + (puX(d.firstSeen) - 3) + ", " + (chartHeight + 25) + ") rotate(90)"; })
		.text(function (d) { return getShortTitle(d.bundleTitle); });

	chartTarget.append("p").html("This graph shows the purchase count for each platform for the selected promotions.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows.");
}

var makeAverageChart = function (data)
{
	var chartTarget = d3.select("#overTimeAverages");
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333", "All" : "#ff0000"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",2f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	removeLoadingImage(chartTarget);
	
	chartTarget.append("a")
		.attr("class", "chartTitle")
		.attr("href", "#overTimeAverages")
		.append("h2")
		.text("Averages Over Time");

	var avChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");
		
	var avX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([d3.min(data, function(d) {return d.firstSeen; }), d3.max(data, function(d) { return d.firstSeen; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var avAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
//		.ticks(20)
		.scale(avX);

	avMax = d3.max(data, function (d) { return d3.max([+d.avWin, +d.avMac, +d.avLin]); });
	var avY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([avMax, 0]);
	
	var avAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-90), 1)
		.ticks(5)
		.tickFormat(function (d) { return "$" + currencyFormatter(d); })
		.scale(avY);
		


	avChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(5, " + (chartHeight - 1) + ")")
		.call(avAxisX);

	avChart.append("g")
		.attr("class", "y axis")
		.attr("transform", "translate(" + 80 + ", 0)")
		.call(avAxisY);
	var avLineLin = d3.svg.line()
		.x(function(d) { return avX(d.firstSeen); })
		.y(function(d,i) { return avY(d.avLin); })
		.defined(function(d) { if (isNaN(d.avLin) || d.avLin == null) { return false; } else { return true; } });
	var avLineMac = d3.svg.line()
		.x(function(d) { return avX(d.firstSeen); })
		.y(function(d,i) { return avY(d.avMac); })
		.defined(function(d) { if (isNaN(d.avMac) || d.avMac == null) { return false; } else { return true; } });
	var avLineWin = d3.svg.line()
		.x(function(d) { return avX(d.firstSeen); })
		.y(function(d,i) { return avY(d.avWin); })
		.defined(function(d) { if (isNaN(d.avWin) || d.avWin == null) { return false; } else { return true; } });
	var avLineAll = d3.svg.line()
		.x(function(d) { return avX(d.firstSeen); })
		.y(function(d,i) { return avY(d.avAll); })
		.defined(function(d) { if (isNaN(d.avAll) || d.avAll == null) { return false; } else { return true; } });
	var avAreaAll = d3.svg.area()
		.x(function(d) { return avX(d.firstSeen); })
		.y0(chartHeight)
		.y1(function(d,i) { return avY(d.avAll); })
		.defined(function(d) { if (isNaN(d.avAll) || d.avAll == null) { return false; } else { return true; } });
	var lines = avChart
		.append("g")
		.attr("class", "lines");
	var avAf = lines.append("svg:path")
		.style("fill", "rgba(255,0,0,0.1)")
		.attr("d", avAreaAll(data));
	var avL = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Lin"]; })
		.attr("d", avLineLin(data));
	var avM = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Mac"]; })
		.attr("d", avLineMac(data));
	var avW = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["Win"]; })
		.attr("d", avLineWin(data));
	var avA = lines.append("svg:path")
		.style("stroke", function(d) { return coloursPlatform["All"]; })
		.attr("d", avLineAll(data));

	var avH = avChart.append("g")
		.attr("class", "hoverTargets")

	avH.selectAll("rect").data(data).enter().append("rect")
		.attr("x", function(d) { return avX(d.firstSeen) - 5; })
		.attr("y", 0)
		.attr("width", 10)
		.attr("height", chartHeight)
		.on("mouseover", function(d) { showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicatorAll'>All: " + (!(d.avLin + d.avMac + d.avWin) ? "?" : "$" + currencyFormatter(d.avAll)) + "</p><p class = 'indicatorMac'>Mac: " + (!(d.avLin + d.avMac + d.avWin) ? "?" : "$" + currencyFormatter(d.avMac)) + "</p><p class = 'indicatorWin'>Win: " + (!(d.avLin + d.avMac + d.avWin) ? "?" : "$" + currencyFormatter(d.avWin)) + "</p><p class = 'indicatorLin'>Lin: " + (!(d.avLin + d.avMac + d.avWin) ? "?" : "$" + currencyFormatter(d.avLin)) + "</p>"); })
		.on("mouseout", function(d) { hideTooltip(); });

	var labels = avChart
		.append("g")
		.attr("class", "labels");

	var label = labels.selectAll("g.av")
		.data(data)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + (avX(d.firstSeen) - 3) + ", " + (chartHeight + 25) + ") rotate(90)"; })
		.text(function (d) { return getShortTitle(d.bundleTitle); });

	chartTarget.append("p").html("This graph shows the average purchase price for each platform for the selected promotions.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows, <span class = 'indicatorAll'>Bright red</span> represents the cross-platform average.<br />The <span class = 'indicatorAvgArea'>pink</span> filled area highlights values below the cross-platform average.");
}

var makeBundleStats = function(bundle, data, id)
{
	var pieWidth = 122;
	var pieHeight = pieWidth;
	var pieRadius = pieWidth / 2 - 10

	var coloursPrice = {"fullPriceFirst":"#3D7930", "fullPriceLast" : "#A2C180"};
	var coloursPriceGrey = {"fullPriceFirst":"#3D7930", "fullPriceLast" : "#A2C180"};
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",.2f");
	var thousandsFormatter = d3.format(",f");
	
	removeLoadingImage(bundle);
	var h2 = bundle.insert("a", ":first-child")
			.attr("class", "bundleTitle")
			.attr("href", "#" + (id ? id : getShortTitle(data.bundleTitle)))
		.append("h2", ":first-child")
			.text(data.bundleTitle);
	bundle.append("p")
			.text(getSensibleDate(data.firstSeen) + " to " + getSensibleDate(data.lastSeen) + " (" + getDaysBetween(data.firstSeen, data.lastSeen) + " days)")
		.append("sup")
			.attr("class", "footnote")
		.append("a")
			//.attr("href", "#bottom")
			.text("1")
			.on("mouseover", function (d) {
				showTooltip(d3.event.pageX, d3.event.pageY, helpersWrapper.select("#foot1").html());
				})
			.on("mouseout", function (d) {
				hideTooltip();
				})
			.on("click", function (d) { showHelper("footnotes"); return false; });
	pyUL = bundle.append("ul");
	//TODO: Make this a function that we can call for each desired platform?
	pyUL.append("li")
			.text("Total Payments:")
		.append("span")
			.attr("class", "statValue")
			.text("$" + currencyFormatter(data.pyTotal));
	pyUL.append("li")
			.attr("class", "value valueLin")
			.text("Linux Payments:")
		.append("span")
			.attr("class", "statValue indicatorLin")
			.text(!(data.pyLin + data.pyMac + data.pyWin) ? "?" : "$" + currencyFormatter(data.pyLin));
	pyUL.append("li")
			.attr("class", "value valueMac")
			.text("Mac OS Payments:")
		.append("span")
			.attr("class", "statValue indicatorMac")
			.text(!(data.pyLin + data.pyMac + data.pyWin) ? "?" : "$" + currencyFormatter(data.pyMac));
	pyUL.append("li")
			.attr("class", "value valueWin")
			.text("Windows Payments:")
		.append("span")
			.attr("class", "statValue indicatorWin")
			.text(!(data.pyLin + data.pyMac + data.pyWin) ? "?" : "$" + currencyFormatter(data.pyWin));

	pyChart = bundle.append("svg")
		.attr("width", pieWidth)
		.attr("height", pieHeight)
	.append("g")
		.attr("transform", "translate(" + (pieWidth / 2) + "," + (pieHeight / 2) + ")");

	pyPie = d3.layout.pie()
		.sort(null)
		.value(function(d) { return d.v; } );
	pyArc = d3.svg.arc()
		.outerRadius(pieRadius - 10)
		.innerRadius(0);
	pyArcOver = d3.svg.arc()
		.outerRadius(pieRadius - 5)
		.innerRadius(0);
		
		var g = pyChart.selectAll(".arc")
			.data(pyPie(data.pyCumulative))
			.enter().append("g")
			.attr("class", "arc");
		g.append("path")
			.attr("d", pyArc)
			.attr("class", function(d) { return "slice slice" + d.data.p; })
			.style("fill", function(d) { return coloursPlatform[d.data.p]; })
			.style("stroke", "#ffffff")
	        .on("mouseover", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArcOver);
				dimIn(d.data.p);
				showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicator" + d.data.p + "'>" + d.data.p + ": " + d.data.pc + "%</p>");
				})
	        .on("mouseout", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArc);
				dimOut(d.data.p);
				hideTooltip();
				});

	bundle.append("br")
		.attr("class", "clearfix");

	avUL = bundle.append("ul");
	//TODO: Make this a function that we can call for each desired platform?
	avUL.append("li")
			.text("Average Payment:")
		.append("span")
			.attr("class", "statValue indicatorAll")
			.text("$" + currencyFormatter(data.avAll));
	avUL.append("li")
			.attr("class", "value valueLin")
			.text("Linux Average:")
		.append("span")
			.attr("class", "statValue indicatorLin")
			.text(!(data.avLin + data.avMac + data.avWin) ? "?" : "$" + currencyFormatter(data.avLin));
	avUL.append("li")
			.attr("class", "value valueMac")
			.text("Mac OS Average:")
		.append("span")
			.attr("class", "statValue indicatorMac")
			.text(!(data.avLin + data.avMac + data.avWin) ? "?" : "$" + currencyFormatter(data.avMac));
	avUL.append("li")
			.attr("class", "value valueWin")
			.text("Windows Average:")
		.append("span")
			.attr("class", "statValue indicatorWin")
			.text(!(data.avLin + data.avMac + data.avWin) ? "?" : "$" + currencyFormatter(data.avWin));


	var avX = d3.scale.ordinal()
		.rangeRoundBands([0, pieWidth - 40], 0.1)
		.domain(data.avCumulative.map(function(d) { return d.p; }));
	var avY = d3.scale.linear()
		.range([pieHeight - 30, 0])
		.domain([0, data.avMax]);
	
	var avAxisX = d3.svg.axis()
		.orient("top")
		.tickPadding(3)
		.tickSize(1)
		.scale(avX);

	var avAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(1)
		.tickSize(4, 1)
		.ticks(3)
		.tickFormat(function (d) { return "$" + d; })
		.scale(avY);


	avChart = bundle.append("svg")
		.attr("width", pieWidth)
		.attr("height", pieHeight)
		.attr("class", "bundleAverageChart");
	avChart.append("g")
		.attr("transform", "translate(" + 30 + ", 10)")
		.selectAll(".bar")
		.data(data.avCumulative)
		.enter().append("rect")
			.attr("class", function (d) { return "bar bar" + d.p; })
			.style("fill", function(d) { return coloursPlatform[d.p]; })
			.attr("x", function(d) { return avX(d.p); })
			.attr("width", avX.rangeBand())
			.attr("y", function(d) { return avY(d.v); })
			.attr("height", function(d) { return (pieHeight - 30) - avY(d.v); })
			.on("mouseover", function (d) { dimIn(d.p); showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicator" + d.p + "'>" + d.p + ": " + (d.diff > 0 ? "+" : "-") + "$" + Math.abs(d.diff) + (d.diff > 0 ? " above" : " below") + " the cross-platform average</p>"); })
			.on("mouseout", function (d) { dimOut(d.p); hideTooltip(); })
	avLine = d3.svg.line()
			.x(function(d, i) { return d; })
			.y(function(d, i) { return avY(data.avAll); });
	avChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(30, " + (pieHeight - 20) + ")")
		.call(avAxisX);
	avChart.append("g")
		.attr("class", "y axis")
		.attr("transform", "translate(" + 30 + ", 9.5)")
		.call(avAxisY);

	avChart.append("g")
		.attr("transform", "translate(" + 30 + ", 9.5)")
		.append("svg:path")
		.attr("class", "averageLine")
		.style("stroke", "#FF0000")
		.attr("d", avLine([0, pieWidth - 40]));

	bundle.append("br")
		.attr("class", "clearfix");

	puUL = bundle.append("ul");
	//TODO: Make this a function that we can call for each desired platform?
	puUL.append("li")
			.text("Total Purchases:")
		.append("span")
			.attr("class", "value statValue")
			.text(thousandsFormatter(data.puTotal));
	puUL.append("li")
			.attr("class", "value valueLin")
			.text("Linux Purchases:")
		.append("span")
			.attr("class", "statValue indicatorLin")
			.text(!(data.puLin + data.puMac + data.puWin) ? "?" : thousandsFormatter(data.puLin));
	puUL.append("li")
			.attr("class", "value valueMac")
			.text("Mac OS Purchases:")
		.append("span")
			.attr("class", "statValue indicatorMac")
			.text(!(data.puLin + data.puMac + data.puWin) ? "?" : thousandsFormatter(data.puMac));
	puUL.append("li")
			.attr("class", "value valueWin")
			.text("Windows Purchases:")
		.append("span")
			.attr("class", "statValue indicatorWin")
			.text(!(data.puLin + data.puMac + data.puWin) ? "?" : thousandsFormatter(data.puWin));

	puChart = bundle.append("svg")
		.attr("width", pieWidth)
		.attr("height", pieHeight)
	.append("g")
		.attr("transform", "translate(" + (pieWidth / 2) + "," + (pieHeight / 2) + ")");

		var g = puChart.selectAll(".arc")
			.data(pyPie(data.puCumulative))
			.enter().append("g")
			.attr("class", "arc");
		g.append("path")
			.attr("d", pyArc)
			.attr("class", function(d) { return "slice slice" + d.data.p; })
			.style("fill", function(d) { return coloursPlatform[d.data.p]; })
			.style("stroke", "#ffffff")
	        .on("mouseover", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArcOver);
				dimIn(d.data.p);
				showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicator" + d.data.p + "'>" + d.data.p + ": " + d.data.pc + "%</p>");
				})
	        .on("mouseout", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArc);
				dimOut(d.data.p);
				hideTooltip();
				});
			
	bundle.append("br")
		.attr("class", "clearfix");

	if (data.fullPriceLast > 0)
	{
	priceUL = bundle.append("ul");
	//TODO: Make this a function that we can call for each desired platform?
	priceUL.append("li")
			.text("Price of Games Separately:")
		.append("span")
			.attr("class", "value statValue")
			.text(!(data.fullPriceLast) ? "?" : "$" + currencyFormatter(data.fullPriceLast))
			.append("sup")
				.attr("class", "footnote")
			.append("a")
				//.attr("href", "#bottom")
				.text("4")
				.on("mouseover", function (d) {
					showTooltip(d3.event.pageX, d3.event.pageY, helpersWrapper.select("#foot4").html());
					})
				.on("mouseout", function (d) {
					hideTooltip();
					})
			.on("click", function (d) { showHelper("footnotes"); return false; });
	priceUL.append("li")
			.attr("class", "value valuefullPriceFirst")
			.text("Initial Games Value:")
		.append("span")
			.attr("class", "value statValue indicatorInitialGames")
			.text(!(data.fullPriceLast) ? "?" : "$" + currencyFormatter(data.fullPriceFirst));
	priceUL.append("li")
			.attr("class", "value valuefullPriceLast")
			.text("Extra Games Value:")
		.append("span")
			.attr("class", "value statValue indicatorExtraGames")
			.text(!(data.fullPriceLast) ? "?" : "$" + currencyFormatter(data.fullPriceLast - data.fullPriceFirst));

	priceChart = bundle.append("svg")
		.attr("width", pieWidth)
		.attr("height", pieHeight)
	.append("g")
		.attr("transform", "translate(" + (pieWidth / 2) + "," + (pieHeight / 2) + ")");

		var g = priceChart.selectAll(".arc")
			.data(pyPie(data.priceCumulative))
			.enter().append("g")
			.attr("class", "arc");
		g.append("path")
			.attr("d", pyArc)
			.attr("class", function(d) { return "slice slice" + d.data.p; })
			.style("fill", function(d) { return coloursPrice[d.data.p]; })
			.style("stroke", "#ffffff")
	        .on("mouseover", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArcOver);
				dimInPrice(d.data.p);
				showTooltip(d3.event.pageX, d3.event.pageY, "<p class = 'indicator" + (d.c == "fullPriceFirst" ? "Initial" : "Extra") + "Games'>" + (d.data.p == "fullPriceFirst" ? "Initial" : "Extra") + ": " + d.data.pc + "%</p>");
				})
	        .on("mouseout", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArc);
				dimOut(d.data.p);
				hideTooltip();
				});
			
	bundle.append("br")
		.attr("class", "clearfix");

	}

	bundle.append("p")
		.text("Data sourced: " + data.lastUpdated + "")
		.append("sup")
			.attr("class", "footnote")
		.append("a")
			//.attr("href", "#bottom")
			.text("2")
			.on("mouseover", function (d) {
				showTooltip(d3.event.pageX, d3.event.pageY, helpersWrapper.select("#foot2").html());
				})
			.on("mouseout", function (d) {
				hideTooltip();
				})
			.on("click", function (d) { showHelper("footnotes"); return false; });
	if (!isNaN(data.variance))
	{
		bundle.append("p")
			.text("Variance: " + data.variance + " purchases unaccounted for")
			.append("sup")
				.attr("class", "footnote")
			.append("a")
				//.attr("href", "#bottom")
				.text("3")
				.on("mouseover", function (d) {
					showTooltip(d3.event.pageX, d3.event.pageY, helpersWrapper.select("#foot3").html());
					})
				.on("mouseout", function (d) {
					hideTooltip();
					})
				.on("click", function (d) { showHelper("footnotes"); return false; });
	}
};
