<?php

	if ($success) {
		ss_paramKeyAndNoStringLength($asset->cereal,"AST_SURVEYTHANKYOU_CONTENT","Thank You.");
		$thanks = (ss_parseText($asset->cereal["AST_SURVEYTHANKYOU_CONTENT"]));
        print $thanks;
        $asset->display->title = 'Thank You';
	} else {
		$fieldsHTML = '';
        $submitText = ss_HTMLEditFormat($asset->cereal[$this->fieldPrefix.'SUBMIT_BUTTON']);

        if (strlen($submitText) == 0)
            $submitText = "Submit";

		if ($asset->cereal[$this->fieldPrefix."USE_CUSTOM_SURVEY_TEMPLATE"] == 1) {

			// Using a custom one, replacing all the [FieldName] tags with the field input elements
			$fieldsHTML = "<tr><td>".$asset->cereal[$this->fieldPrefix."SURVEYPAGE_CONTENT"]."</td></tr>";

            $data=array(
                'AssetPath'=>$assetPath,
//                'BackText'=>ss_HTMLEditFormat($asset->cereal[$this->fieldPrefix.'BACKTOMAIN_TEXT']),
            );
//        	$backTo = $this->processTemplate('BackToMain',$data);
//        	$fieldsHTML = stri_replace('[Back To Main]',$backTo,$fieldsHTML);

			foreach($fieldsArray as $fieldDef) {

				// Param all the settings we might have
				ss_paramKey($fieldDef,'name','Unknown');
				ss_paramKey($fieldDef,'type','Unknown');
				ss_paramKey($fieldDef,'required',0);
				ss_paramKey($fieldDef,'prefixed',0);
				ss_paramKey($fieldDef,'uuid','');
				ss_paramKey($fieldDef,'comments','');
				ss_paramKey($fieldDef,'size','100');

				$fieldCellHTML = '';
				if ($fieldDef['type'] != 'Comment') {
					$fieldCellHTML = $fieldSet->getFieldInputHTML('Su'.$fieldDef['uuid']);
					$fieldsHTML = stri_replace('['.$fieldDef['name'].']',$fieldCellHTML,$fieldsHTML);
    			}

			}
			$submitButtonHTML = "<input class=\"".$this->getClassName()."SubmitButton\" type=\"submit\" value=\"".$submitText."\" name=\"submit\">";

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
    				$fieldCellHTML = "<td class=\"".$this->getClassName()."FieldCell\">".$fieldSet->getFieldInputHTML('Su'.$fieldDef['uuid'])."</td>";
    			}

    			$fieldsHTML .= "<tr>$fieldDescriptionHTML $fieldCellHTML</tr>";
    			if (strlen($fieldDef['comments'])) {
    				$fieldsHTML .= "<tr><td class=\"".$this->getClassName()."CommentCell\" colspan=\"2\">{$fieldDef['comments']}</td></tr>";
    			}
    		}

    		$fieldsHTML .= "<tr><td align=\"left\">&nbsp;</td><td align=\"left\"><span class=\"Required\">*</span> Indicates required fields</td></tr>";
    		$fieldsHTML .= "<tr><td align=\"left\">&nbsp;</td><td align=\"left\" class=\"inputsubmit\"><input type=\"submit\" value=\"$submitText\"></td></tr>";

        }

		$data = array(
			'FieldsHTML'	=>	$fieldsHTML,
			'AssetPath'		=>	ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath())),
			'Errors'		=>	$errors,
		);

        $this->useTemplate('Survey',$data);
	}

?>