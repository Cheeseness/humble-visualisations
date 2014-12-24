<?php
	if (!headers_sent())
	{
		header("Access-Control-Allow-Origin: *");
		header('Content-type: application/json');
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

	$bundleTitle = "Humble Bundle with Android 5";
	if(isset($_GET['bundle']))
	{
		$t = urldecode($_GET['bundle']);

		$query = "select distinct bundleTitle from scrapedata2";
		$result = runQuery($query);
		while ($bundle = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			if (strtolower($_GET['bundle']) === strtolower(getShortTitle($bundle['bundleTitle'])) && strlen($bundle['bundleTitle']) > 0)
			{
				$bundleTitle = $bundle['bundleTitle'];
				break;
			}
		}
	}

	$bundleData = getData($bundleTitle);
	echo json_encode($bundleData);

	/**
	* This function yoinks all the relevant data from the database and returns an array containing calculated statistics for every bundle.
	*/
	function getData($bundleTitle = "Humble Weekly Sale: Tripwire")
	{
		//Select all the data for all the bundles

		$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, fullPrice, firstPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/60/60) as hour from (select * from scrapedata2 where bundleTitle = '" . $bundleTitle . "') as currentBundle , (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata2 where bundleTitle = '" . $bundleTitle . "') as minDate order by lastUpdated asc";
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

			$bundleTitle = htmlspecialchars_decode($bundle['bundleTitle'], ENT_QUOTES);
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
