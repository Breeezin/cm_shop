<?php

	$one_at_a_time = true;

	$list = 1;
	if( array_key_exists('List', $_GET ) )
		$list = (int) $_GET['List'];

	$OrdersCount = getField("
		SELECT COUNT(*) as count FROM shopsystem_orders, transactions
		WHERE or_archive_year IS NULL
			AND or_charge_list = $list
			AND or_tr_id = tr_id
			AND tr_payment_details_szln IS NOT NULL
		ORDER BY tr_id
	");

	$Q_Orders = query("
		SELECT * FROM shopsystem_orders, transactions
		WHERE or_archive_year IS NULL
			AND or_charge_list = $list
			AND or_tr_id = tr_id
			AND tr_payment_details_szln IS NOT NULL
		ORDER BY tr_id".($one_at_a_time?" limit 1":"") );

	$Q_CreditCardTypes = query("
		SELECT * FROM credit_card_types
	");

	$ccTypes = array();
	while ($row = $Q_CreditCardTypes->fetchRow()) {
		$ccTypes[$row['cct_id']] = $row['cct_name'];
	}
	
?>
