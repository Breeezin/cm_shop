<?php

requireOnceClass('AssetTypes');
requireOnceClass('Field');

class MenuAsset extends AssetTypes {
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		//ss_DumpVar($asset->ATTRIBUTES);
		$result = new Request('Menus.Display',
			array_merge(
				$asset->ATTRIBUTES,
				$asset->cereal
			)
		);
		
		print $result->display;
		//print 'menu goes here';
		$asset->display->layout = 'None';
	}
	
	function embed(&$asset) {
		$this->display($asset);
	}

	function defineFields(&$asset) {
		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
				
		require(dirname(__FILE__).'/Menus/inc_menuFields.php');
				
		foreach ($menuFields as $fieldList) {
			foreach ($fieldList as $name => $values) {
				if ($values[0] != "AssetTreeField") {
					$this->fieldSet->addField(new $values[0] (array(
						'name'			=>	$name,
						'displayName'	=>	$values[1],
						'required'		=>	$values[6],
						'size'		=>	$values[3],	'maxLength'	=>	1000,
						'defaultValue'	=>	$values[2],
						'options'	=>	$values[4],
						'onChange'	=>	$values[5],
					)));
				} else {
					/*includeChildrenOf,excludeAssets,excludeChildrenOf,,,*/
					$this->fieldSet->addField(new AssetTreeField(array(
						'name'			=>	$name,
						'displayName'	=>	$values[1],
						'required'		=>	$values[6],
						'size'		=>	$values[3],	'maxLength'	=>	1000,
						'defaultValue'	=>	$values[2],
						'onFocus'		=> 'onFocus="this.form.AST_MENU_ROOT_ASSETLEVEL.select()"',			
						'treeProperty'   => array('openerFormName'=>'AssetForm',
												  'treeDescription'=>'Please select the root asset for the menus.',
												  'treeAssetRootID'=>'1',
												  'treeStyle'=>'width:260;height:300; overflow:auto;border:solid black 1px;',
												  'appearsInMenus'=>'No',
												  'includeChildrenOf'=>array(),
												  'excludeAssets'=>array(),
												  'excludeChildrenOf'=>array(),
												  'appearsInMenus'=>'No',),
						'treePopWindowProperty' => 'width=300,height=350,scrollbar=1',
					)));
				}
			}
		}
		
	}
	
	function edit(&$asset) {
		require(dirname(__FILE__).'/Menus/inc_menuFields.php');

		foreach ($menuFields as $fieldType => $fieldList) {
			print("<DIV STYLE=\"display:none;\" ID=\"".str_replace(' ','',$fieldType)."Container\"><FIELDSET NAME=\"{$fieldType}\"><LEGEND>{$fieldType}</LEGEND><TABLE>");
			foreach ($fieldList as $name => $values) {
				$field = $this->fieldSet->getField($name);
				
				print('<tr><td width="150"><STRONG>'.ss_HTMLEditFormat($field->displayName).' :</STRONG></td><td>');
				$this->fieldSet->displayField($name);
				if ($field->defaultValue != NULL) {
					print("&nbsp; Defaults to: ".ss_HTMLEditFormat($field->defaultValue));
				}
				print('</td></tr>');	
				
			}
			print("</TABLE></FIELDSET></DIV>");
		}
		
		$js = <<< EOD
<SCRIPT LANGUAGE="Javascript">
	function hideId(id) { 
		document.getElementById(id).style.display = 'none'; 
	}
	function showId(id) { 
		document.getElementById(id).style.display = ''; 
	}
	function updateFieldSets(which) {
		value = which.options[which.selectedIndex].value;
		if (value == 'Footer') {
			showId('FooterMenuSettingsContainer');
			hideId('StandardMenuSettingsContainer');
			hideId('DropDownMenuSettingsContainer');
		} else {
			hideId('FooterMenuSettingsContainer');
			showId('StandardMenuSettingsContainer');
			showId('DropDownMenuSettingsContainer');
		}
	}
	
	// Always want to show the menu type selector
	showId('MenuTypeContainer');
	
	// Display the correct fields based on what is selected in the menu type field
	updateFieldSets(document.forms.AssetForm.AST_MENU_TYPE);
</SCRIPT>
EOD;
		print $js;
	}
	
}


?>
