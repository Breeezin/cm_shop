<?php
   session_start();

    $isAdmin = array_key_exists( 'User', $_SESSION ) && array_key_exists( 'user_groups', $_SESSION['User'] ) && array_key_exists(1,$_SESSION['User']['user_groups']);

    require_once('../Custom/GlobalSettings.php');

    $link = mysql_connect($dbCfg['dbServer'],$dbCfg['dbUsername'], $dbCfg['dbPassword'])
            or die ("Could not connect to '".$dbCfg['dbServer']."' as '".$dbCfg['dbUsername']."'");

    mysql_select_db( $dbCfg['dbName'] )
        or die ( "Error selecting database '".$dbCfg['dbName']."'" );
?>
