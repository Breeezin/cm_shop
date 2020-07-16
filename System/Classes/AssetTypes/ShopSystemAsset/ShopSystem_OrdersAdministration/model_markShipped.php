<?php 

	$this->param('or_id');	
	$this->param('BackURL');

	if( ss_adminCapability( ADMIN_ORDER_STATUS ) )
	{
		ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'setting Shipped' );
		
		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");
		
		$extraSQL = '';
		if (ss_optionExists('Shop Advanced Ordering')) {
			if ($Q_Order['or_paid_not_shipped'] !== null) {
				$extraSQL = ", or_paid_not_shipped = NULL";
			}
		}	
		
		$Q_UpdateOrder = query("
				UPDATE shopsystem_orders 
				SET or_shipped = Now() 
					$extraSQL
				WHERE or_id = {$this->ATTRIBUTES['or_id']}");
	}

	locationRelative($this->ATTRIBUTES['BackURL']);
		
?>
