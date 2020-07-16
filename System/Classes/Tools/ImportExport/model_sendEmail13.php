<?php 
	$email_body = "(M)Stock on hand, units, cost and sales value<br/>";

	$email_body .= ss_query_to_html( "select pr_name, sum( pro_stock_available) as num, sum(pro_stock_available*pro_supplier_price) as \$cost, sum(pro_stock_available*pro_price) as \$sales, sum(Quantity) as sold_last_month from shopsystem_products join shopsystem_product_extended_options ON pro_pr_id = pr_id left join (select op_pr_id, sum(op_quantity) as Quantity from ordered_products, shopsystem_orders where or_archive_year IS NULL and op_or_id = or_id and or_paid > now() - interval 1 month group by op_pr_id) AS QtySum on pr_id = op_pr_id where pro_stock_available > 0 and pr_offline IS NULL and pr_combo IS NULL and pr_deleted IS NULL and pr_ve_id  = 4 group by pr_name order by 2 desc" );

	$email_body .= ss_query_to_html( "select \"Total\", sum( pro_stock_available) as num, sum(pro_stock_available*pro_supplier_price) as \$cost, sum(pro_stock_available*pro_price) as \$sales, sum(Quantity) as sold_last_month from shopsystem_products join shopsystem_product_extended_options ON pro_pr_id = pr_id left join (select op_pr_id, sum(op_quantity) as Quantity from ordered_products, shopsystem_orders where or_archive_year IS NULL and op_or_id = or_id and or_paid > now() - interval 1 month group by op_pr_id) AS QtySum on pr_id = op_pr_id where pro_stock_available > 0 and pr_offline IS NULL and pr_combo IS NULL and pr_deleted IS NULL and pr_ve_id  = 4");

	$recipients = array( "acme@admin.com", 'bjork.christina@gmail.com' );


	foreach( $recipients as $recipient )
		$result = new Request('Email.Send',array(
						'to'	=>	$recipient, 
						'from'	=>	'webserver@acmerockets.com',
						'subject'	=>	"(M) Stock on hand",
						'html'	=>	$email_body,
						'useTemplate'  => 0,
					));
?>
