<?php

	$data = array();
	
	$data['AssetPath'] = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));
	$data['user_groups'] = $asset->cereal[$this->fieldPrefix.'USERGROUPS'];
	$data['Email'] = $this->ATTRIBUTES['Email'];
	$data['first_name'] = $this->ATTRIBUTES['first_name'];
	$data['last_name'] = $this->ATTRIBUTES['last_name'];
	$data['ATTRIBUTES'] = $this->ATTRIBUTES;
	$data['success'] = $success;
	$data['errors'] = $errors;
	
	

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		if ($this->ATTRIBUTES['DoAction'] == 'Subscribe') {
			$data['thankYouContent'] = ss_parseText($asset->cereal[$this->fieldPrefix.'SUBSCRIBE_CONTENT']);
		} else {
			$data['thankYouContent'] = ss_parseText($asset->cereal[$this->fieldPrefix.'UNSUBSCRIBE_CONTENT']);
		}
	}
	
	if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
		$fieldsHTML = '';
		
		foreach($selectedFieldDefs as $fieldDef) {			
			// Param all the settings we might have
			ss_paramKey($fieldDef,'name','Unknown');
			ss_paramKey($fieldDef,'type','Unknown');
			ss_paramKey($fieldDef,'required',0);
			ss_paramKey($fieldDef,'prefixed',0);
			ss_paramKey($fieldDef,'uuid','');		
			ss_paramKey($fieldDef,'comments','');		
			
			$fieldCellHTML = '';
			//print($fieldDef['type'].' '.$fieldDef['name']);
			$fieldCellHTML = $fieldSet->getFieldInputHTML('Us'.$fieldDef['uuid']);
			$fieldCellHTML = str_replace(':', '', $fieldCellHTML);
			// Construct some field desciption html
			if ($fieldDef['required']) {
				$fieldRequiredHTML = '<TD WIDTH="5%" VALIGN="TOP" CLASS="requiredFlag">+</TD>';
			} else {
				$fieldRequiredHTML = '<TD WIDTH="5%" VALIGN="TOP" CLASS="requiredFlag">&nbsp;</TD>';
			}
			
			//$fieldDescriptionHTML = "<td class=\"".$this->getClassName()."DescriptionCell\" valign=\"top\" align=\"left\">".ss_HTMLEditFormat($fieldDef['name']).$fieldRequiredHTML."</td>";					
			$fieldNameHTML = '<TH ALIGN="LEFT" VALIGN="TOP">'.ss_HTMLEditFormat($fieldDef['name']).'</TH>';
			
			$fieldsHTML .= "<tr>$fieldRequiredHTML $fieldNameHTML <TD>$fieldCellHTML</td></tr>";							
		}		
	
		$data['FieldsHTML'] = 	$fieldsHTML;
	}

	$this->useTemplate('display',$data);
	
?>
