<?php

	function ss_DecimalToFraction( $decimal, $resolution )
	{
		$resolution = abs( $resolution );
		$current_divisor = 2;
		$rint = (int) $decimal;
		if( $rint == 0 )
			$rint = "";

		$rfrac = $decimal - $rint;
		$rnum = (int) ($rfrac * $current_divisor);
		$error = $rfrac - $rnum / $current_divisor;

		while( abs($error) > $resolution )
		{
			$rnum = (int) ($rfrac * ++$current_divisor);
			$error = $rfrac - $rnum / $current_divisor;
		}

		if( $rnum == 0 )
			return "$rint";
		else
			return "$rint $rnum/$current_divisor";
	}

	function ss_ShowCigarSize( $length, $width )
	{
		$linch = $length / 25.4;
		$gauge = (int) ($width * 64 / 25.4 );
		echo "<strong>Length:</strong> ".ss_DecimalToFraction( $linch, 0.1 )."\"  <strong>Ring Gauge:</strong> $gauge ({$length}mm x {$width}mm)";
	}

	function ss_query_to_html( $query_str )
	{
		$html = "<table>";

		$firstLine = true;
		
		$query = query( $query_str );
		while ($row = $query->fetchRow())
		{
			if( $firstLine )
			{
				$firstLine = false;
				$html .= "<tr>";
				foreach( $row as $index => $val )
					$html .= "<th>$index</th>";
				$html .= "</tr>";
			}

			$html .= "<tr>";

			foreach( $row as $index => $val )
				if( strstr( $index, "\$" ) )
					$html .= "<td>".number_format( $val, 2 )."</td>";
				else
					$html .= "<td>$val</td>";

			$html .= "</tr>";
		}
		$html .= "</table>";

		return $html;
	}

	function ss_fixKeywords($keywords) {
		// english suffixes
		$newKeywords = '';
		$suffixes = array('ing', 's', 'ies', 'en', 'er', 'ese', 'y');
		foreach (ListToArray($keywords,' ') as $word) {
			$suffixfound = false;
			$newWord = $word;
			
			foreach($suffixes as $suffix) {
				if (strlen($word)-strlen($suffix) > 3) {
					if ($suffixfound) {
						break;
					}
					$start = -1 * strlen($suffix);
					if(substr($word, $start) == $suffix) {
						$newWord = substr($word, 0, (strlen($word)-strlen($suffix)));
						$suffixfound = true;
					}
				}
			}
			ss_comma($newKeywords,' ');
			$newKeywords .= $newWord;
		}
		
		return $newKeywords;
	}
	// get "s=273,w=500,h=500"
	// return array('s'=>273, 'w'=>500, 'h'=>500);
	function ss_ListToKeyArray($inList, $inDelim = ",", $keyInDelim='=') {
		$outArray = array();
		foreach (ListToArray($inList,$inDelim) as $value) {
			
			$outArray[ListFirst($value, $keyInDelim)] = ListLast($value, $keyInDelim);
		}
		return $outArray;	
	}
	

	function ss_getInputStatus($value, $confirm, $status) {
		if ($value == $confirm) {
			return $status;
		}
		return '';
	}	
	function ss_URLEncodedFormat($var) {
		return rawurlencode($var);
	}
	
	function ss_HTMLEditFormat($var,$useNBSP = false) {
		if ($useNBSP) {
			return str_replace(' ','&nbsp;',htmlspecialchars($var,ENT_QUOTES, $GLOBALS['cfg']['Web_Charset']));
		} else {
			return htmlspecialchars($var,ENT_QUOTES, $GLOBALS['cfg']['Web_Charset']);
		}
	//	return htmlentities($var,ENT_QUOTES);
	}
	
	function ss_HTMLEditFormatWithBreaks($var,$useNBSP = false) {
		return str_replace("\n","<br />",ss_HTMLEditFormat($var,$useNBSP));
	}
	
	// From PHP Manual User contributed notes: dave at [nospam]netready dot biz
	function stri_replace($find, $replace, $string )
	{
		$parts = explode( strtolower($find), strtolower($string) );
		$pos = 0;
		foreach( $parts as $key=>$part ){
			$parts[ $key ] = substr($string, $pos, strlen($part));
			$pos += strlen($part) + strlen($find);
		}
		return( join( $replace, $parts ) );
	}
	
	
	function ss_JSStringFormat($var)
	{
		$var = str_replace("\\", "\\\\", $var); // \
		$var = str_replace("\"", "\\\"", $var); // "
		$var = str_replace("'", "\\'", $var); // '
		$var = str_replace("\t", "\\t", $var);  // \t
		$var = str_replace("\r", "\\r", $var);  // \r
		$var = str_replace("\n", "\\n", $var);  // \n
		return ($var);
	}
	
	function ss_SetIfSet(&$var,$value) {
		if (($value !== null) && strlen($value)) {
			$var = $value;	
		}
	}
	
	/*
		Window/Server etc
	*/
	function ss_EscapeAssetPath($path) {
		$path = str_replace('%20', '_', str_replace('%2E','.',str_replace('%2F','/',ss_URLEncodedFormat($path))));
		return $path;	
	}
	
	
	function expandPath($relativePath) {
		/* Take a relative path and make that an absolute path 
		 * emulation of CF function of the same name          */
		if (substr($relativePath, 0, 1) != '/') {
			$relativePath = '/' . $relativePath;
			return getcwd(). $relativePath;
		} else return $relativePath;
		//return dirname($_SERVER['PATH_TRANSLATED']) . $relativePath;
	} 
	
	function expandPath2($relativePath) {
		/* Take a relative path and make that an absolute path 
		 * emulation of CF function of the same name          */
		if (substr($relativePath, 0, 1) != '/') {
			$relativePath = '/' . $relativePath;
		}
		return getcwd(). $relativePath;
	} 
	
	
	
	function ss_absolutePathToURL($path,$full = false) {
		global $cfg;
		$remove = getcwd()."/";
		$path = stri_replace($remove,'',$path);
		$path = stri_replace('/www/htdocs/contentmanager/','',$path);
		return $path;
	}
	
	function ss_withoutPreceedingSlash($dir) {
		if (substr($dir,0,1) == '/') return substr($dir,1);
		return $dir;	
	}
	
	function ss_withPreceedingSlash($dir) {
		if (substr($dir,0,1) != '/') return '/'.$dir;
		return $dir;	
	}
	
	function ss_withTrailingSlash($dir) {
		if (substr($dir,-1) != '/') return $dir.'/';
		return $dir;
	}
	
	function ss_withoutTrailingSlash($dir) {
		if (substr($dir,-1) == '/') return substr($dir,1,-1);
		return $dir;
	}
	
	function ss_parseText($text,$assetID = null, $fullURL = false, $noCurrentLinks = false) {
		global $cfg;

		// hack in a few things

		while( $pos = strpos( $text, '<!--?=' ) )
		{
			$purpose = [];
			$purpose['statsUser'] = 'Raw statistic collection';
			$purpose['keepMeLoggedInCookie'] = 'Keeping user logged in';
			$purpose['tokenCheck'] = 'Spoofing Protection';
			$purpose['PHPSESSID'] = 'Shopping Cart';

			$lifespan = [];
			$lifespan['statsUser'] = '5 Years';
			$lifespan['keepMeLoggedInCookie'] = '5 Years';
			$lifespan['tokenCheck'] = '1 Day';
			$lifespan['PHPSESSID'] = '< 24 Minutes';

			extract( $cfg );
			$cookietext = "<table border=1><tr><th>COOKIE NAME</th><th>LIFE SPAN</th><th>PURPOSE</th></tr>";
			foreach( $_COOKIE  as $name=>$val )
			{
				$cookietext .= "<tr><td>$name</td><td>";
				if( array_key_exists( $name, $lifespan ) )
					$cookietext .= $lifespan[$name];
				else
					$cookietext .= 'Unknown';
				$cookietext .= "</td><td>";
				if( array_key_exists( $name, $purpose ) )
					$cookietext .= $purpose[$name];
				else
					$cookietext .= 'Unknown';
				$cookietext .= "</td></tr>";
			}
			$cookietext .= "</table>";
			$rest = substr( $text, $pos+6 );
			if( ( $pos2 = strpos( $rest, '?-->' ) ) < 50 )
			{
				$foo = substr( $rest, 0, $pos2 );
				ss_log_message( "Found variable \{$foo\}" );
				if( ( $content = ${$foo} ) == NULL )
					$content = '';
				ss_log_message( "variable evaluated is $content, inserting at $pos, ".($pos + $pos2 + 10) );
				$ntext = substr( $text, 0, $pos ).$content.substr( $text, $pos + $pos2 + 10 );
				$text = $ntext;
			}
		}

		
		// Handle "next" and "previous" asset links
		$previousAssetLink = "Javascript:alert('There is no previous page in this context')";
		$nextAssetLink = "Javascript:alert('There is no next page in this context')";
		if ($assetID !== null) {
			if (stristr($text,"Asset://Next") !== false or stristr($text,"Asset://Previous") !== false) {
				// First find out the parent of the asset
				$asset = getRow("
					SELECT * FROM assets
					WHERE as_id = {$assetID}
				");
				// Now find all the children of that parent
				$Q_SiblingAssets = query("
					SELECT * FROM assets
					WHERE as_parent_as_id = {$asset['as_parent_as_id']}
						AND (
							as_deleted IS NULL 
						OR as_deleted = 0)
					ORDER BY as_sort_order, as_name
				");
				$assetArray = $Q_SiblingAssets->columnValuesArray('as_id');
				for ($i=0;$i<count($assetArray);$i++) {
					if ($assetArray[$i] == $assetID) {
						// Figure out the previous asset
						if ($i != 0) {
							$previousAssetID = $assetArray[$i-1];
						} else {
							// Wrap around to the end if we're at the first one
							$previousAssetID = $assetArray[count($assetArray)-1];
						}
						
						// Figure out the next asset
						if ($i != count($assetArray)-1) {
							$nextAssetID = $assetArray[$i+1];
						} else {
							// Wrap around to the start if we're at the last one in the list
							$nextAssetID = $assetArray[0];
						}
					}	
				}
				//ss_log_message_r($assetArray);
				$result = new Request("Asset.PathFromID",array('as_id'=>$nextAssetID));
				$nextAssetLink = ss_EscapeAssetPath(ss_withoutPreceedingSlash($result->value));
				$result = new Request("Asset.PathFromID",array('as_id'=>$previousAssetID));
				$previousAssetLink = ss_EscapeAssetPath(ss_withoutPreceedingSlash($result->value));

			}
		}

		// Asset://Current
		$currentAssetLink = '';
		if ($noCurrentLinks === false) {
			$currentAssetLink = $_SERVER['REQUEST_URI'];
		} else if ($noCurrentLinks !== true) {
			// anything not true/false is inserted verbatim
			$currentAssetLink = $noCurrentLinks;
		}
		
		// Fix the links
		$text = stri_replace("Asset://Next/",$nextAssetLink,$text);
		$text = stri_replace("Asset://Next",$nextAssetLink,$text);
		$text = stri_replace("Asset://Previous/",$previousAssetLink,$text);
		$text = stri_replace("Asset://Previous",$previousAssetLink,$text);
		$text = stri_replace("Asset://Current/",$currentAssetLink,$text);
		$text = stri_replace("Asset://Current",$currentAssetLink,$text);
				
		
		// Look for assets to embed
		$GLOBALS['forceBuffer'] = true;
		while (preg_match('/<IMG[^>]* ALT="EMB: ([0-9]+) ([0-9]+)x([0-9]+)"[^>]*>/i',$text,$result)) {
			$assetContent = new Request('Asset.Embed',array('as_id' => $result[1],'Size' => $result[2].'x'.$result[3]));
			$text = stri_replace($result[0],$assetContent->display,$text);
		}
		$GLOBALS['forceBuffer'] = false;
		$GLOBALS['forceBuffer'] = true;
		while (preg_match('/<IMG[^>]* ALT="EMB: ([0-9]+)[^>]*"[^>]*>/i',$text,$result)) {			
			$assetContent = new Request('Asset.Embed',array('as_id' => $result[1]));
			$text = stri_replace($result[0],$assetContent->display,$text);
		}
		$GLOBALS['forceBuffer'] = false;
		
		// Look for anchor image from graphical editor e.g) <img alt="testanc" src="System/Libraries/Field/htmlarea/images/ed_anchor.gif" />
		
		//<img src="http://jadestadium.im.co.nz/System/Libraries/Field/htmlarea/images/ed_anchor.gif" alt="ScenicCircleLounge" />		  
		while (preg_match('/<IMG[^>]* SRC="[^>]*htmlarea\/images\/ed_anchor.gif" ALT="([^>]*)"[^>]*>/i',$text,$result)) {
			$anchorName = $result[1];
			$text = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$text);
		}
		//<img id="SPC" alt="SPC" src="http://steel-detailing.com/System/Libraries/Field/htmlarea/images/ed_anchor.gif" />
		while (preg_match('/<IMG[^>]* ALT="([^>]*)"[^>]*SRC="[^>]*htmlarea\/images\/ed_anchor.gif"[^>]*>/i',$text,$result)) {
			$anchorName = $result[1];
			$text = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$text);
		}		
		//<img src="http://steel-detailing.com/System/Libraries/Field/htmlarea/images/ed_anchor.gif" alt="SPC" id="SPC" />
		while (preg_match('/<IMG[^>]* SRC="System\/Libraries\/Field\/htmlarea\/images\/ed_anchor.gif" ALT="([^>]*)"[^>]*>/i',$text,$result)) {
			$anchorName = $result[1];
			$text = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$text);
		}
		// Look for anchor image from graphical editor e.g) <img alt="testanc" src="System/Libraries/Field/htmlarea/images/ed_anchor.gif" />
		while (preg_match('/<IMG[^>]* ALT="([^>]*)"[^>]* SRC="System\/Libraries\/Field\/htmlarea\/images\/ed_anchor.gif" [^>]*>/i',$text,$result)) {
			$anchorName = $result[1];
			$text = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$text);
		}
		

