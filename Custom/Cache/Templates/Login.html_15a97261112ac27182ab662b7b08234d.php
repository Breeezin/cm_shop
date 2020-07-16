<?
	ss_paramKey($data,'ConfirmOrder',false);
?>
<div class="form-group">

<?php if( ss_getUserID() <= 0 ) { ?>
	<p>If you are a returning customer, please log in here first, it might enable more options.</p>

		<div class="container-fluid">
			<div class="row">
						<input type="radio" name="LoginOrGuest" id='Guest' value="0" checked onclick='document.getElementById("LoginHTML").style.display=""' /> I am a Returning Customer
			</div>
			<div class="row">
						<input type="radio" name="LoginOrGuest" id='Guest' value="1" onclick='document.getElementById("LoginHTML").style.display="none"' /> I am a New Customer
			</div>
		</div>

		<div id='LoginHTML'>
		<?php print($data['LoginHTML']); ?>
		</div>

	<p></p>
		<?php if (count($data['Errors'])) { ?>
			<p> <?php if (count($data['Errors']) != 0) {	$errorMessages = ''; foreach ($data['Errors'] as $messages) foreach ($messages as $message) $errorMessages .= "<LI>$message</LI>"; print('<P><TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD CLASS="entryErrors">Errors were detected in the data you entered, please correct the	following issues and re-submit. <UL>'.$errorMessages.'</UL></TD></TR></TABLE></P>'); } ?> </p>
		<?php } ?>	
<?php } else {  ?>
		<p>Thanks for logging in <?php echo ss_getFirstName(); ?></p>
<?php } ?>

	<script language="javascript" type="text/javascript">
	function newLoginCountry(chosen)
	{
		if( chosen )
			if( chosen != '<?=$_SESSION['ForceCountry']['cn_two_code']?>' )
				window.location = "index.php?act=Security.SetCountry&CC="+chosen+"&BackURL=<?=$_SERVER['REQUEST_URI']?>/Chosen/1";
	}
	</script>

<?php
$buttonName = 'Continue with checkout';
?>

<form name="LoginForm" method="post" action="/Shop_System/Service/Login">
	<div>
		<label class="control-label" for="input-shipping">Shipping to</label>
		<select name="ShippingCountry" onchange="newLoginCountry(this.value);">
			<option value="">Please confirm which country you are shipping to here</option>
			<option value="">--------</option>
