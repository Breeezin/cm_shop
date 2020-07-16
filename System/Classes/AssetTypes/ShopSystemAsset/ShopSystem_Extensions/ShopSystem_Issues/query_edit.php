<?php 

	$Issue = getRow( "select client_issue.*, cu.*, ad.us_first_name as Admin, po_id, pg_name, po_currency_name from client_issue left join users cu on us_id = ci_us_id left join users ad on ad.us_id = ci_assigned_to left join payment_gateway_options on cu.us_credit_from_gateway_option = po_id left join payment_gateways on pg_id = po_pg_id where ci_id = $ci_id" );

	$usID = (int)$Issue['ci_us_id'];

	$Q_Audit = query( "select * from audit where au_key = $usID and au_table = 'users' order by au_id desc limit 100" );

	$Q_Edits = query( "select *, us_first_name, UNIX_TIMESTAMP()-UNIX_TIMESTAMP(cie_when) as ago from client_issue_edit left join users on us_id = cie_us_id where cie_ci_id = $ci_id order by cie_when desc limit 3" );
	query( "insert into client_issue_edit (cie_ci_id, cie_us_id) values ($ci_id, ".ss_getUserID().")" );

	$Q_Attachments = query( "select * from client_issue_attachment where cia_ci_id = $ci_id" );

	$Q_Others = query( "select or_tr_id, or_recorded, or_shipped, or_paid_not_shipped, or_paid, or_total, or_out_of_stock,
						or_invoiced, or_card_denied, or_standby, or_reshipment, or_profit, tr_profit, tr_currency_code,
						tr_bank, pg_name
						from shopsystem_orders
						  join transactions on or_tr_id = tr_id
						  left join payment_gateways on tr_bank = pg_id
						where or_us_id = $usID
						  and or_card_denied IS NULL and or_deleted = 0 and or_cancelled IS NULL
						  and tr_completed = 1
						order by or_recorded DESC limit 100" );		

	$othersTotal = getRow( "select sum( tr_total ) as Total, sum( tr_profit ) as Profit, count(*) as Num
						from shopsystem_orders, transactions
						where or_us_id = $usID
						  and or_card_denied IS NULL and or_deleted = 0 and or_cancelled IS NULL
						  and or_tr_id = tr_id
						  and tr_completed = 1
						order by or_recorded DESC" );

	$Q_Issues = query( "select ce_created as created, ce_text as text, us_first_name as who, '' as emailed, 'black' as colour, ce_id as entry, 0 as deleted, 0 as response,
							ce_session as session, ce_website as website, ce_id as id
							from client_issue_entry join client_issue on ce_ci_id = ci_id left join users on ci_us_id = us_id where ce_ci_id = $ci_id
						union select cir_created as created, cir_text as text, concat('Admin - ', us_first_name) as who, cir_emailed as emailed, 'blue' as colour, 0 as entry, 
							cir_deleted as deleted, cir_id as response, '' as session, '' as website, 0 as id
							from client_issue_response left join users on cir_us_id = us_id where cir_ci_id = $ci_id order by 1" );

	$Q_OtherIssues = query( "select ci_id, ci_transaction_number, ci_created, ci_assigned_to, ci_closed from client_issue where ci_us_id = $usID and ci_id != $ci_id order by ci_created desc" );

	$Q_Administrators = query( "select * from users where us_admin_level & ".ADMIN_CUSTOMER_ISSUE );
	$Q_CannedQuestions = query( "select * from canned_question" );
	if( $Issue['ci_cq_id'] )
		$Q_CannedResponses = query( "select * from canned_responses where cr_cq_id = {$Issue['ci_cq_id']}" );
	else
		$Q_CannedResponses = query( "select * from canned_responses" );

	$Q_VisibleOrders = query( "select * from shopsystem_orders, transactions 
			where or_us_id = $usID 
			and or_tr_id = tr_id
			and or_deleted  = 0
			and tr_completed = 1
			and or_recorded > NOW() - INTERVAL 30 WEEK
			order by or_id desc
			" );




?>
