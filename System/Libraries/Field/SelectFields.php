<?php

// ############# Select Field ############# //
// the options are from the specific query..
class SelectField extends Field {	
	var $linkQueryValueFieldIsText = false;
	var $multi = false;
	var $noValueDesc = ' ';
	var $linkQuery = '';
	var $displayValues = array();
	var $enumField = NULL;


	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($class === null) {
			$class = $this->class;
		}
		//stores all the selected values' texts
		$selectedOptions = '';
		$multi = $this->multi;		
		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		if( strlen( $this->enumField ) )
		{

		}
		else
		{
			if( strlen( $this->linkQuery ) )
				$result = query( $this->linkQuery );
			else
			{
					$result = new Request($this->linkQueryAction,$parameters);
					$result = $result->value;
			}
		}
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$onChange = isset($this->onChange) ? $this->onChange : "";
		
		// Draw the form item using the results of the query		
		$displayHTML = "";
		$multiple = $multi ? "MULTIPLE SIZE=\"$this->rows\" NAME=\"{$name}[]\"" : "NAME=\"{$name}\"";
		$disabled = '';
		if ($this->displayType == 'output') {
			 $disabled = 'disabled';
			 //$displayHTML .= "<input name=\"\"";
		}
		//bob
		$displayHTML .= "<SELECT $disabled class=\"$class\" $multiple $onChange>";
		if (!$this->required && !$multi) {
			$selected = $value == NULL ? 'SELECTED' : '';
			$displayHTML .= "<OPTION $selected VALUE=\"NULL\">{$this->noValueDesc}</OPTION>";
		}

		if( strlen( $this->enumField ) )
		{
			$result=query("SHOW COLUMNS FROM `$this->tableName` LIKE '$this->name'");
			if($row = $result->fetchRow())
			{
				$options=explode("','", preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $row['Type']));
				foreach ($options as $enum)
				{
					$selected = $value == $enum ? 'SELECTED' : '';
					$displayHTML .= "<OPTION $selected VALUE=\"'$enum'\">$enum</OPTION>";
				}
			}
		}
		else
		{
			if( $result )
				while($row = $result->fetchRow())
				{ 
					if (is_array($this->linkQueryDisplayField)) {
						$escaped = ''; $delimiterIndex = -1;
						
						if ($this->linkQueryDisplayFieldDelimiters != NULL) {
							$delimiters = $this->linkQueryDisplayFieldDelimiters;
						} else {
							$delimiters = array();
						}	
						foreach($this->linkQueryDisplayField as $displayField) {
							if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
								$escaped .=	$delimiters[$delimiterIndex];
							} else {
								$escaped .= ' ';
							}					
							$escaped .= ss_HTMLEditFormat($row[$displayField]);
							$delimiterIndex++;
						}
					} else {
						$escaped = ss_HTMLEditFormat($row[$this->linkQueryDisplayField]);
					}
					if ($multi) {
						$selected = is_array($value) && in_array($row[$this->linkQueryValueField],$value) ? 'SELECTED' : '';
						
					} else {
						$selected = $value == $row[$this->linkQueryValueField] ? 'SELECTED' : '';
					}
					if (strlen($selected)) {
						$selectedOptions = ListAppend($selectedOptions, $escaped, ",");
					}
					
					$displayHTML .= "<OPTION $selected VALUE=\"{$row[$this->linkQueryValueField]}\">$escaped</OPTION>";
				}
		}

		$displayHTML .= '</SELECT>';
		
		if ($this->displayType == 'show') {			
			//$selectedOptions = ListSort($selectedOptions,"TEXT", "ASC");
		
			$displayHTML = $selectedOptions."<input name=\"{$this->name}\" type=\"hidden\" value=\"{$value}\">";
		} 
		
		return $displayHTML;
		
	}

	function validate() {
		if (($this->value != 'NULL') && is_array($this->value)) {
			foreach ($this->value as $key) {
				if (!$this->linkQueryValueFieldIsText and !is_numeric($key)) {
					return "An internal error has occured in $this->displayName.";
				}
			}
		}
		return NULL;
	}
	
	function displayValue($value){
		$result = '';
		$result_value = NULL;
		$multi = $this->multi;
		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		if( $this->linkQueryAction )
		{
			$result = new Request($this->linkQueryAction,$parameters);
			$result_value = $result->value;
		}
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		
		$selectedValue = $value;
		
		if( strlen( $this->enumField ) )
		{
		}
		else
			if( $result_value )
			{
				while($row = $result_value->fetchRow()) {
					if (is_array($this->linkQueryDisplayField)) {
						$escaped = ''; $delimiterIndex = -1;
						
						if ($this->linkQueryDisplayFieldDelimiters != NULL) {
							$delimiters = $this->linkQueryDisplayFieldDelimiters;
						} else {
							$delimiters = array();
						}	
						foreach($this->linkQueryDisplayField as $displayField) {
							if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
								$escaped .=	$delimiters[$delimiterIndex];
							} else {
								$escaped .= ' ';
							}
							$escaped .= ss_HTMLEditFormat($row[$displayField]);
							$delimiterIndex++;
						}
					} else {
						$escaped = ss_HTMLEditFormat($row[$this->linkQueryDisplayField]);
					}
					
					if ($multi) {
						$temp = is_array($value) && in_array($row[$this->linkQueryValueField],$value) ? $escaped : '';
						$selectedValue = ListAppend($selectedValue, $temp);  			
					} else {
						//ss_DumpVar($row, $value);
						if ($value == $row[$this->linkQueryValueField]) {
							$selectedValue = $escaped;
							
							break;
						}				
					}							
				}
			}
			else
			{
				if( array_key_exists( $selectedValue, $this->displayValues ) )
					$selectedValue = $this->displayValues[$selectedValue];
				else
				{
					ss_log_message_stack( "displayValue($value -> $selectedValue) failure, \$result follows" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->displayValues );
				}
			}
		
		return $selectedValue;
	}
	
	function valueSQL() {
		if ($this->linkQueryValueFieldIsText) {
			return $this->valueSQLText();
		} else {
			return parent::valueSQL();
		}
	}
}

