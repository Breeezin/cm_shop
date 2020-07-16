<?php 

	$q = "select * from ce";

	$Q = query( $q );
	
	$fname = "ce.csv";
	$fullname = "/tmp/$fname";

	if( $Q->numRows() > 0 )
	{
		$fd = fopen( $fullname, "w+" );

		if( $fd )
		{
			while( $row = $Q->fetchRow( ) )
			{
				//$PrDescription = addslashes(strip_tags( $row['pr_long'] ));
				$pr_name = iconv( "ISO-8859-1", "UTF-8//TRANSLIT", $row['pr_name'] );
				$prd_pr_name = iconv( "ISO-8859-1", "UTF-8//TRANSLIT", $row['prd_pr_name'] );

				$search = array( "/\r\n/", "/\n/", "/<br \/>/", "/<.*?>/" );
				$replace = array(  " "    ,   " ",	" ",       ""        );

				$pr_long = preg_replace($search, $replace,  addslashes(strip_tags( $row['pr_long'] )));
				$prd_long = preg_replace($search, $replace, addslashes(strip_tags( $row['prd_long'] )));

				fwrite( $fd, $row['pr_id'].",".
					"\"$pr_name\",".
					"\"$prd_pr_name\",".
					"\"$pr_long\",".
					"\"$prd_long\"\n"
					);

			}

			fclose( $fd );
		}
	}

	die;

	$smtp_relay_host = "localhost";
	$smtp_relay_port = 25;
	$smtp_relay_from = "webserver@acmerockets.com";
	$smtp_relay_to = "rex@admin.com";
	$smtp_relay_subject = "Product Export";
	$smtp_relay_body = "See attached";


	require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
	$mailer = new htmlMimeMail();
	$mailer->setSMTPParams($smtp_relay_host, $smtp_relay_port, 'acmerockets.com' );
	$mailer->setFrom($smtp_relay_from);
	$mailer->setSubject($smtp_relay_subject);

	//$mailer->setText( $email_text );

	$mailer->setText( $smtp_relay_body );
	$mailer->addAttachment( $mailer->getFile($fullname), $fname );
	echo "Sending to $smtp_relay_to via $smtp_relay_host:$smtp_relay_port<br/>";
	//if( $mailer->send( array($smtp_relay_to), 'smtp' ) )
	if( $mailer->send( array($smtp_relay_to) ) )
		{
		echo "Done<br/>";
		}
	else
		{
		print_r( $mailer->errors );
		}
?>

