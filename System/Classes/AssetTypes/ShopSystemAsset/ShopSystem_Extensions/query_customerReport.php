<?
	$this->param('or_id');
	$this->param('customer', 0);

	if( $this->ATTRIBUTES['customer'] == 0 )		// default, computer and IP address report
	{

		echo "WHOIS<br />\n"; flush();
		$tr = getRow("select * from transactions join shopsystem_orders on or_tr_id = tr_id where or_id = ".safe($this->ATTRIBUTES['or_id']) );

		ss_log_message( "getting row for ".safe($this->ATTRIBUTES['or_id']) );

		if( strlen( $tr['tr_ip_address'] ) )
		{
			exec( "whois {$tr['tr_ip_address']}", $whois );
			$hostname = gethostbyaddr( $tr['tr_ip_address'] );
			$ipa = explode( '.', $tr['tr_ip_address'] );

			$cidr = "";
			$first = "";
			$last = "";
			foreach( $whois as $line )
			{
				$new = preg_replace( "/.*({$ipa[0]}+.\d+.\d+.\d+\/\d+)/", "$1", $line );
				if( $new != $line )		// matched
					$cidr = $new;
			}

			if( strlen( $cidr ) )
			{
				$ca = explode( "/", $cidr );
				$first = ip2long( $ca[0] );
				$last = $first + pow( 2, 32-$ca[1] ) - 1;
			}
			else
			{
				foreach( $whois as $line )
				{
					$new = preg_replace( "/.*\s(\d+.\d+.\d+.\d+)\s-\s(\d+.\d+.\d+.\d+)/", "$1-$2", $line );
					if( $new != $line )		// matched
					{
						$cidr = $new;
						$newa = explode( '-', $new );
						$first = ip2long( $newa[0] );
						$last = ip2long( $newa[1] );
					}
				}
			}



		}
		else
		{
			$first = '';
			$last = '';
			$tr['tr_ip_address'] = '999';
			$tr['tr_fingerprint'] = '9999';
			$whois = '';
			$hostname = '';
			$cidr = '';
		}

		echo ".<br />\n"; flush();
		echo "."; flush();
		if( ($txd = unserialize($tr['tr_payment_details_szln'])) !== FALSE )
			if( array_key_exists('TrCreditCardNumber', $txd ) && strlen($txd['TrCreditCardNumber']) )
				$cc = $txd['TrCreditCardNumber'];
			else
				$cc = "1234567890123456789";
		else
			$cc = "1234567890123456789";

		echo ".<br />\n"; flush();
		echo "."; flush();
		if( $txd )
			if( array_key_exists('TrCreditCardHolder', $txd ) && strlen($txd['TrCreditCardHolder']) )
				$ch = safe($txd['TrCreditCardHolder']);
			else
				$ch = "Englebert Humperdink";
		else
			$ch = "Englebert Humperdink";

		echo ".<br />\n"; flush();
		echo "."; flush();
		$Q_IPlike = query( "select transactions.*, shopsystem_orders.or_id, users.* from transactions join shopsystem_orders on or_tr_id = tr_id left join users on us_id = or_us_id where tr_ip_address = '{$tr['tr_ip_address']}' and tr_completed >= 1 order by tr_id desc limit 100" );
		if( $first != '' )
			$Q_BrowserLike = query( "select transactions.*, shopsystem_orders.or_id, users.* from transactions join shopsystem_orders on or_tr_id = tr_id left join users on us_id = or_us_id where INET_ATON(tr_ip_address) >= ".sprintf( "%u", $first)." and tr_completed >= 1 and INET_ATON(tr_ip_address) <= ".sprintf( "%u", $last)." and tr_fingerprint = '{$tr['tr_fingerprint']}' order by tr_id desc limit 100" );
		else
			$Q_BrowserLike = query( "select transactions.*, shopsystem_orders.or_id, users.* from transactions join shopsystem_orders on or_tr_id = tr_id left join users on us_id = or_us_id where tr_fingerprint = '{$tr['tr_fingerprint']}' and tr_completed >= 1 order by tr_id desc limit 100" );

		echo ".<br />\n"; flush();
		echo "."; flush();
		if( strlen( $ch ) )
			$Q_SameCardHolder = query( "select transactions.*, shopsystem_orders.or_id, users.* from transactions join shopsystem_orders on or_tr_id = tr_id left join users on us_id = or_us_id where tr_payment_details_szln like '%$ch%' and tr_completed >= 1 and 0=1 order by tr_id desc limit 100" );
		else
			$Q_SameCardHolder = new fakeQuery();

		echo ".<br />\n"; flush();
		echo "."; flush();
		$billingName = 'foofoo';
		$billingAddress = 'foofoo';
		$shippingName  = 'foofoo';
		$shippingAddress = 'foofoo';

		if ( strlen($tr['or_shipping_details']))
		{
			$sdetails = unserialize($tr['or_shipping_details']);
							
			ss_paramKey($sdetails['PurchaserDetails'],'0_50A1','');
			ss_paramKey($sdetails['ShippingDetails'],'0_50A1','');
						
			$billingName = escape(rtrim(ltrim($sdetails['PurchaserDetails']['Name'])));
			$billingAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
						
			$shippingName = escape(rtrim(ltrim($sdetails['ShippingDetails']['Name'])));
			$shippingAddress = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_50A1'])));
		}
		$blackListcheck = new Request('shopsystem_blacklist.CheckOrder', array( 'tr_id'	=>	$tr['tr_id']));
		$uniq = array();
		foreach( $blackListcheck->value as $match )
			if( !in_array( $match['bl_id'], $uniq ) )
				$uniq[] = $match['bl_id'];
		$Q_BlackList = NULL;
		if( count( $uniq ) )
			$Q_BlackList = query( "select transactions.*, shopsystem_orders.or_id from transactions join shopsystem_orders on or_tr_id = tr_id join blacklist on bl_us_id = or_us_id
					where bl_id in (".implode( ', ', $uniq ).")" );

/*
		$Q_BlackList = query( "select transactions.*, shopsystem_orders.or_id, users.* from transactions join shopsystem_orders on or_tr_id = tr_id left join users on us_id = or_us_id 
				where us_bl_id in (select BlLiID from shopsystem_blacklist where BlLiBillingName LIKE '%$billingName%'
											OR
											BlLiBillingAddress LIKE '%$billingAddress%'
											OR
											BlLiShippingName LIKE '%$shippingName%'
											OR
											BlLiShippingAddress LIKE '%$shippingAddress%'
			) and tr_completed >= 1 order by tr_id desc limit 100" );
*/

		echo ".<br />\n"; flush();
		echo "."; flush();
		$data = array( 'Q_IPlike' => $Q_IPlike, 'Q_BrowserLike' => $Q_BrowserLike, 'Q_SameCardHolder' => $Q_SameCardHolder, 'Q_BlackList' => $Q_BlackList, 'Transaction' => $tr, 'Whois' => $whois, 'Hostname' => $hostname, 'cidr' => $cidr, 'first' => sprintf( "%u", $first), 'last' => sprintf( "%u", $last) );

		echo ".<br />\n"; flush();
		echo "."; flush();
		$this->display->title = 'IP/Computer Report';
		$this->useTemplate("ComputerReport",$data);
		echo ".<br />\n"; flush();
	}
	else		// customer report
	{
		echo "."; flush();
		$tr = getRow("select * from transactions join shopsystem_orders on or_tr_id = tr_id where or_id = ".safe($this->ATTRIBUTES['or_id']) );

		$us_id = (int) $tr['or_us_id'];

		$Q_AllCompleteOrders = query( "select * from transactions join shopsystem_orders on or_tr_id = tr_id LEFT JOIN payment_gateways on tr_bank = pg_id where or_us_id = $us_id and tr_completed >= 1" );
		$Q_AllIncompleteOrders = query( "select * from transactions join shopsystem_orders on or_tr_id = tr_id LEFT JOIN payment_gateways on tr_bank = pg_id where or_us_id = $us_id and tr_completed < 1" );
		$User = getRow( "select * from users where us_id = $us_id" );
		$Q_Audit1 = query( "select * from audit left join users on au_userid = us_id where au_key = $us_id and au_table = 'users'" );
		$Q_Audit2 = query( "select * from audit left join users on au_userid = us_id where au_userid = $us_id" );

		$data = array( 'Q_AllCompleteOrders' => $Q_AllCompleteOrders, 'Q_AllIncompleteOrders' => $Q_AllIncompleteOrders, 'User' => $User, 'Q_Audit1' => $Q_Audit1, 'Q_Audit2' => $Q_Audit2 );
		$this->display->title = 'Customer Report';
		$this->useTemplate("CustomerReport",$data);
		echo ".<br />\n"; flush();
	}

?>
