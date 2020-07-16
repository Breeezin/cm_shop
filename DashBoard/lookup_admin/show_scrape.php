<?php
require("db.php");
require("auth.php");

if( (int)$_GET['id'] > 0 )
	{
	$cs_id = (int)$_GET['id'];

	$Q = $dbC->query("select * from competitor_scraper where cs_id = $cs_id");
	if( $row = $dbC->fetch_assoc( $Q ) )
		{
		$scrape = gzinflate($row['cs_scrape']);
		echo "<html>".strlen( $scrape )." bytes from ".$row['cs_url']." <br/>";
		echo $scrape;
		}
	}

?>
