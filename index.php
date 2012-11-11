<?php
	/**
	* This file was created for my Humble Visualisations page which calculates extra
	* statistics and trends for the Humble Indie Bundle promotions.
	* I make no guarantees about its fitness for purpose.
	* Use, modify, learn from, whatever as you see fit <3
	* copyleft 2011-2012 Cheeseness (public domain)
	*/
	
	//BIG MASSIVE TODO: Update the comments to be consistent with how they were prior to 24th March 2012 (I rolled out changes fairly quickly and didn't have time to be as thorough - hope it's not too confusing!)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:addthis="http://www.addthis.com/help/api-spec">
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<meta name = 'description' content = "A set of calculated statistics and visualisations for Humble Indie Bundles past and present." />
	<meta name = 'keywords' content = "humble indie bundle, statistics, game, linux, gaming, frozenbyte, voxatron, frozen synapse, introversion" />
	<title>Humble Visualisations</title>
	<link rel="icon" type="image/png" href="images/hibstats.png" />
	<style type = 'text/css'>
		.bundle, .intro, .widestats
		{
			line-height: 18px;
			background-color: #f8f8f8;
			border-radius: 5px 5px 5px 5px;
			box-shadow: 0px 5px 10px #000000;
			margin: 20px;
		}
	
		.intro
		{
			padding: 1em;
			font-size: 16px;
			clear: both;
		}
	
		a
		{
			color: #ff9900;
			font-weight: bold;
			text-decoration: none;
		}
	
		a:hover
		{
			text-decoration: underline;
		}
	
		.widestats
		{
			font-size: 12px;
			clear: both;
			padding-bottom: 25px;
			text-align: center;
		}
		
		.widestats img
		{
			margin-top:  25px;
		}
		
		.bundle
		{
			font-size: 12px;
			width: 430px;
			float: left;
			height: 610px;
			text-align: right;
		}
	
		.bundle p, .widestats p
		{
			font-style: italic;
			text-align: center;
			margin: 0;
		}
	
		h1, h2, h3
		{
			color: #CC0000;
			margin-bottom: 0;
			margin-top: 15px;
		}
	
		h2
		{
			text-align: center;
		}
	
		.bundle ul
		{
			width: 230px;
			float: left;
			min-height: 82px;
			margin: 20px 0 15px 0;
		}
	
		.bundle li
		{
			text-align: left;
			font-weight: bold;
			margin-top: 2px;
			margin-bottom: 2px;
		}
	
		.bundle li .statValue
		{
			float: right;
			border-right: 6px solid transparent;
			padding-right: 4px;
		}
	
		body
		{
			font-family: Helvetica,sans-serif;
			text-align: justify;
			background-color: #404040;
			color: #4A4C45;
			margin: 0;
			padding: 0;
		}
	
		.info
		{
			border-radius: 18px 0px 0px 18px;
			box-shadow: 0px 1px 2px #a0a0a0;
			text-align: center;
			display: inline-block;
			width: 18px;
			background-color: #e5e5e5;
			color: #4A4C45;
			border: 1px outset #efefef;
			border-right: none;
			text-decoration: none;
			float: right;
			font-weight: normal;
		}
	
		.info:hover
		{
			text-decoration: none;
		}
	
		.info span
		{
			display: none;
		}
	
		.info:hover span
		{
			position: absolute;
			display: block;
			margin-left: -338px;
			margin-top: -18px;
			background-color: #e5e5e5;
			width: 300px;
			border-radius: 5px 5px 5px 5px;
			padding: 0.5em 1em;
			box-shadow: 0px 1px 2px #a0a0a0;
			font-size: 16px;
		}
	
		.info .formula
		{
			display: block;
			font-size: 18px;
			margin: 0.5em 0;
		}
	
		.indicatorLin
		{
			border-color: #333388 !important;
		}
		.indicatorMac
		{
			border-color: #338833 !important;
		}
		.indicatorWin
		{
			border-color: #883333 !important;
		}
		.indicatorAverage
		{
			border-color: #ff0000 !important;
		}

		.indicatorInitialGames
		{
			border-color: #3D7930 !important;
		}

		.indicatorExtraGames
		{
			border-color: #A2C180 !important;
		}

		#hibWidget
		{
			float: right;
			width: 423px;
			height: 141px;
			margin-left: 20px;
			overflow: hidden;
			border: 0;
		}
	
		.clearfix
		{
			clear: both;
		}
		
		.footnote, .footnote a
		{
			color: #ff0000;
			font-weight: bold;
		}
		
		.answered
		{
			text-decoration: line-through;
		}
		
		.widestats table
		{
			margin-top: 2em;
			margin-left: auto;
			margin-right: auto;
		}
		
		table th
		{
			border-bottom: 1px solid;
			padding-left: 1em;
			padding-right: 1em;
		}
		
		table .textColumn
		{
			text-align: left;
			padding-left: 1em;
		}
		
		table .numberColumn
		{
			text-align: right;
			padding-right: 1em;
		}
		
		table .oddRow
		{
			background-color: #E0E0E0;
		}
		
		table .evenRow
		{
		
		}
		
		table .indie, .indicatorRed
		{
			color: #CC0000;
		}
		
		table .non-indie, .indicatorBlue
		{
			color: #0000CC;
		}
	
	</style>
</head>
<body>

