<?php
   session_start();

   global $link;

   echo "<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=iso-8859-1\"><title>";

	if( IsSet( $Title ) )
		echo $Title;
	else
		echo "DashBoard";

   echo "</title>";

    $isAdmin = array_key_exists( 'User', $_SESSION ) && array_key_exists( 'user_groups', $_SESSION['User'] ) && array_key_exists(1,$_SESSION['User']['user_groups']);

//    if( !$isAdmin )
      if( 0 )
        {
        echo "<html><b>You are not an administrator</b><br></html>";
        die;
        }

    if( !array_key_exists( 'HTTP_HOST', $_SERVER ) )
	    $_SERVER['HTTP_HOST'] = 'www.acmerockets.com';

    if( !array_key_exists( 'SERVER_PORT', $_SERVER ) )
	    $_SERVER['SERVER_PORT'] = '443';

    if( !array_key_exists( 'REQUEST_URI', $_SERVER ) )
	    $_SERVER['REQUEST_URI'] = '/index.php';

    require_once('../Custom/GlobalSettings.php');

    //$link = mysql_connect($dbCfg['dbServer'],$dbCfg['dbUsername'], $dbCfg['dbPassword'])
      //	mysqli_connect("127.0.0.1", "my_user", "my_password", "my_db");
    $link = mysqli_connect($dbCfg['dbServer'],$dbCfg['dbUsername'], $dbCfg['dbPassword'], $dbCfg['dbName'] )
            or die ("Could not connect to '".$dbCfg['dbServer']."' as '".$dbCfg['dbUsername']."'");


	function mysql_query( $q )
	{
	global $link;
	return mysqli_query( $link, $q );
	}

	function mysql_free_result( $q )
	{
	global $link;
	return mysqli_free_result( $q );
	}

	function mysql_fetch_assoc( $q )
	{
	global $link;
	return mysqli_fetch_assoc( $q );
	}

	function mysql_errno( )
	{
	global $link;
	return mysqli_errno( $link );
	}

	function mysql_fetch_array( $q )
	{
	global $link;
	return mysqli_fetch_array( $q );
	}

	function mysql_error( )
	{
	global $link;
	return mysqli_error( $link );
	}

	function mysql_affected_rows( )
	{
	global $link;
	return mysqli_affected_rows( $link );
	}

	function mysql_num_rows( $q )
	{
	global $link;
	return mysqli_num_rows( $link, $q );
	}


?>
