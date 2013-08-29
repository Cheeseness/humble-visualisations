<?php
	if (!headers_sent())
	{
		header("Access-Control-Allow-Origin: *");
		header('Content-type: application/json');
	}
	//Include our parser/database/functions library
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

	$bundleTitle = "Humble Weekly Sale: Blendo Games";
	if(isset($_GET['bundle']))
	{
		if (strcasecmp($_GET['bundle'], "tripwire") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Tripwire";
		}
		else if (strcasecmp($_GET['bundle'], "bastion") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Bastion";
		}
		else if (strcasecmp($_GET['bundle'], "telltale") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Telltale Games";
		}
		else if (strcasecmp($_GET['bundle'], "serious sam") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Serious Sam";
		}
		else if (strcasecmp($_GET['bundle'], "11 bit") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: 11 bit studios";
		}
		else if (strcasecmp($_GET['bundle'], "rochard") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Rochard";
		}
		else if (strcasecmp($_GET['bundle'], "two tribes") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Two Tribes";
		}
		else if (strcasecmp($_GET['bundle'], "spiderweb") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Spiderweb Software";
		}
		else if (strcasecmp($_GET['bundle'], "jim guthrie") == 0)
		{
			$bundleTitle = "The Humble Weekly Sale: Jim Guthrie and Friends";
		}
		else if (strcasecmp($_GET['bundle'], "1c company") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: 1C Company";
		}
		else if (strcasecmp($_GET['bundle'], "introversion") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Introversion";
		}
		else if (strcasecmp($_GET['bundle'], "pewdiepie") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Hosted by PewDiePie";
		}
		else if (strcasecmp($_GET['bundle'], "paradox") == 0)
		{
			$bundleTitle = "The Humble Weekly Sale: Paradox Interactive";
		}
		else if (strcasecmp($_GET['bundle'], "chicken") == 0)
		{
			$bundleTitle = "Humble Weekly Sale: Chicken";
		}
	}

	$bundleData = getData($bundleTitle);
	echo json_encode($bundleData);	


	/**
	* This function attempts to give us a quick and dirty short version of the
	* given bundle title.
	*/
	function getShortTitle($title)
	{
		return str_replace(array("Humble Weekly Sale: ", "The Humble Bundle for ", "The Humble Bundle with ", "Humble Bundle with ", "The Humble Bundle ", "The Humble ", " Bundle", " Debut"),"", $title);
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
	* This function yoinks all the relevant data from the database and returns an array containing calculated statistics for every bundle.
	*/
	function getData($bundleTitle = "Humble Weekly Sale: Tripwire")
	{
		//Select all the data for all the bundles

		$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, fullPrice, firstPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/60/60) as hour from (select * from scrapedata_weekly where bundleTitle = '" . $bundleTitle . "') as currentBundle , (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata_weekly where bundleTitle = '" . $bundleTitle . "') as minDate order by lastUpdated asc";
//		$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, fullPrice, firstPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/60/60) as hour from (select * from scrapedata_weekly where bundleTitle = (select bundleTitle from scrapedata_weekly order by lastUpdated desc limit 1)) as currentBundle , (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata_weekly where bundleTitle = (select bundleTitle from scrapedata_weekly order by lastUpdated desc limit 1)) as minDate order by lastUpdated asc";
//		$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, fullPrice, firstPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/60/60) as hour from scrapedata2, (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata2) as minDate order by lastUpdated asc";
//$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, max(paymentTotal) as paymentTotal, max(purchaseTotal) as purchaseTotal, avg(pcLin) as pcLin, avg(pcMac) as pcMac, avg(pcWin) as pcWin, avg(paymentAverage) as paymentAverage, avg(avLin) as avLin, avg(avMac) as avMac, avg(avWin) as avWin, max(fullPrice) as fullPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/120/60) as hour, floor(hour(lastUpdated)/2) as bihour from scrapedata2, (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata2) as minDate group by hour order by lastUpdated asc";

		$result = runQuery($query);

		//This array is going to store everything so that it can be returned and manipulated later
		$returnData = array();


		//loop through to create derived statistics for each bundle and add to an array of bundles
		//For every bundle that we have data for, let's loop through and make some stats!
		//$returnData = array();

		$rvLinLast = 0;
		$rvMacLast = 0;
		$rvWinLast = 0;
		$puLinLast = 0;
		$puMacLast = 0;
		$puWinLast = 0;

		$bundleTitle = "";

		$first = false;
		while ($bundle = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			//Calculate total revenue per platform based on the percentage of the cross-platform total
			$rvLin = $bundle['pcLin'] * $bundle['paymentTotal'];
			$rvMac = $bundle['pcMac'] * $bundle['paymentTotal'];
			$rvWin = $bundle['pcWin'] * $bundle['paymentTotal'];

			//Calculate the total purchase count for each platform by dividing the calculated total revenue for each platform by its average purchase price
			if ($bundle['avLin'] > 0)
			{
				$puLin = $rvLin / $bundle['avLin'];
			}
			else
			{
				$puLin = 0;
			}
			if ($bundle['avMac'] > 0)
			{
				$puMac = $rvMac / $bundle['avMac'];
			}
			else
			{
				$puMac = 0;
			}
			if ($bundle['avWin'] > 0)
			{
				$puWin = $rvWin / $bundle['avWin'];
			}
			else
			{
				$puWin = 0;
			}

			if (!$first)
			{

			$returnData[$bundle["lastUpdated"]] = array("lastUpdated" => $bundle["lastUpdated"], "ts" => $bundle["ts"], "puLinDiff" => floor($puLin - $puLinLast), "puMacDiff" => floor($puMac - $puMacLast), "puWinDiff" => floor($puWin - $puWinLast), "pyLinDiff" => round(($rvLin - $rvLinLast), 2), "pyMacDiff" => round(($rvMac - $rvMacLast), 2), "pyWinDiff" => round(($rvWin - $rvWinLast), 2), "puLin" => floor($puLin), "puMac" => floor($puMac), "puWin" => floor($puWin), "pyLin" => round($rvLin, 2), "pyMac" => round($rvMac, 2), "pyWin" => round($rvWin, 2), "avLin" => round($bundle['avLin'], 2), "avMac" => round($bundle['avMac'], 2), "avWin" => round($bundle['avWin'], 2), "avAll" => round($bundle['paymentAverage'], 2), "fullPrice" => $bundle['fullPrice'], "fullPriceDiff" => $bundle['fullPrice'] - $bundle['firstPrice'], "firstPrice" => $bundle['firstPrice']);
			}
			else
			{
				$first = false;
			}

			$bundleTitle = $bundle['bundleTitle'];
			$rvLinLast = $rvLin;
			$rvMacLast = $rvMac;
			$rvWinLast = $rvWin;
			$puLinLast = $puLin;
			$puMacLast = $puMac;
			$puWinLast = $puWin;
			$puWinLast = $puWin;

		}
		
		return array("bundleTitle" => $bundleTitle, "data" => $returnData);
	}
	
	
	
?>
