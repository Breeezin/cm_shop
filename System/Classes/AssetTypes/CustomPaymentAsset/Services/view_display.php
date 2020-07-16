<?php

	$paymentOptions = new Request("WebPay.Options", array('FormName'=>'adminForm'));

	ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix."SUBMIT_BUTTON","Submit");
	ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix."USE_CUSTOM_DISPLAY_TEMPLATE","0");
	ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix."CUSTOM_DISPLAY_TEMPLATE","");
			
	
	$fieldsHTML = '';
	
	if ($asset->cereal[$this->fieldPrefix."USE_CUSTOM_DISPLAY_TEMPLATE"] == 1) {
		// Using a custom one, replacing all the [FieldName] tags with the field input elements
		$fieldsHTML = "<tr><td>".$asset->cereal[$this->fieldPrefix."CUSTOM_DISPLAY_TEMPLATE"]."</td></tr>";

		foreach($fieldsArray as $fieldDef) {
	
			// Param all the settings we might have
			ss_paramKey($fieldDef,'name','Unknown');
			ss_paramKey($fieldDef,'type','Unknown');
			ss_paramKey($fieldDef,'required',0);
			ss_paramKey($fieldDef,'prefixed',0);
			ss_paramKey($fieldDef,'uuid','');		
			ss_paramKey($fieldDef,'comments','');		
	
			$fieldCellHTML = '';
			if ($fieldDef['type'] != 'Comment') {
				$fieldCellHTML = $fieldSet->getFieldInputHTML('F'.$fieldDef['uuid']);
				$fieldsHTML = stri_replace('['.$fieldDef['name'].']',$fieldCellHTML,$fieldsHTML);
			}
			
		}
			
		$submitButtonHTML = $paymentOptions->value;
		$fieldsHTML = stri_replace('[Submit]',$submitButtonHTML,$fieldsHTML);

	} else {

        	// Standard: <tr><td>field name:</td><td>field</td></tr>
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
				$fieldCellHTML = "<td align=\"left\" class=\"".$this->getClassName()."FieldCell\">".$fieldSet->getFieldInputHTML('F'.$fieldDef['uuid'])."</td>";
			}
			
			$fieldsHTML .= "<tr>$fieldDescriptionHTML $fieldCellHTML</tr>";
			if (strlen($fieldDef['comments'])) {
				$fieldsHTML .= "<tr><td class=\"".$this->getClassName()."CommentCell\" colspan=\"2\">{$fieldDef['comments']}</td></tr>";
			}
			
		}	
	
		//$fieldsHTML .= "<tr><td align=\"center\" colspan=2>".$paymentOptions->value."</td></tr>";
		$fieldsHTML .= "<tr><td align=\"left\" colspan=2><span class=\"Required\">*</span> Indicates required fields</td></tr>";
	}
	
	$data = array(
		'FieldsHTML'	=>	$fieldsHTML,
		'AssetPath'		=>	ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath())),
		'Errors'		=>	$errors,
		'PaymentButtons'=>	$paymentOptions->value,
		'Atts'		=>	$this->ATTRIBUTES,		
		'Total'			=>	$this->ATTRIBUTES['Total'],		
		'Products'		=>	$productDetails,				
	);
	
	$this->useTemplate('Display',$data);


		
?>