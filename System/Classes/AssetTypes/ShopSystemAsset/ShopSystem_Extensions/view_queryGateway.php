<br />
<?php 
// figure out which object we are dealing with
// this should be better

$tx = getRow( "select * from transactions join payment_gateways on pg_id = tr_bank where tr_id = ".((int)$this->ATTRIBUTES['tr_id']) );

if( $pg_object = $tx['pg_object'] )
{
	echo "Object:$pg_object<br/>";

	if (array_key_exists('Do',$this->ATTRIBUTES)) 
	{
		$refund = $pg_object::refund( escape( $this->ATTRIBUTES['amount'] ),
										escape( $this->ATTRIBUTES['unique_id'] ) );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $refund );
		$note = print_r( $refund, true );
		$or_id = getField( "select or_id from shopsystem_orders where or_tr_id = ".((int)$this->ATTRIBUTES['tr_id']) );
		query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$note', NOW(), $or_id)" );
		echo "Refund status : ".$refund->Message;

		echo "<p>You may now close this window.</p>";	
	}
	else 
	{
		echo "Query tx:".$this->ATTRIBUTES['tr_id']."<br />";
		$enq = $pg_object::enquire( $this->ATTRIBUTES['tr_id'] );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $enq );
		$unique_id = $pg_object::refund_tx( $enq );

		foreach ( $enq as $f=>$v )
		{
			echo "$f = ";
			if( is_array( $v ) )
				foreach ($v as $e )
					print_r( $e );
			else
			{
				print_r( $v );
			}
			echo "<br />";
		}

		if( $unique_id )
		{
	?>
	<br />
	<br />
	<br />
	<form name="theForm" method="post" action="index.php?act=ShopSystem.QueryGateway&tr_id=<?=$this->ATTRIBUTES['tr_id']?>&Do=1">
		Refund: <input name="amount" value="" type="text" /><br/>
		<input type='hidden' name='unique_id' value='<?= $unique_id?>' />
		<input type="Submit" name="submit" value="Refund">
	</form>
	<? }
	}
}
else
{ ?>
	Not implemented
<?php
}?>
