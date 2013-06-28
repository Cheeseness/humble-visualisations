<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
<head>
	<meta charset='utf-8' />
	<meta name='viewport' content='width=device-width' />

	<title>The Humble Visualisations</title>

	<link rel='shortcut icon' href='images/fav.png' type='image/x-icon' />
	<link rel='stylesheet' href='styles/default.css' type='text/css' />

	<script type='text/javascript' src='scripts/d3/d3.v3.min.js'></script>
	<script type='text/javascript' src='scripts/charts.js'></script>
</head>
<body onload = 'javascript:init();'>
<noscript><div class = 'intro'><h1>Oh noes!</h1><p>You've encountered the not-so-friendly-edge of the double-edged-ness of this new version of the Humble Visualisations! It uses the awesome D3 visualisation library, which requires JavaScript. You're not going to see much at all here.</p><p>This was a tough call for me to make, and if you feel inconvenienced by this, please <a href = 'mailto:cheese@twolofbees.com'>get in touch</a> or comment on <a href = 'https://github.com/Cheeseness/humble-visualisations/issues/1'>this GitHub issue</a> - I would like to know so that I can assess how much interest there is in non-JavaScript based presentation of this data.</p><p>Cheese</p></div></noscript>
<div class = 'intro'>
	<object id = 'hibWidget' data="http://www.humblebundle.com/_widget/html">This is meant to be a widget that shows the status of the current Humble Indie Bundle. If you can't see it, don't worry - the stats are still here :)</object>
	<h1>The Humble Visualisations!</h1>
	<p>First up, if you haven't already, go to humblebundle.com and have a look at what these guys are doing. They're doing lots of worthwhile stuff that you should know about if you're interested in or care about the gaming industry.</p>
	<p>This page aims to be an aid to understanding the statistics for the pay-what-you-want game bundles promoted by Humble Bundle Inc by calculating some extra numbers from the already available stats. You can click the tabs at the bottom of the window for more info (<strong>seriously, just click the <a onclick = 'javascript: showHelper("help"); return false;'>help one</a> and skim it for a few seconds</strong>).</p>
	<p>You can show and hide individual bundles in the "<a href = '#chartPlayground'>chart playground</a>" by clicking on them in the <a href = '#timeline'>timeline</a> and <a href = '#revenueTable'>revenue table</a>. This is also reflected in the <a href = 'overTimeValue'>X vs Time charts</a> at the bottom of the page (at page load, these show all promotions of the current promotion's type).</p>
	<p>Please note that for obvious reasons any in-progress promotion's figures will only be an indication of that promotion's performance so far. The figures for in-progress promotions update at least every hour, so if you're interested, don't forget to check back later.</p>
	<p>If you have any questions or comments, please get in touch.</p>
	<p>Cheese</p>
</div>

<div id = 'currentStats'>
	<div class = 'bundle' id = 'current'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "Per platform statistics for the current Humble Bundle promotion." /></div>
	<div class = 'bundle' id = 'currentCategory'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "Aggregated per platform statistics for the current Humble Bundle promotions of the current promotion's type." /></div>
	<div class = 'bundle' id = 'aggregateAll'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "Aggregated per platform statistics for all Humble Bundle promotions." /></div>
</div>
<div id = 'timeline' class = 'widestats'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "This table shows the frequency with which Humble Bundle promotions have occurred alongside the revenue raised for each." /></div>
<div id = 'chartPlayground'>
<h1><a href = '#chartPlayground' class = 'chartTitle'>Chart Playground</a></h1>
<p>Click on the table rows above to show and hide details of individual promotions.</p>
<div id = 'bundleStatsAg'></div>
<div id = 'bundleStats'></div>
<br class = 'clearfix' />
</div>

<div id = 'overTimeValue' class = 'widestats'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "This graph shows the revenue from each platform for the selected promotions." /></div>
<div id = 'overTimeRevenue' class = 'widestats'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "This graph shows the purchase count for each platform for the selected promotions." /></div>
<div id = 'overTimePurchases' class = 'widestats'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "This graph shows the purchase count for each platform for the selected promotions." /></div>
<div id = 'overTimeAverages' class = 'widestats'><img src = 'images/humvis_loading.gif' class = 'loadingImage' alt = "This graph shows the average purchase price for each platform for the selected promotions." /></div>

<ul id = 'helperTargets'>
	<li id = 'help_target'>Howto/Help!</li>
	<li id = 'footnotes_target'>Footnotes</li>
	<li id = 'sightings_target'>Sighted Citings</li>
	<li id = 'about_target'>About/History</li>
	<li id = 'source_target'>Source/Contribute</li>
</ul>
<div id = 'helpers'>
<div id = 'help' class = 'popup'>
	<img src = 'images/humvis2_overview.png' alt = 'Humble Visualisations Site Layout' id = 'overviewThumb' />
	<h2>How To Use</h2>
	<p>Reformat this and include a thumbnail showing page layout</p>
	<p>The first set of charts and graphs shows calculated statistics for the current/most recent Humble Bundle promotion, the aggregated statistics for all promotions of that type and the aggregated statistics for all Humble Bundle promotions combined. Following that is an illustrative timeline showing the frequency of promotions and a table showing comparative revenue which can also be clicked to show and hide specific promotions' figures in the "chart playground". Beneath the "chart playground" are charts showing variations in separate price value, revenue, purchases and averages across the promotions shown in the "chart playground" (or all the promotions of the current one's type if nothing is selected).</p>

	<h2>Hate this new version?</h2>
	<p>You can see something close to the original presenation by following <a href = 'index2.php'>this link</a>. Note that it still uses D3 and has all the same interactivity minus the chart playground.</p>
	<p>Don't expect this to work in older browsers. I don't.</p>
	<br class = 'clearfix' />