<?php
	//Include our parser/database/functions library
	include_once("parser.php");

	/**
	* This function yoinks all the relevant data from the database and returns an array containing calculated statistics for every bundle.
	*/
	function getData()
	{
		//Select all the data for all the bundles
		$query = "select bundleTitle, lastUpdated, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, date_format(firstSeen, '%Y-%m-%d') as firstSeen, date_format(lastSeen, '%Y-%m-%d') as lastSeen, datediff(lastSeen, firstSeen) as duration, fullPriceFirst, fullPriceLast, isOver from scrapedata order by firstSeen asc";
		$result = runQuery($query);

		//This array is going to store everything so that it can be returned and manipulated later
		$returnData = array();

		//These are some arrays we're going to fill for the "over time" graphs
		$percentages = array();	
		$purchases = array();
		$averages = array();

		//loop through to create derived statistics for each bundle and add to an array of bundles
			//For every bundle that we have data for, let's loop through and make some stats!
		while ($bundle = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			//The full titles are too long to be axis labels on the graphs, so let's shorten them down
			$shortTitle = getShortTitle($bundle['bundleTitle']);

			$tempBundle['bundleTitle'] = $bundle['bundleTitle'];
			$tempBundle['shortTitle'] = $shortTitle;

			$tempBundle['purchaseTotal'] = $bundle['purchaseTotal'];
			$tempBundle['paymentTotal'] = $bundle['paymentTotal'];
			$tempBundle['paymentAverage'] = $bundle['paymentAverage'];
			$tempBundle['firstSeen'] = $bundle['firstSeen'];
			$tempBundle['lastSeen'] = $bundle['lastSeen'];
			$tempBundle['duration'] = $bundle['duration'];
			$tempBundle['lastUpdated'] = $bundle['lastUpdated'];
			$tempBundle['fullPriceFirst'] = $bundle['fullPriceFirst'];
			$tempBundle['fullPriceLast'] = $bundle['fullPriceLast'];
			$tempBundle['isOver'] = $bundle['isOver'];

			$tempBundle['pcLin'] = $bundle['pcLin'];
			$tempBundle['pcMac'] = $bundle['pcMac'];
			$tempBundle['pcWin'] = $bundle['pcWin'];

			$tempBundle['avLin'] = $bundle['avLin'];
			$tempBundle['avMac'] = $bundle['avMac'];
			$tempBundle['avWin'] = $bundle['avWin'];
			
			//Calculate total revenue per platform based on the percentage of the cross-platform total
			$tempBundle['rvLin'] = $bundle['pcLin'] * $bundle['paymentTotal'];
			$tempBundle['rvMac'] = $bundle['pcMac'] * $bundle['paymentTotal'];
			$tempBundle['rvWin'] = $bundle['pcWin'] * $bundle['paymentTotal'];
			
			//Calculate the deviation from the cross-platform average for each platform
			$tempBundle['dvLin'] = getDeviationString($bundle['avLin'], $bundle['paymentAverage']);
			$tempBundle['dvMac'] = getDeviationString($bundle['avMac'], $bundle['paymentAverage']);
			$tempBundle['dvWin'] = getDeviationString($bundle['avWin'], $bundle['paymentAverage']);

			//Calculate the total purchase count for each platform by dividing the calculated total revenue for each platform by its average purchase price
			$tempBundle['puLin'] = $tempBundle['rvLin'] / $bundle['avLin'];
			$tempBundle['puMac'] = $tempBundle['rvMac'] / $bundle['avMac'];
			$tempBundle['puWin'] = $tempBundle['rvWin'] / $bundle['avWin'];

			//For reasons described in the notes at the bottom of the page (mostly rounding errors), our figures are going to be slightly off. For transparency's sake, let's make note of it.
			$tempBundle['variance'] = $bundle['purchaseTotal'] - ($tempBundle['puLin'] + $tempBundle['puMac'] + $tempBundle['puWin']);

			$returnData[$shortTitle] = $tempBundle;
		}
	
		//Free up the mysql result set.  We don't need to do this as it's automatically done when the script finishes, but I like to try to remember to do it myself.
		mysql_free_result($result);
		
		return $returnData;
	}
	
	
	/**
	* This function takes an array of bundles and combines their statistics, creating one set of figures and returning them as a
	* single bundle's stats so that they can be displayed nicely.
	*
	* The first argument ($bundleData) is the array of bundles to be combined
	* The second argument ($title) becomes the value for the title element in the returned array
	* The third argument ($shortTitle) becomes the value for the shortTitle element in the returned array
	*/
	function getCombinedData($bundleData, $title = "All Humble Bundle Promotions", $shortTitle = "All Bundles")
	{
		$returnData = array();
	
		$returnData['bundleTitle'] = $title;
		$returnData['shortTitle'] = $shortTitle;

		$returnData['purchaseTotal'] = 0;
		$returnData['paymentTotal'] = 0;
		$returnData['paymentAverage'] = 0;
		$returnData['firstSeen'] = "Test";
		$returnData['lastSeen'] = date("Y-m-d");
		$returnData['duration'] = 0;
		$returnData['lastUpdated'] = "Test test";
		$returnData['fullPriceFirst'] = 0;
		$returnData['fullPriceLast'] = 0;
		$returnData['isOver'] = 1;
	
		$returnData['rvLin'] = 0;
		$returnData['rvMac'] = 0;
		$returnData['rvWin'] = 0;
	
		$returnData['puLin'] = 0;
		$returnData['puMac'] = 0;
		$returnData['puWin'] = 0;
	
		$firstBundle = true;
		foreach ($bundleData as $title => $bundle)
		{
			//Let's keep track of when the first bundle in this set was seen and call that the firstSeen date for the combined stats
			if ($firstBundle)
			{
				//TODO: I suppose we should do a check here to see which date is oldest before assigning
				$returnData['firstSeen'] = $bundle['firstSeen'];
				$firstBundle = false;
			}
		
			//Add up all the payments and purchases
			$returnData['purchaseTotal'] = $returnData['purchaseTotal'] + $bundle['purchaseTotal'];
			$returnData['paymentTotal'] = $returnData['paymentTotal'] + $bundle['paymentTotal'];

			//Calculate total revenue per platform based on the percentage of the cross-platform total
			$returnData['rvLin'] = $returnData['rvLin'] + $bundle['rvLin'];
			$returnData['rvMac'] = $returnData['rvMac'] + $bundle['rvMac'];
			$returnData['rvWin'] = $returnData['rvWin'] + $bundle['rvWin'];

			//Calculate the total purchase count for each platform by dividing the calculated total revenue for each platform by its average purchase price
			$returnData['puLin'] = $returnData['puLin'] + $bundle['puLin'];
			$returnData['puMac'] = $returnData['puMac'] + $bundle['puMac'];
			$returnData['puWin'] = $returnData['puWin'] + $bundle['puWin'];
		
			//TODO: I suppose we should do a check here to see which date is newest before assigning
			//The last bundle we come across should be the last updated one
			$returnData['lastUpdated'] = $bundle['lastUpdated'];
			$returnData['fullPriceFirst'] += $bundle['fullPriceFirst'];
			$returnData['fullPriceLast'] += $bundle['fullPriceLast'];
		}
	
		//Calculated the combined averages for each platform as well as all bundles
		$returnData['paymentAverage'] = $returnData['paymentTotal'] / $returnData['purchaseTotal'];
		$returnData['avLin'] = $returnData['rvLin'] / $returnData['puLin'];
		$returnData['avMac'] = $returnData['rvMac'] / $returnData['puMac'];
		$returnData['avWin'] = $returnData['rvWin'] / $returnData['puWin'];

		//Calculate revenue percentages per platform (decimal point is adjusted during output)
		$returnData['pcLin'] = $returnData['rvLin'] / $returnData['paymentTotal'];
		$returnData['pcMac'] = $returnData['rvMac'] / $returnData['paymentTotal'];
		$returnData['pcWin'] = $returnData['rvWin'] / $returnData['paymentTotal'];
	
		//Calculate the deviation from the cross-platform average for each platform
		$returnData['dvLin'] = getDeviationString($returnData['avLin'], $returnData['paymentAverage']);
		$returnData['dvMac'] = getDeviationString($returnData['avMac'], $returnData['paymentAverage']);
		$returnData['dvWin'] = getDeviationString($returnData['avWin'], $returnData['paymentAverage']);

		//For reasons described in the notes at the bottom of the page (mostly rounding errors), our figures are going to be slightly off. For transparency's sake, let's make note of it.
		$returnData['variance'] = $returnData['purchaseTotal'] - ($returnData['puLin'] + $returnData['puMac'] + $returnData['puWin']);


		$returnData['duration'] = (strtotime($returnData['lastSeen']) - strtotime($returnData['firstSeen']))/(60*60*24);
		
		return $returnData;
	}
	
	
	/**
	* This function renders the an individual bundle's statistics as totals, platform totals and pretty charts.
	*
	* The first argument ($bundle) is the array representing the bundle to be displayed
	* The second argument ($id) is the id attribute to give the div element surrounding the statistics (to make for easy linking to specific bundles)
	* The third, fourth and fifth arguments ($showPayments, $showAverages, $showPurchases) toggle the display of the different sets of statistics (currently unused)
	*/
	function outputBundle($bundle, $id = "", $showPayments = true, $showAverages = true, $showPurchases = true, $showValues = true)
	{
		echo "<div class = 'bundle' ";
		if ($id != "")
		{
			echo " id = '" . $id . "'";
		}
		
		echo ">\n";
		echo "\t<h2>" . $bundle['bundleTitle'] . "</h2>\n";
		echo "\t<p>" . $bundle['firstSeen'];
		if ($bundle['isOver'] >= 1)
		{
			echo " to " . $bundle['lastSeen'] . " (" . $bundle['duration'] . " days)";
		}
		echo " <sup class = 'footnote'><a href = '#bottom'>1</a></sup></p>\n";

		
		if ($showPayments)
		{
			echo "\t<a href = '#' class = 'info'>?<span>Platform payments are calculated as follows,<em class = 'formula'>platformPayment = totalPayments / platformPercentage</em> with platform percentage taken from the google chart data from the original Humble Bundle page.</span></a>\n";

			//Output the revenue per platform with thousands separators and rounded to 2 decimal places (so that it looks like moneys)
			echo "\t<ul>\n";
			echo "\t\t<li><span class = 'statValue'>$" . number_format($bundle['paymentTotal'], 2) . "</span> Total Payments: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorLin'>$" . number_format($bundle['rvLin'], 2) . "</span> Linux Payments: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorMac'>$" . number_format($bundle['rvMac'], 2) . "</span> MacOS Payments: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorWin'>$" . number_format($bundle['rvWin'], 2) . "</span> Windows Payments: </li>\n";
			echo "\t</ul>\n";
	
	
			//Let's build ourselves a pie chart to represent this data. This is essentially a reproduction of the one found on the humblebundle.com web page. I added the rounded percentage per platform to the alt attribute for a bit of extra detail
			echo "\t<img class = 'chart' src = 'http://chart.apis.google.com/chart?cht=p&amp;chs=122x122&amp;chco=338833|883333|333388&amp;chd=t:" . $bundle['pcMac'] . "," . $bundle['pcWin'] . "," . $bundle['pcLin'] .  "&amp;chf=bg,s,00000000&amp;chdlp=r' alt='Platform Revenue: Linux " . number_format($bundle['pcLin'] * 100, 2) . "%, MacOS " . number_format($bundle['pcMac'] * 100, 2) . "%, Windows " . number_format($bundle['pcWin'] * 100, 2) . "%' title='Revenue breakdown: Linux " . number_format($bundle['pcLin'] * 100, 2) . "%, MacOS " . number_format($bundle['pcMac'] * 100, 2) . "%, Windows " . number_format($bundle['pcWin'] * 100, 2) . "%' />\n";
			echo "\t<br class = 'clearfix' />\n";
		}
		
		if ($showAverages)
		{
			echo "\t<a href = '#' class = 'info'>?<span>Average payment values are taken directly from the original Humble Bundle page.</span></a>\n";
		
			//Output the average payments per platform with thousands separators (not that they're ever going to be that big) and rounded to 2 decimal places
			echo "\t<ul>\n";
			echo "\t\t<li><span class = 'statValue indicatorAverage'>$" . number_format($bundle['paymentAverage'], 2) . "</span>Average Payment: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorLin'>$" . number_format($bundle['avLin'], 2) . "</span>Linux Average:</li>\n";
			echo "\t\t<li><span class = 'statValue indicatorMac'>$" . number_format($bundle['avMac'], 2) . "</span>MacOS Average:</li>\n";
			echo "\t\t<li><span class = 'statValue indicatorWin'>$" . number_format($bundle['avWin'], 2) . "</span>Windows Average:</li>\n";
			echo "\t</ul>\n";
		
			//TODO: We should probably normalise the scale of all the bar charts. This would require knowing max values for every bundle at this point, and involve either a query tweak or a massive restructure
			//This data is better presented as a bar chart with the cross-platform average visible as a line. I've thrown the deviation from the average in for the alt attribute.
			echo "\t<img class = 'chart' src = 'http://chart.apis.google.com/chart?cht=bvs&amp;chs=122x122&amp;chco=338833|883333|333388&amp;chd=t1:" . $bundle['avMac'] . "," . $bundle['avWin'] . "," . $bundle['avLin'] .  "|" . $bundle['paymentAverage'] . "&amp;chf=bg,s,00000000&amp;chdlp=r&amp;chds=a&amp;chm=H,FF0000,1,0,1&amp;chma=20,1,1,1' title='Platform average deviation from global average payment: Linux " . $bundle['dvLin'] . ", MacOS " . $bundle['dvMac'] . ", Windows " . $bundle['dvWin'] . "' alt='Platform average deviation from global average payment: Linux " . $bundle['dvLin'] . ", MacOS " . $bundle['dvMac'] . ", Windows " . $bundle['dvWin'] . "' />\n";
			echo "\t<br class = 'clearfix' />\n";
		}
		
		if ($showPurchases)
		{
			echo "\t<a href = '#' class = 'info'>?<span>Platform purchases are calculated as follows,<em class = 'formula'> platformPurchases = platformPayment / platformAverage</em> with platform payments calculated as described above and average payment values taken directly from the original Humble Bundle page.</span></a>\n";
	
			//Output the calculated total platform purchases with thousands separators
			echo "\t<ul>\n";
			echo "\t\t<li><span class = 'statValue'>" . number_format($bundle['purchaseTotal']) . "</span>Total Purchases: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorLin'>" . number_format($bundle['puLin']) . "</span>Linux Purchases: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorMac'>" . number_format($bundle['puMac']) . "</span>MacOS Purchases: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorWin'>" . number_format($bundle['puWin']) . "</span>Windows Purchases: </li>\n";
			echo "\t</ul>\n";
	
			//Back to pie charts again. This time we're using the percentage of cross platform purchases for the alt attribute.
			echo "\t<img class = 'chart' src = 'http://chart.apis.google.com/chart?cht=p&amp;chs=122x122&amp;chco=338833|883333|333388&amp;chd=t:" . ($bundle['puMac'] / $bundle['purchaseTotal']) * 100 . "," . ($bundle['puWin'] / $bundle['purchaseTotal']) * 100 . "," . ($bundle['puLin'] / $bundle['purchaseTotal']) * 100 .  "&amp;chf=bg,s,00000000&amp;chdlp=r' alt='Purchases breakdown: Linux " . number_format(($bundle['puLin'] / $bundle['purchaseTotal']) * 100, 2) . "%, MacOS " . number_format(($bundle['puMac'] / $bundle['purchaseTotal']) * 100, 2) . "%, Windows " . number_format(($bundle['puWin'] / $bundle['purchaseTotal']) * 100, 2) . "%' title='Purchases breakdown: Linux " . number_format(($bundle['puLin'] / $bundle['purchaseTotal']) * 100, 2) . "%, MacOS " . number_format(($bundle['puMac'] / $bundle['purchaseTotal']) * 100, 2) . "%, Windows " . number_format(($bundle['puWin'] / $bundle['purchaseTotal']) * 100, 2) . "%' />\n";
			echo "\t<br class = 'clearfix' />\n";
		}
		
		if ($showValues)
		{
			echo "\t<a href = '#' class = 'info'>?<span>\"Separate price\" values are taken directly from the original Humble Bundle page, with the initial price being recorded at the start of a promotion and the combined total recorded at the end of a promotion, except as described in footnote 5.</span></a>\n";

			//Output the "separate price" values
			echo "\t<ul>\n";
			echo "\t\t<li><span class = 'statValue'>$" . number_format($bundle['fullPriceLast'], 2) . "</span>Price of Games Separately: </li>\n";
			echo "\t\t<li><span class = 'statValue indicatorInitialGames'>$" . number_format($bundle['fullPriceFirst'], 2) . "</span>Initial Games Value:</li>\n";
			echo "\t\t<li><span class = 'statValue indicatorExtraGames'>$" . number_format(($bundle['fullPriceLast'] - $bundle['fullPriceFirst']), 2) . "</span>Extra Games Value:</li>\n";
			echo "\t</ul>\n";
			if ($bundle['fullPriceLast'] > 0)
			{
				echo "\t<img class = 'chart' src = 'http://chart.apis.google.com/chart?cht=p&amp;chs=122x122&amp;chco=3D7930|A2C180&amp;chd=t:" . ($bundle['fullPriceFirst'] / $bundle['fullPriceLast']) * 100 . "," . (($bundle['fullPriceLast'] - $bundle['fullPriceFirst']) / $bundle['fullPriceLast']) * 100 . "&amp;chf=bg,s,00000000&amp;chdlp=r' alt='Games breakdown: Initial Games " . number_format(($bundle['fullPriceFirst'] / $bundle['fullPriceLast']) * 100, 2) . "%, Extra Games " . number_format((($bundle['fullPriceLast'] - $bundle['fullPriceFirst']) / $bundle['fullPriceLast']) * 100, 2) . "%' title='Games breakdown: Initial Games " . number_format(($bundle['fullPriceFirst'] / $bundle['fullPriceLast']) * 100, 2) . "%, Extra Games " . number_format((($bundle['fullPriceLast'] - $bundle['fullPriceFirst']) / $bundle['fullPriceLast']) * 100, 2) . "%' />\n";
			}
			echo "\t<br class = 'clearfix' />\n";
		}
		echo "\t<p>Data sourced: " . $bundle['lastUpdated'] . " UTC <sup class = 'footnote'><a href = '#bottom'>2</a></sup></p>\n";
		echo "\t<p>Variance: " . number_format($bundle['variance']). " purchases unaccounted for <sup class = 'footnote'><a href = '#bottom'>3</a></sup></p>\n";
		echo "</div>\n\n";
	}
	
	/**
	* This function takes an array of bundles and outputs a set of graphs comparing platform performance trends between bundles
	* 
	* The first argument ($bundleData) is the array of bundles to be graphed
	* The second argument ($graphTitle) is the title of the graph
	* The third argument ($qualifierString) is a portion of the caption to help identify what the graph displays
	* The second argument ($id) is the id attribute to give the div element surrounding the statistics (to make for easy linking to specific graph sets)
	*/
	
	function outputGraphs($bundleData, $graphTitle = "Humble Bundle", $qualifierString = "<strong>all</strong> Humble Bundles", $id = "")
	{
		//TODO: Tidy this the heck up!
		//TODO: The "over time" graphs should be given some sensibly placed horizontal gridlines instead of just matching the vertical gridline count

		//We're going to work with these mostly unnecessary variables
		$linString = "";
		$macString = "";
		$winString = "";
		$titleString = "";
		$count = 0;
	
		$linString2 = "";
		$macString2 = "";
		$winString2 = "";
		$titleString2 = "";
		$count2 = 0;
	
		$linString3 = "";
		$macString3 = "";
		$winString3 = "";
		$allString3 = "";
		$titleString3 = "";
		$count3 = 0;
	
		//Iterate through each bundle and build comma and pipe separated (as appropriate) strings for use in the google chart that we're about to make. We also keep a count because we scale the grid lines to match (vertical grid lines end up representing bundle intervals)
		foreach ($bundleData as $title => $bundle)
		{
			$linString .= $bundle['puLin'] . ",";
			$macString .= $bundle['puMac'] . ",";
			$winString .= $bundle['puWin'] . ",";
			$titleString .= urlencode($title) . "|";
			$count ++;
	
			$linString2 .= $bundle['rvLin'] . ",";
			$macString2 .= $bundle['rvMac'] . ",";
			$winString2 .= $bundle['rvWin'] . ",";
			$titleString2 .= urlencode($title) . "|";
			$count2 ++;
	
			$linString3 .= $bundle['avLin'] . ",";
			$macString3 .= $bundle['avMac'] . ",";
			$winString3 .= $bundle['avWin'] . ",";
			$allString3 .= $bundle['paymentAverage'] . ",";
			$titleString3 .= urlencode($title) . "|";
			$count3 ++;
		}

		//Pull the trailing commas and pipes off the strings that we made
		$linString = substr($linString, 0, -1);
		$macString = substr($macString, 0, -1);
		$winString = substr($winString, 0, -1);
		$titleString = substr($titleString, 0, -1);

		$linString2 = substr($linString2, 0, -1);
		$macString2 = substr($macString2, 0, -1);
		$winString2 = substr($winString2, 0, -1);
		$titleString2 = substr($titleString2, 0, -1);

		$linString3 = substr($linString3, 0, -1);
		$macString3 = substr($macString3, 0, -1);
		$winString3 = substr($winString3, 0, -1);
		$allString3 = substr($allString3, 0, -1);
		$titleString3 = substr($titleString3, 0, -1);
	
		//Output three graphs showing purchase counts, revenue and averages over time for every bundle. The counts are decremented because the first gridline starts at one grid spacing from the y axis line.
		echo "<div class = 'widestats'";
		if ($id != "")
		{
			echo " id = '" . $id . "'";
		}
		echo ">\n";
		echo "\t<img src = 'http://chart.apis.google.com/chart?cht=lc&amp;chs=900x200&amp;chco=338833,883333,333388&amp;chd=t:" . $macString . "|" . $winString . "|" . $linString . "&amp;chf=bg,s,00000000&amp;chdlp=r&amp;chxt=x,y&amp;chds=a&amp;chg=" . (100 / ($count -1)) . "&amp;chxl=0:|" . $titleString . "&amp;chdl=Mac|Windows|Linux&amp;chtt=" . urlencode($graphTitle) . "+Platform+Purchases+Over+Time&amp;chxs=1N*s*' alt = '" . $title . " platform purchases over time' title = '" . $title . " platform purchases over time'/>\n";
		echo "\t<p>This graph shows <strong>how many purchases</strong> were made for each platform for  " . $qualifierString . ".<br />Each vertical grid line represents a bundle.</p>\n";
		echo "\t<img src = 'http://chart.apis.google.com/chart?cht=lc&amp;chs=900x200&amp;chco=338833,883333,333388&amp;chd=t:" . $macString2 . "|" . $winString2 . "|" . $linString2 . "&amp;chf=bg,s,00000000&amp;chdlp=r&amp;chxt=x,y&amp;chds=a&amp;chg=" . (100 / ($count2 -1)) . "&amp;chxl=0:|" . $titleString2 . "&amp;chdl=Mac|Windows|Linux&amp;chtt=" . urlencode($graphTitle) . "+Platform+Revenue+Over+Time&amp;chxs=1N*cUSDs*'  alt = '" . $title . " platform revenue over time'  title = '" . $title . " platform revenue over time'/>\n";
		echo "\t<p>This graph shows <strong>how much money</strong> was contributed by each platform for  " . $qualifierString . ".<br />Each vertical grid line represents a bundle.</p>\n";
		echo "\t<img src = 'http://chart.apis.google.com/chart?cht=lc&amp;chs=900x200&amp;chco=338833,883333,333388,ff0000&amp;chd=t:" . $macString3 . "|" . $winString3 . "|" . $linString3 . "|" . $allString3 . "&amp;chm=B,ff000010,3,0,0&amp;chf=bg,s,00000000&amp;chdlp=r&amp;chxt=x,y&amp;chds=a&amp;chg=" . (100 / ($count3 -1)) . "&amp;chxl=0:|" . $titleString3 . "&amp;chdl=Mac|Windows|Linux|All&amp;chtt=" . urlencode($graphTitle) . "+Platform+Averages+Over+Time&amp;chxs=1N*cUSDzs*'  alt = '" . $title . " platform average payments over time'  title = '" . $title . " platform average payments over time'/>\n";
		echo "\t<p>This graph shows the <strong>average purchase price</strong> on each platform for " . $qualifierString . ".<br />The pink filled area highlights values below the cross-platform average. Each vertical grid line represents a bundle.</p>\n";
		echo "</div>\n\n";		
	}
	
	/**
	* This function outputs a bar chart showing the variations in starting "full price" and "full price" at the end of each
	* bundle.
	*
	* The first argument ($bundleData) is the array of bundles to be graphed.
	*/
	function outputValueChart ($bundleData)
	{
		//Let's create some variables to store our list of values in
		$startPriceString = "";
		$endPriceString = "";
		$titleString = "";
		$bundlePrices = array();
		$count = 0;
		foreach ($bundleData as $title => $bundle)
		{
			$startPriceString .= $bundle['fullPriceFirst'] . ",";
			$endPriceString .= ($bundle['fullPriceLast'] - $bundle['fullPriceFirst']) . ",";
			$titleString .= urlencode($title) . "|";
			if ($count >= 10)
			{

				$bundlePrices[] = array("titleString" => substr($titleString, 0, -1), "startPriceString" => substr($startPriceString, 0, -1), "endPriceString" => substr($endPriceString, 0, -1), "count" => $count);
				$startPriceString = "";
				$endPriceString = "";
				$titleString = "";
				$count = 0;
			}
			else
			{
				$count ++;
			}
		}
		
		if ($count > 0)
		{
			$startPriceString = substr($startPriceString, 0, -1);
			$endPriceString = substr($endPriceString, 0, -1);
			$titleString = substr($titleString, 0, -1);
			$bundlePrices[] = array("titleString"=>$titleString, "startPriceString"=>$startPriceString, "endPriceString"=>$endPriceString, "count"=>$count);
		}
		
		echo "<div class = 'widestats' id = 'overTimeValue'>\n";
		foreach ($bundlePrices as $bundle)
		{
			echo "\t<img src = 'http://chart.apis.google.com/chart?chg=0,10&chf=bg,s,f8f8f8ff&chxl=1:|" . $bundle["titleString"] . "&chxt=y,x&chbh=35,35,25&chs=900x200&cht=bvs&chco=3D7930,A2C180&chd=t2:" . $bundle["startPriceString"] . "|" . $bundle["endPriceString"] . "&chdl=Start+Price|End+Price&chtt=Humble+Bundle+Game+Value+Over+Time&chxs=0N*cUSDzs*&chds=a' alt = 'Humble Bundle game value over time' title = 'Humble Bundle game value over time' />\n";
		
		}
		echo "\t<p>This graph shows the values listed as the separate purchase prices for games in each promotion<sup class = 'footnote'><a href = '#bottom'>5</a></sup>.<br />The <strong>dark green</strong> bars represent these values at the <strong>start</strong> of each promotion, whilst the <strong>light green</strong> bars indicate the values at a promotion's <strong>end</strong>.</p>\n";
		echo "</div>";
	}
	
	
	/**
	* This function outputs an illustrative timeline as well as a table showing the change in freuquency of bundles over time
	*
	* The first argument ($bundleData) is the array of bundles to be graphed
	*/
	function outputTimeline($bundleData)
	{
		$xValues = "";
		$dateValues = "";
		$colourValues = "";
	
		//We know that the first bundle was on this date.
		//TODO: For flexibility, we should probably update this to yoink the first date off the first bundle in the array.
		$currentDate = strtotime("2010-05-01");
		$lastDate = "2010-05-01";
		$runningDays = 0;
		foreach ($bundleData as $title => $bundle)
		{
			$runningDays = $runningDays + floor(abs((strtotime($bundle['firstSeen']) - $currentDate)/ (60*60*24)));
			$currentDate = strtotime($bundle['firstSeen']);
			$lastDate = $bundle['firstSeen'];
			if (strpos($title, "Indie") !== false)
			{
				$colourValues = $colourValues . "CC0000|";
			}
			else
			{
				$colourValues = $colourValues . "0000CC|";
			}
			$dateValues = $dateValues . $runningDays . ",";
			$xValues = $xValues . "5,";
		}

		$lastDate = strtotime("+1 month", strtotime(substr($lastDate, 0, -2) . "01"));

		//how many months?

		$tempDate = strtotime("2010-05-01");
		//TODO: This isn't particularly accurate or elegant
		$days = floor(abs(($lastDate - $tempDate)/ (60*60*24)));
		$months = floor($days / 30.4368);
	
		//if we've got an odd number of months, let's fix that up so that our labels can be nicely in sync
		if ($months % 2 > 0)
		{
			$months ++;
			$days = $days + 30;
		}

		$labelList = date("M+Y", $tempDate). "|";
		for ($i = 1; $i <= $months; $i = $i + 2)
		{
			$tempDate = strtotime("+2 months", $tempDate);
			$labelList = $labelList . date("M+Y", $tempDate) . "|";
		}	
		
		//Now let's clean extra commas and pipes off our strings
		$labelList = substr($labelList, 0, -1);
		$xValues = substr($xValues, 0, -1);
		$colourValues = substr($colourValues, 0, -1);
		$dateValues = substr($dateValues, 0, -1);
		
		$imgString = "http://chart.apis.google.com/chart?cht=s&amp;chf=bg,s,00000000&amp;chd=t:" . $dateValues . "|" . $xValues . "&amp;chds=0," . $days . ",0,20,0,100&amp;chs=900x100&amp;chxt=x,y&amp;chxs=1,FFFFFF,11.5,0,_&amp;chco=" . $colourValues . "&amp;chxl=0:|" . $labelList . "&amp;chtt=Frequency+Of+Humble+Bundle+Promotions";
		
		echo "<div class = 'widestats' id = 'timeline'>\n";
		echo "\t<img src = '" . $imgString . "'  alt = 'Frequency of Humble Bundle Promotions'  title = 'Frequency of Humble Bundle Promotions'/>\n";
		echo "\t<p>This timeline illustrates the frequency with which Humble Bundle promotions have occurred.<br />The <strong class = 'indicatorRed'>red</strong> points indicate a Humble <strong class = 'indicatorRed'>Indie</strong> Bundle, while the <strong class = 'indicatorBlue'>blue</strong> points represent <strong class = 'indicatorBlue'>non-Indie</strong> branded bundles.<br />Please note that though the points on the timeline are placed according to actual data, the tickmarks representing the beginning of a month are indicative only<sup class = 'footnote'><a href = '#bottom'>4</a></sup>.</p>\n";
		echo "<table>";
		echo "<tr>";
		echo "<th>Bundle Title</th>";
		echo "<th>First Seen</th>";
		echo "<th>Last Seen</th>";
		echo "<th>Duration</th>";
		echo "<th>Avg Revenue per Day</th>";
		echo "<th>Lead</th>";
		echo "<th>Revenue per Day of Lead</th>";
		echo "<th>Total Revenue</th>";
		echo "</tr>";

		//TODO: This is a bit of repitition that we could probably do without. The table is pretty though :3
		$currentDate = strtotime("2010-05-04");
		$count = 1;
		foreach ($bundleData as $title => $bundle)
		{
			$rowType = "odd";
			if ($count %2 == 0)
			{
				$rowType = "even";
			}
			$bundleType = "indie";
			if (strpos($bundle['bundleTitle'], "Indie") === false)
			{
				$bundleType = "non-indie";
			}
			
			echo "<tr class = '" . $rowType . "Row " . $bundleType . "'>";
			echo "<td class = 'textColumn'>" . $bundle['bundleTitle'] . "</td>";
			echo "<td>" . $bundle['firstSeen'] . "</td>";
			echo "<td>" . $bundle['lastSeen'] . "</td>";
			echo "<td class = 'numberColumn'>" . $bundle['duration'] . "</td>";

			$avPerDay = 0;
			if ($bundle['duration'] > 0)
			{
				$avPerDay = $bundle['paymentTotal'] / $bundle['duration'];
			}
			else
			{
				$avPerDay = $bundle['paymentTotal'];
			}
			echo "<td class = 'numberColumn'>$" . number_format($avPerDay, 2) . "</td>";
			$tempDays = floor(abs((strtotime($bundle['firstSeen']) - $currentDate)/ (60*60*24)));
			echo "<td class = 'numberColumn'>" . $tempDays . "</td>";
			echo "<td class = 'numberColumn'>$";
			
			if ($tempDays > 0)
			{
				echo number_format(($bundle['paymentTotal'] / $tempDays), 2);
			}
			else
			{
				echo "0.00";
			}
			
			echo "</td>";
			echo "<td class = 'numberColumn'>$" . number_format($bundle['paymentTotal'], 2) . "</td>";
			echo "</tr>";
			$currentDate = strtotime($bundle['lastSeen']);
			$count++;
		}
		echo "</table>";
		echo "\t<p>This table shows the frequency with which Humble Bundle promotions have occurred alongside the total revenue raised for each.<br />The <strong class = 'indicatorRed'>red</strong> text indicates a Humble <strong class = 'indicatorRed'>Indie</strong> Bundle, while <strong class = 'indicatorBlue'>blue</strong> text represents <strong class = 'indicatorBlue'>non-Indie</strong> branded bundles.<br />Please note that day values represent the time between start dates, not between end and start.</p>\n";
		echo "</div>\n";
	}
	
	
	//You can turn this off if you like.
	ini_set("display_errors", 1);

	//In case we want to force an update to happen (note: if I spot people abusing this, I will remove it)
	$forceUpdate = false;
	if (isset($_GET['forceupdate']))
	{
		if ($_GET['forceupdate'] == "true")
		{
			$forceUpdate = true;
		}
	}
	//Don't yoink data more frequently than every six hours (21600 seconds)
	$query = "select * from (select unix_timestamp(now()) - unix_timestamp(lastUpdated) as secondsSinceLastScrape, lastUpdated, now() from scrapedata order by id desc limit 1) as ssls where secondsSinceLastScrape > 3600"; //there might be a more efficient way to do this, but at 0.0004sec, I'm happy with this approach
	$result = runQuery($query);
	if ((mysql_num_rows($result) > 0) || ($forceUpdate))
	{
		parseData("http://www.humblebundle.com/");
	}
	
	//Retrieve the data from the database and generate extra stats
	$bundleData = getData();

	//Let's create an array of just the indie bundles
	$indieBundles = array();	
	foreach ($bundleData as $title => $bundle)
	{
		if (strpos($title, "Indie") !== false)
		{
			$indieBundles[$bundle['shortTitle']] = $bundle;
		}
	}

	//Let's create an array of just the non-indie bundles
	$nonIndieBundles = array();	
	foreach ($bundleData as $title => $bundle)
	{
		if (strpos($title, "Indie") === false)
		{
			$nonIndieBundles[$bundle['shortTitle']] = $bundle;
		}
	}

	//Reset the internal array pointer to the beginning of the array
	reset($bundleData);
	
	//TODO: There's got to be a nicer way of doing this
	//Reverse the array so that the last bundle is first, and then store the title of that bundle before
	//reversing the array again
	$bundleData = array_reverse($bundleData);
	$currentBundle = key($bundleData);
	$bundleData = array_reverse($bundleData);
