<?php
requireOnceClass('Administration');
class UsersAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('Users');
	}

	function update() {
//		ss_log_message( "users:update" );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->ATTRIBUTES );
		/*
			vgl5:2015-09-18 10:22:52:Array
			(
				[act] => UsersAdministration.Edit
				[us_name] => Array
					(
						[first_name] => Bogo
						[last_name] => Mips
					)

				[us_0_B4BF] => 
				[us_email] => im@admin.com
				[us_password] => fred2
				[UsPassword_V] => fred2
				[us_0_50A1] => 129837 Short St
				[us_0_50A2] => city
				[us_0_50A4] => 840&|&select&|&19
				[Us0_50A4_Parent] => 840
				[Us0_50A4_ChildText] => 
				[Us0_50A4_ChildSelect] => 19
				[us_0_B4C0] => 98789787
				[us_0_B4C1] => 987897987978
				[us_html_email] => 1
				[user_groups] => Array
					(
						[0] => 2
						[1] => 7
					)

				[us_no_spam] => 1
				[us_notes] => 
				[us_bl_id] => -1
				[us_payment_gateway] => NULL
				[us_temporary_tracking] => 0
				[us_discount] => 0
				[us_discount_expires] => 2000-11-29
				[us_account_credit] => 0.01
				[us_credit_from_gateway_option] => 25
				[us_no_chargeback_count] => 0
				[us_do_not_track] => 'true'
				[us_do_not_address_check] => 'false'
				[BreadCrumbs] => Administration
				[DoAction] => Submit
				[BackURL] => 
				[us_id] => 39529
				[as_id] => 2
				[REQUEST_URI] => /index.php?act=UsersAdministration.Edit
			)
		*/

		ss_audit( 'update', 'users', $this->ATTRIBUTES['us_id'],
						"Updating User ID {$this->ATTRIBUTES['us_id']} to Name {$this->ATTRIBUTES['us_name']['first_name']} {$this->ATTRIBUTES['us_name']['last_name']}"
						." Account Credit: {$this->ATTRIBUTES['us_account_credit']} from Gateway Option ID {$this->ATTRIBUTES['us_credit_from_gateway_option']}" );

		ss_audit( 'update', 'users', $this->ATTRIBUTES['us_id'],
						"Updating User ID {$this->ATTRIBUTES['us_id']} to Name {$this->ATTRIBUTES['us_name']['first_name']} {$this->ATTRIBUTES['us_name']['last_name']}"
						." Credit Notes: {$this->ATTRIBUTES['us_credit_notes']}" );

		parent::update();
	}

	function entries() {
		if( ss_adminCapability( ADMIN_EDIT_USERS ) )
			parent::entries();
	}

	function query($params = array()) {
		$result = new Request('Security.Authenticate',array(
			'Permission'	=>	'IsDeployer',
			'LoginOnFail'	=>	'No',
		));
		// This sets the minimum primary key to allow in the query to be 2
		// so it will hide guest, super and innovative media user accounts		
		if ($result->value == TRUE) {
			$query = parent::query($params);
		} else {
			$query = parent::query(array_merge($params,array(
				'Min'	=>	2,
			)));
		}

		if (is_object($query)) {
			$query->addColumn('user_groups');
			$counter = 0;
			while ($row = $query->fetchRow()) {
				$Q_UserGroups = query("
					SELECT ug_name FROM user_user_groups, user_groups
					WHERE uug_us_id = {$row['us_id']}
						AND uug_ug_id = ug_id
				");
				$userGroups = '';
				while ($userGroup = $Q_UserGroups->fetchRow()) {
					if (strlen($userGroups)) $userGroups .= ', ';
					$userGroups .= $userGroup['ug_name'];
				}
				$query->setCell('user_groups',$userGroups,$counter++);
			}
		}
				
		return $query;
	}

	function delete() {
		
		require('DeleteAction.php');	
	}
	function parentDelete() {
		parent::delete();
	}
	function insert() {

		// unbelievably stupid routine elided....
		/*
		startTransaction();
		// Read the old value for primary key
		$config = getRow("
			SELECT * FROM configuration
			WHERE cfg_id = 1
		");
		
		// Find new value
		if ($config['cfg_last_us_id'] !== null) {
			$config['cfg_last_us_id']++;
			$this->primaryKey = $config['cfg_last_us_id'];
		} else {
			$config['cfg_last_us_id'] = newPrimaryKey($this->tableName,$this->tablePrimaryKey);
		}
		
		// Update configuration
		$configUpdate = query("
			UPDATE configuration
			SET cfg_last_us_id = ".safe($config['cfg_last_us_id'])."
			WHERE cfg_id = 1
		");
		commit();
		*/
		
		$ret = parent::insert();

		ss_log_message( "inserted new user id #". (int)$this->primaryKey );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );

		// need to check and if needed set us_temporary_tracking
		ss_log_message( "Country to use for us_temporary_tracking is ".$_SESSION['ForceCountry']['cn_name'] );
		if( $_SESSION['ForceCountry'][ 'cn_shipping_tracking' ] == 'By User' )
			query( "update users set us_temporary_tracking = 1 where us_id = ".((int)$this->primaryKey) );

		return $ret;
	}
	
	function __construct($isAdmin = true,$hidePassword = false) {
			
		
		$fieldsArray = array();	
			
		$Q_UserAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'users'");
		ss_paramKey($Q_UserAsset,'as_serialized',''); 
		
		if (strlen($Q_UserAsset['as_id']) AND strlen($Q_UserAsset['as_serialized'])) {
			$cereal = unserialize($Q_UserAsset['as_serialized']);			
			ss_paramKey($cereal,'AST_USER_FIELDS','');
			if (strlen($cereal['AST_USER_FIELDS'])) {
				$fieldsArray = unserialize($cereal['AST_USER_FIELDS']);
			} else {
				$fieldsArray = array();	
			}
		} else {
			$fieldsArray = array();	
		}

		ss_log_message_r( "UsersAdmin fields are ", $fieldsArray );
		
		// Optionally remove the password field from the fields array
		// This is because some shoppers find the concept of a password difficult -_-'
		if ($hidePassword and ss_optionExists('Shop Checkout Hide Password')) {
			$newFieldsArray = array();
			foreach($fieldsArray as $fieldDef) {
				ss_paramKey($fieldDef,'uuid','');			
				if ($fieldDef['uuid'] != 'password') {
					array_push($newFieldsArray,$fieldDef);
				}
			}
			$fieldsArray = $newFieldsArray;
		}
		
		$tableDisplayFields = array();
		$tableDisplayFieldTitles = array();
		$tableSearchFields = array();
		$orderBys = array();
//		if (ss_optionExists('Show User ID')) { 
			array_push($tableDisplayFields, "us_id");
			array_push($tableDisplayFieldTitles,'ID');
			array_push($tableSearchFields, "us_id");
			$orderBys["us_id"] = "ID";
//		}
		
		
		$specialOrderBys = array();
		$tableSearchFieldsFromOptions = array();
		$specialFieldTypes = ss_getFieldSetTypes(false);
		
		// Decide which fields are going to be displayed in the list page
		foreach($fieldsArray as $fieldDef) {		
			// Param all the settings we might have
			ss_paramKey($fieldDef,'uuid','');			
			ss_paramKey($fieldDef,'name','unknown');									
			ss_paramKey($fieldDef,'type','');									
			ss_paramKey($fieldDef,'AppearInList','no');								
			if ($fieldDef['AppearInList'] == 'yes') {
				if (!($fieldDef['type'] == 'MonthlyScheduleField' and is_array($assetLink)) )
				{
					if (array_key_exists($fieldDef['type'], $specialFieldTypes))
					{
						if ($fieldDef['type'] == "CountryField")
							$tableSearchFieldsFromOptions["us_".$fieldDef['uuid']]
								= array("table"=> "countries", "joinField" =>"cn_three_code", 'displayField' =>  "cn_name", 'groupField' =>  "");
						else
							$tableSearchFieldsFromOptions["us_".$fieldDef['uuid']]
								= array("table"=> "select_field_options", "joinField" =>"sfo_uuid", 'displayField' =>  "sfo_value", 'groupField' =>  "sfo_parent_uuid");
						$specialOrderBys["us_".$fieldDef['uuid']] = $fieldDef['name'];
					}
					else
					{
						if ($fieldDef['uuid'] == 'name')
						{
							array_push($tableSearchFields, "us_first_name");
							array_push($tableSearchFields, "us_last_name");
							$orderBys["us_first_name,us_last_name"] = 'First Name';
							$orderBys["us_last_name,us_first_name"] = 'Last Name';
//							array_push($tableSearchFields, "us_first_name");
//							array_push($tableSearchFields, "us_last_name");
//							$orderBys["us_first_name,us_last_name"] = $fieldDef['name'];
						}
						else
						{
							array_push($tableSearchFields, "us_".$fieldDef['uuid']);
							$orderBys["us_".$fieldDef['uuid']] = $fieldDef['name'];
						}
					}

					if ($fieldDef['uuid'] == 'name') {
						array_push($tableDisplayFields, "us_first_name");
						array_push($tableDisplayFields, "us_last_name");
						array_push($tableDisplayFieldTitles, "First Name");						
						array_push($tableDisplayFieldTitles, "Last Name");						
					} else {
						array_push($tableDisplayFields, "us_".$fieldDef['uuid']);
						array_push($tableDisplayFieldTitles, $fieldDef['name']);
					}
				}
				
			}	
		}	
		
		array_push($tableDisplayFields,'user_groups');
		array_push($tableDisplayFieldTitles,'User Groups');
		if (ss_optionExists('Member Expiry Date')) {
			array_push($tableDisplayFields,'us_activated');
			array_push($tableDisplayFieldTitles,'Expiry Date');
		}

		parent::__construct(array(
			'prefix'					=>	'users',
			'singular'					=>	'User',
			'plural'					=>	'users',
			'assetLink'					=>	2,
			'tableName'					=>	'users',
			'tablePrimaryKey'			=>	'us_id',
			'tableOrderBy'				=>	$orderBys,			
			'tablePrefix'				=>	'us_',			
			'tableSpecialOrderBy'		=>	$specialOrderBys,			
			'tableDisplayFields'		=>	$tableDisplayFields,
			'tableSearchFields'			=>	$tableSearchFields,
			'tableSearchFieldsFromOption'=>	$tableSearchFieldsFromOptions,
			'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,									
		));
		
		$Q_UserGroups = query("
			SELECT * FROM user_groups
			WHERE ug_id > 0
			ORDER BY ug_name
		");
		$this->filterByMulti = array('UserGroup' => array(
			'name'	=>	'UserGroup',
			'displayName'	=>	'User Group',
			'filters'	=>	array(),
		));
		while ($row = $Q_UserGroups->fetchRow()) {
			$this->filterByMulti['UserGroup']['filters'][$row['ug_id']] = array(
				'name'			=>	$row['ug_name'],
				'filterSQL'		=>	'us_id = uug_us_id AND uug_ug_id = '.$row['ug_id'],
				'filterTablesSQL'	=>	'user_user_groups',
			);
		}
		
		if (ss_optionExists('Shop Advanced Ordering')) {
			$this->listManageOptions	=	array("Create Order" => "javascript:openwindow('".ss_JSStringFormat(ss_withTrailingSlash($GLOBALS['cfg']['plaintext_server']))."Shop_System/Service/OrderForClient/ExistingClient/[us_id]?".ss_getHashMeInURL()."','NewOrder');",);
		}
		
		// get the customized user field objects from 'users' asset.	
		// add the customized user field	
		$this->addCustomizedFields($fieldsArray, "us_");			
		
		if (!array_key_exists("Service", $_REQUEST) and $isAdmin) {					
		
			$this->addField(new CheckBoxField (array(
				'name'			=>	'us_html_email',
				'displayName'	=>	'HTML Email',
				'note'			=>	NULL,
				'required'		=>	FALSE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'10',	'maxLength'	=>	'10',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	NULL,
				'linkQueryValueField'	=>	NULL,
				'linkQueryDisplayField'	=>	NULL,
			)));
			
				
			
			$displayPart = true;
			if (array_key_exists("NoGroups", $_REQUEST)) {
				if ($_REQUEST['NoGroups'] == "Yes") $displayPart = false;
				$this->hiddenValues = array(
					'NoGroups'	=>	'Yes',
				);
			}
			if ($displayPart) {	
				$this->addField(new MultiCheckField (array(
					'name'			=>	'user_groups',
					'displayName'	=>	'Groups/MailingLists',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'40',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	'UserGroupsAdministration.Query',
					'linkQueryValueField'	=>	'ug_id',
					'linkQueryDisplayField'	=>	'ug_name',
					'linkTableName'		=>	'user_user_groups',
					'linkTableOurKey'	=>	'uug_us_id',
					'linkTableTheirKey'	=>	'uug_ug_id',
				)));	
				
				if (ss_optionExists('Member Expiry Date')) {
					$this->addField(new DateTimeField (array(						
							'name'			=>	'us_activated',
							'displayName'	=>	'Expiry Date',
							'note'			=>	'leave blank for never',
							'required'		=>	false,
							'class'			=>	'formsNoSize',
							'verify'		=>	FALSE,
							'value'			=>	null,
							'unique'		=>	FALSE,
							'displayDateFormat'	=>	'',
							'showCalendar'	=> 	TRUE,
							'size'	=>	'8',	'maxLength'	=>	'10',	
					)));
				}
				
				if (ss_optionExists('Shop Per Member Discounts')) {
					$this->addField(new PercentField (array(						
							'name'			=>	'us_discount_percentage',
							'displayName'	=>	'Shop Discount',
							'note'			=>	'leave blank for none',
							'required'		=>	false,
							'verify'		=>	false,
							'unique'		=>	false,
							'size'	=>	'8',	'maxLength'	=>	'10',	
					)));
				}				
				
				$this->addField(new CheckBoxField (array(
					'name'			=>	'us_no_spam',
					'displayName'	=>	'Do Not Send Newsletters',
					'note'			=>	'Newsletters will not be sent to users with this box ticked.  users who have used the \'Unsubscribe\' function will have this box ticked, as well as being removed from any of the mailing lists selected in the \'Subscribe\' item.  Unsubscribe will not affect other user group assignments.',
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'10',	'maxLength'	=>	'10',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
				)));
				
				$this->addField(new CheckBoxField (array(
					'name'			=>	'us_confirm_receipt',
					'displayName'	=>	'User must confirm previous order.',
					'note'			=>	'Automatically put orders on standby for this user.',
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'10',	'maxLength'	=>	'10',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
				)));

/*
				$this->addField(new MemoField (array(
					'name'			=>	'us_login_note',
					'displayName'	=>	'Login Note',
					'note'			=>	'Leave a note for them at login',
					'required'		=>	false,
					'verify'		=>	false,
					'unique'		=>	false,
					'size'	=>	'30',	'maxLength'	=>	'1024',
					'rows'	=>	'6',	'cols'		=>	'40',
				)));
*/

				$this->addField(new MemoField (array(
					'name'			=>	'us_notes',
					'displayName'	=>	'General Notes',
					'note'			=>	'General Notes',
					'required'		=>	false,
					'verify'		=>	false,
					'unique'		=>	false,
					'size'	=>	'30',	'maxLength'	=>	'1024',
					'rows'	=>	'6',	'cols'		=>	'40',
				)));

						
				$this->addField(new IntegerField (array(
					'name'			=>	'us_bl_id',
					'displayName'	=>	'Blacklist Ident',
					'note'			=>	'The identifier of this person on the blacklist',
					'required'		=>	false,
					'verify'		=>	FALSE,
					'unique'		=>	false,
				)));

				$this->addField(new CheckBoxField (array(
					'name'			=>	'us_permanent_tracking',
					'displayName'	=>	'User must always be tracked.',
					'note'			=>	'',
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'10',	'maxLength'	=>	'10',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
				)));

				$this->addField(new SelectField (array(
					'name'			=>	'us_payment_gateway',
					'displayName'	=>	'Payment Gateway',
					'tableName'		=>	'users',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	false,
					'linkQuery'		=>	'select * from payment_gateways join payment_gateway_options on po_pg_id = pg_id where po_active = true and po_restrict_to_person = true',
					'linkQueryValueField'	=>	'po_id',
					'linkQueryDisplayField'	=>	array( 'po_id', 'pg_name', 'pg_description' ),
				)));

				$this->addField(new IntegerField (array(
					'name'			=>	'us_temporary_tracking',
					'displayName'	=>	'Number of automatic tracking times left.',
					'note'			=>	'',
					'required'		=>	TRUE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
				)));

				$this->addField(new IntegerField (array(
					'name'			=>	'us_discount',
					'displayName'	=>	'Discount (%) on orders',
					'note'			=>	'',
					'required'		=>	TRUE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
				)));

				$this->addField(new DateField (array(
					'name'			=>	'us_discount_expires',
					'displayName'	=>	'Discount Expiry Date',
					'note'			=>	NULL,
					'required'		=>	false,
					'class'			=>	'formborder',
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'defaultValue'	=>	'2010-01-01',
					'showCalendar'	=> 	TRUE,
					'size'	=>	'10',	'maxLength'	=>	'10',
					'rows'	=>	'6',	'cols'		=>	'47',			
				)));

				$this->addField(new FloatField (array(
					'name'			=>	'us_account_credit',
					'displayName'	=>	'Credit on next order',
					'note'			=>	'',
					'required'		=>	TRUE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
				)));

				$this->addField(new SelectField (array(
					'name'			=>	'us_credit_from_gateway_option',
					'displayName'	=>	'Credit From Gateway',
					'tableName'		=>	'users',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	false,
					'linkQuery'		=>	'select * from payment_gateway_options join payment_gateways on po_pg_id = pg_id where po_active = 1',
					'linkQueryValueField'	=>	'po_id',
					'linkQueryValueFieldIsText' => true,
					'linkQueryDisplayField'	=>	array( 'po_id', 'pg_name', 'po_currency' ),
				)));

				$this->addField(new MemoField (array(
					'name'			=>	'us_credit_notes',
					'displayName'	=>	'Credit Notes',
					'note'			=>	'Credit Notes',
					'required'		=>	false,
					'verify'		=>	false,
					'unique'		=>	false,
					'size'	=>	'30',	'maxLength'	=>	'1024',
					'rows'	=>	'6',	'cols'		=>	'40',
				)));

				$this->addField(new IntegerField (array(
					'name'			=>	'us_no_chargeback_count',
					'displayName'	=>	'Number of times this customer must use a no chargeback gateway',
					'note'			=>	'',
					'required'		=>	TRUE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
				)));

				$this->addField(new SelectField (array(
						'name'			=>	'us_do_not_track',
						'displayName'	=>	'Do not track this user',
						'tableName'		=>	'users',
						'note'			=>	NULL,
						'required'		=>	TRUE,
						'verify'		=>	FALSE,
						'unique'		=>	false,
						'enumField'		=>	true,
					)));	

				$this->addField(new SelectField (array(
						'name'			=>	'us_do_not_address_check',
						'displayName'	=>	'Do not address check this user',
						'tableName'		=>	'users',
						'note'			=>	NULL,
						'required'		=>	TRUE,
						'verify'		=>	FALSE,
						'unique'		=>	false,
						'enumField'		=>	true,
					)));	

				$this->addField(new SelectField (array(
						'name'			=>	'us_has_import_license',
						'displayName'	=>	'Has Import License, ignore Max Order by Country total for this user',
						'tableName'		=>	'users',
						'note'			=>	NULL,
						'required'		=>	TRUE,
						'verify'		=>	FALSE,
						'unique'		=>	false,
						'enumField'		=>	true,
					)));	

			}
		}		
	}

}
?>
