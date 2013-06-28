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
		return str_replace(array("The Humble Bundle for ", "The Humble Bundle with ", "Humble Bundle with ", "The Humble Bundle ", "The Humble ", "Humble ", " Bundle", " Debut"),"", $title);
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




?>