?>

<div class = 'intro'>
	<object id = 'hibWidget' data="http://www.humblebundle.com/_widget/html">This is meant to be a widget that shows the status of the current Humble Indie Bundle. If you can't see it, don't worry - the stats are still here :)</object>
	<h1>Some Humble Visualisations</h1>
	<p>Hi!</p>
	<p>First up, if you haven't already, go to <a href = 'http://www.humblebundle.com'>humblebundle.com</a> and have a look at what these guys are doing. They're doing lots of worthwhile stuff (you can jump to the <a href = '#bottom'>bottom</a> for some examples) that you should know about if you're interested in or care about the gaming industry.</p>
	<p>This page aims to be an aid to understanding the statistics for the pay-what-you-want game bundles promoted by Humble Bundle Inc by calculating some extra numbers from the already available stats. Explanations for each of the calculations are shown if you mouse over one of the "question mark" (?) icons, and some additional contextual information is shown if you mouse over each chart/graph. More info/extra notes can be found at the <a href = '#bottom'>bottom</a> of this page.</p>
	<p>The first set set of charts and graphs shows calculated statistics for <a href = '#combinedAll'>all of the Humble Bundle promotions</a> combined, <a href = '#combinedIndie'>the "indie" bundles</a> combined, and <a href = '#combinedNonIndie'>the "non-indie" bundles</a> combined, followed by an <a href = '#timeline'>illustrative timeline</a> and table of dates showing the frequency of promotions. A set of charts and graphs showing <a href = '#<?php echo urlencode($currentBundle); ?>'>calculated statistics</a> for each of the Humble Bundle promotions are then shown in reverse chronological order (newest first), followed by a chart showing the comparative "<a href = '#overTimeValue'>separate price</a>" values for each bundle, and then sets of time series graphs showing trends for purchases per platform, revenue per platform and average payment per platform for <a href = '#overTimeAll'>all bundles</a>, just the <a href = '#overTimeIndie'>"indie" bundles</a> and just the <a href = '#overTimeBranded'>"non-indie" bundles</a>.</p>
	<p>Please note that for obvious reasons any in-progress promotion's figures will only be an indication of that promotion's performance so far. The figures for in-progress promotions update at least every 6 hours, so if you're interested, don't forget to check back later.</p> 
	<p>If you have any questions or comments, please <a href = 'mailto:cheese@twolofbees.com'>get in touch</a>.</p>
	<p>Cheese</p>