</div>
<div id = 'footnotes' class = 'popup'>
	<h2>Specific notes</h2>
	<p id = 'foot1'><span class = 'footnote'>1</span>: Promotion dates are recorded when the new promotion title is first seen. Everything older than the Humble Indie Bundle #4 inclusive were manually added in January 2011 with dates sourced from the <a href = 'http://en.wikipedia.org/wiki/Humble_Indie_Bundle'>Humble Indie Bundle</a> article on Wikipedia. End dates are now recorded when "is now over" is detected in the bundle title. End dates for the Android #3 bundle and prior were sourced from the <a href = 'http://en.wikipedia.org/wiki/Humble_Indie_Bundle'>Humble Indie Bundle</a> article on Wikipedia.</p>
	<p id = 'foot2'><span class = 'footnote'>2</span>: Data is read directly from the Humble Bundle page hourly and stored in a MySQL database. Data for past bundles is assumed to be static (initial values for earlier bundles have been imported from a saved copy of my download page for each bundle - if you notice that the details for an expired bundle are incorrect, please <a href = 'mailto:cheese@twolofbees.com'>let me know</a>).</p>
	<p id = 'foot3'><span class = 'footnote'>3</span>: As noted, the calculated figures have variances. These are most likely the result of rounding, payments of $0.01 and users who did not select or selected multiple operating systems. Bundles without cross-platform stats available (initially the THQ bundle) don't allow per-platform stats to be calculated, and so their total number of purchases appear as a variation. Promotions without cross-platform data do not display variances.</p>
	<p id = 'foot4'><span class = 'footnote'>4</span>: "Separate price" values up to and including the Humble Bundle for Android 2 onwards were sourced from developer prices where available (and Steam prices where not, excluding Canabalt, for which the iTunes price was used) as at 11th of April 2012. Values for later bundles have been parsed from the humblebundle.com website. Keep in mind that some bundle titles (including tech demos, prototypes and game jam outcomes) have no price associated with them.</p>

	<p>Though I believe the visualisations present an adequate representation of different aspects of the data available, I make no guarantees about the accuracy of the calculations or the results, and offer transparency instead. The source is <a href = 'https://github.com/Cheeseness/humble-visualisations'>available on GitHub</a> (sans db credentials), and a copy of the <a href = 'downloads/data.sql.gz'>most recent data set</a> is also available for anybody who wants to see how it works or use it for something else (note that this contains historical tables from previous versions as well as additional hourly data for regular and weekly promotions).</p>

	<h2>Questions!</h2>
	<p><strong>Gah! Why do you put Linux first in everything?</strong> Whilst I may be biased towards Free/Open Source Software, this has nothing to do with the order in which platforms are displayed. It's just a happy coincidence that alphabetical ordering puts Linux first ;)</p>
	<p><strong>Can we see how the charity/developer donations were distributed?</strong> Honestly, this is probably the #1 piece of data I'd love to have my hands on. To my knowldege, this information is not readily available for any but the <a href = 'http://www.wolfire.com/humble/stats'>first bundle</a>. If enough people ask me, I'll put together some separate charts for that ;)</p>
	<p><strong>Can we see [<em>miscellaneous piece of data not shown on the Humble Bundle website</em>]?</strong> Currently, everything that is shown here is derived <em>exclusively</em> from information available from the Humble Bundle website. I think this is an important part of the data's credibility, and I'm not willing to compromise that. There is a lot of information that can be found in Jeffrey and John's <a href = 'http://www.gdcvault.com/play/1014437/The-Humble-Indie'>GDC 2011 talk</a> if you're keen to know more stuff.</p>
	<p><strong>Your JavaScript is awful and you should feel awful!/Can I help?/Where can I send suggestions and feedback?</strong> I've got a <a href = 'https://github.com/Cheeseness/humble-visualisations/'>GitHub repository</a> up for the Humble Visualisations, and I'm keen to have suggestions and feedback as issues <a href = 'https://github.com/Cheeseness/humble-visualisations/issues'>there</a> where stuff can be referenced in commits/pull requests. Please do a search before submitting an issue so that we can keep all related discussion together.</p>

	<p><strong>You're showing us a lot of stuff here, but you're not telling us what it <em>means</em>?</strong> I think it's important to separate information from interpretation, and I'm keen to let other people come to their own conclusions before suggesting what it might all mean. If you click the <strong><a onclick = 'javascript: showHelper("sightings"); return false;'>Sighted Citings</a></strong> tab above, you can see a list of my own and other people's ramblings about and interpretations of the Humble Visualisations' data.</p>
	<p><strong>Your excessive verbosity is exciting to me, can I contact you?</strong> Sure, why not? Details can be found by clicking the <strong><a onclick = 'javascript: showHelper("about"); return false;'>About/History</a></strong> tab above. ^_^</p>

