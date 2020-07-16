<?php
	
	$Q_Orders = query("
		SELECT * FROM shopsystem_orders, shopsystem_invoices
		WHERE (or_paid_not_shipped IS NOT NULL OR (or_reshipment IS NOT NULL AND or_shipped IS NULL) OR (or_lottery IS NOT NULL AND or_shipped IS NULL))
			AND or_cancelled IS NULL
			AND or_standby IS NULL
			AND in_or_id = or_id
		ORDER BY or_id
	");	

?>