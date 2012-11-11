<?php
  /// copyleft 2011 meklu (public domain)
  // This tries to check whether a URL should be accessed
  // or not.
  // Just pass the url (and user agent, if you'd like) to the function.
  // If an argument is NULL, its default value will be used.
  function isUrlBotSafe($url, $your_useragent = "meklu::isUrlBotSafe", $debug = FALSE) {
    if($your_useragent === NULL) $your_useragent = "meklu::isUrlBotSafe";
    if($debug === NULL) $debug = FALSE;

    if($debug === TRUE) {
      error_reporting(E_ALL);
      ini_set('display_errors', true);
      ini_set('html_errors', false);
      echo "PHP version: " . phpversion() . "\n";
    }
    $original_ua=ini_get("user_agent");
    ini_set("user_agent", $your_useragent);
    $tmp=parse_url($url);
    $baseurl=$tmp["scheme"] . "://";
    if(isset($tmp["user"])) {
      $baseurl=$baseurl . $tmp["user"] . ":";
      if(isset($tmp["pass"])) {
	$basurl=$baseurl . $tmp["pass"];
      }
      $baseurl=$baseurl . "@";
    }
    $baseurl=$baseurl . $tmp["host"];
    if(isset($tmp["port"])) {
      $baseurl=$baseurl . ":" . $tmp["port"];
    }
    $baseurl=$baseurl . "/";
    if(isset($tmp["path"])) {
      $checkedpath=$tmp["path"];
    } else {
      $checkedpath="/";
    }
    if(isset($tmp["query"])) {
      $checkedpath=$checkedpath . "?" . $tmp["query"];
    }
    if(strlen($checkedpath) > 1) {
      if(substr_count($checkedpath, '?') == 0) {
	if(preg_match("#\w$#", $checkedpath))
	  $checkedpath=preg_replace("#$#", "/", $checkedpath);
      } elseif(substr_count($checkedpath, "/?") > 0) {
	$checkedpath=str_replace("/?", "/index.stuff?", $checkedpath);
      }
    }
    unset($tmp);
    // checking whether robots.txt can be accessed or not.
    // yes, this is costly. trying to do the var assignment directly didn't
    // work for some reason. I'm trying to lessen it by only reading the first
    // byte
    if(@file_get_contents($baseurl . "robots.txt", NULL, NULL, 0, 1) === FALSE) {
      ini_set("user_agent", $original_ua);
      return TRUE;
    } else {
      // we were able to download something!
      $raw=file_get_contents($baseurl . "robots.txt");
      // check if we ran into an html (error) page.
      // we're looking for the closing tag because <html foo="bar">
      if(preg_match("#</html>#i", $raw) > 0) {
	ini_set("user_agent", $original_ua);
	return TRUE;
      }
      // so far so good!
      // fixing some newlines
      // i.e. making all carriage returns newlines and removing duplicates
      //      and trimming the result
      $raw=str_replace("\r", "\n", $raw);
      $raw=preg_replace("#\n+#", "\n", $raw);
      $raw=trim($raw);
    }
    $lines=explode("\n", $raw);
    $rules=array("*" => array(
			  "/" => TRUE
			)
		);
    $current_agent=NULL;
    foreach($lines as &$line) {
      $rule=explode(":", $line, 2);
      $key=trim($rule[0]);
      $value=trim($rule[1]);
      if(strcasecmp($key, "user-agent") == 0) {
	$current_agent=$value;
	unset($rule, $key, $value);
	continue;
      }
      if(strcasecmp($key, "allow") == 0 && $current_agent !== NULL) {
      	if(strlen($value) > 1) {
	  if(substr_count($value, '?') == 0) {
	    if(preg_match("#\w$#", $value))
	      $value=preg_replace("#$#", "/", $value);
	  } else {
	    if(substr_count($value, "/?") > 0) {
	      $value=str_replace("/?", "/index\\.\w+?", $value);
	    }
	    $value=str_replace("?", "\\?", $value);
	  }
	}
	$rules[$current_agent][$value]=TRUE;
	unset($rule, $key, $value);
	continue;
      }
      if(strcasecmp($key, "disallow") == 0 && $current_agent !== NULL) {
      	if(strlen($value) > 1) {
	  if(substr_count($value, '?') == 0) {
	    if(preg_match("#\w$#", $value))
	      $value=preg_replace("#$#", "/", $value);
	  } else {
	    if(substr_count($value, "/?") > 0) {
	      $value=str_replace("/?", "/index\\.\w+?", $value);
	    }
	    $value=str_replace("?", "\\?", $value);
	  }
	}
	$rules[$current_agent][$value]=FALSE;
	unset($rule, $key, $value);
	continue;
      }
    }
    unset($line);
    unset($current_agent);
    $state=TRUE;
    if(isset($rules["*"])) {
      if(isset($rules["*"]["/"]))
	$state=$rules["*"]["/"];
      reset($rules["*"]);
      foreach($rules["*"] as $key => $value) {
	if(preg_match("#^" . $key . "#", $checkedpath) > 0) {
	  $state=$value;
	}
      }
    }
    if(isset($rules[$your_useragent])) {
      if(isset($rules[$your_useragent]["/"]))
	$state=$rules[$your_useragent]["/"];
      reset($rules[$your_useragent]);
      foreach($rules[$your_useragent] as $key => $value) {
	if(preg_match("#^" . $key . "#", $checkedpath) > 0) {
	  $state=$value;
	}
      }
    }
    if($debug === TRUE) {
      echo "Var dumps...\n";
      echo "\$checkedpath:\n";
      var_dump($checkedpath);
      echo "\$baseurl:\n";
      var_dump($baseurl);
      echo "\$raw:\n";
      var_dump($raw);
      echo "\$rules:\n";
      var_dump($rules);
    }
    ini_set("user_agent", $original_ua);
    return $state;
  }
?>
