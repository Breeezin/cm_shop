<span style="background-color: #3f3830;
	opacity: 0.95; 
    filter:alpha(opacity=95);
    width: 206px;
	height: 45px;">
<?php
	if( array_key_exists('User', $_SESSION)
	 &&  array_key_exists('us_email', $_SESSION['User'])
	 && strlen($_SESSION['User']['us_email']) )
	echo "<a class='membersLoggedin' href='Members'>Members Page<br />".$_SESSION['User']['us_email']."</a>";
else
	echo "<a class='membersLogin' href='Members'> Members Login</a>";
?>
</span>
