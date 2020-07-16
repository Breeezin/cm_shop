<?php
class ProductExtendedOptionsField extends MultiField  {
	var $currencySettings = array();
	
	function validate() {		
		$stockCodes = '';
		if (($this->value != 'NULL') && is_array($this->value)) {			
			foreach ($this->value as $key) {
				if (!strlen($key['pro_stock_code'])) {
					return "Stock Code is a requried field.";	
				}
				if (!strlen($key['pro_price'])) {
					return "Price is a requried field.";	
				}
				
				if (!is_numeric($key['pro_price']))
					return "Price field must be a number.";
				
				if (strlen($key['pro_special_price'] and !is_numeric($key['pro_special_price'])) )
					return "Special Price field must be a number.";
						
				if (strlen($key['pro_rrp_price'] and !is_numeric($key['pro_rrp_price'])) )
					return "Recommanded Retail Price field must be a number.";
					
				if (strlen($key['pro_member_price'] and !is_numeric($key['pro_member_price'])) )
					return "Member Price field must be a number.";	
				
				$Q_UniquStockCode = query("
					SELECT * 
					FROM 
						$this->linkTableName 
					WHERE 						
						pro_stock_code LIKE '{$key['pro_stock_code']}'							
				");
				if ($Q_UniquStockCode->numRows()){
					$stockCodes =  ListAppend($stockCodes, "'{$key['pro_stock_code']}'");
				}	
			}
		}
		if (strlen($stockCodes)) 
			return  "Stock Code {$stockCodes} ".ss_pluralize(ListLen($stockCodes),'is', 'are')." already existing.";
		return NULL;
	}
	
	
	function processFormInputValues() {
		$value = array();
		$index = 0;
		
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name, array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_StockCode", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_Price", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_SpecialPrice", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_MemberPrice", array());
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name."_RRPrice", array());
		
