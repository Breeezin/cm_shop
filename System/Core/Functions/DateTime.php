<?php 

	require_once( "System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_Extensions/IsHoliday.php" );

	function isLeapYear($date = null) {
		if ($date == null) {
			$data = now();
		}
		return date('L', $date);
	}

	function ss_dayOfWeek($day,$month,$year) {
		
	    $a = floor((14 - $month)/12);
	    $y = $year - $a;
	    $m = $month + 12*$a - 2;
	    $d = ($day + $y + floor($y/4) - floor($y/100) + floor($y/400) + floor((31*$m)/12)) % 7;
	    
	    return $d+1;
	}

	
	function NthDay($nth,$weekday,$month,$year = null) {
		if ($year == null) $year = date('Y', now());	
		$yearMonths = array(12, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);	
		$leapYearMonths = array(12, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);	
    	if ($nth > 0) return ($nth-1)*7 + 1 + (7 + $weekday - ss_dayOfWeek(($nth-1)*7 + 1,$month,$year))%7;
    
		if (isLeapYear()) $days = $leapYearMonths[$month];
    	else              $days = $yearMonths[$month];
    
		return $days - (ss_dayOfWeek($days,$month,$year) - $weekday + 7)%7;
	}


/*
	Date /Time
*/
// Adjust the year for 2 digit years
function ss_AdjustTwoDigitYear($year) {
	if ($year <= 50) $year += 2000;
	if ($year >= 51 && ($year <= 99)) $year += 1900;
	return $year;	
}

// From PHP Manaul
function getmicrotime2(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
} 

function getmicrotime() {
	global $refTime;
	if (!isset($refTime)) {
	    list($usec, $sec) = explode(" ",microtime()); 
		$refTime = $sec;
	}
    list($usec, $sec) = explode(" ",microtime()); 
	$sec = $sec - $refTime;
    return ((float)$usec + (float)$sec); 
}

$time = array();
function TimeBlock($name) {
	global $time;
	$timeNow = getmicrotime();
	if ($name == 'Start') {
		$time['StartTime'] = $timeNow;
		$time['lastStartTime'] = $timeNow;
		$time['currentlyTiming'] = NULL;		
		$time['times'] = array();
	} else if ($name == 'Finish') {
 		if ($time['currentlyTiming'] != NULL) {
			if (!array_key_exists($time['currentlyTiming'],$time['times'])) {
				$time['times'][$time['currentlyTiming']] = 0;
			}
			$time['times'][$time['currentlyTiming']] += $timeNow-$time['lastStartTime'];
		}
		$time['times']['Total'] = $timeNow-$time['StartTime'];	
	} else {
		if ($time['currentlyTiming'] != NULL) {
			if (!array_key_exists($time['currentlyTiming'],$time['times'])) {
				$time['times'][$time['currentlyTiming']] = 0;
			}
			$time['times'][$time['currentlyTiming']] += $timeNow-$time['lastStartTime'];
		}
		$time['currentlyTiming'] = $name;
		$time['lastStartTime'] = $timeNow;
	}
}
function timerStart($name) {
	global $timer;
	global $cfg;
	if (!isset($cfg) or $cfg['debugMode']) $timer = $timer->start($name);
}

function timerFinish($name) {
	global $timer;
	global $cfg;
	if (!isset($cfg) or $cfg['debugMode']) $timer = $timer->finish($name);
}
function ss_ExplodeDateTime($datetime) {
	$result = array();
	if (strstr($datetime," ")) {
		list($date,$time) = explode(' ',$datetime);
	} else {
		$date = $datetime;
		$time = '';	
	}
	list($result['Year'],$result['Month'],$result['Day']) = explode('-',$date);
	if (strlen($time)) {
		list($result['Hours'],$result['Minutes'],$result['Seconds']) = explode(':',$time);
	} else {
		$result['Hours'] = 0;
		$result['Minutes'] = 0;
		$result['Seconds'] = 0;
	}
	return $result;
}

function ss_SQLtoTimeStamp($datetime) {
	$result = ss_ExplodeDateTime($datetime);
	return mktime($result['Hours'],$result['Minutes'],$result['Seconds'],$result['Month'],$result['Day'],$result['Year']);
}
function ss_TimeStampToSQL($datetime,$quote = "'") {
	if (!strlen($datetime)) return 'NULL';
	return date($quote."Y-m-d H:i:s".$quote,$datetime);
}

function dayOfWeek($date) {
	return date("w",$date);
}

function addOneDay($date) {
	return $date + 60*60*24;
}
	
function dateAdd($datePart,$amount,$date) {
	$month = month($date);
	$year = year($date);
	$day = day($date);

	// add the amount to the specified date part
	switch ($datePart) {
		case 'm':	$month += $amount; break;
		case 'y':	$year += $amount; break;		
	}
	
	// fix up the date
	while (!checkdate($month,$day,$year)) {
		while ($month > 12) {
			$month -= 12;
			$year += 1;
		}
		while ($month < 1) {
			$month += 12;
			$year -= 1;
		}		
	}

	return mktime(0,0,0,$month,$day,$year);
}
	
function year($date) {
	return date('Y',$date);
}
	
function month($date) {
	return date('n',$date);
}
	
function monthAsString($date) {
	return date('F',$date);
}

function day($date,$leadingZero = FALSE) {
	if ($leadingZero) {
		return date('d',$date);
	} else {
		return date('j',$date);
	}
}
	
