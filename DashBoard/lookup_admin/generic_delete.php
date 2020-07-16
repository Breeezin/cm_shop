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
	echo "<td><a href=\"$list_script\">Back</a></td>";

if( !IsSet( $_GET[$key] ) )
	if( !IsSet( $_POST[$key] ) )
		{
		echo "<br> Who ya gonna edit ?  Ghostbusters ?<br>";
		echo "</html>";
		exit;
		}
	else
		$sanitised_key = $_POST[$key];
else
	$sanitised_key = $_GET[$key];

if( $fields[$key] == "S" )
	$sanitised_key = "'".$sanitised_key."'";

if( IsSet( $_POST['update'] ) )
    {
    $query = "delete from $tablename ";
	$query .= " where $key = ".$sanitised_key;

	echo "<br>";
	echo "<br>";
	echo $query;
	echo "<br>";
	echo "<br>";

    $result = $dbC->query( $query );
    }

echo "<br>";

$query = "select * from $tablename where $key = ".$sanitised_key;
$result = $dbC->query( $query );

echo "<form ACTION=\"$delete_script\"METHOD=POST NAME=\"$tablename\">";

echo "<input type=\"hidden\" value=".$sanitised_key." name=\"$key\">";
echo "<input type=\"hidden\" value=\"".$update."\" name=\"update\">";

if ($row = $dbC->fetch_assoc($result))
    {
    echo "<table BORDER COLS=3 WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
    echo "<tr>";
    echo "<td>Column</td>";
    echo "<td>Value</td>";
    echo "</tr>";

    echo "<tr>";
	if( array_key_exists( $key, $titles ) )
		echo "<td>".$titles[$key]."</td>";
	else
		echo "<td>$key</td>";
    echo "<td>".$row[$key]."</td>";
    echo "</tr>";

	foreach ($fields as $field=>$type)
		{
		echo "<tr>";
		if( array_key_exists( $field, $titles ) )
			echo "<td>".$titles[$field]."</td>";
		else
			echo "<td>$field</td>";
		echo "<td>".$row[$field]."</td>";
		echo "</tr>";
		}

    echo "<td><input type=\"submit\" value=\"Permanently Delete\" name=\"Submit\"></td>";
    }


echo "</form>";
echo "<br>";
echo "<table BORDER COLS=3 WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
echo "<tr>";
echo "<td><a href=\"$delete_script?$key=".($row[$key]-1)."\">Previous</a> </td>";
echo "<td><a href=\"$delete_script?$key=".($row[$key]+1)."\">Next</a> </td>";
echo "</tr>";
echo "</table>";

?>
