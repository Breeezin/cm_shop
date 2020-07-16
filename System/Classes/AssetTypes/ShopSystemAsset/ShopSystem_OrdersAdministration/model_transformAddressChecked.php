<?php 

	$this->param('OrderList');

	$transactions = ListToArray( $this->ATTRIBUTES['OrderList'], "," );
	foreach( $transactions as $Transaction )
	{
		echo "Checked Address on Order #".$Transaction."<br>";
		if( $Transaction > 0 )
		{
			query( "update shopsystem_orders set or_not_new = 2 where or_tr_id = {$Transaction}");
			ss_log_message( "User ".ss_getUserID()." marking order $Transaction as address checked" );
			ss_audit( 'update', 'transactions', $Transaction, 'Address Checked ' );
		}
		else
		{
			$Transaction = -$Transaction;
			query( "update shopsystem_orders set or_not_new = 1 where or_tr_id = {$Transaction}");
			ss_log_message( "User ".ss_getUserID()." removing order $Transaction as address checked" );
			ss_audit( 'update', 'transactions', $Transaction, 'NOT Address Checked' );
		}
	}

?>