</div>
<div id = 'sightings' class = 'popup'>
	<h2>Spotted Citings</h2>
	<ul>
		<li><a href = 'http://www.heise.de/open/artikel/Die-Woche-Die-Linux-Spiele-kommen-1648026.html'>Die Woche: Die Linux-Spiele kommen</a> - An article on German open source news site heise.de, which talks about how Linux users are willing to pay for games.</li>
		<li><a href = 'http://en.wikipedia.org/wiki/Humble_Indie_Bundle'>Humble Indie Bundle</a> - It seems this page has been mentioned on the German Humble Indie Bundle Wikipedia article.</li>
		<li><a href = 'http://www.reclaimyourgame.com/content.php/222-RYG-Interviews-Cheese'>RYG Interviews Josh Bush (Cheese)</a> - In September 2012, Reclaim Your Game interviewed me about the Humble Bundle stats work I've been doing.</li>
		<li><a href = 'http://news.softpedia.com/news/Linux-Users-Pay-More-for-Humble-Bundles-than-Mac-Windows-294778.shtml'>Linux Users Pay More for Humble Bundles than Mac/Windows Ones</a> - A small news post on Softpedia highlighting high Linux averages.</li>
		<li><a href = 'http://www.smh.com.au/it-pro/innovation/blogs/smoke--mirrors/bundle-of-joy-winning-formula-tested-on-ebooks-20121012-27h34.html'>The Humble Bundle brings ebooks to their offerings</a> - A look at the Humble eBook Bundle within the context of Humble Bundle history by George Wright, columnist for the Sydney Morning Herald.</li>
	</ul>
	<p>If you find something I've missed, please <a href = 'mailto:cheese@twolofbees.com'>let me know</a>.</p>
	<h2>Cheese's Articles</h2>
	<ul>
		<li><a href = 'http://cheesetalks.twolofbees.com/humble_presentation/'>Cross-platform Support In Humble Bundles</a> - A browser based slideshow covering most of the topics from my third Humble Bundle stats article.</li>
		<li><a href = 'http://cheesetalks.twolofbees.com/humbleStats3.php'>Cheese talks to himself (about cross-platform support in Humble Bundles)</a> - My third article, this time focusing more on cross platform support and porting outcomes from Humble Bundle promotions.</li>
		<li><a href = 'http://cheesetalks.twolofbees.com/humbleStats2.php'>Cheese talks to himself (about Humble Bundle statistics again)</a> - Another article by myself which looks at the Humble Music Bundle as a "control sample" for understanding the statistics of other bundles.</li>
		<li><a href = 'http://cheesetalks.twolofbees.com/humbleStats.php'>Cheese talks to himself and others (about Humble Bundle statistics)</a> - An article by myself which has a bunch of additional graphs with information from additional sources and some quotes/comments from top Humble Bundle contributors.</li>
	</ul>
