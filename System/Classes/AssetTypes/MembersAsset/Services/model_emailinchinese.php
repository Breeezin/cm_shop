<?php 
	$errors = array();
	if (array_key_exists('Do_Service',$this->ATTRIBUTES))
	{	
		ss_log_message( "chinese submitting issue" );

		$error = '';
		$text = $this->ATTRIBUTES['issue'];

		if( array_key_exists( 'email', $this->ATTRIBUTES ) )
		{
			$email =  trim(escape(strip_tags( $this->ATTRIBUTES['email'] ) ) );
			if( strlen( $text ) )
			{
				$email_body = '<html lang="en"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> </head><body> Entered email address:'.$email.' Request:'.$text.'</body></html>';

				ss_log_message( "from $email '$text'");
				new Request("Email.Send",array(
					'from'		=>	'acmecustomerservice@gmail.com',
					'to'		=>	array('acmecustomerservice@gmail.com','info@translationsbysilvia.com'),
					'subject'	=>	"AcmeRockets Chinese support request from $email",
					'html' 		=>	$email_body,
					'useTemplate'  => 0,
				));
				//echo "<html>Your issue has been saved and will be responded to with 24 hours.<br/><a href='/'>Click here to return home</a></html>";
				echo "<html><img src='/images/FormSubmittedInChinese.png' /></html>";
				die;
			}
			else
				ss_log_message( "No text supplied" );
		}
	}
?>
