<?php

	if ($hasRows) {
		print('<form><TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="4">');
		// Print each row		
	
		// Titles
		if ($this->tableDisplayFieldTitles != NULL) {
			print("<TR>");
			print("<TD >&nbsp;</TD>");
			foreach ($this->tableDisplayFieldTitles as $title) {
				print("<TD ><STRONG>".ss_HTMLEditFormat($title)."</STRONG></TD>");
			}
			if (ss_optionExists('Shop Advanced Product Manage')) {			
				foreach ($customOrderBys as $title) {
					print("<TD><STRONG>".ss_HTMLEditFormat($title)."</STRONG></TD>");
				}
			}
			print("<TD >&nbsp;</TD><TD >&nbsp;</TD></TR>");
		}
		
		// Initialise the loops
		$evenRow = TRUE;
		$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
		$currentRow = $startRow;
		$counter = 0;
		while(($row = $result->fetchRow()) && ($currentRow < $startRow+$this->ATTRIBUTES['RowsPerPage'])) { 

			// Start the row
			if ($counter == 0) {
				$rowClass = 'adminOddRow';
				$cellClass = 'adminSolidLine';
			} else {
				$cellClass = 'adminDottedLine';				
				//if ($counter+1 == $this->ATTRIBUTES['RowsPerPage']) $cellClass = 'adminSolidBottomLine';	
				if ($counter % 2) {
					$rowClass = 'adminEvenRow';						
				} else {
					$rowClass = 'adminOddRow';
					
				}
				
			}
			print("<TR CLASS=\"$rowClass\" ID=\"row{$row[$this->tablePrimaryKey]}\" >");
			print("<TD WIDTH=\"30\" CLASS=\"$cellClass\"><INPUT TYPE=\"CHECKBOX\" style='border:0' NAME=\"Delete_{$this->tablePrimaryKey}\" VALUE=\"{$row[$this->tablePrimaryKey]}\"></TD>");
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

				if (ss_optionExists('Shop Product Stock Levels') and $displayField == 'pro_stock_available') {
					$value = "<input type=\"text\" value=\"$value\" name=\"Stock\" maxlength=\"7\" size=\"5\" onchange=\"updateStock(this.value,'".ss_JSStringFormat($row['pro_stock_code'])."');\">";						
				}
				
				if (ss_optionExists('Shop Product Update Prices')) {
				 	if ($displayField == 'pro_price' or $displayField == 'pro_special_price' or $displayField == 'pro_member_price') {
						$value = '<input type="text" oldvalue="'.$value.'" value="'.$value.'" name="'.$displayField.'" maxlength="7" size="5" onchange="updatePrice(this.value,\''.ss_JSStringFormat($row['pro_stock_code']).'\',\''.$displayField.'\',this);">';						
				 	}
				}				
				
				// Print the display fields
				if (!strlen($value)) $value = "&nbsp;";
				// Print the display fields
				print("<TD CLASS=\"$cellClass\" ALIGN=\"LEFT\">$value</TD>");
			}
			$breadCrumbs .= '</A>';
			if (ss_optionExists('Shop Advanced Product Manage')) {			
				foreach ($customOrderBys as $displayField => $name) {
					if (array_key_exists($displayField, $this->fields) AND is_object($this->fields[$displayField])) {
						$value = $this->fields[$displayField]->displayValue($row[$displayField]);
					} else {
						$value = $row[$displayField];
					}
					if (!strlen($value)) $value = '&nbsp;';
					print("<TD CLASS=\"$cellClass\" ALIGN=\"LEFT\">$value</TD>");
				}
			}
			// Start the manage cell
			$editLink =  "{$script_name}?act={$this->prefix}Administration%2EEdit&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa);				
			if ($this->tableAssetLink !== null or $this->assetLink !== null) {
				$editLink .= "&as_id={$this->assetLink}";
			}
				
			print('<TD CLASS="'.$cellClass.'Dottedleft" ALIGN="RIGHT" VALIGN="middle" WIDTH="20"><a href="'.$editLink.'"><img border="0" src="images/but-view-edit-detail.gif" alt="View/Edit Detail"></a></TD>');
			print('<TD CLASS="'.$cellClass.'Dottedleft" ALIGN="RIGHT" VALIGN="middle" WIDTH="105"><SELECT NAME="jumperSelect_'.$row[$this->tablePrimaryKey].'" onChange="jumper(this)"><OPTION VALUE="#">Manage</OPTION>');

				// Print the manage cell
				print("<OPTION VALUE=\"$editLink\">View/Edit Detail</OPTION>");
				
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
					$displayChild = true;
					if ($child->prefix == 'ShopSystem_ComboProducts' and !$row['pr_combo']) $displayChild = false;
					if ($displayChild) {
						print("<OPTION VALUE=\"{$script_name}?act={$child->prefix}Administration%2EList&{$child->linkField}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."");
						if ($this->tableAssetLink !== null or $this->assetLink !== null) {
							if ($child->tableAssetLink !== null) {
								print("&as_id={$this->assetLink}");
							}	
						}
						print("\">{$child->plural}</OPTION>");
					}
					
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
		$cellCounter = count($this->tableDisplayFields) + count($customOrderBys) + 3;	
		print('<tr><td class=adminSolidLine colspan='.$cellCounter.'>&nbsp;</TD></tr></TABLE>');
	}
?>
<TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">
	<TR>
		<!--- button for adding a new record --->		
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Delete Selected" onclick="deleteSelected(this.form)">		
		</TD>
	</tr>
</TABLE></FORM>
<SCRIPT language="javascript">
	function deleteSelected(theForm) {		
		var ids = '';
		var deleted = null;		
		if (1) {
			var checkField = 'Delete_<?=$this->tablePrimaryKey?>';
			
			if (theForm[checkField].length) {
				var theFrame = document.getElementById('AdminExtraFrame');
				for(var i=0; i < theForm[checkField].length; i++) {
					if (theForm[checkField][i].checked) {	
						deleted = true;
						if (ids.length) {
							ids += ",";
						}
						ids += theForm[checkField][i].value;
						//theFrame.src = "<?php print "{$script_name}?act={$this->prefix}Administration%2EDelete&{$this->tablePrimaryKey}=\" + theForm[checkField][i].value + \"&as_id={$this->assetLink}&noReturn=1";?>";
					}
				}
			} else {
				if (theForm[checkField].checked) {	
					ids = theForm[checkField].value;
					deleted = theForm[checkField].value;					
				}
			}
		}
		if (deleted != null) {
			//alert(ids);
			confirmDelete('index.php?act=<?=$this->prefix?>Administration%2EDelete&<?=$this->tablePrimaryKey?>='+ids+'&as_id=<?=$this->assetLink?>&BackURL=<?=ss_URLEncodedFormat($rfa)?>');
			
		}
		
	}
</SCRIPT>
