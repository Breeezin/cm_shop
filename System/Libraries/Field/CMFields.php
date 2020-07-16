<?php 
/*
 	List of fields in this file
 		AssetNameField
 		AssetTreeField
 */
// ############# Text Field ############# //

class AssetNameField extends TextField  {
	var $as_id;
	
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {		
		return parent::display($verify, $formName, $multi, $class);
	}
	function validate() {
		$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = {$this->as_id}");
		
		if ($Q_Asset['as_parent_as_id'] !== null) {
			$Q_NameCheck = query("SELECT * FROM assets 
							WHERE as_parent_as_id = {$Q_Asset['as_parent_as_id']} 
								AND as_id != {$this->as_id} 
								AND as_name LIKE '{$this->value}'
								AND as_deleted != 1
			");
		} else {
			$Q_NameCheck = query("SELECT * FROM assets 
							WHERE as_parent_as_id IS NULL 
								AND as_id != {$this->as_id} 
								AND as_name LIKE '{$this->value}'
								AND as_deleted != 1
			");
		}

		
			
		if ($Q_NameCheck->numRows()) return "$this->displayName must be unique.";
		
		return NULL;
	}
	
	function valueSQL() { 
		return $this->valueSQLText();
	}
}
 
class AssetTreeField extends Field {
	var $treeAssetRootID = 1;
	var $AppearsInMenus = 'No';
	
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		// style, class
		//'RootAssetID', 'AppearsInMenus', 'OnClick',	'IncludeChildrenOf',	'ExcludeChildrenOf',	'ExcludeAssets','TreeStyle'
		$value = $this->value;
		$tree = $this->treeProperty;
		//if ($this->defaultValue != null AND $this->value == null) $value = $this->defaultValue;
		$verifyValue = "";
		if (strlen($value)) {
			$result = new Request("Asset.PathFromID", array('as_id'=>$value));
			$verifyValue = $result->value;
		}
		$name  = $this->name;

		$strings = array();
		foreach (array('includeChildrenOf','excludeChildrenOf','excludeAssets') as $itemName) {
			$strings[$itemName] = ArrayKeysToList($tree[$itemName]);
		}		
		
		$returnVal = <<< EOD
		<INPUT TYPE="TEXT" SIZE="{$this->size}" {$this->onFocus} NAME="{$name}_V" VALUE="$verifyValue" MAXLENGTH="$this->maxLength">&nbsp;
		<INPUT NAME="Browse" Value="Browse" Type="Button" ONCLICK="window.open(getAssetTreeURL(),'AssetTree','{$this->treePopWindowProperty}');" class="formborder">
		&nbsp;
		<INPUT NAME="Delete" Value="Clear" Type="Button" ONCLICK="clearFields()" class="formborder">
		<INPUT NAME="{$name}" value="$value" TYPE="hidden">
		<script language="Javascript">		
			function clearFields() {
				document.forms.{$tree['openerFormName']}.{$name}_V.value='';
				document.forms.{$tree['openerFormName']}.{$name}.value='';
			}
			
			function getAssetTreeURL() {
				openerForm = 'opener.document.forms.{$tree['openerFormName']}';
				onClick = 'TreeOnClick&TreeOnClick='+openerForm+'.{$name}_V.value=AssetPath;'+openerForm+'.{$name}.value=as_id;window.close();';
				
				theURL = 'index.php?act=Asset.Tree';
				theURL += '&RootAssetID={$tree['treeAssetRootID']}&AppearsInMenus={$tree['appearsInMenus']}';
				theURL += '&IncludeChildrenOf={$strings['includeChildrenOf']}&ExcludeChildrenOf={$strings['excludeChildrenOf']}'
				theURL += '&TreeDescription={$tree['treeDescription']}';
				theURL += '&FilterByAdmintrue=1&ExcludeAssets={$strings['excludeAssets']}&TreeStyle={$tree['treeStyle']}&Layout=AdminPopup&OnClick=' + onClick +'&';
				return theURL;
			}
		</script>
EOD;

		return $returnVal;
	}
	function displayValue($value) {
		if (strlen($value) and is_numeric($value)){
			$result = new Request("Asset.PathFromID", array('as_id'=> $value));
			$path = ss_withoutPreceedingSlash($result->value);
			return "<a href='{$GLOBALS['cfg']['currentServer']}$path'>".ListLast($path,'/')."</a>";
		} else {
			return "&nbsp;";
		}
	}
	function validate() {
		return NULL;
	}
	function valueSQL() { 
		return parent::valueSQL();
	}
}


class MultiAssetTreeField extends Field {
	var $treeAssetRootID = 1;
	var $AppearsInMenus = 'No';
		
	
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		// style, class
		//'RootAssetID', 'AppearsInMenus', 'OnClick',	'IncludeChildrenOf',	'ExcludeChildrenOf',	'ExcludeAssets','TreeStyle'
		$value = $this->value;
		$tree = $this->treeProperty;
		//if ($this->defaultValue != null AND $this->value == null) $value = $this->defaultValue;
		if (!is_array($value)) {			
			if (strlen($value)) {							
				$value = unserialize($value);
			} else {
				$value = array();
			}				
		}
		
