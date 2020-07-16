<?php 
	global $cfg;
	$formFieldValue = $this->fieldSet->getFieldValue($this->fieldPrefix.'FORM');
	$directory =ss_storeForAsset($asset->getID());
	
	$result = new Request("UID.Get",array('Count' => 238));
	$uidJS = '';
	foreach ($result->value as $uid) {
		$uidJS .= ss_comma($uidJS)."'".$uid."'";
	}
			
	if (strlen($formFieldValue)) {
		$formFields = unserialize($formFieldValue);
	} else {
		$formFields = array();
	}
	
	// Make some javascript to define all the fields
	$defineFieldsJS = '';
	$fieldOptionsHTML = '';
	$counter = 0;			
	$firstFieldStatus = '';
	$firstField = null;
	ksort($formFields,SORT_NUMERIC); 
	//"title" : "Untitled", "smurl" : "", "meurl" : "", "lgurl" : "", "description" : "", "link" : ""
	foreach($formFields as $index => $formField) {				
		ss_paramKey($formField,'title','Unknown');		
		ss_paramKey($formField,'target','_blank');		
		ss_paramKey($formField,'url','');
		ss_paramKey($formField,'description','');
		ss_paramKey($formField,'link','');	
		ss_paramKey($formField,'uuid','');	
		
		$defineFieldsJS .= "FieldSet[$index] = new Field('".ss_JSStringFormat($formField['title'])."','".ss_JSStringFormat($formField['url'])."','".ss_JSStringFormat($formField['description'])."','".ss_JSStringFormat($formField['link'])."', '".ss_JSStringFormat($formField['uuid'])."','".ss_JSStringFormat($formField['target'])."');\n";
		$fieldOptionsHTML .= "<option value=\"$counter\">{$formField['title']}</option>";	
		$counter++;
	}
			
