<?php
	$data = array();
	$data['or_id'] = ss_getTrasacationRef($Q_Order['tr_id']);
	$data['TheOrderID'] = $Q_Order['or_id'];	
	$data['or_recorded'] = $Q_Order['or_recorded'];
	$data['or_purchaser_firstname'] = $Q_Order['or_purchaser_firstname'];
	$data['or_purchaser_lastname'] = $Q_Order['or_purchaser_lastname'];	
	$data['tr_reference'] = $Q_Order['tr_reference'];	
	$data['or_tracking_code'] = $Q_Order['or_tracking_code'];	   
	$details = unserialize($Q_Order['or_details']);
	$data['BackURL'] = getBackURL();
	
	$shippingDetails = unserialize($Q_Order['or_shipping_details']);
	$data['ShippingDetailsHTML'] = '';
	foreach($shippingDetails['ShippingDetails'] as $key => $aValue) {
		if ($key != 'first_name' and $key != 'last_name') {
			$data['ShippingDetailsHTML'] .= $aValue."<BR>";
		}
	}
	
	$data['PurchaserDetailsHTML'] = '';
	foreach($shippingDetails['PurchaserDetails'] as $key => $aValue) {
		if ($key != 'first_name' and $key != 'last_name') {
			$data['PurchaserDetailsHTML'] .= $aValue."<BR>";
		}
	}
	
	ss_paramKey($details, 'BasketHTML', '');
	ss_paramKey($details, 'GiftMessage', '');		
	$data['BasketHTML'] = $details['BasketHTML'];
	$data['GiftMessage'] = $details['GiftMessage'];
	$data['ThankYouNote'] = $shopSetting['AST_SHOPSYSTEM_THANKYOUNOTE'];	
	
	
	$data['LogoHTML'] = $GLOBALS['cfg']['website_name'];
	if(file_exists(expandPath('Custom/ContentStore/Layouts/Images/Shop/invoiceLogo.gif'))) {
		$data['LogoHTML'] = "<img src='Custom/ContentStore/Layouts/Images/Shop/invoiceLogo.gif'>";
	}
	
	
	$this->useTemplate('Invoice', $data);
?>