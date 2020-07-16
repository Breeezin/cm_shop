<?php

	class FieldSetBuilderField extends TextField {
		var $prefixedFields = array();
		var $defaultValue = array();
		var $fieldSetName = 'Form Definition';
		// e.g. $extraOption = array( 'Name' => 'AppearInList', 'Description' => 'Field', 'Options' => array('Appear In List'=>'yes','Disappear In List'=>'no'));
		var $extraOption = array(); 			
		var $typeMappings = array(); 			

		function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
			if ($verify) die("You cannot verify FieldSetBuilderFields");
			$name  = $this->name;

			// Grab some UIDs for the fields to use
			$result = new Request("UID.Get",array('Count' => 128));
			
			// Take the UIDs and create some javascript to load them up.			
			$uidJS = '';
			foreach ($result->value as $uid) {
				$uidJS .= ss_comma($uidJS)."'".$uid."'";
			}

			// Get the form name;
			$form = $this->fieldSet->formName;
			
			$valueHTMLEditFormatted = ss_HTMLEditFormat($this->value);
			//ss_DumpVarDie(ss_JSStringFormat($this->value));

			if ($this->value !== null) {
				$formFields = unserialize($this->value);
			} else {
				$formFields = $this->defaultValue;	
			}
			
			// FOR TESTING ONLY!!!
			//$formFields = $this->defaultValue;	
			
			// If we haven't got any fields.. then make one new empty field
			if (count($formFields) == 0) {
				$preCounter = 0;
				foreach ($this->prefixedFields as $fieldname => $type) {
					$formFields["$preCounter"] = array(
						'name'		=>	$fieldname,
						'type'		=>	$type,						
						'defaultValue'	=>	'',						
						'size'		=>	'',
						'options'	=>	array(),
						'uuid'		=>	$fieldname,
						'prefixed'	=>	1,
					);
					if (count($this->extraOption)) {
						foreach ($this->extraOption as $extraOption) {
							$formFields["$preCounter"][$extraOption['Name']] = '';
							$preCounter++;
						}
					}	
					if ($fieldname == "Password") {			
						$formFields["$preCounter"] = array_merge($formFields["$preCounter"], array('verify' => true, 'required'	=>	0,));			
					}
					if ($fieldname == "Email") {			
						$formFields["$preCounter"] = array_merge($formFields["$preCounter"], array('unique' => true, 'required'	=> 1,));			
					}			
					if ($fieldname == "Name") {			
						$formFields["$preCounter"] = array_merge($formFields["$preCounter"], array('unique' => true, 'required'	=> 0,));			
					}			
					$preCounter++;			
				}
			}
			
			// Make some javascript to define all the fields
			$defineFieldsJS = '';
			$fieldOptionsHTML = '';
			$counter = 0;			
			$firstFieldStatus = '';
			$firstField = null;
			ksort($formFields,SORT_NUMERIC); 
			//ss_DumpVarDie($this->extraOption);					
			foreach($formFields as $index => $formField) {				
				ss_paramKey($formField,'name','Unknown');
				ss_paramKey($formField,'type','Unknown');
				ss_paramKey($formField,'required',0);
				ss_paramKey($formField,'size','');
				ss_paramKey($formField,'options',array());
				ss_paramKey($formField,'defaultValue','');
				ss_paramKey($formField,'uuid','');
				ss_paramKey($formField,'prefixed',0);
				
				if (count($this->extraOption)) {
					foreach ($this->extraOption as $extraOption) {
						ss_paramKey($formField,$extraOption['Name'], '');				
					}
				}	
				
				$defineFieldsJS .= "{$name}_FieldSet[$index] = new {$name}_Field('".ss_JSStringFormat($formField['name'])."','".ss_JSStringFormat($formField['type'])."',{$formField['required']},'".ss_JSStringFormat($formField['uuid'])."', {$formField['prefixed']});\n";
				$defineFieldsJS .= "{$name}_FieldSet[$index].size = '".ss_JSStringFormat($formField['size'])."';\n";
				$defineFieldsJS .= "{$name}_FieldSet[$index].defaultValue = '".ss_JSStringFormat($formField['defaultValue'])."';\n";
				//$tempOptions = //Replace(Replace(Field.Options,"==","=","ALL"),"=","#Chr(13)##Chr(10)#","ALL")>
				
				if (count($formField['options'])) {				
					$defineFieldsJS .= "{$name}_FieldSet[$index].options = new Array();\n";
					if (is_array($formField['options'])) {
						for ($i = 0; $i < count($formField['options']);$i++) {
							if (is_array($formField['options'][$i]) and count($formField['options'][$i])) {
								$defineFieldsJS .= "{$name}_FieldSet[$index].options[{$i}] = new {$name}_Option('{$formField['options'][$i]['name']}','{$formField['options'][$i]['name']}');\n";
							}
						}
					}
				} else {				
					$defineFieldsJS .= "{$name}_FieldSet[$index].options = new Array();\n";				
				}
				
				if (count($this->extraOption)) {
					foreach($this->extraOption as $extraOption) {
						$defineFieldsJS .= "{$name}_FieldSet[$index].".$extraOption['Name']." = '".ss_JSStringFormat($formField[$extraOption['Name']])."';\n";										
					}
				}	
				
				$fieldOptionsHTML .= "<option value=\"$counter\">{$formField['name']}</option>";
				
				if ($counter == 0) {
					$firstField = $formField;
					foreach($firstField as $key => $value) {						
						if (!is_array($value)) {
							$firstField[$key] = ss_HTMLEditFormat($value);
						}
					}
					if($firstField['prefixed'] == 1) {
						$firstFieldStatus = 'disabled';
					}
				}
				$counter++;
			}
			
			// 'required' options html
			$requiredOptionsHTML = '<option value="0">No</option><option value="1">Yes</option>';
			$fieldExtras = array();
			//$fieldExtraOptionsHTMLDesc = '';
			//$fieldExtraOptionsHTML = '';
			$jsExtraDefineFieldClass = '';
			$jsExtraFieldMethod = '';			
			$jsExtraFieldDeleteMethod = '';
			$jsExtraFieldMethodDefine = '';
			$jsExtraFieldMethod2 = '';
			
			if (count($this->extraOption)) {		
				$temp = 0;	
				foreach($this->extraOption as $extraOption) {
					$fieldExtraOptionsHTMLDesc = '';
					$fieldExtraOptionsHTML = '';
			
					$jsExtraDefineFieldClass .= "this.".$extraOption['Name']." = '';\n";
					
					
					$fieldExtraOptionsHTML = "<SELECT NAME=\"{$name}_FieldExtra{$temp}\" ONCHANGE=\"{$name}_FieldExtra{$temp}Store();\">"; 
					foreach ($extraOption['Options'] as $desc => $option) {
						$fieldExtraOptionsHTML .= "<option value=\"$option\">$desc</option>";
					}
					$fieldExtraOptionsHTML .= "</select>";
					
					$fieldExtras[$temp] = array("Description" => $extraOption['Description'], "Options" => $fieldExtraOptionsHTML);
					
					$jsExtraFieldMethod .= "{$name}_FieldExtra{$temp}Store();";
					$jsExtraFieldMethodDefine .= "
						// Store the field's extra status into the array
						function {$name}_FieldExtra{$temp}Store() {
							var fieldExtra = document.forms.{$form}.{$name}_FieldExtra{$temp}.options;
							if ({$name}_CurrentSelectedField >= 0) {
								var selectedItem = fieldExtra.selectedIndex;
								{$name}_FieldSet[{$name}_CurrentSelectedField].".$extraOption['Name']." = fieldExtra[selectedItem].value;		
							}
						}
					";
					
					$jsExtraFieldDeleteMethod .= "theForm.{$name}_FieldExtra{$temp}.options.selectedIndex = 0;";								
					$jsExtraFieldMethod2 .= "
						var fieldExtra = theForm.{$name}_FieldExtra{$temp}.options;
						for (var i=0; i < fieldExtra.length; i++) {
							if (fieldExtra[i].value == {$name}_FieldSet[{$name}_CurrentSelectedField].".$extraOption['Name'].") {
								theForm.{$name}_FieldExtra{$temp}.options.selectedIndex = i;
							}
						}
					";
					$temp++;			
				}
			}	
			//ss_DumpVarDie($this->extraOption);					
			$fieldTypeOptionsHTML = '';
			if (count($this->typeMappings)) {
				$typeMappings = $this->typeMappings;
			} else {
				$typeMappings = ss_getFieldSetTypes();
			}
			
			foreach($typeMappings as $class => $description) {
				$fieldTypeOptionsHTML .= "<option value=\"$class\">$description</option>";
			}
/*
			<OPTION #Iif(LCase(FirstField.Type) EQ "text",DE('SELECTED'),DE(''))# VALUE="text">Text</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "checkbox",DE('SELECTED'),DE(''))# VALUE="checkbox">Check Box</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "comment",DE('SELECTED'),DE(''))# VALUE="comment">Comment</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "country",DE('SELECTED'),DE(''))# VALUE="country">Country</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "date",DE('SELECTED'),DE(''))# VALUE="date">Date</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "email",DE('SELECTED'),DE(''))# VALUE="email">Email Address</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "longtext",DE('SELECTED'),DE(''))# VALUE="longtext">Long Text</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "mailinglist",DE('SELECTED'),DE(''))# VALUE="mailinglist">Mailing List</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "name",DE('SELECTED'),DE(''))# VALUE="name">Name</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "number",DE('SELECTED'),DE(''))# VALUE="number">Number</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "checkboxset",DE('SELECTED'),DE(''))# VALUE="checkboxset">Select Many</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "radiobuttonset",DE('SELECTED'),DE(''))# VALUE="radiobuttonset">Select One</OPTION>
			<OPTION #Iif(LCase(FirstField.Type) EQ "select",DE('SELECTED'),DE(''))# VALUE="select">Select One (Drop down)</OPTION>
*/			
			
$output = <<< EOD

<SCRIPT LANGUAGE="Javascript">
<!--
	{$name}_FieldSet = new Array();
	{$name}_CurrentSelectedField = -1;
	{$name}_CurrentSelectedOpionField = -1;	
	{$name}_CurrentSelectedIndex = -1;
	{$name}_CurrentSelectedOpionIndex = -1;

	NextUID = 1;
	uids = new Array($uidJS);	
	
	function {$name}_HideAndDisplayOptionSet() {
		var theSelect = document.forms.{$form}.{$name}_FieldType;
		
		selectedFieldType = theSelect.options[theSelect.selectedIndex].value
	
		if (selectedFieldType == "MultiSelectFromArrayField" || 			
			selectedFieldType == "SelectFromArrayField" ||
			selectedFieldType == "ProductOptionsField" ||
			selectedFieldType == "MonthlyScheduleField") {
			document.getElementById('{$name}_OptionFieldSet').style.display = '';
			document.getElementById('{$name}_OptionFieldSetDesc').style.display = '';
		} else {		
			document.getElementById('{$name}_OptionFieldSet').style.display = 'none';
			document.getElementById('{$name}_OptionFieldSetDesc').style.display = 'none';
		}
	}
	
	function {$name}_Field(name, type, required, uuid, prefixed) {
		this.name = name;
		this.type = type;
		this.required = required;
		this.prefixed = prefixed;
		this.defaultValue = '';
		this.size = '';
		this.options = new Array();
		$jsExtraDefineFieldClass
		
		if (uuid.length == 0) {
			this.uuid = uids[NextUID];
			NextUID = NextUID+1;
			if (NextUID > uids.length) alert('Please save this item now before adding any new fields.');
		} else {
			this.uuid = uuid;
		}
	}
	function {$name}_Option(name, uuid) {
		
		this.name = name;		
		if (uuid.length == 0) {
			this.uuid = uids[NextUID];
			NextUID = NextUID+1;
			if (NextUID > uids.length) alert('Please save this item now before adding any new option.');
		} else {
			this.uuid = uuid;
		}		
	}
	

	function {$name}_Delete() {
		var theForm = document.forms.{$form};
		var fields = theForm.{$name}_Fields.options;
		if (fields.length > 0) {
			if ({$name}_FieldSet[{$name}_CurrentSelectedField].prefixed == 1) {
				alert('You cannot delete the pre-defined fields.');
			} else {
				fields[{$name}_CurrentSelectedIndex] = null;
				{$name}_CurrentSelectedIndex = -1;
		
				if (fields.length > 0) {
					fields.selectedIndex = 0;
					{$name}_FieldLoad();	
				} else {				
					theForm.{$name}_FieldName.value = '';
					theForm.{$name}_FieldSize.value = '';
					var fieldoptions = theForm.{$name}_OptionFields.options;		
					temp = fieldoptions.length - 1;									
					
					for (var i=temp; i >= 0; i--) {			
						theForm.{$name}_OptionFields.options[i] = null;			
					}		
							
					for (var i=0; i < {$name}_FieldSet[{$name}_CurrentSelectedField].options.length; i++) {			
						theForm.{$name}_OptionFields.options[i] = new Option({$name}_FieldSet[{$name}_CurrentSelectedField].options[i].name, {$name}_FieldSet[{$name}_CurrentSelectedField].options[i].uuid);			
					}
					theForm.{$name}_OptionFieldValue.value = '';
					
					theForm.{$name}_FieldDefaultValue.value = '';
					theForm.{$name}_FieldRequired.options.selectedIndex = 0;
					$jsExtraFieldDeleteMethod
					theForm.{$name}_FieldType.options.selectedIndex = 0;
					{$name}_HideAndDisplayOptionSet();
					
				}
			}
		}
	}
	function {$name}_OptionFieldLoad() {
	
		// Make sure everything is up to date before we wipe out those fields	
		
		{$name}_OptionFieldValueStore();		
			
		
		theForm = document.forms.{$form};		
		
		var fields = theForm.{$name}_OptionFields;
		var selectedItem = fields.selectedIndex;
		{$name}_CurrentSelectedOpionIndex = selectedItem;
		//{$name}_CurrentSelectedOpionField = fields.options[selectedItem].value;
		
		theForm.{$name}_OptionFieldValue.value = fields.options[selectedItem].text;
										
		return true;

	}
	
	
	// Store the defaultvalue value of the field into the array
	function {$name}_OptionFieldValueStore() {
		theForm = document.forms.{$form};		
		var value = theForm.{$name}_OptionFieldValue.value;
						
		if ({$name}_CurrentSelectedOpionIndex >= 0) {
			
			{$name}_FieldSet[{$name}_CurrentSelectedField].options[{$name}_CurrentSelectedOpionIndex].name = value;
			theForm.{$name}_OptionFields.options[{$name}_CurrentSelectedOpionIndex].text  = value;
		}
		
	}
	
	
	function {$name}_OptionDelete() {
		var theForm = document.forms.{$form};
		var fields = theForm.{$name}_OptionFields.options;
		if (fields.length > 0) {					
			fields[fields.selectedIndex] = null;	
			{$name}_FieldOptionsStore();							
			if (fields.length > 0) {
				fields.selectedIndex = 0;
				{$name}_OptionFieldLoad();	
			} else {								
				for (var i=temp; i >= 0; i--) {			
					theForm.{$name}_OptionFields.options[i] = null;			
				}														
				theForm.{$name}_OptionFieldValue.value = '';														
			}			
		}
	}
	
	function {$name}_OptionNew() {
		var fields = document.forms.{$form}.{$name}_OptionFields.options;
		newSelected = fields.length;		
		uuid = uids[NextUID];
		NextUID = NextUID+1;
		if (NextUID > uids.length) {
			alert('Please save this Asset now before adding any new fields.');			
		}
				
		
		newindex = {$name}_FieldSet[{$name}_CurrentSelectedField].options.length;
		
		fields[fields.length] = new Option('Untitled',uuid,false);
		fields.selectedIndex = fields.length;
	
		{$name}_FieldSet[{$name}_CurrentSelectedField].options[newindex] = new {$name}_Option('Untitled',uuid);		
		
		{$name}_OptionFieldLoad();		
	}
	
	// Move a form field up or down in the list 
	function {$name}_OptionMove(how) {
		var fields = document.forms.{$form}.{$name}_OptionFields.options;
		var selectedItem = fields.selectedIndex;
		
		if (((how == -1) && (selectedItem > 0))
			|| ((how == 1) && (selectedItem < fields.length-1))) {
			// Swap the display first
			oldText = fields[selectedItem+how].text;
			fields[selectedItem+how].text = fields[selectedItem].text; 
			fields[selectedItem].text = oldText; 
			
			// Update the selection bar
			document.forms.{$form}.{$name}_OptionFields.options.selectedIndex = selectedItem+how;

			// Swap the values
			oldValue = fields[selectedItem+how].value;
			fields[selectedItem+how].value = fields[selectedItem].value; 
			fields[selectedItem].value = oldValue; 
			
			{$name}_FieldSet[{$name}_CurrentSelectedField].options = new Array();		
			for(var i=0; i < fields.length; i++) {
				alert(fields[i].value+ " " + fields[i].text);
				{$name}_FieldSet[{$name}_CurrentSelectedField].options[i] = new {$name}_Option(fields[i].text,fields[i].value);
			}
		}			
		{$name}_CurrentSelectedOpionIndex = fields.selectedIndex;
	}
	
	
	function {$name}_New() {
		var fields = document.forms.{$form}.{$name}_Fields.options;
		newSelected = fields.length;
		fields[fields.length] = new Option('Untitled',{$name}_FieldSet.length,false);
		newType = '';
		var fieldTypes = document.forms.{$form}.{$name}_FieldType.options;					
		if (fieldTypes.length >= 0) {
			newType = fieldTypes[0].value;										
		}
		
		{$name}_FieldSet[{$name}_FieldSet.length] = new {$name}_Field('Untitled',newType,0,'',0);
		fields.selectedIndex = newSelected;		
		document.forms.{$form}.{$name}_FieldRequired.disabled = false
		document.forms.{$form}.{$name}_FieldType.disabled = false
		
		{$name}_FieldLoad();
	}
	
	// Move a form field up or down in the list 
	function {$name}_Move(how) {
		var fields = document.forms.{$form}.{$name}_Fields.options;
		var selectedItem = fields.selectedIndex;
		
		if (((how == -1) && (selectedItem > 0))
			|| ((how == 1) && (selectedItem < fields.length-1))) {
			// Swap the display first
			oldText = fields[selectedItem+how].text;
			fields[selectedItem+how].text = fields[selectedItem].text; 
			fields[selectedItem].text = oldText; 
			
			// Update the selection bar
			document.forms.{$form}.{$name}_Fields.options.selectedIndex = selectedItem+how;

			// Swap the values
			oldValue = fields[selectedItem+how].value;
			fields[selectedItem+how].value = fields[selectedItem].value; 
			fields[selectedItem].value = oldValue; 
		}
		{$name}_CurrentSelectedIndex = fields.selectedIndex;		
	}
	
	function replace(string,text,by) {
	// Replaces 'text' with 'by' in 'string'
	    var strLength = string.length, txtLength = text.length;
	    if ((strLength == 0) || (txtLength == 0)) return string;
	
	    var i = string.indexOf(text);
	    if ((!i) && (text != string.substring(0,txtLength))) return string;
	    if (i == -1) return string;
	
	    var newstr = string.substring(0,i) + by;
	
	    if (i+txtLength < strLength)
	        newstr += replace(string.substring(i+txtLength,strLength),text,by);
	
	    return newstr;
	}

	{$defineFieldsJS}
	
	// Update the field settings area with the 
	// attributes from the currently selected field
	function {$name}_FieldLoad() {
		
		// Make sure everything is up to date before we wipe out those fields
		{$name}_FieldNameStore();
		{$name}_FieldDefaultValueStore();
		{$name}_FieldSizeStore();		
		{$name}_FieldTypeStore();
		{$name}_FieldRequiredStore();
		$jsExtraFieldMethod
		
		theForm = document.forms.{$form};		
		var fields = theForm.{$name}_Fields.options;
		var selectedItem = fields.selectedIndex;

		{$name}_CurrentSelectedIndex = fields.selectedIndex;
		{$name}_CurrentSelectedField = fields[selectedItem].value;		
		
		theForm.{$name}_FieldName.value = {$name}_FieldSet[{$name}_CurrentSelectedField].name;
		theForm.{$name}_FieldSize.value = {$name}_FieldSet[{$name}_CurrentSelectedField].size;		
		theForm.{$name}_FieldDefaultValue.value = {$name}_FieldSet[{$name}_CurrentSelectedField].defaultValue;
		
		
		
		var fieldTypes = theForm.{$name}_FieldType.options;
		for (var i=0; i < fieldTypes.length; i++) {
			if (fieldTypes[i].value.toUpperCase() == {$name}_FieldSet[{$name}_CurrentSelectedField].type.toUpperCase()) {
				theForm.{$name}_FieldType.options.selectedIndex = i;
			}
		}				

		var fieldRequireds = theForm.{$name}_FieldRequired.options;
		for (var i=0; i < fieldRequireds.length; i++) {
			if (fieldRequireds[i].value == {$name}_FieldSet[{$name}_CurrentSelectedField].required) {
				theForm.{$name}_FieldRequired.options.selectedIndex = i;
			}
		}		
		$jsExtraFieldMethod2
		// if the selected field is prefixed, then dispable type and required 						
		if ({$name}_FieldSet[{$name}_CurrentSelectedField].prefixed == '1') {
			theForm.{$name}_FieldType.disabled = true;
			theForm.{$name}_FieldRequired.disabled = true;								
		} else {
			theForm.{$name}_FieldType.disabled = false;
			theForm.{$name}_FieldRequired.disabled = false;					
		}
		
		var fieldoptions = theForm.{$name}_OptionFields.options;		
		temp = fieldoptions.length - 1;
		
		//alert({$name}_FieldSet[{$name}_CurrentSelectedField].options[0].name);	
		
		for (var i=temp; i >= 0; i--) {			
			theForm.{$name}_OptionFields.options[i] = null;			
		}		
				
		for (var i=0; i < {$name}_FieldSet[{$name}_CurrentSelectedField].options.length; i++) {			
			theForm.{$name}_OptionFields.options[i] = new Option({$name}_FieldSet[{$name}_CurrentSelectedField].options[i].name, {$name}_FieldSet[{$name}_CurrentSelectedField].options[i].uuid);			
		}
		theForm.{$name}_OptionFieldValue.value = '';
		{$name}_HideAndDisplayOptionSet();
		
		return true;

	}
	
	
	
	// Store the name of the field into the array
	function {$name}_FieldNameStore() {
		theForm = document.forms.{$form};
	
		var fieldName = theForm.{$name}_FieldName.value;
		if ({$name}_CurrentSelectedIndex >= 0) {
			if (fieldName.length == 0) {
				fieldName = 'Untitled';
				theForm.{$name}_FieldName.value = fieldName;
			}
			{$name}_FieldSet[{$name}_CurrentSelectedField].name = fieldName;
	
				
			
			// Also update the list of fields
			var fields = theForm.{$name}_Fields.options;
			if (fields.length > 0) {
				if ({$name}_CurrentSelectedIndex != -1) {
					// It flickers when updating.. so don't bother to update
					// if hasn't changed.
					if (fields[{$name}_CurrentSelectedIndex].text != fieldName) {
						fields[{$name}_CurrentSelectedIndex].text = fieldName;
					}
				}
			}
		}
	}

	
	// Store the defaultvalue value of the field into the array
	function {$name}_FieldDefaultValueStore() {
		theForm = document.forms.{$form};
		var value = theForm.{$name}_FieldDefaultValue.value;
		if ({$name}_CurrentSelectedIndex >= 0) {
			{$name}_FieldSet[{$name}_CurrentSelectedField].defaultValue = value;
		}
	}
	
	// Store the size of the field into the array
	function {$name}_FieldSizeStore() {
		var value = document.forms.{$form}.{$name}_FieldSize.value;
		if ({$name}_CurrentSelectedIndex >= 0) {
			{$name}_FieldSet[{$name}_CurrentSelectedField].size = value;
		}
	}
	
	// Store the "Options" of the field into the array
	function {$name}_FieldOptionsStore() {
		theForm = document.forms.{$form};				
		var fields = theForm.{$name}_OptionFields;
		
		{$name}_FieldSet[{$name}_CurrentSelectedField].options = new Array();
		if (fields.options.length) {
			for(i=0; i < fields.options.length; i++) {
				{$name}_FieldSet[{$name}_CurrentSelectedField].options[i] = new {$name}_Option(fields.options[i].text,fields.options[i].value);
			}
		}
		
	}
	

	// Store the field type into the array
	function {$name}_FieldTypeStore() {	
		var fieldTypes = document.forms.{$form}.{$name}_FieldType.options;
		if ({$name}_CurrentSelectedIndex >= 0) {
			{$name}_HideAndDisplayOptionSet();
			if (fieldTypes.length == 0) {
				fieldTypes.selectedIndex = 0;
			}
			var selectedItem = fieldTypes.selectedIndex;
			{$name}_FieldSet[{$name}_CurrentSelectedField].type = fieldTypes[selectedItem].value;		
		}
	}

	// Store the field's required status into the array
	function {$name}_FieldRequiredStore() {
		var fieldRequireds = document.forms.{$form}.{$name}_FieldRequired.options;
		if ({$name}_CurrentSelectedIndex >= 0) {
			var selectedItem = fieldRequireds.selectedIndex;
			{$name}_FieldSet[{$name}_CurrentSelectedField].required = fieldRequireds[selectedItem].value;		
		}
	}
	
	$jsExtraFieldMethodDefine
	
	
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
/*
	var whitespace = new String("\n");
    var s = new String(str);
    var newStr = new String();
    for(i=0;i<s.length;i++){
         if (s.charAt(i) == whitespace) {    
              newStr += "<BR>";
         }else{
              newStr += s.charAt(i)
         }
    }
	
*/
	// Write the whole field set array out into a
	// hidden field on the form
	function {$name}_DumpFieldSet() {
		var dumpData = new Array();
		var fields = document.forms.{$form}.{$name}_Fields.options;
		// Loop through the Fields select list to get the data
		// so that it will be inserted in the correct order in the array
		for (var i=0; i < fields.length; i++) {	
			index = fields[i].value;
			dumpData[dumpData.length] = {$name}_FieldSet[index];
		}
		formDef = {$name}_serialize(dumpData);
		//alert(formDef);
		document.forms.{$form}.{$name}.value = formDef;
	}
	
	extraProcesses[extraProcesses.length] = {$name}_DumpFieldSet;
	
//-->
</SCRIPT>

			<INPUT TYPE="hidden" NAME="{$name}" VALUE="{$valueHTMLEditFormatted}">
			<FIELDSET TITLE="Fields"><LEGEND>{$this->fieldSetName}</LEGEND>
			<TABLE BORDER="0" WIDTH="100%">
				<TR><TD VALIGN="TOP">
					<TABLE BORDER="0">
						<TR>
							<TD VALIGN="TOP">
								<SELECT NAME="{$name}_Fields" SIZE="12" STYLE="width:20ex;" ONCHANGE="return {$name}_FieldLoad();">
									{$fieldOptionsHTML}
								</SELECT>		
							</TD>
							<TD VALIGN="TOP">
								<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Up" ONCLICK="{$name}_Move(-1)"><BR>
								<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Down" ONCLICK="{$name}_Move(1)"><BR>
								<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Add" ONCLICK="{$name}_New()"><BR>
								<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Delete" ONCLICK="{$name}_Delete()">
							</TD>
						</TR>
					</TABLE>
				</TD>				
				<TD VALIGN="TOP" align="Left" WIDTH="99%">
					<FIELDSET TITLE="Field Settings"><LEGEND>Field Settings</LEGEND>
					<TABLE BORDER="0" WIDTH="100%">
						<TR>
							<TD width="15%">Name:</TD>
							<TD width="35%"><INPUT TYPE="TEXT" NAME="{$name}_FieldName" VALUE="" SIZE="20" ONCHANGE="{$name}_FieldNameStore();"></TD>
							<TD width="15%">Size/Columns:</TD>
							<TD width="35%"><INPUT TYPE="TEXT" NAME="{$name}_FieldSize" VALUE="" SIZE="4" ONCHANGE="{$name}_FieldSizeStore();"></TD>
						</TR>
						<TR>
							<TD>Type:</TD>
							<TD>
								<SELECT NAME="{$name}_FieldType" ONCHANGE="{$name}_FieldTypeStore();" >
								{$fieldTypeOptionsHTML}
								</SELECT>
							</TD>
							<TD>Required:</TD>
							<TD><SELECT NAME="{$name}_FieldRequired" ONCHANGE="{$name}_FieldRequiredStore();">{$requiredOptionsHTML}</SELECT></TD>
						</TR>
						<TR>
							<TD>Default:</TD>
							<TD><INPUT TYPE="TEXT" NAME="{$name}_FieldDefaultValue" VALUE="" SIZE="20" ONCHANGE="{$name}_FieldDefaultValueStore();"></TD>						
EOD;
						for($i=0; $i < count($fieldExtras); $i++){														
							if (($i % 2) == 1) {
								$output .= "<TR>";								
							}
							$output .= "	
								<TD>{$fieldExtras[$i]["Description"]}:</TD>
								<TD>{$fieldExtras[$i]["Options"]}</TD>
							";
							if (($i % 2) == 0) {
								$output .= "</TR>";
							} 
						}
						
						$output .= <<< EOD
						
						
						
						<TR>
							<TD valign="Top">
								<span id="{$name}_OptionFieldSetDesc" style="display:none">Options:</span>
							</TD>							
							<TD COLSPAN="3">													
								<TABLE width="400" cellpadding="1" cellspacing="1" border="0" id="{$name}_OptionFieldSet" style="display:none">	
									<TR>
										<TD VALIGN="TOP">
											<SELECT NAME="{$name}_OptionFields" SIZE="6" STYLE="width:20ex;" ONCHANGE="return {$name}_OptionFieldLoad();">						
											</SELECT>		
										</TD>
										<TD VALIGN="TOP">
											<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Up" ONCLICK="{$name}_OptionMove(-1)"><BR>
											<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Down" ONCLICK="{$name}_OptionMove(1)"><BR>
											<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Add" ONCLICK="{$name}_OptionNew()"><BR>
											<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Delete" ONCLICK="{$name}_OptionDelete()">
										</TD>
										<TD VALIGN="TOP">
											Value :
											<INPUT TYPE="TEXT" NAME="{$name}_OptionFieldValue" VALUE="" SIZE="20" ONCHANGE="return {$name}_OptionFieldValueStore();">
										</TD>
									</TR>
								</TABLE>
								
							</TD>
						</TR>
					</TABLE>
					</FIELDSET>
 
				</TD>
				</TR>
			</TABLE>
			</FIELDSET>
	
EOD;
			return $output;
		}
		
	}

?>