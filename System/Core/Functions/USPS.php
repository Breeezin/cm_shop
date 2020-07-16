<?php

function SomethingHasChanged( $indicator, $file, $line )
{
		ss_log_message( "USPS, $indicator @ $file:$line" );

//		$temp = new Request("Email.Send",
//			array(
//				'from'	=>	'errors@acmerockets.com',
//				'to'	=>	'im@admin.com',
//				'subject'	=>	'USPS failure',
//				'text'	=>	$indicator.'@'.$file.':'.$line,
//			));

		// remove this for live site
		// ss_DumpVarDie( $indicator." @ ".$file.":".$line );
		//echo "Sorry, i'm unable to connect to the USPS site to give you postage options";
		// die;
}

function file_get_contents_utf8($fn)
{
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			)
		);

	$context = stream_context_create($opts);

	return file_get_contents($fn, FILE_TEXT, $context );
}


function grab_first_chunk( $start_pattern, $end_pattern, $from )
	{
	$pos = strpos( $from, $start_pattern );
	if( !$pos )
		return false;

	$pos += strlen( $start_pattern );
	$endpos = strpos( substr($from, $pos), $end_pattern );
	if( !$endpos )
		return false;

	return substr( $from, $pos, $endpos );
	}

function grab_last_chunk( $start_pattern, $end_pattern, $from )
	{
	$pos = strrpos( $from, $start_pattern );
	if( !$pos )
		return false;

	$pos += strlen( $start_pattern );
	$endpos = strpos( substr($from, $pos), $end_pattern );
	if( !$endpos )
		return false;

	return substr( $from, $pos, $endpos );
	}

