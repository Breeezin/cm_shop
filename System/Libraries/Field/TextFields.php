<?php


class TextField extends Field {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if ($class === null) {
			$class = $this->class;
		}
		$disabled = '';
		if ($this->disabled) {
			$disabled = 'disabled';
		}
		$onChange = isset($this->onChange) ? $this->onChange : "";
		if ($this->displayType == 'hidden') {
			$returnVal = "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$value\" $disabled $onChange>";
		} elseif ($this->displayType == 'output') {
			$returnVal = "$value";
		} else {
			$returnVal = "<INPUT TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\" CLASS=\"$class\" $disabled $onChange>";
		}
		return $returnVal;
	}
	function displayValue($value) {
		return $value;
	}
	function validate() {
		$this->value = trim( $this->value );
		if( preg_match( '/&#[0-9]{4,6};/', $this->value ) && !ss_isAdmin() )
		{
			ss_log_message( "TextField::validate( ".$this->value." ) length is ".strlen($this->value ) );
			ss_log_message( "Not ISO-88959-1" );
			return ss_HTMLEditFormat($this->displayName)." Needs to be in ISO-8859-1 format.";
		}
//		ss_log_message( "TextField:".$this->displayName.":validate( ".$this->value." ) length is ".strlen($this->value ) );
//		if( $this->required &&  (strlen( $this->value ) == 0) )
//			return ss_HTMLEditFormat($this->displayName)." is required.";
		return NULL;
	}
	function valueSQL() {
		return $this->valueSQLText();
	}
}


class NameField extends Field {

	var $defaultValue = array('first_name'=>'','last_name'=>'');
	var $size = 13;

	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$name  = $verify ? $this->name.'_V'   : $this->name;

		$disabled = '';
		if ($this->disabled) {
			$disabled = 'disabled';
		}
		if ($this->displayType == 'hidden') {
			$returnVal = "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$value\" $disabled>";
		} elseif ($this->displayType == 'output') {
			$returnVal = "$value";
		} else {
			$useValue = $verify ? $this->verifyValue : $this->value;

			$firstNameValue = ss_HTMLEditFormat($useValue['first_name']);
			$lastNameValue = ss_HTMLEditFormat($useValue['last_name']);
			$returnVal = "<table cellpadding=\"0\" cellspacing=\"0\"><tr>".
				"<td valign=top nowrap><input type=\"text\" size=\"{$this->size}\" name=\"{$name}[first_name]\" VALUE=\"$firstNameValue\" maxlength=\"$this->maxLength\" class=\"$class\" $disabled>&nbsp;</td>".
				"<td valign=top><input type=\"text\" size=\"{$this->size}\" name=\"{$name}[last_name]\" VALUE=\"$lastNameValue\" maxlength=\"$this->maxLength\" class=\"$class\" $disabled>".
				"</td></tr><tr><td>First Name</td><td>Last Name</td></tr></table>";
		}
		return $returnVal;
	}
	function processDatabaseInputValues($primaryKey = -1) {
		if (strlen($this->value)) {
			$tab = chr(9);
			$values = explode($tab,$this->value);
			$this->value = array('first_name' => $values[0],'last_name' => $values[1]);
		} else {
			if ($this->name == 'us_name') {
				$name = getRow("SELECT us_first_name,us_last_name FROM users WHERE us_id = $primaryKey");
				$this->value = array('first_name' => $name['us_first_name'],'last_name' => $name['us_last_name']);
			} else {
				$this->value = array('first_name' => null,'last_name' => null);
			}
		}
	}
	function displayValue($value) {
		//ss_DumpVarDie($value);
		if ($this->name != 'us_name') {

			if(!is_array($value)) {
				$tab = chr(9);
				$values = explode($tab,$value);
				$value = array('first_name' => $values[0],'last_name' => $values[1]);
			}
		}

		return $value['first_name']." ".$value['last_name'];
	}
	function displayFullName($value) {
		//ss_DumpVarDie($value);
		if ($this->name != 'us_name') {

			if(!is_array($value)) {
				$tab = chr(9);
				$values = explode($tab,$value);
				$value = array('first_name' => $values[0],'last_name' => $values[1]);
			}
		}

		return $value['first_name']." ".$value['last_name'];
	}
	function displayFirstName($value) {
		//ss_DumpVarDie($value);
		if ($this->name != 'us_name') {

			if(!is_array($value)) {
				$tab = chr(9);
				$values = explode($tab,$value);
				$value = array('first_name' => $values[0],'last_name' => $values[1]);
			}
		}

		return $value['first_name'];
	}
	function displayLastName($value) {
		//ss_DumpVarDie($value);
		if ($this->name != 'us_name') {

			if(!is_array($value)) {
				$tab = chr(9);
				$values = explode($tab,$value);
				$value = array('first_name' => $values[0],'last_name' => $values[1]);
			}
		}

		return $value['last_name'];
	}
	function validate() {
		if ($this->required) {
			if (strlen($this->value['first_name'])==0 or strlen($this->value['last_name'])==0) {
				return ss_HTMLEditFormat($this->displayName)." (first name and last name) is a required field.";
			}
		}
		return NULL;
	}
	function valueSQL() {
		$tab = chr(9);
		return "'".escape($this->value['first_name'].$tab.$this->value['last_name'])."'";
	}

	// This function should return the sql required to insert this field. This can
	// return a null value for those fields that do not actually enter into the db.
	function updateSQL() {
		if ($this->name == 'us_name') {
			$firstName = strlen($this->value['first_name'])?"'".escape($this->value['first_name'])."'":'null';
			$lastName = strlen($this->value['last_name'])?"'".escape($this->value['last_name'])."'":'null';
			return "us_first_name = {$firstName}, us_last_name = {$lastName}";
		} else {
			$sql = $this->valueSQL();
			return "{$this->name} = {$sql}";
		}
	}

	function insertSQLField() {
		if ($this->name == 'us_name') {
			return "us_first_name, us_last_name";
		} else {
			return $this->name;
		}


	}

	function insertSQLValue() {
		$firstName = strlen($this->value['first_name'])?"'".escape($this->value['first_name'])."'":'null';
			$lastName = strlen($this->value['last_name'])?"'".escape($this->value['last_name'])."'":'null';
		if ($this->name == 'us_name') {
			return "$firstName, $lastName";
		} else {
			return $this->valueSQL();
		}
	}

}

