<?php 
	if (array_key_exists('DoAction', $this->ATTRIBUTES) and $this->ATTRIBUTES['DoAction'] == 'Confirm') {
		print ("<BR>Thank you.</BR>");	
	} else {				
		$comfirmButton = '';
		if (array_key_exists('DoAction', $this->ATTRIBUTES)) {
			$action = 'ReDraw';
			if (strlen($this->ATTRIBUTES['Winner']) and !count($errors))
				$comfirmButton = "<input type=\"submit\" name=\"DoAction\" value=\"Confirm\">";
		} else {
			$action = 'Draw';
		}
		
		if	(count ($errors)) {
			print("<p>Error has been detected<BR><ui>");
			foreach ($errors as $error) {
				print('<li>'.$error.'</li>');
			}
			print("</ui></p>");
		} 
?>
<form name="adminForm" method="POST" action="index.php?act=<?=$this->ATTRIBUTES['act']?>">
<p>
<table width="100%" border="0" cellpadding="5" cellspacing="0" class="onlineShop_BasketTable">
	<tr>
		<td colspan="3">
			Select a product to add.
		</td>
	</tr>
	<tr><td>
	<table class="onlineShop_checkoutSectionTable" width="100%" cellspacing="0" cellpadding="5">	
	<tr>
		<td width="100"><strong>Category:</strong></td>
		<td>
			 <select name="pr_ca_id" class="onlineShop_productBand_categorySelect" onchange="document.getElementById('loader').src = 'Shop_System/Service/CategoryProductsJS/ca_id/'+this.options[selectedIndex].value;">
				<option selected value="">Please Select</option>								
				<?php while($row = $Q_Categories->fetchRow()) { ?>
					<option value="<?=$row['ca_id']?>"><?=$row['ca_name']?></option>
				<?php  } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><strong>Product:</strong></td>
		<td>
			 <select name="Key">
				<option value="">Select Category First</option>
			</select>
		</td>
	</tr>
		<script language="Javascript">
			function updateProducts(p) {
				keys = document.forms.adminForm.Key;
				for(var j= keys.options.length-1; j >= 0; j--) keys.options[j] = null;
				keys[0] = new Option('Please Select','');
				for(var i=0;i<p.length;i++) keys[i+1] = new Option(p[i].n,p[i].k);
			}
		</script>			
	</table>
	</td></tr>
<tr><td>
<p>
Date From <?PHP print $dateFromField->display(false);?> To <?PHP print $dateToField->display(false);?>
<tr><td>
Minium Order At &euro; <?PHP print $minTotalField->display(false);?><BR>
<?php 
	if ($winner != null) {
		print ("<BR>Winner Name:  {$winner['us_first_name']} {$winner['us_last_name']}<BR>");
		print ("Winner Email: {$winner['us_email']}<BR>");
		print ("Winner Order Ref#: {$winner['tr_id']}<BR>");
		print ("Winner Order Total: &euro; {$winner['tr_total']}<BR><BR>");
	}
?>
</p>
</td></tr>
<tr><td>
<input type="hidden" name="Winner" value="<?=$this->ATTRIBUTES['Winner']?>">
<input type="submit" name="DoAction" value="<?=$action?>">&nbsp;&nbsp;&nbsp;<?=$comfirmButton?>
</td></tr>
</TABLE>
</form>
<iframe src="" id="loader" name="loader" style="display:none;"></iframe>
<?php
	if (strlen($this->ATTRIBUTES['pr_ca_id']) and strlen($this->ATTRIBUTES['Key'])) {
	
?>
<SCRIPT language="javascript">
	function setSelectValue(theSelect, selected) {			
		for (var i = 0; i < theSelect.options.length; i++) {
			if (theSelect.options[i].value == selected) {
				theSelect.selectedIndex = i;
				break;
			}
		}	
	}
	
	if (document.adminForm.pr_ca_id.options.length) {
		setSelectValue(document.adminForm.pr_ca_id, <?=$this->ATTRIBUTES['pr_ca_id']?>); 
	}
	document.getElementById('loader').src = 'Shop_System/Service/CategoryProductsJS/ca_id/<?=$this->ATTRIBUTES['pr_ca_id']?>/CallBack/parent.updateProducts(p);parent.setSelectValue(parent.document.adminForm.Key,\'<?=$this->ATTRIBUTES['Key']?>\');';
	
	
</SCRIPT>
<?php
	 } 
	}
?>