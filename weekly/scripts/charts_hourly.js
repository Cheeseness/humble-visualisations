 /*****************************************************************
 * IF YOU'RE READING THIS, THEN YOU'RE PROBABLY GOING TO JUDGE ME *
 *                                                                *
 * What you see here is a massively rushed quick-fix that was     *
 * implemented as a stopgap measure to keep the hourly stuff      *
 * going whilst I work on a full D3 rewrite of HumVis.            *
 *                                                                *
 * I'm not proud of any of this <3                                *
 *                                                                *
 *****************************************************************/

var dayLength = 24 * 60 * 60 * 1000;

function getShortTitle(title)
{
	return title.replace(/(The\sHumble\sBundle\sfor\s)/gi, "").replace(/(The\sHumble\sBundle\swith\s)/gi, "").replace(/(Humble\sBundle\swith\s)/gi, "").replace(/(The\sHumble\sBundle\s)/gi, "").replace(/(The\sHumble\s)/gi, "").replace(/(Humble\s)/gi, "").replace(/(\sBundle)/gi, "").replace(/(Bundle:\sPC\sand\s)/gi, "").replace(/(\sDebut)/gi,"");
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
	d3.json("getdata.php?bundle=" + bundleTitle, function(result) {
		console.log(bundleTitle);
		data = [];
		avMax = []
		firstRow = true;
		i = 0;
		lastDate = null;
		for (row in result.data)
		{

			if ((result.bundleTitle == "Humble Weekly Sale: Telltale Games") && (firstRow))
			{
				firstRow = false;
				continue;
			}
			//TODO: If there's more than an hour's cap between this record and the last, we need to insert an empty one so that the line graphs know to break.

			lastUpdated = buildDate(result.data[row].lastUpdated);
			if (lastDate != null)
			{
				console.log("Got last date" + (lastDate - lastUpdated))
				if (lastUpdated - lastDate > 4000000) //If we're a bit over an hour
				{
					console.log("Skipped data")
					lastDate.setHours(lastDate.getHours() + 1);
					data[i] = Array();
					data[i].lastUpdated = lastDate;
					data[i].pyLinDiff = null;
					data[i].pyMacDiff = null;
					data[i].pyWinDiff = null;
					data[i].puLinDiff = null;
					data[i].puMacDiff = null;
					data[i].puWinDiff = null;
					data[i].rvLinDiff = null;
					data[i].rvMacDiff = null;
					data[i].rvWinDiff = null;
					data[i].pyLin = null;
					data[i].pyMac = null;
					data[i].pyWin = null;
					data[i].puLin = null;
					data[i].puMac = null;
					data[i].puWin = null;
					data[i].rvLin = null;
					data[i].rvMac = null;
					data[i].rvWin = null;
					data[i].avLin = null;
					data[i].avMac = null;
					data[i].avWin = null;
					data[i].avAll = null;
					data[i].firstPrice = null;
					data[i].fullPrice = null;
					//TODO: Add in empty values for everything we care about.
					i++;
				}
			}

			data[i] = result.data[row];
			data[i].lastUpdated = lastUpdated;
			
			data[i].avCumulative = [];
			data[i].avCumulative.push({'v' : parseFloat(data[i].avMac), 'p' : "Mac"});
			data[i].avCumulative.push({'v' : parseFloat(data[i].avWin), 'p' : "Win"});
			data[i].avCumulative.push({'v' : parseFloat(data[i].avLin), 'p' : "Lin"});
			if ((data[i].avLin == null) && (data[i].avMac == null) && (data[i].avWin == null))
			{
				avMax.push(data[i].avAll);
			}
			else
			{
				avMax.push(d3.max(data[i].avCumulative, function(d) { return d.v; }));
			}

			
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
			lastDate = lastUpdated;
			i++;
		}

		avMax = d3.max(avMax);

		console.log(data);

		
		makeRevenueDiffChart(data);
		makePurchaseDiffChart(data);
		//makeValueChart(data);
		makeRevenueChart(data);
		makePurchaseChart(data);
		makeAverageChart(data);
	});
}

var makeValueChart = function(data)
{
	var chartTarget = d3.select("#chartPlayground").append("div").attr("class", "widestats");
	chartTarget.append("h2").html(data.bundleTitle + "<br />Hourly Separate Price Values");

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
		.attr("height", chartHeight + 30)
	.append("g")
		.attr("transform", "translate(0, "+ 10.5 + ")");
		
	chartTarget.append("p").html("This graph shows the change in separate price value across the duration of this promotion.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows.");

	var earliestDate = d3.min(data, function(d) {return d.lastUpdated; });
	var twoWeeks = new Date();
	twoWeeks.setTime(earliestDate.getTime() + (15 * 86400000)); //Well, that's a little more than a week (it's usually 14 days plus a little
		
	var valX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([earliestDate, twoWeeks]);
	//tlX.ticks(d3.time.month.utc, 1);
	
	var valAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
		//.ticks(d3.time.day.utc, 1)
		.scale(valX);
		
		var yMax = d3.max(d2[d2.length - 1], function(d) { return d.y0 + d.y; });
		console.log(yMax);
		if (yMax < 50)
		{
			yMax = 50;
		}
	var valY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([0, yMax]);
	
	var valAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-90), 1)
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
		.attr("transform", "translate(" + 80 + ", 0)")
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


