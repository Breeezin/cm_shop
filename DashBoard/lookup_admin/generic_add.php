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

if( IsSet( $parent_script ) )
	echo "<p><a href=\"$parent_script\">Back</a></p>";

$sanitised = array();
foreach ($fields as $field=>$type)
	if( $type == 'S' )
		if( strlen( trim( $_POST[$field] ) ) )
			$sanitised[$field] = "'".myaddslashes(trim($_POST[$field]))."'";
		else
			$sanitised[$field] = "NULL";
	else
		{
		if( is_numeric($_POST[$field] ) )
			$sanitised[$field] = myaddslashes(str_replace('$,.','',$_POST[$field]));
		else
			$sanitised[$field] = "'".myaddslashes(str_replace('$,.','',$_POST[$field]))."'";
		}

$query = '';

if( IsSet( $_POST['update'] ) && $sanitised[$key] != "NULL" )
    {
	if( $fields[$key] == "I" )
		{
		$query = "insert into $tablename ($key, ";
		$values = "(".$dbC->get_next_key($tablename).", ";
		}
	else
		{
		$query = "insert into $tablename (";
		$values = "(";
		}

	foreach ($fields as $field=>$type)
		if( strlen( $sanitised[$field] ) )
			{
			$query .= $field.", ";
			$values .= $sanitised[$field].", ";
			}

	$query = substr( $query, 0, strlen( $query )-2 );
	$values = substr( $values, 0, strlen( $values )-2 );
	$query .= ") values ".$values." )";

	echo "<br>";
	echo "<br>";
	echo $query;
	echo "<br>";
	echo "<br>";

    $result = $dbC->query( $query );
    }

echo "<br>";

echo "<form ACTION=\"$new_script\"METHOD=POST NAME=\"$tablename\">";

echo "<input type=\"hidden\" value=\"".$update."\" name=\"update\">";

echo "<table BORDER COLS=3 WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
echo "<tr>";
echo "<td>Column</td>";
echo "<td>New Value</td>";
echo "</tr>";

foreach ($fields as $field=>$type)
	{
	echo "<tr>";
	if( array_key_exists( $field, $titles ) )
		echo "<td>".$titles[$field]."</td>";
	else
		echo "<td>$field</td>";
	echo "<td>";
	if( ($type == 'S') || ($type == 'I') )
		echo "<input name=\"$field\" value=\"\" size=\"60\">";
	else
		{
		echo "<select name=\"$field\">";
		$qr = $dbC->query($type);
		while( $val = $dbC->fetch_row($qr) )
			echo "<option value=".$val[0].">".$val[1];
		echo "</select>";
		}
	echo "</td>";
	echo "</tr>";
	}

echo "<td><input type=\"submit\" value=\"Insert\" name=\"Submit\"></td>";

echo "</form>";
echo "<br>";
echo "<table BORDER COLS=3 WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
echo "<tr>";
echo "</tr>";
echo "</table>";

?>
