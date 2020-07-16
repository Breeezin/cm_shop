<?php
	if( array_key_exists('User', $_SESSION)
	 &&  array_key_exists('us_email', $_SESSION['User'])
	 && strlen($_SESSION['User']['us_email']) )
	echo "<a href='Members'>".$_SESSION['User']['us_email']."</a>";
else
	echo "<a href='Members'>Members Login</a>";
?>
