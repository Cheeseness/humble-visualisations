<?php
	include_once("dbcreds.php");
	include_once("process.php");

	$debug = false;
	if (isset($_GET['debug']))
	{
		$debug = true;
	}
	$conn = null;
	connectDB();
	
	$success = pullData("http://www.humblebundle.com/");
	echo "Result: " . $success;
	
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
	
	function pullData($url)
	{
		global $conn;
		global $debug;
		
		$bundleTitle = "Unknown";
		$pyTotal = null;
		$puTotal = null;
		$pcLin = null;
		$pcMac = null;
		$pcWin = null;
		$avAll = null;
		$avLin = null;
		$avMac = null;
		$avWin = null;
		$fullPriceLast = null;
		$pyLin = null;
		$pyMac = null;
		$pyWin = null;
		$puLin = null;
		$puMac = null;
		$puWin = null;
		
		//Tell the server who we are - if you use or edit this for your own purposes, please change the user agent string to something appropriate for what you're doing <3
		ini_set("user_agent", "HumbleStatsParser2 (http://cheesetalks.twolofbees.com/)");

		
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
		
		//We're going to use DOMDocument to pull some stuff out out
		$dom = new DOMDocument;
		//We use the @ to suppress all the warnings caused by improperly escaped & chars and some questionable tag closures. Unfortunately, this means we don't get 
		@$dom->loadHTML($page);
		
		//If that doesn't work, there's no point in continuing either.
		if ($dom === false)
		{
			return false;
		}
		
		//Some of the stuff we're interested in is only accessible via class name. Yay.
		$pathfinder = new DomXPath($dom); //because who doesn't like things called Pathfinder?
		
		
		//The bundle title can be a few different variations of things depending upon the status of the bundle, plus it has a whole stack of unnecessary whitespace
		$bundleTitle = str_replace(array("\n", "\t"), '', strip_tags(getValue('hibtext', $dom)));
		$bundleTitle = trim(preg_replace('/\s\s+/', ' ', $bundleTitle)); //they don't make this easy, do they?
		$bundleTitle = str_replace("Thanks for purchasing the ", "The ", $bundleTitle); //this allows us to parse saved download pages (we can't pull them live since robots.txt doesn't allow it
		$bundleTitle = str_replace("!", "", $bundleTitle);

		if ($bundleTitle == "")
		{
			//And now we have weekly stuff D:
			$headings = $dom->getElementsByTagName("h2");
			
			
			
			
			//Let's check and see if the bundle is finished
			$isOver = false;
			foreach ($headings as $h)
			{
				if (stripos($h->nodeValue, "weekly") !== false)
				{
					$bundleTitle = trim($h->nodeValue);
					if ($debug)
					{
						echo "Found: " . $bundleTitle . "\n";
					}
					break; //Yay, now we have multiple h2 elements with the bundle title in it
				}
				else if (stripos($h->nodeValue, "the humble") !== false)
				{
					$bundleTitle = trim($h->nodeValue);
					if ($debug)
					{
						echo "Found: " . $bundleTitle . "\n";
					}
					break; //Yay, now we have multiple h2 elements with the bundle title in it
				}
				else if ((stripos($h->nodeValue, "humble") !== false) && (stripos($h->nodeValue, "bundle") !== false))
				{
					$bundleTitle = trim($h->nodeValue);
					if ($debug)
					{
						echo "Found: " . $bundleTitle . "\n";
					}
					break; //Yay, now we have multiple h2 elements with the bundle title in it
				}
				
				
				if (stripos($h->nodeValue, " is now") !== false)
				{
					$bundleTitle = str_replace(" is now closed", "", $bundleTitle); //This stops us from making a new entry for closed bundles :D
					$bundleTitle = str_replace(" is now over", "", $bundleTitle); //This wording for expired bundles was added with the Botanicula Debut
					$isOver = true;
					if ($debug)
					{
						echo "Is over: " . $isOver . "\n";
					}
					break; //Yay, now we have multiple h2 elements with the bundle title in it
				}
			}
		}
		
		//New page markup is getting harder to pull the bundle title from, so let's try getting it from the alt attribute of the logo when all else fails
		if ($bundleTitle == "")
		{
			$nodes = $pathfinder->query("//*[contains(concat(' ', normalize-space( @class ), ' '), ' first-section-heading ' )]");
			foreach ($nodes as $node)
			{
				$h = $node->getElementsByTagName("h2");
				foreach ($h as $i)
				{
					$i = $i->nodeValue;
					if ((stripos($i, "humble") !== false) && (stripos($i, "bundle") !== false))
					{
						$bundleTitle = $i;
						if ($debug)
						{
							echo "Found: " . $bundleTitle . "\n";
						}
					}
				}
			}
		}

		//The "full price" value is tricky to grab as well
		$fullPriceLast = getValue('pwyw', $dom);
		
		//Now with even more trickiness
		
		$othernodes = $pathfinder->query("//*[contains(concat(' ', normalize-space( @class ), ' '), ' pwyw ' )]");
		foreach ($othernodes as $node)
		{
			$fullPriceLast = $node->nodeValue;

			if ($debug)
			{
				echo "Full price: " . $fullPriceLast . "\n";
			}
		}
		
		$yetmorenodes = $pathfinder->query("//*[contains(concat(' ', normalize-space( @class ), ' '), ' how-is-bundle-formed ' )]"); //We should probably use the same variable for these, but I get a kick out of giving them silly names
		foreach ($yetmorenodes as $node)
		{
			$pgraphs = $node->getElementsByTagName("p");
			
			foreach ($pgraphs as $i)
			{
				if ((stripos($i->nodeValue, "$") !== false) && (stripos($i->nodeValue, "cost") !== false))
				{
					$fullPriceLast = $i->nodeValue;
					if ($debug)
					{
						echo "Full price text: " . $fullPriceLast . "\n";
					}
					break;
				}
			}
			
			if ($fullPriceLast == "")
			{
				$pgraphs = $node->getElementsByTagName("aside");
			
				foreach ($pgraphs as $i)
				{
					if ((stripos($i->nodeValue, "$") !== false) && (stripos($i->nodeValue, "cost") !== false))
					{
						$fullPriceLast = $i->nodeValue;
						if ($debug)
						{
							echo "Full price text: " . $fullPriceLast . "\n";
						}
						break;
					}
				}
			}
			
		}
		
		
		//We don't need the HTML tags (in fact, they're just going to get in the way
	        $fullPriceLast = strip_tags($fullPriceLast);
	        //Shorten the string to everything from $ symbol
		$fullPriceLast = substr($fullPriceLast, strpos($fullPriceLast, "$"));
		//And now let's drop everything from (including) the first space, as well as the $ symbol
		$fullPriceLast = substr($fullPriceLast, 1, (- (strlen($fullPriceLast) - strpos($fullPriceLast, " "))) - 1);
		//And last but not least, let's kill any pesky trailing (or otherwise) commas
		$fullPriceLast = str_ireplace(",", "", $fullPriceLast);



		if ($debug)
		{
			echo "\nFull price: " . $fullPriceLast . "\n\n";
		}
		
		$pattern = "(^.*initial_stats_data\':.*$)m";
		if (preg_match($pattern, $page, $result))
		{
			$result = trim($result[0]);
			$result = substr($result, strpos($result, "{"), strlen($result));
			$result = substr($result, 0, strlen($result)-1);
			$result = json_decode($result, true);
			
			if ($debug)
			{
				print_r($result);
			}
			
			$pyTotal = array_sum($result["rawplatformtotals"]);
			$puTotal = $result["numberofcontributions"]["total"];
			$avAll = $pyTotal / $puTotal;

			$pyLin = $result["rawplatformtotals"]["linux"];
			$pyMac = $result["rawplatformtotals"]["mac"];
			$pyWin = $result["rawplatformtotals"]["windows"];

			$puLin = $result["numberofcontributions"]["linux"];
			$puMac = $result["numberofcontributions"]["mac"];
			$puWin = $result["numberofcontributions"]["windows"];

			$avLin = $result["rawplatformtotals"]["linux"] / $result["numberofcontributions"]["linux"];
			$avMac = $result["rawplatformtotals"]["mac"] / $result["numberofcontributions"]["mac"];
			$avWin = $result["rawplatformtotals"]["windows"] / $result["numberofcontributions"]["windows"];

			$pcLin = $result["rawplatformtotals"]["linux"] / $pyTotal;
			$pcMac = $result["rawplatformtotals"]["mac"] / $pyTotal;
			$pcWin = $result["rawplatformtotals"]["windows"] / $pyTotal;

			//TODO: Sort this out. The numbers that Humble give for each platform don't add up to the amount shown in rawtotal (which is shown on the humblebundle.com site).
			$paymentTotal = $result["rawtotal"];
		}
		
		//Is there an existing record with this title?
		$query = "select id from newdata where bundleTitle = '" . $bundleTitle . "' limit 1";
		$stmt = $conn->query($query);
		$existingRecord = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($debug)
		{
			print ("Existing record for " . $bundleTitle . ":\n");
			print_r($existingRecord);
		}
		if(count($existingRecord) > 0)
		{
			//Update the existing record
			//$query = "update newdata set lastUpdated = utc_timestamp(), pyTotal = '" . $pyTotal . "', puTotal = '" . $puTotal . "', pcLin = '" . $pcLin . "', pcMac = '" . $pcMac . "', pcWin = '" . $pcWin . "', avAll = '" . $avAll . "', avLin = '" . $avLin . "', avMac = '" . $avMac . "', avWin = '" . $avWin . "', fullPriceLast = '" . $fullPriceLast . "', pyLin = '" . $pyLin . "', pyMac = '" . $pyMac . "', pyWin = '" . $pyWin . "', puLin = '" . $puLin . "', puMac = '" . $puMac . "', puWin = '" . $puWin . "'";
			$query = "update newdata set lastUpdated = utc_timestamp(), pyTotal = :pyTotal, puTotal = :puTotal, pcLin = :pcLin, pcMac = :pcMac, pcWin = :pcWin, avAll = :avAll, avLin = :avLin, avMac = :avMac, avWin = :avWin, fullPriceLast = :fullPriceLast, pyLin = :pyLin, pyMac = :pyMac, pyWin = :pyWin, puLin = :puLin, puMac = :puMac, puWin = :puWin";
			if (!$isOver)
			{
				$query .= ", lastSeen = utc_timestamp()";
			}
			else
			{
				$query .= ", isOver = 1";
			}
			$query .= " where id = :id";



			if ($debug)
			{
				echo $query;
				return;
			}
			else
			{
				$stmt = $conn->prepare($query);
				$success = $stmt->execute(array('pyTotal' => $pyTotal, 'puTotal' => $puTotal, 'pcLin' => $pcLin, 'pcMac' => $pcMac, 'pcWin' => $pcWin, 'avAll' => $avAll, 'avLin' => $avLin, 'avMac' => $avMac, 'avWin' => $avWin, 'fullPriceLast' => $fullPriceLast, 'pyLin' => $pyLin, 'pyMac' => $pyMac, 'pyWin' => $pyWin, 'puLin' => $puLin, 'puMac' => $puMac, 'puWin' => $puWin, 'id' => $existingRecord[0]['id']));

				//Do an updated dump of the database and return the number of rows that the query updated (it's not really that relevant, but it's nice for debugging)
				dumpMySQLData();
				return $success;
			}
		}
		else
		{
			//Insert our discovered data
			//$query = "insert into newdata (bundleTitle, pyTotal, puTotal, pcLin, pcMac, pcWin, avAll, avLin, avMac, avWin, firstSeen, fullPriceFirst, fullPriceLast, lastSeen, pyLin, pyMac, pyWin, puLin, puMac, puWin) values ('" . $bundleTitle . "', '" . $pyTotal . "', '" . $puTotal . "', '" . $pcLin . "', '" . $pcMac . "', '" . $pcWin . "', '" . $avAll . "', '" . $avLin . "', '" . $avMac . "', '" . $avWin . "', utc_timestamp() , '" . $fullPriceLast . "', '" . $fullPriceLast . "', utc_timestamp() , '" . $pyLin . "', '" . $pyMac . "', '" . $pyWin . "', '" . $puLin . "', '" . $puMac . "', '" . $puWin . "')";
			$query = "insert into newdata (bundleTitle, pyTotal, puTotal, pcLin, pcMac, pcWin, avAll, avLin, avMac, avWin, firstSeen, fullPriceFirst, fullPriceLast, lastSeen, pyLin, pyMac, pyWin, puLin, puMac, puWin) values ( :bundleTitle, :pyTotal, :puTotal, :pcLin, :pcMac, :pcWin, :avAll, :avLin, :avMac, :avWin, utc_timestamp(), :fullPriceLast, :fullPriceLast, utc_timestamp(), :pyLin, :pyMac, :pyWin, :puLin, :puMac, :puWin)";

			if ($debug)
			{
				echo $query;
				return;
			}
			else
			{
				$stmt = $conn->prepare($query);
				$success = $stmt->execute(array('bundleTitle' => $bundleTitle, 'pyTotal' => $pyTotal, 'puTotal' => $puTotal, 'pcLin' => $pcLin, 'pcMac' => $pcMac, 'pcWin' => $pcWin, 'avAll' => $avAll, 'avLin' => $avLin, 'avMac' => $avMac, 'avWin' => $avWin, 'fullPriceFirst' => $fullPriceLast, 'fullPriceLast' => $fullPriceLast, 'pyLin' => $pyLin, 'pyMac' => $pyMac, 'pyWin' => $pyWin, 'puLin' => $puLin, 'puMac' => $puMac, 'puWin' => $puWin));
				//Do an updated dump of the database and return the number of rows that the query updated (it's not really that relevant, but it's nice for debugging)
				dumpMySQLData();
				return $success;
			}
		}
	}


?>
