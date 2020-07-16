<?php
requireOnceClass('Field');

class FieldSet extends Plugin {

	var $fields = array();
	var $tableName;
	var $tablePrimaryKey;
	var $tableAssetLink = null;
	var $assetLink = null;
	var $parentTable = null;
	var $primaryKey = null;
	var $parentKey = null;
	var $tableDeleteFlag = null;
	var $tableTimeStamp = null;
	var $formName = 'adminForm';
	var $class = null;
	

	function __construct($settings = array()) {
		foreach($settings as $property => $value) $this->{$property} = $value;
	}

	// Add a field into this Administration items field set
	function addField($field) {
		$field->fieldSet = $this;
		$this->fields[$field->name] = $field;
	}
	
	/*
	not working T_T
	function deleteField ($fieldName) {
		$tempFields = $this->fields;
		
		
		$this->fields = array();
		
		foreach($tempFields as $field) {			
			if ($field->name != $fieldName) {
				$this->addField($field);							
			}
		}

	}*/
	function addCustomizedFields(&$fieldsArray, $prefix = 'F', $namePrefix = '') {
		//$field->fieldSet = $this;

		foreach($fieldsArray as $fieldDef) {
		
			// Param all the settings we might have
			ss_paramKey($fieldDef,'name','Unknown');
			ss_paramKey($fieldDef,'type','Unknown');
			ss_paramKey($fieldDef,'required',0);
			ss_paramKey($fieldDef,'prefixed',0);
			ss_paramKey($fieldDef,'size','');
			ss_paramKey($fieldDef,'options',array());
			ss_paramKey($fieldDef,'defaultValue','');
			ss_paramKey($fieldDef,'uuid','');
			ss_paramKey($fieldDef,'comments','');
            if(ss_optionExists('Fieldset Force Unique Option')) {
			    ss_paramKey($fieldDef,'unique',0);
            }

			if ($fieldDef['type'] != 'Comment') {
				
				// Assign settings that are the same for each field
				$fieldSettings = array(
					'name'			=>	$prefix.$fieldDef['uuid'],
					'displayName'	=>	$namePrefix.$fieldDef['name'],
					'required'		=>	$fieldDef['required'],
					'defaultValue'	=>	$fieldDef['defaultValue'],
					'note'			=>	$fieldDef['comments'],
				);
                if(ss_optionExists('Fieldset Force Unique Option')) {
    			    $fieldSettings['unique'] = $fieldDef['unique'];
                }
				$processOk = true;
				
				if (strtolower(ListFirst($fieldDef['type'],'_')) == 'datacollectionfield' || strtolower(ListFirst($fieldDef['type'],'_')) == 'datacollectionmultifield') {					
					$assetID = ListLast($fieldDef['type'],'_');
					$fieldSettings['assetID'] = $assetID;
					
					if (strtolower(ListFirst($fieldDef['type'],'_')) == 'datacollectionmultifield') {
						$fieldSettings['multi'] = true;
					}
					$Q_DataCollection = getRow("SELECT * FROM assets WHERE as_id = ".$assetID.".");
					$cereal = unserialize($Q_DataCollection['as_serialized']); 							
					ss_paramKey($cereal, "AST_DATABASE_FIELDS", '');							
					ss_paramKey($cereal, "AST_DATABASE_SUBPAGE_CONTENT", '');												
					if (strlen($cereal['AST_DATABASE_FIELDS'])) {
						$dataFieldsArray = unserialize($cereal['AST_DATABASE_FIELDS']);
					} else {
						$dataFieldsArray = array();					
					}

									
					if (count($dataFieldsArray)) {						
						$Q_Options = query("
								SELECT DaCoID, DaCo{$dataFieldsArray[0]['uuid']} 
								FROM DataCollection_$assetID 
								WHERE DaCo{$dataFieldsArray[0]['uuid']} IS NOT NULL");
						
						
						$values = array();
						while($option = $Q_Options->fetchRow()) {
							$values[$option["DaCo{$dataFieldsArray[0]['uuid']}"]] = $option['DaCoID'];
						}												
						$fieldSettings['options'] = $values;
					}
					$processOk = false;
					$fieldDef['type'] = "DataCollectionField";
					$fieldSettings['dataFields'] = $dataFieldsArray;
					$fieldSettings['detailTemplate'] = $cereal['AST_DATABASE_SUBPAGE_CONTENT'];				
				}
				if ($processOk) {
					// Assign the 'size' and 'options' values as required for each field type
					switch ($fieldDef['type']) {

						case 'NameField':
							if (strlen($fieldDef['size'])) {
								$fieldSettings['size'] = floor($fieldDef['size']/2);
							}
							if (strlen($fieldSettings['defaultValue'])) {
								$temp = explode(' ',$fieldSettings['defaultValue']);
								$fieldSettings['defaultValue'] = array(
									'first_name'	=>	$temp[0],
									'last_name'	=>	$temp[1],
								);
							} else {
								$fieldSettings['defaultValue'] = array(
									'first_name'	=>	'',
									'last_name'	=>	'',
								);
							}

							if ($fieldSettings['name'] == 'us_name' and ss_optionExists('Required User Name')) {
								$fieldSettings['required'] = true;
							}
							break;
							

						case 'IntegerField':
						case 'TextField':
						case 'EmailField':
							if ($fieldDef['uuid'] == 'email' AND $prefix == 'us_') {
								// some sites dont use email addresses
								
								if (ss_optionExists('User Email Not Required Nor Unique')) {
									$fieldSettings['required'] = false;	
									$fieldSettings['unique'] = false;	
								} else {									
									$fieldSettings['unique'] = true;	
								}
								if (array_key_exists('unique', $fieldDef)) {
									$fieldSettings['unique'] = $fieldDef['unique'];
								}
							}
							if (strlen($fieldDef['size'])) {
								$fieldSettings['size'] = $fieldDef['size'];	
							}
							break;
							
						case 'MemoField':
							$fieldSettings['rows'] = 5;
							if (strlen($fieldDef['size'])) {
								$fieldSettings['cols'] = $fieldDef['size'];	
							}
							break;
						case 'HtmlMemoField2':						
							$fieldSettings['height'] = 200;	
							if (strlen($fieldDef['size'])) {
								$fieldSettings['height'] = $fieldDef['size'];	
							}
							break;
							
						case 'PasswordField':
							$fieldSettings['verify'] = true;							
							if (strlen($fieldDef['size'])) {
								$fieldSettings['size'] = $fieldDef['size'];	
							}

							if ($fieldSettings['name'] == 'us_password' and ss_optionExists('Required User Password')) {
								$fieldSettings['required'] = true;
							}
							break;
						case 'LayoutField':
							$ListLayouts = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Layouts.txt')),Chr(10));
							$layouts = array();
							foreach ($ListLayouts as $aLayout) {
								$index = ListLast($aLayout,":");
								$layouts["$index"] = ListFirst($aLayout,":");		
							}
							$fieldSettings['options']	= $layouts;
							$fieldDef['type'] = "SelectFromArrayField";
							break;
						case 'SelectFromArrayField':															
							$values = array();
							
							if (is_array($fieldDef['options'])) {
								foreach ($fieldDef['options'] as $option) {
									$values[$option['name']] = $option['uuid'];
								}						
							}
							$fieldSettings['options']	= $values;
							break;	
						case 'RadioFromArrayField':															
							$values = array();						
							if (is_array($fieldDef['options'])) {
								foreach ($fieldDef['options'] as $option) {
									$values[$option['name']] = $option['uuid'];
									
									if ($option['name'] == $fieldDef['defaultValue']) {
										$fieldSettings['defaultValue'] = $option['uuid'];
									}
								}						
							}
												
							$cols = $fieldDef['size'];
							if (!strlen($cols)) $cols = 1;
							$fieldSettings['options']	= $values;
							$fieldSettings['columns']	= $cols;
							
							break;	
								
                        case 'RadioWithOtherFromArrayField':
                            $values = array();
                            if (is_array($fieldDef['options'])) {
                                    foreach ($fieldDef['options'] as $option) {
                                            $values[$option['name']] = $option['uuid'];

                                            if ($option['name'] == $fieldDef['defaultValue']) {
                                                    $fieldSettings['defaultValue'] = $option['uuid'];
                                            }
                                    }
                            }

                            $cols = $fieldDef['size'];
                            if (!strlen($cols)) $cols = 1;
                            $fieldSettings['options']       = $values;
                            $fieldSettings['columns']       = $cols;

                            break;

						case 'MultiSelectFromArrayField':
							$values = array();		
							
							foreach ($fieldDef['options'] as $option) {
								$values[$option['name']] = $option['uuid'];
							}						
							$fieldSettings['options']	= $values;
							
							break;	
						case 'MultiCheckFromArrayField':															
							$values = array();												
							foreach ($fieldDef['options'] as $option) {
								$values[$option['name']] = $option['uuid'];
							}						
							$fieldSettings['options']	= $values;
							
							$cols = $fieldDef['size'];
							if (!strlen($cols)) $cols = 1;						
							$fieldSettings['columns'] = $cols;
							break;							
						case 'ProductOptionsField':															
							$values = array();
							
							$fieldSettings['linkQueryAction']	= "SelectFieldOptionsAdministration.Query";						
							$fieldSettings['linkQueryValueField']	= "sfo_uuid";						
							$fieldSettings['linkQueryDisplayField']	= "sfo_value";						
							
							$fieldSettings['linkTableName']	= "ShopSystem_ProductOptions";						
							$fieldSettings['linkTableOurKey']	= "PrOpProductLink";
							$fieldSettings['linkTableTheirKey']	= "PrOpUUID";
							$fieldSettings['linkQueryParameters']	= array('FilterSQL'=>'AND sfo_parent_uuid LIKE \''.$fieldDef['uuid'].'\'');
											
							break;		
						case 'MonthlyScheduleField':
							$values = array();
							
							foreach ($fieldDef['options'] as $option) {
								$values[$option['name']] = $option['uuid'];
							}						
							$fieldSettings['options']	= $values;
							break;	
						case 'DateField':																					
							$fieldSettings['showCalendar']	= true;
							$fieldSettings['size']	= $fieldDef['size'];
							break;	
							
						case 'PopupUniqueImageField':
							//ss_DumpVarDie($this);
							if (!strlen($this->assetLink)) {
								if (ss_isItUs()) ss_DumpVarDie($this, 'No AssetLink FieldSet.php');
								die("sorry you cannot use this field. Please contact the developer.(FieldSet.php)");
							}

							$values = array();
							$directroy = ss_secretStoreForAsset($this->assetLink,$fieldDef['uuid']);
							$fieldSettings['directory'] = $directroy;
							//$fieldSettings['preview'] =	false;

							break;
						case 'FileField':
							if (!strlen($this->assetLink)) {
                                //yes this is a hack
                                $this->assetLink = 523;
                            }

							$values = array();
							$directroy = ss_secretStoreForAsset($this->assetLink,$fieldDef['uuid']);
							$fieldSettings['directory'] = $directroy;
                            $fieldSettings['secure'] = 'true';
							//$fieldSettings['preview'] =	false;

							break;
                        case 'AssetTreeField':
							$fieldSettings['treeProperty'] = array(
												  'openerFormName'=>'adminForm',
												  'treeDescription'=>'',
												  'treeAssetRootID'=>'1',
												  'treeStyle'=>'width:260;height:300; overflow:auto;border:solid black 1px;',
												  'includeChildrenOf'=>array(),
												  'excludeAssets'=>array(),
												  'excludeChildrenOf'=>array(),
												  'appearsInMenus'=>'No',);
							$fieldSettings['treePopWindowProperty'] = 'width=300,height=350,scrollbar=1';
							$fieldSettings['onFocus'] = 'onFocus="this.form.'.$prefix.$fieldDef['uuid'].'.select()"';
							break;

						case 'NZRegionTownField':
							$fieldSettings['parentName'] = 'Region';
							$fieldDef['type'] = "ParentChildrenField";
							$fieldSettings['showTextForNoChildren'] = true;
							$fieldSettings['showAlwaysParentAnyDesc'] = true;
							$fieldSettings['parentAction']	= "NZRegionAdministration.Query";
							$fieldSettings['parentValueField']	= "NZRID";
							$fieldSettings['parentDisplayField']	= "NZRName";
							$fieldSettings['parentAnyDesc']	= "Please Select Region";
							$fieldSettings['parentQueryParams']=array('FilterSQL' => '');
							$fieldSettings['childName'] = 'Town';
							$fieldSettings['childAction']	= "NZTownsAdministration.Query";
							$fieldSettings['childParentKey']	= "NZTRegionLink";
							$fieldSettings['childValueField']	= "NZTID";
							$fieldSettings['childDisplayField']	= array('NZTName');
							$fieldSettings['childQueryParams']	= array('ReturnAll'=>'yes',);
							$fieldSettings['childDisplayValueField'] = $fieldSettings['childDisplayField'];

                            break;
						case 'CountryStateField':
							if( $namePrefix == 'Shipping '
                               and !array_key_exists( 'EditingOrder', $_SESSION['Shop'] ) )
							{
								$fieldSettings['parentName'] = 'Country';
								//$fieldSettings['parentAnyDesc']	= "Please Select Country";
								if( array_key_exists( 'ForceCountry', $_SESSION ) )
								{
									$fieldSettings['parentAnyDesc']	= "";
									$fieldSettings['parentQueryParams']=array( 'FilterSQL' => "AND (cn_two_code = '{$_SESSION['ForceCountry']['cn_two_code']}')");
								}
								else
								{
									$fieldSettings['parentAnyDesc']	= "Country chosen at top left hand side";
									$fieldSettings['parentQueryParams']=array( 'FilterSQL' => 'AND (cn_disabled IS NULL OR cn_disabled = 0) AND (cn_restrict_shipping IS NULL OR cn_restrict_shipping = 0)');
								}

								$fieldDef['type'] = "ParentChildrenField";
								$fieldSettings['showTextForNoChildren'] = true;
								$fieldSettings['showAlwaysParentAnyDesc'] = true;

								$fieldSettings['parentAction']	= "CountryAdministration.Query";
								$fieldSettings['parentValueField']	= "cn_id";
								$fieldSettings['parentDisplayField']	= "cn_name";

								if( !array_key_exists( 'ForceCountry', $_SESSION )
								 || !is_array( $_SESSION['ForceCountry'] ) )
									$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_two_code = '".ss_getCountry(NULL, 'cn_two_code')."'");

								$fieldSettings['selectedValue'] = $_SESSION['ForceCountry']['cn_id'];
								$fieldSettings['onchangeFunction'] = "document.forms.CheckoutForm.action='Shop_System/Service/Checkout/Do_Service/Reload';document.forms.CheckoutForm.reload=1;document.forms.CheckoutForm.submit();";

								$fieldSettings['childName'] = 'Region/State';
								$fieldSettings['childAction']	= "StatesAdministration.Query";
								$fieldSettings['childParentKey']	= "StCountryLink";						
								$fieldSettings['childValueField']	= "sts_id";
								$fieldSettings['childDisplayField']	= array('StName','StCode');	
//								$fieldSettings['childQueryParams']	= array('ReturnAll'=>'yes', 'FilterSQL' => ' AND StCountryLink = '.$country['cn_id'] );
								$fieldSettings['childQueryParams']	= array('ReturnAll'=>'yes',);
								if (ss_optionExists('Show State Code')) {
									$fieldSettings['childDisplayValueField'] = 'StCode';
								} else {
									$fieldSettings['childDisplayValueField'] = $fieldSettings['childDisplayField'];
								}

								if (ss_optionExists('Show Country Note')) {
									$fieldSettings['showParentNote'] = true;
									$fieldSettings['showParentNoteField'] = 'cn_note';
								}
							}
							else
							{
								$fieldSettings['parentName'] = 'Country';
								$fieldDef['type'] = "ParentChildrenField";
								$fieldSettings['showTextForNoChildren'] = true;
								$fieldSettings['showAlwaysParentAnyDesc'] = true;
								$fieldSettings['selectedValue'] = $_SESSION['ForceCountry']['cn_id'];

								$fieldSettings['parentAction']	= "CountryAdministration.Query";
								$fieldSettings['parentValueField']	= "cn_id";
								$fieldSettings['parentDisplayField']	= "cn_name";
								$fieldSettings['parentAnyDesc']	= "Please Select Country";
								if( ss_getUserID() > 0 )
								{
									$fieldSettings['parentQueryParams']=array('FilterSQL' => 'AND (cn_disabled IS NULL OR cn_disabled = 0)');

									if( $namePrefix == 'Shipping ' )
										$fieldSettings['parentQueryParams']=array(
											'FilterSQL' => 'AND (cn_disabled IS NULL OR cn_disabled = 0) AND (cn_restrict_shipping IS NULL OR cn_restrict_shipping = 0)');
								}
								else
								{
									if( array_key_exists( 'ForceCountry', $_SESSION ) )
										$fieldSettings['parentQueryParams']=array( 'FilterSQL' => "AND (cn_two_code = '{$_SESSION['ForceCountry']['cn_two_code']}')");
									else
										$fieldSettings['parentQueryParams']=array( 'FilterSQL' => 'AND (cn_disabled IS NULL OR cn_disabled = 0) AND (cn_restrict_shipping IS NULL OR cn_restrict_shipping = 0)');
								}
								$fieldSettings['childName'] = 'Region/State';
								$fieldSettings['childAction']	= "StatesAdministration.Query";
								$fieldSettings['childParentKey']	= "StCountryLink";						
								$fieldSettings['childValueField']	= "sts_id";
								$fieldSettings['childDisplayField']	= array('StName','StCode');	
								$fieldSettings['childQueryParams']	= array('ReturnAll'=>'yes',);
								if (ss_optionExists('Show State Code')) {
									$fieldSettings['childDisplayValueField'] = 'StCode';
								} else {
									$fieldSettings['childDisplayValueField'] = $fieldSettings['childDisplayField'];
								}

								if (ss_optionExists('Show Country Note')) {
									$fieldSettings['showParentNote'] = true;
									$fieldSettings['showParentNoteField'] = 'cn_note';
								}
							}
                            break;
                    }
				}
				// Add the field to the field set								
				$this->addField(new $fieldDef['type']($fieldSettings));
				
			}
		}

	}
	
	function getField($field) {
		return $this->fields[$field];	
	}
	
	function getFieldInputHTML($field,$verify = FALSE) {
		return $this->fields[$field]->display($verify,$this->formName);
	}
	
	function displayField($field,$verify = FALSE) {
		print $this->getFieldInputHTML($field,$verify);
	}
	
	function getFieldDisplayValue($field) {
		return $this->fields[$field]->displayValue($this->fields[$field]->value);
	}
	
	function getFieldValue($field) {
		return $this->fields[$field]->value;	
	}
	
	function forceRequired($fieldNamesArray) {
		foreach ($fieldNamesArray as $fieldName) {
			if (array_key_exists($fieldName,$this->fields)) {
				$this->fields[$fieldName]->required = true;	
			}
		}	
	}
	
	function validate() {
		return include('ValidateQuery.php');
	}
	
	function update() {
		require( 'UpdateRecord.php' );
		return include('UpdateAction.php');
	}
	
	function insert() {
		
		require( 'InsertRecord.php' );
		return include('InsertAction.php');
	}
	
	function loadFieldValuesFromDB(&$row) {

	
		if ($row === NULL) {
			$row = getRow("
				SELECT * FROM $this->tableName 
				WHERE $this->tablePrimaryKey = '".escape($this->primaryKey)."'
			");
		}				
		//ss_DumpVarDie($this);
		if ($row !== null) {
			foreach ($this->fields as $field) {
				
				if (array_key_exists($field->name,$row)) {
					$this->fields[$field->name]->setValues($row[$field->name], $row[$field->name], "DB", $this->primaryKey);				
				} else {
					// This is for multilookup fields, their field->name
					// wont be in the row from the query
					//print("{$field->name}<BR>");
					$this->fields[$field->name]->setValues(NULL, NULL, "DB", $this->primaryKey);
				}

                if (array_key_exists("{$field->name}_otherValue",$row)) {
                    $this->fields[$field->name]->setOtherValue( $row["{$field->name}_otherValue"], "DB", $this->primaryKey);
                }

			}
		}
	}
	
	function loadFieldValuesFromForm(&$row,$loadDefaults = FALSE) {
		
		//ss_log_message_r( "loadFieldValuesFromForm this:", $this );
		//ss_log_message_r( "loadFieldValuesFromForm row:", $row );
		foreach ($this->fields as $field) {
			
			if (array_key_exists($field->name,$row)) {
				if (array_key_exists("{$field->name}_V",$row)) {
					$this->fields[$field->name]->setValues( $row[$field->name], $row["{$field->name}_V"], "FORM");
				} else {					
//					if ('AST_FLASH_ATTRIBUTES' == $field->name or 'ExtendedOptions' == $field->name ) ss_DumpVarDie($row[$field->name], $field->name);					
					$this->fields[$field->name]->setValues( $row[$field->name], NULL, "FORM");
				}
                if (array_key_exists("{$field->name}_otherValue",$row))
                    $this->fields[$field->name]->setOtherValue( $row["{$field->name}_otherValue"], "FORM");
			}
			
			// If this is the first time we're displaying the form (i.e. not from errors)
			if ($loadDefaults && $field->name ) {					
				$this->fields[$field->name]->useDefaultValue();		
			}			
		}
		//ss_DumpVarDie($this);
	}
	
	function loadFieldValuesFromSpecificArray($fields) {

		foreach ($this->fields as $field) {
			if (array_key_exists($field->name, $fields)) {				
				$this->fields[$field->name]->setValues($fields[$field->name], NULL, "FORM");				
			}		
		}		
	}

	// If this is being displayed for an edit, and it's not a reedit of the 
	// form after a failure we need to default the values from the database
	// (note that if primary key is given, we determine this to be an edit,
	// if it's not, then it's not an edit.
	function isEdit(&$FORM) {
//		ss_DumpVar( $this );
//		ss_DumpVarDie( $FORM );
		return array_key_exists($this->tablePrimaryKey,$FORM) && !array_key_exists('DoAction',$FORM);
	}
	
	function loadFieldValues(&$FORM,$DB = NULL,$loadFromDB = NULL,$errors = NULL) {
		
		// If not specified, then let the system decide whether to load from db or form
		if ($loadFromDB == NULL) $loadFromDB = $this->isEdit($FORM);

		if ($loadFromDB) {
			$this->loadFieldValuesFromDB($DB);	
		} else {
			$this->loadFieldValuesFromForm($FORM,($errors !== NULL and count($errors) == 0));	
		}	
	}
	
	// Get an array of all field values.. this is useful if we want to serialize all
	// the values submitted by the user
	function getFieldValuesArray() {
		$fieldValues = array();
		foreach ($this->fields as $field) {
			$fieldValues[$field->name] = $field->value;
		}
		return $fieldValues;
	}
	
	function getFieldNamesArray() {
		$fieldNames = array();
		foreach ($this->fields as $name => $field) {
			array_push($fieldNames,$name);
		}
		return $fieldNames;
	}
	
}

?>
