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

<div class = 'intro'>
	<object id = 'hibWidget' data="http://www.humblebundle.com/_widget/html">This is meant to be a widget that shows the status of the current Humble Indie Bundle. If you can't see it, don't worry - the stats are still here :)</object>
	<h1>Some More Humble Visualisations</h1>
	<p>Hi!</p>
	<p>You're looking at some work in progress stuff. You can get back to the Humble Visualisations by <a href = '../'>clicking here</a>.</p> 
	<p>If you have any questions or comments, please <a href = 'mailto:cheese@twolofbees.com'>get in touch</a>.</p>
	
	<p>Cheese</p>

	<h3>Currently there is hourly data available for the following bundles</h3>
	<ul>
		<li><a href = '?bundle=Indie+6'>Humble Indie Bundle 6</a> (missing first 5 hours)</li>
		<li><a href = '?bundle=eBook'>Humble eBook Bundle</a></li>
		<li><a href = '?bundle=Android+4'>Humble Bundle for Android 4</a></li>
	</ul> 

</div>

<?php
$bundleTitle = "Android+4";
if (isset($_GET['bundle']))
{
	$bundleTitle = urlencode($_GET['bundle']);
}
		echo "<div class = 'widestats' id = 'bigCharts'>\n";

		echo "\t<img src = '" . $bundleTitle . "_purchase_diff_big.png' />";
		echo "\t<p>This chart shows the hourly number of <strong>additional purchases</strong> calculated from the difference between the current total and last hour's total.</p>";
		
		echo "\t<img src = '" . $bundleTitle . "_revenue_diff_big.png' />";		
		echo "\t<p>This chart shows the hourly amount of <strong>additional revenue</strong> calculated from the difference between the current total and last hour's total.</p>";
		echo "\t<img src = '" . $bundleTitle . "_average_diff_big.png' />";		
		echo "\t<p>This chart shows the hourly state of <strong>averages</strong>.</p>";
		echo "\t<img src = '" . $bundleTitle . "_price_diff_big.png' />";	
		echo "\t<p>This chart shows the hourly state of <strong>separate price</strong> values.</p>";
		echo "\t<img src = '" . $bundleTitle . "_purchase_totals_big.png' />";
		echo "\t<p>This chart shows the hourly state of <strong>total purchases</strong>.</p>";
		echo "\t<img src = '" . $bundleTitle . "_revenue_totals_big.png' />";	
		echo "\t<p>This chart shows the hourly state of <strong>total revenue</strong>.</p>";
				
		echo "</div>";


		echo "<div class = 'widestats' id = 'tinyCharts'>\n";
		echo "\t<p>" . $bundleTitle . " Hourly Purchases</p>";
		echo "\t<img src = '" . $bundleTitle . "_purchase_diff.png' /> <br />";
		echo "\t<p>" . $bundleTitle . " Hourly Revenue</p>";
		echo "\t<img src = '" . $bundleTitle . "_revenue_diff.png' /> <br />";
		echo "\t<p>" . $bundleTitle . " Hourly Averages</p>";
		echo "\t<img src = '" . $bundleTitle . "_average_diff.png' /> <br />";
		echo "\t<p>" . $bundleTitle . " Separate Price</p>";
		echo "\t<img src = '" . $bundleTitle . "_price_diff.png' /> <br />";
		echo "\t<p>" . $bundleTitle . " Hourly Purchase Totals</p>";
		echo "\t<img src = '" . $bundleTitle . "_purchase_totals.png' /> <br />";
		echo "\t<p>" . $bundleTitle . " Hourly Revenue Totals</p>";
		echo "\t<img src = '" . $bundleTitle . "_revenue_totals.png' /> <br />";

		echo "</div>\n";


?>
<div class = 'intro' id = 'bottom'>
	<h3>Some Notes!</h3>
	<p>Ooher, more charts!</p>
	<p>This is still in development, so please be patient ^_^</p>
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