class MultiSelectFromArrayField extends SelectFromArrayField {
	var $multi = true;
}

// ############# MultiSelectField Field ############# //

class MultiSelectField extends MultiField {

	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;

		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		if ($class === null) {
			$class = $this->class;
		}
		// Get the list of fields
		
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request($this->linkQueryAction,$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		
		
		$result = $result->value;
	
		// Draw the form item using the results of the query		
		$displayHTML = "<SELECT MULTIPLE SIZE=\"$this->rows\" NAME=\"{$name}[]\" class=\"$class\">";

		while($row = $result->fetchRow()) { 
			if (is_array($this->linkQueryDisplayField)) {
				$escaped = ''; $delimiterIndex = -1;
				
				if ($this->linkQueryDisplayFieldDelimiters != NULL) {
					$delimiters = $this->linkQueryDisplayFieldDelimiters;
				} else {
					$delimiters = array();
				}	
				foreach($this->linkQueryDisplayField as $displayField) {
					if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
						$escaped .=	$delimiters[$delimiterIndex];
					} else {
						$escaped .= ' ';
					}
					$escaped .= ss_HTMLEditFormat($row[$displayField]);
					$delimiterIndex++;
				}
			} else {
				$escaped = ss_HTMLEditFormat($row[$this->linkQueryDisplayField]);
			}
			$selected = is_array($value) && in_array($row[$this->linkQueryValueField],$value) ? 'SELECTED' : '';
			$displayHTML .= "<OPTION $selected VALUE=\"{$row[$this->linkQueryValueField]}\">$escaped</OPTION>";
		}
		$displayHTML .= '</SELECT>';
		
		return $displayHTML;
	}
	function displayValue($value) {
		return $value;
	}

	function validate() {
		
		
		if (($this->value != 'NULL') && is_array($this->value)) {
			foreach ($this->value as $key) {
				if (!is_numeric($key)) {
					return "An internal error has occured in $this->displayName.";
				}
			}
		}
		return NULL;
	}
}


// ############# Select Field ############# //
// the options are from the user defined array 

class SelectFromArrayField extends Field {
	
	var $multi = false;	
	var $onChange = '';
	var $noValueDesc = ' ';
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($class === null) {
			$class = $this->class;
		}
		$multi = $this->multi;
	
		if ($multi) {	
			if (!is_array($value)) {
				if (strlen($value))
					$value = ListToArray($value);				
				else 
					$value = array();
			}
		}
		//ss_DumpVar($value);
		//stores all the selected values' texts
		$selectedOptions = '';
				
