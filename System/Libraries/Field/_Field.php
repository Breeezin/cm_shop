<?php

// ############# Field ############# //

class Field {

	// These are used when displaying the field in
	// the administration area
	var $name 			= 'unknown';
	var $displayName 	= 'unknown';
	var $displayType 	= 'input';	// Allowed values - input, output, hidden
	var $note 			= '';

	var $disabled		= FALSE;
	var $trim			= FALSE;

	// These are used for validation
	var $required 		= FALSE;
	var $unique 		= FALSE;
	var $uniqueToParent = FALSE;
	var $verify 		= FALSE;

	// Form display settings
	var $size 			= 30;
	var $maxLength 		= 255;
	var $rows 			= 6;
	var $cols 			= 30;

	// Style settings (most fields wont' pay attention to this... this should be setup
	// properly in all fields at some stage..... )
	var $class = null;
	var $style = null;

	// Used in select fields
	var $linkQueryAction 				= NULL;
	var $linkQueryValueField 				= NULL;
	var $linkQueryDisplayField 				= NULL;
	var $linkQueryDisplayFieldDelimiters 	= NULL;
	var $linkQueryParameters 				= NULL;
	var $linkTableName 						= NULL;

	// Need to store the value of the field also
	var $value 								= NULL;
	var $verifyValue 						= NULL;

	// Allow a default value
	var $defaultValue 						= NULL;

	function __construct($settings) {
		foreach($settings as $property => $value) $this->{$property} = $value;
	}