var makePurchaseDiffChart = function(data)
{
	var chartTarget = d3.select("#chartPlayground").append("div").attr("class", "widestats");
	chartTarget.append("h2").html(data.bundleTitle + "<br />Hourly Per Platform Purchases");

	var coloursPlatform = {"puLinDiff":"#333388", "puMacDiff" : "#338833", "puWinDiff" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var d2 = d3.layout.stack()(["puLinDiff", "puMacDiff", "puWinDiff"].map(function(p) { console.log(p); return data.map(function(d) { return {x: d.lastUpdated, y: +d[p], t: getShortTitle(data.bundleTitle), c: p, a: +d.puLinDiff + d.puMacDiff + d.puWinDiff}; }); }));
	console.log("Making value chart");

	var valueChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 30)
	.append("g")
		.attr("transform", "translate(0, "+ 10.5 + ")");
		
	chartTarget.append("p").html("This graph shows the new purchases per hour on each platform across the duration of this promotion.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows.");

	var earliestDate = d3.min(data, function(d) {return d.lastUpdated; });
	var twoWeeks = new Date();
	twoWeeks.setTime(earliestDate.getTime() + (15 * 86400000)); //Well, that's a little more than a week (it's usually 14 days plus a little
	console.log(twoWeeks);
		
	var valX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([earliestDate, twoWeeks]);
		//.domain([d3.min(data, function(d) {return d.lastUpdated; }), d3.max(data, function(d) { return d.lastUpdated; })]);
		//TODO: Range should be evenly divisible by two weeks' worth of bar widths
	//tlX.ticks(d3.time.month.utc, 1);
	
	var valAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
		//.ticks(d3.time.day.utc, 1)
		.scale(valX);

	var valY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([0, d3.max(d2[d2.length - 1], function(d) { return d.a; })]);
	
	var valY2 = d3.scale.linear()
		.range([chartHeight, 0])
		.domain([0, d3.max(d2[d2.length - 1], function(d) { return d.a; })]);
	
	var valAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-90), 1)
		.ticks(5)
		.tickFormat(function (d) { return currencyFormatter(d); })
		.scale(valY2);

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
		.attr("transform", "translate(" + 80 + ", 0)")
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
		.attr("width", 2.5);
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
	chartTarget.append("h2").html(data.bundleTitle + "<br />Hourly Per Platform Revenue");

	var coloursPlatform = {"pyLinDiff":"#333388", "pyMacDiff" : "#338833", "pyWinDiff" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var d2 = d3.layout.stack()(["pyLinDiff", "pyMacDiff", "pyWinDiff"].map(function(p) { console.log(p); return data.map(function(d) { return {x: d.lastUpdated, y: +d[p], t: getShortTitle(data.bundleTitle), c: p, a: +d.pyLinDiff + d.pyMacDiff + d.pyWinDiff}; }); }));
	console.log("Making value chart");

	var valueChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 30)
	.append("g")
		.attr("transform", "translate(0, "+ 10.5 + ")");
		
	chartTarget.append("p").html("This graph shows the new revenue per hour on each platform across the duration of this promotion.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows.");

	var earliestDate = d3.min(data, function(d) {return d.lastUpdated; });
	var twoWeeks = new Date();
	twoWeeks.setTime(earliestDate.getTime() + (15 * 86400000));
	//console.log(twoWeeks)
		
	var valX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([earliestDate, twoWeeks]);
		//.domain([d3.min(data, function(d) {return d.lastUpdated; }), d3.max(data, function(d) { return d.lastUpdated; })]);
		//TODO: Range should be evenly divisible by two weeks' worth of bar widths
	//tlX.ticks(d3.time.month.utc, 1);
	
	var valAxisX = d3.svg.axis()
		.orient("bottom")
		.tickPadding(3)
		.tickSize(4, 3, 1)
		//.ticks(d3.time.day.utc, 1)
		.scale(valX);

	var valY = d3.scale.linear()
		.range([0, chartHeight])
		.domain([0, d3.max(d2[d2.length - 1], function(d) { return d.a; })]);
	
	var valY2 = d3.scale.linear()
		.range([chartHeight, 0])
		.domain([0, d3.max(d2[d2.length - 1], function(d) { return d.a; })]);
	
	var valAxisY = d3.svg.axis()
		.orient("left")
		.tickPadding(3)
		.tickSize(-(chartWidth-90), 1)
		.ticks(5)
		.tickFormat(function (d) { return "$" + currencyFormatter(d); })
		.scale(valY2);

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
		.attr("transform", "translate(" + 80 + ", 0)")
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
		.attr("width", 2.5);
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
	chartTarget.append("h2").html(data.bundleTitle + "<br />Hourly Per Platform Revenue Totals");

	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var pyChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 30)
	.append("g")
		.attr("transform", "translate(0, "+ 10.5 + ")");
		
	chartTarget.append("p").html("This graph shows the change in total revenue on each platform across the duration of this promotion.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows.");

	var earliestDate = d3.min(data, function(d) {return d.lastUpdated; });
	var twoWeeks = new Date();
	twoWeeks.setTime(earliestDate.getTime() + (15 * 86400000)); //Well, that's a little more than a week (it's usually 14 days plus a little

	console.log("Making revenue chart");
	var pyX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([earliestDate, twoWeeks]);
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
	chartTarget.append("h2").html(data.bundleTitle + "<br />Hourly Per Platform Totals");

	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var puChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 30)
	.append("g")
		.attr("transform", "translate(0, "+ 10.5 + ")");
		
	chartTarget.append("p").html("This graph shows the change in total purchase count on each platform across the duration of this promotion.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows.");
	
	var earliestDate = d3.min(data, function(d) {return d.lastUpdated; });
	var twoWeeks = new Date();
	twoWeeks.setTime(earliestDate.getTime() + (15 * 86400000)); //Well, that's a little more than a week (it's usually 14 days plus a little
		
	var puX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([earliestDate, twoWeeks]);
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
		.tickFormat(function (d) { return currencyFormatter(d); })
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
		.y(function(d,i) { return puY(d.puLin); })
		.defined(function(d) { if (isNaN(d.puLin) || d.puLin == null) { return false; } else { return true; } });
	var puLineMac = d3.svg.line()
		.x(function(d) { return puX(d.lastUpdated); })
		.y(function(d,i) { return puY(d.puMac); })
		.defined(function(d) { if (isNaN(d.puMac) || d.puMac == null) { return false; } else { return true; } });
	var puLineWin = d3.svg.line()
		.x(function(d) { return puX(d.lastUpdated); })
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
	chartTarget.append("h2").html(data.bundleTitle + "<br />Hourly Per Platform Averages");

	var coloursPlatform = {"Lin":"#333388", "Mac" : "#338833", "Win" : "#883333", "All" : "#ff0000"};
	var coloursPlatformGrey = {"Lin":"#999999", "Mac" : "#333333", "Win" : "#666666"};
	var currencyFormatter = d3.format(",.2f");
	
	var chartWidth = 900;
	var chartHeight = 200;

	var avChart = chartTarget.append("svg")
		.attr("class", "valueChart")
		.attr("width", chartWidth)
		.attr("height", chartHeight + 30)
	.append("g")
		.attr("transform", "translate(0, "+ 10.5 + ")");

	chartTarget.append("p").html("This graph shows the change in average purchase price on each platform across the duration of this promotion.<br /><span class = 'indicatorLin'>Blue</span> represents Linux, <span class = 'indicatorMac'>Green</span> represents Mac OS, <span class = 'indicatorWin'>Red</span> represents Windows, <span class = 'indicatorAll'>Bright red</span> represents the cross-platform average.<br />The <span class = 'averageAreaIndicator'>pink</span> filled area highlights values below the cross-platform average.");

	var earliestDate = d3.min(data, function(d) {return d.lastUpdated; });
	var twoWeeks = new Date();
	twoWeeks.setTime(earliestDate.getTime() + (15 * 86400000)); //Well, that's a little more than a week (it's usually 14 days plus a little

	var avX = d3.time.scale.utc()
		.range([85, chartWidth-15])
		.domain([earliestDate, twoWeeks]);
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
		.y(function(d,i) { return avY(d.avLin); })
		.defined(function(d) { if (isNaN(d.avLin) || d.avLin == null) { return false; } else { return true; } });
	var avLineMac = d3.svg.line()
		.x(function(d) { return avX(d.lastUpdated); })
		.y(function(d,i) { return avY(d.avMac); })
		.defined(function(d) { if (isNaN(d.avMac) || d.avMac == null) { return false; } else { return true; } });
	var avLineWin = d3.svg.line()
		.x(function(d) { return avX(d.lastUpdated); })
		.y(function(d,i) { return avY(d.avWin); })
		.defined(function(d) { if (isNaN(d.avWin) || d.avWin == null) { return false; } else { return true; } });
	var avAreaAll = d3.svg.area()
		.x(function(d) { return avX(d.lastUpdated); })
		.y0(chartHeight)
		.y1(function(d,i) { return avY(d.avAll); })
		.defined(function(d) { if (isNaN(d.avAll) || d.avAll == null) { return false; } else { return true; } });
	var avLineAll = d3.svg.line()
		.x(function(d) { return avX(d.lastUpdated); })
		.y(function(d,i) { return avY(d.avAll); })
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
