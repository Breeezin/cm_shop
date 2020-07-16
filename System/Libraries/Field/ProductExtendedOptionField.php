<?php

		class ProductExtendedOptionField extends ProductExtendedOptionsField  {
			
			function processDatabaseInputValues($primaryKey = 1) {				
				// find all the linked items
				$result = Query("
					SELECT * FROM $this->linkTableName 
					WHERE $this->linkTableOurKey = $primaryKey				
					AND pro_is_main = 1
				");
				
				// read the values from the query into the fields
				$this->value = array();
				while ($row = $result->fetchRow()) {
					array_push($this->value,$row);
				}
				$this->verifyValue = $this->value;
				
			}	
			
			function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) 
			{
				$value = $verify ? $this->verifyValue : $this->value;
				$name  = $verify ? $this->name.'_V'   : $this->name;
				if (!is_array($value)) 
					$value = array();

				if ($this->preview != false) 
					$previewSize = $this->preview;
				else
					$previewSize = 'None';

				$noImage = "System/Libraries/Field/Images/noimage.gif";

				$thisCurrency = "";
				if (is_array($this->currencySettings) and array_key_exists('CurrencyCode', $this->currencySettings))
					//$thisCurrency = $this->currencySettings['CurrencyCode']."(".$this->currencySettings['Symbol'].")";
					$thisCurrency = $this->currencySettings['CurrencyCode'];
				if( count($value) )
					if( array_key_exists( 'pro_source_currency', $value[0]) )
						$thisCurrency = $value[0]['pro_source_currency'];

//				ss_log_message( "this->fieldSet->fields = " );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->value );
				if( strlen( $thisCurrency ) )
//					$priceHTML = "<input type='hidden' name='pro_source_currency' value='$thisCurrency'>*All Prices are in ".$thisCurrency;
					$priceHTML = "*All Prices are in <input type='text' name='pro_source_currency' size='3' value='$thisCurrency'/>";
				else
					$priceHTML = "&nbsp;";
//				if( $this->fieldSet->fields['pr_ve_id']->value == 1 )
//					$priceHTML = "*All Prices are in USD($)";
//				else
//					if (is_array($this->currencySettings) and array_key_exists('CurrencyCode', $this->currencySettings))
//						$priceHTML = "*All Prices are in ".$this->currencySettings['CurrencyCode']."(".$this->currencySettings['Symbol'].")";
//					else
//						$priceHTML = "&nbsp;";

				$optionsHTML = '';
				$previewImage = $noImage;	
				$optionIndex = 0;
				if(count($value)) 
				{			
					//ss_DumpVarDie($value);

					//ss_DumpVarDie($this->fieldSet->fields['pr_combo']->value);
					foreach ($value as $aValue) 
					{												
						$priceHTML .= "<br/>Shipping Weight (grams) <input type='text' name='pro_weight' size='3' value='{$aValue['pro_weight']}'/>";
						$priceHTML .= "<br/>Net Weight (grams) <input type='text' name='pro_net_weight' size='3' value='{$aValue['pro_net_weight']}'/>";

						$imageDirectory = ss_withTrailingSlash($this->directory);
						if ($aValue['pro_image'] != NULL and strlen($aValue['pro_image']))
						{						
							$imageFile = $imageDirectory.$aValue['pro_image'];
							$size = getimagesize($imageFile);
							if (ListFirst($this->preview,'x') < $size[0])
								$previewImage = "$imageFile";
							else 
								if (ListLast($this->preview,'x') < $size[1])
									$previewImage = "$imageFile";
								else
									$previewImage = "index.php?act=ImageManager.get&Image="
													.ss_URLEncodedFormat($imageFile)."&Size=".$this->preview;						
						}
						else
							$previewImage = $noImage;

						$gotExisting = strlen($aValue['pro_image'])?'':'none';

						$optionsHTML .="<tr><input type='hidden' name='{$name}_IsMain[]' value='{$aValue['pro_uuids']}'><TD><input type='text' size='10' name='{$name}_stock_code[]' value=\"{$aValue['pro_stock_code']}\"></TD><TD><input type='text' size='10' name='{$name}_supplier_sku[]' value=\"{$aValue['pro_supplier_sku']}\"></TD><TD><input type='text' size='5' name='{$name}_price[]' oldvalue=\"{$aValue['pro_price']}\" value=\"{$aValue['pro_price']}\" changereason='' onchange=\"updatePrice(this.value,'pro_price',this);\"></TD><TD><input type='text' size='5' name='{$name}_special_price[]' oldvalue=\"{$aValue['pro_special_price']}\" value=\"{$aValue['pro_special_price']}\" changereason='' onchange=\"updatePrice(this.value,'pro_special_price',this);\"></TD><TD><input type='text' size='5' name='{$name}_rrp_price[]' value=\"{$aValue['pro_rrp_price']}\"></TD>";
						if(ss_optionExists('Shop Members'))
							$optionsHTML .="<TD><input type='text' size='5' name='{$name}_member_price[]' value=\"{$aValue['pro_member_price']}\"></TD>";
						$optionsHTML .="<TD><input type='text' size='35' name='{$name}_country_price_override[]' value=\"{$aValue['pro_country_price_override']}\"></TD>";
						if(ss_optionExists('Shop Supplier Price') )
							if( $this->fieldSet->fields['pr_combo']->value )
							{
								$optionsHTML .="<input type='hidden' name='{$name}_supplier_price[]' value=\"{$aValue['pro_supplier_price']}\">";
								$optionsHTML .="<input type='hidden' name='{$name}_supplier_disount[]' value=\"{$aValue['pro_supplier_disount']}\">";
							}
							else
							{
								$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_price[]' value=\"{$aValue['pro_supplier_price']}\"></TD>";
								$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_disount[]' value=\"{$aValue['pro_supplier_disount']}\"></TD>";
							}	
					
						if(array_key_exists('pro_id', $aValue)) 
							$tempID = $aValue['pro_id'];
						else 
							$tempID = $aValue['ID'];

						$optionsHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ID[]\" VALUE=\"{$tempID}\">";
						$optionsHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_PriceChangeReason[]\" VALUE=\"\">";
						$optionsHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_SpecialPriceChangeReason[]\" VALUE=\"\">";

						if (ss_optionExists('Shop Product Option Images')) 
						{
							$optionsHTML .="<TD><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_Image[]\" VALUE=\"{$aValue['pro_image']}\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageAction[]\" VALUE=\"NoChange\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageOriginal[]\" VALUE=\"{$aValue['pro_image']}\"><IMG ID=\"{$name}_ImagePreview_$optionIndex\" SRC=\"$noImage\" STYLE=\"display:none;border:0px\"><IMG ID=\"{$name}_ImagePreviewOriginal_$optionIndex\" SRC=\"$previewImage\" STYLE=\"border:0px\"><BR><A HREF=\"index.php?act=ImageManager.SimpleUpload&ReturnForm=adminForm&ReturnField=&ReturnJSFunction={$name}_UploadImages($optionIndex,&Preview={$previewSize}\" TARGET=\"SimpleUpload_{$name}\" ONCLICK=\"winhandle=window.open('','SimpleUpload_{$name}','width=300,height=175')\">Upload</A><SPAN STYLE=\"display:$gotExisting\" ID=\"{$name}_ImageDelete_$optionIndex\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnDelete($optionIndex);\">Delete</A></SPAN><SPAN STYLE=\"display:none;\" ID=\"{$name}_ImageRevert_$optionIndex\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnRevert($optionIndex);\">Revert</A></SPAN></TD>";          
						}
						if (ss_optionExists('Shop Product Stock Levels') )
							if( $this->fieldSet->fields['pr_combo']->value )
							{
								$optionsHTML .="<input type='hidden' name='{$name}_stock_available[]' value=\"{$aValue['pro_stock_available']}\">";
								if( strlen( $aValue['pro_stock_unavailable'] ) )
									$optionsHTML .="<input type='hidden' name='{$name}_stock_unavailable[]' value=\"{$aValue['pro_stock_unavailable']}\">";
								else
									$optionsHTML .="<input type='hidden' name='{$name}_stock_unavailable[]' value=\"0\">";
							}
							else
							{
								if( $this->fieldSet->fields['pr_ve_id']->value == 99 )		// 2 -> can't edit swiss stock available
								{
									$optionsHTML .="<TD><input type='text' readonly size='5' name='{$name}_stock_available[]' value=\"{$aValue['pro_stock_available']}\"></TD>";
									if( strlen( $aValue['pro_stock_unavailable'] ) )
										$optionsHTML .="<TD><input type='text' size='5' readonly name='{$name}_stock_unavailable[]' value=\"{$aValue['pro_stock_unavailable']}\"></TD>";
									else
										$optionsHTML .="<TD><input type='text' size='5' readonly name='{$name}_stock_unavailable[]' value=\"0\"></TD>";
								}
								else
								{
									$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_available[]' value=\"{$aValue['pro_stock_available']}\"></TD>";
									if( strlen( $aValue['pro_stock_unavailable'] ) )
										$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_unavailable[]' value=\"{$aValue['pro_stock_unavailable']}\"></TD>";
									else
										$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_unavailable[]' value=\"0\"></TD>";
								}
							}
							/*
							if( $this->fieldSet->fields['pr_combo']->value )
							{
								$optionsHTML .="<input type='hidden' name='{$name}_stock_available[]' value=\"{$aValue['pro_stock_available']}\">";
								if( strlen( $aValue['pro_stock_unavailable'] ) )
									$optionsHTML .="<input type='hidden' name='{$name}_stock_unavailable[]' value=\"{$aValue['pro_stock_unavailable']}\">";
								else
									$optionsHTML .="<input type='hidden' name='{$name}_stock_unavailable[]' value=\"0\">";
							}
							else
							{
								$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_available[]' value=\"{$aValue['pro_stock_available']}\"></TD>";
								if( strlen( $aValue['pro_stock_unavailable'] ) )
									$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_unavailable[]' value=\"{$aValue['pro_stock_unavailable']}\"></TD>";
								else
									$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_unavailable[]' value=\"0\"></TD>";
							}
							*/
						else
							if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Advanced')) 
							{
								$inStockSelected = '';
								$outOfStockSelected = '';
								if (strlen($aValue['pro_stock_available']) == 0 or $aValue['pro_stock_available'] > 0) {
									$inStockSelected = 'selected';
								} else {
									$outOfStockSelected = 'selected';	
								}
								$optionsHTML .="<TD><select name='{$name}_stock_available[]'><option $inStockSelected value=''> </option><option $outOfStockSelected value='0'>Out of Stock</option></TD>";	
							}
						$optionsHTML .= "<TD><input type='hidden' name='{$name}[]' value='{$aValue['pro_uuids']}'></TD></TR>";
					}
				}
				else 		// $value is an empty array....
				{
						$priceHTML .= "<br/>Shipping Weight (grammes) <input type='text' name='pro_weight' size='3' value='{$aValue['pro_weight']}'/>";
						$priceHTML .= "<br/>Net Weight (grammes) <input type='text' name='pro_net_weight' size='3' value='{$aValue['pro_net_weight']}'/>";
					
						$optionsHTML .="<tr><input type='hidden' name='{$name}_IsMain[]' value=''><TD><input type='text' size='10' name='{$name}_StockCode[]' value=\"\"></TD><TD><input type='text' size='10' name='{$name}_SupplierSKU[]' value=\"\"></TD><TD><input type='text' size='5' name='{$name}_Price[]' value=\"\"></TD><TD><input type='text' size='5' name='{$name}_SpecialPrice[]' value=\"\"></TD><TD><input type='text' size='5' name='{$name}_RRPrice[]' value=\"\"></TD>";
						if(ss_optionExists('Shop Members'))
							$optionsHTML .= "<TD><input type='text' size='5' name='{$name}_MemberPrice[]' value=\"\"></TD>";
						$optionsHTML .="<TD><input type='text' size='35' name='{$name}_CountryPrices[]' value=\"{$aValue['pro_country_price_override']}\"></TD>";
						if(ss_optionExists('Shop Supplier Price') )
							if( $this->fieldSet->fields['pr_combo']->value )
							{
								$optionsHTML .="<input type='hidden' size='5' name='{$name}_supplier_price[]' value=\"\">";
								$optionsHTML .="<input type='hidden' size='5' name='{$name}_supplier_discount[]' value=\"\">";
							}	
							else
							{
								$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_price[]' value=\"\"></TD>";
								$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_discount[]' value=\"\"></TD>";
							}	
			
							
						$optionsHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ID[]\" VALUE=\"\">";          			
						if (ss_optionExists('Shop Product Option Images')) {
							$optionsHTML .="<TD><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_Image[]\" VALUE=\"\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageAction[]\" VALUE=\"NoChange\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageOriginal[]\" VALUE=\"\"><IMG ID=\"{$name}_ImagePreview_0\" SRC=\"$noImage\" STYLE=\"display:none;border:0px\"><IMG ID=\"{$name}_ImagePreviewOriginal_0\" SRC=\"$previewImage\" STYLE=\"border:0px\"><BR><A HREF=\"index.php?act=ImageManager.SimpleUpload&ReturnForm=adminForm&ReturnField=&ReturnJSFunction={$name}_UploadImages(0,&Preview={$previewSize}\" TARGET=\"SimpleUpload_{$name}\" ONCLICK=\"winhandle=window.open('','SimpleUpload_{$name}','width=300,height=175')\">Upload</A><SPAN STYLE=\"display:none\" ID=\"{$name}_ImageDelete_0\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnDelete(0);\">Delete</A></SPAN><SPAN STYLE=\"display:none;\" ID=\"{$name}_ImageRevert_0\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnRevert(0);\">Revert</A></SPAN></TD>";       
						}
						
						if (ss_optionExists('Shop Product Stock Levels') )
						{
							$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_available[]' value=\"\"></TD>";
							$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_unavailable[]' value=\"\"></TD>";
						}
						else
							if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Advanced'))
							{
								$optionsHTML .="<TD><select name='{$name}_stock_available[]'><option value=''> </option><option value='0'>Out of Stock</option></TD>";
							}
									
						$optionsHTML .= "<TD><input type='hidden' name='{$name}[]' value=''></TD></TR>";			
				}			

				$total = count($value);
				$titles = "<TH  valign=\"bottom\">Stock Code</TH><TH  valign=\"bottom\">Supplier SKU</TH><TH valign=\"bottom\">Price</TH><TH valign=\"bottom\">Special<BR>Price</TH><TH valign=\"bottom\">RRP</TH>";
				if (ss_optionExists('Shop Members')) 
					$titles .= "<TH  valign=\"bottom\">Member<BR>Price</TH>";
				$titles .= "<TH  valign=\"bottom\">Country override prices<BR>Price</TH>";
				if (ss_optionExists('Shop Supplier Price')
				 && !$this->fieldSet->fields['pr_combo']->value )
				{
					$titles .= "<TH  valign=\"bottom\">Supplier<BR>Price</TH>";
					$titles .= "<TH  valign=\"bottom\">Supplier<BR>Discount %</TH>";					
				}
					
				if (ss_optionExists('Shop Product Option Images')) 
					$titles .= "<TH valign=\"bottom\">Image</TH>";			
				if ( ( ss_optionExists('Shop Product Stock Levels') 
				 or ss_optionExists('Shop Product Out Of Stock') 
				 or ss_optionExists('Shop Advanced') )
				 && !$this->fieldSet->fields['pr_combo']->value )
				{
					$titles .= "<TH valign=\"bottom\">Stock<br>Availabile</TH>";			
					$titles .= "<TH valign=\"bottom\">Stock<br>Unavailabile</TH>";			
				}
				
				$titles .= "<TH>&nbsp;</TH>";			
				
				$htmlTypeOptions = '';
				$htmlSourceCurrency = '';
				$htmlDiscountGroupsOptions = '';
				$htmlDiscountGroups = '';		
				if(ss_optionExists('Sell Products'))
				{
					if (ss_optionExists('Shop Discount Codes')) {
						$Q_DiscountGroups = query("SELECT * FROM  shopsystem_discount_groups WHERE dig_deleted IS NULL ORDER BY dig_sort_order ASC");			
						$htmlDiscountGroupsOptions = '';
						$htmlDiscountGroupsOptions .= '<option value=\'\'>No Discount</option>';						
						while($aGroup = $Q_DiscountGroups->fetchRow()) {				
							$htmlDiscountGroupsOptions .= "<option value=\"{$aGroup['dig_id']}\">{$aGroup['dig_name']}</option>\n";				
						}			
						
						$htmlDiscountGroups .= "<TR><th align='right'>Discount Group: </th><TD colspan=\"8\">\n<select name=\"{$name}_DiGroup\" onchange='{$name}_getGroup()'>$htmlDiscountGroupsOptions</select></TD></TR>\n";
					}
				}	
				$displayHTML = <<< EOD
		
		<SCRIPT LANGUAGE="JavaScript">
		     var {$name}_inputCount = {$total};
     		
	function {$name}_getGroup() {
     	var theForm = document.forms.adminForm;
     	var selected = {$name}_getSelectedValue('{$name}_DiGroup');
     	if(theForm.pr_dig_id) {			
				theForm.pr_dig_id.value = selected;
		}
     }
      
     function {$name}_setSelectedValueFromItem(theSelect, selectedValue, index) {
		var selectedIndex = -1;
		
		originalLength = document.forms.adminForm[theSelect].item(index).options.length;		
		for(var i=originalLength-1; i >= 0; i--) {			
			if (document.forms.adminForm[theSelect].item(index).options[i].value == selectedValue) {
				selectedIndex = i;
				break;
			}
		}			
		
		document.forms.adminForm[theSelect].item(index).selectedIndex = selectedIndex;

	 }

	function updatePrice(price,priceType,what) {
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
//				notes = prompt( blurb, notes );
				if(notes === null)
					notes = '';
				what.setAttribute('changereason', notes );
				if( priceType == 'pro_price' )
					document.forms.adminForm['{$name}_PriceChangeReason[]'].value = notes; 
				else
					document.forms.adminForm['{$name}_SpecialPriceChangeReason[]'].value = notes; 
			}

		if (price.length == 0)
			price = 'NULL'; 
		else 
			what.value = floatPrice;
		}
	}

	function OldUpdatePrice(price,priceType,what) {
		var newvalue = what.value;
		var floatPrice = parseFloat(price);
		what.value = what.getAttribute('oldvalue');

		if ((isNaN(floatPrice) && price.length > 0)) {
			alert('Price must be a valid positive number or left blank (special and members prices only).');
		}
		else {
			if (price.length == 0 && priceType == 'pro_price') {
				alert('Price must be a valid positive number');
			} else {
				var notes =  what.getAttribute('changereason');
				if( notes.length < 10 ) {
					var blurb = 'Please enter at least 10 chars as a reason for alteration';
					notes = prompt( blurb, what.getAttribute('changereason') );
					if(notes === null)
						notes = '';
					if( notes.length < 10 )
						return;
					what.setAttribute('changereason', notes );
					if( priceType == 'pro_price' )
						document.forms.adminForm['{$name}_PriceChangeReason[]'].value = notes; 
					else
						document.forms.adminForm['{$name}_SpecialPriceChangeReason[]'].value = notes; 
				}
				what.setAttribute('oldvalue', newvalue );
				if (price.length == 0)
					price = 'NULL'; 
				else 
					what.value = floatPrice;
			}
		}
	}





		    function {$name}_UploadImages( index, newFileName) {
		    	
		    	if ({$name}_inputCount > 0) {
				  	 if ({$name}_inputCount == 1) {		  	 	
				  	 	document.forms.adminForm['{$name}_ImageAction[]'].value='Upload';
						document.forms.adminForm['{$name}_Image[]'].value=newFileName;				      
				  	  } else {				  				 	          	
				  	 	document.forms.adminForm['{$name}_ImageAction[]'].item(index).value='Upload';
						document.forms.adminForm['{$name}_Image[]'].item(index).value=newFileName;		
			          }			          
			    }
			    	    
					previewOriginal = document.getElementById('{$name}_ImagePreviewOriginal_'+index);
					previewOriginal.style.display = 'none';
								
					preview = document.getElementById('{$name}_ImagePreview_'+index);			
					preview.src = 'Custom/Cache/Incoming/' + newFileName;						
					preview.style.display = '';
					
					revert = document.getElementById('{$name}_ImageRevert_'+index);			
					revert.style.display = '';
		    }
		     
		    function {$name}_OnDelete(index) {
				document.forms.adminForm['{$name}_ImageAction[]'].item(index).value='Delete';
				document.forms.adminForm['{$name}_Image[]'].item(index).value='';		
				
				if ({$name}_inputCount > 0) {
				  	 if ({$name}_inputCount == 1) {		  	 	
				  	 	document.forms.adminForm['{$name}_ImageAction[]'].value='Delete';
						document.forms.adminForm['{$name}_Image[]'].value='';				      
				  	  } else {				  				 	          	
				  	 	document.forms.adminForm['{$name}_ImageAction[]'].item(index).value='Delete';
						document.forms.adminForm['{$name}_Image[]'].item(index).value='';		
			          }			          
			    }
			      
				
				document.getElementById('{$name}_ImageRevert_'+index).style.display='';
				document.getElementById('{$name}_ImageDelete_'+index).style.display='none';
				document.getElementById('{$name}_ImagePreview_'+index).style.display='';
				document.getElementById('{$name}_ImagePreview_'+index).src='$noImage';
				document.getElementById('{$name}_ImagePreviewOriginal_'+index).style.display='none';
			}
			
			function {$name}_OnRevert(index) {		
											
				document.getElementById('{$name}_ImagePreview_'+index).style.display='none';
				document.getElementById('{$name}_ImagePreviewOriginal_'+index).style.display='';
				document.getElementById('{$name}_ImageRevert_'+index).style.display='none';
				if ({$name}_inputCount > 0) {
				  	 if ({$name}_inputCount == 1) {
				  	 	temp = document.forms.adminForm['{$name}_ImageOriginal[]'].value;
				  	 	document.forms.adminForm['{$name}_ImageAction[]'].value='NoChange';
						document.forms.adminForm['{$name}_Image[]'].value=temp;		
						  	 	
				  	 	if (temp.length) {
				  	 		document.getElementById('{$name}_ImageDelete_'+index).style.display='';
				         }	
				  	  } else {				  				 
				  	  	document.forms.adminForm['{$name}_ImageAction[]'].item(index).value='NoChange';
						document.forms.adminForm['{$name}_Image[]'].item(index).value=document.forms.adminForm['{$name}_ImageOriginal[]'].item(index).value;						
						
			          	if (document.forms.adminForm['{$name}_ImageOriginal[]'].item(index).value.length) {
				  	 		document.getElementById('{$name}_ImageDelete_'+index).style.display='';
			          	}	
				          
			      	}          
				}
			}
						 
		 	function dump(o) {
				var s = '';
				for (var prop in o) {
					s += prop + ' = ' + o[prop] + '\\n';
				}		
				alert(s);
			}
			
		   	 function {$name}_setSelectedValue(theSelect, selectedValue) {
				var selectedIndex = -1;
				
				originalLength = document.forms.adminForm[theSelect].options.length;		
				for(var i=originalLength-1; i >= 0; i--) {			
					if (document.forms.adminForm[theSelect].options[i].value == selectedValue) {
						selectedIndex = i;
						break;
					}
				}			
				
				document.forms.adminForm[theSelect].selectedIndex = selectedIndex;
		
			 }
			 
		     function {$name}_getSelectedValue(theSelect) {
		     	if (document.forms.adminForm[theSelect]) {		     
		     		return document.forms.adminForm[theSelect].options[document.forms.adminForm[theSelect].selectedIndex].value;
		     	}
		     	return null;
			 }
			 function {$name}_getSelectedText(theSelect) {		 	
				return document.forms.adminForm[theSelect].options[document.forms.adminForm[theSelect].selectedIndex].text;
			 }
			     
		     </script>
		
		<table id="{$name}_optionsTable" cellspacing="0" cellpadding="3" border="0">
			$htmlDiscountGroups
		    <tr>
				<TD colspan="8">$priceHTML.</TD>		
			</TR>    
			<tr>
				$titles
			</TR>
			$optionsHTML
		</table>
<script>
	if (document.forms.adminForm.pr_dig_id && document.forms.adminForm.{$name}_DiGroup) {
		var temp = document.forms.adminForm.pr_dig_id.value;
		{$name}_setSelectedValue('{$name}_DiGroup', temp);
	}
</script>
EOD;
			

			return $displayHTML;
			}
		}
		
?>
