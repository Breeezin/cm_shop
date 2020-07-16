<?php
$sname = '';
$script = basename($_SERVER['SCRIPT_NAME']);
if( $pos = strrpos( $script, "_" ) )
    $sname = substr( $script, 0, $pos );
require $sname.".php";

require $database_interface;
require("auth.php");
echo "<style type=\"text/css\">@import url(\"site.css\");</style>";

echo "<head></head><body>";
if( IsSet( $parent_script ) )
	echo "<p><a href=\"$parent_script\">Back</a></p>";
if( IsSet( $new_script ) )
	echo "<p><a href=\"$new_script\">New</a><br/></p>";

echo "<br><b>$tablename</b>";

$filter_clause = '';
$seen_post = false;
if( isset( $searches ) && is_array($searches) )
	{
	foreach( $searches as $search )
		{
		if( array_key_exists( 'filter_'.$search, $_POST ) )
			$seen_post = true;

		if( array_key_exists( 'filter_'.$search, $_POST ) and strlen($_POST['filter_'.$search]) )
			$filter_clause .= " and ".$search." ~ '.*".$_POST['filter_'.$search].".*'";

		if( array_key_exists( 'filter_'.$search, $_GET ) and strlen($_GET['filter_'.$search]) )
			$filter_clause .= " and ".$search." ~ '.*".$_GET['filter_'.$search].".*'";
		}

	if( strlen( $filter_clause ) )
		$seen_post = true;
	}

$query = "select *, $key from ".$tablename;

if( strlen( $filter_clause ) )
	$query .= ' where '.substr( $filter_clause, 5);

$order = '';
if( array_key_exists( 'order', $_GET ) )
	$order .= myaddslashes( $_GET['order'] );
else
	if( array_key_exists( 'order', $_POST ) )
		$order .= myaddslashes( $_POST['order'] );
if( strlen( $order ) )
	$query .= ' order by '.$order;

echo "<br>".$query."<br/>";
$result = $dbC->query( $query );

if( !$result )
	{
	echo $dbC->error;
	die;
	}

echo "<br/>";
echo "<br/>";

if( isset( $searches ) && is_array($searches) )
	{
	echo "<form action=".$list_script." METHOD=POST NAME=".$tablename.">";
	echo "<input type=\"hidden\" value=".$order." name=\"order\">";
	echo "<table><tr>";
	foreach( $searches as $search )
		{
		echo "<td>".$titles[$search]."</td><td><input name='filter_".$search."' value='";
		if( array_key_exists( 'filter_'.$search, $_GET ) and strlen($_GET['filter_'.$search]) )
			echo $_GET['filter_'.$search];
		else
			if( array_key_exists( 'filter_'.$search, $_POST ) and strlen($_POST['filter_'.$search]) )
				echo $_POST['filter_'.$search];
		echo "' size=10></td>";
		}
    echo "<td><input type=\"submit\" value=\"Search\" name=\"Submit\"></td>";
	echo "</tr></table>";
	echo "</form>";
	if( !$seen_post )
		die;
	}

echo "<table BORDER COLS=9 WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";

echo "<tr>";
echo "<td><a href=\"$list_script?order=$key";
if( isset( $searches ) && is_array($searches) )
	foreach( $searches as $search )
		{
		if( array_key_exists( 'filter_'.$search, $_POST ) and strlen($_POST['filter_'.$search]) )
			echo "&filter_".$search."=".$_POST['filter_'.$search];
		if( array_key_exists( 'filter_'.$search, $_GET ) and strlen($_GET['filter_'.$search]) )
			echo "&filter_".$search."=".$_GET['filter_'.$search];
		}
echo "\">ID</a></td>";
$lookups = array();
foreach ($fields as $field=>$type)
	{
	echo "<td><a href=\"$list_script?order=$field";
	if( isset( $searches ) && is_array($searches) )
		foreach( $searches as $search )
			{
			if( array_key_exists( 'filter_'.$search, $_POST ) and strlen($_POST['filter_'.$search]) )
				echo "&filter_".$search."=".$_POST['filter_'.$search];
			if( array_key_exists( 'filter_'.$search, $_GET ) and strlen($_GET['filter_'.$search]) )
				echo "&filter_".$search."=".$_GET['filter_'.$search];
			}
	echo "\">";
	if( array_key_exists( $field, $titles ) )
		echo $titles[$field]."</a></td>";
	else
		echo $field."</a></td>";
	if( ($type[0] != 'S') && ($type[0] != 'I') && ($type[0] != 'T') )
		{
		$lookups[$field] = array( );
		$lookups[$field][] = array( 'NULL' => 'NULL' );
		$lresult = $dbC->query($type);
		while( $val = $dbC->fetch_row($lresult) )
			$lookups[$field][] = $val;
		}
	}
echo "<td></td>";
echo "</tr>";

while ($row = $dbC->fetch_assoc($result))
    {
    echo "<tr><a NAME=\"c".$row[$key]."\"></a>";
	echo "<td>".$row[$key]."</td>";

	foreach ($fields as $field=>$type)
		{
		echo "<td>";
		if( ($type[0] != 'S') && ($type[0] != 'I') && ($type[0] != 'T') )
			{
			foreach( $lookups[$field] as $lrow )
				if( $lrow[0] == $row[$field] )
					{
					echo $lrow[1];
					break;
					}
			}
		else
			echo htmlentities($row[$field]);
		echo "</td>";
		}

    echo "<td><a href=\"".$edit_script."?$key=".$row[$key]."\">Edit</td>";
    echo "<td><a href=\"".$delete_script."?$key=".$row[$key]."\">Delete</td>";
    echo "</tr>";
    }
echo "</table>";


?>
