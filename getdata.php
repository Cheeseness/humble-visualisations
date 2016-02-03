<?php
	if (!headers_sent())
	{
		header("Access-Control-Allow-Origin: *");
		header('Content-type: application/json');
	}

	include_once("dbcreds.php");
	include_once("process.php");

	$conn = null;
	connectDB();
	getData();
	
	function connectDB()
	{
		global $conn;
		try
		{
			$conn = new PDO( getPDODrv() . ":dbname=" . getDBName() . ";host=" . getDBHost(), getDBUser(), getDBPass());
			//echo "PDO connection object created";
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}

	function closeDB()
	{
		global $conn;
		$conn = null;
	}
	
	/**
	* This function exports a dump of the database to a compressed file.
	* TODO: Is this necessary if we're also providing json representations?
	*/
	function dumpMYSQLData()
	{
		passthru("mysqldump --opt --host=" . getDBHost() . " --user=" . getDBUser() . " --password=" . getDBPass() . " --databases " . getDBName() . " --add-drop-database --add-drop-table | gzip > downloads/data.sql.gz");
	}

	function getAggregate($title = "Aggregate Results", $request = array(), $conditions = array())
	{
		$params = array();
		if (in_array("pcLin", $request["params"])) { $params[] = "sum(pyLin)/sum(pyTotal) as pcLin"; }
		if (in_array("pcMac", $request["params"])) { $params[] = "sum(pyMac)/sum(pyTotal) as pcMac"; }
		if (in_array("pcWin", $request["params"])) { $params[] = "sum(pyWin)/sum(pyTotal) as pcWin"; }

		if (in_array("avLin", $request["params"])) { $params[] = "sum(pyLin)/sum(puLin) as avLin"; }
		if (in_array("avMac", $request["params"])) { $params[] = "sum(pyMac)/sum(puMac) as avMac"; }
		if (in_array("avWin", $request["params"])) { $params[] = "sum(pyWin)/sum(puWin) as avWin"; }
		if (in_array("avAll", $request["params"])) { $params[] = "sum(pyTotal)/sum(puTotal) as avAll"; }

		if (in_array("puLin", $request["params"])) { $params[] = "sum(puLin) as puLin"; }
		if (in_array("puMac", $request["params"])) { $params[] = "sum(puMac) as puMac"; }
		if (in_array("puWin", $request["params"])) { $params[] = "sum(puWin) as puWin"; }
		if (in_array("puTotal", $request["params"])) { $params[] = "sum(puTotal) as puTotal"; }

		if (in_array("pyLin", $request["params"])) { $params[] = "sum(pyLin) as pyLin"; }
		if (in_array("pyMac", $request["params"])) { $params[] = "sum(pyMac) as pyMac"; }
		if (in_array("pyWin", $request["params"])) { $params[] = "sum(pyWin) as pyWin"; }
		if (in_array("pyTotal", $request["params"])) { $params[] = "sum(pyTotal) as pyTotal"; }

		if (in_array("fullPriceFirst", $request["params"])) { $params[] = "sum(fullPriceFirst) as fullPriceFirst"; }
		if (in_array("fullPriceLast", $request["params"])) { $params[] = "sum(fullPriceLast) as fullPriceLast"; }

		if (in_array("firstSeen", $request["params"])) { $params[] = "min(firstSeen) as firstSeen"; }
		if (in_array("lastSeen", $request["params"])) { $params[] = "max(lastSeen) as lastSeen"; }
		if (in_array("isOver", $request["params"])) { $params[] = "0 as isOver"; } //FIXME: Should this be the current status of the most recent bundle?
		if (in_array("lastUpdated", $request["params"])) { $params[] = "max(lastUpdated) as lastUpdated"; }
		if (in_array("bundleTitle", $request["params"])) { $params[] = "concat('" . $title . " (', count(id), ')') as bundleTitle"; }		
	
		$query = "select " . implode($params, ",") . " from newdata ";

		if (count($conditions) > 0)
		{
			$query .= " where " . $conditions;
		}
		return $query;
	}

	function getData($queryString = null, $showAggregate = true)
	{
		global $conn;

		if ($queryString == null)
		{
			//Check for get/post values
			//If there are none, just pull the aggregate values and the latest bundle
		}
		$request = array(
		
			"params"=> array('pcLin', 'pcMac', 'pcWin',
			 'avLin', 'avMac', 'avWin', 'avAll', 
			 'pyLin', 'pyMac', 'pyWin', 'pyTotal',
			 'puLin', 'puMac', 'puWin', 'puTotal',
			 'fullPriceFirst', 'fullPriceLast',
			 'firstSeen', 'lastSeen', 'isOver', 'lastUpdated',
			 'bundleTitle'),

		//Always going to be OR
		//Indie Bundles
		//Non-Indie Bundles
		//Android Bundles
		//Debut Bundles
		//Mojam Bundles
		//Game Bundles
		//Music Bundles
		//E-Book Bundles
		//Audiobook Bundles
			"bundleTypes"=> array("indie", "non-indie", "android", "mobile", "debut", "mojam",
			 "game", "music", "ebook", "comedy", "weekly", "audiobook"),


		//TODO: This (moving out to JS for the moment at Hannah's advice. Because she's awesome.
		//Could be AND or OR (or NOT?)
		//Bundles with param ><==! value (purcahses, payments, averages, percentages, full price, title count?)
			"conditions"=> array(	array("puLin", ">", "10000"),
						array("avWin", "<", "5.00"),
						array("isOver", "==", "1"))
					);
			 
		//If it's been requested, let's create a fictional "bundle" that repesents the combined values of the bundles pulled.
		if (isset($_GET['aggregate']))
		{
			$query = getAggregate("All Results Combined", $request);
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			echo "{";
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Indie Bundles Combined", $request, "bundleTitle like '%indie%' and bundleTitle not like '%bundle for%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Debut Bundles Combined", $request, "bundleTitle like '%debut%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			echo "";
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Android Bundles Combined", $request, "bundleTitle like '%android%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Mobile Bundles Combined", $request, "bundleTitle like '%mobile%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Mojam Bundles Combined", $request, "bundleTitle like '%mojam%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Comedy Bundles Combined", $request, "bundleTitle like '%comedy%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Audiobook Bundles Combined", $request, "bundleTitle like '%audiobook%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Non-Indie Bundles Combined", $request, "bundleTitle not like '%indie%' and bundleTitle not like '%music%' and bundleTitle not like '%ebook%' and bundleTitle not like '%comedy%' and bundleTitle not like '%audiobook%' and bundleTitle not like '%android%' and bundleTitle not like '%mojam%' and bundleTitle not like '%mobile%' and bundleTitle not like '%debut%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

/*
			$query = getAggregate("All Weekly Bundles Combined", $request, "bundleTitle like '%weekly%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";
*/
			$query = getAggregate("All Music Bundles Combined", $request, "bundleTitle like '%music%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";

			$query = getAggregate("All Audiobook Bundles Combined", $request, "bundleTitle like '%audiobook%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo ",";
			
			$query = getAggregate("All Ebook Bundles Combined", $request, "bundleTitle like '%ebook%'");
			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row)
			{
				echo "\"" . $row['bundleTitle'] . "\":";
				echo json_encode($row);
				echo "\n";
			}
			echo "}";
			
		}
		else
		{

			$params = array();
			if (in_array("pcLin", $request["params"])) { $params[] = "pcLin"; }
			if (in_array("pcMac", $request["params"])) { $params[] = "pcMac"; }
			if (in_array("pcWin", $request["params"])) { $params[] = "pcWin"; }

			if (in_array("avLin", $request["params"])) { $params[] = "avLin"; }
			if (in_array("avMac", $request["params"])) { $params[] = "avMac"; }
			if (in_array("avWin", $request["params"])) { $params[] = "avWin"; }
			if (in_array("avAll", $request["params"])) { $params[] = "avAll"; }

			if (in_array("puLin", $request["params"])) { $params[] = "puLin"; }
			if (in_array("puMac", $request["params"])) { $params[] = "puMac"; }
			if (in_array("puWin", $request["params"])) { $params[] = "puWin"; }
			if (in_array("puTotal", $request["params"])) { $params[] = "puTotal"; }

			if (in_array("pyLin", $request["params"])) { $params[] = "pyLin"; }
			if (in_array("pyMac", $request["params"])) { $params[] = "pyMac"; }
			if (in_array("pyWin", $request["params"])) { $params[] = "pyWin"; }
			if (in_array("pyTotal", $request["params"])) { $params[] = "pyTotal"; }

			if (in_array("fullPriceFirst", $request["params"])) { $params[] = "fullPriceFirst"; }
			if (in_array("fullPriceLast", $request["params"])) { $params[] = "fullPriceLast"; }

			if (in_array("firstSeen", $request["params"])) { $params[] = "firstSeen"; }
			if (in_array("lastSeen", $request["params"])) { $params[] = "lastSeen"; }
			if (in_array("isOver", $request["params"])) { $params[] = "isOver"; }
			if (in_array("lastUpdated", $request["params"])) { $params[] = "lastUpdated"; }
			if (in_array("bundleTitle", $request["params"])) { $params[] = "bundleTitle"; }		



			//TODO: Support explicit bundle naming?
			$bundleTypes = array();
		
			//needs to be not music, not ebook
			if (in_array("game", $request["bundleTypes"])) { $bundleTypes[] = ""; }

			//needs to be not indie, not music, and not ebook, not jam, not android (and not debut?)
			if (in_array("non-indie", $request["bundleTypes"])) { $bundleTypes[] = ""; }

			// but not  android?
			if (in_array("indie", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%indie%' "; }
			if (in_array("android", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%android%' "; }
			if (in_array("mobile", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%mobile%' "; }
			if (in_array("debut", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%debut%' "; }
			if (in_array("jam", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%mojam%' "; }

			if (in_array("music", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%music%' "; }
			if (in_array("ebook", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%ebook%' "; }
			if (in_array("audiobook", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%audiobook%' "; }
			if (in_array("comedy", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%comedy%' "; }
			if (in_array("weekly", $request["bundleTypes"])) { $bundleTypes[] = "bundleTitle like '%weekly%' "; }
		
			$limit = -1;
		
			$query = "select " . implode($params, ",") . " from newdata ";

/*			if (count($conditions) > 0)
			{
				$query .= " where " . implode($conditions, ",");
			}
*/
			$query .= " order by firstSeen";
			
			if ($limit > 0)
			{
				$query .= " limit :limit"; 
			}

			$stmt = $conn->query($query);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			echo "{";
			for ($i = 0; $i < count($result); $i ++)
			{
				echo "\"" . $result[$i]['bundleTitle'] . "\":";
				$result[$i]['bundleTitle'] = htmlspecialchars_decode($result[$i]['bundleTitle'], ENT_QUOTES);
				echo json_encode($result[$i]);
				if ($i < (count($result) - 1))
				{
					echo ",";
				}
					
				echo "\n";
			}
			echo "}";
		}
		
	}
	
	function pullData()
	{
		global $conn;
	}


?>
