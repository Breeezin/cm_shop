<!--- Display the fields from the table --->

<?php

	if ($hasRows) {
		print('<form><TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">');
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
			print("<TR CLASS=\"$rowClass\" ID=\"row{$row[$this->tablePrimaryKey]}\" >");
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
						$value = $row[$displayField];
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
				print("<OPTION VALUE=\"{$script_name}?act={$this->prefix}Administration%2EEdit&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa));
					
				print("\">View/Edit Detail</OPTION>");
				
				if (count($this->listManageOptions)) {
					foreach ($this->listManageOptions as $desc => $url) {						
						$actionURL = FindAndReplace($url,$row);
						$actionURL = str_replace("[BreadCrumbs]",ss_URLEncodedFormat($breadCrumbs),$actionURL);					
						$actionURL = str_replace("[BackURL]",ss_URLEncodedFormat($rfa),$actionURL);					
						
						if ($this->tableAssetLink !== null or $this->assetLink !== null) {							
							$actionURL = str_replace("[as_id]",$this->assetLink,$actionURL);					
						}	
						
						print("<OPTION VALUE=\"{$actionURL}\">$desc</option>");
						
					}					
				}
															
				// Print the children 
				foreach ($this->children as $child) {
					print("<OPTION VALUE=\"{$script_name}?act={$child->prefix}Administration%2EList&{$child->linkField}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."");
					if ($this->tableAssetLink !== null or $this->assetLink !== null) {
						if ($child->tableAssetLink !== null) {
							print("&as_id={$this->assetLink}");
						}	
					}
					print("\">{$child->plural}</OPTION>");
					
				}
	
				// Print the delete option
				
				print("<OPTION STYLE=\"background-color:red; color:white\" VALUE=\"javascript:confirmDelete('{$script_name}?act={$this->prefix}Administration%2EDelete&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&as_id={$this->assetLink}&BackURL=".ss_URLEncodedFormat($rfa)."')\">Delete</OPTION>");
				
			
			// End the manage cell
			print('</SELECT></TD>');			
			
			// End the Row
			print("</TR>");
			$currentRow++;
			$counter++;
		}
		print('</TABLE>');
	}
?>
</FORM>
<DIV ALIGN="CENTER"><?php print $pageThru->display?></DIV>
<script>
<!--
    function selectAll(theForm){
        for (var i=0;i < theForm.length;i++){
            fldObj = theForm[i];
            if (fldObj.type == 'checkbox'){
                fldObj.checked = true;
            }
        }
    }
-->
</script>
