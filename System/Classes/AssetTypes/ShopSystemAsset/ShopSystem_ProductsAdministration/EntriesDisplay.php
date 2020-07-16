<?php
	
	// set the title for the page
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'] . ' : ' . $this->plural;
	
	// Get the URL to view this page again (useful for breadcrumbs and to return)
	$backURL = $_SESSION['BackStack']->getURL();
	
	// Set globals for the template
	$script_name = basename($_SERVER['SCRIPT_NAME']);
	$rfa = $backURL;
	$numRows = $result->numRows();
	$hasRows = $result->numRows() > 0;
	$breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">'.$this->plural.'</A>';

	// Only include the parent hidden field if this administration has a parent
	$parentHiddenField = '';
	if (($this->parentTable != NULL) && array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
		$parentHiddenField = "<INPUT TYPE=\"HIDDEN\" NAME=\"{$this->parentTable->linkField}\" VALUE=\"{$this->ATTRIBUTES[$this->parentTable->linkField]}\">";
	}
	$maxNumProduct = ss_optionExists('Number of Products');
	
	if ($maxNumProduct === false) {
		$maxNumProduct = 100;
	}

	if ($totalProducts >= $maxNumProduct) {
		$this->hideNewButton = '<strong>The number of your website\'s products is exceeding its limit by ';
		$this->hideNewButton .= ($totalProducts-$maxNumProduct).'.</strong><BR>We recommend you to delete unused products.<BR>Also you may consider increasing your number of products allowance.';
	}
	
?>
<DIV ID="report"></DIV>


