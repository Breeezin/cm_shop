<?php

	$errors = array();
	$justDid = "";
	$needReload = false;
	$closeTab = false;
	$assetNameChanged = false;
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		
		if (array_key_exists('Revert',$this->ATTRIBUTES)) {
			$Q_Revert = query("
				UPDATE assets
				SET as_pending_serialized = NULL
				WHERE as_id = ".$this->getID()."
			");
			$justDid = "Revert completed";
			$needReload = true;

		} else { 
		
			// See if we need to reload the asset tree or not.. if the asset name has changed
			$oldAssetName = getRow("
				SELECT as_name FROM assets
				WHERE as_id = ".$this->getID()."
			");
			if (array_key_exists('as_name',$oldAssetName) and array_key_exists('as_name',$this->fieldSet->fields)) {
				if ($this->fieldSet->fields['as_name']->value != $oldAssetName['as_name']) $assetNameChanged = true;
			}
			
			
			// Validate the data for each field
			// Set up the error array
			//ss_DumpVarDie($this);
			if (array_key_exists($this->fieldSet->tablePrimaryKey,$this->ATTRIBUTES)) {
				$this->fieldSet->primaryKey = $this->ATTRIBUTES[$this->fieldSet->tablePrimaryKey];
			}
			
			// Validate each field and record any errors reported
			$errors = array_merge($errors,$this->fieldSet->validate());
			$errors = array_merge($errors,$assetType->fieldSet->validate());
			$errors = array_merge($errors,$this->layoutFieldSet->validate());
			
			// Update if no errors validating data
			if (count($errors) == 0) {
			
				// hacked in by rex
				$Q_lang = query( "select * from languages where lg_id > 0" );
				while( $lrow = $Q_lang->fetchRow() )
				{
					if( array_key_exists( 'as_search_keywords'.$lrow['lg_id'], $this->ATTRIBUTES )
					 && array_key_exists( 'as_search_description'.$lrow['lg_id'], $this->ATTRIBUTES ) )
					{
						$Qu = query( "update asset_descriptions set ad_metadata_keywords = '".addslashes( $this->ATTRIBUTES['as_search_keywords'.$lrow['lg_id']] )."',
											ad_metadata_description = '".addslashes( $this->ATTRIBUTES['as_search_description'.$lrow['lg_id']] )."',
											ad_window_title = '".addslashes( $this->ATTRIBUTES['AssetWindowTitle'.$lrow['lg_id']] )."'
										where ad_language = ".$lrow['lg_id']." and ad_as_id = ".$this->getID() );
						if( affectedRows() == 0 )
						{
							$Qi = query( "insert into  asset_descriptions  (ad_as_id, ad_language, ad_metadata_keywords, ad_metadata_description, ad_window_title)
								values (".$this->getID().", ".$lrow['lg_id'].", '".addslashes( $this->ATTRIBUTES['as_search_keywords'.$lrow['lg_id']] )."','"
											.addslashes( $this->ATTRIBUTES['as_search_description'.$lrow['lg_id']] )."','"
											.addslashes( $this->ATTRIBUTES['AssetWindowTitle'.$lrow['lg_id']] )."')" );
						}
					}

				}
				// Construct the SQL
				$insertFields = '';
				foreach ($this->fieldSet->fields as $field) {
					$insertFields .= ', '.$field->updateSQL();
				}
				//ss_DumpVarDie($assetType);
				//ss_DumpVarDie($this);
				// Construct the as_serialized field from the assetType fieldset values
				$assetTypeFieldsSerialized = serialize($assetType->fieldSet->getFieldValuesArray());
				$layoutFieldsSerialized = serialize($this->layoutFieldSet->getFieldValuesArray());
				
				//if (ss_HasPermission('IsDeployer')) {
				if (false) {
					$securityStuff = ", as_can_use_default = {$this->ATTRIBUTES['Perm_CanUse_Default']}
						, as_can_admin_default = {$this->ATTRIBUTES['Perm_CanAdminister_Default']}
						, as_child_can_use_default = {$this->ATTRIBUTES['Perm_ChildCanUse_Default']}
						, as_child_can_admin_default = {$this->ATTRIBUTES['Perm_ChildCanAdminister_Default']}";
				} else {
					$securityStuff = '';	
				}
				
				
				$cerealField = 'as_serialized';
				// If we're using version control and this is a Page asset,
				// then just update the pending cereal instead of the main one.
				if ($this->fields['as_type'] == 'Page') {
					if (ss_optionExists('Review Process')) {
						$cerealField = 'as_pending_serialized';	
						
						// Author stuff
						$securityStuff .= ', AssetAuthor = '.ss_getUserID();
					}			
				}

                $assetAuthor = '';
                if (ss_OptionExists('Newsletter Signatures') and isset($this->ATTRIBUTES['AssetAuthor'])){
                    $assetAuthor = ', AssetAuthor = '.$this->ATTRIBUTES['AssetAuthor'];
                }

				if ($cerealField == 'as_serialized' and array_key_exists('AST_PAGE_PAGECONTENT',$assetType->fieldSet->fields)) {		
					$content = strip_tags($assetType->fieldSet->fields['AST_PAGE_PAGECONTENT']->value);
					$insertFields .= ", as_search_content = '".escape($content)."'";
				}
				
				if ($cerealField == 'as_serialized' and array_key_exists('AST_FOLDER_CONTENT',$assetType->fieldSet->fields)) {
					$content = strip_tags($assetType->fieldSet->fields['AST_FOLDER_CONTENT']->value);
					$insertFields .= ", as_search_content = '".escape($content)."'";
				}

				// Update the fields
				$result = query("
					UPDATE {$this->fieldSet->tableName}
					SET {$this->fieldSet->tablePrimaryKey} = {$this->fieldSet->tablePrimaryKey},
						$cerealField = '".escape($assetTypeFieldsSerialized)."',
						as_layout_serialized = '".escape($layoutFieldsSerialized)."'
						$insertFields
				
						$securityStuff		
				        $assetAuthor
						,as_last_modified = NOW()
					WHERE {$this->fieldSet->tablePrimaryKey} = {$this->fieldSet->primaryKey}
				");
		
				// Now handle the special fields.. e.g MultiSelectField
				foreach ($this->fieldSet->fields as $field) {
					$field->specialUpdate();
				}
				
				$needReload = true;
				
				// Update the asset sort order fields of the sub assets
				/*$ord = 0;
				foreach (ListToArray($this->ATTRIBUTES['subAssetSortOrder']) as $subAssetID) {
					$Q_OrderUpdate = query("
						UPDATE assets
						SET as_sort_order = $ord
						WHERE as_id = $subAssetID
					");
					$ord++;	
				}*/
				
				// Now update the security information
				if (ss_HasPermission('IsDeployer')) {
					$Q_Groups = array();
					$Q_Groups[0] = query("SELECT * FROM user_groups	WHERE ug_mailing_list IS NULL OR ug_mailing_list = 0 ORDER BY ug_name");
					$Q_Groups[1] = query("SELECT * FROM user_groups	WHERE ug_mailing_list = 1	ORDER BY ug_name");
		
					for ($groupIndex=0; $groupIndex<2; $groupIndex++) {
						while ($row = $Q_Groups[$groupIndex]->fetchRow()) {
							if (array_key_exists("Perm_CanUse_{$row['ug_id']}",$this->ATTRIBUTES)
							and array_key_exists("Perm_CanAdminister_{$row['ug_id']}",$this->ATTRIBUTES)
							and array_key_exists("Perm_ChildCanUse_{$row['ug_id']}",$this->ATTRIBUTES)
							and array_key_exists("Perm_ChildCanAdminister_{$row['ug_id']}",$this->ATTRIBUTES) ) {
								
								$assetID = $this->getID();
								
								// Delete any existing permissions
								$result = query("
									DELETE FROM asset_user_groups 
									WHERE aug_as_id = $assetID
									AND aug_ug_id = {$row['ug_id']}
								");
								
								// Find the values for all three 3 State fields
								$useVal = strlen($this->ATTRIBUTES["Perm_CanUse_{$row['ug_id']}"])?$this->ATTRIBUTES["Perm_CanUse_{$row['ug_id']}"]:"NULL";
								$adminVal = strlen($this->ATTRIBUTES["Perm_CanAdminister_{$row['ug_id']}"])?$this->ATTRIBUTES["Perm_CanAdminister_{$row['ug_id']}"]:"NULL";
								$childUseVal = strlen($this->ATTRIBUTES["Perm_ChildCanUse_{$row['ug_id']}"])?$this->ATTRIBUTES["Perm_ChildCanUse_{$row['ug_id']}"]:"NULL";
								$childAdminVal = strlen($this->ATTRIBUTES["Perm_ChildCanAdminister_{$row['ug_id']}"])?$this->ATTRIBUTES["Perm_ChildCanAdminister_{$row['ug_id']}"]:"NULL";
								
								// Write new permissions to database
								$result = query("
									INSERT INTO asset_user_groups
										(aug_as_id, aug_ug_id, aug_can_use, aug_can_administer, aug_child_can_use, aug_child_can_administer)
									VALUES
										($assetID, {$row['ug_id']}, $useVal, $adminVal, $childUseVal, $childAdminVal)
								");
								
							}
						}
					}
				}
			
				$justDid = "Save completed";
				$this->param('Save','');
	//			ss_DumpVarDie($this->ATTRIBUTES);
				if (($this->ATTRIBUTES['Save'] == 'Propagate')) {
					$temp = new Request('Security.PropagateAssetPermissions',array(
						'as_id'	=>	$this->ATTRIBUTES['as_id'],
					));			
					$justDid = "Propagate&nbsp;and&nbsp;Save&nbsp;completed";
				}
				
				// proccess save action since each asset type can different save(edit) action.
				$assetType->processSave($this);

			} else {
				$assetNameChanged = false;
						
			} 
			
			if (array_key_exists('RequestReview',$this->ATTRIBUTES)) {
				
				$Q_SendReview = query("
					UPDATE assets
					SET AssetReview = 1
					WHERE as_id = ".$this->getID()."
				");
				$justDid = "Saved&nbsp;and&nbsp;sent&nbsp;for&nbsp;review";

				// Get some details for the email
				$data = array();
				$data['as_type'] = strtolower($this->fields['as_type']);
				$temp = new Request('Asset.PathFromID',array('as_id'	=>	$this->getID()));
				$data['AssetPath'] = $temp->value;		
				$author = getRow("SELECT * FROM users WHERE us_id = ".ss_getUserID()." ");
				$fromAddress = $author['us_email'];
				$data['Author'] = $author['us_first_name'].' '.$author['us_last_name'];
				$reviewer = getRow("SELECT * FROM users WHERE us_id = ".safe($this->ATTRIBUTES['AssetReviewer'])." ");
				$toAddress = $reviewer['us_email'];
				$data['Reviewer'] = $reviewer['us_first_name'];
				
				require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
				$mailer = new htmlMimeMail();
				$mailer->setFrom($fromAddress);
				$mailer->setSubject('New '.$data['as_type'].' for you to review');
				$htmlEmail = $this->processTemplate('EmailReviewRequested',$data);
				$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
				$mailer->send(array($toAddress));
								
			}
			
			if (array_key_exists('RequestDelete',$this->ATTRIBUTES)) {
				$Q_SendReview = query("
					UPDATE assets
					SET AssetReview = 1,
						as_pending_serialized = 'Delete'
					WHERE as_id = ".$this->getID()."
				");
				$justDid = "Delete&nbsp;Requested";
				
				// Get some details for the email
				$data = array();
				$data['as_type'] = strtolower($this->fields['as_type']);
				$temp = new Request('Asset.PathFromID',array('as_id'	=>	$this->getID()));
				$data['AssetPath'] = $temp->value;		
				$author = getRow("SELECT * FROM users WHERE us_id = ".ss_getUserID()." ");
				$fromAddress = $author['us_email'];
				$data['Author'] = $author['us_first_name'].' '.$author['us_last_name'];
				$reviewer = getRow("SELECT * FROM users WHERE us_id = ".safe($this->ATTRIBUTES['AssetReviewer'])." ");
				$toAddress = $reviewer['us_email'];
				$data['Reviewer'] = $reviewer['us_first_name'];
				
				require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
				$mailer = new htmlMimeMail();
				$mailer->setFrom($fromAddress);
				$mailer->setSubject('New '.$data['as_type'].' for you to review');
				$htmlEmail = $this->processTemplate('EmailDeleteRequested',$data);
				$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
				$mailer->send(array($toAddress));
				
			}			
			
			if (array_key_exists('export_x',$this->ATTRIBUTES)) {
				$justDid = "Save and export completed";
				$result = new Request('Export.Page',array(
					'as_id'	=>	$this->getID(),
				));	
			}
			if (array_key_exists('import_x',$this->ATTRIBUTES)) {
				$justDid = "Import completed";
				$result = new Request('Import.Page',array(
					'as_id'	=>	$this->getID(),
				));	
				$needReload = true;
			}
			if (array_key_exists('subexport_x',$this->ATTRIBUTES)) {
				$justDid = "Save and export completed";
				$result = new Request('Export.Page',array(
					'as_id'	=>	$this->getID(),
					'Type'		=>	'Sub',
				));	
			}
			if (array_key_exists('subimport_x',$this->ATTRIBUTES)) {
				$justDid = "SubContent Import completed";
				$result = new Request('Import.Page',array(
					'as_id'	=>	$this->getID(),
					'Type'		=>	'Sub',
				));	
				$needReload = true;
			}
			
			if (array_key_exists('SaveCloseButton_x',$this->ATTRIBUTES)) {
				if (count($errors) == 0) {
					// Only reload if we could save...
					$needReload = false;
					$closeTab = true;
				}
			}
		}
		
		if ($needReload) {
			$this->loadAsset(true);
			
			// Get an object for the correct asset type and define the fields
			$className = $this->fields['as_type'].'Asset';
			requireOnceClass($className);
			$assetType = new $className;
			$assetType->defineFields($this);
			
			// Load the fields with values from the DB which have just been updated
			$this->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->fields,true);
			$assetType->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->cereal,true);
			$this->layoutFieldSet->loadFieldValues($this->ATTRIBUTES,$this->layout,true);
		}
				
	}
		
?>
