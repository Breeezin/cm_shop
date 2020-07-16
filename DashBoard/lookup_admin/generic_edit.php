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

global $dbC;

if( !IsSet( $_GET[$key] ) )
	if( !IsSet( $_POST[$key] ) )
		{
		echo "<br> No edit key<br>";
		echo "</html>";
		exit;
		}
	else
		$sanitised_key = $_POST[$key];
else
	$sanitised_key = $_GET[$key];


$sanitised = array();
$lookups = array();
foreach ($fields as $field=>$type)
	{
	if( $type[0] == 'S' || $type[0] == 'T' )
		if( strlen( trim( $_POST[$field] ) ) )
			$sanitised[$field] = "'".myaddslashes(trim($_POST[$field]))."'";
		else
			$sanitised[$field] = "NULL";
	else
		{
		if( is_numeric($_POST[$field] ) )
			$sanitised[$field] = myaddslashes(str_replace('$,.','',$_POST[$field]));
		else
			if( ( strlen( $_POST[$field] ) == 0 && (strlen( $type ) == 2) && ( $type[1] == 'N' ) ) || !strcmp( $_POST[$field], 'NULL' ) )
				$sanitised[$field] = "NULL";
			else
				$sanitised[$field] = "'".myaddslashes(str_replace('$,.','',$_POST[$field]))."'";
		}

	if( ($type[0] != 'S') && ($type[0] != 'I')  && ($type[0] != 'T') )
		{
		$lookups[$field] = array();
		$lookups[$field][] = array( "NULL", "NULL" );
		$lresult = $dbC->query($type);
		while( $val = $dbC->fetch_row($lresult) )
			$lookups[$field][] = $val;
		}

	if( strlen( $type ) == 2 && $type[1] == 'N' && strlen( $sanitised[$field] ) == 0 )
		$sanitised[$field] = 'NULL';

	}
if( $type[0] == 'S' || $type[0] == 'T' )
	$sanitised_key = "'".myaddslashes($sanitised_key)."'";
else
	$sanitised_key = myaddslashes($sanitised_key);

$query = '';

if( IsSet( $_POST['update'] ) )
    {
    $query = "update $tablename set ";

	foreach ($fields as $field=>$type)
		if( strlen( $sanitised[$field] ) )
			$query .= $field." = ".$sanitised[$field].", ";

	$query = substr( $query, 0, strlen( $query )-2 );
	$query .= " where $key = ".$sanitised_key;

	echo "<br>";
	echo "<br>";
	echo $query;
	echo "<br>";
	echo "<br>";

    $result = $dbC->query( $query );
    }

echo "<br>";

$query = "select *, $key from $tablename where $key = ".$sanitised_key;
$result = $dbC->query( $query );

echo "<form ACTION=\"$edit_script\"METHOD=POST NAME=\"$tablename\">";

echo "<input type=\"hidden\" value=".$sanitised_key." name=\"$key\">";
echo "<input type=\"hidden\" value=\"".$update."\" name=\"update\">";

if ($row = $dbC->fetch_assoc($result))
    {
    echo "<table BORDER COLS=3 WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
    echo "<tr>";
    echo "<td>Column</td>";
    echo "<td>Value</td>";
    echo "<td>New Value</td>";
    echo "</tr>";

    echo "<tr>";
	if( array_key_exists( $key, $titles ) )
		echo "<td>".$titles[$key]."</td>";
	else
		echo "<td>$key</td>";
    echo "<td>".$row[$key]."</td>";
    echo "<td>".$row[$key]."</td>";
    echo "</tr>";

	foreach ($fields as $field=>$type)
		{
		echo "<tr>";
		if( array_key_exists( $field, $titles ) )
			echo "<td>".$titles[$field]."</td>";
		else
			echo "<td>$field</td>";
		if( ($type[0] == 'S') || ($type[0] == 'I') )
			{
			echo "<td>".htmlentities($row[$field])."</td>";
			echo "<td>";
			echo "<input name=\"$field\" value=\"".trim(htmlentities($row[$field]))."\" size=\"".(strlen($row[$field])+5)."\">";
			echo "</td>";
			}
		else
			if( $type[0] == 'T' ) 
				{
				echo "<td>".htmlentities($row[$field])."</td>";
				echo "<td>";
				echo "<textarea name=\"$field\" cols='100' rows='20'>".trim(htmlentities($row[$field]))."</textarea>";
				echo "</td>";
				}
			else
				{
				echo "<td>";
				foreach( $lookups[$field] as $lrow )
					if( $lrow[0] == $row[$field] )
						{
						echo $lrow[1];
						break;
						}
				echo "</td>";
				echo "<td>";
				echo "<select name=\"$field\">";
				foreach( $lookups[$field] as $lrow )
					echo "<option value=".$lrow[0].($lrow[0] == $row[$field]?" selected":"").">".$lrow[1];
				echo "</select>";
				echo "</td>";
				}
		echo "</tr>";
		}

    echo "<td><input type=\"submit\" value=\"Update\" name=\"Submit\"></td>";
    }


echo "</form>";
echo "<br>";
echo "<table BORDER COLS=3 WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
echo "<tr>";
echo "<td><a href=\"$edit_script?$key=".($row[$key]-1)."\">Previous</a> </td>";
if( IsSet( $parent_script ) )
	echo "<td><a href=\"$list_script\">Back</a></td>";
echo "<td><a href=\"$edit_script?$key=".($row[$key]+1)."\">Next</a> </td>";
echo "</tr>";
echo "</table>";

?>
