<?php

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
	* This function attempts to give us a quick and dirty short version of the
	* given bundle title.
	*/
	function getShortTitle($title)
	{
		return str_replace(array("Humble Weekly Sale: ", "The Humble Weekly Sale: ", "The Humble Bundle for ", "The Humble Bundle with ", "Humble Bundle with ", "The Humble Bundle ", "The Humble ", "Humble ", " Bundle", " Debut"),"", $title);
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



?>
