<?php 
	$email_body = "Combo report <br/>";

	$email_body .= ss_query_to_html( "select p1.pr_offline, p1.pr_id, p1.pr_name, cpr_qty, p2.pr_name as ComboProduct from shopsystem_products p1, shopsystem_combo_products, shopsystem_products p2 where p1.pr_combo is not null and cpr_element_pr_id = p1.pr_id and cpr_pr_id = p2.pr_id order by 1, 2" );
	$recipients = array( "acme@admin.com" );

	foreach( $recipients as $recipient )
		$result = new Request('Email.Send',array(
						'to'	=>	$recipient, 
						'from'	=>	'webserver@acmerockets.com',
						'subject'	=>	"Combo Report",
						'html'	=>	$email_body,
					));
?>
