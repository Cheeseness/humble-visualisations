var dayLength = 24 * 60 * 60 * 1000;

function getShortTitle(title)
{
	return title.replace(/(The\sHumble\sBundle\sfor\s)/gi, "").replace(/(The\sHumble\sBundle\swith\s)/gi, "").replace(/(Humble\sBundle\swith\s)/gi, "").replace(/(The\sHumble\sBundle\s)/gi, "").replace(/(The\sHumble\s)/gi, "").replace(/(Humble\s)/gi, "").replace(/(\sBundle)/gi, "").replace(/(\sDebut)/gi,"");
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
	newDate = new Date();
	newDate.setUTCFullYear(string[0]);
	newDate.setUTCMonth(string[1] - 1);
	newDate.setUTCDate(string[2]);
	newDate.setUTCHours(string[3]);
	newDate.setUTCMinutes(string[4]);
	newDate.setUTCSeconds(string[5]);
	return newDate
}

function showBundle(bundleTitle)
{	

	var data = [];
	d3.json("getdata.php", function(result) {
		data = [];
		avMax = []
		i = 0;
		for (row in result.data)
		{
			//TODO: If there's more than an hour's cap between this record and the last, we need to insert an empty one so that the line graphs know to break.

			data[i] = result.data[row];
			data[i].lastUpdated = buildDate(data[i].lastUpdated);
			
			if (!(data[i].rvLin > 0))
			{
				data[i].rvLin = null;
			}
			
			data[i].pyCumulative = [];
			data[i].pyCumulative.push({'v' : parseFloat(data[i].pyMac), 'p' : "Mac"});
			data[i].pyCumulative.push({'v' : parseFloat(data[i].pyWin), 'p' : "Win"});
			data[i].pyCumulative.push({'v' : parseFloat(data[i].pyLin), 'p' : "Lin"});
			data[i].avCumulative = [];
			data[i].avCumulative.push({'v' : parseFloat(data[i].avMac), 'p' : "Mac"});
			data[i].avCumulative.push({'v' : parseFloat(data[i].avWin), 'p' : "Win"});
			data[i].avCumulative.push({'v' : parseFloat(data[i].avLin), 'p' : "Lin"});
			avMax.push(d3.max(data[i].avCumulative, function(d) { return d.v; }));
			data[i].puCumulative = [];
			data[i].puCumulative.push({'v' : parseFloat(data[i].puMac), 'p' : "Mac"});
			data[i].puCumulative.push({'v' : parseFloat(data[i].puWin), 'p' : "Win"});
			data[i].puCumulative.push({'v' : parseFloat(data[i].puLin), 'p' : "Lin"});
			data[i].priceCumulative = [];
			data[i].priceCumulative.push({'v' : parseFloat(data[i].fullPriceFirst), 'p' : "Initial"});
			data[i].priceCumulative.push({'v' : parseFloat(data[i].fullPriceLast) - parseFloat(data[i].fullPriceFirst), 'p' : "Extra"});
			
			data.bundleTitle = result.bundleTitle;
			data.type = "non-indie";
			if (result.bundleTitle.toLowerCase().indexOf("android") >= 0)
			{
				data.type = "android";
			}
			else if (result.bundleTitle.toLowerCase().indexOf("indie") >= 0)
			{
				data.type = "indie";
			}
			else if (result.bundleTitle.toLowerCase().indexOf("mojam") >= 0)
			{
				data.type = "mojam";
			}
			else if (result.bundleTitle.toLowerCase().indexOf("debut") >= 0)
			{
				data.type = "debut";
			}
			else if (result.bundleTitle.toLowerCase().indexOf("music") >= 0)
			{
				data.type = "music";
			}
			else if (result.bundleTitle.toLowerCase().indexOf("ebook") >= 0)
			{
				data.type = "ebook";
			}
			else if (result.bundleTitle.toLowerCase().indexOf("mobile") >= 0)
			{
				data.type = "mobile";
			}
			
			i++;
		}
		
		avMax = d3.max(avMax);
		
		makeRevenueDiffChart(data);
		makeValueChart(data);
		makeRevenueChart(data);
		makePurchaseChart(data);
		makeAverageChart(data);
	});
}

