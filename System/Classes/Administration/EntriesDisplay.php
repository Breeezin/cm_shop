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
    //ss_DumpVar($numRows, 'number of rows');
    $breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">'.$this->plural.'</A>';

	// Only include the parent hidden field if this administration has a parent
	$parentHiddenField = '';
	if (($this->parentTable != NULL) && array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
		$parentHiddenField = "<INPUT TYPE=\"HIDDEN\" NAME=\"{$this->parentTable->linkField}\" VALUE=\"{$this->ATTRIBUTES[$this->parentTable->linkField]}\">";
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
					
					<?
					if (count($this->filterByMulti)) {						
						foreach($this->filterByMulti as $filter) {
					?>
						<TR>	
						<TD ALIGN="LEFT">Filter By <?=$filter['displayName']?>: </TD>
						<TD ALIGN="RIGHT"><SELECT NAME="FilterBy<?=$filter['name']?>">
							<option value="" >All</OPTION>
							<?php 
								foreach ($filter['filters'] as $filterIndex => $aBy) {
									$selected = '';
									if (array_key_exists('FilterBy'.$filter['name'],$this->ATTRIBUTES) and strlen($this->ATTRIBUTES['FilterBy'.$filter['name']]) and $this->ATTRIBUTES['FilterBy'.$filter['name']] == $filterIndex) {
										$selected = 'Selected';
									}
									print "<OPTION VALUE=\"{$filterIndex}\" $selected>{$aBy['name']}</OPTION>";
								}							
							?>
							</SELECT>						
						</TD>
						</TR>
					<?php 
						}
					}
					?>					
					
					<?php }?>
					<TR>
					<TD ALIGN="LEFT">Show: </TD>
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


<?PHP 
	$advanced = ss_optionExists('Advanced Administration')?'Upgraded':'';
	include('inc_entriesDisplay'.$advanced.'.php');
?>

<!--- Supporting Javascript Functions --->
<SCRIPT language="Javascript">
<!--

	/*for(var i=0;i<rowCount;i++) {
		theRow = document.getElementById('row'+i);
		rowPositions[i] = getPageOffsetTop(theRow);
	}*/

	function openwindow(url,name) {
		res = window.open(url,name, 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');				
	}

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
		if ( confirm("Are you sure you want to activate this record ?") ) {
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