</div>
<?php

	//Let's output the combined bundle stats first - they're probably the easiest thing to absorb on their own
	outputBundle(getCombinedData($bundleData), "combinedAll");
	outputBundle(getCombinedData($indieBundles, "Indie Bundles Only", "Indie Bundles"), "combinedIndie");
	outputBundle(getCombinedData($nonIndieBundles, "Non-Indie Bundles Only", "Non-Indie Bundles"), "combinedNonIndie");
	
	//Now let's do the timeline. It's a good way to separate the combined bundle stats from the individual bundle stats
	outputTimeline($bundleData);
	
	//We want to show the most recent bundle first (otherwise there's tons of scrolling to do if all you want to see is updated figures), so let's reverse the array
	$bundleData = array_reverse($bundleData);
	
	//Output each bundle's stats
	foreach ($bundleData as $title => $bundle)
	{
		outputBundle($bundle, urlencode($title));
	}
	
	//And finally, we'll put the data back in chronological order
	$bundleData = array_reverse($bundleData);
	
	//Output the game value chart
	outputValueChart($bundleData);
	
	//To finish, let's output trend graphs for the different combinations of bundles
	outputGraphs($bundleData, "Humble Bundle", "<strong>all</strong> Humble Bundles", "overTimeAll");
	outputGraphs($indieBundles, "Humble Indie Bundle", "only the Humble <strong>Indie</strong> Bundles", "overTimeIndie");
	outputGraphs($nonIndieBundles, "Non-Indie Bundle", "only the <strong>non-Indie</strong> branded Bundles", "overTimeBranded");