		// Draw the form item using the results of the query		
		$multiple = $multi ? "MULTIPLE SIZE=\"$this->rows\" NAME=\"{$name}[]\"" : "NAME=\"{$name}\"";
		$disabled = '';
		if ($this->displayType == 'output') $disabled = 'disabled';
		$displayHTML = "<SELECT $disabled class=\"$class\" $multiple {$this->onChange}>";
		if (!$this->required && !$multi) {
			$selected = $value == NULL ? 'SELECTED' : '';
			$displayHTML .= "<OPTION $selected VALUE=\"\">{$this->noValueDesc}</OPTION>";
		}

		foreach ($this->options as $desc => $option) {
			$escaped = ss_HTMLEditFormat($desc);
			
			if ($multi) {
				$selected = is_array($value) && in_array($option,$value) ? 'SELECTED' : '';			
			} else {
				$selected = $value == $option ? 'SELECTED' : '';
			}
			
			if (strlen($selected)) {
				$selectedOptions = ListAppend($selectedOptions, $escaped, ",");
			}
			
			$displayHTML .= "<OPTION $selected VALUE=\"$option\">$escaped</OPTION>";
		}
		$displayHTML .= '</SELECT>';
		/*
		if ($this->displayType == 'output') {
			$displayHTML = $selectedOptions;
		} 
		*/
		return $displayHTML;
		
	}

	function validate() {	
		
		return NULL;
	}
	
	function displayValue($value){
		$result = '';
		$multi = $this->multi;
		if (!is_array($value)) {
			$valueArray = ListToArray($value);
		} else {
			$valueArray = $value;
		}
		
		foreach ($this->options as $desc => $option) {					
			foreach ($valueArray as $aValue) {
				if ($aValue == $option) {	
					$result = ListAppend($result, " ".$desc);					
				}					
			}
		}
		
		return $result;
	}
	function valueSQL() {		
		return $this->valueSQLText();		
	}
}


class ProductOptionsField extends MultiSelectField  {
	var $currencySettings = array();
	function processFormInputValues() {
		$value = array();
		$index = 0;
		foreach($this->value as $aValue) {
			ss_paramKey($this->fieldSet->ATTRIBUTES, "{$aValue}_stock_code", "");
			ss_paramKey($this->fieldSet->ATTRIBUTES, "{$aValue}_price", 0);
			$value[$index] = array('PrOpUUID'=>$aValue, 'PrOpStockCode'=>$this->fieldSet->ATTRIBUTES["{$aValue}_stock_code"], 'PrOpAdditionalPrice'=>$this->fieldSet->ATTRIBUTES["{$aValue}_price"], );		
			$index++;
		}
		$this->value = $value;
	}
	
	function specialInsert() {
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
		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request($this->linkQueryAction,$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		
		
		$result = $result->value;
	
		// Draw the form item using the results of the query		
		$displayHTML = "<table>";
			
		while($row = $result->fetchRow()) { 
			if (is_array($this->linkQueryDisplayField)) {
				$escaped = ''; $delimiterIndex = -1;
				
				if ($this->linkQueryDisplayFieldDelimiters != NULL) {
					$delimiters = $this->linkQueryDisplayFieldDelimiters;
				} else {
					$delimiters = array();
				}	
				foreach($this->linkQueryDisplayField as $displayField) {
					if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
						$escaped .=	$delimiters[$delimiterIndex];
					} else {
						$escaped .= ' ';
					}
					$escaped .= ss_HTMLEditFormat($row[$displayField]);
					$delimiterIndex++;
				}
			} else {
				$escaped = ss_HTMLEditFormat($row[$this->linkQueryDisplayField]);
			}
			//$selected = is_array($value) && in_array($row[$this->linkQueryValueField],$value[$this->linkTableTheirKey]) ? 'CHECKED' : '';
			$selected = '';
			$index = 0;		
			
			foreach ($value as $aValue) {
				if ($row[$this->linkQueryValueField] ==$aValue[$this->linkTableTheirKey]) {
					$selected = 'checked';					
					break;
				}
				$index++;
			}
			$stock = '';
			$addPrice = '';
			if (strlen($selected)) {
				$stock = $value[$index]['PrOpStockCode'];
				$addPrice = $value[$index]['PrOpPrice'];
			}
			$displayHTML .= "<TR><TD><input type=\"checkbox\" $selected VALUE=\"{$row[$this->linkQueryValueField]}\" style=\"border:0\" NAME=\"{$name}[]\" class=\"$class\"> $escaped</TD>";
			$displayHTML .= "<TD>Stock Code: <input type=\"Text\" CLASS=\"$class\" name=\"{$row[$this->linkQueryValueField]}_stock_code\" VALUE=\"$stock\" ></TD>";
			
			$displayHTML .= "<TD>(add ";
			if ($this->currencySettings['Appears'] == "before") {
				$displayHTML .= $this->currencySettings['Symbol'];
			}
			$displayHTML .= "<input CLASS=\"$class\" size=\"3\" name=\"{$row[$this->linkQueryValueField]}_Price\" type=\"Text\" VALUE=\"$addPrice\" >";
			if ($this->currencySettings['Appears'] == "after") {
				$displayHTML .= $this->currencySettings['Symbol'];
			}
			$displayHTML .= " ".$this->currencySettings['CurrencyCode'].")</TD></TR>";
			
		}
		$displayHTML .= '</Table>';
		
		return $displayHTML;
	}	
}







