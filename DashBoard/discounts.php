<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	display_query( "select us_email, us_first_name, us_last_name, us_payment_gateway, us_discount, us_discount_expires from users where us_discount > 0 and us_discount_expires > now()" );

	exit;
?>
