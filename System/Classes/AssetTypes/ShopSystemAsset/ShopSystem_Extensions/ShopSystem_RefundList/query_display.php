<?php

	$list = 1;
	if( array_key_exists('List', $_GET ) )
		$list = (int) $_GET['List'];

	$OrdersCount = getField("
		SELECT COUNT( DISTINCT rfd_or_id ) as count FROM shopsystem_refunds
		WHERE rfd_pending = true
	");

	$Q_Orders = query("
		SELECT rfd_or_id, min(tr_id) as tr_id, sum(rfd_amount) as Amount, min(tr_payment_details_szln) as tr_payment_details_szln FROM shopsystem_refunds join shopsystem_orders on or_id = rfd_or_id join transactions on tr_id = or_tr_id
		WHERE rfd_pending = true
			group by rfd_or_id
		limit 1
	");

	$Q_CreditCardTypes = query("
		SELECT * FROM credit_card_types
	");

	$ccTypes = array();
	while ($row = $Q_CreditCardTypes->fetchRow()) {
		$ccTypes[$row['cct_id']] = $row['cct_name'];
	}
	
?>
