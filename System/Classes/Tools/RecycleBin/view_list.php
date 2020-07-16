
<!--- Display the fields from the table --->
<BR>
<?php
	if (strlen($this->ATTRIBUTES['Message'])) {
		print ("<p>".ss_HTMLEditFormat($this->ATTRIBUTES['Message'])."</p>");
	}
	
	if ($Q_DeletedAssets->numRows()) {
		print('<TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">');
		// Print each row		
		
		// Titles		
		print("<TR>");				
		print("<TD><STRONG>Item Full Name</STRONG></TD>");		
		print("<TD><STRONG>Item Type</STRONG></TD>");		
		print("<TD>&nbsp;</TD></TR>");
		
		$evenRow = TRUE;
			
		while($row = $Q_DeletedAssets->fetchRow()) { 

			// Start the row
			$rowClass = $evenRow ? 'AdminEvenRow' : 'AdminOddRow';
			$evenRow = !$evenRow;
			print("<form><TR CLASS=\"$rowClass\">");						
				// Print the display fields
			$AssetPathResult = new Request("Asset.PathFromID", array('as_id'=>$row['as_id'], "Deleted" => true));
			
			$path = ss_withoutPreceedingSlash($AssetPathResult->value);
			print("<TD ALIGN=\"LEFT\">".ss_HTMLEditFormat($path)."</TD>");			
			print("<TD ALIGN=\"LEFT\">".ss_HTMLEditFormat($row['as_type'])."</TD>");			
			// Start the manage cell
			print('<TD ALIGN="RIGHT" VALIGN="BOTTOM">
				<SELECT NAME="jumperSelect" onChange="jumper(this)"><OPTION VALUE="#">Manage</OPTION>');

				// Print the manage cell				
				print("<OPTION VALUE=\"javascript:confirmRestore('index.php?act=RecycleBin.Restore&as_id={$row['as_id']}&AssetPath=".ss_URLEncodedFormat($path)."')\">Restore</OPTION>");
				print("<OPTION STYLE=\"background-color:red; color:white\" VALUE=\"javascript:confirmDelete('index.php?act=RecycleBin.Delete&as_id={$row['as_id']}')\">Delete</OPTION>");
				
			// End the manage cell
			print('</SELECT></TD>');			
			
			// End the Row
			print("</TR></FORM>");
			
		}
		print('</TABLE>');
	}

?>

<!--- Supporting Javascript Functions --->
<SCRIPT language="Javascript">
<!--

	/*for(var i=0;i<rowCount;i++) {
		theRow = document.getElementById('row'+i);
		rowPositions[i] = getPageOffsetTop(theRow);
	}*/

	function confirmDelete(URL) {
		if ( confirm("Are you sure you want to delete this item ?") ) {
			document.location=URL;
		}
	}
	function confirmRestore(URL) {
		if ( confirm("Are you sure you want to restore this item ?") ) {
			document.location=URL;
		}
	}
	
	function jumper(selectList) {
		urlAppend = selectList.options[selectList.selectedIndex].value;
		selectList.form.reset();
		document.location = urlAppend;
	}
//-->
</SCRIPT>
