<?php
	if ($this->ATTRIBUTES['Service'] == 'fillForm') {
	if ($success) {
		ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix."THANK_YOU_PAGE","Thank You. We will be in contact with you shortly.");
		print(ss_parseText($asset->cereal[$this->fieldPrefix."THANK_YOU_PAGE"]));
		$asset->display->title = 'Thank You';
	} else {
		ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix."SUBMIT_BUTTON","Submit");
		
		$fieldsHTML = '';
		foreach($fieldsArray as $fieldDef) {
	
			// Param all the settings we might have
			ss_paramKey($fieldDef,'name','Unknown');
			ss_paramKey($fieldDef,'type','Unknown');
			ss_paramKey($fieldDef,'required',0);
			ss_paramKey($fieldDef,'prefixed',0);
			ss_paramKey($fieldDef,'uuid','');		
			ss_paramKey($fieldDef,'comments','');		
	
			// Construct some field desciption html
			if ($fieldDef['required']) {
				$fieldRequiredHTML = " <span class=\"Required\">*</span>";
			} else {
				$fieldRequiredHTML = '';	
			}
			$fieldDescriptionHTML = "<td class=\"".$this->getClassName()."DescriptionCell\" valign=\"top\" align=\"left\">".ss_HTMLEditFormat($fieldDef['name']).$fieldRequiredHTML."</td>";
			
			$fieldCellHTML = '';
			if ($fieldDef['type'] != 'Comment') {
				$fieldCellHTML = "<td class=\"".$this->getClassName()."FieldCell\">".$fieldSet->getFieldInputHTML('F'.$fieldDef['uuid'])."</td>";
			}
			
			$fieldsHTML .= "<tr>$fieldDescriptionHTML $fieldCellHTML</tr>";
			if (strlen($fieldDef['comments'])) {
				$fieldsHTML .= "<tr><td class=\"".$this->getClassName()."CommentCell\" colspan=\"2\">{$fieldDef['comments']}</td></tr>";
			}
			
		}	
	
		$fieldsHTML .= "<tr><td colspan=\"2\" align=\"center\"><input class=\"".$this->getClassName()."SubmitButton\" type=\"submit\" value=\"".ss_HTMLEditFormat($asset->cereal[$this->fieldPrefix.'SUBMIT_BUTTON'])."\" name=\"submit\"></td></tr>";
		$fieldsHTML .= "<tr><td colspan=\"2\" align=\"center\"><span class=\"Required\">*</span> Indicates required fields</td></tr>";

		$data = array(
			'FieldsHTML'	=>	$fieldsHTML,
			'AssetPath'		=>	ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath())),
			'Errors'		=>	$errors,
		);
		
		$this->useTemplate('Display',$data);

	}
	} else {
		ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix."PAYMENT_THANK_YOU_PAGE","Thank You. We will be in contact with you shortly.");
		print(ss_parseText($asset->cereal[$this->fieldPrefix."PAYMENT_THANK_YOU_PAGE"]));
		$asset->display->title = 'Thank You';
	}	
?>