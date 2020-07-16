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

	function __construct( )
	{
//		$this->db = mysqli_connect("host=".DB_HOST." dbname=".DB_NAME." user=".DB_USER." password=".DB_PASSWORD);
//		$this->db = mysqli_connect("host=".DB_HOST." dbname=".DB_NAME." user=".DB_USER);
		$this->db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)
            or die ("Could not connect");

		if ($this->db === false)
		{
			$this->error = mysqli_error();
			print_r( debug_backtrace() );
		}

		mysqli_select_db($this->db, DB_NAME)
			or die ( "Error selecting database" );
	}

	function get_row( $query )
	{
		// get the first row from a q, return an assoc array
		if( $q = $this->query( $query ) )
			if( $r = $this->fetch_assoc( $q ) )
				return $r;
			else
				return array();
		else
			return NULL;
	}

	function get_value( $query )
	{
		// get the first row from a q, return an assoc array
		if( $q = $this->query( $query ) )
			if( $r = $this->fetch_array( $q ) )
				return $r[0];
			else
				return NULL;
		else
			return NULL;
	}

	function query( $query )
	{
		$res = mysqli_query ( $this->db, $query  );
		if ( !$res )
		{
			$this->error = mysqli_error($this->db);
			print_r( debug_backtrace() );
			print( "<br/>Query Error: ".$query."<br/>\n" );
			return 0;
		}

		//echo "<br/>Query Debug>> ".$query."<br/>";
		return $res;
	}

	function data_seek( $result, $row_id )
	{
		if( !$result->data_seek($row_id) )
		{
			$this->error = mysqli_error();
			print( "<br/>Error at :<br/>" );
			print_r( debug_backtrace() );
			return 0;
		}
		return $res;
	}

	function fetch_row( $result, $row = null )
		{
		return mysqli_fetch_row( $result );
		}

	function fetch_array( $result, $result_type = MYSQLI_BOTH )
		{
		return mysqli_fetch_array( $result, $result_type );
		}

	function fetch_object( $result, $row = null, $result_type = null )
		{
		$res = mysqli_fetch_object( $result, $row, $result_type );
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
		return mysqli_fetch_assoc( $result );
		}

	function fetch_all( $result )
		{
		$ret = array();

		while( $r = mysqli_fetch_assoc( $result ) )
			$ret[] = $r;

		return $ret;
		}


//	function fetch_array( $result, $result_type = MYSQLI_BOTH )
//	{
//		return $result->fetch_array( $result_type );
//	}
//

	function num_rows( $result )
	{
		return $result->num_rows;
	}

	function get_last_auto_key( )
	{
		return mysqli_insert_id( $this->db );
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
		$result->free();
	}

	function esc( $str )
	{
		return mysqli_real_escape_string( $str );
	}

	function rows_affected()
	{
		return mysqli_affected_rows( $this->db );
	}

	function error()
	{
		return $this->error;
	}
}

	global $dbC;

	if( !$dbC )
		if( ($dbC = new dbClass( )) == false )
			echo "Unable to instantiate database connector";

?>
