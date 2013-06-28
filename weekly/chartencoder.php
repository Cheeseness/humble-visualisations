<?

// Same as simple encoding, but for extended encoding.
$EXTENDED_MAP= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
$EXTENDED_MAP_LENGTH = strlen($EXTENDED_MAP);

function extendedEncode($arrVals, $maxVal) 
{
//  $chartData = 'e:';
  $chartData = '';

foreach ($arrVals as $numericVal)
{
    // In case the array vals were translated to strings.
    // Scale the value to maxVal.

    	$scaledVal = floor($EXTENDED_MAP_LENGTH * $EXTENDED_MAP_LENGTH * $numericVal / $maxVal);

    if($scaledVal > ($EXTENDED_MAP_LENGTH * $EXTENDED_MAP_LENGTH) - 1) {
      $chartData .= "..";
    } else if ($scaledVal < 0) {
      $chartData .= '__';
    } else {
      // Calculate first and second digits and add them to the output.
      $quotient = floor($scaledVal / $EXTENDED_MAP_LENGTH);
      $remainder = $scaledVal - $EXTENDED_MAP_LENGTH * $quotient;
      $chartData .= $EXTENDED_MAP{$quotient} . $EXTENDED_MAP{$remainder};
    }
  }

  return $chartData;
}

?>