function ss_PHPDateFormat($format) {
	// e.g 9:03:02am Wednesday 5th August 1996
	
	$conversions = array(
		'mmmm'	=>	'F',	// August
		'mmm'	=>	'M',	// Aug
		'mm'	=>	'm',	// 08
		'm'		=>	'n',	// 8 
		'dddd'	=>	'l',	// Wednesday
		'ddd'	=>	'D',	// Wed
		'dd'	=>	'd',	// 05
		'd'		=>	'j',	// 5
		'yyyy'	=>	'Y',	// 1996
		'yy'	=>	'y',	// 96
		'hh'	=>	'h',	// 09
		'h'	=>	'g',		// 9
		'HH'	=>	'H',	// 09 (24hr)
		'H'	=>	'G',		// 9 (24hr)
		'NN'	=>	'i',	// 00-59 mins	
		'nn'	=>	'i',	// 00-59 mins	
		'S'		=>	's',	// 00-59 secs
		's'		=>	's',	// 00-59 secs
	);

	// Find all the matching ones
	$counter = 1;
	foreach ($conversions as $from => $to) {
		$format = str_replace($from,chr($counter+200),$format);
		$counter++;
	}
	
	// Then replace with the desired php equivalents
	$counter = 1;
	foreach ($conversions as $from => $to) {
		$format = str_replace(chr($counter+200),$to,$format);
		$counter++;
	}
	
	return $format;

}

function date_error($date,$separator = '/') {
	// returns any error with dates, or null if no error
	if (ListLen($date,$separator) == 3) {
		$day = ListGetAt($date,1,$separator);
		$month = ListGetAt($date,2,$separator);
		$year = ss_AdjustTwoDigitYear(ListGetAt($date,3,$separator));
		
		if (checkdate($month,$day,$year)) {						
			return null;		
		} else {
			return 'Invalid date.';
		}
	} else {
		return 'Invalid date format. Please use dd/mm/yyyy format.';
	}
}

function now() {
	return time();
}

function now1() {
	return date('Y-m-d H:i:s');
}

function formatDateTime($date=NULL,$format = "D, jS M Y") {
	if ($date === NULL) {
		$date = '0000-00-00 00:00:00';
	}
	return date($format,strtotime($date));
}

function overdue_in( $day_from, $cnCode, $max_days )
{
	ss_log_message( "overdue in: from : $day_from, country :$cnCode, days : $max_days" );

	$left = $max_days;
	$hols = array();
	$we = array();

	if( strlen( $day_from ) && ( $max_days < 60 ) )
	{
		$cn = getRow( "select * from countries where cn_id = {$cnCode}" );

		if( $cn )
			$hol = new Holiday($cn['cn_two_code']);
		else
			$hol = new Holiday();

		$maxc = 1000;
		for( $day = substr($day_from, 0, 10); $left > 0;  )
		{
			if( --$maxc <= 0 )
				break;

			$dow = date( 'w', strtotime( $day ) );
			if( ( $dow == 0 ) || ($dow == 6 ) )
			{
				$we[] = $day;
//				ss_log_message( "$day is weekend" );
			}
			else
			{
				if( $hol->is_holiday($day) )
				{
					$hols[] = $day;
//					ss_log_message( "$day is holiday" );
				}
				else
				{
//					ss_log_message( "$day is working day" );
					$left--;
				}
			}
			$day = strtotime ( '1 day' , strtotime ( $day ) ) ;
			$day = date( 'Y-m-j', $day );
		}

//		ss_log_message( "holdays = " );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $hols );
//		ss_log_message( "weekends = " );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $we );

		return $day;
	}
	else
	{
		return "";
	}

}

function days_in_transit( $day_from, $cnCode )
{
	if( strlen( $day_from ) )
	{
		if( strlen( $cnCode ) )
			$cn = getRow( "select * from countries where cn_id = {$cnCode}" );
		else
			$cn = getRow( "select * from countries where cn_id = 840" );

		if( $cn )
			$hol = new Holiday($cn['cn_two_code']);
		else
			$hol = new Holiday();

		$working_days = 0;
//		ss_log_message( "now = ".date('Y-m-d'). " from = ".$day_from );
		$hols = array();
		$we = array();
		for( $day = substr($day_from, 0, 10); $day < date('Y-m-d');  )
		{
			$dow = date( 'w', strtotime( $day ) );
			if( ( $dow == 0 ) || ($dow == 6 ) )
			{
				$we[] = $day;
//				ss_log_message( "$day is weekend" );
			}
			else
			{
				if( $hol->is_holiday($day) )
				{
					$hols[] = $day;
//					ss_log_message( "$day is holiday" );
/*					$working_days -= 10;	*/
				}
				else
				{
//					ss_log_message( "$day is working day" );
					$working_days++;
				}
			}

			if( $working_days > 30 )
			{
//				ss_log_message( "holdays = " );
//				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $hols );
		//		ss_log_message( "weekends = " );
		//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $we );
				return "More than 30 days";
			}

			$day = strtotime ( '1 day' , strtotime ( $day ) ) ;
			$day = date( 'Y-m-d', $day );
		}

		if( $working_days < 0 )
			$working_days = 0;

//		ss_log_message( "holdays = " );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $hols );
//		ss_log_message( "weekends = " );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $we );

		return "$working_days working days";
	}
	else
	{
		return "";
	}

}

?>
