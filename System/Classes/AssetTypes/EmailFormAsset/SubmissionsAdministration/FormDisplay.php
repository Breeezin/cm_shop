<?php
/*	$form = '<TABLE WIDTH="100%" CELLSPACING="0" CELLPADDING="2">';
	foreach ($this->fields as $field) {
		
		// Display the standard field
		$rowClass = '';
		if (array_key_exists($field->name,$errors)) {
			$rowClass = 'CLASS="AdminErrorField"';
		}
		$note = '';
		if (strlen($field->note) > 0) {
			$note = '<BR><SPAN CLASS="AdminNote">'.$field->note.'</SPAN>';
		}
		
		$form .='<TR '.$rowClass.'>'.
			'<TD VALIGN="TOP" ALIGN="RIGHT" WIDTH="5" CLASS="AdminRequired">'.($field->required?'*':NULL).'</TD>'.
			'<TD VALIGN="TOP" WIDTH="30%"><SPAN CLASS="AdminDisplayName">'.$field->displayName.'</SPAN>'.$note.'</TD>'.
			'<TD WIDTH="70%">'.$field->display(FALSE, 'adminForm').'</TD>'.
		'</TR>';

		// Display the verify field (if required)
		if ($field->verify) {
			$rowClass = '';
			if (array_key_exists($field->name.'_V',$errors)) {
				$rowClass = 'CLASS="AdminErrorField"';
			}
			$note = '';
			if (strlen($field->note) > 0) {
				$note = '<BR><SPAN CLASS="AdminNote">'.$field->note.'</SPAN>';
			}
			
			$form .='<TR '.$rowClass.'>'.
				'<TD VALIGN="TOP" ALIGN="RIGHT" WIDTH="5" CLASS="AdminRequired">'.($field->required?'*':NULL).'</TD>'.
				'<TD VALIGN="TOP" WIDTH="30%"><SPAN CLASS="AdminDisplayName">Verify '.$field->displayName.'</SPAN>'.$note.'</TD>'.
				'<TD WIDTH="70%">'.$field->display(TRUE, 'adminForm').'</TD>'.
			'</TR>';
		}

	}	
	$form .= '</TABLE>';*/
	//$tableTags
	/*$data = array(
		'fields'	=>	$this->fields,
		'errors'	=>	$errors,
		'tableTags'	=>	$tableTags,
		'isForm'	=>	$isForm,
	);
	$form = $this->processTemplate($formTemplate,$data);*/
	
	$form = '<p>Received '.ss_HTMLEditFormat($this->fields['efs_timestamp']->value).'</p>';
	$form .= $this->fields['efs_text']->value;
	
	// Insert hidden field for parent table link if one was specified
	if ($this->parentTable != NULL) {
		if (array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
			$form .= "<INPUT TYPE=\"HIDDEN\" NAME=\"{$this->parentTable->linkField}\" VALUE=\"{$this->ATTRIBUTES[$this->parentTable->linkField]}\">";
		}
	}
	
	// add any hidden values that need to be passed
	if ($this->hiddenValues !== null) {
		foreach($this->hiddenValues as $name => $value) {
			$form .= "<INPUT TYPE=\"HIDDEN\" NAME=\"".ss_HTMLEditFormat($name)."\" VALUE=\"".ss_HTMLEditFormat($value)."\">";
		}	
	}
	return $form;
?>
