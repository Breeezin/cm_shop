<?php

class AttributesField extends Field  {	

	var $options = array();
	var $managedBy = '';
	function processFormInputValues($value = NULL) {

		$tempValue = array();
		
		foreach($this->options as $option) {	
			ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name.'_'.$option['name'], array());								
		}
		
		for($i = 0; $i < $this->fieldSet->ATTRIBUTES[$this->name]; $i++) {
			$temp = array();
			foreach($this->options as $option) {	
				$temp[$option['name']] = $this->fieldSet->ATTRIBUTES[$this->name."_".$option['name']][$i];			
			}
			array_push($tempValue, $temp);
		}
		$this->value = $tempValue;	
		
	}

	
	
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$str_value = $value;
		if (!is_array($value)) {
			if (strlen($value))
				$value = ListToArray($value);				
			else 
				$value = array();
		} else {
			$str_value = serialize($value);
		}

		$displayButtons = true;		
		if(strlen($this->managedBy)) {
			$result = new Request("Security.Authenticate",array(
				'Permission'	=>	$this->managedBy,
				'LoginOnFail'	=>	false,
			));
		
			$hasPerm = $result->value;
			if (!$hasPerm) {
				$displayButtons = false;
			} 
		}
		
		$colTitles = '';					
		$numcols = count($this->options);
		$cols = array();
		$colJSNames = array();
		foreach ($this->options as $option) {
			$colTitles .= '<TH align="left">'.$option['title'].'</TH>';	
			
			array_push($colJSNames, "'".ss_JSStringFormat($option['name'])."'");
			if (strlen($option['permission'])) {
				$result = new Request("Security.Authenticate",array(
					'Permission'	=>	$option['permission'],
					'LoginOnFail'	=>	false,
				));
			
				$hasPerm = $result->value;
				if ($hasPerm) {
					array_push($cols, array('name'=>$option['name'], 'isForm' => true));
				} else {
					array_push($cols, array('name'=>$option['name'],'isForm' => false));
				}
			} else {
				array_push($cols, array('name'=>$option['name'], 'isForm' => true));
			}
		}
		if ($displayButtons)
			$colTitles .= "<TD><input type=\"button\" name=\"Add\" value=\"Add\" onClick=\"{$name}_addRow('{$name}_AttributesTable','','')\"></TD>";	
		$valueHTML = '';	
		
		$total = count($value);		
		
		if(count($value)) {
			$index = 0;
			foreach ($value as $aValue) {
				$valueHTML .="<TR>";
				
				foreach ($cols as $tempCol) {
					ss_paramKey($aValue, $tempCol['name'],'');		
					if ($tempCol['isForm']) {				
						$valueHTML .= "<TD><input name='{$name}_{$tempCol['name']}[]' value=\"{$aValue[$tempCol['name']]}\" type='text' size = '30'></TD>";
					} else {
						$valueHTML .= "<TD><input type='hidden' name='{$name}_{$tempCol['name']}[]' value=\"{$aValue[$tempCol['name']]}\">{$aValue[$tempCol['name']]}</TD>";
					}
				}
				if ($displayButtons)
					$valueHTML .= "<TD><input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'><TD>";
				$valueHTML .="</TR>";					
			}
		} else {
			$valueHTML .="<TR>";
			$total = 1;	
			foreach ($cols as $tempCol) {
				
				if ($tempCol['isForm']) {				
						$valueHTML .= "<TD><input name='{$name}_{$tempCol['name']}[]' value=\"\" type='text' size = '30'></TD>";
					} else {
						$valueHTML .= "<TD><input type='hidden' name='{$name}_{$tempCol['name']}[]' value=\"\"></TD>";
					}
			}
			if ($displayButtons)
				$valueHTML .= "<TD><input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'><TD>";
			$valueHTML .="</TR>";	
		}
		
		$jsShowDeleteButton = '';
		if ($displayButtons)
			$jsShowDeleteButton = " var td = document.createElement(\"TD\");            var div = document.createElement('div');         div.innerHTML = \"<input type='Button' class='Button' onclick='{$name}_removeLine(this)' value='Delete'>\";          td.appendChild(div);          row.appendChild(td); ";
		
		
		$colJSNames = ArrayToList($colJSNames);
		$displayHTML = <<< EOD

<SCRIPT LANGUAGE="JavaScript">
   				
 	function dump(o) {
		var s = '';
		for (var prop in o) {
			s += prop + ' = ' + o[prop] + '\\n';
		}		
		alert(s);
	}

     function {$name}_addRow(id){
          var tempName = '';
                            
          var tbody = document.getElementById(id).getElementsByTagName("TBODY")[0];
          var row = document.createElement("TR");
          var numCols = $numcols;
          var colNames = new Array($colJSNames);
          
          for(var i=0; i < numCols; i++) {
          	var td = document.createElement("TD");  
          	var div = document.createElement('div');
          	div.innerHTML = "<input type='text' name='{$name}_"+ colNames[i] + "[]' value='' size = '30'>";
          	td.appendChild(div);
          	row.appendChild(td);      
          	                    
          }
          
		  $jsShowDeleteButton
		      
          document.forms.AssetForm.{$name}.value = 1 + parseInt(document.forms.AssetForm.{$name}.value); 
          tbody.appendChild(row);                                                     
     }

     function {$name}_removeLine(object) {
          var table = document.getElementById("{$name}_AttributesTable");
          var tBody = table.getElementsByTagName("tbody")[0];
          var rows = tBody.getElementsByTagName("tr");  

          while (object.tagName !=  'TR') {
               object = object.parentNode
          }     
          var row = rows[object.rowIndex]; 
     		
          tBody.removeChild(row);    
          document.forms.AssetForm.{$name}.value = parseInt(document.forms.AssetForm.{$name}.value) - 1; 
     }
     </script>
<input type="hidden" name="{$name}" value="$total">
<table id="{$name}_AttributesTable" cellspacing="0" cellpadding="3" border="0">
    <tr>
		$colTitles	
	</TR>    	
	$valueHTML
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
