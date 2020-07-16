<?php
	if( !count( $_POST ) )
	{
?>
<html>
<head><base href="/"/><meta http-equiv="Pragma" CONTENT="no-cache"/><meta http-equiv="Cache-Control" CONTENT="no-cache"/><meta http-equiv="Expires" CONTENT="Mon, 06 Jan 1990 00:00:01 GMT"/>
	<title>Administration : Add to Blacklist :</title>
	<link rel="stylesheet" href="System/Classes/MDI/sty_AdministrationUpgraded.css" type="text/css">
</head>
<body>
<?php
if( array_key_exists( 'or_id', $_GET ) && strlen( $_GET['or_id'] ) )
{
	$or_id = (int) $_GET['or_id'];
	$Order = getRow("SELECT or_id, or_us_id, or_shipping_details, us_email FROM shopsystem_orders join users on us_id = or_us_id
									WHERE or_id = $or_id");

	if (strlen($Order['or_shipping_details']))
	{
		$details = unserialize($Order['or_shipping_details']);

		$billing_name = escape(trim($details['PurchaserDetails']['Name']));
		$billing_email = escape(trim(strip_tags($details['PurchaserDetails']['Email'])));
		$billing_company = escape(trim($details['PurchaserDetails']['0_B4BF']));
		$billing_address1 = escape(trim($details['PurchaserDetails']['0_50A1']));
		$billing_city = escape(trim($details['PurchaserDetails']['0_50A2']));
		$billing_zip = escape(trim($details['PurchaserDetails']['0_B4C0']));
		$billing_phone = escape(trim($details['PurchaserDetails']['0_B4C1']));
		$billing_country_state = escape(trim($details['PurchaserDetails']['0_50A4']));

		if( $pos = strrpos( $billing_country_state, '>' ) )
			$bcname = substr( $billing_country_state, ++$pos );

		if( $pos = strrpos( $billing_country_state, '<' ) )
		{
			$ccode = substr( $billing_country_state, 0, $pos );
			$billing_state = getField( "select StName from country_states where StCode = '$ccode'" );
		}

		ss_log_message( "billing_country_state:$billing_country_state billing_country:$billing_country billing_state:$billing_state" );

		$shipping_name = escape(trim($details['ShippingDetails']['Name']));
		$shipping_email = escape(trim(strip_tags($details['ShippingDetails']['Email'])));
		$shipping_company = escape(trim($details['ShippingDetails']['0_B4BF']));
		$shipping_address1 = escape(trim($details['ShippingDetails']['0_50A1']));
		$shipping_city = escape(trim($details['ShippingDetails']['0_50A2']));
		$shipping_zip = escape(trim($details['ShippingDetails']['0_B4C0']));
		$shipping_phone = escape(trim($details['ShippingDetails']['0_B4C1']));
		$shipping_country_state = escape(trim($details['ShippingDetails']['0_50A4']));

		if( $pos = strrpos( $shipping_country_state, '>' ) )
			$scname = substr( $shipping_country_state, ++$pos );

		if( $pos = strrpos( $shipping_country_state, '<' ) )
		{
			$ccode = substr( $shipping_country_state, 0, $pos );
			$shipping_state = getField( "select StName from country_states where StCode = '$ccode'" );
		}

		echo "<table>";
		echo "<tr><td>billing_name</td><td>$billing_name</td></tr>";
		echo "<tr><td>billing_email</td><td>$billing_email</td></tr>";
		echo "<tr><td>billing_company</td><td>$billing_company</td></tr>";
		echo "<tr><td>billing_address1</td><td>$billing_address1</td></tr>";
		echo "<tr><td>billing_city</td><td>$billing_city</td></tr>";
		echo "<tr><td>billing_zip</td><td>$billing_zip</td></tr>";
		echo "<tr><td>billing_phone</td><td>$billing_phone</td></tr>";
		echo "<tr><td>billing_state</td><td>$billing_state</td></tr>";
		echo "<tr><td>billing_country</td><td>$bcname</td></tr>";

		echo "<tr><td>shipping_name</td><td>$shipping_name</td></tr>";
		echo "<tr><td>shipping_email</td><td>$shipping_email</td></tr>";
		echo "<tr><td>shipping_company</td><td>$shipping_company</td></tr>";
		echo "<tr><td>shipping_address1</td><td>$shipping_address1</td></tr>";
		echo "<tr><td>shipping_city</td><td>$shipping_city</td></tr>";
		echo "<tr><td>shipping_zip</td><td>$shipping_zip</td></tr>";
		echo "<tr><td>shipping_phone</td><td>$shipping_phone</td></tr>";
		echo "<tr><td>shipping_state</td><td>$shipping_state</td></tr>";
		echo "<tr><td>shipping_country</td><td>$scname</td></tr>";
		echo "</table><br /><br />";
	}
}
if( array_key_exists( 'bl_idstring', $_GET ) && strlen( $_GET['bl_idstring'] ) )
{
	$Qidents = query( "select * from blacklist where bl_id in (".escape( $_GET['bl_idstring'] ).")" );
	while( $brow = $Qidents->fetchRow() )
	{
		echo "Blacklist Ident ".$brow['bl_id']."<br />";
		echo "<table>";

		echo "<tr><td>User ID</td><td>{$brow['bl_us_id']}</td></tr>";
		echo "<tr><td>billing_name</td><td>{$brow['bl_billing_name']}</td></tr>";
		echo "<tr><td>billing_email</td><td>{$brow['bl_billing_email_address']}</td></tr>";
		echo "<tr><td>billing_company</td><td>{$brow['bl_billing_company']}</td></tr>";
		echo "<tr><td>billing_address1</td><td>{$brow['bl_billing_address1']}</td></tr>";
		echo "<tr><td>billing_city</td><td>{$brow['bl_billing_address_city']}</td></tr>";
		echo "<tr><td>billing_zip</td><td>{$brow['bl_billing_address_zip']}</td></tr>";
		echo "<tr><td>billing_phone</td><td>{$brow['bl_billing_address_phone']}</td></tr>";
		echo "<tr><td>billing_state</td><td>{$brow['bl_billing_address_state']}</td></tr>";
		$bcname = getField( "select cn_name from countries where cn_id = ".((int)$brow['bl_billing_address_country']) );
		echo "<tr><td>billing_country</td><td>$bcname</td></tr>";

		echo "<tr><td>shipping_name</td><td>{$brow['bl_shipping_name']}</td></tr>";
		echo "<tr><td>shipping_email</td><td>{$brow['bl_shipping_email_address']}</td></tr>";
		echo "<tr><td>shipping_company</td><td>{$brow['bl_shipping_company']}</td></tr>";
		echo "<tr><td>shipping_address1</td><td>{$brow['bl_shipping_address1']}</td></tr>";
		echo "<tr><td>shipping_city</td><td>{$brow['bl_shipping_address_city']}</td></tr>";
		echo "<tr><td>shipping_zip</td><td>{$brow['bl_shipping_address_zip']}</td></tr>";
		echo "<tr><td>shipping_phone</td><td>{$brow['bl_shipping_address_phone']}</td></tr>";
		echo "<tr><td>shipping_state</td><td>{$brow['bl_shipping_address_state']}</td></tr>";
		$scname = getField( "select cn_name from countries where cn_id = ".((int)$brow['bl_shipping_address_country']) );
		echo "<tr><td>shipping_country</td><td>$scname</td></tr>";
		echo "<tr><td>notes</td><td>{$brow['bl_notes']}</td></tr>";

		echo "</table><br /><br />";
		
	}
}


?>
<form method="post" action="<?php echo $GLOBALS['cfg']['FullURI'];?>">
<?php
		$displayHTML = "Select a reason:<SELECT NAME=\"Reason\">";
		$result=query("SHOW COLUMNS FROM blacklist LIKE 'bl_reason'");
		if($row = $result->fetchRow())
		{
			$options=explode("','", preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $row['Type']));
			foreach ($options as $enum)
				$displayHTML .= "<OPTION VALUE=\"'$enum'\">$enum</OPTION>";
		}
		$displayHTML .= "</SELECT><br /><textarea name=\"Notes\" rows=\"10\" cols=\"60\" style=\"width:100%\"></textarea>";

		echo $displayHTML;
?>
<input type="submit" name="Submit" value="Blacklist">
</form>
<?php
	}
?>
