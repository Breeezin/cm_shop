<?php 

	$this->param('OrderList');

	set_time_limit( 30000 );
	@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
	ob_implicit_flush(1);

	echo "<br />";

	$transactions = ListToArray( $this->ATTRIBUTES['OrderList'], "," );
	foreach( $transactions as $Transaction )
	{
		echo "Sending mass customer email to Order #".$Transaction."<br />";

		$Order = getRow("SELECT shopsystem_orders.*, transactions.tr_charge_total, transactions.tr_total FROM shopsystem_orders join transactions on tr_id = or_tr_id WHERE or_tr_id = {$Transaction}");
		$OrderDetails = unserialize($Order['or_basket']);

		$asset = getRow("
			SELECT * FROM assets
			WHERE as_id = ".safe($Order['or_as_id'])."
			");

		$ShopCereal = unserialize($asset['as_serialized']);

		if (is_array($Order['or_details']))
			$basket = $Order['or_details'];
		else
			$basket = unserialize($Order['or_details']);

		$PaidDate = '';
		if( strlen( $Order['or_paid']  ) )
			$PaidDate = strftime( "%e %B %Y", ss_SQLtoTimeStamp( $Order['or_paid'] ) );

		$ShippedDate = '';
		if( strlen( $Order['or_shipped']  ) )
			$ShippedDate = strftime( "%e %B %Y", ss_SQLtoTimeStamp( $Order['or_shipped'] ) );

		// html manipulation from Email.Send
		$emailData = array_merge( $Order, array(
			'first_name'	=>	$Order['or_purchaser_firstname'],
			'OrderID'	=>	$Order['or_tr_id'],
			'PaidDate'  =>  $PaidDate,
			'ShippedDate'  =>  $ShippedDate,
			'PlacedDate'  =>  strftime( "%e %B %Y", ss_SQLtoTimeStamp( $Order['or_recorded'] ) ),
			));

		$htmlMessage = processTemplate("Custom/ContentStore/Templates/acmerockets/Email/MassCustomEmail.html",$emailData);

		echo $htmlMessage;
		if( strlen( $htmlMessage ) )
		{
			echo "<br />";

			include_once( "System/Libraries/Rmail/Rmail.php" );

			$mailer = new Rmail();
			//$mailer->setFrom($ShopCereal['AST_SHOPSYSTEM_ADMINEMAIL']);
			$mailer->setFrom('noreply@acmerockets.com');
			$mailer->setSubject("order ".$Order['or_tr_id']." entirely shipped");				
			$mailer->setHTML($htmlMessage);				
			$mailer->setSMTPParams("localhost", 25);
			//$mailer->setSMTPParams("localhost", 587);
			//$mailer->setSMTPParams("smtp.admin.com", 25);
			if( array_key_exists('TEST', $_GET) )
			{
				echo "test<br/>";
				$result = $mailer->send(array('admin@acmerockets.com'), 'smtp');				
			}
			else
			{
				echo "{$Order['or_purchaser_email']}<br/>";
				$to = array($Order['or_purchaser_email']);
				$result = $mailer->send($to, 'smtp');				
			}

			echo "<br />";
			echo "<br />";

			// save order notes
			if( $result === true )
			{
				echo "Saving in notes<br/>";
				query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id, orn_show_packing) values ('Shipped Spam sent.', NOW(), {$Order['or_id']}, 0)" );
			}
			else
			{
				echo "Sending Failed<br/>";
			}
		}
		else
			echo "No message, process template failed";

		flush( );
		sleep( 1 );
	}

?>
