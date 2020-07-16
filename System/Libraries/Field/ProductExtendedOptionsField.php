<?php

class ProductExtendedOptionsField extends MultiField  {
	var $currencySettings = array();
	var $preview = '64x64';	
	function validate() {		
		$stockCodes = '';
		$hasMainOption = false;		
		//ss_DumpVarDie($this);
		// check whether the selected freight use code or not
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_HasCodes", '');	
		
		//ss_DumpVarDie($this->value);		
		$errorTypes = array(
			'StCoRe'	=> array('Msg'=>'Stock Code is a requried field.', 'Occurred'=> 0),
			'PrRe'	=> array('Msg'=>'Price is a requried field.', 'Occurred'=>0),
			'PrFiNum'	=> array('Msg'=>'Price field must be a number.','Occurred'=> 0),
			'SpPrFiNum'	=> array('Msg'=>'Special Price field must be a number.', 'Occurred'=> 0),
			'RRPrFiNum'	=> array('Msg'=>'Recommanded Retail Price field must be a number.', 'Occurred'=> 0),
			'MePrFiNum'	=> array('Msg'=>'Member Price field must be a number.', 'Occurred'=> 0),
			'SuPrFiNum'	=> array('Msg'=>'Supplier Price field must be a number.', 'Occurred'=> 0),
			'SuDiFiNum'	=> array('Msg'=>'Supplier Discount field must be a number.', 'Occurred'=> 0),
			'MainProduct'	=> array('Msg'=>'Please choose a main product option.', 'Occurred'=> 0),
			'StAvFiNum'	=> array('Msg'=>'Stock Available/Unavailable must be a number', 'Occurred'=> 0),
		);
		
		if (($this->value != 'NULL') && is_array($this->value)) {			
			
			foreach ($this->value as $key) {				
				if (ss_optionExists('Shop Product Stock Code Not Required') === false) {
					if (!strlen($key['pro_stock_code'])) {
						$errorTypes['StCoRe']['Occurred'] = 1;				
					}
				}
				if (ss_optionExists('Sell Products')) {
					if (!strlen($key['pro_price'])) {
						//return "Price is a requried field.";						
						$errorTypes['PrRe']['Occurred'] = 1;				
					}
				
					if (!is_numeric($key['pro_price']))
						$errorTypes['PrFiNum']['Occurred'] = 1;				
					
				}
				if (strlen($key['pro_special_price']) and !is_numeric($key['pro_special_price']) )
					$errorTypes['SpPrFiNum']['Occurred'] = 1;				
						
				if (strlen($key['pro_rrp_price']) and !is_numeric($key['pro_rrp_price']) )
					$errorTypes['RRPrFiNum']['Occurred'] = 1;				
					
				if (strlen($key['pro_member_price']) and !is_numeric($key['pro_member_price']) )
					$errorTypes['MePrFiNum']['Occurred'] = 1;	
					
				if (strlen($key['pro_supplier_price']) and !is_numeric($key['pro_supplier_price']) )
					$errorTypes['SuPrFiNum']['Occurred'] = 1;	
				if (strlen($key['pro_supplier_disount']) and !is_numeric($key['pro_supplier_disount']) )
					$errorTypes['SuDiFiNum']['Occurred'] = 1;	

/*
				if (strlen($key['pro_stock_available']) and !is_numeric($key['pro_stock_available']) )
					$errorTypes['StAvFiNum']['Occurred'] = 1;	

				if (strlen($key['pro_stock_unavailable']))
					if( !is_numeric($key['pro_stock_unavailable']) )
						$errorTypes['StAvFiNum']['Occurred'] = 1;	
					else
						;
				else
					$errorTypes['StAvFiNum']['Occurred'] = 1;	
*/
				$andSQL = "";
				if (ss_optionExists('Shop Product Stock Code Not Required') === false) {
					foreach($this->value as $testkey) {
						if ($testkey['pro_stock_code'] == $key['pro_stock_code'] and $testkey['pro_uuids'] != $key['pro_uuids']) {						
							if(!ListFind($stockCodes, "'N{$key['pro_stock_code']}'")) {
								$stockCodes =  ListAppend($stockCodes, "'{$key['pro_stock_code']}'");												
								
							}
							break;
						} 
					}
				
					$whereSQL = "";
					if (strlen($key['ID'])) {
						$whereSQL = "AND pro_id != {$key['ID']}";
					}
					$Q_UniquStockCode = query("
						SELECT * 
						FROM 
							{$this->linkTableName} LEFT JOIN shopsystem_products ON shopsystem_products.pr_id = {$this->linkTableName}.pro_pr_id
						WHERE 						
							pro_stock_code LIKE '".escape($key['pro_stock_code'])."'
							AND pr_deleted IS NULL						
							$whereSQL
					");
			
					if ($Q_UniquStockCode->numRows()){
						if(!ListFind($stockCodes, "'{$key['pro_stock_code']}'")) {
								$stockCodes =  ListAppend($stockCodes, "'{$key['pro_stock_code']}'");
						}
					}												
				}
				if ($key['pro_is_main'] == 1) $hasMainOption = true;
			}
		}
		if (!$hasMainOption) $errorTypes['MainProduct']['Occurred'] = 1;				
		
		$errors = '';
		if (strlen($stockCodes)) 
			$errors .= "<li>Stock Code {$stockCodes} ".ss_pluralize(ListLen($stockCodes),'is', 'are')." already existing.</LI>";				
		
		foreach ($errorTypes  as $aType) {
			if ($aType['Occurred']) {
				$errors .= "<li>".$aType['Msg']."</li>";
			}
		}
		
		
			
		if (strlen($errors)) 
			return $this->displayName."<UL>$errors</UL>";
		if (($this->value != 'NULL') && is_array($this->value)) {			
			foreach ($this->value as $key) {								
				if ($key['ImageAction'] == 'Upload') {
					// We have a new image
					$oldFileName = expandPath("Custom/Cache/Incoming/{$key['pro_image']}");
					$newFileName = ss_withTrailingSlash(expandpath($this->directory)).$key['pro_image'];
					
					if (file_exists($oldFileName)){
						rename($oldFileName, $newFileName);					
					}
									
					if (strlen($key['ImageOriginal'])) {
						ss_deleteFile(expandPath($this->directory),$key['ImageOriginal']);
					}					
				} else if ($key['ImageAction'] == 'Delete') {
					// Delete existing image	
					if (strlen($key['ImageOriginal'])) {
						ss_deleteFile(expandPath($this->directory),$key['ImageOriginal']);
					}
				}					
			}
		}
		
		return NULL;
	}
	
	
	function processFormInputValues() {
		$value = array();
		$index = 0;
		
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name, array());		
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_id", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_PriceChangeReason", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_SpecialPriceChangeReason", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_stock_code", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_supplier_sku", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_price", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_special_price", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_country_prices", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_member_price", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_country_prices", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_supplier_price", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_supplier_discount", array());		
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_Image", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_ImageAction", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_ImageOriginal", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_IsMain", array());
		
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_HasCodes", '');	
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_stock_available", '');
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_stock_unavailable", '');
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_source_currency", '');
		//ss_DumpVarDie($this);
		if (is_array($this->value)) {
			
			foreach($this->value as $aValue) {	
				
				$temp = null;		
				$isMain = 0;				
				if (count($this->fieldSet->ATTRIBUTES[$this->name."_IsMain"])) {
					if ($this->fieldSet->ATTRIBUTES[$this->name][$index] == $this->fieldSet->ATTRIBUTES[$this->name."_IsMain"][0]) {
						$isMain = 1;
					} else {
						ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_IsMain"], $index, 0);
						if ($this->fieldSet->ATTRIBUTES[$this->name."_IsMain"][$index] == '1') 	$isMain = 1;					
					}
				}
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_id"], $index, '');
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_member_price"], $index, '');
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_supplier_price"], $index, '');
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_supplier_discount"], $index, '');
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_Image"], $index, '');				
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_ImageOriginal"], $index, '');
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_ImageAction"], $index, '');				
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_stock_available"], $index, '');					
				ss_paramKey($this->fieldSet->ATTRIBUTES[$this->name."_stock_unavailable"], $index, '');					
				//ss_DumpVar($this->fieldSet->ATTRIBUTES, 'dump');
				$temp = array(					
						'pro_uuids'=>$aValue, 
						'id'=>$this->fieldSet->ATTRIBUTES[$this->name."_id"][$index], 
						'pro_stock_code'=>$this->fieldSet->ATTRIBUTES[$this->name."_stock_code"][$index], 
						'pro_supplier_sku'=>$this->fieldSet->ATTRIBUTES[$this->name."_supplier_sku"][$index], 
						'pro_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_price"][$index],
						'pro_special_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_special_price"][$index],
						'pro_member_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_member_price"][$index],
						'pro_country_price_override'=>$this->fieldSet->ATTRIBUTES[$this->name."_country_prices"][$index],
						'pro_supplier_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_supplier_price"][$index],						
						'pro_supplier_disount'=>$this->fieldSet->ATTRIBUTES[$this->name."_supplier_discount"][$index],						
						'pro_rrp_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_RRPrice"][$index],						
						'pro_image'=>$this->fieldSet->ATTRIBUTES[$this->name."_Image"][$index],
						'pro_is_main'=>$isMain,					
						'ImageAction'=>$this->fieldSet->ATTRIBUTES[$this->name."_ImageAction"][$index],
						'ImageOriginal'=>$this->fieldSet->ATTRIBUTES[$this->name."_ImageOriginal"][$index],
						'pro_stock_available'=>$this->fieldSet->ATTRIBUTES[$this->name."_stock_available"][$index],						
						'pro_stock_unavailable'=>$this->fieldSet->ATTRIBUTES[$this->name."_stock_unavailable"][$index],						
						'pro_source_currency'=>$this->fieldSet->ATTRIBUTES['pro_source_currency'],						
						'pro_weight'=>$this->fieldSet->ATTRIBUTES['pro_weight'],						
						'pro_net_weight'=>$this->fieldSet->ATTRIBUTES['pro_net_weight'],						
					);		

				$value[$index] = $temp;
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->fieldSet->ATTRIBUTES );
				//ss_DumpVarHide($value, 'extended ok', false, true);
				
				$index++;
			}
			
		}
		$this->value = $value;		
		
		
	}
	
	function specialInsert() {
		// could they have made this any more obscure?   -Rex

		//ss_DumpVarDie($this->value, "special");
		// Delete any existing linked items
		$this->delete();
		// Insert the new linked items
		if (is_array($this->value)) 
		{
			foreach ($this->value as $theirKey)
			{				
				$keys = '';
				$keyvalues = '';
				
				// extract out the linked table information for q below
				foreach ($theirKey as $key => $value)
				{
					if (substr($key, 0, 4) == "pro_")
					{
						$keys .= ss_comma($keys).$key;
						if (strlen($value)) 
							$keyvalues .= ss_comma($keyvalues)."'".escape($value)."'";
						else 
							$keyvalues .= ss_comma($keyvalues)." NULL ";
					}
				}

				if( IsSet( $pr_id ) && ( $pr_id > 0 ) )
				{
					if( array_key_exists( 'ExtendedOptions_special_priceChangeReason', $this->fieldSet->ATTRIBUTES )
					 && array_key_exists( 0, $this->fieldSet->ATTRIBUTES['ExtendedOptions_special_priceChangeReason'] )
					 && strlen( $this->fieldSet->ATTRIBUTES['ExtendedOptions_special_priceChangeReason'][0] ) )
					{
						$pr_id = $this->fieldSet->ATTRIBUTES['pr_id'];
						$notes = escape($this->fieldSet->ATTRIBUTES['ExtendedOptions_special_priceChangeReason'][0]);
						$value = $this->fieldSet->ATTRIBUTES['ExtendedOptions_special_price'][0];
						$available = $this->fieldSet->ATTRIBUTES['ExtendedOptions_stock_available'][0];
						query( "insert into price_changes (pc_us_id, pc_pr_id, pc_field_name, pc_notes, pc_amount, pc_in_stock )"
							." values (".ss_getUserID().", $pr_id, 'pro_special_price', '$notes', $value, $available )" );
					}

					if( array_key_exists( 'ExtendedOptions_priceChangeReason', $this->fieldSet->ATTRIBUTES )
					 && array_key_exists( 0, $this->fieldSet->ATTRIBUTES['ExtendedOptions_priceChangeReason'] )
					 && strlen( $this->fieldSet->ATTRIBUTES['ExtendedOptions_priceChangeReason'][0] ) )
					{
						$pr_id = $this->fieldSet->ATTRIBUTES['pr_id'];
						$notes = escape($this->fieldSet->ATTRIBUTES['ExtendedOptions_priceChangeReason'][0]);
						$value = $this->fieldSet->ATTRIBUTES['ExtendedOptions_price'][0];
						$available = $this->fieldSet->ATTRIBUTES['ExtendedOptions_stock_available'][0];
						query( "insert into price_changes (pc_us_id, pc_pr_id, pc_field_name, pc_notes, pc_amount, pc_in_stock )"
							." values (".ss_getUserID().", $pr_id, 'pro_price', '$notes', $value, $available )" );
					}
				}

				ss_log_message( "
					INSERT INTO $this->linkTableName ($this->linkTableOurKey, $keys)
					VALUES ('{$this->fieldSet->primaryKey}', $keyvalues)
					");
				
				$result = query("
					INSERT INTO $this->linkTableName ($this->linkTableOurKey, $keys)
					VALUES ('{$this->fieldSet->primaryKey}', $keyvalues)
				");
			}
		}

	}
	
	
	function processDatabaseInputValues($primaryKey = -1) {				
		// find all the linked items
		$result = Query("
			SELECT * FROM $this->linkTableName 
			WHERE $this->linkTableOurKey = $primaryKey				
		"); 
		
		// read the values from the query into the fields
		$this->value = array();
		while ($row = $result->fetchRow()) {
			array_push($this->value,$row);
		}
		$this->verifyValue = $this->value;
		
	}

	
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		
		if (!is_array($value)) {
			$value = array();
		}
		if ($this->preview != false) {
			$previewSize = $this->preview;
		} else {
			$previewSize = 'None';
		}
		$noImage = "System/Libraries/Field/Images/noimage.gif";
		if (is_array($this->currencySettings) and array_key_exists('CurrencyCode', $this->currencySettings)) {
			$priceHTML = "*All Prices are in ".$this->currencySettings['CurrencyCode']."(".$this->currencySettings['Symbol'].")";
		} else {
			$priceHTML = "&nbsp;";
		}		
						
		// Get the list of fields		
		$result = $this->options;
		$optionTitles = '';
		$optionSelectFields = '';
		$jsOptionNameDefine = '';
		$jsOptionValueDefine = '';
		$jsOptionNameValidataDefine = '';
		$displayOptions = array();
		$optionWidthStyle = "";
		if (ss_optionExists("Shop Has Really Long Options")) {
			$optionWidthStyle = ' style="width:'.ss_optionExists("Shop Has Really Long Options").'px;" ';
		}
		foreach($result as $option) {
			$optionTitles .= "<TH>{$option['name']}</TH>";
			$optionSelectFields .= "<TD><select $optionWidthStyle name=\"{$name}_{$option['uuid']}\"><option value=\"\"></option>";
			foreach($option['options'] as $subOption) {
				$optionSelectFields .= "<option value=\"{$subOption['uuid']}\">{$subOption['name']}</option>";
			}	
			$optionSelectFields .= "</TD>";			
			$jsOptionNameValidataDefine .= "tempName += {$name}_getSelectedText('{$name}_{$option['uuid']}');\n";
			
			$jsOptionNameDefine .= ss_comma($jsOptionNameDefine,"+ ' - ' + ")."{$name}_getSelectedText('{$name}_{$option['uuid']}')";			
		//	$jsOptionValueDefine .= "newOptionValue += ',{$option['uuid']}=' + {$name}_getSelectedValue('{$name}_{$option['uuid']}');\n";
			$jsOptionValueDefine .= "var selectedOption = {$name}_getSelectedValue('{$name}_{$option['uuid']}');if (selectedOption.length) { newOptionValue += ',{$option['uuid']}=' + {$name}_getSelectedValue('{$name}_{$option['uuid']}');}\n";
			$displayOptions[$option['uuid']] = $option['options'];
		}
		$jsOptionNameDefine .= " + '';";
		$previewImage = $noImage;	
		$optionsHTML = '';
		if(count($value)) {
			$optionIndex = 0;
			foreach ($value as $aValue) {
				$tempOptionName = '';
				$tempAdd = true;
				$uuids = ListToArray($aValue['pro_uuids']);
				//ss_DumpVarDie($uuids);
				$tempIsAdd = false;
				if ($tempAdd) {
					if (count($uuids)) {
						foreach ($uuids as $uuid) {
							
							$parentOptionValue = ListFirst($uuid,"=");
							$optionValue = ListLast($uuid,"=");
							
							if(strlen($uuid) and array_key_exists($parentOptionValue, $displayOptions)) {							
								foreach ($displayOptions[$parentOptionValue] as $temp) {
							
									if ($temp['uuid'] == $optionValue) {
										$tempOptionName .= ss_comma($tempOptionName," - ").$temp['name'];
										$tempIsAdd = true;
										break;
									} 							
								}
							}
						}	
					} else {						
						$tempIsAdd = true;
					}
				}		
				
				if ($tempIsAdd) {
					
					$checked = '';
					if ($aValue['pro_is_main'] == 1) {
						$checked = 'checked';
					}
							
					$imageDirectory = ss_withTrailingSlash($this->directory);
					if ($aValue['pro_image'] != NULL and strlen($aValue['pro_image'])) {						
						$imageFile = $imageDirectory.$aValue['pro_image'];
						$size = getimagesize($imageFile);
						if (ListFirst($this->preview,'x') < $size[0]) {
							$previewImage = "$imageFile";
						} else if (ListLast($this->preview,'x') < $size[1]) {
							$previewImage = "$imageFile";
						} else {											
							$previewImage = "index.php?act=ImageManager.get&Image=".ss_URLEncodedFormat($imageFile)."&Size=".$this->preview;						
						}
					} else {
						$previewImage = $noImage;
					}							
					$gotExisting = strlen($aValue['pro_image'])?'':'none';
					
					$optionsHTML .="<tr><TD><input type='radio' name='{$name}_IsMain[]' value='{$aValue['pro_uuids']}' $checked></TD><TD>$tempOptionName</TD><TD><input type='text' size='10' name='{$name}_stock_code[]' value=\"{$aValue['pro_stock_code']}\"></TD><TD><input type='text' size='10' name='{$name}_supplier_sku[]' value=\"{$aValue['pro_supplier_sku']}\"></TD><TD><input type='text' size='5' name='{$name}_price[]' value=\"{$aValue['pro_price']}\"></TD><TD><input type='text' size='5' name='{$name}_special_price[]' value=\"{$aValue['pro_special_price']}\"></TD><TD><input type='text' size='5' name='{$name}_RRPrice[]' value=\"{$aValue['pro_rrp_price']}\"></TD>";
					if (ss_optionExists('Shop Members'))
						$optionsHTML .="<TD><input type='text' size='5' name='{$name}_member_price[]' value=\"{$aValue['pro_member_price']}\"></TD>";
					$optionsHTML .="<TD><input type='text' size='35' name='{$name}_country_prices[]' value=\"{$aValue['pro_country_price_override']}\"></TD>";

					if(array_key_exists('pro_id', $aValue)) 
						$tempID = $aValue['pro_id'];
					else 
						$tempID = $aValue['ID'];											
					
					if(ss_optionExists('Shop Supplier Price')) {													
						$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_price[]' value=\"{$aValue['pro_supplier_price']}\"></TD>";
						$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_discount[]' value=\"{$aValue['pro_supplier_disount']}\"></TD>";
					}	
					$optionsHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_id[]\" VALUE=\"{$tempID}\">";
					
					if (ss_optionExists('Shop Product Option Images')) {
						$optionsHTML .="<TD><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_Image[]\" VALUE=\"{$aValue['pro_image']}\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageAction[]\" VALUE=\"NoChange\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageOriginal[]\" VALUE=\"{$aValue['pro_image']}\"><IMG ID=\"{$name}_ImagePreview_$optionIndex\" SRC=\"$noImage\" STYLE=\"display:none;border:0px\"><IMG ID=\"{$name}_ImagePreviewOriginal_$optionIndex\" SRC=\"$previewImage\" STYLE=\"border:0px\"><BR><A HREF=\"index.php?act=ImageManager.SimpleUpload&ReturnForm=adminForm&ReturnField=&ReturnJSFunction={$name}_UploadImages($optionIndex,&Preview={$previewSize}\" TARGET=\"SimpleUpload_{$name}\" ONCLICK=\"winhandle=window.open('','SimpleUpload_{$name}','width=300,height=175')\">Upload</A><SPAN STYLE=\"display:$gotExisting\" ID=\"{$name}_ImageDelete_$optionIndex\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnDelete($optionIndex);\">Delete</A></SPAN><SPAN STYLE=\"display:none;\" ID=\"{$name}_ImageRevert_$optionIndex\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnRevert($optionIndex);\">Revert</A></SPAN></TD>";       
					}
					
					
					$optionsHTML .="<TD><input type='hidden' name='{$name}_source_currency' value=\"{$aValue['pro_source_currency']}\"></TD>";

					if (ss_optionExists('Shop Product Stock Levels')) 
					{
						if( $this->fieldSet->ATTRIBUTES['pr_ve_id']->value == 99 )		// 2 -> swiss readonly
							$optionsHTML .="<TD><input type='text' readonly size='5' name='{$name}_stock_available[]' value=\"{$aValue['pro_stock_available']}\"></TD>";
						else
							$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_available[]' value=\"{$aValue['pro_stock_available']}\"></TD>";
					}
					else
						if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Advanced')) 
						{
							$inStockSelected = '';
							$outOfStockSelected = '';
							if (strlen($aValue['pro_stock_available']) == 0 or $aValue['pro_stock_available'] > 0)
								$inStockSelected = 'selected';
							else
								$outOfStockSelected = 'selected';	

							$optionsHTML .="<TD><select name='{$name}_stock_available[]'><option $inStockSelected value=''> </option><option $outOfStockSelected value='0'>Out of Stock</option></TD>";
						}
					
					$optionsHTML .= "<TD><input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'><input type='hidden' name='{$name}[]' value='{$aValue['pro_uuids']}'></TD></TR>";
					$optionIndex++;
				}
			}
			
			/*
			if($tempAdd) 						
				$optionsHTML .="<tr><TD>$tempOptionName</TD><TD>{$aValue['pro_stock_code']}</TD><TD>{$aValue['pro_price']}</TD><TD><input type='Button' class='Button' onclick='removeLine(this)' value='Delete'></TD></TR>";
			*/
			$total = count($value);
		} else {
			$total = 1;
			$optionsHTML .="<tr><TD><input type='radio' name='{$name}_IsMain[]' value='' checked></TD><TD>&nbsp;</TD><TD><input type='text' size='10' name='{$name}_stock_code[]' value=\"\"></TD><TD><input type='text' size='10' name='{$name}_supplier_sku[]' value=\"\"></TD><TD><input type='text' size='5' name='{$name}_price[]' value=\"\"></TD><TD><input type='text' size='5' name='{$name}_special_price[]' value=\"\"></TD><TD><input type='text' size='5' name='{$name}_RRPrice[]' value=\"\"></TD>";
			if (ss_optionExists('Shop Members'))
				$optionsHTML .= "<TD><input type='text' size='5' name='{$name}_member_price[]' value=\"\"></TD>";
			$optionsHTML .="<TD><input type='text' size='35' name='{$name}_country_prices[]' value=\"{$aValue['pro_country_price_override']}\"></TD>";
			if(ss_optionExists('Shop Supplier Price')) {													
				$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_price[]' value=\"\"></TD>";
				$optionsHTML .="<TD><input type='text' size='5' name='{$name}_supplier_discount[]' value=\"\"></TD>";
			}	
			
			$optionsHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_id[]\" VALUE=\"\">";          					
			if (ss_optionExists('Shop Product Option Images')) {
				$optionsHTML .="<TD><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_Image[]\" VALUE=\"\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageAction[]\" VALUE=\"NoChange\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageOriginal[]\" VALUE=\"\"><IMG ID=\"{$name}_ImagePreview_0\" SRC=\"$noImage\" STYLE=\"display:none;border:0px\"><IMG ID=\"{$name}_ImagePreviewOriginal_0\" SRC=\"$previewImage\" STYLE=\"border:0px\"><BR><A HREF=\"index.php?act=ImageManager.SimpleUpload&ReturnForm=adminForm&ReturnField=&ReturnJSFunction={$name}_UploadImages(0,&Preview={$previewSize}\" TARGET=\"SimpleUpload_{$name}\" ONCLICK=\"winhandle=window.open('','SimpleUpload_{$name}','width=300,height=175')\">Upload</A><SPAN STYLE=\"display:none\" ID=\"{$name}_ImageDelete_0\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnDelete(0);\">Delete</A></SPAN><SPAN STYLE=\"display:none;\" ID=\"{$name}_ImageRevert_0\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnRevert(0);\">Revert</A></SPAN></TD>";       
			}
			
			if (ss_optionExists('Shop Product Stock Levels')) {
				$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_available[]' value=\"\"></TD>";
				$optionsHTML .="<TD><input type='text' size='5' name='{$name}_stock_unavailable[]' value=\"\"></TD>";
			} else if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Advanced')) {
				$optionsHTML .="<TD><select name='{$name}_stock_available[]'><option value=''> </option><option value='0'>Out of Stock</option></TD>";
				$optionsHTML .="<TD><select name='{$name}_stock_unavailable[]'><option value=''> </option><option value='0'>No unavailable</option></TD>";
			}
			$optionsHTML .= "<TD><input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'><input type='hidden' name='{$name}[]' value=''></TD></TR>";						

		}
		$titles = "<TH valign=\"bottom\">Main</TH><TH valign=\"bottom\">Option</TH><TH valign=\"bottom\">Stock Code</TH><TH  valign=\"bottom\">Price</TH><TH valign=\"bottom\">Special<BR>Price</TH><TH valign=\"bottom\">RRP</TH>";
		$JSt8HTML = '';
		$JStd8HTML = '';
		$JStd8RowHTML = '';
		if (ss_optionExists('Shop Members')) {
			$titles .= "<TH valign=\"bottom\">Member<BR>Price</TH>";
			$JSt8HTML = "t8.innerHTML = \"<input type='text' size='5' name='{$name}_member_price[]' value=\"+addprice+\">\";";
			$JStd8HTML = 'td8.appendChild(t8);';
			$JStd8RowHTML = 'row.appendChild(td8);';
		}
		
		$JStSuppHTML = '';
		$JStdSuppHTML = '';
		$JStdSuppRowHTML = '';
		if (ss_optionExists('Shop Supplier Price')) {
			$titles .= "<TH valign=\"bottom\">Supplier<BR>Price</TH><TH valign=\"bottom\">Supplier<BR>Discount %</TH>";
			$JStSuppHTML = "tsupp.innerHTML = \"<input type='text' size='5' name='{$name}_supplier_price[]' value=\"+addprice+\">\";tsuppDis.innerHTML = \"<input type='text' size='5' name='{$name}_supplier_discount[]' value=\"+addprice+\">\";";
			$JStdSuppHTML = 'tdsupp.appendChild(tsupp);tdsuppDis.appendChild(tsuppDis);';
			$JStdSuppRowHTML = 'row.appendChild(tdsupp);row.appendChild(tdsuppDis);';
		}
		
		
			
		// t9.innerHTML = "<INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_id[]\" VALUE=\"\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_Image[]\" VALUE=\"\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageAction[]\" VALUE=\"NoChange\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_ImageOriginal[]\" VALUE=\"\"><IMG ID=\"{$name}_ImagePreview_" +{$name}_inputCount+"\" SRC=\"$noImage\" STYLE=\"display:none;border:0px\"><IMG ID=\"{$name}_ImagePreviewOriginal_" +{$name}_inputCount+"\" SRC=\"$previewImage\" STYLE=\"border:0px\"><BR><A HREF=\"index.php?act=ImageManager.SimpleUpload&ReturnForm=adminForm&ReturnField=&ReturnJSFunction={$name}_UploadImages("+{$name}_inputCount+",&Preview={$previewSize}\" TARGET=\"SimpleUpload_{$name}\" ONCLICK=\"winhandle=window.open('','SimpleUpload_{$name}','width=300,height=175')\">Upload</A><SPAN STYLE=\"display:none\" ID=\"{$name}_ImageDelete_"+{$name}_inputCount+"\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnDelete("+{$name}_inputCount+");\">Delete</A></SPAN><SPAN STYLE=\"display:none;\" ID=\"{$name}_ImageRevert_"+{$name}_inputCount+"\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"{$name}_OnRevert("+{$name}_inputCount+");\">Revert</A></SPAN>";          
		$JSt9HTML = 't9.innerHTML = "<INPUT TYPE=\'HIDDEN\' NAME=\'{$name}_id[]\' VALUE=\'\'>";';
		$JStd9HTML = '';
		$JStd9RowHTML = '';
		if (ss_optionExists('Shop Product Option Images')) {
			$titles .= "<TH valign=\"bottom\">Image</TH>";	
			$JSt9HTML = "t9.innerHTML = \"<INPUT TYPE='HIDDEN' NAME='{$name}_id[]' VALUE=''><INPUT TYPE='HIDDEN' NAME='{$name}_Image[]' VALUE=''><INPUT TYPE='HIDDEN' NAME='{$name}_ImageAction[]' VALUE='NoChange'><INPUT TYPE='HIDDEN' NAME='{$name}_ImageOriginal[]' VALUE=''><IMG ID='{$name}_ImagePreview_\" +{$name}_inputCount+\"' SRC='$noImage' STYLE='display:none;border:0px'><IMG ID='{$name}_ImagePreviewOriginal_\" +{$name}_inputCount+\"' SRC='$previewImage' STYLE='border:0px'><BR><A HREF=\\\"index.php?act=ImageManager.SimpleUpload&ReturnForm=adminForm&ReturnField=&ReturnJSFunction={$name}_UploadImages(\"+{$name}_inputCount+\",&Preview={$previewSize}' TARGET='SimpleUpload_{$name}' ONCLICK=\\\"winhandle=window.open('','SimpleUpload_{$name}','width=300,height=175');\\\">Upload</A><SPAN STYLE='display:none' ID='{$name}_ImageDelete_\"+{$name}_inputCount+\"'><A HREF='Javascript:void(0);' ONCLICK=\\\"{$name}_OnDelete(\"+{$name}_inputCount+\");\\\">Delete</A></SPAN><SPAN STYLE='display:none;' ID='{$name}_ImageRevert_\"+{$name}_inputCount+\"'><A HREF='Javascript:void(0);' ONCLICK='{$name}_OnRevert(\"+{$name}_inputCount+\");'>Revert</A></SPAN>\";";
			$JStd9HTML = 'td9.appendChild(t9);';
			$JStd9RowHTML = 'row.appendChild(td9);';	
		}
		if (ss_optionExists('Shop Product Stock Levels') or ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Advanced')) {
			$titles .= "<TH valign=\"bottom\">Stock<br>Availabile</TH>";			
			$titles .= "<TH valign=\"bottom\">Stock<br>Unavailabile</TH>";			
		}
		$titles .= "<TH>&nbsp;</TH>";	
			
		$previewImage = $noImage;	
		
		
		
		$htmlTypeOptions = '';
		$htmlDiscountGroupsOptions = '';
		$htmlDiscountGroups = '';
		$JSt10HTML = '';
		$JStd10HTML = '';
		$JStd10RowHTML = '';
		
		$JSt11HTML  = '';
		$JStd11RowHTML  = '';
		$JSt12HTML  = '';
		$JStd12RowHTML  = '';
		if (ss_optionExists('Shop Product Stock Levels')) {
			$JSt11HTML = "t11.innerHTML = '<input type=\'text\' size=\'5\' name=\'{$name}_stock_available[]\' value=\'\'>';";
			$JStd11RowHTML  = 'row.appendChild(td11);';
			$JSt12HTML = "t12.innerHTML = '<input type=\'text\' size=\'5\' name=\'{$name}_stock_unavailable[]\' value=\'\'>';";
			$JStd12RowHTML  = 'row.appendChild(td12);';
		} else if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Advanced')) {
			$JSt11HTML = "t11.innerHTML = '<select name=\'{$name}_stock_available[]\'><option value=\'\'> </option><option value=\'0\'>Out of Stock</option></select>';";
			$JStd11RowHTML  = 'row.appendChild(td11);';
			$JSt12HTML = "t12.innerHTML = '<select name=\'{$name}_stock_unavailable[]\'><option value=\'\'> </option><option value=\'0\'>Out of Stock</option></select>';";
			$JStd12RowHTML  = 'row.appendChild(td12);';
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
	 
	 
     
     function {$name}_addRow(id, stockcode, addprice){
          var tempName = '';
          
          $jsOptionNameValidataDefine
          
          if (!tempName.length) {
          	alert("Please Select at least one option.");
          	return false;
          } 
          
         
          var tbody = document.getElementById(id).getElementsByTagName("TBODY")[0];
          var row = document.createElement("TR");
          
          var td1 = document.createElement("TD");          
          var td2 = document.createElement("TD");               
          var td4 = document.createElement("TD");
          var td5 = document.createElement("TD");
          var td6 = document.createElement("TD");
          var td7 = document.createElement("TD");
          var td8 = document.createElement("TD");
          var td9 = document.createElement("TD");
          var td10 = document.createElement("TD");
          var td11 = document.createElement("TD");
          var td12 = document.createElement("TD");
          var tdsupp = document.createElement("TD");
          var tdsuppDis = document.createElement("TD");
          
          var t1 = document.createElement('div');
          
          var t2 = document.createElement('div');          
          var t4 = document.createElement('div');
          var t5 = document.createElement('div');
          var t6 = document.createElement('div');
          var t7 = document.createElement('div');
          var t8 = document.createElement('div');
          var t9 = document.createElement('div');
          var t10 = document.createElement('div');
          var t11 = document.createElement('div');
          var t12 = document.createElement('div');
          var t13 = document.createElement('div');
          var tsupp = document.createElement('div');
          var tsuppDis = document.createElement('div');
          
		  var newOptionValue = '';
		  $jsOptionValueDefine
		  
			if ({$name}_inputCount > 0) {
				if ({$name}_inputCount == 1) {
				
					
					if (document.forms.adminForm['{$name}[]'].value == newOptionValue) {
						alert("The new option is already existing.");
						return false;
					}	
					//alert(document.forms.adminForm['{$name}[]'].value);
				} else {
				
					for(var i=0; i <= {$name}_inputCount; i++) {          			  
						if (document.forms.adminForm['{$name}[]'].item(i)) {
							if (document.forms.adminForm['{$name}[]'].item(i).value == newOptionValue) {
								alert("The new option is already existing.");
								return false;
							}	
						}
					}
				}
			}
            
            
          
     	  

          t1.innerHTML = "<input type='radio' name='{$name}_IsMain[]' value='"+newOptionValue+"'>";
          t2.innerHTML = $jsOptionNameDefine          
          t4.innerHTML = "<input type='text' size='10' name='{$name}_stock_code[]'  value="+stockcode+">";          
          t4.innerHTML = "<input type='text' size='10' name='{$name}_supplier_sku[]'  value="+stockcode+">";          
          t5.innerHTML = "<input type='text' size='5' name='{$name}_price[]' value="+addprice+">";
          t6.innerHTML = "<input type='text' size='5' name='{$name}_special_price[]' value="+addprice+">";
          t7.innerHTML = "<input type='text' size='5' name='{$name}_RRPrice[]' value="+addprice+">";
          $JSt8HTML          
          $JStSuppHTML	
          $JSt9HTML
          $JSt10HTML
          $JSt11HTML 
          $JSt12HTML 
          t13.innerHTML = "<input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'>" + "<input type='hidden' name='{$name}[]' value='"+ newOptionValue+"'>";
          
		  
          td1.appendChild(t1);
          td2.appendChild(t2);          
          td4.appendChild(t4);
          td5.appendChild(t5);
          td6.appendChild(t6);
          td7.appendChild(t7);
          
          $JStd8HTML
          $JStdSuppHTML
          $JStd9HTML
          $JStd10HTML
          
          td11.appendChild(t11);
          td12.appendChild(t12);
          td12.appendChild(t13);
          


          row.appendChild(td1);
          row.appendChild(td2);          
          row.appendChild(td4);
          row.appendChild(td5);
          row.appendChild(td6);
          row.appendChild(td7);
          
          $JStd8RowHTML          
          $JStdSuppRowHTML
          $JStd9RowHTML
          $JStd10RowHTML                   
          $JStd11RowHTML                   
          $JStd12RowHTML                   
          
          row.appendChild(td12);

		  	
          tbody.appendChild(row);
          
          // set freight codes or value
          //set row number
          {$name}_inputCount++;
          //dump(document.forms.adminForm['{$name}[]']);
                    
                   
     }

     
     function {$name}_removeLine(object) {
          var table = document.getElementById("{$name}_optionsTable");
          var tBody = table.getElementsByTagName("tbody")[0];
          var rows = tBody.getElementsByTagName("tr");  

          while (object.tagName !=  'TR') {
               object = object.parentNode
          }     
          var row = rows[object.rowIndex]; 
     	
          tBody.removeChild(row);          
          {$name}_inputCount--;
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
     </script>





<table cellspacing="0" border="0" cellpadding="5">
	$htmlDiscountGroups
    <tr>
		$optionTitles
	</TR>
	<tr>
		$optionSelectFields	
	<TD>&nbsp;</TD>
	<TD>
	<input type="button" name="Add" value="Add Option" onClick="{$name}_addRow('{$name}_optionsTable','','')">
	</TD>
	</TR>
</table>
<table id="{$name}_optionsTable" cellspacing="0" cellpadding="3" border="0">
    <tr>
		<TD colspan="9">$priceHTML.</TD>		
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
	/*
	
	<tr>
		<TD>Option</TD><TD>Stock Code</TD><TD>Additional Price</TD><TD>"<input type='Button' class='Button' onclick='removeLine(this)' value='Delete'></TD>
	</TR>
	
	*/			
		return $displayHTML;
	}	
}



?>
