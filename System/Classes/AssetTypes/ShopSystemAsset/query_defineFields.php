<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$extraOption = array(
		array( 
		'Name' 			=> 'ShowTo', 
		'Description' 	=> 'Show To', 
		'Options' 		=> array(
							'All Categories'=>'all',
							'Selected Categories'=>'selected')
		),
											
	);	
	
	if (ss_optionExists('Shop Advanced Product Manage')) {
		array_push($extraOption, array( 
		'Name' 			=> 'OrderBy', 
		'Description' 	=> 'Order By Option',
		'Options' 		=> array(
							'Yes'=>'1',
							'No'=>'0')
		));
	}

    if (ss_optionExists('Shop Gallery')) {
    	$this->fieldSet->addField(new TextField(array(
    		'name'		=>	$this->fieldPrefix.'GALLERY_PAGE_TITLE',
    		'required'	=>	false,
    		'default'	=>	'',
    		'size'	=> 70, 	'maxLength'	=> 256,
    	)));

    	$this->fieldSet->addField(new IntegerField(array(
    		'name'			=>	$this->fieldPrefix.'GALLERY_THUMBNAIL_HEIGHT',
    		'displayName'	=>  'Thumbnail Height',
    		'required'		=>	true ,
    		'size'	=>	3,	'maxlength'	=> 3,
    	)));

    	$this->fieldSet->addField(new IntegerField(array(
    		'name'			=>	$this->fieldPrefix.'GALLERY_THUMBNAIL_WIDTH',
    		'displayName'	=>  'Thumbnail Width',
    		'required'		=>	true,
    		'size'	=>	3,	'maxlength'	=> 3,
    	)));

    	$this->fieldSet->addField(new IntegerField(array(
    		'name'			=>	$this->fieldPrefix.'GALLERY_IMAGES_PER_ROW',
    		'displayName'	=>  'Images Per Row',
    		'required'		=>	true ,
    		'size'	=>	3,	'maxlength'	=> 2,
    	)));

    	$this->fieldSet->addField(new IntegerField(array(
    		'name'			=>	$this->fieldPrefix.'GALLERY_ROWS_PER_PAGE',
    		'displayName'	=>  'Rows Per Page',
    		'required'		=>	false,
    		'size'	=>	3,	'maxlength'	=> 2,
    	)));

    	$this->fieldSet->addField(new IntegerField(array(
    		'name'			=>	$this->fieldPrefix.'GALLERY_POPUP_HEIGHT',
    		'displayName'	=>  'Popup Height',
    		'required'		=>	true ,
    		'size'	=>	3,	'maxlength'	=> 3,
    	)));

    	$this->fieldSet->addField(new IntegerField(array(
    		'name'			=>	$this->fieldPrefix.'GALLERY_POPUP_WIDTH',
    		'displayName'	=>  'Popup Width',
    		'required'		=>	true,
    		'size'	=>	3,	'maxlength'	=> 3,
    	)));
    }

	$this->fieldSet->addField(new FieldSetBuilderField (array(
		'name'			=>	$this->fieldPrefix.'ATTRIBUTES',
		'fieldSetName'	=>	"Product Attributes Definition",			
		'extraOption'	=>	$extraOption,	
	)));
	
	$this->fieldSet->addField(new FieldSetBuilderField (array(
		'name'			=>	$this->fieldPrefix.'PRODUCT_OPTIONS',
		'fieldSetName'	=>	"Product Options Definition",	
		'typeMappings'		=>	array('ProductOptionsField'	=>	'Product Options',),
		'extraOption'	=>	array(
								array( 
								'Name' 			=> 'ShowTo', 
								'Description' 	=> 'Show To', 
								'Options' 		=> array(
													'All Categories'=>'all',
													'Selected Categories'=>'selected')
								),									
							)		
	)));

	$this->fieldSet->addField(new TextField(array(
		'name'		=>	$this->fieldPrefix.'CATEGORY_WINDOW_TITLE_TEMPLATE',
		'displayName'	=>	"Category Window Title Template",
		'required'	=>	false,
		'default'	=>	'[SiteName] - [Category]',			
		'size'	=> 60, 	'maxLength'	=> 256,
	)));
		
	$this->fieldSet->addField(new TextField(array(
		'name'		=>	$this->fieldPrefix.'PRODUCT_WINDOW_TITLE_TEMPLATE',
		'displayName'	=>	"Product Window Title Template",
		'required'	=>	false,
		'default'	=>	'[SiteName] - [Product]',			
		'size'	=> 60, 	'maxLength'	=> 256,
	)));
	
	if (ss_optionExists('Sell Products')) {
	
		$userFields = array();
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
		foreach($fieldsArray as $fieldDef) {		
			// Param all the settings we might have
			ss_paramKey($fieldDef,'uuid','');			
			ss_paramKey($fieldDef,'name','unknown');									
			$userFields[$fieldDef['name']] = $fieldDef['uuid'];
		}
		
		$this->fieldSet->addField(new TextField(array(
			'name'		=>	$this->fieldPrefix.'ADMINEMAIL',
			'displayName'	=>	"Shop Email Address",
			'required'	=>	true,
			'default'	=>	'',			
			'size'	=> 40, 	'maxLength'	=> 256,
		)));

		//briar put this in - hope it doesn't break anything else
		$this->fieldSet->addField(new IntegerField (array(
			'name'			=>	$this->fieldPrefix.'AAREWARDRATE',
			'displayName'	=>	'AA Reward Rate',
			'required'		=>	false,
			'size'	=>	2,	'maxlength'	=> 2,
		)));

		$this->fieldSet->addField(new MultiSelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.'ADDRESSFIELDS',
				'displayName'	=>	'Address Fields',
				'options'		=>	$userFields,
				'multi'			=>	true,
		)));

    	if (ss_optionExists('Shop Customer Select Fields')) {
    		$this->fieldSet->addField(new MultiSelectFromArrayField (array(
    				'name'			=>	$this->fieldPrefix.'CUSTOMER_FIELDS',
    				'displayName'	=>	'Customer Fields',
    				'options'		=>	$userFields,
    				'multi'			=>	true,
    		)));
        }


		$this->fieldSet->addField(new MultiSelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.'REQUIREDFIELDS',
				'displayName'	=>	'Required Fields',
				'options'		=>	$userFields,
				'multi'			=>	true,
		)));

		$this->fieldSet->addField(new MultiSelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.'SHIPPING_REQUIREDFIELDS',
				'displayName'	=>	'Required Shipping Fields',
				'options'		=>	$userFields,
				'multi'			=>	true,
		)));
		
		$this->fieldSet->addField(new MemoField (array(
				'name'			=>	$this->fieldPrefix.'THANKYOUNOTE',
				'displayName'	=>	'Invoice Thank You Note',			
				'rows'	=> 7,	'cols'	=> 40,			
		)));
		
		$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'THANKYOU_CONTENT',
				'displayName'	=>	"Thank You Page",
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-100',
				'height'	=>	'200',
		)));
			
		
		$Q_UserGroups = query("
			SELECT * FROM user_groups			
			ORDER By ug_name
		");
		
		$userGroups = array();
		$newsGroups = array();
		while ($row = $Q_UserGroups->fetchRow()) {
			$userGroups[$row['ug_name']] = $row['ug_id'];
			if ($row['ug_mailing_list'] == 1) 
				$newsGroups[$row['ug_name']] = $row['ug_id'];		
		}
		
		$this->fieldSet->addField(new SelectFromArrayField (array(
			'name'			=>	$this->fieldPrefix.'CUSTOMER_USERGROUPS',
			'displayName'	=>	'Customer User Groups',
			'options'		=>	$userGroups,
			'multi'			=>	true,
			'required'		=>	false,
		)));
		$this->fieldSet->addField(new SelectFromArrayField (array(
			'name'			=>	$this->fieldPrefix.'NEWSLETTER_USERGROUPS',
			'displayName'	=>	'Join Newsletter Groups',
			'options'		=>	$newsGroups,
			'multi'			=>	true,
			'required'		=>	false,
		)));
		if (ss_optionExists('Shop Restricted Order Access')) {
			$this->fieldSet->addField(new SelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.'RESTRICTED_ORDER_ACCESS_USERGROUPS',
				'displayName'	=>	'Restricted Order Access Groups',
				'options'		=>	$userGroups,
				'multi'			=>	true,
				'required'		=>	false,
			)));
		}
		
		$this->fieldSet->addField(new TextField(array(
			'name'		=>	$this->fieldPrefix.'NEWSLETTER_QUESTION',
			'required'	=>	false,
			'default'	=>	'',			
			'size'	=> 70, 	'maxLength'	=> 256,
		)));
		
		$webpaySetting = ss_getWebPaymentConfiguration();
		if ($webpaySetting['UseCheque']) {
			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_CHEQUEEMAIL',
				'displayName'	=>	'Cheque Payment - Client Email Body',
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));
		}
		if ($webpaySetting['UseDirect']) {
			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_DIRECTEMAIL',
				'displayName'	=>	'Direct Payment - Client Email Body',
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));
		}
		if ($webpaySetting['UseInvoice']) {
			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_INVOICEEMAIL',
				'displayName'	=>	'Invoice Payment - Client Email Body',
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));
		}
		if ($webpaySetting['UseCreditCard']) {

			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_CREDITCARDEMAIL',
				'displayName'	=>	$webpaySetting['CreditCardSetting']['ProcessorDisplayName'].' Transaction - Client Email Body',
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));

			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_NEW_OVERSPEND_EMAIL',
				'displayName'	=>	'Overspent '.$webpaySetting['CreditCardSetting']['ProcessorDisplayName'],
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));

			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_NEW_SHIPBILLDIFF_EMAIL',
				'displayName'	=>	'Shipping not Billing '.$webpaySetting['CreditCardSetting']['ProcessorDisplayName'],
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));

			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_NEW_CLOSE_ORDER_EMAIL',
				'displayName'	=>	'Orders too close together '.$webpaySetting['CreditCardSetting']['ProcessorDisplayName'],
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));

			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_CARDCHARGED_EMAIL',
				'displayName'	=>	'Card Charged '.$webpaySetting['CreditCardSetting']['ProcessorDisplayName'],
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));

			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_CARDDENIED_EMAIL',
				'displayName'	=>	'Card Denied '.$webpaySetting['CreditCardSetting']['ProcessorDisplayName'],
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));

		}
		if (ss_optionExists('Shop Product Stock Notifications')) {
			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'STOCK_NOTIFICATION_EMAIL',
				'displayName'	=>	'Stock Notification Email',
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));
		}
		if (ss_optionExists('Shop Order Confirmation Email')) {
			$this->fieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	$this->fieldPrefix.'CLIENT_CONFIRMATION_EMAIL',
				'displayName'	=>	'Order Confirmation Email',
				'required'		=>	true,
				'width'	=>	'document.body.clientWidth-250',
				'height'	=>	'200',
			)));
			$this->fieldSet->addField(new CheckBoxField (array(
				'name'			=>	$this->fieldPrefix.'SEND_CONFIRMATION_CC',
				'displayName'	=>	'Send Confirmation Email Copy?',
				'required'		=>	true,
			)));
		}

		if (ss_optionExists('Shop Advanced Ordering')) {
			$this->fieldSet->addField(new FloatField(array(
				'name'			=>	$this->fieldPrefix.'SUPPLIER_DISCOUNT',
				'displayName'	=>	'Supplier Discount',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));		
		}
		
		if (ss_optionExists('Shop Acme Rockets')) {
			$this->fieldSet->addField(new FloatField(array(
				'name'			=>	$this->fieldPrefix.'LEVEL0_PERCENTAGE',
				'displayName'	=>	'Customer order percentage points',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));		
			$this->fieldSet->addField(new FloatField(array(
				'name'			=>	$this->fieldPrefix.'LEVEL1_PERCENTAGE',
				'displayName'	=>	'Direct referral percentage points',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));		
			$this->fieldSet->addField(new FloatField(array(
				'name'			=>	$this->fieldPrefix.'LEVEL2_PERCENTAGE',
				'displayName'	=>	'Level 2 referral percentage points',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));		
			$this->fieldSet->addField(new FloatField(array(
				'name'			=>	$this->fieldPrefix.'LEVEL3_PERCENTAGE',
				'displayName'	=>	'Level 3 referral percentage points',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));		
			$this->fieldSet->addField(new FloatField(array(
				'name'			=>	$this->fieldPrefix.'LEVEL4_PERCENTAGE',
				'displayName'	=>	'Level 4 referral percentage points',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));		
			$this->fieldSet->addField(new FloatField(array(
				'name'			=>	$this->fieldPrefix.'FREE_PRODUCT_LIMIT',
				'displayName'	=>	'Free product minimum product price',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));		
			$this->fieldSet->addField(new TextField(array(
				'name'			=>	$this->fieldPrefix.'FREE_PRODUCT_STOCK_CODE',
				'displayName'	=>	'Free product stock code',
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'20',	'maxLength'	=>	'30',					
			)));		
		}
		
		if (ss_optionExists('Shop Non-NZD Currencies')) {
			$this->fieldSet->addField( new SelectField (array(
				'name'			=>	$this->fieldPrefix.'ENTER_CURRENCY',
				'displayName'	=>	'Enter Currency',
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'multi'			=>	false,
				'size'	=>	'30',	'maxLength'	=>	'25',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	'CountryAdministration.Query',
				'linkQueryValueField'	=>	'cn_id',
				'linkQueryDisplayField'	=>	'cn_currency',
				'linkQueryParameters'	=>	array('FilterSQL'	=>	'AND cn_currency_code IS NOT NULL AND cn_currency_disabled IS NULL'),
			)));
			
			$this->fieldSet->addField( new TextField (array(
				'name'			=>	$this->fieldPrefix.'ENTER_CURRENCY_SYMBOL',
				'displayName' 	=>	'Enter Currency Symbol',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'4',	'maxLength'	=>	'25',
			)));
			
			$this->fieldSet->addField( new SelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.'ENTER_CURRENCY_SYMBOL_POS',
				'displayName'	=>	'Enter Currency Symbol Position',			
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'multi'			=>	false,			
				'options'	=>	array('before'	=>	'before', 'after'	=>	'after'),
			)));
			
			$this->fieldSet->addField( new SelectField (array(
				'name'			=>	$this->fieldPrefix.'DISPLAY_CURRENCY',
				'displayName'	=>	'Enter Currency',
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'multi'			=>	false,
				'size'	=>	'30',	'maxLength'	=>	'25',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	'CountryAdministration.Query',
				'linkQueryValueField'	=>	'cn_id',
				'linkQueryDisplayField'	=>	'cn_currency',
				'linkQueryParameters'	=>	array('FilterSQL'	=>	'AND cn_currency_code IS NOT NULL AND cn_currency_disabled IS NULL'),
			)));
			
			$this->fieldSet->addField( new TextField (array(
				'name'			=>	$this->fieldPrefix.'DISPLAY_CURRENCY_SYMBOL',
				'displayName' 	=>	'Enter Currency Symbol',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'4',	'maxLength'	=>	'25',
			)));
			
			$this->fieldSet->addField( new SelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.'DISPLAY_CURRENCY_SYMBOL_POS',
				'displayName'	=>	'Enter Currency Symbol Position',			
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'multi'			=>	false,			
				'options'	=>	array('before'	=>	'before', 'after'	=>	'after'),
			)));			
		}
	}
	
	
	
	
?>
