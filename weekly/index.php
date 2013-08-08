<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
<?php
	$title = "1c+company";
	if (isset($_GET['bundle']))
	{
		$title = urldecode($_GET['bundle']);
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
	<ul>
		<li><a href = '?bundle=Bastion'>Humble Weekly Sale: Bastion</a></li>
		<li><a href = '?bundle=Tripwire'>Humble Weekly Sale: Tripwire</a></li>
		<li><a href = '?bundle=Blendo+Games'>Humble Weekly Sale: Blendo Games</a></li>
		<li><a href = '?bundle=Telltale'>Humble Weekly Sale: Telltale Games</a> (missing first two days)</li>
		<li><a href = '?bundle=serious+sam'>Humble Weekly Sale: Serious Sam</a></li>
		<li><a href = '?bundle=11+bit'>Humble Weekly Sale: 11 bit studios</a></li>
		<li><a href = '?bundle=rochard'>Humble Weekly Sale: Rochard</a></li>
		<li><a href = '?bundle=two+tribes'>Humble Weekly Sale: Two Tribes</a></li>
		<li><a href = '?bundle=spiderweb'>Humble Weekly Sale: Spiderweb Software</a></li>
		<li><a href = '?bundle=jim+guthrie'>Humble Weekly Sale: Jim Guthrie and Friends</a></li>
		<li><a href = '?bundle=1c+company'>Humble Weekly Sale: 1C Company</a></li>
	</ul>
	
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