function getAccessoryShippingCost()
{

			ini_set("user_agent", "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; GTB6; .NET CLR 1.1.4322; .NET CLR 2.0.50727)" );

/*
			$dest = unserialize( $order_details['or_shipping_details']);

			$zip = $dest['ShippingDetails']['0_B4C0'];
			$state_country = $dest['ShippingDetails']['0_50A4'];
			$pos = strpos( $state_country, "<BR>" );
			if( $pos )
			{
				$state = substr( $state_country, 0, $pos );
				$country = substr( $state_country, $pos + 4 );
			}
			else
			{
				$state = $state_country;
				$country = $state_country;
			}
*/

//	return 0;
	$total = 0;
	$origin = "33029";			// zip code of sender
	$furthest = "98326";
	$mailing_date = strftime( "%m/%d/%Y", time()+24*60*60 );

	ini_set("user_agent", "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)" );

	$products = $_SESSION['Shop']['Basket']['Products'];
	for	($index=0;$index<count($products);$index++)
	{
		$entry = $products[$index];

		if ($entry['Qty'] > 0)
		{
			$prod = getRow( "select * from shopsystem_products JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id where pr_id = ".$entry['Product']['pr_id'] );

			if( ($prod['pr_ve_id'] == 1) &&  ($prod['pr_quote_shipping'] != 1) )		// accessory that weighs something
			{
				$weight = (int)($prod['pro_weight']/454);		// now in grammes

				$o = 0;
				if( $weight == 0 )	// actually weight is about 1/3
					$o = 5;

				$rw = (double) $weight + (double)$o / (double)16;
				$rw *= $entry['Qty'];

				$weight = (int) $rw;
				$o = ($rw - $weight) * 16;


				$length = (int)($prod['pr_shipping_d1']/25.4+0.5);
				$width = (int)($prod['pr_shipping_d2']/25.4+0.5);
				$height = (int)($prod['pr_shipping_d3']/25.4+0.5);

				if( $length <= 1 )
					$length = 1;

				if( $width <= 1 )
					$width = 1;

				if( $height <= 1 )
					$height = 1;

				if( $_SESSION['ForceCountry']['cn_usps_ident'] == "Domestic" )
				{
					$addition = 0;
					if( strstr( $prod['pr_shipping_usps'], "Large" ) )
						$guess = 18.95;
					else
						if( strstr( $prod['pr_shipping_usps'], "Small" ) )
							$guess = 4.95;
						else
							$guess = 4.95 + $weight * 1.8;

/*
					$URL = "http://postcalc.usps.gov/MailServices.aspx?";
					// http://postcalc.usps.gov/MailServices.aspx?Country=Domestic&M=2&P=3&O=0&OZ=90210&DZ=75432&MailingDate=2/23/2009&MailingTime=17:00";
					// http://postcalc.usps.gov/MailServices.aspx?M=3&P=58&O=0&OZ=33029&DZ=98326&RECT=True&L=15&H=10&W=8&G=0&MailingDate=6/23/2009&MailingTime=19:00&Restrict=False
					$URL .= "m=6&p=".$weight."&o=$o&oz=".$origin."&dz=".$furthest."&rect=True&l=".$length."&h=".$height."&w=".$width."&g=0&MailingDate=".$mailing_date."&MailingTime=17:00";


					// ss_DumpVarDie( $prod );
					// looking for $prod['PrUSPSSizeCategory']
					// ss_DumpVarDie( $URL );
					$foo = file_get_contents( $URL );

					$ss = $prod['pr_shipping_usps'];
					if( $pos = strpos( $ss, '-' ) )
					{
						$ss = substr( $ss, 0, $pos );
					}

					//if( $line = grab_first_chunk( "<span>".$ss, "</tr>", $foo ) )
					if( $line = grab_first_chunk( $ss, "</tr>", $foo ) )
					{
///							if( $chunk = grab_first_chunk( "<td class=\"itemCenterDots\" align=\"right\"", "/td>", $line ) )	
						if( $cost = grab_first_chunk( "LabelPrice\">$", "<", $line ) )
						{
							if( $cost > 0 )			// this USD.
								$addition = $cost * ss_getExchangeRate($prod['pro_source_currency'], "USD");
						}
						else
							SomethingHasChanged( "Find LabelPrice failed for product ".$prod['pr_id']." in results from '$URL'", __FILE__, __LINE__ );
					}
					else
						SomethingHasChanged( "Find $ss failed for product ".$prod['pr_id']." in results from '$URL'", __FILE__, __LINE__ );
					if( $addition == 0 )
					*/
						$addition = $guess;

					$_SESSION['Shop']['Basket']['Products'][$index]['ExtraShipping'] = $addition * ss_getExchangeRate( "USD", getDefaultCurrencyCode( ) );

					$total += $addition;

				}
				else		// international
				{
					$URL = "http://ircalc.usps.gov/MailServicesPF.aspx?"; // "Country=Domestic&M=2&P=3&O=0&OZ=90210&DZ=75432&MailingDate=2/23/2009&MailingTime=17:00";
					$URL .= "country=".$_SESSION['ForceCountry']['cn_usps_ident']."&M=6&P=".$weight."&O=$o&sd=1";

					//ss_DumpVarDie( $URL );
					
					$foo = file_get_contents( $URL );

					// if( $line = grab_first_chunk( ">".$prod['pr_shipping_usps'], "</tr>", $foo ) )
					//if( $line = grab_first_chunk( ">Priority Mail", "</tr>", $foo ) )
					//if( $line = grab_first_chunk( "6 - 10 business days</span>", "GridViewItemRight", $foo ) )
					$addition = 999;
					if( $line = grab_first_chunk( "Priority Mail Express International™</div>", "</tr>", $foo ) )
					{
						if( $pos = strrpos( $line, '$' ) )
						{
							$cost = substr( $line, $pos + 1 );
							if( $cost > 0 )			// this USD.
								$addition = $cost * ss_getExchangeRate($prod['pro_source_currency'], "USD");
							else
								SomethingHasChanged( "cost is '$cost' ", __FILE__, __LINE__ );
						}
						else
							SomethingHasChanged( "no price found in '$line' from $URL ", __FILE__, __LINE__ );
					}
					else
						SomethingHasChanged( "Option 'Priority Mail Express International' not found in $URL ", __FILE__, __LINE__ );

					$_SESSION['Shop']['Basket']['Products'][$index]['ExtraShipping'] = $addition * ss_getExchangeRate( "USD", getDefaultCurrencyCode( ) );

					$total += $addition;
				}
			}
		}
	}

	return $total * ss_getExchangeRate( "USD", getDefaultCurrencyCode( ) );
}

?>