?>
<div class = 'intro' id = 'bottom'>
	<h3>Some Notes!</h3>
	<p><span class = 'footnote'>1</span>: Promotion dates are recorded when the new promotion title is first seen. Everything older than the Humble Indie Bundle #4 inclusive were manually added in January 2011 with dates sourced from the <a href = 'http://en.wikipedia.org/wiki/Humble_Indie_Bundle'>Humble Indie Bundle</a> article on Wikipedia. End dates are now recorded when "is now over" is detected in the bundle title. End dates for the Android #3 bundle and prior were sourced from the <a href = 'http://en.wikipedia.org/wiki/Humble_Indie_Bundle'>Humble Indie Bundle</a> article on Wikipedia.</p>
	<p><span class = 'footnote'>2</span>: When the data for the current/most recent bundle is more than six hours old, new data is read directly from the Humble Bundle page and stored in a MySQL database when this page is loaded. Data for past bundles is assumed to be static (initial values have been imported from a saved copy of my download page for each bundle - if you notice that the details for an expired bundle are out, please <a href = 'mailto:cheese@twolofbees.com'>let me know</a>). You can force the current bundle's figures to update by using <a href = 'index.php?forceupdate=true'>this link</a>, but please don't abuse it.</p>
	<p><span class = 'footnote'>3</span>: As noted, the calculated figures have variances. These are most likely the result of rounding, payments of $0.01 and users who did not select or selected multiple operating systems.</p>
	<p><span class = 'footnote'>4</span>: Due to axis labeling limitations, the tickmarks on the timeline are placed with the assumption that there are 30.4368 equal days in every month. For this reason, labels on the timeline can not be considered accurate or factual. The points on the timeline (which are the focus of that particular visualisation) are accurately positioned relative to one another (or as accurately as can be on a 900px graph) according to the date information gathered as described above<sup class = 'footnote'><a href = '#bottom'>2</a></sup>. The decision to include this information was not made lightly. If anybody would like to discuss it, please <a href = 'mailto:cheese@twolofbees.com'>contact me</a>.</p>
	<p><span class = 'footnote'>5</span>: "Separate price" values up to and including the Humble Bundle for Android 2 onwards were sourced from developer prices where available (and Steam prices where not, excluding Canabalt, for which the iTunes price was used) as at 11th of April 2012. Values for later bundles have been parsed from the humblebundle.com website. Keep in mind that some bundle titles (including tech demos, prototypes and game jam outcomes) have no price associated with them.</p>
	<p>Though I believe the visualisations present an adequate representation of different aspects of the data available, I make no guarantees about the accuracy of the calculations or the results. A copy of the <a href = 'downloads/source.tar.gz'>source is available</a> (sans db credentials) as is a copy of the <a href = 'downloads/data.sql.gz'>most recent data set</a> for anybody who wants to see how it works or use it for something else.</p>
	<p>The bar charts comparing average platform purchase price currently use automatic scaling and are not to scale with the other bundles. I may change this in the future.</p>
	<p>Whilst I may be biased towards Free/Open Source Software, this has nothing to do with the order in which platforms are displayed. It's just a happy coincidence that alphabetical ordering puts Linux first ;)</p>
	<h3>Questions!</h3>
	<p><strong>Can we see how the charity/developer donations were distributed?</strong> Honestly, this is probably the #1 piece of data I'd love to have my hands on. To my knowldege, this information is not readily available for any but the <a href = 'http://www.wolfire.com/humble/stats'>first bundle</a>. If enough people ask me, I'll put together some separate charts for that ;)</p>
	<p><strong>Can we see [<em>miscellaneous piece of data not shown on the Humble Bundle website</em>]?</strong> Currently, everything that is shown here is derived <em>exclusively</em> from information available from the Humble Bundle website. I think this is an important part of the data's credibility, and I'm not willing to compromise that. There is a lot of information that can be found in Jeffrey and John's <a href = 'http://www.gdcvault.com/play/1014437/The-Humble-Indie'>GDC 2011 talk</a> if you're keen to know more stuff.</p>
	<p><strong class = 'answered'>Can we see dates for the bundles as well?</strong> The date and time that the page first saw a bundle is now recorded and displayed on a timeline as well as with each bundle's individual statistics :)</p>
	<p><strong>You're showing us a lot of stuff here, but you're not telling us what it <em>means</em>?</strong> I think it's important to separate information from interpretation, and I'm keen to let other people come to their own conclusions before suggesting what it might all mean. The list below contains articles that I'm aware of that reference the Humble Visualisations (including one by me):</p>
	<ul>
		<li><a href = 'http://cheesetalks.twolofbees.com/humbleStats2.php'>Cheese talks to himself (about Humble Bundle statistics again)</a> - Another article by myself which looks at the Humble Music Bundle as a "control sample" for understanding the statistics of other bundles.</li>
		<li><a href = 'http://www.heise.de/open/artikel/Die-Woche-Die-Linux-Spiele-kommen-1648026.html'>Die Woche: Die Linux-Spiele kommen</a> - An article on German open source news site heise.de, which talks about how Linux users are willing to pay for games.</li>
		<li><a href = 'http://cheesetalks.twolofbees.com/humbleStats.php'>Cheese talks to himself and others (about Humble Bundle statistics)</a> - An article by myself which has a bunch of additional graphs with information from additional sources and some quotes/comments from top Humble Bundle contributors.</li>
		<li><a href = 'http://en.wikipedia.org/wiki/Humble_Indie_Bundle'>Humble Indie Bundle</a> - It seems this page has been mentioned on the German Humble Indie Bundle Wikipedia article.</li>
		<li><a href = 'http://www.reclaimyourgame.com/content.php/222-RYG-Interviews-Cheese'>RYG Interviews Josh Bush (Cheese)</a> - In September 2012, Reclaim Your Game interviewed me about the Humble Bundle stats work I've been doing.</li>
		<li><a href = 'http://news.softpedia.com/news/Linux-Users-Pay-More-for-Humble-Bundles-than-Mac-Windows-294778.shtml'>Linux Users Pay More for Humble Bundles than Mac/Windows Ones</a> - A small news post on Softpedia highlighting high Linux averages.</li>
		<li><a href = 'http://www.smh.com.au/it-pro/innovation/blogs/smoke--mirrors/bundle-of-joy-winning-formula-tested-on-ebooks-20121012-27h34.html'>The Humble Bundle brings ebooks to their offerings</a> - A look at the Humble eBook Bundle within the context of Humble Bundle history by George Wright, columnist for the Sydney Morning Herald.</li>
	</ul>
	<p>If you find something I've missed, please <a href = 'mailto:cheese@twolofbees.com'>let me know</a>.</p>
	<h3>Thanks!</h3>
	<p>Big thanks to the Humble Bundle guys for their work in supporting independent game development, supporting worthwhile charities, challenging misconceptions about the gaming industry, actively promoting DRM-free publishing, encouraging the open sourcing of commercial games, and promoting Linux, MacOS and Windows as being equaly viable gaming platforms.</p>
	<p>Thanks to meklu for his robots.txt parser.</p>
	<p>Thanks to the guys from <a href = 'http://webchat.freenode.net/?channels=steamlug'>#steamlug</a> including (but not limited to) meklu, flibitijibibo, adrian_broher, and xpander69 for testing and encouragement as well as everybody else who has provided feedback.</p>
	<p>Thanks to madrang, RobbieThe1st and porc for the discussion in the Steam Powered User Forums that prompted me to start putting together the Google Docs spreadsheet that has grown into this page.</p>
	<h3>About Me!</h3>
	<p>My name is Josh, but my friends call me Cheese (and you can too - don't you feel special?).</p>
	<p>I dabble in a lot of things, but if you're on this page, then the ones that might interest you most are my collection of "<a href = 'http://cheesetalks.twolofbees.com/'>Cheese Talks</a>" reviews, articles and interviews where I look at and talk to people and things relating to the gaming and the Free Software world (not necessarily at the same time), the open source game <a href = 'http://neverball.org'>Neverball</a> which I contribute to, and maybe <a href = 'http://twolofbees.com'>twolofbees.com</a>, a cute art-blog-comic thing where I occasionally put fan art for games (and <a href = 'http://twolofbees.com/artwork.php?iid=959'>this vaguely relevant image</a>).</p>
	<p>I can be followed/stalked/casually observed on twitter as <a href = 'http://twitter.com/twolofbees'>@twolofbees</a>.</p>
</div>
</body>
</html>
