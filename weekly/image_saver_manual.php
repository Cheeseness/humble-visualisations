<?php

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

	echo "Getting data...";

	$bundleData = getData();
	outputBundlePerformance($bundleData);	


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
	function getData()
	{
		echo ".";
		//Select all the data for all the bundles

		$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, fullPrice, firstPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/60/60) as hour from (select * from scrapedata_weekly where bundleTitle = (select bundleTitle from scrapedata_weekly order by lastUpdated desc limit 1)) as currentBundle , (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata_weekly where bundleTitle = (select bundleTitle from scrapedata_weekly order by lastUpdated desc limit 1)) as minDate order by lastUpdated asc";
//		$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, paymentTotal, purchaseTotal, pcLin, pcMac, pcWin, paymentAverage, avLin, avMac, avWin, fullPrice, firstPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/60/60) as hour from scrapedata2, (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata2) as minDate order by lastUpdated asc";
//$query = "select bundleTitle, lastUpdated, unix_timestamp(lastUpdated) as ts, max(paymentTotal) as paymentTotal, max(purchaseTotal) as purchaseTotal, avg(pcLin) as pcLin, avg(pcMac) as pcMac, avg(pcWin) as pcWin, avg(paymentAverage) as paymentAverage, avg(avLin) as avLin, avg(avMac) as avMac, avg(avWin) as avWin, max(fullPrice) as fullPrice, round((unix_timestamp((lastUpdated)) - unix_timestamp(firstStamp))/120/60) as hour, floor(hour(lastUpdated)/2) as bihour from scrapedata2, (select min(lastUpdated) as firstStamp, min(fullPrice) as firstPrice from scrapedata2) as minDate group by hour order by lastUpdated asc";

		$result = runQuery($query);

		//This array is going to store everything so that it can be returned and manipulated later
		$returnData = array_fill(0, 336, array("bundleTitle" => "", "lastUpdated" => "0", "ts" => "0", "puLin" => "_", "puMac" => "_", "puWin" => "_", "rvLin" => "_", "rvMac" => "_", "rvWin" => "_", "puLinDiff" => "_", "puMacDiff" => "_", "puWinDiff" => "_", "rvLinDiff" => "_", "rvMacDiff" => "_", "rvWinDiff" => "_", "avLin" => "_", "avMac" => "_", "avWin" => "_", "avAll" => "_", "fullPrice" => "_", "fullPriceDiff" => "_", "firstPrice" => "_"));
		//loop through to create derived statistics for each bundle and add to an array of bundles
		//For every bundle that we have data for, let's loop through and make some stats!
		//$returnData = array();

		$rvLinLast = 0;
		$rvMacLast = 0;
		$rvWinLast = 0;
		$puLinLast = 0;
		$puMacLast = 0;
		$puWinLast = 0;

		$first = false;
		while ($bundle = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			echo "assembling data for " . $bundle['bundleTitle'];
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

			$returnData[$bundle['hour']] = array("bundleTitle" => $bundle['bundleTitle'], "lastUpdated" => $bundle["lastUpdated"], "ts" => $bundle["ts"], "puLinDiff" => floor($puLin - $puLinLast), "puMacDiff" => floor($puMac - $puMacLast), "puWinDiff" => floor($puWin - $puWinLast), "rvLinDiff" => round(($rvLin - $rvLinLast), 2), "rvMacDiff" => round(($rvMac - $rvMacLast), 2), "rvWinDiff" => round(($rvWin - $rvWinLast), 2), "puLin" => floor($puLin), "puMac" => floor($puMac), "puWin" => floor($puWin), "rvLin" => round($rvLin, 2), "rvMac" => round($rvMac, 2), "rvWin" => round($rvWin, 2), "avLin" => round($bundle['avLin'], 2), "avMac" => round($bundle['avMac'], 2), "avWin" => round($bundle['avWin'], 2), "avAll" => round($bundle['paymentAverage'], 2), "fullPrice" => $bundle['fullPrice'], "fullPriceDiff" => $bundle['fullPrice'] - $bundle['firstPrice'], "firstPrice" => $bundle['firstPrice']);
			}
			else
			{
				$first = false;
			}

			$returnData[0]['bundleTitle'] = $bundle['bundleTitle'];
			$rvLinLast = $rvLin;
			$rvMacLast = $rvMac;
			$rvWinLast = $rvWin;
			$puLinLast = $puLin;
			$puMacLast = $puMac;
			$puWinLast = $puWin;
			$puWinLast = $puWin;

		}
		return $returnData;
	}
	
	function outputBundlePerformance($bundleData)
	{
		echo "\nOutputting bundle images...\n\n";
		
		//Let's create some variables to store our list of values in
		$puLinString = "";
		$puMacString = "";
		$puWinString = "";
		$rvLinString = "";
		$rvMacString = "";
		$rvWinString = "";
		$puLinDiffString = "";
		$puMacDiffString = "";
		$puWinDiffString = "";
		$rvLinDiffString = "";
		$rvMacDiffString = "";
		$rvWinDiffString = "";
		$avLinString = "";
		$avMacString = "";
		$avWinString = "";
		$avAllString = "";
		$fullPriceString = "";
		$firstPriceString = "";
		
		$tempArray = array("lin" => array(), "mac" => array(), "win" => array());
		$count = 0;
		$bundleTitle = $bundleData[0]['bundleTitle'];
		foreach ($bundleData as $sample)
		{
			$puLinString .= $sample['puLin'] . ",";
			$puMacString .= $sample['puMac'] . ",";
			$puWinString .= $sample['puWin'] . ",";
			
			if ($sample['rvLin'] > 0)
			{
				$rvLinString .= round($sample['rvLin'], 0) . ",";
			}
			else
			{
				$rvLinString .= "_,";
			}
			if ($sample['rvMac'] > 0)
			{
				$rvMacString .= round($sample['rvMac'], 0) . ",";
			}
			else
			{
				$rvMacString .= "_,";
			}
			if ($sample['rvWin'] > 0)
			{
				$rvWinString .= round($sample['rvWin'], 0) . ",";
			}
			else
			{
				$rvWinString .= "_,";
			}
			
			$puLinDiffString .= $sample['puLinDiff'] . ",";
			$puMacDiffString .= $sample['puMacDiff'] . ",";
			$puWinDiffString .= $sample['puWinDiff'] . ",";
			
			if ($sample['rvLinDiff'] > 0)
			{
				$rvLinDiffString .= round($sample['rvLinDiff'], 0) . ",";
			}
			else
			{
				$rvLinDiffString .= "_,";
			}
			if ($sample['rvMacDiff'] > 0)
			{
				$rvMacDiffString .= round($sample['rvMacDiff'], 0) . ",";
			}
			else
			{
				$rvMacDiffString .= "_,";
			}
			if ($sample['rvWinDiff'] > 0)
			{
				$rvWinDiffString .= round($sample['rvWinDiff'], 0) . ",";
			}
			else
			{
				$rvWinDiffString .= "_,";
			}

			$avLinString .= $sample['avLin'] . ",";
			$avMacString .= $sample['avMac'] . ",";
			$avWinString .= $sample['avWin'] . ",";
			$avAllString .= $sample['avAll'] . ",";
			$fullPriceString .= $sample['fullPriceDiff'] . ",";
			$firstPriceString .= $sample['firstPrice'] . ",";
			$count ++;
			
			$tempArray['lin'][] = $sample['puLin'];
			$tempArray['mac'][] = $sample['puMac'];
			$tempArray['win'][] = $sample['puWin'];
		}
		
		$puLinString = substr($puLinString, 0, -1);
		$puMacString = substr($puMacString, 0, -1);
		$puWinString = substr($puWinString, 0, -1);
		$rvLinString = substr($rvLinString, 0, -1);
		$rvMacString = substr($rvMacString, 0, -1);
		$rvWinString = substr($rvWinString, 0, -1);
		$puLinDiffString = substr($puLinDiffString, 0, -1);
		$puMacDiffString = substr($puMacDiffString, 0, -1);
		$puWinDiffString = substr($puWinDiffString, 0, -1);
		$rvLinDiffString = substr($rvLinDiffString, 0, -1);
		$rvMacDiffString = substr($rvMacDiffString, 0, -1);
		$rvWinDiffString = substr($rvWinDiffString, 0, -1);
		$avLinString = substr($avLinString, 0, -1);
		$avMacString = substr($avMacString, 0, -1);
		$avWinString = substr($avWinString, 0, -1);
		$avAllString = substr($avAllString, 0, -1);
		$fullPriceString = substr($fullPriceString, 0, -1);
		$firstPriceString = substr($firstPriceString, 0, -1);
		


	echo "Outputting purchase diff\n\n";

	$url = 'https://chart.googleapis.com/chart';
	$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			//"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x",
				"chbh" => "1,0,0",
				"chs" => "340x100",
				"cht" => "bvs",
				"chco" => "333388,338833,883333",
				"chd" => "t3:" . $puLinDiffString . "|" . $puMacDiffString . "|" . $puWinDiffString,
				//"chdl" => "Linux|MacOS|Windows",
				//"chtt" => $bundleTitle . " Hourly Purchases",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,0,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_purchase_diff.png", fopen($url, 'r', false, $context), 0, $context);
	$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x,y",
				"chbh" => "2,0,0",
				"chs" => "900x300",
				"cht" => "bvs",
				"chco" => "333388,338833,883333",
				"chd" => "t3:" . $puLinDiffString . "|" . $puMacDiffString . "|" . $puWinDiffString,
				"chdl" => "Linux|MacOS|Windows",
				"chtt" => $bundleTitle . " Hourly Purchases",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767|1N*s*",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,4,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_purchase_diff_big.png", fopen($url, 'r', false, $context), 0, $context);

		
		
		echo "Outputting revenue diff\n\n";
		$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			//"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x",
				"chbh" => "1,0,0",
				"chs" => "340x100",
				"cht" => "bvs",
				"chco" => "333388,338833,883333",
				"chd" => "t3:" . $rvLinDiffString . "|" . $rvMacDiffString . "|" . $rvWinDiffString,
				//"chdl" => "Linux|MacOS|Windows",
				//"chtt" => $bundleTitle . " Hourly Revenue",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,0,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_revenue_diff.png", fopen($url, 'r', false, $context), 0, $context);
	$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x,y",
				"chbh" => "2,0,0",
				"chs" => "900x300",
				"cht" => "bvs",
				"chco" => "333388,338833,883333",
				"chd" => "t3:" . $rvLinDiffString . "|" . $rvMacDiffString . "|" . $rvWinDiffString,
				"chdl" => "Linux|MacOS|Windows",
				"chtt" => $bundleTitle . " Hourly Revenue",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767|1N*cUSDzs*",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,4,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_revenue_diff_big.png", fopen($url, 'r', false, $context), 0, $context);


		echo "Outputting average diff\n\n";
		$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			//"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x",
				"chbh" => "1,0,0",
				"chs" => "340x100",
				"cht" => "lc",
				"chco" => "333388,338833,883333,ff0000",
				"chd" => "t:" . $avLinString . "|" . $avMacString . "|" . $avWinString . "|" . $avAllString,
				//"chdl" => "Linux|MacOS|Windows|All",
				"chdlp" => "r",
				"chm" => "B,ff000010,3,0,0",
				//"chtt" => $bundleTitle . " Hourly Averages",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,0,0,0,8",
				"chds" => "a",
				"chls" => "1|1|1|1")))));

	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_average_diff.png", fopen($url, 'r', false, $context), 0, $context);
	$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x,y",
				"chbh" => "2,0,0",
				"chs" => "900x300",
				"cht" => "lc",
				"chco" => "333388,338833,883333,ff0000",
				"chd" => "t:" . $avLinString . "|" . $avMacString . "|" . $avWinString . "|" . $avAllString,
				"chdl" => "Linux|MacOS|Windows|All",
				"chdlp" => "r",
				"chm" => "B,ff000010,3,0,0",
				"chtt" => $bundleTitle . " Hourly Averages",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767|1N*cUSDzs*",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,4,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_average_diff_big.png", fopen($url, 'r', false, $context), 0, $context);



		
	echo "Outputting price diff\n\n";
		$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			//"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x",
				"chbh" => "1,0,0",
				"chs" => "340x100",
				"cht" => "bvs",
				"chco" => "3D7930,A2C180",
				"chd" => "t3:" . $firstPriceString . "|" . $fullPriceString,
				//"chdl" => "Initial Titles|Extra Titles",
				//"chtt" => $bundleTitle . " Separate Price",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,0,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_price_diff.png", fopen($url, 'r', false, $context), 0, $context);
	$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x,y",
				"chbh" => "2,0,0",
				"chs" => "900x300",
				"cht" => "bvs",
				"chco" => "3D7930,A2C180",
				"chd" => "t3:" . $firstPriceString . "|" . $fullPriceString,
				"chdl" => "Initial Titles|Extra Titles",
				"chtt" => $bundleTitle . " Separate Price",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767|1N*cUSDzs*",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,4,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_price_diff_big.png", fopen($url, 'r', false, $context), 0, $context);


		echo "Outputting purchase totals\n\n";
		$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			//"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x",
				"chbh" => "1,0,0",
				"chs" => "340x100",
				"cht" => "lc",
				"chco" => "333388,338833,883333",
				"chd" => "t:" . $puLinString . "|" . $puMacString . "|" . $puWinString,
				//"chdl" => "Linux|MacOS|Windows",
				"chdlp" => "r",
				"chm" => "B,ff000010,3,0,0",
				//"chtt" => $bundleTitle . " Hourly Purchase Totals",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,0,0,0,8",
				"chds" => "a",
				"chls" => "1|1|1")))));

	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_purchase_totals.png", fopen($url, 'r', false, $context), 0, $context);
	$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x,y",
				"chbh" => "2,0,0",
				"chs" => "900x300",
				"cht" => "lc",
				"chco" => "333388,338833,883333",
				"chd" => "t:" . $puLinString . "|" . $puMacString . "|" . $puWinString,
				"chdl" => "Linux|MacOS|Windows",
				"chdlp" => "r",
				"chm" => "B,ff000010,3,0,0",
				"chtt" => $bundleTitle . " Hourly Purchase Totals",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767|1N*s*",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,4,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_purchase_totals_big.png", fopen($url, 'r', false, $context), 0, $context);

		echo "Outputting revenue totals\n\n";
		$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			//"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x",
				"chbh" => "1,0,0",
				"chs" => "340x100",
				"cht" => "lc",
				"chco" => "333388,338833,883333",
				"chd" => "t:" . $rvLinString . "|" . $rvMacString . "|" . $rvWinString,
				//"chdl" => "Linux|MacOS|Windows",
				"chdlp" => "r",
				"chm" => "B,ff000010,3,0,0",
				//"chtt" => $bundleTitle . " Hourly Revenue Totals",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,0,0,0,8",
				"chds" => "a",
				"chls" => "1|1|1")))));

	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_revenue_totals.png", fopen($url, 'r', false, $context), 0, $context);
	$context = stream_context_create(
	    array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query(array(
	      			"chg" => "0,10",
				"chf" => "bg,s,f8f8f8ff",
				"chxt" => "x,y",
				"chbh" => "2,0,0",
				"chs" => "900x300",
				"cht" => "lc",
				"chco" => "333388,338833,883333",
				"chd" => "t:" . $rvLinString . "|" . $rvMacString . "|" . $rvWinString,
				"chdl" => "Linux|MacOS|Windows",
				"chdlp" => "r",
				"chm" => "B,ff000010,3,0,0",
				"chtt" => $bundleTitle . " Hourly Revenue Totals",
				"chxs" => "0,f8f8f8,11.5,0,lt,676767|1N*cUSDzs*",
				"chxr" => "0,0," . sizeof($bundleData) . ",4",
				"chxtc" => "0,0,0,4,0,0,8",
				"chds" => "a")))));
	      file_put_contents("./" . urlencode(getShortTitle($bundleTitle)) . "_revenue_totals_big.png", fopen($url, 'r', false, $context), 0, $context);


	echo "\nrvLin:\n" . $rvLinString . "\n";
	echo "\nrvMac:\n" . $rvMacString . "\n";
	echo "\nrvWin:\n" . $rvWinString . "\n";
	$f = fopen($url, 'r', false, $context);
	fpassthru($f);
	fclose($f);

	}	      
?>
