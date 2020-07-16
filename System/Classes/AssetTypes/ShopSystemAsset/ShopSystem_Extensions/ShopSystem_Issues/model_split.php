<?php
	// admin splits issue
	$this->param('ce_id');

	$ce_id = (int)$this->ATTRIBUTES['ce_id'];

	if( $ce_id )
	{
		$split = getRow( "select * from client_issue_entry where ce_id = $ce_id" );

		if( $split )
		{
			$ci_id = $split['ce_ci_id'];
			
			$original = getRow("select * from client_issue where ci_id = $ci_id" );
			if( !strlen($original['ci_transaction_number']) )
				$original['ci_transaction_number'] = 'NULL';
			if( !strlen($original['ci_assigned_to']) )
				$original['ci_assigned_to'] = 'NULL';
			if( !strlen($original['ci_cq_id']) )
				$original['ci_cq_id'] = 'NULL';
			query( "insert into client_issue (ci_us_id, ci_transaction_number, ci_assigned_to, ci_token, ci_verified_email, ci_cq_id)"
					."values ( {$original['ci_us_id']}, {$original['ci_transaction_number']}, {$original['ci_assigned_to']}, '{$original['ci_token']}', '{$original['ci_verified_email']}', {$original['ci_cq_id']} )" );

			$new_ci_id = getLastAutoIncInsert();
			query( "update client_issue_entry set ce_ci_id = $new_ci_id where ce_ci_id = $ci_id and ce_id >= $ce_id" );
			query( "update client_issue_response set cir_ci_id = $new_ci_id where cir_ci_id = $ci_id and cir_created >= '{$split['ce_created']}'" );
			query( "update client_issue_attachment set cia_ci_id = $new_ci_id where cia_ci_id = $ci_id and cia_created >= '{$split['ce_created']}'" );

			locationRelative("index.php?act=ShopSystem_Issues.Edit&ci_id=$new_ci_id&BackURL=index.php%3FAct%3DShopSystem_Issues.Display");
		}
		else
		{
			echo "Invalid id";
		}
	}

?>