	// This should return the edit field displayed in the form
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		return 'I need a display method';
	}

	// This should return the field value
	function displayValue($value) {
		return ss_HTMLEditFormat($value);
	}

	// This should return NULL if no errors or a suitable error
	// message if the field value is not 'valid'
	// e.g (Bad date, invalid email address)
	function validate() {
		return NULL;
	}

	// This should return a string representation of the field ready for insertion
	// into an SQL query
	function valueSQL() {
		if ($this->value === NULL) return 'NULL';
		if (strlen($this->value) == 0) return 'NULL';

		return $this->value;
	}

	// This function returns the field value with quote's escaped for
	// inserting in an SQL query. Can just do return $this->valueSQLText()
	// in your valueSQL() function if it is a text string type
	function valueSQLText() {
		$temp = $this->value;
		if ($this->value === NULL) return 'NULL';
		if (is_array($this->value)) {
			$temp = ArrayToList($this->value);
		} else {
			if (strlen($this->value) == 0) return 'NULL';
		}

		if ($this->trim) {
			$temp = rtrim(ltrim($temp));
		}

		$temp = escape($temp);

		return "'$temp'";
	}

	// This function should return true if the field is not set
	// This will need to be overridden for fields that can accept
	// a null value.. eg. Checkbox will be NULL if not set
	function notSet() {
		return ($this->value == NULL);
	}

	function useDefaultValue() {
		$this->setValues($this->defaultValue,$this->defaultValue,'FORM');
	}

	// This function should modify the values supplied by the database
	function processDatabaseInputValues($primaryKey = -1) {

	}

	// This function should modify the values supplied by the form.
	// Remember to process the verifyValue as well
	function processFormInputValues( ) {

	}

	// This function should delete any additional data associated with this field
	// e.g. Files for file fields, or link records for multi select fields
	function delete() {

	}

	// This function should handle any special fields such as multi select fields
	// which need to insert extra records etc.
	function specialInsert() {

	}

	function specialUpdate() {

	}

	// This function should return the sql required to insert this field. This can
	// return a null value for those fields that do not actually enter into the db.
	function updateSQL() {
		$sql = $this->valueSQL();
		return "{$this->name} = {$sql}";
	}

	function insertSQLField() {
		return $this->name;
	}

	function insertSQLValue() {
		return $this->valueSQL();
	}

	function setValues($value = NULL, $verifyValue = NULL, $whereFrom = NULL, $dbPK = NULL) {
		$this->value = $value;
		$this->verifyValue = $verifyValue;
		//ss_DumpVar($value, $whereFrom);
		if ($whereFrom == 'DB') {
			$this->processDatabaseInputValues($dbPK);
		} elseif ($whereFrom == 'FORM') {
			$this->processFormInputValues($dbPK);

		}
	}

	function fullValidate($dbTableName = NULL, $dbPrimaryKeyField = NULL, $dbPrimaryKeyValue = NULL, $dbDeleteFlag = NULL, $dbAssetLinkField = NULL, $dbAssetLinkValue = NULL, $dbParentLinkField = NULL, $dbParentLinkValue = NULL ) {

		global $sql;
		$errors = array();

		// Ask the field to verify itself for it's specific type
		// e.g. number field must contain a number
		$result = $this->validate();
		if ($result != NULL) {
			if (!array_key_exists($this->name,$errors)) $errors[$this->name] = array();
			array_push($errors[$this->name],$result);
		}

		// Check if the field is present if it must be
		//jp 20051124 if ($this->required && $this->notSet()) {
		

		if ($this->required && ($this->notSet() || $this->value=="_blank") ) {
			//if ($this->displayName == "Email")
			//ss_DumpVarDie($this);
			if (!array_key_exists($this->name,$errors)) $errors[$this->name] = array();
			array_push($errors[$this->name],"$this->displayName is a required field.");
		}

		// Check if the field is verified correctly if it must be
		if ($this->verify && ($this->value != $this->verifyValue)) {
			if (!array_key_exists($this->name.'_V',$errors)) $errors[$this->name.'_V'] = array();
			array_push($errors[$this->name.'_V'],"Please verify $this->displayName by entering the same value into the Verify $this->displayName field.");
		}

		// Check if the field is unique
		if ($this->unique) {
			// build current record exclude SQL
			$currentRecordExcludeSQL = '';
			if (($dbPrimaryKeyValue != NULL) && ($dbPrimaryKeyField != NULL)) {
				$currentRecordExcludeSQL = "AND NOT ($dbPrimaryKeyField = '".escape($dbPrimaryKeyValue)."')";
			}
			// don't care if deleted records use the same value
			$deleteFlagSQL ='';
			if ($dbDeleteFlag != NULL) {
				$deleteFlagSQL = "AND $dbDeleteFlag IS NULL";
			}

			// search from the specific assetlink
			$assetLinkSQL = '';
			if ($dbAssetLinkField != null && $dbAssetLinkValue != null) {
				$assetLinkSQL = "AND $dbAssetLinkField = $dbAssetLinkValue";
			}

			$result = $sql->query("
				SELECT * FROM $dbTableName
				WHERE {$this->name} = ".$this->valueSQL()."
					$currentRecordExcludeSQL
					$deleteFlagSQL
					$assetLinkSQL
			");

			if ($result->numRows() > 0) {
				if (!array_key_exists($this->name,$errors)) $errors[$this->name] = array();
				array_push($errors[$this->name],"The value entered in the {$this->displayName} field is already in use by another record.");
			}
		}

		// Check if the field is unique
		if ($this->uniqueToParent) {
			// build current record exclude SQL
			$currentRecordExcludeSQL = '';
			if (($dbPrimaryKeyValue != NULL) && ($dbPrimaryKeyField != NULL)) {
				$currentRecordExcludeSQL = "AND NOT ($dbPrimaryKeyField = '".escape($dbPrimaryKeyValue)."')";
			}
			// don't care if deleted records use the same value
			$deleteFlagSQL ='';
			if ($dbDeleteFlag != NULL) {
				$deleteFlagSQL = "AND $dbDeleteFlag IS NULL";
			}

			// search from the specific assetlink
			$assetLinkSQL = '';
			if ($dbAssetLinkField != null && $dbAssetLinkValue != null) {
				$assetLinkSQL = "AND $dbAssetLinkField = $dbAssetLinkValue";
			}

			$parentLinkSQL = '';
			if ($dbParentLinkField != null && $dbParentLinkValue != null) {
				$parentLinkSQL = "AND $dbParentLinkField = $dbParentLinkValue";
			}

			$result = $sql->query("
				SELECT * FROM $dbTableName
				WHERE {$this->name} = ".$this->valueSQL()."
					$currentRecordExcludeSQL
					$deleteFlagSQL
					$assetLinkSQL
					$parentLinkSQL
			");

			if ($result->numRows() > 0) {
				if (!array_key_exists($this->name,$errors)) $errors[$this->name] = array();
				array_push($errors[$this->name],"The value entered in the {$this->displayName} field is already in use by another record.");
			}
		}

		return $errors;

	}
}


// ############# MultiField Field ############# //

class MultiField extends Field {
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		return "Inherited classes should be called instead of this class";
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
			array_push($this->value,$row[$this->linkTableTheirKey]);
		}
		$this->verifyValue = $this->value;

	}

	function delete() {
		// Delete any linked items
		$result = query("
			DELETE FROM $this->linkTableName WHERE $this->linkTableOurKey = {$this->fieldSet->primaryKey}
		");
	}

	function specialInsert() {
		// Delete any existing linked items
		$this->delete();
		// Insert the new linked items
		if (is_array($this->value)) {
			foreach ($this->value as $theirKey) {
				$result = query("
					INSERT INTO $this->linkTableName ($this->linkTableOurKey, {$this->linkTableTheirKey})
					VALUES ('{$this->fieldSet->primaryKey}', '$theirKey')
				");
			}
		}

	}

	function specialUpdate() {
		$this->specialInsert();
	}

	function updateSQL() {
		return null;
	}
	function insertSQLField() {
		return null;
	}
	function insertSQLValue() {
		return null;
	}


}


require_once('TextFields.php');
require_once('NumericFields.php');
require_once('SelectFields.php');
require_once('RadioFields.php');
require_once('DateTimeFields.php');
require_once('CheckBoxFields.php');
require_once('FileFields.php');
require_once('CMFields.php');
require_once('HtmlMemoField.php');
if (ss_optionExists('Admin FCK Editor')) {
	require_once('HtmlMemoFieldFCK.php');
} else {
	require_once('HtmlMemoField2.php');
}
requireOnceClass('CustomField');

require_once('ImageFields.php');

require_once('FieldSetBuilderField.php');

require_once('ProductExtendedOptionsField.php');
require_once('ProductExtendedOptionField.php');
require_once('AttributesField.php');
require_once('DataCollectionField.php');
require_once('ParentChildrenField.php');

// ############# Serialized Data Field ############# //
class SerializedDataField extends TextField {

	function getSerializeJS($name) {
		$form = $this->fieldSet->formName;
return <<< EOD
<script language="Javascript">

	// Source: eskaly - http://php.cd/cowiki/Eskaly/Me
	function {$name}_serialize (variable)
	{
	    switch (typeof variable)
	    {
	        case 'number':
	            if (Math.round(variable) == variable)
	                return 'i:'+variable+';';
	            else
	                return 'd:'+variable+';';
	        case 'boolean':
	            if (variable == true)
	                return 'b:1;';
	            else
	                return 'b:0;';
	        case 'string':
				var whitespace = new String('\\r\\n');
				var s = new String(variable);
				var newStr = new String();
				for(i=0;i<s.length;i++){
				     if (s.charAt(i) == whitespace) {
				          newStr += "<BR>";
				     }else{
				          newStr += s.charAt(i)
				     }
				}

	        	return 's:'+newStr.length+':"'+newStr+'";';

	        case 'object':
	        	propCount = 0;
	        	for(var prop in variable) {
	        		propCount++;
	        	}
	            r = 'a:'+propCount+':{';
	            for(var prop in variable)
	            {
	                r+= {$name}_serialize(prop)+{$name}_serialize(variable[prop]);
	            }
	            r += '}';
	            return r;
	            break;
	        default:
	            return 'unkown type: '+typeof variable;
	    }
	}

	// Write the whole field set array out into a
	// hidden field on the form
	function {$name}_DumpFieldSet() {
		formDef = {$name}_serialize({$name}_getData(document.forms.{$form}));
		//alert(formDef);
		document.forms.{$form}.{$name}.value = formDef;
	}

	extraProcesses[extraProcesses.length] = {$name}_DumpFieldSet;

</script>
	<input type="hidden" name="{$name}" value="">
EOD;

	}


	// This is just an example.. not really useful
	//function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		$value = $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;

		// Deserialize the field's value
		$data = array();
		if (strlen($value)) {
			$data = unserialize($value);
		}

		// Param any keys we need
		ss_paramKey($data,'importantInfo');

		// Draw the fields and include a {$name}_getData(form) function
		// that will return an array with all the data we wanna save
		$displayHTML = <<< EOD
		<input type="text" name="{$name}_importantInfo" value="{$data['importantInfo']}">
		<script language="javascript">
		function {$name}_getData(form) {
			var value = form.{$name}_importantInfo.value;
			return {importantInfo: value};
		}
		</script>
EOD;
		return $displayHTML.$this->getSerializeJS($name);
	}


	function displayValue($value) {

	}


}

