<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
<?php
	$title = "warner";
	if (isset($_GET['bundle']))
	{
		$title = urldecode($_GET['bundle']);
	}
?>
<head>
	<meta charset='utf-8' />
	<meta name='viewport' content='width=device-width' />

	<title>The Humble Visualisations (hourly data preview of bundle promotions)</title>

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

	<h3>Currently there is hourly data available for the following bundles</h3>
	<ul>
		<li><a href = '?bundle=Indie+6'>Humble Indie Bundle 6</a> (missing first 5 hours)</li>
		<li><a href = '?bundle=eBook'>Humble eBook Bundle</a></li>
		<li><a href = '?bundle=Android+4'>Humble Bundle for Android 4</a></li>
		<li><a href = '?bundle=Indie+7'>Humble Indie Bundle 7</a></li>
		<li><a href = '?bundle=Mojam+2'>Humble Bundle Mojam 2</a></li>
		<li><a href = '?bundle=Android+5'>Humble Bundle with Android 5</a></li>
<!--		<li><a href = '?bundle=Bastion'>Humble Weekly Sale: Bastion</a></li>-->
		<li><a href = '?bundle=Mobile'>Humble Mobile Bundle</a> (only showing average payment)</li>
		<li><a href = '?bundle=Double+Fine'>Humble Double Fine Bundle</a> (missing first 18 hours)</li>
		<li><a href = '?bundle=indie+8'>Humble Indie Bundle 8</a></li>
		<li><a href = '?bundle=android+6'>Humble Bundle with Android 6</a></li>
		<li><a href = '?bundle=eBook+2'>Humble eBook Bundle 2</a></li>
		<li><a href = '?bundle=deep+silver'>Humble Deep Silver Bundle</a> (missing separate price value data - was initially $190 and rose to $230)</li>
		<li><a href = '?bundle=bollocks'>Humble Origin Bundle</a></li>
		<li><a href = '?bundle=comedy'>Humble Comedy Bundle</a></li>
		<li><a href = '?bundle=indie+9'>Humble Indie Bundle 9</a></li>
		<li><a href = '?bundle=mobile+2'>Humble Mobile Bundle 2</a></li>
		<li><a href = '?bundle=android+7'>Humble Bundle: PC and Android 7</a></li>
		<li><a href = '?bundle=warner'>Humble WB Games Bundle</a></li>
	</ul>
	
	<p>Also available for <a href = '../weekly/'>Humble Weekly Sales</a>.</p>
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