// ############# Restricted Text Field ############# //

class RestrictedTextField extends TextField {
	
	var $onChange	=	null;
	var $keyIsDescription = false;
	
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($class === null) {
			$class = $this->class;
		}
		$disabled = '';
		if ($this->displayType == 'output') $disabled = 'disabled';
		$display = "<select name=\"$name\" $disabled class=\"$class\" ".($this->onChange != NULL?"ONCHANGE=\"{$this->onChange}\"":"").">";
		if (!$this->required) $display .= "<option value=\"\"></option>";		
		foreach($this->options as $key => $option) {
			$option = ss_HTMLEditFormat($option);
			$description = $option;
			if ($this->keyIsDescription) {
				$description = ss_HTMLEditFormat($key);
			}	
			$display .= "<option ".(($value == $option)?'selected ':'')."value=\"$option\">$description</option>";
		}
		$display .= "</select>";
		return $display;
	}
	function validate() {
		if (($this->value != NULL) && !in_array($this->value,$this->options))
			return "$this->displayName must be one of the available values.";
		return NULL;
	}
}

class CountryField extends RestrictedTextField {
	
	var $keyIsDescription = true;
	
	function __construct($settings) {
		parent::__construct($settings);
		if (strtolower($this->defaultValue) == 'detect') {
			$this->defaultValue = ss_getCountry();	
		}
		$Q_Countries = query("
			SELECT cn_three_code, cn_name FROM countries
			WHERE 
				cn_disabled IS NULL OR
				cn_disabled = 0
			ORDER BY cn_name
		");
		while ($row = $Q_Countries->fetchRow()) {
			if ($row['cn_three_code'] !== null) {
				$this->options[$row['cn_name']] = $row['cn_three_code'];
			}
		}
	}
	
	function displayValue($value) {
		return ss_HTMLEditFormat(array_search($value,$this->options));
	}
	
}


class SelectChildField extends Field {

