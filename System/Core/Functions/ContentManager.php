<?php
	define( 'ADMIN_PACKING_ROOM',			0b0000000000000001 );	// 2^0
	define( 'ADMIN_PRODUCT_PRICING',		0b0000000000000010 );
	define( 'ADMIN_PRODUCT_ENTRY',			0b0000000000000100 );
	define( 'ADMIN_CUSTOMER_ISSUE',			0b0000000000001000 );
	define( 'ADMIN_ORDER_LIST',				0b0000000000010000 );
	define( 'ADMIN_VIEW_ORDER',				0b0000000000100000 );	// 2^5
	define( 'ADMIN_EDIT_ORDER',				0b0000000001000000 );
	define( 'ADMIN_CREATE_ORDER',			0b0000000010000000 );
	define( 'ADMIN_ORDER_STATUS',			0b0000000100000000 );
	define( 'ADMIN_STOCKING_UPDATES',		0b0000001000000000 );
	define( 'ADMIN_SEE_INCOMPLETE_ORDERS',	0b0000010000000000 );	// 2^10
	define( 'ADMIN_EDIT_BLACKLIST',			0b0000100000000000 );
	define( 'ADMIN_ASSETS',					0b0001000000000000 );	// 2^12
	define( 'ADMIN_EDIT_USERS',				0b0010000000000000 );
	define( 'ADMIN_DELETE_ISSUE',			0b0100000000000000 );

	// 2^11-1 = 2047 all

	function ss_isGuest()
	{
		if( array_key_exists( 'User', $_SESSION )
		 && array_key_exists( 'us_id', $_SESSION['User'] ) )
		 	if( $_SESSION['User']['us_id'] < 0 )
				return TRUE;
			else
				return FALSE;
		else
			return TRUE;
	}

	function ss_isAdmin()
	{
			return( array_key_exists('User', $_SESSION )
			 and array_key_exists('user_groups', $_SESSION['User'])
			 and array_key_exists(1, $_SESSION['User']['user_groups']) );
	}

	function ss_adminCapability( $capability )
	{
		
//		ss_log_message( $_SESSION['User']['us_admin_level']." vs ".$capability );
		if(  array_key_exists('User', $_SESSION )
			&& array_key_exists('us_admin_level', $_SESSION['User'] ) )
				return( $_SESSION['User']['us_admin_level'] & $capability );

		return false;
	}

	function ss_getAssetParentIDs($assetID) {
		$return = array();
		$assetID = str_replace('(', '', $assetID);
		$assetID = str_replace(')', '', $assetID);
		if (strlen($assetID)) {
			$getParent = getRow("SELECT as_parent_as_id FROM assets WHERE as_id = $assetID");
			if (strlen($getParent['as_parent_as_id'])) {
				array_push($return, $getParent['as_parent_as_id']);
				while (!strlen($getParent['as_parent_as_id']) or $getParent['as_parent_as_id'] == null) {
					$getParent = getRow("SELECT as_parent_as_id FROM assets WHERE as_id = $assetID");
					array_push($return, $getParent['as_parent_as_id']);
				}
			}
		}
		return $return;
	}
	function ss_getDiskSpaceUsage ($isReturnAll = true) {
		$dirPath = expandPath('');

		$cmdDu = "/usr/bin/du -cs {$dirPath}Custom | /bin/grep total";
        //$cmdDu = "/usr/bin/ -cs {$dirPath}Custom | /grep total";
	    $cm_result = exec($cmdDu);
		$cm_result = str_replace(chr(13).chr(10),chr(10),$cm_result);

		// define some characters for clarity
		$tab = chr(9);
		$temp = ListToArray($cm_result, $tab);
		$dirSize = str_replace('M','',$temp[0]);

		$cmdDu = "/usr/bin/du -cs {$dirPath}Custom/ContentStore/ImportExport | /bin/grep total";
	    $cm_result = exec($cmdDu);

		$cm_result = str_replace(chr(13).chr(10),chr(10),$cm_result);

		// define some characters for clarity
		$tab = chr(9);
		$temp = ListToArray($cm_result, $tab);
		$importExportSize = str_replace('M','',$temp[0]);

		$cmdDu = "/usr/bin/du -cs {$dirPath}Custom/ContentStore/Assets/ | /bin/grep total";
	    $cm_result = exec($cmdDu);

		$cm_result = str_replace(chr(13).chr(10),chr(10),$cm_result);

		// define some characters for clarity
		$tab = chr(9);
		$temp = ListToArray($cm_result, $tab);
		$customSize = str_replace('M','',$temp[0]);

		global $cfg;
		$spaceAllowanceMB = 10;

		$space = ss_optionExists("Disk Space Allowance");
		if ($space) {
			$spaceAllowanceMB = $space;
		}

		$spaceAllowance = $spaceAllowanceMB * 1024;

		//$spaceAllowance = $spaceAllowance * (1024 * 1024);

		$dbSize = ss_get_DB_size();

		$systemSizeMB = round(($dirSize - $customSize - $importExportSize) / 1024, 1);
		$dbSizeMB = round($dbSize/1024, 1);
		$customSizeMB = round($customSize/1024, 1);
		//$totalUsageMB = round(($dirSize+$dbSize)/1024, 1);
		//$allowedSpaceMB = round(($spaceAllowance - $dbSize - $dirSize)/1024, 1);
		$totalUsageMB = $customSizeMB + $dbSizeMB + $systemSizeMB;
		$allowedSpaceMB = $spaceAllowanceMB - $totalUsageMB;

		if ($isReturnAll) {
			$returnAll = array();
			$returnAll['allowance'] = $spaceAllowanceMB;
			$returnAll['system'] = $systemSizeMB;
			$returnAll['database'] = $dbSizeMB;
			$returnAll['custom'] = $customSizeMB;
			$returnAll['total'] = $totalUsageMB;
			$returnAll['freespace'] = $allowedSpaceMB;
			return $returnAll;
		} else {
			return $allowedSpaceMB;
		}
		return 1;
	}

	// query asset and get cereal and then return the specific key value
	// if the key is null, return array of cereal
	function ss_getAssetCereal($assetID, $key = null, $isArray = false) {
		$asset = getRow("SELECT as_serialized FROM assets WHERE as_id = $assetID");
		if (strlen($asset['as_serialized'])) {
			$cereal = unserialize($asset['as_serialized']);
			if ($key != null) {
				if (array_key_exists($key, $cereal)) {
					$tempValue = $cereal[$key];
					if ($isArray && !is_array( $cereal[$key] ) ) {
						$tempValue = unserialize($cereal[$key]);
					}
					return $tempValue;
				} else {
					return null;
				}
			}
		} else {
			$cereal = array();
			if ($key == null) {
				return null;
			}
		}
		return $cereal;
	}
	function ss_getAssetLayoutCereal($assetID, $key = null, $isArray = false) {
		$asset = getRow("SELECT as_layout_serialized FROM assets WHERE as_id = $assetID");
		if (strlen($asset['as_layout_serialized'])) {
			$cereal = unserialize($asset['as_layout_serialized']);
			if ($key != null) {
				if (array_key_exists($key, $cereal)) {
					$tempValue = $cereal[$key];
					if ($isArray) {
						$tempValue = unserialize($cereal[$key]);
					}
					return $tempValue;
				} else {
					return null;
				}
			}
		} else {
			$cereal = array();
			if ($key == null) {
				return null;
			}
		}
		return $cereal;
	}
	function hasChildren(&$treepart, &$canAdmin) {
		if ($treepart['HasChildren']) {
			if (array_key_exists($treepart['as_id'], $canAdmin)) {
				$treepart['display'] = true;
			} else {
				$treepart['display'] = false;
			}
			for($i=0; $i < count($treepart['Children']); $i++) {
				$temp = hasChildren($treepart['Children'][$i], $canAdmin);
			}
		} else {
			if (array_key_exists($treepart['as_id'], $canAdmin)) {
				$treepart['display'] = true;
			} else {
				$treepart['display'] = false;
			}
		}
	}
	function makeAllDisplayble(&$treepart) {
		$treepart['display'] = true;
		if ($treepart['HasChildren']) {
			for($i=0; $i < count($treepart['Children']); $i++) {
				$temp = makeAllDisplayble($treepart['Children'][$i]);
			}
		}
	}

	function checkChildren(&$treepart) {

		if ($treepart['HasChildren']) {
			for($i=0; $i < count($treepart['Children']); $i++) {
				checkChildren($treepart['Children'][$i]);
			}
			$display = false;
			for($i=0; $i < count($treepart['Children']); $i++) {
				if ($treepart['Children'][$i]['display'] or (array_key_exists('displayonlylink',$treepart['Children'][$i]) and $treepart['Children'][$i]['displayonlylink'])) {
					$display = true;
					break;
				}
			}
			if (!$treepart['display']) {
				$treepart['displayonlylink'] = $display;
			} else {
				$treepart['displayonlylink'] = false;
			}

			$treepart['HasChildren'] = $display;
			if (!$display) {
				//$treepart['Children'] = array();
			}

		} else {
			$treepart['displayonlylink'] = false;
		}
		//print ($treepart['Path']."<BR>");
	}
	function ss_getDataCollectionRecordDetail($assetID, $primaryID, $content, $fieldsArray, $className = 'DataCollectionAdministration', $prefix = 'DaCo') {
		requireClass($className);

		$recordAdmin = new $className($assetID);
		$recordAdmin->primaryKey = $primaryID;
		$monthlyScheduleOptions = array();
		$Q_Details = getRow("SELECT * FROM {$recordAdmin->tableName} WHERE {$recordAdmin->tablePrimaryKey} = $primaryID");

		foreach($fieldsArray as $fieldDef) {
			// Param all the settings we might have
			ss_paramKey($fieldDef,'uuid','');
			ss_paramKey($fieldDef,'type','');
			ss_paramKey($fieldDef,'size','');
			ss_paramKey($fieldDef,'options',array());
			ss_paramKey($fieldDef,'name','unknown');
			ss_paramKey($fieldDef,'AppearInList','no');

			$displayField = $prefix.$fieldDef['uuid'];
			// Find the value for the field
			if ($fieldDef['type'] == 'MonthlyScheduleField') {
				foreach ($fieldDef['options'] as $key => $values) {
					$monthlyScheduleOptions[$values['uuid']] = "<IMG src=\"Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$recordAdmin->getClassName()."/Images/option_".($key + 1).".jpg\">";
				}

				$monthlyScheduleOptions[0] = "<IMG src=\"Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$recordAdmin->getClassName()."/Images/option_0.jpg\">";
			}


			if ($recordAdmin->tableTimeStamp == $displayField) {
				$value = formatDateTime($Q_Details[$displayField], "Y-m-d");
			} else {
				if (array_key_exists($displayField, $recordAdmin->fields) AND is_object($recordAdmin->fields[$displayField])) {
					if ($fieldDef['type'] == "MonthlyScheduleField") {
						$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField], true, $monthlyScheduleOptions, 12);
					} else if ($fieldDef['type'] == "PopupUniqueImageField") {
						$imgFile = ss_storeForAsset($assetID)."/".$fieldDef['uuid']."/{$Q_Details[$displayField]}";
						if (file_exists($imgFile) and strlen($Q_Details[$displayField])) {
							if (strlen($fieldDef['size'])) {
								$imgProperties = ss_ListToKeyArray($fieldDef['size']);
								//ss_DumpVarDie($imgProperties);
								if (array_key_exists('s', $imgProperties)) {
									$windowLink = "href='$imgFile' target='_blank'";
									if (array_key_exists('w', $imgProperties) and array_key_exists('h', $imgProperties)) {
										$windowLink = "href='javascript:void(0);' onClick = \"window.open('$imgFile', '{$fieldDef['uuid']}', 'height={$imgProperties['h']},width={$imgProperties['w']},scrollbars,resizable');void(0);\"";
									}
									if (!strpos($imgProperties['s'],'x')) {
										$imgProperties['s'] .='x';
									}
									$value = "<a $windowLink ><img border=0 src='index.php?act=ImageManager.get&Image=".ss_URLEncodedFormat($imgFile)."&Size={$imgProperties['s']}'></a>";
								} else {
									$value = "<img src='$imgFile'>";
								}
							} else {
								$value = "<img src='$imgFile'>";
							}
						} else {
							$value = "";
						}
					} else if ($fieldDef['type'] == "NameField") {
						if($displayField == 'us_name') {
							$value = $Q_Details['us_first_name'].' '.$Q_Details['us_last_name'];
						} else {
					  		$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField]);
						}
					} else if (strtolower(ListFirst($fieldDef['type'],'_')) == 'datacollectionfield' || strtolower(ListFirst($fieldDef['type'],'_')) == 'datacollectionmultifield') {
					  	$value = $recordAdmin->fields[$displayField]->displayFullDetails($Q_Details[$displayField]);
					} else  {
						$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField]);
					}
				} else {
					$value = $Q_Details[$displayField];
				}
			}
			if(!strlen($value))	$value = '&nbsp;';
			$content = stri_replace("[{$fieldDef['name']}]",$value,$content);
		}

		return $content;
	}

	function ss_getWebPaymentConfiguration() {
		$Q_Config = getRow("SELECT * FROM web_pay_configuration WHERE wpc_id = 1");
		$result = array();
		$result['UseCheque'] = $Q_Config['wpc_use_cheque'];
		$result['UseCollection'] = $Q_Config['wpc_use_collection'];
		$result['UseInvoice'] = $Q_Config['wpc_can_invoice'];
		$result['UseDirect'] = $Q_Config['wpc_direct_payment'];
		if ($Q_Config['wpc_direct_payment']) {
			$result['DirectSetting'] = unserialize($Q_Config['wpc_direct_payment_details']);
		}
		$result['ChequeSetting'] = array();
		if ($Q_Config['wpc_use_cheque']) {
			$result['ChequeSetting'] = unserialize($Q_Config['wpc_cheque_details']);
		}
		$result['InvoiceSetting'] = array();
		if ($Q_Config['wpc_can_invoice']) {
			$result['InvoiceSetting'] = unserialize($Q_Config['wpc_invoice_details']);
		}
		$result['CollectionSetting'] = array();
		if ($Q_Config['wpc_use_collection']) {
			$result['CollectionSetting'] = unserialize($Q_Config['wpc_collection_details']);
		}

		$result['UseCreditCard'] = $Q_Config['wpc_use_credit_card'];
		$result['CreditCardSetting'] = array();
		if ($Q_Config['wpc_use_credit_card']) {
			$temp = unserialize($Q_Config['wpc_card_details']);
			$Q_Processor = getRow("SELECT * FROM web_pay_processors WHERE wpp_name LIKE '{$temp['Processor']}'");
			$result['CreditCardSetting']['ProcessorDisplayName'] = $Q_Processor['wpp_display_name'];
			$result['CreditCardSetting']['Processor'] = $Q_Processor['wpp_name'];
		}

		return $result;
	}
	function ss_file_size_info($filesize) {
 		$bytes = array('KB', 'KB', 'MB', 'GB', 'TB'); # values are always displayed

 		if ($filesize < 1024) $filesize = 1; # in at least kilobytes.
 		for ($i = 0; $filesize > 1024; $i++) $filesize /= 1024;
 		$file_size_info['size'] = ceil($filesize);
 		$file_size_info['type'] = $bytes[$i];

 		return $file_size_info;
	}

	function ss_get_DB_size($dbName='', $dbUser='', $dbPassword='') {
		// Calculate DB size by adding table size + index size:
		if (strlen($dbName)) {
			$newDB = new QueryManager('mysql',$dbUser,$dbPassword,'210.55.31.56',$dbName);
			$Q_TableStatus = $newDB->query("SHOW TABLE STATUS");
		} else {
			$Q_TableStatus = query("SHOW TABLE STATUS");
		}
		$dbsize = 0;

		while ($row = $Q_TableStatus->fetchRow()) {
 			$dbsize += $row['Data_length'] + $row['Index_length'];
		}
		//print "database size is: $dbsize bytes<br />";
		//print 'or<br />';

		//$dbsize = ss_file_size_info($dbsize);

		return (int) round($dbsize / 1024);

	}


	function ss_getFieldSetTypes($returnDefaultTypes = true, $notsearchbleTypes = false) {
		$specialTypes = array();

		$typeMappings = array(
			'TextField'				=>	'Text',
			'NameField'				=>	'Name',
			'IntegerField'			=>	'Number',
			'FloatField'			=>	'Decimal',
			'CheckBoxField'			=>	'Checkbox',
			'EmailField'			=>	'Email Address',
			'MemoField'				=>	'Multi-line Text',
			'HtmlMemoField2'		=>	'HTML Memo',
			'PasswordField'			=>	'Password',
			'MultiCheckFromArrayField'	=>	'Select Many',
			'MultiSelectFromArrayField'	=>	'Select Many (Drop down)',
			'RadioFromArrayField'	=>	'Select One',
			'SelectFromArrayField'	=>	'Select One (Drop down)',
			'CountryField'			=>	'Country',
			'DateField'				=>	'Date',
			'RangeField'			=>	'Money Range',
			'AssetTreeField'		=>	'Item Link',
			'CountryStateField'		=>	'Country and State',
			'LayoutField'			=>	'Layout Selector',
		);

		if (ss_optionExists("User Parent Data Collection Field")) {
			$Q_DataAssets = query("SELECT as_id, as_name FROM assets WHERE as_type LIKE 'DataCollection' AND (as_deleted IS NULL OR as_deleted = 0)");
			while($aAsset = $Q_DataAssets->fetchRow()) {
				$typeMappings["DataCollectionField_{$aAsset['as_id']}"] = "{$aAsset['as_name']} (Select One)";
				$typeMappings["DataCollectionMultiField_{$aAsset['as_id']}"] = "{$aAsset['as_name']} (Select Many)";
			}
		}
		if ($notsearchbleTypes) {
			return array('PopupUniqueImageField', 'PasswordField', 'MonthlyScheduleField');
		}

		if (ss_optionExists("Monthly Schedule Field")) {
			$typeMappings['MonthlyScheduleField'] = 'Monthly Schedule';
		}

		if (ss_optionExists("Image Field")) {
			$typeMappings['PopupUniqueImageField'] = 'Image';
		}
		if (ss_optionExists("File Field")) {
			$typeMappings['FileField'] = 'File';
		}
		if ($returnDefaultTypes) {
			return $typeMappings;
		}
		$specialTypes = array(
			'MultiCheckFromArrayField'	=>	'Select Many (Checkboxes)',
			'MultiSelectFromArrayField'	=>	'Select Many',
			'RadioFromArrayField'	=>	'Select One',
			'SelectFromArrayField'	=>	'Select One (Drop down)',
			'CountryField'	=>	'Country',
		);
		return $specialTypes;
	}
	/*
		ContentManager
	*/
	function ss_getSubscriptionPrice($ms_id) {
		$clientCountry = ss_getCountryID();


		$Q_Subscription = getRow("SELECT * FROM members_subscriptions, countries WHERE ms_id = $ms_id AND cn_id = ms_default_cn_id ");

		$Q_SubscriptionPrices = query("SELECT * FROM members_subscription_prices, countries WHERE msp_sub_id = $ms_id AND msp_cn_id = $clientCountry AND cn_id = $clientCountry ");


		if ($Q_SubscriptionPrices->numRows()) {
			$Q_Prices = $Q_SubscriptionPrices->fetchRow();
			$sym = $Q_Prices['cn_currency_symbol'];
			if (!strlen($sym)) $sym = "\$";
			return "{$Q_Prices['cn_currency_code']} $sym{$Q_Prices['msp_price']}";
		} else {
			$sym = $Q_Subscription['cn_currency_symbol'];
			if (!strlen($sym)) $sym = "\$";
			return "{$Q_Subscription['cn_currency_code']} $sym{$Q_Subscription['ms_default_price']}";
		}

	}

	function ss_login($usID,&$errors, $useCustom = false) {

		// custom login
		// one of example for the custom login is duty free frequent buyer login
		// check custom login file in the Custom/functioin folder
		/*
		$customFilePath = expandPath('Custom/Core/ss_login.php');
		if ($useCustom and file_exists($customFilePath)) {
			return include($customFilePath);

		}
		*/

		$result = query("
			SELECT * FROM users
			WHERE us_id = $usID
		");

		// See if the user was validated
		if ($result->numRows() > 0) 
		{
			$row = $result->fetchRow();

			$overrideURL = '';
			if( array_key_exists( 'us_login_url', $row )
				 && strlen( $row['us_login_url'] ) )
				$overrideURL = $row['us_login_url'];

			$expiryDateOK = true;
			if (strlen($row['us_activated'])) {
				if (ss_SQLtoTimeStamp($row['us_activated']) < ss_SQLtoTimeStamp(date('Y-m-d h:i:s'))) {
					$expiryDateOK = false;
                    $errors = '<STRONG>A Problem :</STRONG> Your account has expired. To renew it, click here.</LI></UL>';
				}
			}

			if ($expiryDateOK)
			{
				$old_guest_id = $_SESSION['User']['us_id'];
				$_SESSION['User'] = $row;

//				query( "update etag_user_tracking set ut_us_id = {$row['us_id']} where ut_us_id = $old_guest_id" );
				query( "delete from  etag_user_tracking where ut_us_id = $old_guest_id" );

				// Find out what groups the user is in
				$result = query("
					SELECT * FROM user_user_groups
					WHERE uug_us_id = ".$_SESSION['User']['us_id']."
					ORDER BY uug_ug_id
				");

				// Add the user groups into the users session
				// All users are automatically members of the guests group (ugid = 0)
				$_SESSION['User']['user_groups'] = array(0 => 1);
				while ($row = $result->fetchRow()) {
					if ($row['uug_ug_id'] != 0) {
						$_SESSION['User']['user_groups'][$row['uug_ug_id']] = 1;
					}
				}

				// see if they have mark received any boxes
				$boxs = GetField( "select count(*) from shopsystem_order_sheets_items join shopsystem_orders on or_id = orsi_or_id where or_us_id = $usID and or_shipped IS NOT NULL and or_paid IS NOT NULL and orsi_date_shipped IS NOT NULL and orsi_received IS NOT NULL" );

				ss_log_message( "User $usID has marked received $boxs boxes" );
				$_SESSION['User']['BoxesReceived'] = $boxs;

				if( strlen( $overrideURL ) )
				{
					location($overrideURL);
				}

				return true;
			}
		}

		return false;
	}

	function ss_AuthdCustomer( )
	{
		if( $_SESSION['User']['us_id'] > 0 )
			return !$_SESSION['User']['us_bl_id'] && array_key_exists( 'BoxesReceived', $_SESSION['User'] ) && ( $_SESSION['User']['BoxesReceived'] > 0 );
		else
			return false;
	}

	function ss_newAssetName($name, $parentLink) {
		$assetName = $name;

		$Q_NameShare = query("
			SELECT * FROM assets
			WHERE as_parent_as_id = $parentLink
				AND as_name LIKE '{$assetName}'
				AND as_deleted != 1
		");
		$counter = 1;

		while ($Q_NameShare->numRows()) {
			if ($Q_NameShare->numRows()) {
				 $assetName = "{$name} ($counter)";
				 $counter += 1;
			}
			$Q_NameShare = query("
				SELECT * FROM assets
				WHERE as_parent_as_id = $parentLink
					AND   as_name LIKE '{$assetName}'
					AND as_deleted != 1
			");
		}
		return $assetName;
	}

	$cm_systemAssets = array(
		'index.php'	=>	1,
		'users'		=>	2,
		'Images'	=>	3,
		'Links'		=>	4,
		'404 Error'	=>	50,
	);

	function ss_systemAsset($name) {
		return $GLOBALS['cm_systemAssets'][$name];
	}

	// check whether the content manager is offline version or not
	// return true if the cm is offline.
	function ss_isOffline() {
		if (strpos(expandPath("foo"),"www/jobs"))
			return true;

		return  false;
	}
	function ss_optionExists($option) {
		$option = strtolower($option);
		if (array_key_exists( 'options', $GLOBALS['cfg'] ) && array_key_exists($option, $GLOBALS['cfg']['options']))
			return $GLOBALS['cfg']['options'][$option];
		return false;
	}

	function ss_paramKey(&$structure,$key,$default = null, $nullValue='nullvaluedefineherepleaseifuwantavaluedefinewhenthekeyhasnolength') {
		if (!is_array($structure)) {
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, debug_backtrace() );
//			ss_DumpVar($key,'no array');
		}
		if (!array_key_exists($key,$structure)) {
			$structure[$key] = $default;
		}
		if ($nullValue !='nullvaluedefineherepleaseifuwantavaluedefinewhenthekeyhasnolength' and !strlen($structure[$key])) {
			$structure[$key] = $nullValue;
		}

	}

	function ss_paramKeyAndNoStringLength(&$structure,$key,$default = null) {
		if (!array_key_exists($key,$structure) or strlen($structure[$key]) == 0) {
			$structure[$key] = $default;
		}
	}


	/*
	* Set Classes
	* returns an array containing their directories and class names
	*/
	function patternFileFindList($directories, $regExp) {
		$classes = array();

		foreach($directories as $dir) {
			patternFileFind($dir, $classes, $regExp);
		}
		return $classes;
	}
	// '^_'
	/* search for class file names starting with  "_"
	 * if the name contains "_", store its class name and directory.
	 */
	function patternFileFind($dir, &$classes, $regExp) {

		if (is_array($dir)) {

			foreach($dir as $theDir) {
				patternFileFind($theDir, $classes, $regExp);
			}
			//ss_DumpVar('patter classes', $classes);
		} else {
			if( !is_dir($dir) )
				return;
			if( !is_readable($dir) )
				return;
			$dh=opendir($dir);
			while ($file=readdir($dh)){

				if($file!="." && $file!=".."){

					$fullpath=$dir."/".$file;


					if(!is_dir($fullpath)){
						if (preg_match($regExp,$file,$matches)) {
							ss_log_message( "patternFileFind $fullpath" );
							//ss_log_message_r('matches',$matches);
							//if (substr($file, 0,1) == "_") {
							$temp = $matches[1];
							//$temp = basename ($file,".php");
							//$temp = substr($temp, 1, strlen($temp)-1);
							$classes[$temp] = array(
								'name'		=> $temp,
								'fileName'	=> $file,
								'directory'  => $dir
							);
							//print($fullpath."<br>");
						}
					} else {
						patternFileFind($fullpath, $classes, $regExp);
					}
				}
			}
			closedir($dh);
		}
	}




	/* search for class file names starting with  "_"
	 * if the name contains "_", store its class name and directory.
	 */
	function searchClasses($dir, $classes) {
		$dh=opendir($dir);
		while ($file=readdir($dh)){

			if($file!="." && $file!=".."){

				$fullpath=$dir."/".$file;
				//print($fullpath."<br>");
				if(!is_dir($fullpath)){
					if (substr($file, 0,1) == "_") {
						$temp = basename ($file,".php");
						$temp = substr($temp, 1, strlen($temp)-1);
						$classes[$temp] = array(
							'name'		=> $temp,
							'directory'  => $dir
						);
					}
				} else {
					searchClasses($fullpath, $classes);
				}
			}
		}
		closedir($dh);
	}

	/*  Require a class
	 *	Assume that all class names are unique
	 */
	function requireOnceClass($className) {
		global $classes;
		// Include the class definition
		if (strlen($className) and array_key_exists($className,$classes)) {
			if( file_exists($classes[$className]['directory']."/".$classes[$className]['fileName']) )
				require_once($classes[$className]['directory']."/".$classes[$className]['fileName']);
			else
			{
				echo "unable to instantiate class ".$className." which should be here ".$classes[$className]['directory']."/".$classes[$className]['fileName']."<br>";
				echo "stack <br>";
				ss_DumpVarDie( debug_backtrace() );
			}
		}
	}
	function ss_getClassDirectory($className) {
		global $classes;
		// Include the class definition
		if (strlen($className) and array_key_exists($className,$classes)) {
			return $classes[$className]['directory'];
		}
		return '';
	}

	function requireFromArray($name,&$array) {
		// Include the class definition
		if (strlen($name) and array_key_exists($name,$array)) {
			require_once($array[$name]['directory']."/".$array[$name]['fileName']);
		}
	}


	function requireClass($name) {
		global $classes;
		requireFromArray($name,$classes);
	}

	function requireLayout($name) {
		global $layouts;
		requireFromArray($name,$layouts);
	}

	function ss_TryUnserialize($what) {
		if (($what !== null) and strlen($what)) {
			return unserialize($what);
		} else {
			return array();
		}
	}


	function ss_ExecuteRequestOnBranchAssets($root,$fuseaction,$parameters,$includeRoot = false) {
		$searchAssets = ss_GetBranchAssetsArray($root,$includeRoot);
		for ($i=0; $i<count($searchAssets); $i++) {
			$temp = new Request($fuseaction,array_merge(array('as_id'=>$searchAssets[$i]),$parameters));
		}
	}

	function ss_GetBranchAssetsArray($root=1,$includeRoot = false,$asKey = false) {

		$searchArray = array($root);
		$result = array();

		while (count($searchArray)) {
			// Grab an id from the array
			$id = array_pop($searchArray);

			// Get any children
			$Q_Children = query("SELECT as_id FROM assets WHERE as_parent_as_id = $id");

			// Add them to the list
			while($child = $Q_Children->fetchRow()) {
				array_push($searchArray,$child['as_id']);
			}

			// Add the current id to the result array
			if ($includeRoot or ($id != $root)) {
				if ($asKey) {
					$result[$id] = 1;
				} else {
					array_push($result,$id);
				}
			}
		}
		return $result;
	}

	/**
	* @return true if has the specified permission, false if not
	* @param $permission string permission required
	* @param $assetID integer ID of an asset
	* @param $groupsArray array of user group IDs, e.g. array(1,2,3);
	* @param $loginOnFail if this is 'Yes', a login screen will be displayed if the user does not have permission
	* @desc Check if the logged in user has $permission. $permission can be one of the following :
		IsSuperUser,
		IsDeployer,
		IsInAllTheseGroups (GroupsArray),
		IsInAnyOfTheseGroups (GroupsArray),
		IsLoggedIn,
		CanAccessAsset (as_id),
		CanAdministerAsset (as_id),
		CanAdministerAssetBranch (as_id),
		CanAdministerAtLeastOneAsset,
 */
	function ss_HasPermission($permission,$assetID=null,$groupsArray=null,$loginOnFail = 'No') {
		$extraInputName = 'NotUsed';
		$extraInputValue = null;
		if ($assetID !== null) {
			$extraInputName = 'as_id';
			$extraInputValue = $assetID;
		}
		if ($groupsArray !== null) {
			$extraInputName = 'Groups';
			$extraInputValue = $groupsArray;
		}
		$result = new Request("Security.Authenticate",array(
			'Permission'	=>	$permission,
			'LoginOnFail'	=>	$loginOnFail,
			$extraInputName	=>	$extraInputValue,
		));
		return $result->value;
	}

	function ss_RestrictPermission($permission,$assetID=null,$groupsArray=null) {
		ss_HasPermission($permission,$assetID,$groupsArray,'Yes');
	}

	function ss_getUser() {
		return $_SESSION['User'];
	}

	function ss_getUserExpiryDate() {
		if (array_key_exists("us_activated", $_SESSION['User']) and strlen($_SESSION['User']['us_activated'])) {
			return formatDateTime($_SESSION['User']['us_activated'], "d/m/Y");
		} else {
			return "";
		}
	}

	function ss_getUserID() {
		return $_SESSION['User']['us_id'];
	}

	function ss_setUserToken( $new ) {
		$_SESSION['User']['us_token'] = $new;
		query( "Update users set us_token = '$new' where us_id = {$_SESSION['User']['us_id']}" );
	}

	function ss_getFirstName() {
		return $_SESSION['User']['us_first_name'];
	}

	function ss_getLastName() {
		return $_SESSION['User']['us_last_name'];
	}

	function ss_generateUserHash($userRecord) {
		return md5($userRecord['us_id'].'_'.$userRecord['us_email'].'_'.$userRecord['us_password']);
	}

	function ss_getHashMeInURL() {
		$userID = ss_loggedInUsersID();
		if ($userID !== false) {
			$user = ss_getUser();
			return 'HashMeIn='.$userID.'_'.ss_URLEncodedFormat(ss_generateUserHash($user));
		} else {
			return 'NotLogged=In';
		}
	}
	function ss_loggedInUsersID() {
		if (array_key_exists("User", $_SESSION) and array_key_exists("us_id", $_SESSION['User'])) {
			if ($_SESSION['User']['us_id'] >= 0) {
				return $_SESSION['User']['us_id'];
			}
		}
		return false;
	}

	function ss_getTheDeployerPassword() {
		return "so@v93mv5*6zxvpa912#%$";
	}

?>
