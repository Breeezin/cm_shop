<?php

	function getPotentialProfits( $RRP, $ProdCost )
	{
		$results = array( );

		$min_shipping = getField( "select min(if_cost) from included_freight where if_cost > 0" );
		$max_shipping = getField( "select max(if_cost) from included_freight where if_cost > 0" );

		// ok what is going on here is that if the price is less that 50 bux, you cant buy it on it's own.
		// you have to buy 2.  This effectively cuts the shipping in half
		if( $RRP + $min_shipping < 50 )
			$min_shipping /= 2;

		if( $RRP + $max_shipping < 50 )
			$max_shipping /= 2;

		$paymentOptionQ = query( "Select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id where po_active = 1" );
		while( $prow = $paymentOptionQ->fetchRow() )
		{
			$processor_cut_var_ratio = $prow['pg_skim'] / 100.0;
			$procerror_cut_fix = $prow['pg_skim_fixed'];
			$method_discount_ratio = (1 - $prow['po_option_discountx100'] / 10000.0 );

			$results[] = 100* ( $method_discount_ratio * ($RRP + $min_shipping)*(1-$processor_cut_var_ratio) - $min_shipping - $ProdCost - $procerror_cut_fix )
							/( $method_discount_ratio * ($RRP + $min_shipping));
			$results[] = 100* ( $method_discount_ratio * ($RRP + $max_shipping)*(1-$processor_cut_var_ratio) - $max_shipping - $ProdCost - $procerror_cut_fix )
							/( $method_discount_ratio * ($RRP + $max_shipping));
		}
		return "Prof. ".number_format( min($results), 1, '.', '' )." to ".number_format( max($results), 1, '.', '' )." %";
	}

	if ($hasRows) {
		print('<FORM name="Records"><TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="4">');
		// Print each row		

		// Titles
		if ($this->tableDisplayFieldTitles != NULL) {
			print("<TR>");
			print("<TD >&nbsp;</TD>");
			foreach ($this->tableDisplayFieldTitles as $title) {
				print("<TD><STRONG>".ss_HTMLEditFormat($title)."</STRONG></TD>");
			}
			if (ss_optionExists('Shop Advanced Product Manage')) {			
				foreach ($customOrderBys as $title) {
					print("<TD><STRONG>".ss_HTMLEditFormat($title)."</STRONG></TD>");
				}
			}	
			print("<TD>&nbsp;</TD><TD>&nbsp;</TD></TR>");
		}
		
		// Initialise the loops
		$evenRow = TRUE;
		$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
		$currentRow = $startRow;
		$counter = 0;
		while(($row = $result->fetchRow()) && ($currentRow < $startRow+$this->ATTRIBUTES['RowsPerPage'])) { 
			if (ss_optionExists('Shop Acme Rockets')) { 
				print("<script language=\"Javascript\">stockCodes[stockCodes.length] = '".ss_JSStringFormat($row['pro_stock_code'])."'</script>");
			}
			
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

			if( $row['pr_ve_id'] == 1 )		// accessories
				$rowClass .= "Blue";

			if( $row['pr_ve_id'] == 2 )		// swiss
				$rowClass .= "Red";

			print("<TR CLASS=\"$rowClass\" ID=\"row{$row[$this->tablePrimaryKey]}\" >");
			// ID=\"row{$counter}\" ONMOUSEOVER=\"checkDragOver({$counter})\" ONMOUSEOUT=\"checkDragOut({$counter})\" 
			// <TD >&nbsp;<IMG ID=\"dragHandle{$row[$this->tablePrimaryKey]}\" SRC=\"System/Classes/Administration/move.gif\" ONMOUSEDOWN=\"return startDrag({$row[$this->tablePrimaryKey]})\"></TD>
			print("<TD WIDTH=\"30\" CLASS=\"$cellClass\"><INPUT TYPE=\"CHECKBOX\" style='border:0' NAME=\"Delete_{$this->tablePrimaryKey}\" VALUE=\"{$row[$this->tablePrimaryKey]}\"></TD>");
			$breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">';
			$comma = '';
			foreach ($this->tableDisplayFields as $displayField) {
				// Find the value for the field
	
				if ($this->tableTimeStamp == $displayField) {
					$value = formatDateTime($row[$displayField], "Y-m-d");
				} else {
					if (array_key_exists($displayField, $this->fields) AND is_object($this->fields[$displayField])) {
						if( strlen( $row[$displayField] ) )
							$value = $this->fields[$displayField]->displayValue($row[$displayField]);
						else
						{
//							ss_log_message( "displayField $displayField blank" );
//							ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $row );
							$value= '';
						}
					} else {
						$value = $row[$displayField];
					}
				}
	
				// Add the field into the bread crumbs
				$breadCrumbs .= $comma;	$comma = ',';
				$breadCrumbs .= strip_tags($value);

				if (ss_optionExists('Shop Product Stock Levels') and ($displayField == 'pro_stock_available') ) {		// && ($row['pr_ve_id'] != 2
					$ind = 0;
					if( $row['pr_combo'] )
						$ind = 1;
					if( $row['pr_ve_id'] == 5 )
						$ind = 2;

					if( $row['pr_combo'] )
						$value = "<input type=\"hidden\" value=\"$value\" name=\"Stock\" \">&nbsp;";
					else
						$value = "<input type=\"text\" value=\"$value\" name=\"Stock\" maxlength=\"7\" size=\"5\" onchange=\"updateStock(this.value,'".ss_JSStringFormat($row['pro_stock_code'])."', $ind);\">";
				}

				if (ss_optionExists('Shop Product Update Prices')) {
				 	if ( $displayField == 'pr_admin_text' ) {
						$value = '<input type="text" oldvalue="'.$value.'" value="'.$value.'" name="'.$displayField.'" maxlength="255" size="25">';
				 	}
				 	if ($displayField == 'pro_price' or $displayField == 'pro_special_price') {
						$value = '<input type="text" changereason="" oldvalue="'.$value.'" value="'.$value.'" name="'.$displayField.'" maxlength="7" size="5" onchange="updatePrice(this.value,\''.ss_JSStringFormat($row['pro_stock_code']).'\',\''.$displayField.'\',this);">';						
					}
				 	if ( $displayField == 'pro_member_price' or $displayField == 'pro_supplier_price' ) {
						$value = '<input type="text" oldvalue="'.$value.'" value="'.$value.'" name="'.$displayField.'" maxlength="7" size="5" onchange="updatePrice(this.value,\''.ss_JSStringFormat($row['pro_stock_code']).'\',\''.$displayField.'\',this);">';						
				 	}
				 	if ($displayField == 'pro_supplier_price') {
						$retail = ($row['pro_special_price'] === null) ? $row['pro_price'] : $row['pro_special_price'];
						if ($retail != 0 and $row['pro_supplier_price'] !== null) {
							$value .= ' ('.ss_decimalFormat((($retail-$row['pro_supplier_price'])/$retail*100),1).'%) ';						
							$value .= getPotentialProfits( $retail, $row['pro_supplier_price'] );
						}				 		
				 	}
				}
				
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
			// Start the manage cell
			$editLink =  "{$script_name}?act={$this->prefix}Administration%2EEdit&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa);				
			$priceHistoryLink =  "{$script_name}?act={$this->prefix}Administration%2EShowHistory&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa);				
			if ($this->tableAssetLink !== null or $this->assetLink !== null) {
				$editLink .= "&as_id={$this->assetLink}";
			}
			print('<TD CLASS="'.$cellClass.'Dottedleft" ALIGN="RIGHT" VALIGN="middle" WIDTH="20"><a href="'.$editLink.'"><img border="0" src="images/but-view-edit-detail.gif" alt="View/Edit Detail"></a></TD>');	
			print('<TD ALIGN="RIGHT" VALIGN="middle" CLASS="'.$cellClass.'Dottedleft" WIDTH="105"><SELECT NAME="jumperSelect" onChange="jumper(this)"><OPTION VALUE="#">Manage</OPTION>');

				print("<OPTION VALUE=\"$priceHistoryLink\">Show Price/Stock History</OPTION>");

				// Print the manage cell
				print("<OPTION VALUE=\"$editLink\">Edit This</OPTION>");

				// for each of the languages not english, display an edit option
				$Q_Languages = query("
						SELECT * from languages, shopsystem_product_descriptions where lg_id > 0 and lg_id = prd_language and prd_pr_id = ".$row[$this->tablePrimaryKey] );
											
				while($loption = $Q_Languages->fetchRow())
					{
					print("<OPTION VALUE=\"{$script_name}?act={$this->prefix}Administration%2EEdit&prd_id={$loption['prd_id']}&Name={$loption['lg_name']}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa));
						
					if ($this->tableAssetLink !== null or $this->assetLink !== null)
						print("&as_id={$this->assetLink}");
					
					print("\">Edit in ".$loption['lg_name']."</OPTION>");
					}
				
	
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
				
				print("<OPTION STYLE=\"background-color:blue; color:white\" VALUE=\"javascript:reallyDuplicate( {$row[$this->tablePrimaryKey]} )\">Duplicate</OPTION>");
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
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Activate Selected" onclick="activateSelected(this.form)">		
		</TD>
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Deactivate Selected" onclick="deactivateSelected(this.form)">		
		</TD>
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Remove all llamas from Discounts" onclick="noDiscount()">		
		</TD>
		<!--
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Activate all llamas from Las Palmas" onclick="reallyLP()">		
		</TD>
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Activate all llamas die Schweiz" onclick="reallySwiss()">		
		</TD>
		-->
	</TR>
	<TR>
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Set Add Gift to All" onclick="reallySet()">		
		</TD>
		<TD ALIGN="LEFT" valign="baseline">			
			<INPUT TYPE="button" NAME="delete" VALUE="Remove Add Gift to All" onclick="reallyUnSet()">
		</TD>
	</TR>
</TABLE></FORM>
<SCRIPT language="javascript">

    function reallyDuplicate( pr_id )
    {
        if( confirm( "Really duplicate this product" ) )
            window.open( "index.php?act=<?=$this->prefix?>Administration%2EDuplicate&<?=$this->tablePrimaryKey?>="+pr_id );
    }
	
	function reallyLP()
    {
        if( confirm( "Really sell llamas only from Las Palmas?" ) )
            window.open( "index.php?act=<?=$this->prefix?>Administration%2EsetLP");
    }

    function noDiscount()
    {
        if( confirm( "Remove all llamas from all discount groups?" ) )
            window.open( "index.php?act=<?=$this->prefix?>Administration%2EnoDiscount");
    }

    function reallySwiss()
    {
        if( confirm( "Really sell llamas only from Switzerland?" ) )
            window.open( "index.php?act=<?=$this->prefix?>Administration%2EsetSwiss");
    }

    function reallySet()
    {
        if( confirm( "Really set all products to get a free gift?" ) )
            window.open( "index.php?act=<?=$this->prefix?>Administration%2EsetAddGift&pr_add_gift=1");
    }

    function reallyUnSet()
    {
        if( confirm( "Really set all products to not get a free gift?" ) )
            window.open( "index.php?act=<?=$this->prefix?>Administration%2EsetAddGift&pr_add_gift=0");
    }

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

	function activateSelected(theForm) {		
		var ids = '';
		var activated = null;		
		if (1) {
			var checkField = 'Delete_<?=$this->tablePrimaryKey?>';
			
			if (theForm[checkField].length) {
				var theFrame = document.getElementById('AdminExtraFrame');
				for(var i=0; i < theForm[checkField].length; i++) {
					if (theForm[checkField][i].checked) {	
						activated = true;
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
					activated = theForm[checkField].value;					
				}
			}
		}
		if (activated != null) {
			//alert(ids);
			confirmActivate('index.php?act=<?=$this->prefix?>Administration%2EActivate&<?=$this->tablePrimaryKey?>='+ids+'&as_id=<?=$this->assetLink?>&BackURL=<?=ss_URLEncodedFormat($rfa)?>');
			
		}
	}
		
	function deactivateSelected(theForm) {		
		var ids = '';
		var activated = null;		
		if (1) {
			var checkField = 'Delete_<?=$this->tablePrimaryKey?>';
			
			if (theForm[checkField].length) {
				var theFrame = document.getElementById('AdminExtraFrame');
				for(var i=0; i < theForm[checkField].length; i++) {
					if (theForm[checkField][i].checked) {	
						activated = true;
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
					activated = theForm[checkField].value;					
				}
			}
		}
		if (activated != null) {
			//alert(ids);
			confirmDeactivate('index.php?act=<?=$this->prefix?>Administration%2EDeactivate&<?=$this->tablePrimaryKey?>='+ids+'&as_id=<?=$this->assetLink?>&BackURL=<?=ss_URLEncodedFormat($rfa)?>');
			
		}
		
	}
</SCRIPT>