class RangeField extends Field {

	var $defaultValue = array('From'=>'','To'=>'');
	var $options = array('From'=>'$','To'=>' to $');
	var $type = 'int'; // text, float, int
	var $size = 13;
	var $specialInsert = false;
	var $specialInsertConfig = null;//array('tableName' =>'', 'tablePrimaryKey');
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$name  = $verify ? $this->name.'_V'   : $this->name;

		$disabled = '';
		if ($this->disabled) {
			$disabled = 'disabled';
		}
		if ($this->displayType == 'hidden') {
			$returnVal = "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$value\" $disabled>";
		} elseif ($this->displayType == 'output') {
			$returnVal = "$value";
		} else {
			$useValue = $verify ? $this->verifyValue : $this->value;
			if (!is_array($useValue)) {
				if (!strlen($useValue)) {
					$useValue = array('From'=>'','To'=>'');
				} else {
					$useValue = unserialize($useValue);
				}
			}

			$fromValue = ss_HTMLEditFormat($useValue['From']);
			$toValue = ss_HTMLEditFormat($useValue['To']);
			$returnVal = "<table cellpadding=\"0\" cellspacing=\"0\"><tr>".
				"<td>{$this->options['From']}<input type=\"text\" size=\"{$this->size}\" name=\"{$name}[From]\" VALUE=\"$fromValue\" maxlength=\"$this->maxLength\" class=\"$class\" $disabled>&nbsp;</td>".
				"<td>{$this->options['To']}<input type=\"text\" size=\"{$this->size}\" name=\"{$name}[To]\" VALUE=\"$toValue\" maxlength=\"$this->maxLength\" class=\"$class\" $disabled>".
				"</tr></table>";
		}
		return $returnVal;
	}
	function processDatabaseInputValues($primaryKey = -1) {
		if (strlen($this->value)) {
			$tab = chr(9);
			$values = explode($tab,$this->value);
			$this->value = array('From' => $values[0],'To' => $values[1]);
		} else {

			if ($this->specialInsert) {
				if ($this->specialInsertConfig === null) die("No Table Name defiend. Please fix it.");

				$name = getRow("SELECT {$this->name}From, {$this->name}To FROM {$this->specialInsertConfig['tablePrimaryKey']} WHERE {$this->specialInsertConfig['tablePrimaryKey']} = $primaryKey");
				$this->value = array('From' => $name["{$this->name}From"],'To' => $name["{$this->name}To"]);
			} else {
				$this->value = array('From' => null,'To' => null);
			}
		}
	}
	function displayValue($value) {
		if(!is_array($value)) {
			$tab = chr(9);
			$values = explode($tab,$value);
			$value = array('From' => $values[0],'To' => $values[1]);
		}

		return $this->options['From'].$value['From'].$this->options['To'].$value['To'];
	}
	function displayFrom($value) {
		if(!is_array($value)) {
			$tab = chr(9);
			$values = explode($tab,$value);
			$value = array('From' => $values[0],'To' => $values[1]);
		}

		return $this->options['From'].$value['From'];
	}
	function displayTo($value) {
		if(!is_array($value)) {
			$tab = chr(9);
			$values = explode($tab,$value);
			$value = array('From' => $values[0],'To' => $values[1]);
		}

		return $this->options['To'].$value['To'];
	}

	function validate() {
		if ($this->required) {
			if (strlen($this->value['From'])==0 or strlen($this->value['To'])==0) {
				return ss_HTMLEditFormat($this->displayName)." (from and to) is a required field.";
			}
		}
		return NULL;
	}
	function valueSQL() {
		$tab = chr(9);
		return "'".escape($this->value['From'].$tab.$this->value['To'])."'";
	}

	// This function should return the sql required to insert this field. This can
	// return a null value for those fields that do not actually enter into the db.
	function updateSQL() {
		if ($this->specialInsert) {
			if ($this->specialInsertConfig === null) die("No Table Name defiend. Please fix it.");

			$name = getRow("SELECT {$this->name}From, {$this->name}To FROM {$this->specialInsertConfig['tablePrimaryKey']} WHERE {$this->specialInsertConfig['tablePrimaryKey']} = $primaryKey");
			$fromName = strlen($this->value['From'])?"'".escape($this->value['From'])."'":'null';
			$toName = strlen($this->value['To'])?"'".escape($this->value['To'])."'":'null';
			return "{$this->name}From = {$fromName}, {$this->name}To = {$toName}";
		} else {
			$sql = $this->valueSQL();
			return "{$this->name} = {$sql}";
		}
	}

	function insertSQLField() {
		if ($this->specialInsert) {
			if ($this->specialInsertConfig === null) die("No Table Name defiend. Please fix it.");
			return "{$this->name}From, {$this->name}To";
		} else {
			return $this->name;
		}


	}

	function insertSQLValue() {
		$fromName = strlen($this->value['From'])?"'".escape($this->value['From'])."'":'null';
			$toName = strlen($this->value['To'])?"'".escape($this->value['To'])."'":'null';
		if ($this->specialInsert) {
			if ($this->specialInsertConfig === null) die("No Table Name defiend. Please fix it.");
			return "$fromName, $toName";
		} else {
			return $this->valueSQL();
		}
	}

}






