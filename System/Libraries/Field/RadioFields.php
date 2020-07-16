<?php

// ############# Radio Field ############# //

class RadioField extends RestrictedTextField {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		
		$wrapperHTML = "";
		$displayHTML = "";
		$i = 0;

		$wrapperHTML .= "<table width=\"100%\" class=\"$this->value\">\n";
		
		foreach($this->options as $option) {
			$newrow = true;
			if ($i % 2) {
				$newrow = false;
			}

			if ($newrow) {
				$wrapperHTML .= "<tr>\n<td>";
			} else {
				$wrapperHTML .= "<td>";
			}
			
			$option = ss_HTMLEditFormat($option);
			
			$checked = '';
			
			if (strlen($value) > 0) {
				if ($value == $option) {
					$checked = 'CHECKED';
				}
			} elseif ($option == $defaultValue) {
				$checked = 'CHECKED';
			}

			$displayHTML = "<INPUT NAME=\"$name\" TYPE=\"radio\" class=\"$class\" VALUE=\"$option\" $checked>&nbsp;".$option."&nbsp;&nbsp;";

			if ($newrow) {
				$wrapperHTML .= $displayHTML."</td>";
			} else {
				$wrapperHTML .= $displayHTML."</td></tr>\n";
			}

			$i++;
		}		

		if ($newrow) {
			$wrapperHTML .= "</tr>\n";
		}
		
		$wrapperHTML .= "</table>\n";
		return $wrapperHTML;		
	}
	function validate() {
		if (($this->value != NULL) && !in_array($this->value,$this->options))
			return "$this->displayName must be one of the available values.";
		return NULL;
	}

}

// ############# MultiCheck Field ############# //

class RadioFromArrayField extends Field {
	var $onClick = '';
	var $columns = 2;	
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;		
			
		// Draw the form item using the results of the query		
		$wrapperHTML = "";
		$displayHTML = "";
		
		$i = 0;
		$justDrewClosingRow = false;
		$wrapperHTML .= "<table width=\"100%\">";
		//ss_DumpVarDie($this);
		foreach ($this->options as $desc => $option) {
			if ($i % $this->columns == 0) {
				$wrapperHTML .= "<tr><td>";
			} else {
				$wrapperHTML .= "<td>";
			}
			
			
			//$escaped = ss_HTMLEditFormat($desc);
			$escaped = $desc;
			
			$checked = $value == $option? 'checked' : '';
			
			
			$displayHTML = "<input style=\"border:0px;\" type=\"radio\" name=\"{$name}\" onClick=\"{$this->onClick}\" value=\"{$option}\" class=\"$class\" $checked>&nbsp;$escaped&nbsp;\n";

			
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
		
		foreach ($this->options as $desc => $option) {									
			if ($value == $option) {	
				$result = $desc;
				break;
			}								
		}
		
		return $result;
	}
	
	function valueSQL() {		
		return $this->valueSQLText();		
	}
}


class RadioWithOtherFromArrayField extends RadioFromArrayField {
    var $textFlag = "*";
    var $otherValue = "";

	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		// Draw the form item using the results of the query
		$wrapperHTML = "";
		$displayHTML = "";

		$i = 0;
		$justDrewClosingRow = false;
		$wrapperHTML .= "<table width=\"100%\">";
		//ss_DumpVarDie($this);
		foreach ($this->options as $desc => $option) {
			if ($i % $this->columns == 0) {
				$wrapperHTML .= "<tr><td>";
			} else {
				$wrapperHTML .= "<td>";
			}


			//$escaped = ss_HTMLEditFormat($desc);
			$escaped = $desc;

			$checked = $value == $option? 'checked' : '';


            if(strpos($desc, $this->textFlag) !== false) {
                $escaped = str_replace($this->textFlag, ' &nbsp;<em>Please Specify</em>', $escaped);
			    $displayHTML = "<input style=\"border:0px;\" type=\"radio\" name=\"{$name}\" onClick=\"{$this->onClick}\" value=\"{$option}\" class=\"$class\" $checked>&nbsp;$escaped&nbsp;\n";
                $displayHTML .= "&nbsp; <input type=\"text\" name=\"{$name}_otherValue\" onClick=\"{$this->onClick}\" value=\"{$this->otherValue}\" class=\"$class\">";

            } else {
			    $displayHTML = "<input style=\"border:0px;\" type=\"radio\" name=\"{$name}\" onClick=\"{$this->onClick}\" value=\"{$option}\" class=\"$class\" $checked>&nbsp;$escaped&nbsp;\n";
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

	function displayValue($value,$otherValue=null){

		$result = '';

		foreach ($this->options as $desc => $option) {
			if ($value == $option) {
                if (strpos($desc,$this->textFlag) ) {
       //             ss_DumpVar(debug_backtrace());
       //             ss_DumpVarDie($this);
                    if ( $otherValue===null)
                        $result = $this->otherValue;
                    else
                        $result = $otherValue;
                } else
				    $result = $desc;
				break;
			}
		}

		return $result;
	}

	function processDatabaseInputValues($primaryKey = -1) {
     //   ss_DumpVar(debug_backtrace());
     //   ss_DumpVarDie($this);
	}

	function updateSQL() {
        ss_DumpVarDie($this);
		$firstName = strlen($this->value['first_name'])?"'".escape($this->value['first_name'])."'":'null';
		$lastName = strlen($this->value['last_name'])?"'".escape($this->value['last_name'])."'":'null';
		return "us_first_name = {$firstName}, us_last_name = {$lastName}";
	}

	function insertSQLField() {
        // if causing errors, check the _[Name]Asset.php save method.
        // This should have added both database fields
//        ss_DumpVarDie($this);

		return $this->name.", ".$this->name."_OtherValue";
	}

	function insertSQLValue() {
 //       ss_DumpVarDie($this);
		$value = strlen($this->value)?"'".escape($this->value)."'":'null';
		$other = strlen($this->otherValue)?"'".escape($this->otherValue)."'":'null';
		return "$value, $other";
	}

    function setOtherValue($value = NULL, $whereFrom = NULL, $dbPK = NULL){
		$this->otherValue = $value;
        // just set up like Field::setValue().
        // These do not do anything, and I'm not sure what they were intended to do
		if ($whereFrom == 'DB') {
			$this->processDatabaseInputValues($dbPK);
		} elseif ($whereFrom == 'FORM') {
			$this->processFormInputValues($dbPK);
		}
    }

	function setValues($value = NULL, $verifyValue = NULL, $whereFrom = NULL, $dbPK = NULL) {
        //ss_DumpVarDie($this);
        $this->value = $value;
		$this->verifyValue = $verifyValue;
		//ss_DumpVar($value, $whereFrom);
		if ($whereFrom == 'DB') {
			$this->processDatabaseInputValues($dbPK);
		} elseif ($whereFrom == 'FORM') {
			$this->processFormInputValues($dbPK);

		}
	}
}

?>