<?php 

	$this->param('OrderList');
	$this->param('From');
	$this->param('To');

	$transactions = ListToArray( $this->ATTRIBUTES['OrderList'], "," );
	foreach( $transactions as $Transaction )
	{
		echo "Altering ChargeList on Order #".$Transaction." from ".$this->ATTRIBUTES['From']." to ".$this->ATTRIBUTES['To']."<br>";

		$Q_Order = getRow("SELECT * FROM shopsystem_orders join transactions on tr_id = or_tr_id WHERE or_tr_id = {$Transaction}");

		// index.php?act=ShopSystem_ChargeList.AddOrder&or_id=

		ss_audit( 'update', 'Orders', $Q_Order['or_id'], "setting chargelist from ".$this->ATTRIBUTES['From']." to ".$this->ATTRIBUTES['To'] );

		$to = 'NULL';
		if( $this->ATTRIBUTES['To'] == 'true' )
		{
			$to = $Q_Order['tr_bank'];
		/*
			$to++;

			if( array_key_exists( "ChargeList", $GLOBALS['cfg'] )
			 && is_array( $GLOBALS['cfg']['ChargeList'] )
			 && array_key_exists( $Q_Order['tr_currency_link'], $GLOBALS['cfg']['ChargeList'] ) )
				$to = $GLOBALS['cfg']['ChargeList'][$Q_Order['tr_currency_link']];
		*/
		}

		if( ( ( $Q_Order['or_charge_list'] == 1 ) && ( $this->ATTRIBUTES['From'] == 'true' ) )
		 || ( ( $Q_Order['or_charge_list'] == 0 ) && ( $this->ATTRIBUTES['From'] == 'false' ) )
		 || ( $this->ATTRIBUTES['From'] == 'Anything' ) )
		{
			$Q_Update = query(" UPDATE shopsystem_orders
						SET or_charge_list = {$to}
						WHERE or_tr_id = {$Transaction} and or_standby IS NULL and or_cancelled IS NULL and or_card_denied IS NULL");
		}

	}
?>
