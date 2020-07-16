<?php

require_once('../Custom/GlobalSettings.php');

if (!defined ('DB_NAME'))
	define ('DB_NAME', $dbCfg['dbName']);

if (!defined ('DB_HOST'))
	define ('DB_HOST', $dbCfg['dbServer']);

if (!defined ('DB_USER'))
	define ('DB_USER', $dbCfg['dbUsername']);

if (!defined ('DB_PASSWORD'))
	define ('DB_PASSWORD', $dbCfg['dbPassword']);

// simplest abstraction layer i can manage...  Rex

class dbClass
{
	var $db;
	var	$error;

	function dbClass( )
		{
//		$this->db = mysql_connect("host=".DB_HOST." dbname=".DB_NAME." user=".DB_USER." password=".DB_PASSWORD);
//		$this->db = mysql_connect("host=".DB_HOST." dbname=".DB_NAME." user=".DB_USER);
		$this->db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
            or die ("Could not connect to DB server");

		if ($this->db === false)
			{
			$this->error = mysql_error();
			print_r( debug_backtrace() );
			}

		mysql_select_db(DB_NAME)
			or die ( "Error selecting database" );
		}

	function query ( $query )
		{
		$res = mysql_query ( $query, $this->db );
		if ( !$res )
			{
			$this->error = mysql_error();
			print_r( debug_backtrace() );
			print( "<br/>Query Error: ".$query );
			return 0;
			}

		//echo "<br/>Query Debug>> ".$query."<br/>";
		return $res;
		}

	function data_seek( $result, $row_id )
		{
		$res = mysql_result_seek($result, $row_id );
		if ( !$res )
			{
			$this->error = mysql_error();
			print( "<br/>Error at :<br/>" );
			print_r( debug_backtrace() );
			return 0;
			}
		return $res;
		}

	function fetch_row( $result, $row = null )
		{
		return mysql_fetch_row( $result );
		}

	function fetch_array( $result, $result_type = MYSQL_BOTH )
		{
		return mysql_fetch_array( $result, $result_type );
		}

	function fetch_object( $result, $row = null, $result_type = null )
		{
		$res = mysql_fetch_object( $result, $row, $result_type );
		if ( !$res )
			{
			$this->error = mysql_error();
			print( "<br/>Error at :<br/>" );
			print_r( debug_backtrace() );
			return 0;
			}
		return $res;
		}

	function fetch_assoc( $result, $row = null )
		{
		return mysql_fetch_assoc( $result );
		}

	function fetch_all( $result )
		{
		$ret = array();

		while( $r = mysql_fetch_assoc( $result ) )
			$ret[] = $r;

		return $ret;
		}


	function num_rows( $result )
		{
		return mysql_num_rows( $result );
		}

	function get_last_key( $table )
		{
		// we might need to address concurreny issues here.  Sometime.  Actually here and everywhere else.

		$val = -1;
		// grab primary key name
		$q = "select attname from mysql_index, mysql_class, mysql_attribute where mysql_class.oid = mysql_index.indrelid and mysql_attribute.attnum = mysql_index.indkey[0] and mysql_attribute.attrelid = mysql_class.oid and mysql_class.relname = '$table'";
		$res = $this->query( $q );
		$row = $this->fetch_assoc( $res );
		$attname = trim($row['attname']);

		if( strlen($attname) > 0 )
			{
		/*  this no longer seems to work, doh!
			$seqName = $table."_".$attname."_seq";
			$nq = "select currval( '$seqName' ) as val";
			$nres = $this->query( $nq );
			$nrow = $this->fetch_assoc( $nres );
			$val = $nrow['val'];
		*/
			$nq = "select max(".$attname.") as val from $table";
			$nres = $this->query( $nq );
			$nrow = $this->fetch_assoc( $nres );
			$val = $nrow['val'];
			}

		return $val;
		}

	function get_next_key( $table )
		{
		return $this->get_last_key( $table ) + 1;
		}

	function free_result( $result )
	{
		return mysql_free_result( $result );
	}

	function esc( $str )
	{
		return mysql_real_escape_string( $str );
	}

	function error()
	{
		return $this->error;
	}
}

?>
