<?php 

	die;

	$assetID = $asset->getID();
	
	$Q_MembersOrders = query("SELECT * FROM members_orders WHERE as_id = $assetID");
	$orders = '';
	while($aOrder = $Q_MembersOrders->fetchRow()) {				
		$orders .= ss_comma($orders).$aOrder['mo_tr_id'];
	}
	if (strlen($orders)) {	
		$Q_DeleteTransactions = query("DELETE FROM transactions WHERE tr_id IN ($orders)");		
	}	
	$Q_DeleteMembersOrders = query("DELETE FROM members_orders WHERE as_id = $assetID");
	
	
	
	
	$Q_GetSubscriptions = query("SELECT * FROM members_subscriptions WHERE ms_as_id = $assetID");
	$subs = '';
	while($aSub = $Q_GetSubscriptions->fetchRow()) {
		$subs .= ss_comma($subs).$aSub['ms_id'];
	}
	if (strlen($subs)) {	
		$Q_DeletePrices = query("DELETE FROM members_subscription_prices WHERE msp_sub_id IN ($subs)");		
	}		
	$Q_DeleteSubscriptions = query("DELETE FROM members_subscriptions WHERE ms_as_id = $assetID");
?>
