<?php

requireOnceClass('AssetTypes');
requireOnceClass('FieldSet');

class LinkAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_LINK_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	function display(&$asset) {
		ss_paramKey($asset->cereal,$this->fieldPrefix.'URL');
		ss_paramKey($asset->cereal,$this->fieldPrefix.'RELATIVE',0);
		
		//print $asset->cereal[$this->fieldPrefix.'URL'];
		if ($asset->cereal[$this->fieldPrefix.'RELATIVE'] == 1) {
			locationRelative($asset->cereal[$this->fieldPrefix.'URL']);
		} else {
			location($asset->cereal[$this->fieldPrefix.'URL']);
		}
		
	}
	
	function embed(&$asset) {
		$this->display($asset);
	}

	function properties(&$asset) {
		require('view_properties.php');
	}
	
	function defineFields(&$asset) {
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
		
		$this->fieldSet->addField(new TextField (array(
					'name'			=>	$this->fieldPrefix.'URL',
					'displayName'	=>	'Target',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'60',	'maxLength'	=>	'255',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));

		$this->fieldSet->addField(new CheckBoxField (array(
					'name'			=>	$this->fieldPrefix.'RELATIVE',
					'displayName'	=>	'Relative',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'127',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
		)));
		
	}
	
	function edit(&$asset) {
		print "<P>Enter the URL you would like this link to \"point to\":</P><P>".$this->fieldSet->fields[$this->fieldPrefix.'URL']->display(FALSE,'AssetForm')."</P>";
		print "<P>If the above URL is relative to the root of your website, please tick this box: ".$this->fieldSet->fields[$this->fieldPrefix.'RELATIVE']->display(FALSE,'AssetForm')."</P>";
	}
	
	
}


?>