	var $extraOnChange;
	var $mainField = null;
	var $updateParentField = null;
	var $noValueDesc = ' ';
	var $jsFunction = '';
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		if (!strlen($value)) $value = 0;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$maxParentID = 0;
		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request($this->linkQueryAction,$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
				
		$result = $result->value;

		// Draw the form item using the results of the query		
		$multiple = $multi ? "MULTIPLE SIZE=\"$this->rows\" NAME=\"{$name}[]\"" : "NAME=\"{$name}\"";
		
		$onChange = $this->onChange;
		//ss_DumpVarDie("nam",get_class_vars($this));
		if (isset($this->extraOnChange)) {
			$onChange .= ";{$this->extraOnChange}";
		}
		
		$displayHTML = "<SELECT $multiple OnChange=\"{$onChange}\" class=\"{$this->class}\">";
		$required = 1;
		if (!$this->required && !$multi) {
			$selected = $value == NULL ? 'SELECTED' : '';
			$displayHTML .= "<OPTION $selected VALUE=\"NULL\">{$this->noValueDesc}</OPTION>";
			$required = 0;
		}
				
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request($this->linkQueryAction,$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
				
		$result = $result->value;
		
				
		$childParameters = array('NoHusk' => TRUE);
		if ($this->linkChildQueryParameters != NULL)  {
			$childParameters = array_merge($childParameters,$this->linkChildQueryParameters);
		}
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$resultChild = new Request($this->linkChildQueryAction, $childParameters);//???? TODO
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		
		
		//ss_DumpVarDie($resultChild, 'hhe');		
		$resultChild = $resultChild->value;
		
		//ss_DumpVar('Parent', $row[$this->linkQueryValueField]);
		$jsCodes = "\n<script language='Javascript'> var parents_{$name} = new Array();\n";	
		$counterParent = 0;
		while($row = $result->fetchRow()) { 
			if ($maxParentID <= $row[$this->linkQueryValueField]) 
				$maxParentID = $row[$this->linkQueryValueField];
			if (is_array($this->linkQueryDisplayField)) {
				$escaped = ''; $delimiterIndex = -1;
				
				if ($this->linkQueryDisplayFieldDelimiters != NULL) {
					$delimiters = $this->linkQueryDisplayFieldDelimiters;
				} else {
					$delimiters = array();
				}	
				foreach($this->linkQueryDisplayField as $displayField) {
					if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
						$escaped .=	$delimiters[$delimiterIndex];
					} else {
						$escaped .= ' ';
					}
					$escaped .= ss_HTMLEditFormat($row[$displayField]);
					$delimiterIndex++;
				}
			} else {
				$escaped = ss_HTMLEditFormat($row[$this->linkQueryDisplayField]);
			}
			/*
			$counter = 0;
			while ($row = $result->fetchRow()) {
			print("client[$counter] = {id: {$row['as_id']}, name:'".ss_JSStringFormat($row['as_name'])."'};\n");
			$counter++;
			
		}
			*/
			
			$counter = 0;
			$jsCodes .= "parents_{$name}[{$row[$this->linkQueryValueField]}] = new Array();\n";
			while($aChild = $resultChild->fetchRow()) {
				//ss_DumpVar("Des", $aChild);
				if ($aChild[$this->linkChildQueryParentValueField] == $row[$this->linkQueryValueField]) {
					$tempName = '';
					if (is_array($this->linkChildQueryDisplayField)) {
						foreach ($this->linkChildQueryDisplayField as $aField) {
							$tempName .= "{$aChild[$aField]} ";
						}						
					} else {
						$tempName = $aChild[$this->linkChildQueryDisplayField];
					}
					$isMainChild = '0';
					if ($this->mainField) {
						//$isMainChild = $row[$this->mainField];
						if ($row[$this->mainField] == $aChild[$this->linkChildQueryValueField]) 
							$isMainChild = '1';
					}
					$jsCodes .= "parents_{$name}[{$row[$this->linkQueryValueField]}][$counter] = {id: {$aChild[$this->linkChildQueryValueField]}, name:'".ss_JSStringFormat($tempName)."', isMain: $isMainChild};\n";						
					$counter++;
				}
				//ss_DumpVar($aChild, $row[$this->linkQueryValueField]);
			}
	
			
			$counterParent++;
			$displayHTML .= "<OPTION ".(($value == $row[$this->linkQueryValueField])?'SELECTED':'')." VALUE=\"{$row[$this->linkQueryValueField]}\">$escaped</OPTION>";
		}
		
		$displayHTML .= '</SELECT>';
		//die("hehe");
		//ss_DumpVarDie('all', $jsCodes);
		$testCm = '';
		if( ss_isItUs()) $testCm = "alert(document.forms.adminForm.$name.selectedIndex);";
		$jsCodes .= "			
			function Init_{$name}() {\n
			selectedStateLink = -1;
			if (document.forms.adminForm.{$this->childName}_V) {
				selectedStateLink = document.forms.adminForm.{$this->childName}_V.value;\n				
			}
			
			selectedCountryLink = -1;
			if (document.forms.adminForm.$name) {
				
				if (document.forms.adminForm.$name.selectedIndex < 0) {
					document.forms.adminForm.$name.selectedIndex = 0;
					selectedCountryLink = document.forms.adminForm.$name.options[0].value;
				} else {
					selectedCountryLink = document.forms.adminForm.$name.options[document.forms.adminForm.$name.selectedIndex].value;
//					selectedCountryLink = $value;\n
				}
			}
		
			var selectedChildIndex = 0;
			//if (selectedCountryLink == 0) selectedCountryLink = -1;
			if (selectedCountryLink > 0 && document.forms.adminForm.{$this->childName}) {\n
				for (var i=(document.forms.adminForm.{$this->childName}.options.length-1); i>=0;i--) document.forms.adminForm.{$this->childName}.options[i] = null;\n
				if (!$required) {
					document.forms.adminForm.{$this->childName}.options[document.forms.adminForm.{$this->childName}.options.length] = new Option('Any','NULL');\n
				}
				for (var i=0; i< parents_{$name}[selectedCountryLink].length; i++) {\n
					var description = parents_{$name}[selectedCountryLink][i].name;			\n
					var id = parents_{$name}[selectedCountryLink][i].id;				\n
					document.forms.adminForm.{$this->childName}.options[document.forms.adminForm.{$this->childName}.options.length] = new Option(description,id);\n
					if (parents_{$name}[selectedCountryLink][i].isMain) {\n
						selectedChild = i;
					}\n
					if (selectedStateLink == id) document.forms.adminForm.{$this->childName}.selectedIndex = document.forms.adminForm.{$this->childName}.options.length-1;			\n
				}
				
			} 
			
			//document.forms.adminForm.$name.selectedIndex = selectedCountryLink;					\n
		}\n
	
	
		function UpdateState_{$name}() {\n		
			selectedCountryLink = document.forms.adminForm.$name.options[document.forms.adminForm.$name.selectedIndex].value;\n				
			selectedChild = 0;
			for (var i=(document.forms.adminForm.{$this->childName}.options.length-1); i>=0;i--) document.forms.adminForm.{$this->childName}.options[i] = null;\n
			if (selectedCountryLink == 'NULL') {
				document.forms.adminForm.{$this->childName}.options[document.forms.adminForm.{$this->childName}.options.length] = new Option('{$this->noValueDesc}','NULL');\n
			} else {
					if (!$required) {
						document.forms.adminForm.{$this->childName}.options[document.forms.adminForm.{$this->childName}.options.length] = new Option('Any','NULL');\n
					}
					for (var i=0; i< parents_{$name}[selectedCountryLink].length; i++) {\n
						//alert(parents_{$name}[selectedCountryLink].length + ' selected' + selectedCountryLink);
						
						var description = parents_{$name}[selectedCountryLink][i].name;			\n				
						var id = parents_{$name}[selectedCountryLink][i].id;				\n
						document.forms.adminForm.{$this->childName}.options[document.forms.adminForm.{$this->childName}.options.length] = new Option(description,id);\n
						if (parents_{$name}[selectedCountryLink][i].isMain) {\n
							selectedChild = i;
						}\n
						if (selectedStateLink == id) document.forms.adminForm.{$this->childName}.selectedIndex = document.forms.adminForm.{$this->childName}.options.length-1;			\n
					}					\n
				}
			//document.forms.adminForm.$name.selectedIndex = selectedCountryLink;					\n
			document.forms.adminForm.{$this->childName}.selectedIndex = selectedChild;					\n
		}\n		
		";
		
				
		$jsCodes .="{$this->jsFunction}\n</script>\n";
		return $displayHTML."\n $jsCodes \n";
	}
	function displayValue($value){
		$result = '';	
		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request($this->linkQueryAction,$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$result = $result->value;
		
		$selectedValue = '';

		while($row = $result->fetchRow()) { 			
			if (is_array($this->linkQueryDisplayField)) {
				$escaped = ''; $delimiterIndex = -1;
				
				if ($this->linkQueryDisplayFieldDelimiters != NULL) {
					$delimiters = $this->linkQueryDisplayFieldDelimiters;
				} else {
					$delimiters = array();
				}	
				foreach($this->linkQueryDisplayField as $displayField) {
					if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
						$escaped .=	$delimiters[$delimiterIndex];
					} else {
						$escaped .= ' ';
					}
					$escaped .= ss_HTMLEditFormat($row[$displayField]);
					$delimiterIndex++;
				}
			} else {
				$escaped = ss_HTMLEditFormat($row[$this->linkQueryDisplayField]);
			}
			
		
			//ss_DumpVar($row, $value);
			if ($value == $row[$this->linkQueryValueField]) {
				$selectedValue = $escaped;				
				break;
				
			}							
		}
		
		return $selectedValue;
	}
	
	function validate() {
		if (($this->value != 'NULL') && is_array($this->value)) {
			foreach ($this->value as $key) {
				if (!is_numeric($key)) {
					return "An internal error has occured in $this->displayName.";
				}
			}
		}
		return NULL;
	}	

}


?>
