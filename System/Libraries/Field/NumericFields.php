<?php

class PhoneNumberField extends TextField {
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($value !== NULL) {
			$vale = number_format($value);
		}
		return "<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"".$value."\" MAXLENGTH=\"$this->maxLength\">";
	}
	function validate() {
		if ($this->value !== NULL and strlen($this->value) != 0) {

			$foo = $this->value;
			$foo = str_replace( ',', '', $foo );
			$foo = str_replace( '[', '', $foo );
			$foo = str_replace( ']', '', $foo );
			$foo = str_replace( '(', '', $foo );
			$foo = str_replace( ')', '', $foo );
			$foo = str_replace( ' ', '', $foo );
			$foo = str_replace( '-', '', $foo );
			$foo = str_replace( '.', '', $foo );

			$this->value = $foo;

			$nocommas = $foo;

			if (!is_numeric($nocommas) || (strpos($this->value,'.') !== FALSE)) return "$this->displayName must be an numeric format.";
		}
		return NULL;
	}
	function valueSQL() {
		if ($this->value !== NULL and strlen($this->value) != 0) {
			return str_replace(',','',$this->value);
		} else {
			return parent::valueSQL();
		}
	}
}


// ############# Integer Field ############# //

class IntegerField extends TextField {
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($value !== NULL) {
			$vale = number_format($value);
		}
		return "<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"".$value."\" MAXLENGTH=\"$this->maxLength\">";
	}
	function validate() {
		if ($this->value !== NULL and strlen($this->value) != 0) {

			$foo = $this->value;
			$foo = str_replace( ',', '', $foo );
			$foo = str_replace( ' ', '', $foo );
			$foo = str_replace( '.', '', $foo );

			$this->value = $foo;

			$nocommas = $foo;

			if (!is_numeric($nocommas) || (strpos($this->value,'.') !== FALSE)) return "$this->displayName must be an numeric format.";
		}
		return NULL;
	}
	function valueSQL() {
		if ($this->value !== NULL and strlen($this->value) != 0) {
			return str_replace(',','',$this->value);
		} else {
			return parent::valueSQL();
		}
	}
}

// ############# Float Field ############# //

class FloatField extends TextField {
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$onChange = isset($this->onChange) ? $this->onChange : "";
		return "<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\" $onChange>";
	}
	function validate() {
		if (($this->value != NULL) && !is_numeric($this->value)) return "$this->displayName must be a number.";
	}
}

// ############# Money Field ############# //

class MoneyField extends FloatField {
	var $format = null;
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		if ($this->format !== null and is_array($this->format)) {
			$result = '';
			if ($this->format['Appears'] == 'before') $result .= $this->format['Symbol'];
			$result .= parent::display($verify, $formName, $multi, $class);
			if ($this->format['Appears'] == 'after') $result .= $this->format['Symbol'];
			$result .= ' '.$this->format['CurrencyCode'];
			return $result;
		} else {
			return '$'.parent::display($verify, $formName, $multi, $class);
		}
	}
}

// ############# Percent Field ############# //

class PercentField extends FloatField {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		return parent::display($verify, $formName, $multi, $class).'%';
	}
}

// ############# Descriptive Integer Field ############# //

class DescriptiveIntegerField extends IntegerField {
	var $jsFunction = '';
	var $noValueDesc =  ' ';
	var $onChange = "";
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($class === null) {
			$class = $this->class;
		}
		$display = "<SELECT NAME=\"$name\" onChange=\"{$this->onChange}\" class=\"$class\">";
		if (!$this->required) $display .= "<OPTION VALUE=\"\">{$this->noValueDesc}</OPTION>";
		if (strlen($value) == 0) $value = 'NULL';
		foreach($this->options as $option => $optionValue) {
			$option = ss_HTMLEditFormat($option);
			$optionValue = ss_HTMLEditFormat($optionValue);
			$display .= "<OPTION ".(($value == $optionValue)?'SELECTED ':'')."VALUE=\"$optionValue\">$option</OPTION>";
		}
		$display .= "</SELECT><input type='hidden' name='{$name}_V' value='$value'><script>{$this->jsFunction}</script> ";
		return $display;
	}
	function validate() {

		return NULL;
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
		//ss_DumpVarDie($result);
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


}


?>