// ############# Email Field ############# //

class EmailField extends TextField {

	var $size = 30;

	/*
		if (($this->value != NULL) && strpos($this->value,'.',strpos($this->value,'@')) === FALSE)
			return "$this->displayName must be a valid email address.";
		return NULL;
	*/
	function displayValue($value) {
		return "<a href=\"mailto:$value\">$value</a>";
	}
	function validate() {

		$this->value = trim( $this->value );
		if( $this->value && strlen( $this->value ) )
		{
			if( preg_match( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $this->value ) )
				return NULL;
			else
				return "'$this->value' must be a valid email address.";
		}
		else
			return NULL;
	}
}

// ############# Password Field ############# //

class PasswordField extends TextField {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$returnVal = "";
		if ($class === null) {
			$class = $this->class;
		}
		if ($this->displayType == 'hidden') {
			$returnVal = "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$value\">";
		} elseif ($this->displayType == 'output') {
			for ($i=0; $i >= strlen($value); $i++) {
				$returnVal .= '**';
			}
		} else {
			// admin user ?
			if( array_key_exists(1,$_SESSION['User']['user_groups']) )
				$returnVal = "<INPUT TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\" class=\"$class\">";
			else
				$returnVal = "<INPUT TYPE=\"PASSWORD\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\" class=\"$class\">";
		}
		return $returnVal;
	}