require_once('MultipleValuesFields.php');





/*
// ############# Parent Field ############# //
class ParentField extends TextField {

	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		print ("<br>\nDisplay Start...<br>\n");
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;

		$parameters = array('NoHusk' => TRUE);
		if ($this->linkQueryParameters != NULL)  {
			$parameters = array_merge($parameters,$this->linkQueryParameters);
		}


		// Get the list of fields
		// Basically find and list all fields in the table.
		// Fields grouped into a hierachial structure


		// Need to make a new 'query'
		$newResult = new FakeQuery(array($this->linkQueryValueField,$this->linkQueryDisplayField));

		// Should work ^_^
		$this->Build($newResult);

		print_r ($newResult);


		print ("<br>\n...Display End...<br>\n");
	}
	*/

	/*
	function Build(&$fakeQuery, $parent = NULL) {
		print ("Build Start...<br>\n");
		$parent = $parent == NULL ? $parent = 'IS NULL' : $parent = '= ' . $parent;
		// Query for all records matching search
		$mySQL = "SELECT {$this->linkQueryValueField} , {$this->linkQueryDisplayField} FROM " . $this->linkTableName . "
				  WHERE " . $this->name . " " . $parent . "
				  ORDER BY " . $this->linkQueryValueField . "
				 ";


		print ("<br>Query Start ... ##########################<br>");
		print ($mySQL);
		print ("<br>##########################... Query End<br>");


		$result = query($mySQL);

		print ("<br>\nInitial Query Run...<br>\n");
		print ($result->numRows() . " rows returned<br>\n");



		while ($row = $result->fetchRow()) {
			print ("<br>>Row Start ... ##########################<br>");
			ss_DumpVar("Row",$row);
			print ("<br>##########################... Row End<br>");
			// Add first record to fakequery
			// Get each element

			$input = array();
			foreach ($row as $val) {
				array_push($input,$val);
			}

			print ("<br>\nRow input...<br>\n");
			ss_DumpVar("Input",$input);

			$fakeQuery->addRow($input);

			print ("<br>\nFake Query...<br>\n");
			ss_DumpVar('FakeQuery',$fakeQuery);

			// Now need to find if this row has any children

			$this->Build($fakeQuery,$row["{$this->linkQueryValueField}"]);

			print ("Nothing to see here, move along ^_^<br>\n");
		}
		print ("...Build End<br>\n");
		return true;
	}
			*/


