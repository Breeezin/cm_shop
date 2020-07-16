<?
	$Q_ShippedToday = query("
		SELECT shp_date, shp_customs_number, or_tr_id, or_purchaser_lastname FROM shopsystem_shipped_products, shopsystem_orders
		WHERE shp_ssc_id IS NULL
			AND or_id = shp_or_id
		ORDER BY shp_date ASC, shp_customs_number
	");	
	
	
?>