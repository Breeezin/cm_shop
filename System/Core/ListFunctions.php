<?php
/*
name: ListFunctions.php v0.9b
created: 10/2001
author: David Huyck <b@bombusbee.com>
description: I attempted to recreate ColdFusion's list functions in this set of functions.
	I have used the same function names, although the exact functionality may not be
	replicated in all circumstances (especially in ListSort()).  This functionality is
	essential to the core code in PHP Fusebox 3, plus it is pretty useful in general.
updated 11/6/2001: Found a bug in the ListAppend function that pointed to a larger problem of
	empty lists getting turned into arrays with one member ("").  This problem should be fixed.
UPDATED 4/1/2002: Added a check to make sure the functions are not yet defined.  Problems can
	occur when an app Modules another app but does not use the same root app.
*/

if (!function_exists('_listFuncs_PrepListAsArray')) {
	function ArrayToList($inArray, $inDelim = ",") {
		$outList = join($inDelim, $inArray);
		return $outList;
	}
	
	function ArrayKeysToList($inArray, $inDelim = ",") {
		$outList = join($inDelim, array_keys($inArray));
		return $outList;
	}	
	
	function ListAppend($inList, $inValue, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		array_push($aryList, $inValue);
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListChangeDelims($inList, $inNewDelim, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outList = join($inNewDelim, $aryList);
		return $outList;
	}
	
	function ListContains($inList, $inSubstr, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outIndex = 0;
		$intCounter = 0;
		foreach($aryList as $item) {
			$intCounter++;
			if(preg_match("/" . preg_quote($inSubstr) . "/", $item)) {
				$outIndex = $intCounter;
				break;
			}
		}
		return $outIndex;
	}
	
	function ListContainsNoCase($inList, $inSubstr, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outIndex = 0;
		$intCounter = 0;
		foreach($aryList as $item) {
			$intCounter++;
			if(preg_match("/" . preg_quote($inSubstr) . "/i", $item)) {
				$outIndex = $intCounter;
				break;
			}
		}
		return $outIndex;
	}
	
	function ListDeleteAt($inList, $inPosition, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		array_splice($aryList, $inPosition-1, 1);
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListFind($inList, $inSubstr, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outIndex = 0;
		$intCounter = 0;
		foreach($aryList as $item) {
			$intCounter++;
			if(preg_match("/^" . preg_quote($inSubstr, "/") . "$/", $item)) {
				$outIndex = $intCounter;
				break;
			}
		}
		return $outIndex;
	}
	
	function ListFindNoCase($inList, $inSubstr, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outIndex = 0;
		$intCounter = 0;
		foreach($aryList as $item) {
			$intCounter++;
			if(preg_match("/^" . preg_quote($inSubstr, "/") . "$/i", $item)) {
				$outIndex = $intCounter;
				break;
			}
		}
		return $outIndex;
	}
	
	function ListFirst($inList, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outItem = array_shift($aryList);
		return $outItem;
	}
	
	function ListGetAt($inList, $inPosition, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outItem = $aryList[$inPosition-1];
		return $outItem;
	}
	
	function ListInsertAt($inList, $inPosition, $inValue, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		if($inPosition < 1){ $inPosition = 1; }
		array_splice($aryList, $inPosition-1, 0, $inValue);
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListLast($inList, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outItem = array_pop($aryList);
		return $outItem;
	}
	
	function ListLen($inList, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outInt = (strlen($inList)>0)?count($aryList):0;
		return $outInt;
	}
	
	function ListPrepend($inList, $inValue, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		array_unshift($aryList, $inValue);
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListQualify($inList, $inQualifier, $inDelim = ",") {
		$inCharAll = (func_num_args() == 4)?func_get_arg(3):"ALL";
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$intCounter = 0;
		foreach($aryList as $item) {
			if(strtoupper($inCharAll) == "ALL" || (strtoupper($inCharAll) == "CHAR" && preg_match("/\D/", $item))) {
				$aryList[$intCounter] = $inQualifier . $item . $inQualifier;
			}
			$intCounter++;
		}
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListRest($inList, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		array_shift($aryList);
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListSetAt($inList, $inPosition, $inValue, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$aryList[$inPosition-1] = $inValue;
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListSort($inList, $inSortType, $inSortOrder = "ASC") {
		//a bit buggy yet...
		$inDelim = (func_num_args() == 4)?func_get_arg(3):",";
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		if(strtoupper($inSortType) == "NUMERIC") {
			sort($aryList, "SORT_NUMERIC");
		} elseif(strtoupper($inSortType) == "TEXT") {
			sort($aryList, "SORT_REGULAR");
		} elseif(strtoupper($inSortType) == "TEXTNOCASE") {
			sort($aryList, "SORT_STRING");
		}
		if(strtoupper($inSortOrder) == "DESC") {
			array_reverse($aryList);
		}
		$outList = join($inDelim, $aryList);
		return $outList;
	}
	
	function ListToArray($inList, $inDelim = ",") {
		$outArray = _listFuncs_PrepListAsArray($inList, $inDelim);
		return $outArray;
	}
	
	function ListToKeyArray($inList, $inDelim = ",") {
		$outArray = array();
		foreach (ListToArray($inList,$inDelim) as $value) {
			$outArray[$value] = 1;
		}
		return $outArray;	
	}
	
	
	function ListValueCount($inList, $inValue, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outInt = 0;
		foreach($aryList as $item) {
			if($item == $inValue){ $outInt++; }
		}
		return $outInt;
	}
	
	function ListValueCountNoCase($inList, $inValue, $inDelim = ",") {
		$aryList = _listFuncs_PrepListAsArray($inList, $inDelim);
		$outInt = 0;
		foreach($aryList as $item) {
			if(strtolower($item) == strtolower($inValue)){ $outInt++; }
		}
		return $outInt;
	}
	
	//private function
	function _listFuncs_PrepListAsArray($inList, $inDelim) {
		$inList = trim($inList);
		$inList = preg_replace("/^" . preg_quote($inDelim, "/") . "+/", "", $inList);
		$inList = preg_replace("/" . preg_quote($inDelim, "/") . "+$/", "", $inList);
		$outArray = preg_split("/" . preg_quote($inDelim, "/") . "+/", $inList);
		if(count($outArray) == 1 && $outArray[0] == "") {
			$outArray = array();
		}
		return $outArray;
	}
	//private function
	function _listFuncs_PrepListAsList($inList, $inDelim) {
		$inList = trim($inList);
		$inList = preg_replace("/^" . preg_quote($inDelim, "/") . "+/", "", $inList);
		$inList = preg_replace("/" . preg_quote($inDelim, "/") . "+$/", "", $inList);
		$outList = preg_replace("/" . preg_quote($inDelim, "/") . "+/", $inDelim, $inList);
		return $outList;
	}
}

?>