<?php
	$Q_Countries = query("
		SELECT cn_id, cn_name, cn_two_code FROM countries
		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
		 AND (cn_restrict_shipping IS NULL OR cn_restrict_shipping = 0 OR cn_redirect_url IS NOT NULL)
		ORDER BY cn_name
	");	

	$Countries = array();
	while( $row = $Q_Countries->fetchRow())
		$Countries[$row['cn_id']] = $row;

	$fc = $_SESSION['ForceCountry']['cn_id'];
	echo "<option value=\"".$Countries[$fc]['cn_two_code']."\"";
	if( $data['Chosen'] )
		echo " selected='selected'";
	echo ">".$Countries[$fc]['cn_name']."</option>";
?>
		<option value="">--------</option>
<?php
	foreach ( $Countries as $id=>$row )
		echo "<option value=\"".$row['cn_two_code']."\" >".$row['cn_name']."</option>";
?>
		</select>
	</div>
<?php
	if( $data['NeedsPayment']  )
	{
?>
  <p> Payment Options, <strong>please choose one of these...</strong> </p>
<?php
		if( $data['PaymentOptions'] !== NULL )
		{
			if( $data['LastGatewayName'] )
			    echo "<p>You last used {$data['LastGatewayName']}</p>";
?>
	<div class="container-fluid">
		<div class="row">
		<?php
		$last_name = ''; $last_currency = '';
		$selected = false;
		?>
		<?php $tmpl_loop_rows = $data['PaymentOptions']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['PaymentOptions']->fetchRow()) { $tmpl_loop_counter++; ?>
			<?php
			if( $_SESSION['GatewayOption'] == $row['po_id'] )
				$selected = true;

			// if( $last_name == $row['cct_name'] && $last_currency == $row['po_currency'] )
			if( $last_name == $row['cct_name'] )
				continue;
			else
			{
			?>
			<div class="col-sm-4">
				<div id='div<?php print(ss_HTMLEditFormat($row['po_id'])); ?>' class="paymentChoice">
					<script language="javascript" type="text/javascript">
						var s = document.getElementById('div<?php print(ss_HTMLEditFormat($row['po_id'])); ?>');
						s.style.cursor = 'pointer';
						s.onclick = function() {
							var r = document.getElementById('radio<?php print(ss_HTMLEditFormat($row['po_id'])); ?>');
							r.checked = true;
						};
					</script>

					<div style='height:200px'>
						<strong>
						<?php echo str_replace( ' ', '&nbsp;', $row['po_currency'].' - '.$row['cct_name'] ); ?>
						</strong>
						<img src="/images/<?php print(ss_HTMLEditFormat($row['cct_image'])); ?>" />
						<?php print($row['description']); ?>
					</div>
					<?php echo getCurrencySummary( abs($row['po_id']), $_SESSION['Shop']['Basket']['Total'] ); ?>
					<input type="radio" name="GatewayOption" id='radio<?php print(ss_HTMLEditFormat($row['po_id'])); ?>' value="<?php print(ss_HTMLEditFormat($row['po_id'])); ?>" <?php if( $selected ) echo "checked";?>>
				</div>
			</div>
			<?php
				$last_name = $row['cct_name'];
				$last_currency = $row['po_currency'];
				$selected = false;
			} ?>
		<?php } ?>
		</div>
	</div>
<?php
		}

	}
	else
	{
?>
	<p>
		Continue using account credit of <?=-($_SESSION['Shop']['Basket']['Discounts']['Account Credit'])?> <?= $data['CurrencyCode']?>
		<input type="hidden" name="GatewayOption" value="<?=$_SESSION['User']['us_credit_from_gateway_option']?>" />
	</p>
<?php } ?>

<input name="imageField" type="submit" value="<?=$buttonName?>" class="btn btn-primary" />
</form>

<?php
if( $data['PaymentOptionsOtherSite'] !== NULL )
{
	$buttonName = 'Swap to '.$data['OtherSiteName'];
?>
<form action="<?=$data['OtherSiteName'].$_SERVER['REQUEST_URI']?>" method="post">
	<input type='hidden' name='Session' value='<?php echo session_id(); ?>' />
	<p><strong>... or go to our sister site </strong> <?=$data['OtherSiteName']?></p>
	<div class="container-fluid">
		<div class="row">
		<?php $tmpl_loop_rows = $data['PaymentOptionsOtherSite']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['PaymentOptionsOtherSite']->fetchRow()) { $tmpl_loop_counter++; ?>
		<?php
			if( $last_name == $row['cct_name'] && $last_currency == $row['po_currency'] )
				continue;
			else
			{
		?>
			<div class="col-sm-4">
				<div class="paymentChoice">
					<strong>
					<?php echo str_replace( ' ', '&nbsp;', $row['po_currency'].' - '.$row['cct_name'] ); ?>
					</strong>
					<div style="height:60px;"><img src="/images/<?php print(ss_HTMLEditFormat($row['cct_image'])); ?>" /></div>
					<div style="height:100px;"><?php print($row['description']); ?></div>
					<div style="height:80px;"><?php echo getCurrencySummary( abs($row['po_id']), $_SESSION['Shop']['Basket']['SubTotal'] ); ?></div>
				</div>
			</div>
		<?php
	
			$last_name = $row['cct_name'];
			$last_currency = $row['po_currency'];
			} ?>
		<?php } ?>
		</div>
	</div>
	<input name="imageField" type="submit" value="<?=$buttonName?>" class="btn btn-primary" />
</form>
<?php
}
?>
</div>