?>
<SCRIPT LANGUAGE="Javascript">
<!--
	FieldSet = new Array();
	CurrentSelectedField = -1;
	CurrentSelectedIndex = 0;

	NextUID = 1;
	uids = new Array(<?=$uidJS?>);
	
	
	function openImageFolder() {
		var selectedImage = document.AssetForm.FieldURL.value;
		
		if (selectedImage.length) {
			selectedImage = "&SelectedImage=" + selectedImage;
		} 
		
		var url = 'index.php?act=AssetSelector.SharedImageDisplay&OnClick=<?=ss_URLEncodedFormat("opener.document.AssetForm.FieldURL.value=selectedImage;opener.loadImage();opener.FieldURLStore();");?>&as_id=<?=$asset->getID()?>' + selectedImage;
		var newwin = window.open(url, 'sharedImageDirectory','width=590,height=320');
	}
	function Field(title, url, description, link, uuid, target) {
		this.title = title;		
		this.url = url;
		this.description = description;
		this.link = link;
		this.target = target;	
		if (uuid.length == 0) {
			this.uuid = uids[NextUID];
			NextUID = NextUID+1;
			if (NextUID > uids.length) alert('Please save this Asset now before adding any new fields.');
		} else {
			this.uuid = uuid;
		}
	}

	function Delete() {
		var theForm = document.forms.AssetForm;
		var fields = theForm.Fields.options;
		if (fields.length > 0) {			
			fields[CurrentSelectedIndex] = null;
			CurrentSelectedIndex = -1;
	
			if (fields.length > 0) {
				fields.selectedIndex = 0;
				FieldLoad();	
			} else {				
				theForm.FieldTitle.value = '';
				theForm.FieldURL.value = '';
				theForm.FieldDescription.value = '';
				theForm.FieldLink.value = '';										
				theForm.FieldTarget.selectedIndex = -1;										
			}			
		}
	}
	
	function New() {
		var fields = document.forms.AssetForm.Fields.options;
		newSelected = fields.length;
		fields[fields.length] = new Option('Untitled',FieldSet.length,false);
		FieldSet[FieldSet.length] = new Field('Untitled','','','','','_blank');
		fields.selectedIndex = newSelected;				
		FieldLoad();
	}
	
	// Move a form field up or down in the list 
	function Move(how) {
		var fields = document.forms.AssetForm.Fields.options;
		var selectedItem = fields.selectedIndex;
		
		if (((how == -1) && (selectedItem > 0))
			|| ((how == 1) && (selectedItem < fields.length-1))) {
			// Swap the display first
			oldText = fields[selectedItem+how].text;
			fields[selectedItem+how].text = fields[selectedItem].text; 
			fields[selectedItem].text = oldText; 
			
			// Update the selection bar
			document.forms.AssetForm.Fields.options.selectedIndex = selectedItem+how;

			// Swap the values
			oldValue = fields[selectedItem+how].value;
			fields[selectedItem+how].value = fields[selectedItem].value; 
			fields[selectedItem].value = oldValue; 
		}
		CurrentSelectedIndex = fields.selectedIndex;
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

	<?=$defineFieldsJS?>
	
	// Update the field settings area with the 
	// attributes from the currently selected field
	function FieldLoad() {
		
		// Make sure everything is up to date before we wipe out those fields
		FieldTitleStore();
		FieldURLStore();
		FieldDescriptionStore();
		FieldLinkStore();
		FieldTargetStore();
		
		theForm = document.forms.AssetForm;
		
		var fields = theForm.Fields.options;
		var selectedItem = fields.selectedIndex;

		CurrentSelectedIndex = fields.selectedIndex;
		CurrentSelectedField = fields[selectedItem].value;
		
		
		theForm.FieldTitle.value = FieldSet[CurrentSelectedField].title;
		theForm.FieldURL.value = FieldSet[CurrentSelectedField].url;
		loadImage();
		
				
		theForm.FieldDescription.value = FieldSet[CurrentSelectedField].description;
		theForm.FieldLink.value = FieldSet[CurrentSelectedField].link;
		if (FieldSet[CurrentSelectedField].target =='_blank') {
			theForm.FieldTarget.selectedIndex = 0;
		} else {
			theForm.FieldTarget.selectedIndex = 1;
		}
						
		return true;

	}
	function loadImage() {
		theForm = document.forms.AssetForm;	
		if (theForm.FieldURL.value.length) {
			document.images.galShowImage.style.display = "";		
			document.images.galShowImage.src = "<?=$directory?>" + theForm.FieldURL.value;			
		} else {
			document.images.galShowImage.src = "";
			document.images.galShowImage.style.display = "none";		
		}
	}
	// Store the name of the field into the array
	function FieldTitleStore() {
		theForm = document.forms.AssetForm;
	
		var fieldName = theForm.FieldTitle.value;
		if (CurrentSelectedField >= 0) {
			if (fieldName.length == 0) {
				fieldName = 'Untitled';
				theForm.FieldTitle.value = fieldName;
			}
			FieldSet[CurrentSelectedField].title = fieldName;
					
			// Also update the list of fields
			var fields = theForm.Fields.options;
			if (fields.length > 0) {
				if (CurrentSelectedIndex != -1) {
					// It flickers when updating.. so don't bother to update
					// if hasn't changed.
					if (fields[CurrentSelectedIndex].text != fieldName) {
						fields[CurrentSelectedIndex].text = fieldName;
					}
				}
			}
		}
	}

	// Store the defaultvalue value of the field into the array
	function FieldLinkStore() {
		theForm = document.forms.AssetForm;
		var value = theForm.FieldLink.value;
		if (CurrentSelectedField >= 0) 
			FieldSet[CurrentSelectedField].link = value;
	}
	
	// Store the size of the field into the array
	function FieldURLStore() {
		var value = document.forms.AssetForm.FieldURL.value;
		if (CurrentSelectedField >= 0) 		
		FieldSet[CurrentSelectedField].url = value;
	}
	
	// Store the "Options" of the field into the array
	function FieldDescriptionStore() {
		theForm = document.forms.AssetForm;	
		var value = document.forms.AssetForm.FieldDescription.value;
		if (CurrentSelectedField >= 0)
			FieldSet[CurrentSelectedField].description = value;
	}
	
	// Store the "Options" of the field into the array
	function FieldTargetStore() {
		theForm = document.forms.AssetForm;	
		var selected = document.forms.AssetForm.FieldTarget.selectedIndex;
		if (CurrentSelectedField >= 0) {
			if (selected == 0)
				FieldSet[CurrentSelectedField].target = '_blank';
			else 
				FieldSet[CurrentSelectedField].target = '';
		}
	}
	
	// Source: eskaly - http://php.cd/cowiki/Eskaly/Me 
	function serialize (variable)
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
	                r+= serialize(prop)+serialize(variable[prop]);
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
	function DumpFieldSet() {
		var dumpData = new Array();
		var fields = document.forms.AssetForm.Fields.options;
		// Loop through the Fields select list to get the data
		// so that it will be inserted in the correct order in the array
		for (var i=0; i < fields.length; i++) {	
			index = fields[i].value;
			dumpData[dumpData.length] = FieldSet[index];
		}
		formDef = serialize(dumpData);
		
		document.forms.AssetForm.<?=$this->fieldPrefix?>FORM.value = formDef;
	}
	
	extraProcesses[extraProcesses.length] = DumpFieldSet;
	
//-->
</SCRIPT>
	
	<!--- This will be filled with the serializaion of the galleryImages array --->
	<INPUT TYPE="HIDDEN" NAME="<?=$this->fieldPrefix?>FORM"  VALUE="<?=ss_JSStringFormat($formFieldValue)?>">
		
	<TABLE CELLSPACING="0" CELLPADDING="5" WIDTH="100%">
	<TR>
		<TD VALIGN="TOP">
			<FIELDSET>
				<LEGEND>Random Images</LEGEND>
			<TABLE WIDTH="100%">
				<TR>
					<TD VALIGN="TOP">
						<SELECT NAME="Fields" SIZE="10" STYLE="width:20em" ONCHANGE="FieldLoad();">												
						<?=$fieldOptionsHTML?>
						</SELECT>
					</TD>
					<TD VALIGN="TOP">
						<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Up" ONCLICK="Move(-1)"><BR>
						<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Down" ONCLICK="Move(1)"><BR>
						<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Add" ONCLICK="New()"><BR>
						<INPUT TYPE="BUTTON" CLASS="childrenButton" VALUE="Delete" ONCLICK="Delete()">
	<!-------							
						<INPUT TYPE="BUTTON" OnClick="newGalleryImage()" VALUE="Add" CLASS="adminButtons" STYLE="width:8em"><BR>
						<INPUT TYPE="BUTTON" OnClick="copyGalleryImage()" VALUE="Copy" CLASS="adminButtons" STYLE="width:8em"><BR>
						<INPUT TYPE="BUTTON" OnClick="delGalleryImage()" VALUE="Del" CLASS="adminButtons" STYLE="width:8em"><BR>
						<INPUT TYPE="BUTTON" OnClick="galMoveUp()" VALUE="Up" CLASS="adminButtons" STYLE="width:8em"><BR>
						<INPUT TYPE="BUTTON" OnClick="galMoveDown()" VALUE="Down" CLASS="adminButtons" STYLE="width:8em"><BR>
						<INPUT TYPE="BUTTON" OnClick="sortGalImages()" VALUE="Sort" CLASS="adminButtons" STYLE="width:8em">
	------>
					</TD>
				</TR>
			</TABLE>
			</FIELDSET>			
		</TD>
		<TD>
			<FIELDSET>
				<LEGEND>Image Detail</LEGEND>
				<TABLE BORDER="0">
					<TR><TH ALIGN="LEFT">Title :</TH><TD ALIGN="LEFT"><INPUT TYPE="TEXT" NAME="FieldTitle" VALUE="" onChange="FieldTitleStore()"></TD></TR>					
					<TR><TH ALIGN="LEFT">File :</TH><TD VALIGN="MIDDLE"><INPUT TYPE="TEXT" NAME="FieldURL" VALUE="" onfocus="alert('Please select an image from the folder');this.blur();"></TD><TD><INPUT type="button" name="folder" onclick="openImageFolder()" value="Image Folder">&nbsp;<INPUT type="button" name="delete" onclick="this.form.FieldURL.value='';loadImage();" value="Delete Image"></TD></TR>					
					<TR><TD COLSPAN="3">				
						<DIV STYLE="width:400px;height:256px;overflow:auto;border:none"><IMG SRC="" NAME="galShowImage" style="display:none"></DIV>					
					</TD></TR>
					<TR>
						<TH COLSPAN="3" ALIGN="LEFT">Description :</TH></TR>
					<TR>
						<TD COLSPAN="3"><TEXTAREA ROWS="3" STYLE="width:100%" NAME="FieldDescription" OnChange="FieldDescriptionStore()"></TEXTAREA></TD>
					</TR>
					<TR><TH ALIGN="LEFT">Link (optional) :</TH><TD ALIGN="LEFT"><INPUT TYPE="TEXT" NAME="FieldLink" VALUE="" onChange="FieldLinkStore()"></TD></TR>
					<TR><TH ALIGN="LEFT">Link Target(optional) :</TH><TD ALIGN="LEFT"><SELECT NAME="FieldTarget" onChange="FieldTargetStore()"><option value='_blank'>New Window</option><option value=''>Same Window</option></SELECT></TD></TR>	
				</TABLE>
			</FIELDSET>
		</TD>
	</TR>
	</TABLE>
	