/*
		if (ss_optionExists('Email Spam Protection')) {
			// Fix up links to email addresses <a href="mailto:blah@blah.com">blah@blah.com</a>
			while (eregi('<A[^>]*mailto:([^@]+)@([^\'"]+)[^>]*>([^@<]+)@([^<]+)</a>',$text,$result)) {
				//ss_log_message_r($result);
				// $result[0] = <a href="mailto:someone@somewhere.com">someoneelse@somewhereelse.com</a>
				// $result[1] = someone
				// $result[2] = somewhere.com
				// $result[3] = someoneelse
				// $result[4] = somewhereelse.com
				$user = ss_JSStringFormat($result[1]);
				$domain = ss_JSStringFormat(str_replace('.','~',$result[2]));
				$user2 = ss_JSStringFormat($result[3]);
				$domain2 = ss_JSStringFormat(str_replace('.','~',$result[4]));
				$text = stri_replace($result[0],"<script type=\"text/javascript\" language=\"Javascript\">ed='$domain';var re = new RegExp ('~');while(ed.search(re) != -1) {ed = ed.replace(re,'.');}eu='$user';ed2='$domain2';while(ed2.search(re) != -1) {ed2 = ed2.replace(re,'.');}eu2='$user2';document.write('<a href=\"'+'mai'+'lto:'+eu+'@'+ed+'\">'+eu2+'@'+ed2+'</a>');</script>",$text);
			}
			
			// Fix up links to email addresses <a href="mailto:blah@blah.com">
			while (eregi('<A[^>]*mailto:([^@]+)@([^\'"]+)[^>]*>',$text,$result)) {
				//ss_log_message_r($result);
				// $result[0] = <a href="mailto:someone@somewhere.com">
				// $result[1] = someone
				// $result[2] = somewhere.com
				$user = ss_JSStringFormat($result[1]);
				$domain = ss_JSStringFormat(str_replace('.','~',$result[2]));
				$text = stri_replace($result[0],"<script type=\"text/javascript\" language=\"Javascript\">ed='$domain';eu='$user';var re = new RegExp ('~');while(ed.search(re) != -1) {ed = ed.replace(re,'.');}document.write('<a href=\"'+'mai'+'lto:'+eu+'@'+ed+'\">');</script>",$text);
			}
		}
*/		
		// Fix up links to assets
		while (preg_match('/<A[^>]*((Asset:\/\/([0-9]+))[\\/]*)["\'#][^>]*>/i',$text,$result)) {
		//	ss_log_message_r($result);
			// $result[1] = Asset://501/
			// $result[2] = Asset://501
			// $result[3] = 501
			$assetPath = new Request('Asset.PathFromID',array(
				'as_id' => $result[3]
			));
			$text = stri_replace($result[1],str_replace(" ","%20",$GLOBALS['cfg']['currentSite'].$assetPath->value),$text);
		}
		if ($fullURL) {
			$filePath = $GLOBALS['cfg']['currentServer'];
			$text = stri_replace("SRC=\"Custom/","SRC=\"".$filePath."Custom/",$text);
			$text = stri_replace("SRC='Custom/","SRC='".$filePath."Custom/",$text);						
		}
		return $text;	
	}
	
	function ss_comma(&$testString,$comma = ',') {
		if (strlen($testString)) $testString .= $comma;	
	}
	
	function ss_pluralize($count,$singular,$plural) {
		if ($count != 1) return $plural;
		return $singular;
	}

	function ss_queryToTab($query,$exclude = array(), $rowFilter = null) {
		$data = '';

		// define some characters for clarity
		$tab = chr(9);
		$newLine = chr(10);
		
		$firstLine = true;
		
		while ($row = $query->fetchRow()) {
			$currentLine = '';
			
			// first line.. add some headers
			if ($firstLine) {
				$firstValue = true;
				foreach(array_keys($row) as $key) {					
					if (count(array_intersect(array($key),$exclude)) == 0) {
						if (!$firstValue) {
							$currentLine .= $tab;
						}
						if ($rowFilter == null or ($rowFilter != null and $key != $rowFilter)) {
							$currentLine .= $key;	
						}
						$firstValue = false;
					}
				}
				
				$currentLine .= $newLine;
				$data .= $currentLine;
				$currentLine = '';
				$firstLine = false;
			}	
			if ($rowFilter == null OR ($rowFilter != null and $row[$rowFilter])) {
				// normal lines.. add the data....
				$firstValue = true;
				foreach($row as $key => $value) {
					if (count(array_intersect(array($key),$exclude)) == 0) {
						if (!$firstValue) $currentLine .= $tab;
						
						if (strstr($value,'"') !== false) {
							// escape any double quotes
							$value = '"'.str_replace('"','""',$value).'"';	
						}
						if ($rowFilter == null or ($rowFilter != null and $key != $rowFilter)) {
							$currentLine .= $value;	
						}
						$firstValue = false;
					}
				}
				
				$currentLine .= $newLine;
				$data .= $currentLine;
			}
		}
		
		return $data;
	}
	
	// Take a tabbed delimited format file and turn it into a FakeQuery object :)
	function ss_ParseTabDelimitedFile($file,$startingRowText = null,$showProgress = false,$headers = null) {
		$query = null;

		// load the tabbed delimited file
		$data = file_get_contents($file);

		$data = str_replace(chr(13).chr(10),chr(10),$data);

		// add the field headers to the file (for the case that the file doesn't include them
		if ($headers !== null) {
			$data = $headers.chr(10).$data;	
		}
		
		// Look the starting row text.. and then skip over any junk before it
		if ($startingRowText !== null) {
			if (substr($data,0,strlen($startingRowText)) != $startingRowText) {
				// if the startingRowText is found...
				if (strpos($data,chr(10).$startingRowText) !== false) {
					// then skip over everything before it
					$data = substr($data,strpos($data,chr(10).$startingRowText));
				} else {
					// assume there is no valid information to import
					$data = '';	
				}
			}
		}
		
		// first replace all double quote pairs with somethin else to ease things
		$escapedQuotes = chr(4);
		$data = str_replace('""',$escapedQuotes,$data);

		// define some characters for clarity
		$tab = chr(9);
		$newLine = chr(10);

		$row = array();
		$firstLine = true;
		
		// Loop thru the data while there is some
		$nextProgress = 0;
		$originalDataSize = strlen($data);
		
		while (strlen($data)) {
			$extraQuote = '';
			$endOfLine = false;
			
			// If we have a quote at the start of the data, then go into extraquote mode
			if (substr($data,0,1) == '"') {
				$extraQuote = '"';
			}
			
			// Find the next new line or tab (with optional quote character before it,
			// This will allow us to eat tabs or newlines within the single cell)
			$nextTabPos = strpos($data,$extraQuote.$tab);
			$nextLinePos = strpos($data,$extraQuote.$newLine);
			
			if ($nextTabPos !== false and $nextLinePos !== false) {
				// If we have both a newline pos and tab position, then use the first one
				if ($nextTabPos < $nextLinePos) {
					$endPos = $nextTabPos;	
				} else {
					$endPos = $nextLinePos;
					$endOfLine = true;
				}
			} else {
				// Otherwise, use either value which is valid
				if ($nextTabPos === false) {
					$endPos = $nextLinePos;
					$endOfLine = true;
				} else if ($nextLinePos === false) {
					$endPos = $nextTabPos;	
				}
			}
		
			// If no valid end pos was found, assume its the end of the file	
			if ($endPos === false) {
				$endPos = strlen($data);	
				$endOfLine = true;
			} else {
				// If we were using the extra quotes mode then make sure we eat the quote
				if ($extraQuote == '"') {
					$endPos++;	
				}
			}
			
			// Grab the field data
			$field = trim(substr($data,0,$endPos));
			$field = str_replace($escapedQuotes,'"',$field);
			if ($extraQuote == '"') {
				$field = substr($field,1,-1);	
			}

			// Add it to the row data  :: added escape 4-10-05 to prevent query errors
			array_push($row,escape($field));
			
			if ($endOfLine) {
				if ($firstLine) {
					// If its the first line, make a new fake query
					$query = new FakeQuery($row);
					$firstLine = false;
				} else {
					// Otherwise, add the data to the query
					$query->addRow($row);	
				}
				unset($row);
				// and get ready for a new row
				$row = array();	
			}
			
			// Eat the field we just read 
			$data = substr($data,$endPos+1);
			
			if ($showProgress) {
				if (($originalDataSize-strlen($data))/$originalDataSize*100 > $nextProgress) {
					print($nextProgress.' ');
					flush();	
					$nextProgress += 1;
				}
			}
		}
		return $query;
	}

	function ss_alphaNumeric($string,$replacement) {
		return preg_replace("/[^A-Za-z0-9]/",$replacement,$string);
	}

	function ss_CleanURL($string,$replaceWith = '_') {
		return trim(str_replace($replaceWith.$replaceWith,$replaceWith,ss_alphaNumeric($string,$replaceWith)),$replaceWith);
	}
	
	function ss_decimalFormat($value,$decimalPlaces='2') {
		if ($value === null) return null;
		return sprintf("%01.{$decimalPlaces}f",$value);	
	}
	
	function ss_roundMoney($value,$type='up') {
		if ($value === null) return null;
		if ($type == 'up') {
			// Hmm.. looks like you can't do $value % 0.05.. so we do some math
			$value100 = round($value * 100);
			$remainder = $value100 % 5;
			if ($remainder != 0) return ss_decimalFormat(($value100-$remainder+5)/100);
		} else if ($type == 'hungarian') {
			die('Not implemented yet :)');
		} else if ($type == 'rounddollarup') {
			$valuedollar = round($value + 0.5);
			return sprintf("%01.0f",$valuedollar);	
    	}
		return ss_decimalFormat($value);
	}

	function add_children( $data, $ind, $cats )
	{
		if( count( $data['categories'][$ind]['Children'] ) > 0 )
		foreach( $data['categories'][$ind]['Children'] as $child )
		{
			$cats[] = $child;
			add_children( $data, $child, $cats );
		}
	}

	function output_row( $data, $ind, $level )
	{
		$highlight = -1;
		if( $pos = strpos( $_REQUEST['REQUEST_URI'], 'pr_ca_id/' ) )
		{
			$rest = substr( $_REQUEST['REQUEST_URI'], $pos+strlen('pr_ca_id/') );

			if( $pos = strpos( $rest, '/' ) )
				$rest = substr( $rest, 0, $pos );

			if( $rest > 0 )
				$highlight = $rest;
		}
		if( $level > $data['categories'][$ind]['cnv_open'] )
			echo "<div id='cat$ind' style='display:none;'>";
		else
			echo "<div id='cat$ind' style='display:;'>";

		if( $highlight == $ind )
			echo "<a class='textsideLinksCurrent' style='margin:".($level*10)."px 15px 0px 15px;' href=\"".$data['AssetPath']."/Service/Engine/OrderBy/Avail.Price/pr_ca_id/".$data['categories'][$ind]['ca_id']."\">";
		else
			echo "<a class='textsideLinks' style='margin:".($level*10)."px 15px 0px 15px;' href=\"".$data['AssetPath']."/Service/Engine/OrderBy/Avail.Price/pr_ca_id/".$data['categories'][$ind]['ca_id']."\">";

		echo "<span style='text-align:left;'>";
		echo $data['categories'][$ind]['ca_name'];
		echo "</span>";

		/*		Patrick didn't want this!!!!
		echo "<span style='text-align:right;'>";
		echo "   (";
		echo $data['categories'][$ind]['Available'];
		echo ")";
		echo "</span>";
		*/

		echo "</a>";

		if( count( $data['categories'][$ind]['Children'] ) > 0 )
		{
			// poke out an opener for all the children
			echo "<a class='textsideLinksMore' href=\"javascript:";
			foreach( $data['categories'][$ind]['Children'] as $child )
				echo "togglecat( '$child' );";
			echo "void(0);\">(more)</a>";
		}

		echo "</div>";
	}

	function output_children( $data, $ind, $level )
	{
		$level++;
		if( count( $data['categories'][$ind]['Children'] ) > 0 )
			foreach( $data['categories'][$ind]['Children'] as $child )
			{
				output_row( $data, $child, $level );
				output_children( $data, $child, $level );
			}
}
?>
