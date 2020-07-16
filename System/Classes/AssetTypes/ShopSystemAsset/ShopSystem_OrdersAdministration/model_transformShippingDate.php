<?php 

	$this->param('OrderList');
	$this->param('To');

	echo "<br>";
	// Make a date object for date shipped
	$or_shipped = new DateField(array(
		'name'			=>	'To',
		'displayName'	=>	'Shipping Date',
		'note'			=>	null,			
		'required'		=>	false,
		'class'			=>	'formborder',
		'verify'		=>	false,
		'unique'		=>	false,
		'defaultValue'	=>	'Now',
		'showCalendar'	=> 	false,
		'size'	=>	'8',	'maxLength'	=>	'10',
		'rows'	=>	'6',	'cols'		=>	'40',			
	));	

	$ok = false;
	if (array_key_exists('To',$this->ATTRIBUTES))
	{
		$or_shipped->value = $this->ATTRIBUTES['To'];
		$or_shipped->processFormInputValues(null);
		$errors = $or_shipped->validate();
		if( $errors === null )
			$ok = true;
	} 

	if( !$ok )
	{
		echo "Invalid shipping date<br>";
		exit;
	}

	$transactions = ListToArray( $this->ATTRIBUTES['OrderList'], "," );
	foreach( $transactions as $Transaction )
	{
		echo "Altering Shipping Date on Order #".$Transaction." to ".$this->ATTRIBUTES['To']."<br>";

		$Q_Update = query("
			UPDATE shopsystem_orders
			SET or_shipped = ".$or_shipped->valueSQL().",
			or_paid_not_shipped = NULL
			WHERE or_tr_id = {$Transaction}");

		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_tr_id = {$Transaction}");
		$OrderDetails = unserialize($Q_Order['or_basket']);

		if (is_array($Q_Order['or_details']))
			$basket = $Q_Order['or_details'];
		else
			$basket = unserialize($Q_Order['or_details']);

		foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
		{

			if( strlen( $this->ATTRIBUTES['To'] ) > 0 )
			{
				// Send an email to notify the customer..
				$product = getRow("
					SELECT pr_name FROM shopsystem_products
					WHERE pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']."
				");
				$emailData = array(
					'first_name'	=>	$Q_Order['or_purchaser_firstname'],
					'Box'	=>	$product['pr_name'],
					'OrderID'	=>	$Q_Order['or_tr_id'],
				);
				
				$emailText = $this->processTemplate('AcmeShippingEmail',$emailData);

				if (file_exists(expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}"
							.'ShopSystemAsset/sty_invoice.css')))
					$stylesheet = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}"
							.'ShopSystemAsset/sty_invoice.css';
				else
					$stylesheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';

				print "Sending shipping confirmation email to ".$Q_Order['or_purchaser_email']."<br>";

				$emailResult = new Request('Email.Send',array(
					'from'	=>	$GLOBALS['cfg']['EmailAddress'],
					'to'	=>	$Q_Order['or_purchaser_email'],
					'subject'	=>	"Re: Your order at {$GLOBALS['cfg']['website_name']}",
					'html'	=>	$emailText,
					'css'	=>	$stylesheet,
					'templateFolder'	=>	$Q_Order['or_site_folder'],
					));
			}

			// take care of the visibility bits in or_basket
			for ($qty=0; $qty < $entry['Qty']; $qty++)
			{
				echo "Altering Shipping Date for entry ".($id+1)." box ".($qty+1)."<br>";
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Shipped',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());

				$OrderDetails['Basket']['Products'][$id]['Shipped'][$qty] = $this->ATTRIBUTES['To'];
				$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = "instock";
			}
		}
		// Serialize back into the order
		$OrderDetailsSerialized = serialize($OrderDetails);

		$Q_UpdateOrder = query("UPDATE shopsystem_orders 
					SET or_basket = '".escape($OrderDetailsSerialized)."'
					WHERE or_tr_id = {$Transaction}
					");
	}

?>
