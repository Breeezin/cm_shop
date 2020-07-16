<?php 
	// ############# Select Field ############# //
// the options are from the specific query..
class ParentChildrenField extends Field {	

	
	var $showParentNote = 0;
	var $showParentNoteField = null;
		
	var $showTextForNoChildren = 0;
	var $showAlwaysParentAnyDesc = 0;
	
	var $parentName = '';
	var $parentAnyDesc = '';
	var $parentAction = null;
	var $parentValueField = null;
	var $parentDisplayField = null;
	var $parentQueryParams = null;
	var $parentQueryDisplayFieldDelimiters = null;
	
	var $childName = '';
	var $childAction = null;
	var $childParentKey = null;
	var $childValueField = null;
	var $childDisplayField = null;
	var $childQueryParams = null;
	var $noValueDesc = "";
	var $childDisplayValueField = null;
	
	
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($class === null) {
			$class = $this->class;
		}
		//ss_DumpVar($this->value, $value, true);
		if(strlen($value)) {
			$value_array = ListToArray($value, "&|&");
			$tempCount = count($value_array);
			if($tempCount == 1) {
				$value_array[1] = '';
				$value_array[2] = '';
			} else if ($tempCount == 2) {
				$value_array[2] = '';
			}
		} else {
			$value_array = array('','','');							
		}
		
		//ss_DumpVarDie($this, $value, true);
		
		//stores all the selected values' texts
		$selectedOptions = '';
		
