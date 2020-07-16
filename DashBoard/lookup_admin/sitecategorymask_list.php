<?php
require("db.php");
require("auth.php");
echo "<style type=\"text/css\">@import url(\"site.css\");</style>";

$parent_script = "index.php";

if( count( $_POST ) )
	{
	$dbC->query( "delete from site_category_mask" );
	foreach ($_POST as $key=>$val )
		{
		$e = explode( '_', $key );
		$lg_id = $e[1];
		$ca_id = $e[2];

		if( $Lg >= 0 && $ca_id >= 0 && $val == 1 )
			$dbC->query( "insert into site_category_mask (scm_ca_id, scm_lg_id, scm_ca_active) values ($lg_id, $ca_id, 1)" );
		}
	}

echo "<head></head><body>";
if( IsSet( $parent_script ) )
	echo "<p><a href=\"$parent_script\">Back</a></p>";

$mask = array();
$MaskQuery = $dbC->query("select * from site_category_mask");
while( $r = $dbC->fetch_assoc( $MaskQuery ) )
	{
	if( !array_key_exists( $r['scm_ca_id'], $mask ) )
		$mask[$r['scm_ca_id']] = array();
	$mask[$r['scm_ca_id']][$r['scm_lg_id']] = $r['scm_ca_active'];
	}

$CaQuery = "select ca_id, ca_name from shopsystem_categories";
$LgQuery = $dbC->query( "select lg_id, lg_name, lg_site_url from languages" );
$LgResult = $dbC->fetch_all( $LgQuery );

echo "<form action='sitecategorymask_list.php' method='post'>"; 
echo "<table border=1>\n<tr><td>Category</td>";
foreach( $LgResult as $row )
	echo "<td>{$row['lg_name']}<br/>{$row['lg_site_url']}</td>";
echo "</td></tr>\n";
for( $CaResult = $dbC->query( $CaQuery ); $CaRow = $dbC->fetch_assoc($CaResult); )
	{
	echo "<tr><td>{$CaRow['ca_id']} - {$CaRow['ca_name']}</td>";
	foreach( $LgResult as $row )
		{
		$enabled = false;

		if( array_key_exists( $CaRow['ca_id'], $mask )
		 && array_key_exists( $row['lg_id'], $mask[$CaRow['ca_id']] )
		 && $mask[$CaRow['ca_id']][$row['lg_id']] )
		 	$enabled = true;

		echo "<td><input type='CheckBox' name=Enable_{$CaRow['ca_id']}_{$row['lg_id']} value='1' ".($enabled?'checked':'')."/></td>";
		}

	echo "</tr>\n";
	}
echo "</table>";
echo "<input type='submit' name='Submit'/>";
echo "</form>";

die;

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
	if( ($type != 'S') && ($type != 'I') )
		{
		$lookups[$field] = array();
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
		if( ($type != 'S') && ($type != 'I') )
			{
			foreach( $lookups[$field] as $lrow )
				if( $lrow[0] == $row[$field] )
					{
					echo $lrow[1];
					break;
					}
			}
		else
			echo $row[$field];
		echo "</td>";
		}

    echo "<td><a href=\"".$edit_script."?$key=".$row[$key]."\">Edit</td>";
    echo "<td><a href=\"".$delete_script."?$key=".$row[$key]."\">Delete</td>";
    echo "</tr>";
    }
echo "</table>";


?>
