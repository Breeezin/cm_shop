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

	$this->listManageOptions = array("Product Settings" => "index.php?act=shopsystem_categories.SettingEdit&BreadCrumbs=[BreadCrumbs]&ca_id=[ca_id]&BackURL=[BackURL]&as_id=[as_id]",);
	
?>

<SCRIPT language="Javascript">
<!--
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

<br />
<input type="button" value="Enter new invoice" onclick="document.location='index.php?act=SupplierInvoices.New'">

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


<!--- Display the fields from the table --->
<?php

	if ($hasRows) {
		print('<TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">');
		// Print each row		

		// Titles
		if ($this->tableDisplayFieldTitles != NULL) {
			print("<TR>");
			
			foreach ($this->tableDisplayFieldTitles as $title) {
				print("<TD><STRONG>".ss_HTMLEditFormat($title)."</STRONG></TD>");
			}
			print("<TD>&nbsp;</TD></TR>");
		}
		
		// Initialise the loops
		$evenRow = TRUE;
		$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
		$currentRow = $startRow;
		$counter = 0;
		while(($row = $result->fetchRow()) && ($currentRow < $startRow+$this->ATTRIBUTES['RowsPerPage'])) { 

			// Start the row
			$rowClass = $evenRow ? 'AdminEvenRow' : 'AdminOddRow';
			$evenRow = !$evenRow;
			print("<FORM><TR CLASS=\"$rowClass\" ID=\"row{$row[$this->tablePrimaryKey]}\" >");
			// ID=\"row{$counter}\" ONMOUSEOVER=\"checkDragOver({$counter})\" ONMOUSEOUT=\"checkDragOut({$counter})\" 
			// <TD >&nbsp;<IMG ID=\"dragHandle{$row[$this->tablePrimaryKey]}\" SRC=\"System/Classes/Administration/move.gif\" ONMOUSEDOWN=\"return startDrag({$row[$this->tablePrimaryKey]})\"></TD>
			
			$breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">';
			$comma = '';
			foreach ($this->tableDisplayFields as $displayField) {
				// Find the value for the field
				
				
				if ($this->tableTimeStamp == $displayField) {
					$value = formatDateTime($row[$displayField], "Y-m-d");
				} else {
					if (array_key_exists($displayField, $this->fields) AND is_object($this->fields[$displayField])) {
						$value = $this->fields[$displayField]->displayValue($row[$displayField]);
					} else {
						if ($displayField == 'ors_date' or $displayField == 'ors_paid') {
							if (strlen($row[$displayField])) {
								$value = formatDateTime($row[$displayField], "d M Y");
							} else {
								$value ='';	
							}
						} else {
							$value = $row[$displayField];
						}
					}
				}
	
				// Add the field into the bread crumbs
				$breadCrumbs .= $comma;	$comma = ',';
				$breadCrumbs .= strip_tags($value);

				// Print the display fields
				print("<TD ALIGN=\"LEFT\">$value</TD>");
			}
			$breadCrumbs .= '</A>';

			// Start the manage cell
			print('<TD ALIGN="RIGHT" VALIGN="BOTTOM"><SELECT NAME="jumperSelect" onChange="jumper(this)"><OPTION VALUE="#">Manage</OPTION>');

				// Print the manage cell
				print("<OPTION VALUE=\"{$script_name}?act={$this->prefix}%2EEdit&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa));
					
				if ($this->tableAssetLink !== null or $this->assetLink !== null) {
					print("&as_id={$this->assetLink}");
				}
				print("\">Edit</OPTION>");

				// Print the manage cell
				print("<OPTION VALUE=\"{$script_name}?act={$this->prefix}%2EShowPDF&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa));
					
				if ($this->tableAssetLink !== null or $this->assetLink !== null) {
					print("&as_id={$this->assetLink}");
				}
				print("\">PDF</OPTION>");

				// Print the children 
				/*foreach ($this->children as $child) {
					print("<OPTION VALUE=\"{$script_name}?act={$child->prefix}Administration%2EList&{$child->linkField}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."");
					if ($this->tableAssetLink !== null or $this->assetLink !== null) {
						if ($child->tableAssetLink !== null) {
							print("&as_id={$this->assetLink}");
						}	
					}
					print("\">{$child->plural}</OPTION>");
					
				}*/
	
				// Print the delete option
				
				//print("<OPTION STYLE=\"background-color:red; color:white\" VALUE=\"javascript:confirmDelete('{$script_name}?act={$this->prefix}Administration%2EDelete&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&as_id={$this->assetLink}&BackURL=".ss_URLEncodedFormat($rfa)."')\">Delete</OPTION>");
				
			
			// End the manage cell
			print('</SELECT></TD>');			
			
			// End the Row
			print("</TR></FORM>");
			$currentRow++;
			$counter++;
		}
		print('</TABLE>');
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
