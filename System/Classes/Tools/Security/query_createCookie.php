<?

	$this->param('BackURL');
	
	if( array_key_exists( 'User', $_SESSION )
	 && array_key_exists( 'us_password', $_SESSION['User'] )
	 && array_key_exists( 'us_id', $_SESSION['User'] ) )
	{
		$cookieSettings = array(
			'UserID'	=>	$_SESSION['User']['us_id'],
			'Auth'	=>	md5($_SESSION['User']['us_id'].$_SESSION['User']['us_password'].'salt'),
		);
		
		setcookie('keepMeLoggedInCookie',serialize($cookieSettings),time()+3600*24*365*5,str_replace('index.php','',$_SERVER['SCRIPT_NAME']),str_replace('www','',$_SERVER['HTTP_HOST']));
	}
	else
		die;

?>
<html>
<head>
<title>Please wait...</title>
</head>
<p align="center" style="font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;font-size:10pt;">Setting cookie, please wait...</p>
<script language="Javascript">
	function doRedirect() {
		document.location = '<?=ss_JSStringFormat($this->ATTRIBUTES['BackURL'])?>';
	}
	setTimeout('doRedirect()',2000);
</script>
</html>
