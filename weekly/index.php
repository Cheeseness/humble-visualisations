<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
<?php
	$title = "bitcomposer";
	if (isset($_GET['bundle']))
	{
		$title = urldecode($_GET['bundle']);
	}
	
	//Include our parser/database/functions library
	include_once("dbcreds.php");
	include_once("../process.php");
	//Let's make sure we're pulling time in UTC, OK?
	putenv("TZ=UTC");
	
	//Let's connect to the database server and set the database we'll be using 
	$con = ConnectToMySQL(getDBHost(), getDBUser(), getDBPass());
	if(!$con)
	{
		echo "Cannot Connect To MySQL: " . mysql_error();
	}
	ConnectToDB(getDBName(), $con);

	function showBundleList()
	{
		echo "\t<ul>\n";
		$query = "select distinct bundleTitle from scrapedata_weekly";
		$result = runQuery($query);
		while ($bundle = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			if (strlen($bundle['bundleTitle']) > 0)
			{
				echo "\t\t<li><a href = '?bundle=" . getShortTitle($bundle['bundleTitle']) . "'>" . $bundle['bundleTitle'] . "</a></li>\n";
			}
		}
		echo "\t</ul>\n";
	}
?>
<head>
	<meta charset='utf-8' />
	<meta name='viewport' content='width=device-width' />

	<title>The Humble Visualisations (hourly data preview of weekly sales)</title>

	<link rel='shortcut icon' href='images/fav.png' type='image/x-icon' />
	<link rel='stylesheet' href='styles/default.css' type='text/css' />

	<script type='text/javascript' src='scripts/d3/d3.v3.min.js'></script>
	<script type='text/javascript' src='scripts/charts_hourly.js'></script>
	<script type = 'text/javascript'>
		var title = "<?php echo $title; ?>";
	</script>
</head>
<body onload = 'javascript:showBundle(title);'>

<div class = 'intro'>
	<object id = 'hibWidget' data="http://www.humblebundle.com/_widget/html">This is meant to be a widget that shows the status of the current Humble Indie Bundle. If you can't see it, don't worry - the stats are still here :)</object>
	<h1>Some More Humble Visualisations</h1>
	<p>Hi!</p>
	<p>You're looking at some work in progress stuff. You can get back to the Humble Visualisations by <a href = '../'>clicking here</a>.</p> 
	<p>If you have any questions or comments, please <a href = 'mailto:cheese@twolofbees.com'>get in touch</a>.</p>
	
	<p>Cheese</p>

	<h3>Currently there is hourly data available for the following weekly sales</h3>
	<?php showBundleList(); ?>
	<p>Note that the Humble Weekly Sale: Telltale Games is missing the first two days of data.</p>
	
	<p>Also available for <a href = '../ts/'>Humble Bundle promotions</a>.</p>
</div>
<div id = 'chartPlayground'></div>
<div class = 'intro' id = 'bottom'>
	<h3>Some Notes!</h3>
	<p>Ooher, more charts!</p>
	<p>This is still in development, so please be patient ^_^ (<em>head back <a href = '../'>here</a> for more stuff</em>)</p>
	<p>Several promotions have gaps in the data (<em>caused by maintenance on my host, Humble's uptime and the odd unforseen markup change, etc.</em>). In the graphs that show hourly data, the first data point following a gap will include the sum of the skipped period.</p>
	<p>If you have any questions or comments, please <a href = 'mailto:cheese@twolofbees.com'>get in touch</a>.</p>
	<p>I can be followed/stalked/casually observed on twitter as <a href = 'http://twitter.com/twolofbees'>@twolofbees</a>.</p>
</div>
<footer>
Proudly presented by Cheeseness of <a href = 'http://cheesetalks.twolofbees.com'>cheesetalks.twolofbees.com</a> and <a href = 'https://twitter.com/twolofbees'>@twolofbees</a>.
</footer>
</body>
</html>

