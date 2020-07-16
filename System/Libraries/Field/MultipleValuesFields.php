<?php

class FloatPerEntryFromArray extends SerializedDataField {
	
	var $cachedQuery = null;
	
	// This is just an example.. not really useful
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		
		// Grab the list of fields
		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request($this->linkQueryAction,$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$result = $result->value;
				
		
		// Deserialize the field's value
		$data = array();
		if (strlen($value)) {
			$data = unserialize($value);
		} 
		
		$displayHTML = '';
		$getDataJS = '';
		while ($row = $result->fetchRow()) {
			ss_paramKey($data,$row[$this->linkQueryValueField]);
			$displayHTML .= "<tr><td><strong>".ss_HTMLEditFormat($row[$this->linkQueryDisplayField])."</strong></td>";
			$displayHTML .= "<td><input name=\"{$name}_{$row[$this->linkQueryValueField]}\" type=\"text\" size=\"5\" value=\"".ss_HTMLEditFormat($data[$row[$this->linkQueryValueField]])."\">%</tr>";
			$getDataJS .= ss_comma($getDataJS).$row[$this->linkQueryValueField].":form.{$name}_{$row[$this->linkQueryValueField]}.value";
		}
			
		// Draw the fields and include a {$name}_getData(form) function 
		// that will return an array with all the data we wanna save		
		$displayHTML = <<< EOD
		<table>
		{$displayHTML}
		</table>
		<script language="javascript">
		function {$name}_getData(form) {
			return {{$getDataJS}};
		}
		</script>
EOD;
		return $displayHTML.$this->getSerializeJS($name);
	}

	function displayValue($value) {
		$data = array();
		if (strlen($value)) {
			$data = unserialize($value);
		}
		
		// Grab the list of fields
		if ($this->cachedQuery === null) {
			$parameters = array('NoHusk' => TRUE);
			if ($this->linkQueryParameters != NULL)  {
				$parameters = array_merge($parameters,$this->linkQueryParameters);
			}
			$temp = new Request("Security.Sudo",array('Action'=>'start'));
			$result = new Request($this->linkQueryAction,$parameters);
			$temp = new Request("Security.Sudo",array('Action'=>'stop'));
			$this->cachedQuery = $result->value;
		}
		
		$output = '';
		while ($row = $this->cachedQuery->fetchRow()) {
			ss_paramKey($data,$row[$this->linkQueryValueField]);
			if (strlen($data[$row[$this->linkQueryValueField]])) {
				$output .= ss_comma($output,', ').ss_HTMLEditFormat($row[$this->linkQueryDisplayField]).": ".ss_HTMLEditFormat($data[$row[$this->linkQueryValueField]]).'%';
			}
		}
		
		return $output;			
		
	}
}

?>
