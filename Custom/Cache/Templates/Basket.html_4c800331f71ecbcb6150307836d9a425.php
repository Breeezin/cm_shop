
<?php if (count($data['Basket']['Products']) || ( $_SESSION['User']['us_account_credit'] < 0 )) { ?>
	<?php if ($data['Style'] == 'WithInputs') { ?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2"align="left"><?php print($data['CurrencyConverterHTML']); ?></td>
	</tr>
	
	<tr>
		<td align="left">&nbsp;</td>
		<td><hr size="1"></td>
	</tr>
	<tr>
	 <td align="left">&nbsp;</td>
	 <td>
  <?php } ?>

<!--basket-->
	<table width="100%" border="0" cellpadding="5" cellspacing="0" class="onlineShop_BasketTable basket">
		<tr>
			<td colspan="5"><span class="textSubHeaders">Cart</span></td>
		</tr>
		<tr>
			<td colspan="5">To change a quantity of a product, alter the number then press enter. To remove a product,
				enter 0 as the quantity then press enter.<br /><br /></td>
	 </tr>
		<tr>
			<td class="onlineShop_BasketHeaderRow">Product</td>
			<td class="onlineShop_BasketHeaderRow">Qty</td>
			<td class="onlineShop_BasketHeaderRow">Services</td>
			<td class="onlineShop_BasketHeaderRow">Price</td>
			<td class="onlineShop_BasketHeaderRow">Sub Total</td>
	 </tr>
	 <script language="Javascript">
		var qtyChanged = 0;
	 </script>
<? 
	$data['TotalPrice'] = 0;
	$data['Basket']['TotalServices'] = 0;
	$flipFlop = 0;
	foreach($data['Basket']['Products'] as $item)
	{ 
		$data['Name'] = $item['Product']['pr_name'];
		$data['Options'] = $item['Product']['Options'];
		$data['Qty'] = $item['Qty'];
		$data['Key'] = $item['Key'];
		$data['StockCode'] = $item['Product']['pro_stock_code'];
		$data['UnitPrice'] = $item['Product']['Price'];
		$flipFlop = 1-$flipFlop;
		$data['Class'] = '';
		if ($flipFlop == 1)
			$data['Class'] = 'onlineShopBasketOddRow';	

	?>
		 
		<tr align="left" valign="middle">
			<td class="<?php print(ss_HTMLEditFormat($data['Class'])); ?>">
				<strong><?php print(ss_HTMLEditFormat($data['Name'])); ?><?php if (strlen($data['Options'])) { ?> (<?php print(ss_HTMLEditFormat($data['Options'])); ?>)<?php } ?></strong><br /><?php print(ss_HTMLEditFormat($data['StockCode'])); ?>
			</td>
<?php
		if( $item['Product']['pr_is_service'] == 'false' )
		{
?>
				<form action="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/UpdateBasket/Key/<?php print(ss_URLEncodedFormat($data['Key'])); ?>/Mode/Set">
					<input type="hidden" name="BackURL" value="<?php echo $_SERVER['REQUEST_URI'];?>">
					<td class="<?php print(ss_HTMLEditFormat($data['Class'])); ?>"><input name="Qty" value="<?php print(ss_HTMLEditFormat($data['Qty'])); ?>" size="3" onChange="this.form.submit();" class="onlineShop_basketQuantityField"></td>
				</form>
				<td class="<?php print(ss_HTMLEditFormat($data['Class'])); ?>">
<?php
				// available services for this product
				$selectedServices = query( 'select * from product_service_options join shopsystem_products on sv_pr_id_service = pr_id join shopsystem_product_extended_options on pro_pr_id = pr_id where sv_pr_id = '.$item['Product']['pr_id'].' and pr_offline IS NULL' );

				while( $service = $selectedServices->fetchRow() )
				{
					?>
					<form action="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/UpdateBasket/AddService/<?=$service['sv_id']?>/Mode/Set">
					<input type="hidden" name="BackURL" value="<?php echo $_SERVER['REQUEST_URI'];?>">
					<input type='hidden' name="Qty" value="<?php print(ss_HTMLEditFormat($data['Qty'])); ?>" />
					<?php
//					echo '<INPUT style="border:0px;" TYPE="CHECKBOX" NAME="DoIt" VALUE="1" class="checkBox" onChange="this.form.submit();"';
					if( array_key_exists('AddService', $item) && is_array($item['AddService']) && in_array( $service['sv_id'], $item['AddService'] ) )
					{
						if( stristr( $service['pr_name'], "padding" ) )
							echo '<INPUT style="border:0px;" TYPE="CHECKBOX" NAME="DoIt" VALUE="1" class="checkBox" onChange="alert( \'In removing this you assume responsibility for any damage.\' ); this.form.submit();"';
						else
							echo '<INPUT style="border:0px;" TYPE="CHECKBOX" NAME="DoIt" VALUE="1" class="checkBox" onChange="this.form.submit();"';
						echo ' checked>';
					}
					else
					{
						echo '<INPUT style="border:0px;" TYPE="CHECKBOX" NAME="DoIt" VALUE="1" class="checkBox" onChange="this.form.submit();"';
						echo '>';
					}

					echo $service['pr_name'];
					echo '<br /></form>';
				}
		}
		else
		{
//				$data['Basket']['TotalServices'] += $item['Qty']*$item['Product']['Price'];
		?>
			<td class="<?php print(ss_HTMLEditFormat($data['Class'])); ?>"><?php print(ss_HTMLEditFormat($data['Qty'])); ?></td>
			<td class="<?php print(ss_HTMLEditFormat($data['Class'])); ?>"></td>
		<?php
		}

?>

			
			
			</td>
			<td class="<?php print(ss_HTMLEditFormat($data['Class'])); ?>"><?php if ($data['UnitPrice'] == 0) { ?>FREE<?php } else { ?><?php print($data['This']->formatPrice('display',$data['UnitPrice'])) ?><?php } ?></td>
			<td class="<?php print(ss_HTMLEditFormat($data['Class'])); ?>"><?php if ($data['UnitPrice'] == 0) { ?>FREE<?php } else { ?><?php print($data['This']->formatPrice('display',$data['Qty']*$data['UnitPrice'])) ?><?php } ?></td>
	 </tr>
<? 		
	} ?>
	 
	<? $data['Tax'] = $data['Basket']['Tax']['Code']; ?>
	<?php if (strlen($data['Tax'])) { ?>
	<tr>
			<td>&nbsp;</td>
		<?php if ($data['Style'] == 'WithInputs') { ?>
			<td>&nbsp;</td>
		<?php } ?>
			<td>&nbsp;</td>
			<td class="onlineShopBasketSubTotal"><?php print(ListLast($data['TaxCountryNoteHTML'],'~')); ?></td>
		<td class="onlineShopBasketSubTotal">
			<?php print($data['This']->formatPrice('display',$data['Basket']['Tax']['Amount'])) ?>			
		</td>
	<?php } ?>
	 </tr>
	 <tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td class="onlineShopBasketSubTotal">Sub-total: </td>
		<td class="onlineShopBasketSubTotal"><?php print($data['This']->formatPrice('display',$data['Basket']['SubTotal'])) ?></td>
	</tr>
	<?php /*
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td class="onlineShopBasketSubTotal">Services: </td>
		<td class="onlineShopBasketSubTotal"><?php print($data['This']->formatPrice('display',$data['Basket']['TotalServices'])) ?></td>
	</tr>
	*/ ?>
	<?php if ($data['Basket']['Freight']['Amount'] != 0) { ?>
	 <tr>
		<?php if ($data['Style'] == 'WithInputs') { ?>
			<td colspan="3">&nbsp;</td>
		<?php } ?>
		<td class="onlineShopBasketSubTotal">Shipping/Tracking:</td>
		<td class="onlineShopBasketSubTotal">
			<?php print($data['This']->formatPrice('display',$data['Basket']['Freight']['Amount'])) ?>			
		</td>
	 </tr>
	<?php } ?>
 </tr>
	 <? 
		if (array_key_exists('Discounts',$data['Basket'])) { 
			foreach($data['Basket']['Discounts'] as $data['DiscountName'] => $data['DiscountAmount']) {
	 ?>
		<tr>
			<td>&nbsp;</td>
		<?php if ($data['Style'] == 'WithInputs') { ?>
			<td>&nbsp;</td>
		<?php } ?>
			<td>&nbsp;</td>
			<td class="onlineShopBasketSubTotal"><?php print(ss_HTMLEditFormat($data['DiscountName'])); ?>: </td>
			<td class="onlineShopBasketSubTotal"><?php print($data['This']->formatPrice('display',$data['DiscountAmount'])) ?></td>
	 </tr>
	 <? 
			}
		}
	 ?>
		<tr>
			<td>&nbsp;</td>
		<?php if ($data['Style'] == 'WithInputs') { ?>
			<td>&nbsp;</td>
		<?php } ?>
			<td>&nbsp;</td>
			<td class="onlineShopBasketTotal">Total: </td>
			<td class="onlineShopBasketTotal"><?php print($data['This']->formatPrice('display',$data['Basket']['Total'])) ?></td>
	 </tr>
	 </table>
	 
	 <!--end of basket-->

	<?php if ($data['Style'] == 'WithInputs') { ?>
        	</td>
	</tr>

</table>
	<?php } ?>
<?php } else { ?>
	<p>You currently have no items in your cart.</p>
<?php } ?>

