<?php

function SomethingHasChanged( $indicator, $file, $line )
{
		$temp = new Request("Email.Send",
			array(
				'from'	=>	'errors@acmerockets.com',
				'to'	=>	'im@admin.com',
				'subject'	=>	'USPS failure',
				'text'	=>	$indicator.'@'.$file.':'.$line,
			));

		// remove this for live site
		//ss_DumpVarDie( $indicator." @ ".$file.":".$line );
		echo "Sorry, i'm unable to connect to the USPS site to give you postage options";
		die;
}

	$origin = "33029";			// zip code of sender

	$this->display->title = 'Choose Shipping';
	$this->param("BackURL");
	$this->param("tr_id");
	$this->param("tr_token");
	$this->param("NextAction");
	$this->param("AccessCode");
	$this->param("us_id");
	$this->param("as_id");

	$weight = 0;
	for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
	{
		$entry = $_SESSION['Shop']['Basket']['Products'][$index];

		if ($entry['Qty'] > 0)
		{
			if( ($entry['Product']['pr_ve_id'] == 1) && array_key_exists( 'pro_weight', $entry['Product']) &&  ($entry['Product']['pro_weight'] > 0) )		// accessory that weighs something
				$weight += $entry['Product']['pro_weight'];
		}
	}

	$weight = (int) ($weight/454);

	if( $order_details = getRow( "select * from shopsystem_orders, transactions where or_tr_id = tr_id and or_tr_id = ".safe( $this->ATTRIBUTES['tr_id'] )." and tr_token = '".safe( $this->ATTRIBUTES['tr_token'] )."'" ) )
	{
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

		$cRow = getRow( "select * from countries where cn_name = '$country'" );
		if( $cRow )
		{
			$mailing_date = strftime( "%m/%d/%Y", time()+24*60*60 );

			if( $cRow['cn_usps_ident'] == "Domestic" )
			{
				if( 0 )
				{
					$URL = "http://postcalc.usps.gov/MailServices.aspx?"; // "Country=Domestic&M=2&P=3&O=0&OZ=90210&DZ=75432&MailingDate=2/23/2009&MailingTime=17:00";
					$URL .= "Country=Domestic&M=2&P=".$weight."&O=0&OZ=".$origin."&DZ=".$zip."&MailingDate=".$mailing_date."&MailingTime=17:00";

					$foo = file_get_contents( $URL );

		//			ss_DumpVarDie( $URL );

					$pos = strpos( $foo, "<table class=\"dataGridDots\"" );

					if( !$pos )
					{
						// error report to me
						SomethingHasChanged( "No table start", __FILE__, __LINE__ );
					}

					$foo = substr( $foo, $pos );

					$pos = strpos( $foo, "</table" );

					if( !$pos )
					{
						SomethingHasChanged( "No table end", __FILE__, __LINE__ );
					}

					$foo = substr( $foo, 0, $pos-1 )."</table>";

					$pattern = '/<a.*?\/a>/is';
					$replacement = "";
					$foo = preg_replace( $pattern, $replacement, $foo );

					$pattern = '/<th.*?\/th>/is';
					$replacement = "";
					$foo = preg_replace( $pattern, $replacement, $foo );

					$pattern = '/width:[0-9]*px;/is';
					$replacement = "";
					$foo = preg_replace( $pattern, $replacement, $foo );

					$pattern = '/width="[0-9]*"/is';
					$replacement = "";
					$foo = preg_replace( $pattern, $replacement, $foo );

					if( array_key_exists( 'MailServices', $_POST ) )
					{
						//saving
						$method = trim(safe( $_POST['MailServices'] ));
						$noteStr = "USPS chosen method ($method) ";

						// look for the string <input name="MailServices" type="radio" value='$method' as

						$pos = strpos( $foo, "<input name=\"MailServices\" type=\"radio\" value='$method'" );

						if( $pos )
						{
							$foo = substr( $foo, $pos );		// chop off earlier stuff
							// grab the </td><td class="itemCenterDots" align="left" >Friday, February 27 by 12 p.m</td><td class="itemCenterDots" align="right" >$58.40</td></tr><tr>


							$pattern = '/(.*?)<\/td>(.*)<\/tr>/is';
							$replacement = '${1}';		// == <input name="MailServices" type="radio" value='1' ></input>Priority Mail<sup> etc etc etc
							$noteStr .= html_entity_decode(trim(preg_replace( "/<.*?>/is", "", preg_replace( $pattern, $replacement, $foo ))));

							
							$pattern = '/(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*)/is';
							$replacement = '${7}';

	//						$pattern = '/(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<\/tr>/is';
	//						$replacement = '${8}';
							$foo = preg_replace( $pattern, $replacement, $foo );

							// remove rubbish
							$foo = trim( $foo );
							while( ($foo[0] < '0' or $foo[0] > '9') and strlen( $foo ) > 0 )
								$foo = substr( $foo, 1 );

							$fv = -1;
							if( strlen( $foo ) > 0 )
								$fv = floatval( $foo );

							if( $fv > 0 )
							{
								$noteStr .= " cost USD".$fv." ";

								// this is USD, convert it to EURO.
								$fv = ss_decimalFormat( $fv * ss_getExchangeRate("USD", "EUR") );

								$noteStr .= " cost EUR".$fv." ";

								// update order details with this additional charge
								// this needs to be made reversable.
								// this is no good.... query( "Update transactions set tr_total = tr_total + $fv where tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
								$OrderDetails = getRow( "Select * from shopsystem_orders where or_tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
								$basketS = $OrderDetails['or_basket'];
								if( strlen( $basketS ) )
									if( $basket = unserialize( $basketS ) )
									{
										if( array_key_exists( 'Basket', $basket )
										 && array_key_exists( 'SubTotal', $basket['Basket'] ) )
										{
											$subtotal = $basket['Basket']['SubTotal'];
											$total = ss_decimalFormat($subtotal + $fv);

											$basket['Basket']['Total'] = $total;
											
											$newBasket = escape(serialize( $basket ));

											query( "Update shopsystem_orders set or_basket = \"$newBasket\" where or_tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
											query( "Update transactions set tr_total = $total, tr_charge_total = '&euro;$total EUR' where tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
										}
									 
									}
							}
							else
							{
								SomethingHasChanged( "No MailServices value", __FILE__, __LINE__ );
							}
						}
						else
						{
							SomethingHasChanged( "No MailServices radio", __FILE__, __LINE__ );
						}

						$noteStr = escape( $noteStr );
						query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$noteStr', NOW(), {$order_details['or_id']} )" );

						$secureSite = $GLOBALS['cfg']['secure_server'];
						$secureSite = ss_withTrailingSlash($secureSite);
						$order="";
						if( array_key_exists( 'or_id', $this->ATTRIBUTES) && strlen( $this->ATTRIBUTES['or_id']  ) )
							$order = '&or_id='.$this->ATTRIBUTES['or_id'];

						location($secureSite."index.php?act={$this->ATTRIBUTES['NextAction']}$order&AccessCode={$this->ATTRIBUTES['AccessCode']}&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&us_id={$this->ATTRIBUTES['us_id']}&BackURL={$this->ATTRIBUTES['BackURL']}&Type=Shop&as_id={$this->ATTRIBUTES['as_id']}");
					}
					else		// displaying, not saving
					{
						$pattern = '/<tr(.*?)>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<\/tr>/is';
						$replacement = '<tr${1}>${2}<td${3}>${4}</td>${5}<td${6}>${7}</td>${8}<td${9}>${10}</td></tr>';			// only first 3 columns of table keep 
						$foo = preg_replace( $pattern, $replacement, $foo );

						$data = array( 
							'weight' => $weight,
							'zip_code' => $zip,
							'country' => $country,
							'BackURL' => $this->ATTRIBUTES['BackURL'],
							'ThisURL' => $_SERVER['REQUEST_URI'],
							'foo' => $foo );

						$this->useTemplate("ChooseShipping", $data);
					}
				}
				else
				{
					$secureSite = $GLOBALS['cfg']['secure_server'];
					$secureSite = ss_withTrailingSlash($secureSite);
					$order="";
					if( array_key_exists( 'or_id', $this->ATTRIBUTES) && strlen( $this->ATTRIBUTES['or_id']  ) )
						$order = '&or_id='.$this->ATTRIBUTES['or_id'];
					$data = array( 
						'weight' => $weight,
						'zip_code' => $zip,
						'BackURL' => $secureSite."index.php?act={$this->ATTRIBUTES['NextAction']}$order&AccessCode={$this->ATTRIBUTES['AccessCode']}&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&us_id={$this->ATTRIBUTES['us_id']}&BackURL={$this->ATTRIBUTES['BackURL']}&Type=Shop&as_id={$this->ATTRIBUTES['as_id']}",
						);

					$this->useTemplate("UPSShipping", $data);

				}
			}
			else		// international
			{
				$URL = "http://ircalc.usps.gov/MailServices.aspx?"; // "Country=Domestic&M=2&P=3&O=0&OZ=90210&DZ=75432&MailingDate=2/23/2009&MailingTime=17:00";
				$URL .= "Country=".$cRow['cn_usps_ident']."&M=2&P=".$weight."&O=0&sd=1";

				$foo = file_get_contents( $URL );

	//			ss_DumpVarDie( $URL );

				$pos = strpos( $foo, "<table class=\"dataGridDots\"" );

				if( !$pos )
				{
					// error report to me
				}

				$foo = substr( $foo, $pos );

				$pos = strpos( $foo, "</table" );

				if( !$pos )
				{
					// error report to me
				}

				$foo = substr( $foo, 0, $pos-1 )."</table>";

				$pattern = '/<a.*?\/a>/is';
				$replacement = "";
				$foo = preg_replace( $pattern, $replacement, $foo );

				$pattern = '/<input type="image".*?\/>/is';
				$replacement = "";
				$foo = preg_replace( $pattern, $replacement, $foo );

				$pattern = '/<th.*?\/th>/is';
				$replacement = "";
				$foo = preg_replace( $pattern, $replacement, $foo );

				$pattern = '/width:[0-9]*px;/is';
				$replacement = "";
				$foo = preg_replace( $pattern, $replacement, $foo );

				$pattern = '/width="[0-9]*"/is';
				$replacement = "";
				$foo = preg_replace( $pattern, $replacement, $foo );

				if( array_key_exists( 'speed', $_POST ) )
				{
					//saving
					$method = trim(safe( $_POST['speed'] ));
					$noteStr = "USPS chosen method ($method) ";

					$pos = strpos( $foo, "name=\"speed\" type=\"radio\" value=\"$method\"" );

					if( $pos )
					{
						$foo = substr( $foo, $pos );		// chop off earlier stuff

						$pattern = '/.*?\/>(.*?)<\/td>(.*)<\/tr>/is';
						$replacement = '${1}';
						$noteStr .= html_entity_decode(trim(preg_replace( "/<.*?>/is", "", preg_replace( $pattern, $replacement, $foo ))));


						$pattern = '/(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*)/is';
						$replacement = '${7}';
						$foo = preg_replace( $pattern, $replacement, $foo );


						// remove rubbish
						$foo = trim( $foo );
						if( strlen( $foo ) > 0 )
							while( ($foo[0] < '0' or $foo[0] > '9') and strlen( $foo ) > 0 )
								$foo = substr( $foo, 1 );


						$fv = -1;
						if( strlen( $foo ) > 0 )
							$fv = floatval( $foo );

						if( $fv > 0 )
						{
							$noteStr .= " cost USD".$fv." ";

							// this is USD, convert it to EURO.
							$fv = ss_decimalFormat( $fv * ss_getExchangeRate("USD", "EUR") );

							$noteStr .= " cost EUR".$fv." ";

							// update order details with this additional charge
							// this needs to be made reversable.
							// this is no good.... query( "Update transactions set tr_total = tr_total + $fv where tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
							$OrderDetails = getRow( "Select * from shopsystem_orders where or_tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
							$basketS = $OrderDetails['or_basket'];
							if( strlen( $basketS ) )
								if( $basket = unserialize( $basketS ) )
								{
									if( array_key_exists( 'Basket', $basket )
									 && array_key_exists( 'SubTotal', $basket['Basket'] ) )
									{
										$subtotal = $basket['Basket']['SubTotal'];
										$total = ss_decimalFormat($subtotal + $fv);

										$basket['Basket']['Total'] = $total;
										
										$newBasket = escape(serialize( $basket ));

										query( "Update shopsystem_orders set or_basket = \"$newBasket\" where or_tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
										query( "Update transactions set tr_total = $total, tr_charge_total = '&euro;$total EUR' where tr_id = ".safe( $this->ATTRIBUTES['tr_id'] ) );
									}

								}
						}
						else
						{
							SomethingHasChanged( "No speed value", __FILE__, __LINE__ );
						}
					}
					else
					{
						SomethingHasChanged( "No speed radio", __FILE__, __LINE__ );
					}

					$noteStr = escape( $noteStr );
					query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$noteStr', NOW(), {$order_details['or_id']} )" );

					$secureSite = $GLOBALS['cfg']['secure_server'];
					$secureSite = ss_withTrailingSlash($secureSite);
					$order="";
					if( array_key_exists( 'or_id', $this->ATTRIBUTES) && strlen( $this->ATTRIBUTES['or_id']  ) )
						$order = '&or_id='.$this->ATTRIBUTES['or_id'];

					location($secureSite."index.php?act={$this->ATTRIBUTES['NextAction']}$order&AccessCode={$this->ATTRIBUTES['AccessCode']}&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&us_id={$this->ATTRIBUTES['us_id']}&BackURL={$this->ATTRIBUTES['BackURL']}&Type=Shop&as_id={$this->ATTRIBUTES['as_id']}");
				}
				else		// displaying, not saving
				{
					$pattern = '/<tr(.*?)>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<td(.*?)>(.*?)<\/td>(.*?)<\/tr>/is';
					$replacement = '<tr${1}>${2}<td${3}>${4}</td>${5}<td${6}>${7}</td>${8}<td${9}>${10}</td></tr>';			// only first 3 columns of table keep 
					$foo = iconv('utf-8','iso-8859-1',preg_replace( $pattern, $replacement, $foo ));

					$data = array( 
						'weight' => $weight,
						'zip_code' => $zip,
						'country' => $country,
						'BackURL' => $this->ATTRIBUTES['BackURL'],
						'ThisURL' => $_SERVER['REQUEST_URI'],
						'foo' => $foo );

					$this->useTemplate("ChooseShipping", $data);
				}
			}
		}
	}

?>
