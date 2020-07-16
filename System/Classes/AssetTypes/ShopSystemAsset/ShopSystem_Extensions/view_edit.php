<?php 

if (array_key_exists('Do',$this->ATTRIBUTES)) {
	echo "<p>Updated!  You may now close this window.</p>";	
} else {
$Q_Gateways = query( "select * from payment_gateways" );
$Q_CardTypes = query( "select * from credit_card_types" );
?>
<form name="theForm" method="post" action="index.php?act=ShopSystem.AcmeEdit&tr_id=<?=$this->ATTRIBUTES['tr_id']?>&Do=1">
	Please enter the total charged: <input name="Total" value="<?=$this->ATTRIBUTES['Total']?>" type="text">
	<select name="CurrencyLink">	
		<option value=20 <?php if($this->ATTRIBUTES['Currency'] == 20) echo "selected";?>>EUR
		<option value=784 <?php if($this->ATTRIBUTES['Currency'] == 784) echo "selected";?>>USD
		<option value=344 <?php if($this->ATTRIBUTES['Currency'] == 344) echo "selected";?>>HKD
		<option value=9999 <?php if($this->ATTRIBUTES['Currency'] == 9999) echo "selected";?>>BTC
	</select>	
	
	<br/>
	CC Number: <input name="TrCreditCardNumber" value="<?=$this->ATTRIBUTES['TrCreditCardNumber']?>" type="text"><br/>
	CC Type: <SELECT  class="" NAME="TrCreditCardType">
	<?php
	  while( $r = $Q_CardTypes->fetchRow() )
	  	echo "<option value='{$r['cct_id']}'".($r['cct_id'] == $this->ATTRIBUTES['Gateway']?' selected':'').">{$r['cct_name']}";
	?>
	</SELECT><br/>
	Holder: <input name="TrCreditCardHolder" value="<?=$this->ATTRIBUTES['TrCreditCardHolder']?>" type="text"><br/>
	Company: <input name="TrCreditCardCompany" value="<?=$this->ATTRIBUTES['TrCreditCardCompany']?>" type="text"><br/>
	CVV2: <input name="TrCreditCardCVV2" value="<?=$this->ATTRIBUTES['TrCreditCardCVV2']?>" type="text"><br/>
	Expiry: <input name="TrCreditCardExpiry" value="<?=$this->ATTRIBUTES['TrCreditCardExpiry']?>" type="text"><br/>
	Payment Gateway: <select name='Gateway'>
	<?php
	  while( $r = $Q_Gateways->fetchRow() )
	  	echo "<option value='{$r['pg_id']}'".($r['pg_id'] == $this->ATTRIBUTES['Gateway']?' selected':'').">{$r['pg_name']}";
	?>
	</select><br />
	<input type="Submit" name="submit" value="Submit">
</form>
<? } ?>
