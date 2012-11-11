<?php
	/**
	* This file was created for my Humble Visualisations page which calculates extra
	* statistics and trends for the Humble Indie Bundle promotions.
	* I make no guarantees about its fitness for purpose.
	* Use, modify, learn from, whatever as you see fit <3
	* copyleft 2011 Cheeseness (public domain)
	*/

	//So that I don't share my creds, I've put functions that return my database username, password, etc. into functions that live in here. You can either create your own dbCreds.php or you can replace the function calls with strings or variables.
	include_once("dbcreds.php");
	
	//Let's make sure we're pulling time in UTC, OK?
	putenv("TZ=UTC");

	//Let's connect to the database server and set the database we'll be using 
	$con = ConnectToMySQL(getDBHost(), getDBUser(), getDBPass());
	if(!$con)
	{
		echo "Cannot Connect To MySQL: " . mysql_error();
	}
	ConnectToDB(getDBName(), $con);

	/**
	* This function exports a dump of the database to a compressed file.
	*/
	function dumpData()
	{
		passthru("mysqldump --opt --host=" . getDBHost() . " --user=" . getDBUser() . " --password=" . getDBPass() . " --databases " . getDBName() . " --add-drop-database --add-drop-table | gzip > downloads/data.sql.gz");
	}
	
	
	/**
	* This function attempts to give us a quick and dirty short version of the
	* given bundle title.
	*/
	function getShortTitle($title)
	{
		return str_replace(array("The Humble Bundle for ", "The Humble Bundle ", "The Humble ", " Bundle", " Debut"),"", $title);
	}
	
	
	/**
	* This function connects us to the specified database server using the
	* given connection.
	*/
	function ConnectToMySQL($host, $user, $pass=null) {
		global $TEST;
		if($TEST) echo "MySQL: Connecting to MySQL Server $host as $user<br />";
		return mysql_connect($host, $user, $pass);
	}
	
	
	/**
	* This function selects the specified database for queries via the
	* given connection.
	*/
	function ConnectToDB($dbname, $connection=null) {
		global $TEST;
		if($TEST)
		{
			echo "MySQL: Connecting to Database $dbname using connection $connection";
		}
		
		if(mysql_select_db($dbname, $connection)) 
		{
			if($TEST)
			{
				echo "MySQL: Connection Successful";
			}
			return true;
		}
		else
		{
			if($TEST)
			{
				echo "MySQL: Error connecting to Database $dbname : " . mysql_error();
			}
			return false;
		}
	}
	
	
	/**
	* This function simplifies executing SQL queries.
	*/
	function runQuery($query, $output = true)
	{
		global $TEST;
		if($TEST && $output)
		{
			echo $query;
		}
		$result = mysql_query($query) or die(mysql_error());
		return $result;
	}
	
	
	/**
	* This function turns a string representing a dollar figure into a
	* number.
	* TODO: It should probably do something when the result is found to be
	* NaN.
	*/
	function parseDollars($value)
	{
		$number = (float) str_replace(array("$", ","),"", $value);
		if (is_nan($number))
		{
			echo "Oh noes!";
		}
		return $number;
	}
	
	
	/**
	* This function simplifies getting information from inside DOM
	* elements.
	* TODO: It should probably have some sort of error checking incase the
	* given element is not found.
	*/
	function getValue($elementID, $document)
	{
		$node = $document->getElementById($elementID);
		return $node->nodeValue;
	}
	
	
	/**
	* This function takes two values and returns the difference between
	* as a signed dollar figure with the text " over" or " under"
	* appended as appropriate.
	*/
	function getDeviationString($value, $target)
	{
		$deviation = $value - $target;
		if ($deviation > 0)
		{
			return "+$" . number_format($deviation, 2) . " over";
		}
		else
		{
			return "-$" . number_format(abs($deviation), 2) . " under";		
		}
	}
	
	
	/**
	* This function reads the source of the humblebundle.com web page
	* in, scrapes the relevant data out and inserts it into the database.
	* If the detected bundle title does not exist in the db, a new record
	* is created.
	*/
	function parseData($url)
	{
		//Tell the server who we are - if you use or edit this for your own purposes, please change the user agent string to something appropriate for what you're doing <3
		ini_set("user_agent", "HumbleStatsParser (http://cheesetalks.twolofbees.com/)");

		
		//include meklu's robots.txt parsing library
		include_once("rbt_prs.php");

		//Check robots.txt to make sure we're allowed first
		if (!isUrlBotSafe($url, ini_get("user_agent")))
		{
			//Return something we can identify for error reporting
			return false;
		}

		//Grab the page source
		$page = file_get_contents($url) or die("No luck luck getting url: $url");
		
		//If we had trouble with that, there's no point in continuing.
		if ($page === false)
		{
			return false;
		}
		
		//We're going to use DOMDocument to pull everything out
		$dom = new DOMDocument;
		//We use the @ to suppress all the warnings caused by improperly escaped & chars and some questionable tag closures. Unfortunately, this means we don't get 
		@$dom->loadHTML($page);
		
		//If that doesn't work, there's no point in continuing either.
		if ($dom === false)
		{
			return false;
		}
		
		//All these things are simple to get out
		$paymentTotal = parseDollars(getValue('totalcontributed', $dom));
		$paymentAverage = parseDollars(getValue('averagecontribution', $dom));
		$avLin = parseDollars(getValue('averagelinux', $dom));
		$avMac = parseDollars(getValue('averagemac', $dom));
		$avWin = parseDollars(getValue('averagewindows', $dom));
		
		//The bundle title can be a few different variations of things depending upon the status of the bundle, plus it has a whole stack of unnecessary whitespace
		$bundleTitle = str_replace(array("\n", "\t"), '', strip_tags(getValue('hibtext', $dom)));
		$bundleTitle = trim(preg_replace('/\s\s+/', ' ', $bundleTitle)); //they don't make this easy, do they?
		$bundleTitle = str_replace("Thanks for purchasing the ", "The ", $bundleTitle); //this allows us to parse saved download pages (we can't pull them live since robots.txt doesn't allow it
		$bundleTitle = str_replace("!", "", $bundleTitle);
		
		//Let's check and see if the bundle is finished
		$isOver = false;
		if (stripos($bundleTitle, " is now") !== false)
		{
			$bundleTitle = str_replace(" is now closed", "", $bundleTitle); //This stops us from making a new entry for closed bundles :D
			$bundleTitle = str_replace(" is now over", "", $bundleTitle); //This wording for expired bundles was added with the Botanicula Debut
			$isOver = true;
		}

		//The "full price" value is tricky to grab as well
		$fullPrice = getValue('pwyw', $dom);
		//We don't need the HTML tags (in fact, they're just going to get in the way
	        $fullPrice = strip_tags($fullPrice);
	        //Shorten the string to everything from $ symbol
		$fullPrice = substr($fullPrice, strpos($fullPrice, "$"));
		//And now let's drop everything from (including) the first space, as well as the $ symbol
		$fullPrice = substr($fullPrice, 1, (- (strlen($fullPrice) - strpos($fullPrice, " "))) - 1);

		$purchaseTotal = "Unknown";

		//guess what, the purchase total span is only identified by a class. Yay.
		$pathfinder = new DomXPath($dom); //because who doesn't like things called Pathfinder?
		$nodes = $pathfinder->query("//span[ contains (@class, 'totalcontributions') ]");
		foreach ($nodes as $node)
		{
			$purchaseTotal = parseDollars($node->nodeValue);
		}
			
		//Time to parse some data out of the chart URL
		$chartNode = $dom->getElementById('googlechart');
		$chartURL = parse_url($chartNode->getAttribute('src'));
		$chartURL = split("&", $chartURL['query']);
	
		$chartElements = array();
		foreach ($chartURL as $key => $value)
		{
			$temp = split("=", $value);
			$chartElements[$temp[0]] = $temp[1];
		}
	
		//Chop up the colour and data params, remembering to remove the t: (two chars) from the beginning of the data set
		$colours = split("\|", $chartElements['chco']);
		$data = split(",", $chartElements['chd']);
		$data[0] = substr($data[0], 2);
		
		//I find this immensely confusing. The r *should* indicate that the label/colour order is the reverse of the data set order, but it doesn't seem to be. Oh well.
		if ($chartElements['chdlp'] == "r")
		{
			//$data = array_reverse($data);
		}
		
		$pcLin = 0;
		$pcMac = 0;
		$pcWin = 0;
		
		//Work out which data element is for which platform based on the colours assignment: Lin Blue, Mac Green, Win Red
		foreach ($colours as $index => $platform)
		{
			if ($platform == "333388")
			{
				$pcLin = $data[$index];
			}
			else if ($platform == "338833")
			{
				$pcMac = $data[$index];
			}
			else if ($platform == "992222")
			{
				$pcWin = $data[$index];
			}
		}

		//Is there an existing record with this title?
		$query = "select id from scrapedata where bundleTitle = '" . $bundleTitle . "'";
		$result = runQuery($query);
		if(mysql_num_rows($result) > 0)
		{
			//Update the existing record
			$existingRecord = mysql_fetch_array($result, MYSQL_ASSOC);
			$query = "update scrapedata set lastUpdated = utc_timestamp(), paymentTotal = '" . $paymentTotal . "', purchaseTotal = '" . $purchaseTotal . "', pcLin = '" . $pcLin . "', pcMac = '" . $pcMac . "', pcWin = '" . $pcWin . "', paymentAverage = '" . $paymentAverage . "', avLin = '" . $avLin . "', avMac = '" . $avMac . "', avWin = '" . $avWin . "', fullPriceLast = '" . $fullPrice . "'";
			if (!$isOver)
			{
				$query .= ", lastSeen = utc_timestamp()";
			}
			else
			{
				$query .= ", isOver = 1";
			}
			
			$query .= " where id = '" . $existingRecord['id']. "'";
			$result2 = runQuery($query);
			
			//Do an updated dump of the database and return the number of rows that the query updated (it's not really that relevant, but it's nice for debugging)
			dumpData();
			return mysql_affected_rows();
		}
		else
		{
			//Insert our discovered data
			$query = "insert into scrapedata (bundleTitle, lastUpdated, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, firstSeen, fullPriceFirst, fullPriceLast) values ('" . $bundleTitle . "', utc_timestamp(), '" . $paymentTotal . "', '" . $purchaseTotal . "', '" . $pcLin . "', '" . $pcMac . "', '" . $pcWin . "', '" . $paymentAverage . "', '" . $avLin . "', '" . $avMac . "', '" . $avWin . "', utc_timestamp(), '" . $fullPrice . "', '" . $fullPrice . "')";
			$result2 = runQuery($query);
			
			//Do an updated dump of the database and return the number of rows that the query inserted (it's not really that relevant, but it's nice for debugging)
			dumpData();
			return mysql_affected_rows();
		}
	}
?>