		$parentParameters = array('NoHusk' => TRUE);
		if ($this->parentQueryParams != NULL)  {
			$parentParameters = array_merge($parentParameters,$this->parentQueryParams);
		}
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$parentResult = new Request($this->parentAction,$parentParameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$parentResult = $parentResult->value;
		
		$childParameters = array('NoHusk' => TRUE);
		if ($this->childQueryParams != NULL)  {
			$childParameters = array_merge($childParameters,$this->childQueryParams);
		}
		
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$childResult = new Request($this->childAction,$childParameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		//ss_DumpVarDie($childResult);	
		$childResult = $childResult->value;
		
		//ss_DumpVar('Parent', $row[$this->linkQueryValueField]);
		$jsCodes = "var parents_{$name} = new Array();\n";								
			
		// Draw the form item using the results of the query				
		$displayHTML = "<input name=\"$name\" value=\"$value\" type=\"hidden\">";		
		
		if ($this->showTextForNoChildren) {
			if ($value_array[1] == 'select') {
				$textStyle = "style=\"display:none\"";
				$selectStyle = "";			
			} else {
				$textStyle = "";
				$selectStyle = "style=\"display:none\"";
			}
		} else {
			$textStyle = "style=\"display:none\"";
			$selectStyle = "";
		}
		$displayHTML .= "<table cellpadding=\"3\">";
		$forus = '';
		//if(ss_isItUs()) $forus = 'alert(selectedParentValue + "&|&" + whichChild + "&|&" + childValue);';
		
		$one = $parentResult->numRows() == 1;
			
		// display the parent
		$extraFunc = '';
		if( isset( $this->onchangeFunction ) && strlen( $this->onchangeFunction ) > 0 )
			$extraFunc = $this->onchangeFunction;

		$displayHTML .= "<tr><td>{$this->parentName}:</td><td>";
		if( $one )
			$displayHTML .= "<div id='foobar{$name}' style='display:none'>";
		$displayHTML .= "<SELECT name=\"{$name}_Parent\" onChange=\"{$name}_checkParent(false);{$name}_updateChild(false);$extraFunc\" class=\"$class\" >";
		if ( (!$this->required or $this->showAlwaysParentAnyDesc) and (strlen($this->parentAnyDesc) > 0 ) ) 
		{
			if( $one )
				$selected = $value_array[0] == NULL ? 'SELECTED' : '';
			else
				$selected = '';
			$displayHTML .= "<OPTION $selected VALUE=\"NULL\">{$this->parentAnyDesc}</OPTION>";
		}
		$pa = array();
		$escaped = '';
		while($row = $parentResult->fetchRow())
		{ 
			if (is_array($this->parentDisplayField)) {
				$escaped = '';
				$delimiterIndex = -1;
				
				if ($this->parentQueryDisplayFieldDelimiters != NULL) {
					$delimiters = $this->parentQueryDisplayFieldDelimiters;
				} else {
					$delimiters = array();
				}	
				foreach($this->parentDisplayField as $displayField) {
					if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
						$escaped .=	$delimiters[$delimiterIndex];
					} else {
						$escaped .= ' ';
					}
					$escaped .= ss_HTMLEditFormat($row[$displayField]);
					$delimiterIndex++;
				}
			} else {
				$escaped = ss_HTMLEditFormat($row[$this->parentDisplayField]);
			}
			
			if( $value_array[0] != NULL )
				$selected = ($one || $value_array[0] == $row[$this->parentValueField] ) ? 'SELECTED' : '';
			else
				$selected = ($one || ( IsSet( $this->selectedValue ) && $this->selectedValue == $row[$this->parentValueField] ) ) ? 'SELECTED' : '';

			if (strlen($selected)) {
				$selectedOptions = ListAppend($selectedOptions, $escaped, ",");
			}
			$note = '';
			if ($this->showParentNote) {
				if ($this->showParentNoteField != null) {
					$note = "desc=\"".ss_HTMLEditFormat($row[$this->showParentNoteField])."\"";
				}
			}
			
			$displayHTML .= "<OPTION $selected VALUE=\"{$row[$this->parentValueField]}\" $note>$escaped</OPTION>";
			$jsCodes .= "parents_{$name}[{$row[$this->parentValueField]}] = new Array();\n";
			$pa[] = $row[$this->parentValueField];
		}
		$displayHTML .= '</SELECT>';
		if( $one )
			$displayHTML .= '</div>'.$escaped;
		$displayHTML .= '</td></tr>';


		// display the child
		$displayHTML .= "<tr><td>{$this->childName}:</td>";
		$displayHTML .= "<td><span id=\"{$name}_ChildText_span\" $textStyle><input name=\"{$name}_ChildText\" type=\"text\" size=\"{$this->size}\" onFocus=\"{$name}_checkParent(false)\" onChange=\"{$name}_update()\"></span>";
		$displayHTML .= "<span id=\"{$name}_ChildSelect_span\" $selectStyle><select name=\"{$name}_ChildSelect\" onFocus=\"{$name}_checkParent(false)\" onChange=\"{$name}_update()\"></select></span></td></tr>";
		
		$displayHTML .= '</table>';
		if ($this->showParentNote != null) {
			$displayHTML .="<BR><span id='{$name}_ParentNote'></span>";
		}
		/*
		if ($this->displayType == 'output') {
			
			//$selectedOptions = ListSort($selectedOptions,"TEXT", "ASC");
			$displayHTML = $selectedOptions;
		} 
		*/
		// add 'Please select' option for acme express
		$acmeAdd ="";
		if (ss_optionExists('Acme Country State Not Required'))
			$acmeAdd = "{$name}_theForm.{$name}_ChildSelect.options[{$name}_theForm.{$name}_ChildSelect.options.length] = new Option('Please select', '');";	
		while($aChild = $childResult->fetchRow()) {			
			
			$tempName = '';
			if (is_array($this->childDisplayField)) {
				foreach ($this->childDisplayField as $aField) {
					$tempName .= "{$aChild[$aField]} ";
				}						
			} else {
				$tempName = $aChild[$this->childDisplayField];
			}
			
			if( in_array( $aChild[$this->childParentKey], $pa ) )
				$jsCodes .= "parents_{$name}[{$aChild[$this->childParentKey]}][parents_{$name}[{$aChild[$this->childParentKey]}].length] = {id: {$aChild[$this->childValueField]}, name:'".ss_JSStringFormat($tempName)."'};\n";											
		}
		$display = <<< EOD
	<script language='Javascript'>
		
		var {$name}_theForm = null;
				
		if (document.forms.adminForm) {
			{$name}_theForm = document.forms.adminForm;			
		} else {
			{$name}_theForm = document.forms.CheckoutForm;			
		}
		function dump(o) {
		var s = '';
		for (var prop in o) {
			s += prop + ' = ' + o[prop] + '<BR>';
		}		
		newwin = window.open();		
		newwin.document.write(s);
		
	}
	
		$jsCodes	
		function {$name}_checkParent(isInit) {
			
			var whichChild = 'text';
			var selectedParentIndex = {$name}_theForm.{$name}_Parent.selectedIndex;
			var selectedParentValue = {$name}_theForm.{$name}_Parent.options[{$name}_theForm.{$name}_Parent.selectedIndex].value;
			
			if(selectedParentValue == 'NULL' || selectedParentValue.length == 0) {
				if (!isInit)
					alert("Please select {$this->parentName} first.");
					{$name}_theForm.{$name}_Parent.focus();
				return void(0);
			}
			
			if (parents_{$name}[selectedParentValue].length) {
				whichChild = 'select';			
			}			
			if ({$this->showTextForNoChildren}) {				
				if (whichChild == 'text') {
					if (isInit) {
						{$name}_theForm.{$name}_ChildText.value = "{$value_array[2]}";
					}
					document.getElementById('{$name}_ChildText_span').style.display = '';				
					document.getElementById('{$name}_ChildSelect_span').style.display = 'none';
					
				} else {					
					document.getElementById('{$name}_ChildText_span').style.display = 'none';	
					document.getElementById('{$name}_ChildSelect_span').style.display = '';
					if (isInit) { 
						
						for(var temp =0; temp < {$name}_theForm.{$name}_ChildSelect.options.length; temp++) {
							
							if ({$name}_theForm.{$name}_ChildSelect.options[temp].value == "{$value_array[2]}") {
								{$name}_theForm.{$name}_ChildSelect.selectedIndex = temp;
								break;
							}
						}
					}
				}					
			} 
			
			//{$name}_theForm.{$name}.value = selectedParentValue + "&|&" + whichChild + "&|&";			
		}	
		function {$name}_updateChild(isInit) {
			var selectedParentIndex = {$name}_theForm.{$name}_Parent.selectedIndex;
			var selectedParentValue = {$name}_theForm.{$name}_Parent.options[{$name}_theForm.{$name}_Parent.selectedIndex].value;			
			var whichChild = 'text';
		
			if(selectedParentValue == 'NULL'  || selectedParentValue.length == 0) return void(0);
			
			if ({$this->showParentNote}) {
				//dump({$name}_theForm.{$name}_Parent.options[{$name}_theForm.{$name}_Parent.selectedIndex]);
				if( {$name}_theForm.{$name}_Parent.options[{$name}_theForm.{$name}_Parent.selectedIndex].desc) {			
					document.getElementById('{$name}_ParentNote').innerHTML = {$name}_theForm.{$name}_Parent.options[{$name}_theForm.{$name}_Parent.selectedIndex].desc;
					//alert("yes" +selectedParentValue);
				} else {
					document.getElementById('{$name}_ParentNote').innerHTML = '';
					//alert("no" +selectedParentValue);
				}
			}			
			if (parents_{$name}[selectedParentValue].length) {
				whichChild = 'select';			
			}
			
			if (!{$this->showTextForNoChildren}) {									
				whichChild = 'select';
			}
			if (whichChild == 'select') {
				for(var j= {$name}_theForm.{$name}_ChildSelect.options.length-1; j >= 0; j--) {
					{$name}_theForm.{$name}_ChildSelect.options[j] = null;
				} 
				//alert(parents_{$name}[selectedParentValue].length);df
				$acmeAdd
				for(var h=0; h < parents_{$name}[selectedParentValue].length; h++){					
					if (parents_{$name}[selectedParentValue][h].name.length) 
						{$name}_theForm.{$name}_ChildSelect.options[{$name}_theForm.{$name}_ChildSelect.options.length] = new Option(parents_{$name}[selectedParentValue][h].name, parents_{$name}[selectedParentValue][h].id);
				}
			}
			if (!isInit)	{$name}_update();			
		}
		function {$name}_update() {
			var whichChild = 'text';
			var selectedParentIndex = {$name}_theForm.{$name}_Parent.selectedIndex;
			var selectedParentValue = {$name}_theForm.{$name}_Parent.options[{$name}_theForm.{$name}_Parent.selectedIndex].value;			

			if(selectedParentValue == 'NULL' || selectedParentValue.length == 0) {
				{$name}_theForm.{$name}.value = 'NULL';
				return void(0);
			}
			if (parents_{$name}[selectedParentValue].length) {
				whichChild = 'select';			
			}			
		
			if ({$this->showTextForNoChildren}) {					
				if (whichChild == 'text') {
					childValue = {$name}_theForm.{$name}_ChildText.value;
				} else  {
					childValue = {$name}_theForm.{$name}_ChildSelect.options[{$name}_theForm.{$name}_ChildSelect.selectedIndex].value;				
				}
			} else {
				whichChild = 'select';
				childValue = {$name}_theForm.{$name}_ChildText.options[{$name}_theForm.{$name}_ChildText.selectedIndex].value;
			}
						
			{$name}_theForm.{$name}.value = selectedParentValue + "&|&" + whichChild + "&|&" + childValue;			
			$forus
		}
		
	</script>
	$displayHTML
	<script language='Javascript'>
		{$name}_updateChild(true);
		{$name}_checkParent(true);
		
	</script>		
EOD;
		
		
		return $display;
		
	}

	
	function displayValue($value){
		
		$parentParameters = array('NoHusk' => TRUE);
		if ($this->parentQueryParams != NULL)  {
			$parentParameters = array_merge($parentParameters,$this->parentQueryParams);
		}
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$parentResult = new Request($this->parentAction,$parentParameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$parentResult = $parentResult->value;
		
		$childParameters = array('NoHusk' => TRUE);
		if ($this->childQueryParams != NULL)  {
			$childParameters = array_merge($childParameters,$this->childQueryParams);
		}
		
		// Get the list of fields
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$childResult = new Request($this->childAction,$childParameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		//ss_DumpVarDie($childResult);	
		$childResult = $childResult->value;
		
		if(strlen($value)) {
			$value_array = ListToArray($value, "&|&");
			$tempCount = count($value_array);
			if($tempCount == 1) {
				$value_array[1] = '';
				$value_array[2] = '';
			} else if ($tempCount == 2) {
				$value_array[2] = '';
			}
		} else {
			return '';
		}
		
		$selectedValue = '';
		if ($value_array[1] == 'select') {
			while($row = $childResult->fetchRow()) {			
				if ($row[$this->childValueField] == $value_array[2]) {
					$tempName = '';
					if (is_array($this->childDisplayValueField)) {
						foreach ($this->childDisplayValueField as $aField) {
							$tempName .= "{$row[$aField]} ";
						}						
					} else {
						$tempName = $row[$this->childDisplayValueField];
					}
					$selectedValue .= $tempName;
					break;
				}			
			}
		} else {
			$selectedValue .= $value_array[2];
		}
		
		while($row = $parentResult->fetchRow()) {
			if ($row[$this->parentValueField] == $value_array[0]) {
				if (is_array($this->parentDisplayField)) {
					$escaped = '';
					$delimiterIndex = -1;
					
					if ($this->parentQueryDisplayFieldDelimiters != NULL) {
						$delimiters = $this->parentQueryDisplayFieldDelimiters;
					} else {
						$delimiters = array();
					}	
					foreach($this->parentDisplayField as $displayField) {
						if (count($delimiters) and array_key_exists($delimiterIndex,$delimiters)) {
							$escaped .=	$delimiters[$delimiterIndex];
						} else {
							$escaped .= ' ';
						}
						$escaped .= ss_HTMLEditFormat($row[$displayField]);
						$delimiterIndex++;
					}
				} else {
					$escaped = ss_HTMLEditFormat($row[$this->parentDisplayField]);
				}	
				$selectedValue .= "<BR>".$escaped;
				break;
			}			
		}
		
		return $selectedValue;
	}
	
	function valueSQL() {		
		return $this->valueSQLText();		
	}
	
	function validate() {
		if ($this->required) {
			$value = $this->value;
			//ss_DumpVar($this->value ,$this->name, true);
			if(strlen($value)) {
				$value_array = ListToArray($value, "&|&");
				//ss_DumpVar($value_array, $value);
				$tempCount = count($value_array);
				if($tempCount == 1) {
					if (ss_optionExists('Acme Country State Not Required') and substr($this->name,0,2) == 'Us') {
						
					} else {
						return ($this->displayName." - ".$this->childName." is required.");
					}
				} else if ($tempCount == 2) {
					if ($value_array[1] == 'select')
						return ($this->displayName." - ".$this->childName." is required.");

					/*
					if (ss_optionExists('Acme Country State Not Required') and substr($this->name,0,2) == 'Us') {
						if ($value_array[1] == 'select') {
							return ($this->displayName." - ".$this->childName." is required.");
						}					
					} else {				
						return ($this->displayName." - ".$this->childName." is required.");
					}
					*/
				} else {
					if (!strlen($value_array[0])) {
						return ($this->displayName." - ".$this->parentName." is required.");
					}
					if (ss_optionExists('Acme Country State Not Required') and substr($this->name,0,2) == 'Us') {					
						if ($value_array[1] == 'select' and !strlen($value_array[2])) {
							return ($this->displayName." - ".$this->childName." is required.");
						}					
					} else {
						if (!strlen($value_array[2])) {
							return ($this->displayName." - ".$this->childName." is required.");
						}					
					}
				}		
			}
		}
	}
}
?>
