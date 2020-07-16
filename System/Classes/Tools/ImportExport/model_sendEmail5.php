<?php 

	$q = "select pro_stock_code, ca_name, pt_name, pr_name, pr_long, pr_customer_rating, pro_price + ifnull(Rate,0) as price, pr_length, pr_thickness, pr_image1_normal 
			from shopsystem_products
			 left join product_type on pr_type = pt_id 
			 join shopsystem_categories on pr_ca_id = ca_id
			 join shopsystem_product_extended_options on pro_pr_id = pr_id
			 LEFT JOIN ShopSystem_FreightRates on FreightCodeLink = PrExOpFreightCodeLink
				where pr_ve_id = 2 and pr_offline IS NULL";

	$Q = query( $q );
	
	$fname = "product_export.csv";
	$fname2 = "tar_pics.sh";
	$fullname = "/tmp/$fname";
	$fullname2 = "/tmp/$fname2";

	if( $Q->numRows() > 0 )
	{
		$fd = fopen( $fullname, "w+" );
		$fd2 = fopen( $fullname2, "w+" );

		if( $fd && $fd2 )
		{
			fwrite( $fd2, "cd /var/www/acmerockets/Custom/ContentStore/Assets/5/14/ProductImages/\ntar cf /tmp/pics.tar " );

			while( $row = $Q->fetchRow( ) )
			{
				//$PrDescription = addslashes(strip_tags( $row['pr_long'] ));
				$pr_name = iconv( "ISO-8859-1", "UTF-8//TRANSLIT", $row['pr_name'] );
				$ca_name = iconv( "ISO-8859-1", "UTF-8//TRANSLIT", $row['ca_name'] );
				$pt_name = iconv( "ISO-8859-1", "UTF-8//TRANSLIT", $row['pt_name'] );

				$search = array( "/\r\n/", "/\n/", "/<br \/>/", "/<.*?>/",  );
				$replace = array(  " "    ,   " ",	" ",       "",       );

				$PrDescription = preg_replace($search, $replace, iconv( "ISO-8859-1", "UTF-8//TRANSLIT", addslashes(strip_tags( html_entity_decode($row['pr_long']) ))));

				$linch = $row['pr_length'] / 25.4;
				$gauge = (int) ($row['pr_thickness'] * 64 / 25.4 );
				$pr_length = ss_DecimalToFraction( $linch, 0.1 );
				$PrWidth = $gauge;

				fwrite( $fd,
					"\"".$row['pro_stock_code']."\",".
					"\"$ca_name\",".
					"\"$pt_name\",".
					"\"$pr_name\",".
					"\"$PrDescription\",".
					$row['pr_customer_rating'].",".
					"$".$row['price'].",".
					$pr_length.",".
					$PrWidth.",".
					"\"".$row['pr_image1_normal']."\"\n" );

				fwrite( $fd2, $row['pr_image1_normal']." " );

			}

			fclose( $fd );
			fclose( $fd2 );
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

