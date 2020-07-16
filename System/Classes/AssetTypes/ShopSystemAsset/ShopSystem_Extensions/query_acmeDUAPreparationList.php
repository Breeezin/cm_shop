<?php
	
	$Q_Orders = query("
		SELECT * FROM shopsystem_orders, shopsystem_invoices LEFT JOIN shopsystem_transit_documents ON shopsystem_invoices.inv_id = shopsystem_transit_documents.TrDoInvoiceLink
		WHERE (or_paid IS NOT NULL OR or_reshipment IS NOT NULL OR or_lottery IS NOT NULL)
			AND or_shipped IS NULL
			AND or_cancelled IS NULL
			AND or_standby IS NULL
			AND in_or_id = or_id
		ORDER BY TrDoID
	");	

?>