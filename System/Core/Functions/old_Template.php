<?php

function processTemplate($templateName,$data = array(),$customTags = array(),$useCustomImagesFolder = null) {
	
	global $cfg;
	
	$cachedName = basename($templateName).'_'.md5($templateName).'.php';
	$cacheFolder = 'Custom/Cache/Templates/';
	$updateCache = FALSE;
	
	if (file_exists($cacheFolder.$cachedName)) {
		clearstatcache();
		$cacheTime = filemtime($cacheFolder.$cachedName);
		$templateTime = filemtime($templateName);
		$thisTime = filemtime('System/Core/Functions/Template.php');
		if ($cacheTime < $templateTime) {
			$updateCache = TRUE;
		}
		if ($cacheTime < $thisTime) {
			$updateCache = TRUE;
		}
	} else {
		$updateCache = TRUE;
	}

	timerStart('Parsing Template');
	if ($updateCache and $cfg['cacheTemplates']) {
		$filename = $templateName;
		$handle = fopen($filename,'r');
		$template = fread($handle,filesize($filename));
		fclose($handle);

		// define allowed tags
		$tagList	=	array(
		'VAR_PARSE'		=>	'print(ss_ParseText($data[\'__NAME__\']));',
		'VAR_URL'		=>	'print(ss_URLEncodedFormat($data[\'__NAME__\']));',
		'VAR_JS'		=>	'print(ss_JSStringFormat($data[\'__NAME__\']));',
		'VAR_RAW'		=>	'print($data[\'__NAME__\']);',
		'VAR'			=>	'print(ss_HTMLEditFormat($data[\'__NAME__\']));',
		'VAR_NO_HTML'	=>	'print($data[\'__NAME__\']);',
		'VAR_DATE' 		=>	'print(date(\'__FORMAT__\',ss_SQLtoTimeStamp($data[\'__NAME__\'])));',
		'VAR_PAD'		=>	'printf(\'%0__LENGTH__d\',$data[\'__NAME__\']);',
		'VAR_NUMBER_FORMAT'		=>	'printf(number_format($data[\'__NAME__\']));',
		'VAR_BR'		=>	'print(ss_HTMLEditFormatWithBreaks($data[\'__NAME__\']));',
		'VAR_CLEANURL'	=>	'print(ss_CleanURL($data[\'__NAME__\']));',
		'VAR_TRUNC'		=>	'if (strlen($data[\'__NAME__\']) > __LENGTH__) print(substr($data[\'__NAME__\'],0,__LENGTH__).\'...\'); else print($data[\'__NAME__\']);',
		'PRINT'			=>	'print(__EXPRESSION__);',
		'IF'			=>	'if (__CONDITION__) {',
		'ELSE'			=>	'} else {',
		'ELSEIF'		=>	'} elseif (__CONDITION__) {',
		'IF_VAR_STRLEN'	=>	'if (array_key_exists(\'__NAME__\',$data) and strlen($data[\'__NAME__\'])) { ',
		'END'			=>	'}',
		'FOR'			=>	'for (__START__;__CONDITION__;__STEP__) {',
		'WHILE'			=>	'while (__CONDITION__) {',
		'SAYHI'			=>	'print("Hi!");',
		'FIELD'			=>	'print($data[\'fieldSet\'][\'__NAME__\']->display(FALSE,\'adminForm\'));',
		'FORMFIELD'			=>	'print($data[\'fieldSet\'][\'__NAME__\']->display(FALSE,\'__FORM__\'));',
		'FIELDSET_FIELD_SUFFIX'		=>	'$data[\'__FIELDSET__\']->displayField(\'__FIELD__\'.$row[\'__SUFFIX__\']);',
		'FIELDSET_FIELD'			=>	'$data[\'__FIELDSET__\']->displayField(\'__FIELD__\');',
		'FIELDSET_FIELD_DISPLAY'	=>	'print($data[\'__FIELDSET__\']->getFieldDisplayValue(\'__FIELD__\'));',
		'FIELDSET_FIELD_VALIDATE'	=>	'$data[\'__FIELDSET__\']->displayField(\'__FIELD__\',TRUE);',
		'SWITCH_VAR'		=>	'switch ($data[\'__NAME__\']) { case \'thisvalueshouldneverbeuseditisjustbecausethephpparserisbroken\': break;',
		'SWITCH_ROW_VAR'	=>	'switch ($row[\'__NAME__\']) { case \'thisvalueshouldneverbeuseditisjustbecausethephpparserisbroken\': break;',
		'CASE'			=>	'case \'__VALUE__\':',
		'BREAK'			=>	'break;',
		'DEFAULT'	=>	'default:',
		'CEREAL'			=>	'print($data[\'cereal\'][\'__NAME__\']->display(FALSE,\'adminForm\'));',
		'EVALUATE'		=>	'__EXPRESSION__;',
		'EVAL'			=>	'__EXPRESSION__',
		'LOOP'			=>	'$tmpl_loop_rows = $data[\'__QUERY__\']->numRows(); $tmpl_loop_counter = 0; while ($row = $data[\'__QUERY__\']->fetchRow()) { $tmpl_loop_counter++;',
		'IF_QUERY_HAS_ROWS'	=>	'if ($data[\'__QUERY__\']->numRows() > 0) {',
		'OTHER_ROWS'	=>	'if ($tmpl_loop_counter > 1 and $tmpl_loop_counter != $tmpl_loop_rows) {',
		'BETWEEN_ROWS'	=>	'if ($tmpl_loop_counter < $tmpl_loop_rows) {',
		'FIRST_ROW'	=>	'if ($tmpl_loop_counter == 1) {',
		'LAST_ROW'	=>	'if ($tmpl_loop_counter == $tmpl_loop_rows) {',
		'NOT_FIRST_ROW'	=>	'if ($tmpl_loop_counter != 1) {',
		'NOT_LAST_ROW'	=>	'if ($tmpl_loop_counter != $tmpl_loop_rows) {',
		'ROW_NUMBER'	=>	'print($tmpl_loop_counter);',
		'ROW_VAR_PARSE'	=>	'print(ss_ParseText($row[\'__NAME__\']));',
		'ROW_VAR_URL'	=>	'print(ss_URLEncodedFormat($row[\'__NAME__\']));',
		'ROW_VAR_JS'	=>	'print(ss_JSStringFormat($row[\'__NAME__\']));',
		'ROW_VAR_HTML'	=>	'print(ss_HTMLEditFormat($row[\'__NAME__\']));',
		'ROW_VAR_HTML_NBSP'	=>	'if (strlen($row[\'__NAME__\'])) print(ss_HTMLEditFormat($row[\'__NAME__\'])); else print(\'&nbsp;\'); ',
		'ROW_VAR_DATE'  =>	'print(date(\'__FORMAT__\',ss_SQLtoTimeStamp($row[\'__NAME__\'])));',
		'ROW_VAR_RAW'	=>	'print($row[\'__NAME__\']);',
		'ROW_VAR'		=>	'print(ss_HTMLEditFormat($row[\'__NAME__\']));',
		'ROW_VAR_PAD'	=>	'printf(\'%0__LENGTH__d\',$row[\'__NAME__\']);',
		'ROW_VAR_NUMBER_FORMAT'	=>	'printf(number_format($row[\'__NAME__\']));',
		'ROW_VAR_BR'		=>	'print(ss_HTMLEditFormatWithBreaks($row[\'__NAME__\']));',
		'ROW_VAR_CLEANURL'	=>	'print(ss_CleanURL($row[\'__NAME__\']));',
		'ROW_VAR_TRUNC'		=>	'if (strlen($row[\'__NAME__\']) > __LENGTH__) print(substr($row[\'__NAME__\'],0,__LENGTH__).\'...\'); else print($row[\'__NAME__\']);',
		'IF_ROW_VAR_STRLEN'	=>	'if (array_key_exists(\'__NAME__\',$row) and strlen($row[\'__NAME__\'])) { ',
		'OUTPUT'		=>	'/*print(ss_HTMLEditFormat($row[\'__FIELD__\']));*/',
		'TAG'			=>	'if (file_exists(expandPath(\'Custom/Tags/__NAME__.php\'))) require(\'Custom/Tags/__NAME__.php\'); else require(\'System/Tags/__NAME__.php\');',
		'EMBED_ASSET'	=>	'$temp = new Request(\'Asset.Embed\',array(\'MainAssetID\' => $data[\'this\']->assetID, \'MainAssetPath\' => $data[\'this\']->assetPath, \'as_id\' => \'__ASSETID__\', \'AssetPath\' => \'__ASSETPATH__\',\'AssetParameters\' => "__PARAMETERS__")); print $temp->display;',
		'EMBED_ASSET_1'	=>	'$temp = new Request(\'Asset.Embed\',array(\'MainAssetID\' => $data[\'this\']->assetID, \'MainAssetPath\' => $data[\'this\']->assetPath, \'as_id\' => __ASSETID__, \'AssetPath\' => \'__ASSETPATH__\',\'AssetParameters\' => "__PARAMETERS__")); print $temp->display;',
		'ERRORS'		=>	'if (count($data[\'__NAME__\']) != 0) {	$errorMessages = \'\'; foreach ($data[\'__NAME__\'] as $messages) foreach ($messages as $message) $errorMessages .= "<LI>$message</LI>"; print(\'<P><TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD CLASS="entryErrors">Errors were detected in the data you entered, please correct the	following issues and re-submit. <UL>\'.$errorMessages.\'</UL></TD></TR></TABLE></P>\'); }',
		'ERRORS2'		=>	'if (count($data[\'__NAME__\']) != 0) {$errorMessages = \'<TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD colspan="2" CLASS="entryErrors">Errors were detected in the data you entered, please correct the following issues and re-submit.</TD></TR>\';foreach ($data[\'__NAME__\'] as $messageKey => $messages){$errorMessages .= \'<TR><TD width="25%" nowrap valign="top"><strong>\'.$messageKey.\'</strong></TD><TD>\';foreach ($messages as $message) {$errorMessages .= "$message<BR>";}$errorMessages .= \'</TD></TR>\';}print($errorMessages.\'</Table>\');}',
		'ADMIN_BUTTON'	=>	'print("<button style=\"width:180px;text-align:left;background-color:white;\" onclick=\"parent.openNamedNonAssetPanel(\'__URL__\',\'__LABEL__\',\'__TARGET__\');return false;\" ><table><tr><td><IMG SRC=\"{$data[\'imagesDirectory\']}__ICON__\"></td><td>__LABEL__</td></tr></table></button>");',
		'ADMIN_BUTTON2'	=>	'print("<button style=\"width:180px;text-align:left;background-color:white;\" onclick=\"\'__onClick__\'\"><table><tr><td><IMG SRC=\"{$data[\'imagesDirectory\']}__ICON__\"></td><td>__LABEL__</td></tr></table></button>");',
		'VAR_PLURALIZE'		=>	'if ($data[\'__NAME__\'] == 1) print ss_HTMLEditFormat(\'__SINGULAR__\'); else print ss_HTMLEditFormat(\'__PLURAL__\');',
		'ROW_VAR_PLURALIZE'	=>	'if ($row[\'__NAME__\'] == 1) print ss_HTMLEditFormat(\'__SINGULAR__\'); else print ss_HTMLEditFormat(\'__PLURAL__\');',
		'ADMIN_LINK'		=>	'print("<p><a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel(\'__URL__\',\'__LABEL__\',\'__TARGET__\');return false;\" class=\"bodytextBlue\">__DESCRIPTION__</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>");',
		'ADMIN_CONFIG_LINK'	=>	'print("<a href=\"javascript:var el=document.getElementById(\'__ID__\');if (el.style.display == \'\') { el.style.display=\'none\'; } else { el.style.display=\'\'; }void(0);\" class=\"bodytextGrey\">__DESCRIPTION__</a>");',
		'ADMIN_CONFIG_LINK_TAB'	=>	'print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel(\'__URL__\',\'__LABEL__\',\'__TARGET__\');return false;\" class=\"bodytextGrey\">__DESCRIPTION__</a>");',
		'ADMIN_MAIN_CONFIG_LINK' =>	'print("<p><a class=\"bodytextBlue\" href=\"javascript:var el=document.getElementById(\'__ID__\');if (el.style.display == \'\') { el.style.display=\'none\'; } else { el.style.display=\'\'; }void(0);\">__DESCRIPTION__</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>");',
		);

// 		'ADMIN_BUTTON'	=>	'print("<button style=\"width:180px;text-align:left;background-color:white;\" onclick=\"res=window.open(\'__URL__\',\'__TARGET__\',\'width=640,height=480,scrollbars=yes,menubar=yes,resizable=yes\');res.focus();return false;\" ><table><tr><td><IMG SRC=\"{$data[\'imagesDirectory\']}__ICON__\"></td><td>__LABEL__</td></tr></table></button>");',
		
		
		$tagList = array_merge($tagList,$customTags);
		
		// find <TMPL_INCLUDE ZZZ> {TMPL_INCLUDE ZZZ}
		// - these are special and they must done first, otherwise any of the template
		//   tags that are included wont get converted
		while (preg_match('/<TMPL_INCLUDE ([^>]+)>/is',$template,$matches)) {
			$includeFile = dirname($templateName).'/'.$matches[1];
			if (file_exists($includeFile)) {
				$filename = $includeFile;
				$handle = fopen($filename,'r');
				$includeTemplate = fread($handle,filesize($filename));
				fclose($handle);
			}
			$template = str_replace($matches[0],$includeTemplate,$template);
		}
		
		// find </TMPL_XXX> and {/TMPL_XXX}
		while (preg_match('/<\/TMPL_([^>]+)>/is',$template,$matches)) {
			if (!array_key_exists(strtoupper($matches[1]),$tagList)) die("Unrecognised tag: TMPL_{$matches[1]} in $filename");
			$replaceText = '<?php } ?>';
			$template = str_replace($matches[0],$replaceText,$template);
		}
		while (preg_match('/{\/TMPL_([^}]+)}/is',$template,$matches)) {
			if (!array_key_exists(strtoupper($matches[1]),$tagList)) die("Unrecognised tag: TMPL_{$matches[1]} in $filename");
			$replaceText = '<?php } ?>';
			$template = str_replace($matches[0],$replaceText,$template);
		}
		
		// find <tmpl_xxx yyy="ZZZ" aaa="BBB" />
		$regex = 	'/<TMPL_([^ >]+)'.
		'('.					// to group all the value/attribute pairs together
		'( ([A-Za-z0-9]+)="'.	// start a value attribute pair
		'([^"]*)'.				// match "value" (\\\")* if u wanna have \"
		'")*'.					// end value attribute pair (and allow zero or more of them)
		')( )*\/?'.				// end group all value attribute pairs
		'>/is';
		while (preg_match($regex,$template,$matches)) {
			if (!array_key_exists(strtoupper($matches[1]),$tagList)) die("Unrecognised tag: TMPL_{$matches[1]} in $filename");
			$parameters = $matches[2];
			$command = $tagList[strtoupper($matches[1])];
			while (preg_match('/ *([A-Za-z0-9]+)="(([^"]*(^\\\)*)*)"/',$parameters,$parameterMatches)) {
				$command = str_replace('__'.strtoupper($parameterMatches[1]).'__',html_entity_decode($parameterMatches[2],ENT_QUOTES),$command);
				$parameters = str_replace($parameterMatches[0],'',$parameters);
			}
			$template = str_replace($matches[0],'<?php '.$command.' ?>',$template);
		}
		
		
		// find {TMPL_XXX YYY="ZZZ"}
		$regex = 	'/{TMPL_([^ }]+)'.
		'('.					// to group all the value/attribute pairs together
		'( ([A-Za-z0-9]+)="'.	// start a value attribute pair
		'([^"]*)'.				// match "value" (\\\")* if u wanna have \"
		'")*'.					// end value attribute pair (and allow zero or more of them)
		')( )*'.					// end group all value attribute pairs
		'}/is';
		while (preg_match($regex,$template,$matches)) {
			if (!array_key_exists(strtoupper($matches[1]),$tagList)) die("Unrecognised tag: TMPL_{$matches[1]} in $filename");
			$parameters = $matches[2];
			$command = $tagList[strtoupper($matches[1])];
			while (preg_match('/ *([A-Za-z0-9]+)="(([^"]*(^\\\)*)*)"/',$parameters,$parameterMatches)) {
				$command = str_replace('__'.strtoupper($parameterMatches[1]).'__',html_entity_decode($parameterMatches[2],ENT_QUOTES),$command);
				$parameters = str_replace($parameterMatches[0],'',$parameters);
			}
			$template = str_replace($matches[0],'<?php '.$command.' ?>',$template);
		}
		
		// find {TMPL_XXX ZZZ} note: assumes YYY
		$regex = 	'/{TMPL_([^ }]+) '.
		'([^}]+)'.		//	anything that isn't a closing curly bracket
		'}/is';
		while (preg_match($regex,$template,$matches)) {
			if (!array_key_exists(strtoupper($matches[1]),$tagList)) die("Unrecognised tag: TMPL_{$matches[1]} in $filename");
			$parameters = $matches[2];
			$command = $tagList[strtoupper($matches[1])];
			if (preg_match('/__[^(__)]+__/',$command,$commandMatches)) {
				$command = str_replace($commandMatches[0],$parameters,$command);
			}
			$template = str_replace($matches[0],'<?php '.$command.' ?>',$template);
		}
		
		// find {[DesignerProof]}
		$regex = '/\{\[([A-Za-z0-9]+)\]\}/';
		while (preg_match($regex,$template,$matches)) {
			$command = $tagList['TAG'];
			$command = str_replace('__NAME__',html_entity_decode($matches[1],ENT_QUOTES),$command);
			$template = str_replace($matches[0],'<?php '.$command.' ?>',$template);
		}
		
		// strip out "<link href="../../Layouts/sty_shop.css" rel="stylesheet" type="text/css">"		
		$searchFor = "<link href=\"../../Layouts/sty_*([A-Za-z0-9]+).css\" rel=\"stylesheet\" type=\"text/css\">";
		$template = eregi_replace($searchFor,'',$template);

		
		// output the parsed template
		$newFilename = $cachedName;
		$newHandle = fopen($cacheFolder.$newFilename,'w');
		flock($newHandle,LOCK_EX);
		fwrite($newHandle,$template);
		flock($newHandle,LOCK_UN);
		fclose($newHandle);
		//print("Created new");
		
	} else {
		//print("Using cached");
	}
	timerFinish('Parsing Template');
	
	// display the template
	ob_start();
	require($cacheFolder.$cachedName);
	$output = ob_get_contents();
	ob_end_clean();
	
	// Fix any relative image paths
	if ($useCustomImagesFolder !== null) {
		$templateDirectory = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$useCustomImagesFolder;
	} else {
		//$templateDirectory = ss_withoutTrailingSlash(ss_absolutePathToURL(dirname($templateName)));
		$templateDirectory = ss_absolutePathToURL(ss_withoutTrailingSlash(dirname($templateName)));
		
	}
	$output = stri_replace('src="Images/','src="'.$templateDirectory.'/Images/',$output);		
	$output = str_replace('\'Images/','\''.$templateDirectory.'/Images/',$output);		
	$output = stri_replace('background="Images/','background="'.$templateDirectory.'/Images/',$output);	
	$output = stri_replace('href="sty_','href="'.$templateDirectory.'/sty_',$output);
	
	return $output;
	
}

?>
