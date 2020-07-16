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
					<TD ALIGN="LEFT">Options Filter : </TD>
					<TD ALIGN="right">
						<SELECT NAME="OptionsFilter">
							<OPTION <?php if ($this->ATTRIBUTES['OptionsFilter'] == 'Main') print('SELECTED'); ?> VALUE="Main">Main Options Only</OPTION>
							<OPTION <?php if ($this->ATTRIBUTES['OptionsFilter'] == 'All') print('SELECTED'); ?> VALUE="All">All Options</OPTION>
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
	function updateStock(level,stockCode) {
		var intLevel = parseInt(level);
		if ((isNaN(intLevel) && level.length > 0)) {
			alert('Stock Availability must be an integer or left blank');
		} else {
			if (level.length == 0) level = 'NULL';
			document.forms.StockUpdateForm.StockLevels.value += '\n'+stockCode+'\t'+level;
			//alert(document.forms.StockUpdateForm.StockLevels.value);
		}
	}
	function updatePrice(price,stockCode,priceType,what) {
		var floatPrice = parseFloat(price);
		if ((isNaN(floatPrice) && price.length > 0)) {
			alert('Price must be a valid positive number or left blank (special and members prices only).');
			what.value = what.oldvalue;
		} else {
			if (price.length == 0 && priceType == 'pro_price') {
				alert('Price must be a valid positive number');
				what.value = what.oldvalue;
			} else {
				what.oldvalue = what.value;
				if (price.length == 0) price = 'NULL'; else what.value = floatPrice;
				document.forms.StockUpdateForm.Prices.value += '\n'+stockCode+'\t'+price+'\t'+priceType;
			}
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