		$serialized = ss_HTMLEditFormat(serialize($value));
		$name  = $this->name;
		
		$valueOptions = '';
		foreach ($value as $temp) {
			$tempArray = ListToArray($temp, '|');
			$valueOptions .= "<option value=\"$temp\">{$tempArray[1]}</option>";
		}
		
		$strings = array();
		foreach (array('includeChildrenOf','excludeChildrenOf','excludeAssets') as $itemName) {
			$strings[$itemName] = ArrayKeysToList($tree[$itemName]);
		}		
		
		$returnVal = <<< EOD
		<table cellpadding=5>
			<TR><TD>
				<SELECT multiple onChange='selectedAsset(this.form)' SIZE="{$this->size}" {$this->onFocus} NAME="{$name}_V" >$valueOptions</select>
			</TD>
			<TD valign=top>
				<INPUT type="checkbox" Name="IncludeChildren" value='1' onClick='changeInclude(this.form)'>Include sub-items<BR><BR>
				<INPUT NAME="Delete" Value="Delete Item" Type="Button" ONCLICK="clearFields(this.form)" class="formborder"><BR><BR>
				<INPUT NAME="Browse" Value="Add Item" Type="Button" ONCLICK="window.open(getAssetTreeURL(),'AssetTree','{$this->treePopWindowProperty}');" class="formborder">
			</TD></TR>		
		</table>
		<input name='{$name}' value="{$serialized}" type='hidden'>
		<script language="Javascript">		
			function changeInclude(theForm) {
				if (theForm['{$name}_V'].selectedIndex >= 0) {
					var temp = theForm['{$name}_V'].options[theForm['{$name}_V'].selectedIndex].value.split('|');
					if (theForm.IncludeChildren.checked) {										
						theForm['{$name}_V'].options[theForm['{$name}_V'].selectedIndex].value = temp[0] + "|" + temp[1]  + "|1";
					} else {
						theForm['{$name}_V'].options[theForm['{$name}_V'].selectedIndex].value = temp[0] + "|" + temp[1] + "|0";
					}
				} else {
					theForm.IncludeChildren.checked = false;
				}
				{$name}_DumpFieldSet();
			}
			
			function selectedAsset(theForm) {
				//alert(theForm['{$name}_V'].selectedIndex);
				var temp = theForm['{$name}_V'].options[theForm['{$name}_V'].selectedIndex].value.split('|');						
				//alert(temp);
				if (temp[2] == '1') {
					theForm.IncludeChildren.checked = true;
				} else {
					theForm.IncludeChildren.checked = false;
				}
			}
			
			function addAsset(path, id) {				
				document.forms.{$tree['openerFormName']}['{$name}_V'].options[document.forms.{$tree['openerFormName']}['{$name}_V'].options.length] = new Option(path, id + "|" + path + "|1");
				document.forms.{$tree['openerFormName']}['{$name}_V'].selectedIndex = document.forms.{$tree['openerFormName']}['{$name}_V'].options.length-1;
				{$name}_DumpFieldSet();
			}
			function clearFields(theForm) {				
				theForm['{$name}_V'].options[theForm['{$name}_V'].selectedIndex] = null;															
				document.forms.{$tree['openerFormName']}['{$name}_V'].selectedIndex = document.forms.{$tree['openerFormName']}['{$name}_V'].options.length-1;
				{$name}_DumpFieldSet();
			}
			
			function getAssetTreeURL() {
				openerForm = 'opener.document.forms.{$tree['openerFormName']}';
				onClick = 'TreeOnClick&TreeOnClick=opener.addAsset(AssetPath, as_id);window.close();';				
				theURL = 'index.php?act=Asset.Tree';
				theURL += '&RootAssetID={$tree['treeAssetRootID']}&AppearsInMenus={$tree['appearsInMenus']}';
				theURL += '&IncludeChildrenOf={$strings['includeChildrenOf']}&ExcludeChildrenOf={$strings['excludeChildrenOf']}'
				theURL += '&TreeDescription={$tree['treeDescription']}';
				theURL += '&FilterByAdmintrue=1&ExcludeAssets={$strings['excludeAssets']}&TreeStyle={$tree['treeStyle']}&Layout=AdminPopup&OnClick=' + onClick +'&';
				return theURL;
			}
			
			// Source: eskaly - http://php.cd/cowiki/Eskaly/Me 
			function {$name}_serialize (variable) {
			    switch (typeof variable) {
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
			var dumpData = new Array();
			var fields = document.forms.{$tree['openerFormName']}.{$name}_V.options;
			// Loop through the Fields select list to get the data
			// so that it will be inserted in the correct order in the array
			for (var i=0; i < fields.length; i++) {	
				index = fields[i].value;
				dumpData[dumpData.length] = index;
			}
			formDef = {$name}_serialize(dumpData);			
			document.forms.{$tree['openerFormName']}.{$name}.value = formDef;
		}
		</script>
EOD;

		return $returnVal;
	}
	
	
	function displayValue($value) {		
		return "&nbsp;";		
	}
	function validate() {
		return NULL;
	}
	function valueSQL() { 
		return parent::valueSQL();
	}
}

?>