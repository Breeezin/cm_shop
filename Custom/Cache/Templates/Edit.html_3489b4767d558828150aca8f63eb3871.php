<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="bodytext">
 	<tr>
 		<td width="213" align="left" valign="top"><table width="213" border="0" cellspacing="0" cellpadding="0">
 				<tr>
 					<td colspan="2"><?php if (ss_optionExists('Sell Products') ) { ?><img src="Images/welcome-shop.gif" width="250" height="125"><?php } else { ?><img src="Images/welcome-pc.gif" width="250" height="125"><?php } ?></td>
		 </tr>
 				<tr>
 					<td width="178" align="center" valign="middle">
					<?php if (ss_optionExists('Sell Products') ) { ?>
						<?php 
						$accessCode = "";
						if (array_key_exists('AccessCode', $_SESSION)) {
							$accessCode = "&AccessCode=".$_SESSION['AccessCode'];
						}
						?>
						<a href="javascript:void(0);" onclick="res=window.open('<?php print(ss_HTMLEditFormat($data['SecureSite'])); ?>index.php?act=WebPayAdministration.List&as_id=&as_id=<?php print(ss_HTMLEditFormat($data['as_id'])); ?><?=$accessCode?>','OrderManager','width=760,height=480,scrollbars=yes,menubar=yes,resizable=yes');res.focus();return false;"><img src="Images/but-orders.gif" width="115" height="113" border="0" alt="Manage Orders"></a>
						<?php } ?>						
 					</td>
 					<td width="72" height="172" background="Images/welcome-right-bot1.gif">&nbsp;</td>
		 </tr>
 				</table>
	 </td>
 		<td align="left" valign="top"><img src="Images/holder.gif" width="25" height="15">
    			<table width="100%" border="0" cellspacing="0" cellpadding="0">
                	<tr>
                		<td><img src="Images/h-products.gif" width="172" height="26"></td>
           		 </tr>
                	<tr>
                		<td align="left" valign="top">
							<?php if (ss_optionExists('Sell Products') ) { ?>
								<?php print("<p><a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_ProductsAdministration.List&as_id={$data['as_id']}','Manage Products','OnlineShopProductsManager');return false;\" class=\"bodytextBlue\">Click here to edit the products found in your shop.</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>"); ?>                		
							<?php } else { ?>
								<?php print("<p><a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_ProductsAdministration.List&as_id={$data['as_id']}','Manage Products','OnlineShopProductsManager');return false;\" class=\"bodytextBlue\">Click here to edit the products found in your product catalogue.</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>"); ?>                		
							<?php } ?>			
           			 </td>
           		 </tr>
                	<tr>
                		<td><img src="Images/h-categories.gif" width="172" height="26"></td>
           		 </tr>
                	<tr>
                		<td align="left" valign="top">
							<?php print("<p><a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_CategoriesAdministration.List&as_id={$data['as_id']}','Manage Categories','OnlineShopCategoriesManager');return false;\" class=\"bodytextBlue\">Click here to edit the categories that your products will be divided into.</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>"); ?>
           			 </td>
           		 </tr>
                	<tr>
                		<td><img src="Images/h-config.gif" width="172" height="26"></td>
           		 </tr>
                	<tr>
                		<td align="left" valign="top"><a class="bodytextBlue" href="javascript:var el=document.getElementById('ConfigureShop');if (el.style.display == '') { el.style.display='none';document.getElementById('ClickHere').style.display=''; document.getElementById('PleaseSelect').style.display='none';} else { el.style.display='';document.getElementById('ClickHere').style.display='none'; document.getElementById('PleaseSelect').style.display=''; }void(0);">To configure your <?php if (ss_optionExists('Sell Products') ) { ?>shop<?php } else { ?>product catalogue<?php } ?>, <span id="ClickHere">click here.</span><span id="PleaseSelect" style="display:none;">please select from the options below:</span></a> <img src="Images/go-arrow.gif"><br />
                		<div id="ConfigureShop" style="display:none;">
        			            <table border="0" cellspacing="0" cellpadding="7">
					<?php if (ss_optionExists('Shop Gallery') ) { ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('ShopGallery');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Manage Shop Gallery</a>"); ?></td>
        				</tr>
        			<?php } ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('ProductAttributes');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Define Product Attributes</a>"); ?></td>
   					 </tr>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('ProductOptions');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Define Product Options</a>"); ?></td>
   					 </tr>
   					<?php if (ss_optionExists('Shop Product Features')) { ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot">
								<?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=shopsystem_features_administration.List&as_id={$data['as_id']}','Manage Product Feature','OnlineShopFeatures');return false;\" class=\"bodytextGrey\">Manage Product Features</a>"); ?>
        					</td>
   					 </tr>   					 
   					 <?php } ?>
					<?php if (ss_optionExists('Sell Products') ) { ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('NotificationSettings');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Notification Settings</a>"); ?></td>
   					 </tr>
        			<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('ThankYouPageSettings');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Edit Thank You Page</a>"); ?></td>
   					 </tr>
   					 <tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('InvoicingSettings');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Invoicing Settings</a>"); ?></td>
   					 </tr>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('CustomerUserGroups');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Customer User Groups</a>"); ?></td>
   					 </tr>
   					 <?php } ?> 
        				<tr>
        					<td width="10">&nbsp;</td>
        					<td width="11"><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot">
								<?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_TaxZonesAdministration.List&as_id={$data['as_id']}','Manage Tax Zones','OnlineShopTaxZonesManager');return false;\" class=\"bodytextGrey\">Manage Tax Zones</a>"); ?>        					
							</td>
   					 </tr>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot">
								<?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_TaxZonesAdministration.Configure&as_id={$data['as_id']}','Manage Tax Rates','OnlineShopTaxRatesManager');return false;\" class=\"bodytextGrey\">Manage Tax Rates</a>"); ?>        					
							</td>
   					 </tr>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot">
								<?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=shopsystem_categories.AttributesSetting&as_id={$data['as_id']}','Manage Category Product Attributes','OnlineShopCategoriesAttributesSetting');return false;\" class=\"bodytextGrey\">Manage Category Product Attributes</a>"); ?>
        					</td>
   					 </tr>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot">
								<?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=shopsystem_categories.AttributesSetting&as_id={$data['as_id']}&Type=Options','Manage Category Product Options','OnlineShopCategoriesOptionsSetting');return false;\" class=\"bodytextGrey\">Manage Category Product Options</a>"); ?>
        					</td>
   					 </tr>
					<?php if (ss_optionExists('Shop Discount Codes') ) { ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_DiscountGroupsAdministration.List&as_id={$data['as_id']}','Manage Discount Groups','OnlineShopDiscountGroups');return false;\" class=\"bodytextGrey\">Manage Discount Groups</a>"); ?></td>
        					</tr>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_DiscountCodesAdministration.List&as_id={$data['as_id']}','Manage Discount Codes','OnlineShopDiscountCodes');return false;\" class=\"bodytextGrey\">Manage Discount Codes</a>"); ?></td>
        					</tr>
        			<?php } ?>
                    <?php if (ss_optionExists('Shop Non-NZD Currencies') ) { ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('CurrencySettings');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Currency Settings</a>"); ?></td>
   					 </tr>
        			<?php } ?>
					<?php if (ss_optionExists('Shop Quick Order List') ) { ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot">
								<?php print("<a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=ShopSystem_QuickOrderCategoriesAdministration.List&as_id={$data['as_id']}','Manage Quick Order Categories','OnlineShopQuickOrderCategories');return false;\" class=\"bodytextGrey\">Manage Quick Order Categories</a>"); ?>        					
        					</td>
   					 </tr>
        			<?php } ?>
   					 <tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('SEOSettings');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Search Engine Optimisation Settings</a>"); ?></td>
   					 </tr>
   					 <?php if (ss_optionExists('Shop Acme Rockets')) { ?>
        				<tr>
        					<td>&nbsp;</td>
        					<td><img src="Images/arrow-left.gif" width="11" height="5"></td>
        					<td class="bottomDot"><?php print("<a href=\"javascript:var el=document.getElementById('PointsPercentages');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\" class=\"bodytextGrey\">Points Percentages</a>"); ?></td>
   					 	</tr>   		
   					 <?php } ?>

        				</table></div>
           			 </td>
           		 </tr>
          	</table></td>
	</tr>
