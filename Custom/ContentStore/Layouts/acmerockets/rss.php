<a href="rss.php<?php
	if( array_key_exists('User', $_SESSION )
	  and array_key_exists('us_id', $_SESSION['User'] )
	  and array_key_exists('us_token', $_SESSION['User'] ) )
		echo "?a={$_SESSION['User']['us_id']}&b={$_SESSION['User']['us_token']}";
?>"> Subscribe via RSS <img src="Custom/ContentStore/Templates/acmerockets/NewsAsset/Images/rss.png"/></a> 
