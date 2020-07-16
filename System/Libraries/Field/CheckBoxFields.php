<?php




// ############# Checkbox Field ############# //

class CheckBoxField extends Field {
	var $displayValueYes = 'Yes';
	var $displayValueNo = '';
	var $onClick = '';

	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
        $class = 'checkBox';

		$checked = $value == 1 ? 'CHECKED' : '';

		if ($this->displayType == 'hidden') {
			$returnVal = "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$value\">";
		} elseif ($this->displayType == 'output') {
			$returnVal = ($value != 0 ? 'Yes' : 'No');
		} else {
			$returnVal = "<INPUT style=\"border:0px;\" TYPE=\"CHECKBOX\" NAME=\"$name\" $checked VALUE=\"1\" class=\"$class\" onClick=\"{$this->onClick}\">";
		}
		return $returnVal;
	}
	function displayValue($value) {
		if ($value == 1) {
			return $this->displayValueYes;
		} else {
			return $this->displayValueNo;
		}
	}
}


// ############# MultiCheck Field ############# //

class MultiCheckField extends MultiField {

	var $columns = 2;

	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
        $class = 'checkBox';

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
		$wrapperHTML = "";
		$displayHTML = "";
		$i = 0;
		$justDrewClosingRow = false;
		$wrapperHTML .= "<table width=\"100%\">";
		while($row = $result->fetchRow()) {
			if ($i % $this->columns == 0) {
				$wrapperHTML .= "<tr><td>";
			} else {
				$wrapperHTML .= "<td>";
			}

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
			$checked = is_array($value) && in_array($row[$this->linkQueryValueField],$value) ? 'CHECKED' : '';

			$displayHTML = "<input style=\"border:0px;\" type=\"checkbox\" name=\"{$name}[]\" value=\"{$row[$this->linkQueryValueField]}\" class=\"$class\" $checked>&nbsp;$escaped&nbsp;\n";


			if ($i % $this->columns == $this->columns-1) {
				$wrapperHTML .= $displayHTML."</td></tr>";
				$justDrewClosingRow = true;
			} else {
				$wrapperHTML .= $displayHTML."</td>";
				$justDrewClosingRow = false;
			}
			$i++;
		}
		if (!$justDrewClosingRow) {
			$wrapperHTML .= "</tr>";
		}

		$wrapperHTML .= "</table>";
		return $wrapperHTML;
	}

	function validate() {
		/*
		if (($this->value != 'NULL') && is_array($this->value)) {
			foreach ($this->value as $key) {
				if (!is_numeric($key)) {
					return "An internal error has occured in $this->displayName.";
				}
			}
		}*/
		return NULL;
	}

}

// ############# MultiCheck Field ############# //

class MultiCheckFromArrayField extends Field {

	var $columns = 2;
	var $multi = true;
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
        $class = 'checkBox';

        if (!is_array($value)) {
			if (strlen($value))
				$value = ListToArray($value);
			else
				$value = array();
		}

		// Draw the form item using the results of the query
		$wrapperHTML = "";
		$displayHTML = "";
		$i = 0;
		$justDrewClosingRow = false;
		$wrapperHTML .= "<table width=\"100%\">";
		foreach ($this->options as $desc => $option) {
			if ($i % $this->columns == 0) {
				$wrapperHTML .= "<tr><td class=\"$class\">";
			} else {
				$wrapperHTML .= "<td>";
			}


			$escaped = ss_HTMLEditFormat($desc);

			$checked = is_array($value) && in_array($option,$value) ? 'CHECKED' : '';

			$displayHTML = "<input style=\"border:0px;\" type=\"checkbox\" name=\"{$name}[]\" value=\"{$option}\" class=\"$class\" $checked>&nbsp;$escaped&nbsp;\n";

            //Briar put this in for PalSchool 2.9.05
            if (ss_OptionExists('Hide MultiCheckBox Name')){
                $displayHTML = "<input style=\"border:0px;\" type=\"checkbox\" name=\"{$name}[]\" value=\"{$option}\" class=\"$class\" $checked>&nbsp;\n";
            }

			if ($i % $this->columns == $this->columns-1) {
				$wrapperHTML .= $displayHTML."</td></tr>";
				$justDrewClosingRow = true;
			} else {
				$wrapperHTML .= $displayHTML."</td>";
				$justDrewClosingRow = false;
			}
			$i++;
		}
		if (!$justDrewClosingRow) {
			$wrapperHTML .= "</tr>";
		}

		$wrapperHTML .= "</table>";
		return $wrapperHTML;
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
					break;
				}
			}
		}

		return $result;
	}
	function valueSQL() {
		return $this->valueSQLText();
	}
}


// ############# MultiCheckArrayFromQuery Field ############# //

class MultiCheckArrayFromQueryField extends Field {

	var $columns = 2;

	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
        $class = 'checkBox';

		if (!is_array($value)) {
			if (strlen($value))
				$value = unserialize($value);
			else
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
		$wrapperHTML = "";
		$displayHTML = "";
		$i = 0;
		$justDrewClosingRow = true;
		$wrapperHTML .= "<table width=\"100%\">";
		while($row = $result->fetchRow()) {
			if ($i % $this->columns == 0) {
				$wrapperHTML .= "<tr><td>";
			} else {
				$wrapperHTML .= "<td>";
			}

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
			$checked = is_array($value) && in_array($row[$this->linkQueryValueField],$value) ? 'CHECKED' : '';

			$displayHTML = "<input style=\"border:0px;\" type=\"checkbox\" name=\"{$name}[]\" value=\"{$row[$this->linkQueryValueField]}\" class=\"$class\" $checked>&nbsp;$escaped&nbsp;\n";


			if ($i % $this->columns == $this->columns-1) {
				$wrapperHTML .= $displayHTML."</td></tr>";
				$justDrewClosingRow = true;
			} else {
				$wrapperHTML .= $displayHTML."</td>";
				$justDrewClosingRow = false;
			}
			$i++;
		}
		if (!$justDrewClosingRow) {
			$wrapperHTML .= "</tr>";
		}

		$wrapperHTML .= "</table>";
		return $wrapperHTML;
	}

	function validate() {

		return NULL;
	}

	function valueSQL() {
		return serialize($this->value);
	}
}


?>
