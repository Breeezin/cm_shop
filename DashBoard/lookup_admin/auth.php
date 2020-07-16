<?php

	global $dbC;

	$dbC = new dbClass( );

	if( $dbC->db === false )
		echo "Bugger";

	echo "Connected to ".DB_NAME."@".DB_HOST."<br />";

	function myaddslashes( $str )
		{
		if( get_magic_quotes_gpc() )
			return $str;
		else
			return addslashes( $str );
		}

	function numberCleanup( $str )
		{
		$unwanted = array(",", "$");
		return str_replace( $unwanted, "", $str );
		}

	function DateDisplay( $iso_date )
		{
		$r = $iso_date;
		return $r[8].$r[9]."/".$r[5].$r[6]."/".$r[0].$r[1].$r[2].$r[3];
		}

	function toISODate( $non_iso_date )
		{
		if( preg_match( "/^[0-3][0-9][\/-][0-1][0-9][\/-][1-2][0-9][0-9][0-9]/", $non_iso_date ) )
			{
			$r = $non_iso_date;
			return $r[6].$r[7].$r[8].$r[9]."-".$r[3].$r[4]."-".$r[0].$r[1];
			}
		else
			{
			return $non_iso_date;
			}
		}
		
?>
