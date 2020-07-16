<?php

	require_once('session.php');

	$tables = [
		'OldExchangeRates',
		'shopsystem_blacklist',
		'shopsystem_categories',
		'shopsystem_category_descriptions',
		'shopsystem_combo_products',
		'shopsystem_discount_codes',
		'shopsystem_discount_groups',
		'shopsystem_order_sheets',
		'shopsystem_order_sheets_items',
		'shopsystem_order_items',
		'shopsystem_order_notes',
		'shopsystem_order_products',
		'shopsystem_orders',
		'shopsystem_product_descriptions',
		'shopsystem_product_extended_options',
		'shopsystem_products',
		'ShopSystem_AcmeOrderProducts',
		'shopsystem_quick_categories',
		'shopsystem_refunds',
		'shopsystem_shipped_products',
		'shopsystem_stock_notifications',
		'product_locations',
		'proxy_addresses',
		'transactions',
		'user_groups',
		'user_user_groups',
		'users',
		'unusable_emails',
		'vendor',
		'audit',
		'back_in_stock_check',
		'bank_transfer_information',
		'bitcoin_addresses',
		'canned_question',
		'canned_responses',
		'product_type',
		'client_issue',
		'client_issue_attachment',
		'client_issue_edit',
		'client_issue_entry',
		'client_issue_response',
		'coh_category',
		'coh_product',
		'coh_product_safe',
		'competitor',
		'competitor_prices',
		'competitor_scraper',
		'competitor_scraper_20100927',
		'competitor_scraper_coh',
		'competitor_scraper_safe',
		'default_scraper',
		'guest_users',
		'issue_labels',
		'payment_gateway_options',
		'payment_gateways',
		'picture_cache',
		'product_service_options',
		'reports',
		'sales_summary',
		'search_engine',
		'exchange_rate',
		'litecoin_addresses',
		'customer_invoice_line',
		'customer_invoice',
		'customer',
		'supplier_invoice_line',
		'supplier_invoice',
		'supplier_sku_lookup',
		'supplier',
		'free_giveaways',
		'reset_frequency',
		'lastest_product_additions',
		'product_dropdown',
		'product_heading',
		'used_cc_details',
		'blacklist_cc_details',
		'blacklist_ip_addresses',
		'blacklist',
		'prexop_history',
		'currency_converter',
		'included_freight',
		'MobileHumidor',
		'MobileUsers',
		'product_service_default',
		'sales_zone',
		'TransactionProfitSafe',
		'address_checker',
		'blacklist',
		'blacklist_cc_details',
		'blacklist_ip_addresses',
		'customer',
		'customer_invoice',
		'customer_invoice_line',
		'customer_invoice_shipment',
		'entered_on_invoice',
		'lastest_product_additions',
		'litecoin_addresses',
		'ordered_products',
		'payment_gateway_options_saved',
		'prexop_history',
		'price_changes',
		'product_dropdown',
		'product_heading',
		'product_tags',
		'product_vendor_map',
		'reset_frequency',
		'sales_since_shipping',
		'stock_movement',
		'stocktake',
		'supplier',
		'supplier_invoice',
		'supplier_invoice_line',
		'supplier_sku_lookup',
		'used_cc_details',
		'user_addresses',
		'user_tracking'
		];

	foreach( $tables as $table )
	{
		$keyname = $keyval = NULL;

		$q = "SELECT k.COLUMN_NAME as keyname FROM information_schema.table_constraints t LEFT JOIN information_schema.key_column_usage k USING(constraint_name,table_schema,table_name) WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema=DATABASE() AND t.table_name='$table'";
		$result = mysql_query( $q );
		if ($result)
			if( $row = mysql_fetch_assoc($result) )
				$keyname = $row['keyname'];

		$q = "SELECT Auto_increment as keyval FROM information_schema.tables WHERE table_name  = '$table' AND table_schema = DATABASE();";
		$result = mysql_query( $q );
		if ($result)
			if( $row = mysql_fetch_assoc($result) )
				$keyval = $row['keyval'];

		if( $keyname && $keyval )
			echo "mysqldump --default-character-set=latin1 --lock-tables=false --single-transaction --skip-opt --quick --complete-insert --no-create-info -ps3Are pe $table --where='where $keyname >= $keyval'\n";

	}

?>