var makeValueChart = function(data)
{
	var chartTarget = d3.select("#chartPlayground").append("div").attr("class", "widestats");
	var coloursPrice = {"fullPriceFirst":"#3D7930", "fullPriceExtra" : "#A2C180"};
	var coloursPriceGrey = {"Initial":"#3D7930", "Extra" : "#A2C180"};
	var currencyFormatter = d3.format(",.2f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var d2 = d3.layout.stack()(["firstPrice", "fullPrice"].map(function(p) { console.log(p); return data.map(function(d) { return {x: d.lastUpdated, y: +d[p], t: getShortTitle(data.bundleTitle), c: p}; }); }));
	console.log("Making value chart");

	var valueChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");
		
	var valX = d3.time.scale.utc()
		.range([40, chartWidth-40])
		.domain([d3.min(data, function(d) {return d.lastUpdated; }), d3.max(data, function(d) { return d.lastUpdated; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var valAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
		//.ticks(d3.time.day.utc, 1)
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
		.attr("transform", "translate(0, " + (chartHeight) + ")")
		.call(valAxisX)
/*		.append("rect")
		.attr("x", "0")
		.attr("y", "0")
		.attr("width", chartWidth-60)
		.attr("height", 1);
*/	valueChart.append("g")
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
		.attr("x", function(d) { return valX(d.x); })
		.attr("y", function(d) { return -valY(d.y0) - valY(d.y); })
		.style("fill", function(d) { return coloursPrice[d.c]; })
		.attr("height", function(d) { return valY(d.y); })
		.attr("width", 10)//x.rangeBand());
/*		
	var labels = prices.selectAll("text")
		.data(Object)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + valX(d.x) + ", 3) rotate(90)"; })
		.text(function (d) { return d.t; });
*/
}


var makeRevenueDiffChart = function(data)
{
	var chartTarget = d3.select("#chartPlayground").append("div").attr("class", "widestats");
	var coloursPlatform = {"pyLinDiff":"#333388", "pyMacDiff" : "#338833", "pyWinDiff" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var d2 = d3.layout.stack()(["pyLinDiff", "pyMacDiff", "pyWinDiff"].map(function(p) { console.log(p); return data.map(function(d) { return {x: d.lastUpdated, y: +d[p], t: getShortTitle(data.bundleTitle), c: p, a: d.pyLinDiff + d.pyMacDiff + d.pyWinDiff}; }); }));
	console.log("Making value chart");

	var valueChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");

	var earliestDate = d3.min(data, function(d) {return d.lastUpdated; });
	var twoWeeks = earliestDate + (2 * d3.time.week)
	console.log(earliestDate, twoWeeks)
		
	var valX = d3.time.scale.utc()
		.range([40, chartWidth-40])
		.domain([d3.min(data, function(d) {return d.lastUpdated; }), d3.max(data, function(d) { return d.lastUpdated; })]);
		//TODO: This should probably jsut be set to two weeks (to keep width and spacing consistent)
	//tlX.ticks(d3.time.month.utc, 1);
	
	var valAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
		//.ticks(d3.time.day.utc, 1)
		.scale(valX);

	var valY = d3.scale.linear()
		.rangeRound([0, chartHeight])
		.domain([0, d3.max(d2[d2.length - 1], function(d) { return d.y0 + d.y; })]);
	
	var valAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-60), 1)
		.ticks(5)
		.tickFormat(function (d) { return "$" + currencyFormatter(d); })
		.scale(valY);

	valueChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(0, " + (chartHeight) + ")")
		.call(valAxisX)
/*		.append("rect")
		.attr("x", "0")
		.attr("y", "0")
		.attr("width", chartWidth-60)
		.attr("height", 1);
*/	valueChart.append("g")
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
		.attr("x", function(d) { return valX(d.x); })
		.attr("y", function(d) { return -valY(d.y0) - valY(d.y); })
		.style("fill", function(d) { return coloursPlatform[d.c]; })
		.attr("height", function(d) { return valY(d.y); })
		.attr("width", 4);
/*		
	var labels = prices.selectAll("text")
		.data(Object)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + valX(d.x) + ", 3) rotate(90)"; })
		.text(function (d) { return d.t; });
*/
}

var makeRevenueChart = function (data)
{
	var chartTarget = d3.select("#chartPlayground").append("div").attr("class", "widestats");
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var pyChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");

	console.log("Making revenue chart");
	var pyX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([d3.min(data, function(d) { return d.lastUpdated; }), d3.max(data, function(d) { return d.lastUpdated; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var pyAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
//		.ticks(20)
		.scale(pyX);

	pyMax = d3.max(data, function (d) { return d3.max([+d.pyWin, +d.pyMac, +d.pyLin]); });
	console.log(pyMax);
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
		.attr("class", "y axis")
		.attr("transform", "translate(" + 80 + ", 0)")
		.call(pyAxisY);

	pyChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(" + 0 + ", " + chartHeight + ")")
		.call(pyAxisX);
	var pyLineLin = d3.svg.line()
		.x(function(d) { return pyX(d.lastUpdated); })
		.y(function(d,i) { return pyY(d.pyLin); })
		.defined(function(d) { if (isNaN(d.pyLin) || d.pyLin == null) { return false; } else { return true; } });
	var pyLineMac = d3.svg.line()
		.x(function(d) { return pyX(d.lastUpdated); })
		.y(function(d,i) { return pyY(d.pyMac); })
		.defined(function(d) { if (isNaN(d.pyMac) || d.pyMac == null) { return false; } else { return true; } });
	var pyLineWin = d3.svg.line()
		.x(function(d) { return pyX(d.lastUpdated); })
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
/*
	var labels = pyChart
		.append("g")
		.attr("class", "labels");

	var label = labels.selectAll("g.py")
		.data(data)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + (pyX(d.lastUpdated) - 3) + ", " + (chartHeight + 5) + ") rotate(90)"; })
		.text(function (d) { return getShortTitle(data.bundleTitle); });
*/
}

var makePurchaseChart = function (data)
{
	var chartTarget = d3.select("#chartPlayground").append("div").attr("class", "widestats");
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var puChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");
		
	var puX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([d3.min(data, function(d) {return d.lastUpdated; }), d3.max(data, function(d) { return d.lastUpdated; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var puAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
//		.ticks(20)
		.scale(puX);

	puMax = d3.max(data, function (d) { return d3.max([+d.puWin, +d.puMac, +d.puLin]); });
	console.log(puMax);
	var puY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([puMax, 0]);
	
	var puAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-90), 1)
		.ticks(5)
		.tickFormat(function (d) { return "$" + currencyFormatter(d); })
		.scale(puY);

	puChart.append("g")
		.attr("class", "y axis")
		.attr("transform", "translate(" + 80 + ", 0)")
		.call(puAxisY);

	puChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(" + 0 + ", " + chartHeight + ")")
		.call(puAxisX);
	var puLineLin = d3.svg.line()
		.x(function(d) { return puX(d.lastUpdated); })
		.y(function(d,i) { return puY(d.puLin); });
	var puLineMac = d3.svg.line()
		.x(function(d) { return puX(d.lastUpdated); })
		.y(function(d,i) { return puY(d.puMac); });
	var puLineWin = d3.svg.line()
		.x(function(d) { return puX(d.lastUpdated); })
		.y(function(d,i) { return puY(d.puWin); });
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
/*
	var labels = puChart
		.append("g")
		.attr("class", "labels");

	var label = labels.selectAll("g.pu")
		.data(data)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + (puX(d.firstSeen) - 3) + ", " + (chartHeight + 5) + ") rotate(90)"; })
		.text(function (d) { return getShortTitle(data.bundleTitle); });
*/
}

var makeAverageChart = function (data)
{
	var chartTarget = d3.select("#chartPlayground").append("div").attr("class", "widestats");
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333", "All" : "#ff0000"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var avChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 110)
	.append("g")
		.attr("transform", "translate(0, "+ 10 + ")");
		
	var avX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([d3.min(data, function(d) {return d.lastUpdated; }), d3.max(data, function(d) { return d.lastUpdated; })]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var avAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
//		.ticks(20)
		.scale(avX);

	avMax = d3.max(data, function (d) { return d3.max([+d.avWin, +d.avMac, +d.avLin]); });
	console.log(avMax);
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
		.attr("class", "y axis")
		.attr("transform", "translate(" + 80 + ", 0)")
		.call(avAxisY);
	avChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(" + 0 + ", " + chartHeight +")")
		.call(avAxisX);
	var avLineLin = d3.svg.line()
		.x(function(d) { return avX(d.lastUpdated); })
		.y(function(d,i) { return avY(d.avLin); });
	var avLineMac = d3.svg.line()
		.x(function(d) { return avX(d.lastUpdated); })
		.y(function(d,i) { return avY(d.avMac); });
	var avLineWin = d3.svg.line()
		.x(function(d) { return avX(d.lastUpdated); })
		.y(function(d,i) { return avY(d.avWin); });
	var avLineAll = d3.svg.line()
		.x(function(d) { return avX(d.lastUpdated); })
		.y(function(d,i) { return avY(d.avAll); });
	var lines = avChart
		.append("g")
		.attr("class", "lines");
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
/*
	var labels = avChart
		.append("g")
		.attr("class", "labels");

	var label = labels.selectAll("g.av")
		.data(data)
		.enter().append("text")
		.attr("transform", function(d) { return "translate(" + (avX(d.firstSeen) - 3) + ", " + (chartHeight + 5) + ") rotate(90)"; })
		.text(function (d) { return getShortTitle(data.bundleTitle); });
*/
}

var makeBundleStats = function(data)
{
	var chartPlayground = d3.select("#chartPlayground");
	var pieWidth = 122;
	var pieHeight = pieWidth;
	var pieRadius = pieWidth / 2 - 10

	var coloursPrice = {"Initial":"#3D7930", "Extra" : "#A2C180"};
	var coloursPriceGrey = {"Initial":"#3D7930", "Extra" : "#A2C180"};
	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",.2f");


	console.log("showing stats for", data.bundleTitle);
	var bundle = chartPlayground.append("div")
		.attr("class", "bundle");
//		.attr("id", getShortName(bundleTitle));

	var h2 = bundle.insert("h2", ":first-child")
			.text(data.bundleTitle);
	bundle.append("p")
			.text(getSensibleDate(data.firstSeen) + " to " + getSensibleDate(data.lastSeen) + " (" + getDaysBetween(data.firstSeen, data.lastSeen) + " days)")
		.append("sup")
			.attr("class", "footnote")
		.append("a")
			.attr("href", "#bottom")
			.text("1");	
	
	pyUL = bundle.append("ul");
	//TODO: Make this a function that we can call for each desired platform?
	pyUL.append("li")
			.text("Total Payments:")
		.append("span")
			.attr("class", "statValue")
			.text("$" + currencyFormatter(data.pyTotal));
	pyUL.append("li")
			.text("Linux Payments:")
		.append("span")
			.attr("class", "statValue indicatorLin")
			.text("$" + currencyFormatter(data.pyLin));
	pyUL.append("li")
			.text("Mac OS Payments:")
		.append("span")
			.attr("class", "statValue indicatorMac")
			.text("$" + currencyFormatter(data.pyMac));
	pyUL.append("li")
			.text("Windows Payments:")
		.append("span")
			.attr("class", "statValue indicatorWin")
			.text("$" + currencyFormatter(data.pyWin));

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
			.style("fill", function(d) { return coloursPlatform[d.data.p]; })
			.style("stroke", "#ffffff")
	        .on("mouseover", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArcOver);
				})
	        .on("mouseout", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArc);
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
			.text("Linux Average:")
		.append("span")
			.attr("class", "statValue indicatorLin")
			.text("$" + currencyFormatter(data.avLin));
	avUL.append("li")
			.text("Mac OS Average:")
		.append("span")
			.attr("class", "statValue indicatorMac")
			.text("$" + currencyFormatter(data.avMac));
	avUL.append("li")
			.text("Windows Average:")
		.append("span")
			.attr("class", "statValue indicatorWin")
			.text("$" + currencyFormatter(data.avWin));


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
			.attr("class", "bar")
			.style("fill", function(d) { return coloursPlatform[d.p]; })
			.attr("x", function(d) { return avX(d.p); })
			.attr("width", avX.rangeBand())
			.attr("y", function(d) { return avY(d.v); })
			.attr("height", function(d) { return (pieHeight - 30) - avY(d.v); });
	avLine = d3.svg.line()
			.x(function(d, i) { return d; })
			.y(function(d, i) { return avY(data.avAll); });
	avChart.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(30, " + (pieHeight - 20) + ")")
		.call(avAxisX);
	avChart.append("g")
		.attr("class", "y axis")
		.attr("transform", "translate(" + 30 + ", 10)")
		.call(avAxisY);

	avChart.append("g")
		.attr("transform", "translate(" + 30 + ", 10)")
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
			.attr("class", "statValue")
			.text(currencyFormatter(data.puTotal));
	puUL.append("li")
			.text("Linux Purchases:")
		.append("span")
			.attr("class", "statValue indicatorLin")
			.text(currencyFormatter(data.puLin));
	puUL.append("li")
			.text("Mac OS Purchases:")
		.append("span")
			.attr("class", "statValue indicatorMac")
			.text(currencyFormatter(data.puMac));
	puUL.append("li")
			.text("Windows Purchases:")
		.append("span")
			.attr("class", "statValue indicatorWin")
			.text(currencyFormatter(data.puWin));

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
			.style("fill", function(d) { return coloursPlatform[d.data.p]; })
			.style("stroke", "#ffffff")
	        .on("mouseover", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArcOver);
				})
	        .on("mouseout", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArc);
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
			.attr("class", "statValue")
			.text(currencyFormatter(data.fullPriceLast));
	priceUL.append("li")
			.text("Initial Games Value:")
		.append("span")
			.attr("class", "statValue indicatorInitialGames")
			.text(currencyFormatter(data.fullPriceFirst));
	priceUL.append("li")
			.text("Extra Games Value:")
		.append("span")
			.attr("class", "statValue indicatorExtraGames")
			.text(currencyFormatter(data.fullPriceLast - data.fullPriceFirst));

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
			.style("fill", function(d) { return coloursPrice[d.data.p]; })
			.style("stroke", "#ffffff")
	        .on("mouseover", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArcOver);
				})
	        .on("mouseout", function(d) {
				d3.select(this).transition()
					.duration(100)
					.attr("d", pyArc);
				});
			
	bundle.append("br")
		.attr("class", "clearfix");
	}
};