		foreach($this->value as $aValue) {			
			$value[$index] = array(
					'pro_uuids'=>$aValue, 
					'pro_stock_code'=>$this->fieldSet->ATTRIBUTES[$this->name."_StockCode"][$index], 
					'pro_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_Price"][$index],
					'pro_special_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_SpecialPrice"][$index],
					'pro_member_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_MemberPrice"][$index],
					'pro_rrp_price'=>$this->fieldSet->ATTRIBUTES[$this->name."_RRPrice"][$index],
					); 
			$index++;
		}
		$this->value = $value;		
		
	}
	
	function specialInsert() {
		//ss_DumpVarDie($this->value, "special");
		// Delete any existing linked items
		$this->delete();
		// Insert the new linked items
		if (is_array($this->value)) {
			foreach ($this->value as $theirKey) {				
				$keys = '';
				$keyvalues = '';
				
				foreach ($theirKey as $key => $value) {
					$keys .= ss_comma($keys).$key;
					$keyvalues .= ss_comma($keyvalues)."'".$value."'";
				}
				
				$result = query("
					INSERT INTO $this->linkTableName ($this->linkTableOurKey, $keys)
					VALUES ('{$this->fieldSet->primaryKey}', $keyvalues)
				");
			}
		}

	}
	
	
	function processDatabaseInputValues($primaryKey) {				
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
		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		
		$additionalPriceHTML = 'Price ('.$this->currencySettings['Symbol'].$this->currencySettings['CurrencyCode'].")";
		$specialPriceHTML = 'Special Price ('.$this->currencySettings['Symbol'].$this->currencySettings['CurrencyCode'].")";
		$rrPriceHTML = 'RRP ('.$this->currencySettings['Symbol'].$this->currencySettings['CurrencyCode'].")";
		$memberPriceHTML = 'Member Price ('.$this->currencySettings['Symbol'].$this->currencySettings['CurrencyCode'].")";
			
			
		// Get the list of fields		
		$result = $this->options;
		$optionTitles = '';
		$optionSelectFields = '';
		$jsOptionNameDefine = '';
		$jsOptionValueDefine = '';
		$jsOptionNameValidataDefine = '';
		$displayOptions = array();
		foreach($result as $option) {
			$optionTitles .= "<TH>{$option['name']}</TH>";
			$optionSelectFields .= "<TD><select name=\"{$name}_{$option['uuid']}\"><option value=\"\"></option>";
			foreach($option['options'] as $subOption) {
				$optionSelectFields .= "<option value=\"{$subOption['uuid']}\">{$subOption['name']}</option>";
			}	
			$optionSelectFields .= "</TD>";			
			$jsOptionNameValidataDefine .= "tempName += {$name}_getSelectedText('{$name}_{$option['uuid']}');\n";
			
			$jsOptionNameDefine .= ss_comma($jsOptionNameDefine,"+ ' - ' + ")."{$name}_getSelectedText('{$name}_{$option['uuid']}')";			
		//	$jsOptionValueDefine .= "newOptionValue += ',{$option['uuid']}=' + {$name}_getSelectedValue('{$name}_{$option['uuid']}');\n";
			$jsOptionValueDefine .= "if ({$name}_getSelectedValue('{$name}_{$option['uuid']}').length) { newOptionValue += ',{$option['uuid']}=' + {$name}_getSelectedValue('{$name}_{$option['uuid']}');}\n";
			$displayOptions[$option['uuid']] = $option['options'];
		}
		$jsOptionNameDefine .= " + '';";
		
		$optionsHTML = '';
		if(count($value)) {
			$optionIndex = 1;
			foreach ($value as $aValue) {
				$tempOptionName = '';
				$tempAdd = true;
				$uuids = ListToArray($aValue['pro_uuids']);
				//ss_DumpVarDie($uuids);
				$tempIsAdd = false;
				if ($tempAdd) {
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
				}		
			
				if ($tempIsAdd) {
					
					$optionsHTML .="<tr><TD>$tempOptionName</TD><TD><input type='text' size='10' name='{$name}_StockCode[]' value=\"{$aValue['pro_stock_code']}\"></TD><TD><input type='text' size='10' name='{$name}_Price[]' value=\"{$aValue['pro_price']}\"></TD><TD><input type='text' size='10' name='{$name}_SpecialPrice[]' value=\"{$aValue['pro_special_price']}\"></TD><TD><input type='text' size='10' name='{$name}_RRPrice[]' value=\"{$aValue['pro_rrp_price']}\"></TD><TD><input type='text' size='10' name='{$name}_MemberPrice[]' value=\"{$aValue['pro_member_price']}\"></TD><TD><input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'><input type='hidden' name='{$name}[]' value='{$aValue['pro_uuids']}'><input type='hidden' name='{$name}_$optionIndex' value='{$aValue['pro_uuids']}'></TD></TR>";										
					$optionIndex++;
				}
			}
			/*
			if($tempAdd) 						
				$optionsHTML .="<tr><TD>$tempOptionName</TD><TD>{$aValue['pro_stock_code']}</TD><TD>{$aValue['pro_price']}</TD><TD><input type='Button' class='Button' onclick='removeLine(this)' value='Delete'></TD></TR>";
			*/
		}
		$total = count($value) - 1;
		if ($total < 0)	$total = 0;
		$displayHTML = <<< EOD
<html>
<body>
<SCRIPT LANGUAGE="JavaScript">
     var {$name}_inputCount = {$total};
     
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
     	return document.forms.adminForm[theSelect].options[document.forms.adminForm[theSelect].selectedIndex].value;
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
          var td3 = document.createElement("TD");          
          var td4 = document.createElement("TD");
          var td5 = document.createElement("TD");
          var td6 = document.createElement("TD");
          var td7 = document.createElement("TD");
          
          
          var t1 = document.createElement('div');
          var t2 = document.createElement('div');
          var t3 = document.createElement('div');
          var t4 = document.createElement('div');
          var t5 = document.createElement('div');
          var t6 = document.createElement('div');
          var t7 = document.createElement('div');
          
		  var newOptionValue = '';
		  $jsOptionValueDefine
		  if ({$name}_inputCount > 0) {
		  	 if ({$name}_inputCount == 1) {
		  	 	if (document.forms.adminForm['{$name}[]'].value == newOptionValue) {
		  	 		alert("The new option is already existing.");
		          		return false;
		          }	
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
            
          
     	  //set row number
          {$name}_inputCount++;

          t1.innerHTML = $jsOptionNameDefine
          t2.innerHTML = "<input type='text' size='10' name='{$name}_StockCode[]'  value="+stockcode+">";
          t3.innerHTML = "<input type='text' size='10' name='{$name}_Price[]' value="+addprice+">";
          t4.innerHTML = "<input type='text' size='10' name='{$name}_SpecialPrice[]' value="+addprice+">";
          t5.innerHTML = "<input type='text' size='10' name='{$name}_RRPrice[]' value="+addprice+">";
          t6.innerHTML = "<input type='text' size='10' name='{$name}_MemberPrice[]' value="+addprice+">";
          t7.innerHTML = "<input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'>" + "<input type='hidden' name='{$name}[]' value='"+ newOptionValue+"'><input type='hidden' name='{$name}_"+{$name}_inputCount+"' value='"+ newOptionValue+"'>";
		  
          td1.appendChild(t1);
          td2.appendChild(t2);
          td3.appendChild(t3);
          td4.appendChild(t4);
          td5.appendChild(t5);
          td6.appendChild(t6);
          td7.appendChild(t7);


          row.appendChild(td1);
          row.appendChild(td2);
          row.appendChild(td3);
          row.appendChild(td4);
          row.appendChild(td5);
          row.appendChild(td6);
          row.appendChild(td7);


          tbody.appendChild(row);
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
     </script>





<table cellspacing="0" border="0" cellpadding="5">
    <tr>
		$optionTitles
	</TR>
	<tr>
		$optionSelectFields
	</TR>
	
</table>
<input type="button" name="Add" value="Add Option" onClick="{$name}_addRow('{$name}_optionsTable','','')">
<BR>
<table id="{$name}_optionsTable" cellspacing="0" cellpadding="5" border="0">
    <tr>
		<TD>Option</TD><TD>Stock Code</TD><TD>$additionalPriceHTML</TD><TD>$specialPriceHTML</TD><TD>$rrPriceHTML</TD><TD>$memberPriceHTML</TD><TD>&nbsp;</TD>		
	</TR>
	$optionsHTML
</table>

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