<!--- New record and Search --->
<TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">
	<TR>
		<td>
			<table>
				<tr>
					<?php
					if( ss_adminCapability( ADMIN_PRODUCT_ENTRY ) ) {
					?>
					<!--- button for adding a new record --->
					<FORM ACTION="<?php print $script_name?>" METHOD="POST">
						<INPUT NAME="BreadCrumbs" TYPE="HIDDEN" VALUE="<?php print ss_HTMLEditFormat($breadCrumbs) ?>">
						<INPUT TYPE="HIDDEN" NAME="BackURL" VALUE="<?php print $rfa ?>">
						<?php 
							if ($this->tableAssetLink !=null or $this->assetLink != null) {
								print('<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="'.$this->assetLink.'">');					
							}
						?>
						
						<TD ALIGN="LEFT" valign="baseline">
							<?php print $parentHiddenField;
							if (strlen($this->hideNewButton))
								print($this->hideNewButton);
							else { ?>
							<INPUT TYPE="HIDDEN" NAME="act" VALUE="<?php print $this->prefix?>Administration.New">
								<?			
								print '<INPUT TYPE="SUBMIT" NAME="SubmitButton" VALUE="New '.$this->singular.'">';						
							}
							?>
				
							
							
							
						</TD>
					
					</FORM>
					<?php } ?>
				</tr>
				<? if (ss_optionExists('Shop Acme Rockets')) { ?>
				<tr>
					<form action="index.php?act=ShopSystem.AcmeSpecialsTextEmail" method="post" target="_blank">
						<td><br>
							<p>
							<strong>Send Specials Text Email</strong>:<br>
							<input type="checkbox" name="Email1" value="macbjorck@mac.com"> macbjorck@mac.com<br>
							<input type="checkbox" name="Email2" value="admin@acmerockets.com"> admin@acmerockets.com<br>
							<tmpl_if condition="ss_isitus()">
								<input type="checkbox" name="Email3" value="acme@admin.com"> acme@admin.com<br>
							</tmpl_if>
							<input type="Submit" value="Send" name="Send">
							</p>
						</td>
					</form>
				</tr>
				<tr>
					<form name="priceChanger" onsubmit="return false;">
					<td>
						<br>
						<strong>Change Prices</strong>
						<table>
							<script language="Javascript">
								var stockCodes = Array();

							function decimalFormat(num) {
									var cents = parseFloat(num) - Math.floor(parseFloat(num));
									cents = Math.round(cents * 100);		
									cents = Math.floor(cents);		
									if (cents < 10) {
										cents = ".0" + cents;
									} else {
										cents = "." + cents;
									}
									var dollars = Math.floor(parseFloat(num));
									return dollars + cents;
								}								
								
								function changePrices(direction,what,amount,type) {
									amount = parseFloat(amount);
									if (isNaN(amount)) {
										alert('Please enter a valid amount to change prices by.');
										return;
									}
																		
									if (type == 'Euro') {
										if (direction == 'Lower') amount = 0-amount;
									} else {
										if (direction == 'Lower') {
											amount = (100-amount)/100;			
										} else {
											amount = (100+amount)/100;
										}
									}
									
									var theForm = document.forms.Records;
									var warn = false;
									if (what == 'All') {
										var fields = Array('pro_price','pro_special_price','pro_member_price');
									} else {
										var fields = Array(what);	
									}
									for (var j=0;j<fields.length;j++) {
										var field = fields[j];
										for (var i=0;i<theForm[field].length;i++) {
											var oldval = parseFloat(theForm[field][i].value);
											if (!isNaN(oldval)) {
												if (type == 'Euro') {
													var newval = decimalFormat(parseFloat(oldval)+amount);
												} else {
													var newval = decimalFormat(parseFloat(oldval)*amount);
												}
												if (newval <= 0) {
													if (oldval != 0) warn = true;
													newval = 0;
												}
												theForm[field][i].value = newval;
												updatePrice(newval,stockCodes[i],field,theForm[field][i]);
											}
										}
									}
									if (warn) alert('WARNING: One or more products are now free.');
									alert('Remember to click \'Update\' when you have finished changing prices.');
								}
							</script>
							<tr>
								<td><select name="change"><option value="Raise">Raise</option><option value="Lower">Lower</option></select></td>
								<td><select name="what"><option value="All">all</option><option value="pro_price">normal</option><option value="pro_special_price">special</option><option value="pro_member_price">member</option></select> prices by</td>
								<td><input type="text" style="text-align:right;" value="" name="amount" size="5"></td>
								<td><select name="type"><option value="Euro">euro</option><option value="Percent">percent</option></select></td>
								<td><input type="button" value="Go" name="Go" onclick="changePrices(this.form.change.options[this.form.change.selectedIndex].value,this.form.what.options[this.form.what.selectedIndex].value,this.form.amount.value,this.form.type.options[this.form.type.selectedIndex].value);"></td>
							</tr>
						</table>
					</td>
					</form>
				</tr>
				<? } ?>
			</table>
		</td>
		
		<?php 
			if (strlen($this->tableSortOrderField)) {				
		?>
						
		<FORM ACTION="<?php print $script_name?>" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="act" VALUE="<?php print $this->prefix?>Administration.Sorting">
			<INPUT NAME="BreadCrumbs" TYPE="HIDDEN" VALUE="<?php print ss_HTMLEditFormat($breadCrumbs) ?>">
			<INPUT TYPE="HIDDEN" NAME="BackURL" VALUE="<?php print $rfa ?>">
			<?php 
				print $parentHiddenField;
				if ($this->tableAssetLink !=null or $this->assetLink != null) {
					print('<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="'.$this->assetLink.'">');					
				}
			?>
			
		<TD ALIGN="LEFT" valign="baseline">
			<?php print '<INPUT TYPE="SUBMIT" NAME="SubmitButton2" VALUE="Modify Order">'; ?>
		</TD>
		</FORM>
		<?php } ?>
		<TD ALIGN="RIGHT" valign="baseline">
		<INPUT type="button" value="Ordering" name="Ordering" onclick="window.open('index.php?act=ShopSystem_ProductsAdministration.EOQ', 'EOQ');">
		<INPUT type="button" value="StockCheck" name="StockCheck" onclick="window.open('index.php?act=ShopSystem_ProductsAdministration.StockCheck', 'StockCheck');">
		</TD>
		<!--- Search Options Selector --->
		<FORM ACTION="<?php print $rfa ?>&CurrentPage=1" METHOD="POST">
			<TD ALIGN="RIGHT">
				<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
				<TR><TD>
				<TABLE WIDTH="100%"  CELLPADDING="2" CELLSPACING="0" BORDER="0">
					<TR>
					<TD ALIGN="LEFT">Search : </TD>
					<TD ALIGN="RIGHT"><INPUT SIZE="20" TYPE="TEXT" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['SearchKeyword']) ?>" NAME="SearchKeyword">&nbsp;</TD>
					</TR>
					<?php 
						$orderBys = array_merge($this->tableOrderBy, $this->tableSpecialOrderBy);
						$customOrderBys = array();
						//for advanced order by from proudct attributes
						if (ss_optionExists('Shop Advanced Product Manage')) {
							$customAttributes = ss_getAssetCereal($this->assetLink, 'AST_SHOPSYSTEM_ATTRIBUTES', true);
							foreach ($customAttributes as $aAtt) {
								ss_paramKey($aAtt, 'OrderBy', 0);							
								if ($aAtt['OrderBy'] == '1') {
									$customOrderBys['Pr'.$aAtt['uuid']] = $aAtt['name'];
								}									
							}
						}
						if (count($orderBys) > 1) {?>
					<TR>
					<TD ALIGN="LEFT">Order By : </TD>
					<TD ALIGN="RIGHT"><SELECT NAME="OrderBy">
						<?php 
							foreach ($this->tableOrderBy as $field => $name) {
								$selected = '';
								if ($this->ATTRIBUTES['OrderBy'] == $field) {
									$selected = 'Selected';
								}
								print "<OPTION VALUE=\"$field\" $selected>$name</OPTION>";
							}
							foreach ($this->tableSpecialOrderBy as $field => $name) {
								$selected = '';
								if ($this->ATTRIBUTES['OrderBy'] == $field) {
									$selected = 'Selected';
								}
								print "<OPTION VALUE=\"$field\" $selected>$name</OPTION>";
							}
							foreach ($customOrderBys as $field => $name) {
								$selected = '';
								if ($this->ATTRIBUTES['OrderBy'] == $field) {
									$selected = 'Selected';
								}
								print "<OPTION VALUE=\"$field\" $selected>$name</OPTION>";
							}
						?>
						</SELECT>&nbsp;
						<SELECT name="SortBy">
						<?php 
							$ascselected = '';
							$descselected = '';
							if ($this->ATTRIBUTES['SortBy'] == 'ASC') {
								$ascselected = 'Selected';
							} else if ($this->ATTRIBUTES['SortBy'] == 'DESC') {
								$descselected = 'selected';
							}
						?>
						<Option value="ASC" <?=$ascselected?>>Ascending</OPTION>
						<Option value="DESC" <?=$descselected?>>Descending</OPTION>
						</SELECT>
					</TD>
					</TR>
					<?php }?>
					<TR>
					<TD ALIGN="LEFT">Offline Filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="OfflineFilter">
							<OPTION <?php if ($this->ATTRIBUTES['OfflineFilter'] == 'All') print('SELECTED'); ?> VALUE="All">All</OPTION>
							<OPTION <?php if ($this->ATTRIBUTES['OfflineFilter'] == 'Off') print('SELECTED'); ?> VALUE="Off">Offline Only</OPTION>
							<OPTION <?php if ($this->ATTRIBUTES['OfflineFilter'] == 'On') print('SELECTED'); ?> VALUE="On">Online Only</OPTION>
						</SELECT>
					</TD>
					</TR>
					<TR>
					<TD ALIGN="LEFT">Upsell Filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="UpsellFilter">
							<OPTION <?php if ($this->ATTRIBUTES['UpsellFilter'] == 'All') print('SELECTED'); ?> VALUE="All">All</OPTION>
							<OPTION <?php if ($this->ATTRIBUTES['UpsellFilter'] == 'Off') print('SELECTED'); ?> VALUE="Off">Upsell Only</OPTION>
							<OPTION <?php if ($this->ATTRIBUTES['UpsellFilter'] == 'On') print('SELECTED'); ?> VALUE="On">NonUpsell Only</OPTION>
						</SELECT>
					</TD>
					</TR>
					<?php if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Product Stock Levels')) { ?>
					<TR>
					<TD ALIGN="LEFT">Stock Level Filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="StockLevelFilter">
							<OPTION <?php if ($this->ATTRIBUTES['StockLevelFilter'] == 'All') print('SELECTED'); ?> VALUE="All">All</OPTION>
							<? if (ss_optionExists('Shop Product Stock Levels')) { ?>
							<OPTION <?php if ($this->ATTRIBUTES['StockLevelFilter'] == 'InStock') print('SELECTED'); ?> VALUE="InStock">Limited Stock</OPTION>
							<? } ?>
							<OPTION <?php if ($this->ATTRIBUTES['StockLevelFilter'] == 'OutOfStock') print('SELECTED'); ?> VALUE="OutOfStock">Out of Stock</OPTION>
							<OPTION <?php if ($this->ATTRIBUTES['StockLevelFilter'] == 'Unspecified') print('SELECTED'); ?> VALUE="Unspecified">Unspecified</OPTION>
						</SELECT>
					</TD>
					</TR>
					<?php } ?>
					<TR>
					<TD ALIGN="LEFT">Category : </TD>
					<TD ALIGN="right">
						<SELECT NAME="CategoryFilter">
							<?
								foreach($categoryFilter as $name => $sql) {
							?>
								<OPTION <?php if ($this->ATTRIBUTES['CategoryFilter'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<TR>
					<TD ALIGN="LEFT">vendor : </TD>
					<TD ALIGN="right">
						<SELECT NAME="External">
							<?
								foreach($externalProduct as $name => $sql) {
							?>
								<OPTION <?php if ($this->ATTRIBUTES['External'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<TR>
					<TD ALIGN="LEFT">Specials Gateway : </TD>
					<TD ALIGN="right">
						<SELECT NAME="SpecialToGateway">
							<?
								foreach($specialToGateway as $name => $sql) {
							?>
								<OPTION <?php if (strlen( $name ) && $this->ATTRIBUTES['SpecialToGateway'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<TR>
					<TD ALIGN="LEFT">Only Gateway : </TD>
					<TD ALIGN="right">
						<SELECT NAME="ProductToGateway">
							<?
								foreach($productToGateway as $name => $sql) {
							?>
								<OPTION <?php if (strlen( $name ) && $this->ATTRIBUTES['ProductToGateway'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<TR>
					<TD ALIGN="LEFT">Combo/Mulitpack : </TD>
					<TD ALIGN="right">
						<SELECT NAME="ComboMultipack">
							<?
								foreach($comboMultipack as $name => $sql) {
							?>
								<OPTION <?php if ($this->ATTRIBUTES['ComboMultipack'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<?php if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Product Price Filter')) { ?>
					<TR>
					<TD ALIGN="LEFT">Price Filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="PriceFilter">
							<?
								foreach($priceFilters as $name => $sql) {
							?>
								<OPTION <?php if ($this->ATTRIBUTES['PriceFilter'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<?php } ?>					
					<?php if (ss_optionExists('Shop Acme Rockets') ) { ?>
					<TR>
					<TD ALIGN="LEFT">Home page filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="QuickOrderFilter">
							<?
								foreach($quickOrderFilters as $name => $sql) {
							?>
								<OPTION <?php if ($this->ATTRIBUTES['QuickOrderFilter'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<?php } ?>							
					<?php if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Product Quick Order Category Filter')) { ?>
					<TR>
					<TD ALIGN="LEFT">Discount Group Filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="DiscountGroupFilter">
							<?
								foreach($discountGroupFilters as $name => $sql) {
							?>
								<OPTION <?php if ($this->ATTRIBUTES['DiscountGroupFilter'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<?php } ?>
					<?php if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Product Quick Order Category Filter')) { ?>
					<TR>
					<TD ALIGN="LEFT">Needs Padding Filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="WrapSafelyFilter">
							<?
								foreach($wrapSafelyFilters as $name => $sql) {
							?>
								<OPTION <?php if ($this->ATTRIBUTES['WrapSafelyFilter'] == $name) print('SELECTED'); ?> VALUE="<?=ss_HTMLEditFormat($name)?>"><?=ss_HTMLEditFormat($name)?></OPTION>
							<?
								}
							?>
						</SELECT>
					</TD>
					</TR>
					<?php } ?>							
					<TR>
					<TD ALIGN="LEFT">Show : </TD>
					<TD ALIGN="right"><SELECT NAME="RowsPerPage">
						<OPTION VALUE="<?php print $this->ATTRIBUTES['RowsPerPage']?>"><?php print $this->ATTRIBUTES['RowsPerPage']?> Rows/Page</OPTION>
						<OPTION>2</OPTION>
						<OPTION>10</OPTION>
						<OPTION>25</OPTION>
						<OPTION>50</OPTION>
						<OPTION>100</OPTION>
						<OPTION VALUE="<?php print $totalRows?>"><?php print $totalRows?></OPTION>
					</SELECT></TD>
					</TR>
					<TR>
				<TD ALIGN="right" colspan="2"><INPUT TYPE="SUBMIT" VALUE="Submit" NAME="Go"></TD></TR>
				</TABLE>
				</TD></TR>
				</TABLE>				
			</TD>
		</FORM>
	</TR>
</TABLE>


<!--- Display the fields from the table --->
<?PHP 
	$advanced = ss_optionExists('Advanced Administration')?'Upgraded':'';
	include('inc_entriesDisplay'.$advanced.'.php');
?>
<?php

	if (ss_optionExists('Shop Product Stock Levels') or ss_optionExists('Shop Product Update Prices')) {

?>
<form method="post" name="StockUpdateForm" action="index.php?act=ShopSystem_ProductsAdministration.UpdateStockAvailability">
			<?php 
				if ($this->tableAssetLink !=null or $this->assetLink != null) {
					print('<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="'.$this->assetLink.'">');					
				}
			?>

	<input type="hidden" name="BackURL" value="<?print(ss_HTMLEditFormat($backURL))?>">
	
	<input type="hidden" name="StockLevels" value="">
	<input type="hidden" name="Prices" value="">
	
	<input name="Submit" value="Update" type="submit">
</form>
<script language="Javascript">
	function updateStock(level,stockCode,combo) 
	{
		var intLevel = parseInt(level);
		if(combo == 1)
			alert('This is a combo product, please alter component products instead');
		else
			if( combo == 2 )
				alert('This is a dropship product');
			else
				if ((isNaN(intLevel) && level.length > 0))
					alert('Stock Availability must be an integer or left blank');
				else
				{
					if (level.length == 0)
						level = 'NULL';
					document.forms.StockUpdateForm.StockLevels.value += '\n'+stockCode+'\t'+level;
					//alert(document.forms.StockUpdateForm.StockLevels.value);
				}
	}

	function updatePrice(price,stockCode,priceType,what) {
		var newvalue = what.value;
		var floatPrice = parseFloat(price);

		if ((isNaN(floatPrice) && price.length > 0)) {
			alert('Price must be a valid positive number or left blank (special and members prices only).');
		}
		else
		{
			if (price.length == 0 && priceType == 'pro_price')
			{
				alert('Price must be a valid positive number');
			}
			else 
			{
				var notes = "Old "+priceType+" was "+what.getAttribute('oldvalue');
				var blurb = 'Notes for alteration';
				// notes = prompt( blurb, notes );
				if(notes === null)
					notes = '';
				what.setAttribute('changereason', notes );
			}

		if (price.length == 0)
			price = 'NULL'; 
		else 
			what.value = floatPrice;
		notes=notes.replace('\t',' ');
		document.forms.StockUpdateForm.Prices.value += '\n'+stockCode+'\t'+price+'\t'+priceType+'\t'+notes;
		}
	}
</script>
<?
	}
?>

<DIV ALIGN="CENTER"><?php print $pageThru->display?></DIV>

<!--- Supporting Javascript Functions --->
<SCRIPT language="Javascript">
<!--

	/*for(var i=0;i<rowCount;i++) {
		theRow = document.getElementById('row'+i);
		rowPositions[i] = getPageOffsetTop(theRow);
	}*/

	
	function confirmDelete(URL) {
		if ( confirm("Are you sure you want to delete this record ?") ) {
			document.location=URL;
		}
	}

	function confirmActivate(URL) {
		if ( confirm("Are you sure you want to activate this record ?") ) {
			document.location=URL;
		}
	}

	function confirmDeactivate(URL) {
		if ( confirm("Are you sure you want to deactivate this record ?") ) {
			document.location=URL;
		}
	}

	function jumper(selectList) {		
		urlAppend = selectList.options[selectList.selectedIndex].value;
		var winOpenReg = new RegExp('windowOpen:');
		if(urlAppend.search(winOpenReg) != -1) {	
			urlAppend = urlAppend.replace(winOpenReg, '');
			 w = 550;
		     h = 700;
		     x = Math.round((screen.availWidth-w)/2); //center the top edge
		     y = Math.round((screen.availHeight-h)/2); //center the left edge
		     popupWin = window.open(urlAppend, 'Win', "width="+w+",height="+h+",toolbar=0,location=0,scrollbars=1,statusbar=1,menubar=0,resizable=1,top="+y+",left="+x+",screeenY="+y+",screenX="+x);
		
		     popupWin.creator=self;		
			 popupWin.focus();
		} else {
			selectList.form.reset();
			document.location = urlAppend;
		}
	}
//-->
</SCRIPT>