//briar added for aerospace
    function validate() {
        $advanced = ss_optionExists('Advanced Password Check');
        //enhanced for Photomaxing so we can stipulate the length and numbers
        //e.g. Advanced Password Check=Length:4-8,Numbers:0
        if ($advanced) {
    		$requirements = array();
    		foreach(ListToArray($advanced) as $def) {
    			$requirements[ListFirst($def,":")] = ListLast($def,":");
    		}
            $lengthReq = array_key_exists('Length', $requirements) ? $requirements['Length'] : '8-16';
            $numbersReq = array_key_exists('Numbers', $requirements) ? $requirements['Numbers'] : 2;

            //default - checks the length is >8 and <16
            $lengthReq = explode('-', $lengthReq);
            $min = $lengthReq[0];
            $max = $lengthReq[1];

            $numbersMessage = ($numbersReq > 0) ? "must consist of atleast {$numbersReq} number/s," : '';

            // default behaviour: check there is 2 letters, 2 numbers, and between 8-16 chars
            $letters = eregi("[a-z]{2,}", $this->value);
            $numbers = eregi("[0-9]{{$numbersReq},}", $this->value);
            $characters = eregi("^[[:alnum:]]{{$min},{$max}}$", $this->value);
            if (($this->value != NULL) && !$letters or !$numbers or !$characters) {
                return "The $this->displayName can contain only letters and numbers, {$numbersMessage} and must be {$min} to {$max} characters long.";
    		}
    		return NULL;
    	} else {
            parent::validate();
        }
    }
}


// ############# Hidden Text Field ############# //

class HiddenField extends Field {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $verify ? $this->verifyValue : $this->value;
		if (is_array($value))
			$value = serialize($value);

		$value = ss_HTMLEditFormat($value);
		$name  = $verify ? $this->name.'_V' : $this->name;
		return "<input type=\"hidden\" name=\"$name\" value=\"$value\">";
	}

	function validate() {
		return NULL;
	}
	function valueSQL() {
		return $this->valueSQLText();
	}
}



// ############# Credit Payment Fields  ########### //

class CreditCardNumberField extends Field {

	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if (is_numeric($this->displayType)) {
			$numChars = (int)$this->displayType;
			$value = substr($value, strlen($value)-$numChars -1);
		}
		$disabled = '';