</table>
<?php if (ss_optionExists('Shop Gallery') ) { ?>
<div id="ShopGallery" style="display:none;">
<FIELDSET TITLE="ShopGallery">
	<LEGEND>Shop Gallery</LEGEND>
<table>
    <TR>
    	<TD>Gallery Page Title :</TD>
        <TD><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_GALLERY_PAGE_TITLE'); ?></TD>
    </TR>
    <TR>
    	<TD>Thumbnail Image Width :</TD>
        <TD><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_GALLERY_THUMBNAIL_WIDTH'); ?></TD>
    </TR>
    <TR>
    	<TD>Thumbnail Image Height :</TD>
        <TD><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_GALLERY_THUMBNAIL_HEIGHT'); ?></TD>
    </TR>
    <TR>
    	<TD>Images Per Row :</TD>
        <TD><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_GALLERY_IMAGES_PER_ROW'); ?></TD>
    </TR>
    <TR>
    	<TD>Rows Per Page :<BR><SMALL>(Leave blank to display all gallery images on a single page)</SMALL></TD>
        <TD><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_GALLERY_ROWS_PER_PAGE'); ?></TD>
    </TR>
    <TR>
    	<TD>Popup Width :</TD>
        <TD><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_GALLERY_POPUP_WIDTH'); ?></TD>
    </TR>
    <TR>
    	<TD>Popup Height :</TD>
    	<TD><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_GALLERY_POPUP_HEIGHT'); ?></TD>
    </TR>
</table>
</FIELDSET>
</div>
<?php } ?>
<div id="ProductAttributes" style="display:none;">
<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_ATTRIBUTES'); ?>
</div>
<div id="ProductOptions" style="display:none;">
<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_PRODUCT_OPTIONS'); ?>
</div>
<?php if (ss_optionExists('Sell Products') ) { ?>
<div id="NotificationSettings" style="display:none;">
<FIELDSET TITLE="Notification">
	<LEGEND>Notification</LEGEND>
	<TABLE CELLPADDING="2">
		<TR><TH ALIGN="LEFT">Shop Email Address :</TH>			
			<TD VALIGN="MIDDLE">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_ADMINEMAIL'); ?>								
			</TD>
		</TR>
<?php 
	$webpaySetting = ss_getWebPaymentConfiguration();
	if ($webpaySetting['UseCheque']) {
?>
		<TR><TH ALIGN="LEFT" valign="top">Cheque Payment - Client Email Body :</TH>			
			<TD VALIGN="top">
				You may use the following codes..<BR>
					<TABLE BORDER="0">
						<TR><TD>[OrderNumber]       </TD><TD>= Order Number</TD></TR>
						<TR><TD>[P.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from purchaser details</TD></TR>
						<TR><TD>[S.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from shipping details</TD></TR>
						<TR><TD>[OrderDetails]      </TD><TD>= Details of products ordered</TD></TR>
						<TR><TD>[Address]           </TD><TD>= Address to send cheque to</TD></TR>
						<TR><TD>[PayableTo]         </TD><TD>= Name to make cheque payable to</TD></TR>
						<TR><TD>[TotalCharge]       </TD><TD>= Total charge for the order</TD></TR>						
					</TABLE>
					<P>
						If you would like to reset this to the default email for cheque orders, click this button:
						<INPUT CLASS="adminButtons" TYPE="BUTTON" ONCLICK="HTMLArea_AST_SHOPSYSTEM_CLIENT_CHEQUEEMAIL.setHTML('<?php print(ss_JSStringFormat($data['DefaultEmailCheque'])); ?>')" VALUE="Use Default Email">
					</P>
			</TD>
		</TR>
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_CHEQUEEMAIL'); ?>								
			</TD>
		</TR>
<?php 
	} 
	if ($webpaySetting['UseDirect']) {
?>
	<TR><TH ALIGN="LEFT" valign="top">Direct Payment - Client Email Body :</TH>
			<TD VALIGN="top">
				You may use the following codes..<BR>
					<TABLE BORDER="0">
						<TR><TD>[OrderNumber]       </TD><TD>= Order Number</TD></TR>
						<TR><TD>[P.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from purchaser details</TD></TR>
						<TR><TD>[S.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from shipping details</TD></TR>
						<TR><TD>[OrderDetails]      </TD><TD>= Details of products ordered</TD></TR>
						<TR><TD>[AccountName]           </TD><TD>= Account Name</TD></TR>
						<TR><TD>[AccountNumber]         </TD><TD>= Account Number</TD></TR>
						<TR><TD>[AccountNote]         </TD><TD>= Note</TD></TR>
						<TR><TD>[TotalCharge]       </TD><TD>= Total charge for the order</TD></TR>						
					</TABLE>
					<P>
						If you would like to reset this to the default email for direct payment orders, click this button:
						<INPUT CLASS="adminButtons" TYPE="BUTTON" ONCLICK="HTMLArea_AST_SHOPSYSTEM_CLIENT_DIRECTEMAIL.setHTML('<?php print(ss_JSStringFormat($data['DefaultEmailDirect'])); ?>')" VALUE="Use Default Email">
					</P>
			</TD>
		</TR>
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_DIRECTEMAIL'); ?>
			</TD>
		</TR>
<?php
	}
    if ($webpaySetting['UseInvoice']) {
?>
	<TR><TH ALIGN="LEFT" valign="top">Invoice Payment - Client Email Body :</TH>
			<TD VALIGN="top">
				You may use the following codes..<BR>
					<TABLE BORDER="0">
						<TR><TD>[OrderNumber]       </TD><TD>= Order Number</TD></TR>
						<TR><TD>[P.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from purchaser details</TD></TR>
						<TR><TD>[S.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from shipping details</TD></TR>
						<TR><TD>[OrderDetails]      </TD><TD>= Details of products ordered</TD></TR>
						<TR><TD>[TotalCharge]       </TD><TD>= Total charge for the order</TD></TR>
					</TABLE>
					<P>
						If you would like to reset this to the default email for invoice payment orders, click this button:
						<INPUT CLASS="adminButtons" TYPE="BUTTON" ONCLICK="HTMLArea_AST_SHOPSYSTEM_CLIENT_INVOICEEMAIL.setHTML('<?php print(ss_JSStringFormat($data['DefaultEmailInvoice'])); ?>')" VALUE="Use Default Email">
					</P>
			</TD>
		</TR>
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_INVOICEEMAIL'); ?>
			</TD>
		</TR>
<?php
	}
	if ($webpaySetting['UseCreditCard']) {
?>
		<TR>
			<TH ALIGN="LEFT" valign="top"><?=$webpaySetting['CreditCardSetting']['ProcessorDisplayName']?> Transaction<BR> Client Email Body :</TH>
			<TD>You may use the following codes..<BR>
					<TABLE BORDER="0">
						<TR><TD>[OrderNumber]       </TD><TD>= Order Number</TD></TR>
						<TR><TD>[P.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from purchaser details</TD></TR>
						<TR><TD>[S.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from shipping details</TD></TR>
						<TR><TD>[OrderDetails]      </TD><TD>= Details of products ordered</TD></TR>
						<TR><TD>[TotalCharge]       </TD><TD>= Total charge for the order</TD></TR>
					</TABLE>
					<P>
						If you would like to reset this to the default email for credit card orders, click this button:
						<INPUT CLASS="adminButtons" TYPE="BUTTON" ONCLICK="HTMLArea_AST_SHOPSYSTEM_CLIENT_CREDITCARDEMAIL.setHTML('<?php print(ss_JSStringFormat($data['DefaultEmailCredit'])); ?>')" VALUE="Use Default Email">
					</P>
			</TD>						
		</TR>		
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_CREDITCARDEMAIL'); ?>								
			</TD>
		</TR>

		<TR>
			<TH ALIGN="LEFT" valign="top"><?=$webpaySetting['CreditCardSetting']['ProcessorDisplayName']?> Transaction<BR> Overspend <br /> Email Body :</TH>
			<TD>You may use the same codes as above..<BR>
			</TD>						
		</TR>		
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_NEW_OVERSPEND_EMAIL'); ?>								
			</TD>
		</TR>

		<TR>
			<TH ALIGN="LEFT" valign="top"><?=$webpaySetting['CreditCardSetting']['ProcessorDisplayName']?> Transaction<BR> Shipping Not Billing<br /> Email Body :</TH>
			<TD>You may use the same codes as above..<BR>
			</TD>						
		</TR>		
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_NEW_SHIPBILLDIFF_EMAIL'); ?>								
			</TD>
		</TR>
		<TR>
			<TH ALIGN="LEFT" valign="top"><?=$webpaySetting['CreditCardSetting']['ProcessorDisplayName']?> Transaction<BR> Orders too close together<br /> Email Body :</TH>
			<TD>You may use the same codes as above..<BR>
			</TD>						
		</TR>		
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_NEW_CLOSE_ORDER_EMAIL'); ?>								
			</TD>
		</TR>
		<TR>
			<TH ALIGN="LEFT" valign="top"><?=$webpaySetting['CreditCardSetting']['ProcessorDisplayName']?> Transaction<BR> Card Charged<br /> Email Body :</TH>
			<TD>You may use the same codes as above..<BR>
			</TD>						
		</TR>		
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_CARDCHARGED_EMAIL'); ?>								
			</TD>
		</TR>		<TR>
			<TH ALIGN="LEFT" valign="top"><?=$webpaySetting['CreditCardSetting']['ProcessorDisplayName']?> Transaction<BR> Card Denied<br /> Email Body :</TH>
			<TD>You may use the same codes as above..<BR>
			</TD>						
		</TR>		
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_CARDDENIED_EMAIL'); ?>								
			</TD>
		</TR>


<?php 
	} 
?>
<?php 
	
	if (ss_optionExists('Shop Order Confirmation Email')) {
?>
		<TR>
			<TH ALIGN="LEFT" valign="top">Order Confirmation Email :</TH>			
			<TD>This email is sent when the order has been paid. You may use the following codes..<BR>
					<TABLE BORDER="0">
						<TR><TD>[OrderNumber]       </TD><TD>= Order Number</TD></TR>
						<TR><TD>[P.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from purchaser details</TD></TR>
						<TR><TD>[S.<I>FieldName</I>]</TD><TD>= <I>FieldName</I> from shipping details</TD></TR>
						<TR><TD>[SpecialNote]		</TD><TD>= A special note added to the confirmation email</TD></TR>
					</TABLE>
				If you would like a copy of the confirmation email sent to the shop admin, please tick this box: <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_SEND_CONFIRMATION_CC'); ?>					
			</TD>						
		</TR>
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CLIENT_CONFIRMATION_EMAIL'); ?>								
			</TD>
		</TR>
<?php 
	} 
?>
<?php 
	
	if (ss_optionExists('Shop Product Stock Notifications')) {
?>
		<TR>
			<TH ALIGN="LEFT" valign="top">Product Back In Stock Email:</TH>			
			<TD>This email is sent when a product that was out of stock has come back in stock again.  You may use the following codes..<BR>
				<TABLE BORDER="0">
					<TR><TD>[first_name]	</TD><TD>= The first name of the customer</TD>
					<TR><TD>[ProductName]       </TD><TD>= Name of the product</TD></TR>
					<TR><TD>[BoxCode]       </TD><TD>= Code of the product</TD></TR>
					<TR><TD>[Price]       </TD><TD>= Price of the product</TD></TR>
				</TABLE>
			</TD>						
		</TR>
		<TR>
			<TD>&nbsp;</TD>
			<TD VALIGN="top">
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_STOCK_NOTIFICATION_EMAIL'); ?>								
			</TD>
		</TR>
<?php 
	} 
?>
	</TABLE>
</FIELDSET>
</div>
<?php 
if (ss_optionExists('Shop Non-NZD Currencies')) {
?>
<div id="CurrencySettings" style="display:none;">
<FIELDSET TITLE="Currency Settings">
	<LEGEND>Currency Setting</LEGEND>
	<TABLE CELLPADDING="2" cellpadding="0" cellspacing="0" align="center">		
		<TR>			
			<TD>
				Enter prices in <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_ENTER_CURRENCY'); ?> with <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_ENTER_CURRENCY_SYMBOL'); ?> appearing <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_ENTER_CURRENCY_SYMBOL_POS'); ?>
			</TD>
		</TR>	
		<TR>			
			<TD>
				Display prices in <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_DISPLAY_CURRENCY'); ?> with <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_DISPLAY_CURRENCY_SYMBOL'); ?> appearing <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_DISPLAY_CURRENCY_SYMBOL_POS'); ?>
			</TD>
		</TR>	
</TABLE>
</FIELDSET>
</div>
<?php } ?>
<div id="ThankYouPageSettings" style="display:none;">
<FIELDSET TITLE="Edit Thank You Page" STYLE="height: 200px">
	<LEGEND>Thank You Page</LEGEND>
	<TABLE CELLPADDING="2" cellpadding="0" cellspacing="0" align="center">		
		<TR>			
			<TD>
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_THANKYOU_CONTENT'); ?>								
			</TD>
		</TR>	
</TABLE>
</FIELDSET>
</div>
<div id="InvoicingSettings" style="display:none;">
<FIELDSET TITLE="Invoicing" STYLE="height: 160px">
	<LEGEND>Order/Invoice</LEGEND>
	<TABLE CELLPADDING="0">
		<TR>
			<TH ALIGN="LEFT" VALIGN="TOP" width="25%">Shipping Detail Fields :</TH>
			<TH ALIGN="LEFT" VALIGN="TOP" width="25%">Required Shippping Detail Fields :</TH>
<?php
    if (ss_optionExists('Shop Customer Select Fields')) {
        echo '<TH ALIGN="LEFT" VALIGN="TOP" width="25%">Customer Fields :</TH>';
    }
?>
			<TH ALIGN="LEFT" VALIGN="TOP" width="25%">Required Customer Fields :</TH>
		</TR>
		<TR valign="top">
			<TD>
			Please select the User details that form the shipping details of a customer.<BR>
			</TD>
			<TD>
			Please select the User details that should be forced as required for the shipping details of a customer.<BR>
			</TD>
<?php if (ss_optionExists('Shop Customer Select Fields')) {  ?>
			<TD>
			Please select the User details that form the saved details of a customer.<BR>
			</TD>
<?php } ?>
			<TD>
			Please select the User details that should be forced as required for a customers details.<BR>
			</TD>
		</TR>
		<TR valign="top">
			<TD>
			<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_ADDRESSFIELDS'); ?>
			</TD>
			<TD>
			<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_SHIPPING_REQUIREDFIELDS'); ?>
			</TD>
<?php if (ss_optionExists('Shop Customer Select Fields')) {  ?>
			<TD>
			<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CUSTOMER_FIELDS'); ?>
			</TD>
<?php } ?>
			<TD>
			<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_REQUIREDFIELDS'); ?>
			</TD>
		</TR>
        <tr>
            <td colspan="4">
                <strong>Invoice Thank You Note :</strong><BR>
                Note: This will be displayed at the bottom of your invoices.<BR>
                <?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_THANKYOUNOTE'); ?>
			</TD>
		</TR>
		<?php if (ss_OptionExists('Shop Advanced Ordering')) { ?>
		<TR>
			<TH ALIGN="LEFT" VALIGN="TOP" width="35%">Supplier discount:</TH>
		</TR>
		<TR>
			<TD>

			<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_SUPPLIER_DISCOUNT'); ?>%
			</TD>
		</TR>
		<?php } ?>
</TABLE>
</FIELDSET>
</div>
<div id="CustomerUserGroups" style="display:none;">
<FIELDSET TITLE="Customer User Groups">
	<LEGEND>Customer User Groups</LEGEND>
	<TABLE CELLPADDING="2">
		<TR>
			<TH ALIGN="LEFT" VALIGN="TOP" width="35%">User Groups: </TH>
			<TH ALIGN="LEFT" VALIGN="TOP">Checkout Newsletter Registration Field: </TH>			
		</TR>
		<TR>
			
			<TD valign="top">
			Please select the user groups that customers will be addded to after placing an order.<BR>
			<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CUSTOMER_USERGROUPS'); ?>					
			</TD>			
			<TD valign="top">				
				Please leave this field blank if you do not want to offer newsletter registration in the checkout page.<BR>
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_NEWSLETTER_QUESTION'); ?><BR>
				Please select the mailing lists offered in the checkout page.<BR>
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_NEWSLETTER_USERGROUPS'); ?>
			</TD>
		</TR>
		<? if (ss_optionExists('Shop Restricted Order Access')) { ?>
		<TR>
			
			<TD valign="top">
			Please select the user groups that will only have restricted access to orders.<BR>
			<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_RESTRICTED_ORDER_ACCESS_USERGROUPS'); ?>					
			</TD>			
		</TR>
		<? } ?>
</TABLE>
</FIELDSET>
</div>
<div id="SEOSettings" style="display:none;">
<FIELDSET TITLE="Search Engine Optimisation Settings">
	<LEGEND>Search Engine Optimisation Settings</LEGEND>
	<TABLE CELLPADDING="2">
		<TR><TH VALIGN="TOP" ALIGN="LEFT">Product Window Title Template :</TH>			
			<TD VALIGN="MIDDLE">
				Use [SiteName] and [Product] tags within the template to display their respective values. e.g: "[SiteName] - [Product]"<br />
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_PRODUCT_WINDOW_TITLE_TEMPLATE'); ?>								
			</TD>
		</TR>
		<TR><TH VALIGN="TOP" ALIGN="LEFT">Category Window Title Template :</TH>			
			<TD VALIGN="MIDDLE">
				Use [SiteName] and [Category] tags within the template to display their respective values. e.g: "[SiteName] - [Category]"<br />
				<?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_CATEGORY_WINDOW_TITLE_TEMPLATE'); ?>								
			</TD>
		</TR>
	</TABLE>
</FIELDSET>
</div>
<?php if (ss_optionExists('Shop Acme Rockets')) { ?>
<div id="PointsPercentages" style="display:none;">
<FIELDSET TITLE="Points Percentages">
	<LEGEND>Points Percentages</LEGEND>
	<p style="padding:5px;">
		Please enter the percentage of points that customers should receive.  E.g. 100% indicates that a customer should get 1 point per euro spent.
	</p>
	<TABLE CELLPADDING="2">
		<TR><TH VALIGN="TOP" ALIGN="LEFT">Frequent Flyer Program Customer :</TH>			
			<TD VALIGN="MIDDLE"><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_LEVEL0_PERCENTAGE'); ?>%</TD></TR>
		<TR><TH VALIGN="TOP" ALIGN="LEFT">Direct Referral Customer :</TH>			
			<TD VALIGN="MIDDLE"><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_LEVEL1_PERCENTAGE'); ?> points</TD></TR>
		<TR><TH VALIGN="TOP" ALIGN="LEFT">Level 2 Referral Customer :</TH>			
			<TD VALIGN="MIDDLE"><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_LEVEL2_PERCENTAGE'); ?> points</TD></TR>
		<TR><TH VALIGN="TOP" ALIGN="LEFT">Level 3 Referral Customer :</TH>			
			<TD VALIGN="MIDDLE"><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_LEVEL3_PERCENTAGE'); ?> points</TD></TR>
		<TR><TH VALIGN="TOP" ALIGN="LEFT">Level 4 Referral Customer :</TH>			
			<TD VALIGN="MIDDLE"><?php $data['FieldSet']->displayField('AST_SHOPSYSTEM_LEVEL4_PERCENTAGE'); ?> points</TD></TR>
	</TABLE>
</FIELDSET>
</div>
<?php } ?>
<?php } ?>
