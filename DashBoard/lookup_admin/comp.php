<?php
require("db.php");
require("auth.php");
echo "<style type=\"text/css\">@import url(\"site.css\");</style>";


$Q = $dbC->query( "select * from competitor" );
while( $row = $dbC->fetch_assoc( $Q ) )
	{
	echo $row['co_name']
		." <a href='http://www.acmerockets.com/DashBoard/lookup_admin/scrape.php?co_id=".$row['co_id']."'> Run just this one afresh </a>"
		."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href='http://www.acmerockets.com/DashBoard/lookup_admin/scrape.php?co_id=".$row['co_id']."&debug=1'> Run just this one using cached results </a>"
		."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href='http://www.acmerockets.com/DashBoard/lookup_admin/scrape.php?co_id=".$row['co_id']."&reset=1'> Run this one including scrapes that have never worked </a>"
		."<br /><br />";
	}

?>
