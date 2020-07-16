<?php
require("db.php");
require("db_shared2.php");
require("auth.php");
echo "<style type=\"text/css\">@import url(\"site.css\");</style>";

echo "<head></head><body>";
if( IsSet( $parent_script ) )
	echo "<p><a href=\"$parent_script\">Back</a></p>";

$CompQ = $dbC->query("select * from competitor where co_active > 0");
while( $r = $dbC->fetch_assoc( $CompQ ) )
	echo "<a href='scrape.php?co_id={$r['co_id']}'>Run scraper for {$r['co_name']}</a><br />";

echo "</body>";