		if ($this->disabled) {
			$disabled = 'disabled';
		}
		if ($this->displayType == 'hidden') {
			$returnVal = "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$value\" $disabled>";
		} elseif ($this->displayType == 'output') {
			$returnVal = "$value";
		} else {
			$returnVal = "<INPUT TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\" CLASS=\"$class\" $disabled>";
		}
		return $returnVal;
	}

	/*
	mastercard: Must have a prefix of 51 to 55, and must be 16 digits in length.
	Visa: Must have a prefix of 4, and must be either 13 or 16 digits in length.
	American Express: Must have a prefix of 34 or 37, and must be 15 digits in length.
	Diners Club: Must have a prefix of 300 to 305, 36, or 38, and must be 14 digits in length.
	Discover: Must have a prefix of 6011, and must be 16 digits in length.
	JCB: Must have a prefix of 3, 1800, or 2131, and must be either 15 or 16 digits in length.
	*/
	function validate() {
		$errorMsg = NULL;

		$value = preg_replace("/\s/", "", $this->value);
		//$value = trim($this->value);
		$value = preg_replace('/-/', '', $value);

		if (!is_numeric($value)) {
			$errorMsg = "$this->displayName contains non-numeric values.";
		} else {
			$cardType = strtolower($this->cardType);
			$cardType = trim($cardType);
			/*
			$creditcardTypes = array(
				'mastercard'	=>	array(
					'allowedPrefixes' 	=>	array(51,52,53,54,55),
					'allowedLengths'	=>	array(16),
				),
				'visa'	=>	array(
					'allowedPrefixes' 	=>	array(4),
					'allowedLengths'	=>	array(13,16),
				),
				'amex'	=>	array(
					'allowedPrefixes' 	=>	array(34,37),
					'allowedLengths'	=>	array(15),
				),
				'mastercard'	=>	array(
					'allowedPrefixes' 	=>	array(51,52,53,54,55),
					'allowedLengths'	=>	array(16),
				),*/


			switch($cardType) {
				case 'mastercard' :
					$twoChars = (int)substr($value, 0, 2);
					if ($twoChars <= 55 && $twoChars >= 51 && strlen($value) == 16) {

					} else {
						$errorMsg = "$this->displayName is invalid.";
					}
					break;
				case 'visa' :
					$oneChars = (int)substr($value, 0, 1);
					if ($oneChars == 4 && strlen($value) <= 16 && strlen($value) >= 13) {

					} else {
						$errorMsg = "$this->displayName is invalid.";
					}
					break;
				case 'americanexpress' :
					$twoChars = (int)substr($value, 0, 2);
					if (($twoChars == 34 || $twoChars == 37) && strlen($value) == 15) {

					} else {
						$errorMsg = "$this->displayName is invalid.";
					}
					break;
				case 'dinersclub' :
					$twoChars = (int)substr($value, 0, 2);
					$threeChars = (int)substr($value, 0, 3);
					if (($twoChars == 36 || $twoChars == 38 || ($threeChars <= 305 && $threeChars >= 300)) && strlen($value) == 14) {

					} else {
						$errorMsg = "$this->displayName is invalid.";
					}
					break;
				case 'discover' :
					$fourChars = (int)substr($value, 0, 4);
					if ($oneChars == 6011 && strlen($value) == 16) {

					} else {
						$errorMsg = "$this->displayName is invalid.";
					}
					break;
				case 'jcb' :
					$oneChars = (int)substr($value, 0, 1);
					$fourChars = (int)substr($value, 0, 4);
					if (($oneChars == 3 || $fourChars == 1800 || $fourChars == 2131) && (strlen($value) == 15 || strlen($value) == 16)) {

					} else {
						$errorMsg = "$this->displayName is invalid.";
					}
					break;
			}
			if (is_null($errorMsg)) {
				//reverse the number
				$cardNumber = strrev($value);
				$numSum = 0;

				for($i = 0; $i < strlen($cardNumber); $i++) {
				 	$currentNum = substr($cardNumber, $i, 1);
					// Double every second digit
					if($i % 2 == 1) {
				  		$currentNum *= 2;
					}
					// Add digits of 2-digit numbers together
					if($currentNum > 9){
				  		//$firstNum = $currentNum % 10;
				  		//$secondNum = ($currentNum - $firstNum) / 10;
				  		//$currentNum = $firstNum + $secondNum;
				  		$currentNum -= 9;
					}
					$numSum += $currentNum;
				}

				// If the total has no remainder it's OK
				$passCheck = ($numSum % 10 == 0);
				if(!$passCheck){
					$errorMsg = "$this->displayName is invalid.$numSum ";
				}
			}
		}
		return $errorMsg;
	}
	function valueSQL() {
		return $this->valueSQLText();
	}
}



// ############# Memo Field ############# //

class MemoField extends TextField {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$onChange = isset($this->onChange) ? $this->onChange : "";
		return "<TEXTAREA ROWS=\"{$this->rows}\" COLS=\"{$this->cols}\" NAME=\"$name\" class=\"$class\" style=\"{$this->style}\" $onChange>$value</TEXTAREA>";
	}
	function displayValue($value) {
		return ss_HTMLEditFormatWithBreaks($value);
	}

}
?>
