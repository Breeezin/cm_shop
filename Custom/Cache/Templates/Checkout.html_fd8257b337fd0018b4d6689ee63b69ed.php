<?
	if( $data['PaymentOptions'] == NULL )
	{
		echo "<strong>{$data['NoChargeBlurb']}</strong>";
		return;
	}

	ss_paramKey($data,'ConfirmOrder',false);
?>
<div class="form-group">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2>Cart</h2>
				<p>Your order is shown below, to change a quantity of
							a product, enter the number and then press enter. To remove a product
							enter 0 as the quantity.</p>
				<!--basket-->
				<table width="100%" border="0" cellpadding="5" cellspacing="0" class="basket">
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
					<td class="onlineShopBasketSubTotal">Tracking:</td>
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
			</div>
			<?php if (ss_OptionExists('Shop Discount Codes') and ($data['ActiveDiscounts']>0)) { ?>
				<div class="col-md-12">
					<form action="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/ChangeDiscountCode/DoAction/Yes" method="post">
					<div class="container">
						<div class="row">
							<div class="col-md-12">
								<p>If you have a discount code, please enter it below.  The products in
										your cart will be updated based on the discount code entered.</p>
								<div class="form-group">
									<label class="control-label" for="input-code">Discount Code</label>
									<input type="text" name="DiscountCode" value="<?php print(ss_HTMLEditFormat($data['DiscountCode'])); ?>" placeholder="Discount Code" id="input-code" class="form-control">
								</div>
								<input type="hidden" name="BackURL" value="<?php echo $_SERVER['REQUEST_URI'];?>">
								<input TYPE="SUBMIT" VALUE="Submit" NAME="SUBBY_THE_SUBMIT" class="btn btn-primary">
							</div>
						</div>
					</div>
					</form>
				</div>
			<?php } ?>

			<div class="col-md-12">
				<?php if (count($data['Errors'])) { ?>
					<p><?php if (count($data['Errors']) != 0) {	$errorMessages = ''; foreach ($data['Errors'] as $messages) foreach ($messages as $message) $errorMessages .= "<LI>$message</LI>"; print('<P><TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD CLASS="entryErrors">Errors were detected in the data you entered, please correct the	following issues and re-submit. <UL>'.$errorMessages.'</UL></TD></TR></TABLE></P>'); } ?></p>
				<?php } ?>	
			</div>

			<!--END Returning Customers-->
			<!-- Customer Details -->
			<div class="col-md-12">
				<?php
				// count up their shipped orders older than say... 21 *7/5 days.
				$usID = ss_getUserID();
				$showShipping = false;
				//$QC = query( "select count(*) as previous from shopsystem_orders where or_shipped < NOW() - interval 30 day and or_us_id = $usID" );
				$QC = query( "select count(*) as previous from shopsystem_orders where or_shipped IS NOT NULL and or_us_id = $usID" );
				if( $r = $QC->fetchRow() )
				{
					if( $r['previous'] > 0 )
						$showShipping = true;
				}

				if( $showShipping )
				{
				?>
				<h2>Billing Details</h2>
				<p>For CREDIT CARD Orders: This MUST be the name and address of the credit card holder, as registered at your bank. Not entering the correct details will result in processing delays and perhaps cancellation.</p>
				<?php
				} else {
				?>
				<h2>Billing and Shipping Details</h2>
				<p>For CREDIT CARD Orders: First orders we will ONLY be shipped to the billing address that your bank holds on record. Please ensure that you enter the name, address, email and telephone number of the card holder.  Failure to do so will result in processing delays and perhaps cancellation.</p>
				<?php
				}
				?>
				<p>You will need to enter a password above to protect your account information and communicate with our support team. You can use this password above to retrieve your details when you return to shop with us again. If you have forgotten your password we can <strong><a href="index.php?act=Security.ForgotPassword&BackURL=<?php echo $_SERVER['REQUEST_URI'];?>">email you your password</a></strong></p>
			</div>
			<div class="col-md-12">
				<form name="CheckoutForm" action="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/<?php print(ss_HTMLEditFormat($data['Service'])); ?>/Do_Service/Yes" method="POST" accept-charset="ISO-8859-1">
					<input type="hidden" name="tr_id" value="<?php print(ss_HTMLEditFormat($data['tr_id'])); ?>">
					<input type="hidden" name="tr_token" value="<?php print(ss_HTMLEditFormat($data['tr_token'])); ?>">
					<?php print($data['PurchaserDetails']); ?>          
					<script language="Javascript">
							function sameAsPurchaser()
							{	
								var theForm = document.forms.CheckoutForm;
								var dontReload = (theForm.us_0_50A4_Parent.options[theForm.us_0_50A4_Parent.selectedIndex].value == theForm.us_0_50A4_Parent.options[theForm.us_0_50A4_Parent.selectedIndex].value);
								<?php 
									foreach ($data['ShippingFields'] as $aField) {
						/*								print ("//".get_class($aField)." class name\n");	*/
										$usFieldName = str_replace('ShDe','us_',$aField->name);
										
										if (strtolower( get_class($aField) ) == 'checkboxfield') {
											print("
												for (var i = 0; i < theForm.$usFieldName.length; i++) {
													if (theForm.$usFieldName[i].checked) {
														theForm.{$aField->name}[i].checked;
													}
												}"
											);
										} else if (strtolower( get_class($aField) ) == 'countryfield' or get_class($aField) == 'selectfromarrayfield' ) {
											print "var temp1 = theForm.{$usFieldName}.options[theForm.$usFieldName.selectedIndex].value;";																		
											print("										
												for (var i = 0; i < theForm.{$aField->name}.options.length; i++) {\n
													if (theForm.{$aField->name}.options[i].value == temp1) {\n												
														theForm.{$aField->name}.options[i].selected = true;\n																								 
														break;
													}										
												}
											");	
										} else if (strtolower(get_class($aField)) == 'parentchildrenfield') {
						/*									print("theForm.{$aField->name}.value = theForm.$usFieldName.value;");	*/
											print "var done = false;";
											print("
												var selectedValue = theForm.{$usFieldName}_Parent.options[theForm.{$usFieldName}_Parent.selectedIndex].value;										
												for (var i = 0; i < theForm.{$aField->name}_Parent.options.length; i++) {\n
													if (theForm.{$aField->name}_Parent.options[i].value == selectedValue) {\n
														theForm.{$aField->name}_Parent.options[i].selected = true;\n
														done = true;
														break;
													}
												}
												{$aField->name}_updateChild();{$aField->name}_checkParent(true);\n
												if (theForm.{$usFieldName}_ChildSelect.options.length) {
													var selectedParentValue = theForm.{$usFieldName}_ChildSelect.options[theForm.{$usFieldName}_ChildSelect.selectedIndex].value;\n
													
													for (var i = 0; i < theForm.{$aField->name}_ChildSelect.options.length; i++) {\n
														if (theForm.{$aField->name}_ChildSelect.options[i].value == selectedParentValue) {\n												
															theForm.{$aField->name}_ChildSelect.options[i].selected = true;\n																								 
															break;
														}
													}
												}										
												if( !done ) alert ('Unable to set shipping properly' );
											");										
											print("theForm.{$aField->name}_ChildText.value = theForm.{$usFieldName}_ChildText.value;\n");																		
											print("{$aField->name}_update();\n");
											print("{$usFieldName}_update();\n");																		


											
										} else if (strtolower( get_class($aField) ) == 'multiselectfromarrayfield') {
											print("
												for (var i = 0; i < theForm.$usFieldName.options.length; i++) {
													if (theForm.$usFieldName.options[i].selected) {
														theForm.{$aField->name}.options[i].selected = true;
													}
												}
											");	
										} else if (strtolower( get_class($aField) ) == 'namefield') {
											print("
												
												theForm['{$aField->name}[first_name]'].value = theForm['{$usFieldName}[first_name]'].value;
												theForm['{$aField->name}[last_name]'].value = theForm['{$usFieldName}[last_name]'].value;
											");					
										} else {									
											print("theForm.{$aField->name}.value = theForm.$usFieldName.value;");
										}
									}
								?>				

							if( !dontReload )
								{
								document.forms.CheckoutForm.action='Shop_System/Service/Checkout/Do_Service/Reload';
								document.forms.CheckoutForm.submit();
								}
							}

							function chooseState( state )
							{
								var theForm = document.forms.CheckoutForm;

								for (var i = 0; i < theForm.us_De0_50A4_ChildSelect.options.length; i++) {
							//		alert( "compare "+theForm.us_0_50A4_ChildSelect.options[i].value+" == "+state );
									if (theForm.us_0_50A4_ChildSelect.options[i].value == state) {
										theForm.us_0_50A4_ChildSelect.options[i].selected = true;
										break;
									}
								}
							}

							function sameAsSavedAddress( id )
							{
								var theForm = document.forms.CheckoutForm;
								<?php $tmpl_loop_rows = $data['OtherShippingDetails']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['OtherShippingDetails']->fetchRow()) { $tmpl_loop_counter++; ?>
								<?php
									$addr = unserialize( $row['ua_shipping_details'] );
									echo "if ( {$row['ua_id']} == id ) { \n";
									foreach( $addr as $name=>$val )
									{
										ss_log_message( "$name => $val" );
										$vale = str_replace( "'", "\\'", $val );
										switch( $name ) {
										case 'first_name':
											echo "theForm['ShDeName[first_name]'].value = '$vale';\n";
											break;
										case 'last_name':
											echo "theForm['ShDeName[last_name]'].value = '$vale';\n";
											break;
										case '0_50A4':
											$state_country = $vale;
											$pos = strpos( $state_country, "<BR>" );
											if( $pos )
											{
												$state = substr( $state_country, 0, $pos );
												$country = substr( $state_country, $pos + 4 );
											}
											else
											{
												$state = $state_country;
												$country = $state_country;
											}

											if( $state_id = getField( "select sts_id from country_states, countries where StCountryLink = cn_id and cn_name = '$country' and StCode = '$state'" ) )
												echo "chooseState( '$state_id' );us_0_50A4_update();\n";
											else
												echo "theForm['us_0_50A4_ChildText'].value = '$state';us_0_50A4_update();";
											break;
										case 'Email':
											$stripped = preg_replace( '/<.*>/U', '', $val );
											echo "theForm['ShDe$name'].value = '$stripped';\n";
											break;
										case 'Name':
											break;
										default :
											echo "theForm['ShDe$name'].value = '$vale';\n";
										}
									}
									echo "}\n";
								?>
								<?php } ?>	
							}

							function toggledivid( what )
							{
								toggler=document.getElementById('cat'+what);
								if( toggler.style.display == 'none' )
									 toggler.style.display = '';
								else
									 toggler.style.display = 'none';
							}
						</script>
						<?php
						if( $showShipping )
						{
						?>
						<div id='catSavedShippingDetails' style='display:none;' class='col-md-12'>
							<h2>Saved Shipping Details</h2><A style="display:" href="Javascript:toggledivid('SavedShippingDetails');toggledivid('NewShippingDetails');void(0);"
								class="morelink"> Enter new shipping details</A>
							<?php if ($data['OtherShippingDetails']->numRows()) { ?>
							<?php $tmpl_loop_rows = $data['OtherShippingDetails']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['OtherShippingDetails']->fetchRow()) { $tmpl_loop_counter++; ?>
							<div class='row'>
								<div class='col-md-12 addressChoice' id='div<?php print(ss_HTMLEditFormat($row['ua_id'])); ?>'>
									<script language="javascript" type="text/javascript">
										var s = document.getElementById('div<?php print(ss_HTMLEditFormat($row['ua_id'])); ?>');
										s.style.cursor = 'pointer';
										s.onclick = function() {
											var r = document.getElementById('radio<?php print(ss_HTMLEditFormat($row['ua_id'])); ?>');
											r.checked = true;
											sameAsSavedAddress(<?php print(ss_HTMLEditFormat($row['ua_id'])); ?>);
										};
									</script>
									<input type="radio" name="OldShippingDetails" id='radio<?php print(ss_HTMLEditFormat($row['ua_id'])); ?>' value="<?php print(ss_HTMLEditFormat($row['ua_id'])); ?>"
										onClick="sameAsSavedAddress(<?php print(ss_HTMLEditFormat($row['ua_id'])); ?>);">
									<?php 
									$addr = unserialize( $row['ua_shipping_details'] );
									echo "<strong>{$addr['Name']}</strong><br />{$addr['0_50A1']} {$addr['0_50A2']} {$addr['0_50A4']} {$addr['0_B4C0']}<br />
										<strong>Ph:</strong>{$addr['0_B4C1']}";
									?>
								</div>
							</div>
							<?php } ?>			
							<?php } else { ?>
							<p> No saved shipping details to <?=$_SESSION['ForceCountry']['cn_name']?> </p>
							<?php } ?>
						</div>


			<!--Shipping Details -->
			<div id='catNewShippingDetails' style='display:;'>
				<h2>New Shipping Details</h2>
				<p>Please enter your shipping details. If the shipping details are the same as the purchaser details, you may click the "same as purchaser" button to copy the details into the fields below.  <strong>We do not ship to APO boxes.</strong><br />
				<span class="textSubHeaders">New Shipping Details</span> <A style="display:" href="Javascript:toggledivid('SavedShippingDetails');toggledivid('NewShippingDetails');void(0);" class="morelink"> See saved shipping details from previous orders</A>
				<input type="button" name="same" value="Same As Purchaser" onClick="sameAsPurchaser();">
				<?php print($data['ShippingDetails']); ?>         
			</div>		
			<?php
				} else {	// don't show shipping, just hide the table
			?>
			<div id='shippingDetails' style='display:none;'>
				 <?php print($data['ShippingDetails']); ?> 
			</div>

			<?php
				}
				/*
			?>
			<!--Shipping Label -->
			<?php if (strlen($data['CountryShippingLabelText'])) { ?>
			<table width="100%" border="0" cellpadding="5" cellspacing="0" class="onlineShop_BasketTable Shipping_Warning" >

				<tr>
					<td><span class="textSubHeaders"><?php print($data['CountryShippingLabelText']); ?></span></td>
				</tr>
				<tr>
					<td valign="top">							
							<textarea id=shippingLabel name=shippingLabel wrap=SOFT tabindex=0 dir="ltr" spellcheck="false" autocapitalize="off" autocomplete="off" autocorrect="off" >
							<?php print($data['ShippingLabel']); ?>
							</textarea>
					</td>
				</tr>
				</table>
			<?php } ?>

			<!--Gift Message Details 
				

			<?php if (ss_OptionExists('Gift Message')) { ?>
			<table border="0" cellpadding="5" cellspacing="0" class="onlineShop_BasketTable Gift_Message" width="100%" >

					<tr>
						<td><span class="textSubHeaders">Special Instructions</span></td>
					</tr>
					<tr>
						<td>
									 If you have any special instructions regarding your order, please enter them below, but note this<br />
									 1) Note that for new customers, we do not allow shipping to an alternate location.<br />
									 2) We do not open boxes for inspection.<br />
									 3) The packing team cannot write notes on the outside of the package.<br />
									 4) Leaving a note here will cause your shipment to be delayed by at least 24 hours.
									 5) DO NOT PUT ANY address details here.  They will potentially be ignored.
									 
									<TEXTAREA NAME="GiftMessage" STYLE="width:100%" ROWS="6" COLS="40"><?php print(ss_HTMLEditFormat($data['GiftMessage'])); ?></TEXTAREA>
						</td>
					</tr>
					</table>
			<?php } ?>	
			-END Gift Message Details -->
			<?php */ ?>

			<?php
			if( ss_isGuest() )
			{
				?>
				<div class='row'>
					<div class="col-md-12">
						<h3>Over 18 years old</h3>
					</div>
				</div>
				<div class='row'>
					<div class="col-xs-11 col-md-11">
						<p class="text-right">I confirm that I am 18 years of age or over.</p>
					</div>
					<div class="col-xs-1 col-md-1">
						<input type='checkbox' name='over_18' value='1' />
					</div>
				</div>
				<?php
			}
			?>
			<!--Shipping warning -->
			<?php if (strlen($data['CountryWarning'])) { ?>
				<div class='row'>
					<div class="col-md-12">
						<h3>Shipping to your country</h3>
					</div>
				</div>
				<div class='row'>
					<div class="col-md-1">
					</div>
					<div class="col-md-11">
						<p><?php print($data['CountryWarning']); ?></p>
					</div>
				</div>
				<div class='row'>
					<div class="col-xs-11 col-md-11">
						<p class="text-right">I have read this and understand</p>
					</div>
					<div class="col-xs-1 col-md-1">
						<input type='checkbox' name='have_read' value='1' />
					</div>
				</div>
			<?php } ?>

			<?php if (strlen(strip_tags($data['VendorCustomerNotes']))) { ?>
				<div class='row'>
					<div class="col-md-12">
						<h3>Notes about the products in your basket</h3>
					</div>
				</div>
				<div class='row'>
					<div class="col-md-1">
					</div>
					<div class="col-md-11">
						<p><?php print($data['VendorCustomerNotes']); ?></p>
					</div>
				</div>
				<div class='row'>
					<div class="col-xs-11 col-md-11">
						<p class="text-right">I have read this and understand</p>
					</div>
					<div class="col-xs-1 col-md-1">
						<input type='checkbox' name='have_read2' value='1' />
					</div>
				</div>
			<?php } ?>

			<div class='row'>
				<div class="col-md-12">
					<h3>Terms and Conditions</h3>
				</div>
			</div>
			<div class='row'>
				<div class="col-md-1">
				</div>
				<div class="col-md-11">
					<p>I have read and accept the <a href='/Acme%20Express/Terms%20and%20Conditions' target='_blank'><strong><font color='black'>Terms and Conditions<font></strong></a> and acknowledge that I have entered a correct and secure delivery address.</p>
					<p>I authorise the operators of this website to store my entered information for the purposes of fullfilling this order</p>
				</div>
			</div>
			<div class='row'>
				<div class="col-xs-11 col-md-11">
					<p class="text-right">I have read this and understand</p>
				</div>
				<div class="col-xs-1 col-md-1">
					<input type='checkbox' name='tandc' value='1' />
				</div>
			</div>
		</div>
		<script language="Javascript">
			function check_warning( )
			{
				var theForm = document.forms.CheckoutForm;
		<?php if( !$showShipping ) { ?>
		<?php if( $data['Basket']['Freight']['Amount'] > 0 ) { ?>
				if( theForm.us_0_50A4_Parent.options[theForm.us_0_50A4_Parent.selectedIndex].value != theForm.us_0_50A4_Parent.options[theForm.us_0_50A4_Parent.selectedIndex].value )
					{
					alert( "Please recheck the shipping amount before proceeding" );
					sameAsPurchaser();
					theForm.action='Shop_System/Service/Checkout/Do_Service/Reload';
					theForm.submit();
					}
				else
					sameAsPurchaser();
		<?php } else { ?>
					sameAsPurchaser();
		<?php } ?>
		<?php } 

			if( array_key_exists( 'User', $_SESSION )
			 && array_key_exists( 'us_first_name', $_SESSION['User'] )
			 && ($_SESSION['User']['us_first_name'] == 'Guest' ) )
			{
			?>

				if( !theForm.over_18.checked )
				{
					alert( "Please indicate you are over 18 before proceeding" );
					return;
				}
			<?php
			}
			?>

			if( !theForm.tandc.checked )
			{
				alert( "Please accept our Terms and Conditions before proceeding" );
				return;
			}

			<?php
		?>
			<?php if (strlen($data['CountryWarning'])) { ?>
				<?php if (strlen(strip_tags($data['VendorCustomerNotes']))) { ?>
					if( theForm.have_read.checked && theForm.have_read2.checked )
						theForm.submit();
					else
						alert( "Please indicate you have read the notes for your country and products" );
				<?php } else { ?>
					if( theForm.have_read.checked )
						theForm.submit();
					else
						alert( "Please indicate you have read the notes for your country" );
				<?php } ?>
			<?php } else { ?>
				<?php if (strlen(strip_tags($data['VendorCustomerNotes']))) { ?>
					if( theForm.have_read2.checked )
						theForm.submit();
					else
						alert( "Please indicate you have read the notes for your products" );
				<?php } else { ?>
					theForm.submit();
				<?php } ?>
			<?php } ?>
				}
				</script>

			<?php
			if( $data['PaymentOptions'] == NULL )
				echo "<p>{$data['NoChargeBlurb']}</p>";
			else {
			?>
			<!-- Payment options -->
			<div class='row' id="PaymentOption">
				<div class='col-xs-8 col-md-8'>
					<?php 
					if( array_key_exists( 'User', $_SESSION )
					 && array_key_exists( 'us_first_name', $_SESSION['User'] )
					 && ($_SESSION['User']['us_first_name'] == 'Guest' ) ) {
					?>
					<p>Click here to go to our payment processor to pay for your order ------&gt;</p>
					<? } else if( $data['NeedsPayment'] ) { ?>
					<p>Click here to go to <?= $data['PaymentOptions']['pg_description'] ?> to pay for your order ------&gt;</p>
					<? } else { ?>
					<p>Checkout using account credit of <?=-($_SESSION['Shop']['Basket']['Discounts']['Account Credit'])?> ------&gt;</p>
					<? } ?>
				</div>
				<div class="col-xs-4 col-md-4 pull-left">
					<span class="product-block">
						<span class="cart">
							<button class="btn btn-cart" title="Pay" type="button" onclick="check_warning(0); return false;">Pay</button>
						</span>
						</span>
				</div>
			</div>
			<?php } ?>
			</form>

			<script language="javascript" type="text/javascript">
				var hawaiiIndex = null;
				for(var temp = 0; temp < parents_us_0_50A4[840].length; temp++) {
					if (parents_us_0_50A4[840][temp].id == 17) {
						parents_us_0_50A4[840][temp].id = '';
						parents_us_0_50A4[840][temp].name = '';
						break;
					}
				}
				//delete(parents_us_0_50A4[840][hawaiiIndex]);
			</script>
		</div>
	</div>
</div>
