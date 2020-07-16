<?php
	$Title = "Dashboard Index";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";
?>
<html>
<a href='index.php'>Back</a><br/>
<a href='stock_on_hand.php'>Interesting stock on hand for LP</a><br/>
<a href='stock_on_hand.php?vendor=%3D2'>Interesting stock on hand for Swiss</a><br/>
<a href='swiss_stock_value.php'>Stock on hand and values for Swiss</a><br/>
<a href='accessory_stock_value.php'>Stock on hand and values for Accessories</a><br/>
<a href='eoq.php'>Recommended Ordering</a><br/>
<a href='refunds_and_reships_by_month_country.php'>Refunds and Reships 2010</a><br/>
</html>