</div>
<div id = 'about' class = 'popup'>
	<h2>What is this?</h2>
	<p>First up, if you haven't already, go to <a href = 'http://www.humblebundle.com'>humblebundle.com</a> and have a look at what these guys are doing. They're doing lots of worthwhile stuff (you can jump to the <a href = '#bottom'>bottom</a> for some examples) that you should know about if you're interested in or care about the gaming industry.</p>
	<p>This page aims to be an aid to understanding the statistics for the pay-what-you-want game bundles promoted by Humble Bundle Inc by calculating some extra numbers from the already available stats. Some additional contextual information is shown if you mouse over each chart/graph. More info/extra notes can be found by clicking on the tabs like the one you clicked to show this :D</p>
	<p>What you see here is the third iteration of the Humble Visualisations. The original was a shared <a href = 'https://docs.google.com/spreadsheet/ccc?key=0AuZwvMMD9ojmdGhZV0hRSGNsYkpoNkVWdDBxdHpGLUE&usp=sharing'>Google Docs spreadsheet</a> which calculated additional figures and generated URLs for <a href = 'https://developers.google.com/chart/image/'>Google Image Charts</a> from manually entered data. The second used <a href = 'http://php.net'>PHP</a> to scrape the humblebundle.com website for data automatically for storage in a <a href = 'http://dev.mysql.com/'>MySQL</a> database, and was able to display images alongside the extra figures on a single page as well as show line graphs showing the fluctuations in revenue, purchases and averages across all promotions. As the number of <a href = 'http://humblebundle.com'>Humble Bundle</a> promotions grew, this became unweildy, and the current version, which uses a similar PHP parser, but provides promotion data <a href = 'getdata.php'>via JSON</a>, which is used to generate graphs and charts via <a href = 'http://d3js.org'>D3</a>, a JavaScript based visualisation library, providing more interactivity and customisation of presentation, which you can read mote about by clicking the <strong><a onclick = 'javascript: showHelper("help"); return false;'>Howto/Help!</a></strong> tab above. Huzzah!</p>
	<p>There are also a bunch of todo items in the <a href = 'https://github.com/Cheeseness/humble-visualisations/issues'>GitHub issue list</a> which should give some idea of things that are on the horizon.</p>

	<h2>Thanks!</h2>
	<p>Big thanks to the Humble Bundle guys for their work in supporting independent game development, supporting worthwhile charities, challenging misconceptions about the gaming industry, actively promoting DRM-free publishing, encouraging the open sourcing of commercial games, and promoting Linux, Mac OS and Windows as being equaly viable gaming platforms.</p>
	<p>Thanks to meklu for his robots.txt parser.</p>
	<p>Thanks to my fellow <a href = 'http://steamlug.org'>SteamLUG</a> community members including (but not limited to) meklu, flibitijibibo, adrian_broher, and xpander69 for testing and encouragement as well as everybody else who has provided feedback along the way.</p>
	<p>Thanks to madrang, RobbieThe1st and porc for the discussion in the <a href = 'http://forums.steampowered.com/forums/showthread.php?t=1897204'>Steam Powered User Forums</a> that prompted me to start putting together the Google Docs spreadsheet that eventually evolved into what you've seen here.</p>

	<h2>About Me!</h2>
	<p>My name is Josh, but my friends call me Cheese (and you can too - don't you feel special?).</p>
	<p>I dabble in a lot of things, but if you're on this page, then the ones that might interest you most are my collection of "<a href = 'http://cheesetalks.twolofbees.com/'>Cheese Talks</a>" reviews, articles and interviews where I look at and talk to people and things relating to the gaming and the Free Software world (not necessarily at the same time), the open source game <a href = 'http://neverball.org'>Neverball</a> which I contribute to, and maybe <a href = 'http://twolofbees.com'>twolofbees.com</a>, a cute art-blog-comic thing where I occasionally put fan art for games (and <a href = 'http://twolofbees.com/artwork.php?iid=959'>this vaguely relevant image</a>).</p>
	<p>I can be followed/stalked/casually observed on twitter as <a href = 'http://twitter.com/twolofbees'>@twolofbees</a>.</p>
</div>
<div id = 'source' class = 'popup'>
	<h2>GitHub</h2>
	<p>The source code for this version of the Humble Visualisations is available from my <a href = 'https://github.com/Cheeseness/humble-visualisations'>GitHub repository</a>.</p>
	<p>There are a bunch of todo items in the <a href = 'https://github.com/Cheeseness/humble-visualisations/issues'>issue list</a> which should give some idea of things that are on the horizon.</p>
	<p>Feel free to submit and comment on issues and <a href = 'https://github.com/Cheeseness/humble-visualisations/pulls'>pull requests</a>. I'll try to respond to everything within a day or two.</p>
	<p>Forking is cool, but if you do set up your own copy of stuff, <em>please</em> avoid hammering the Humble Bundle site when pulling data.</p>
</div>
</div>
<footer>
Proudly presented by Cheeseness of <a href = 'http://cheesetalks.twolofbees.com'>cheesetalks.twolofbees.com</a> and <a href = 'https://twitter.com/twolofbees'>@twolofbees</a> and contributors.
</footer>
</body>
</html>