/*
			// Get children



		$newResult->addRow();


	var $linkQueryValueField 				= NULL;
	var $linkQueryDisplayField 				= NULL;
	var $linkQueryDisplayFieldDelimiters 	= NULL;
	var $linkQueryParameters 				= NULL;
	var $linkTableName 						= NULL;



		$result = new Request($this->linkQueryAction,$parameters);
		$result = $result->value;

		// Draw the form item using the results of the query
		$multiple = $multi ? "MULTIPLE SIZE=\"$this->rows\" NAME=\"{$name}[]\"" : "NAME=\"{$name}\"";
		$displayHTML = "<SELECT class=\"$class\" $multiple>";
		if (!$this->required && !$multi) {
			$selected = $value == NULL ? 'SELECTED' : '';
			$displayHTML .= "<OPTION $selected VALUE=\"NULL\"> </OPTION>";
		}
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
			if ($multi) {
				$selected = is_array($value) && in_array($row[$this->linkQueryValueField],$value) ? 'SELECTED' : '';
			} else {
				$selected = $value == $row[$this->linkQueryValueField] ? 'SELECTED' : '';
			}
			$displayHTML .= "<OPTION $selected VALUE=\"{$row[$this->linkQueryValueField]}\">$escaped</OPTION>";
		}
		$displayHTML .= '</SELECT>';

		return $displayHTML;
	}
*/

/*
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
*/

?>
