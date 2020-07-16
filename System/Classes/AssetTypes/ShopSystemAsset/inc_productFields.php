<?php

    /*    This file is referenced absolutely in ProductsAdministration and 
        category products administration.. So if you copy the ShopSystem asset,
        those files will need to be updated with the new path for this file..    */

            //ss_DumpVar($pr_id);
        if ($pr_id === null) {
            
            if (array_key_exists('pr_id',$_REQUEST)) {
                $pr_id = $_REQUEST['pr_id'];
            } else {
                if (is_array($this->ATTRIBUTES)) {
                    if (array_key_exists('pr_id',$this->ATTRIBUTES)) {
                        $pr_id = $this->ATTRIBUTES['pr_id'];                
                    }
                }
            }
        }
        
        
        $this->addField(new TextField (array(
            'name'            =>    'pr_name',
            'displayName'    =>    'Name',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '40',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
        )));

		$this->addField(new SelectField (array(
				'name'			=>	'pr_is_service',
				'displayName'	=>	'Is an add-on service?',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/service.gif" alt="Is Service">',
			)));	

		$this->addField(new SelectField (array(
				'name'			=>	'pr_service_default',
				'displayName'	=>	'default state for add-on service',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/service.gif" alt="Is Service">',
			)));	

		$this->addField(new IntegerField (array(
			'name'            =>    'pr_service_exclude',
			'displayName'    =>    'Exclude this service ID if selected',
			'note'            =>    null,
			'required'        =>    false,
			'verify'        =>    false,
			'unique'        =>    false,
		)));


        $this->addField(new TextField (array(
            'name'            =>    'pr_window_title',
            'displayName'    =>    'Window Title',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '60',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
        )));
        
        $dataAssetID = ss_optionExists('Shop Product DataCollection');
        if ($dataAssetID) {
                
            $Q_DataCollection = getRow("SELECT * FROM assets WHERE as_id = ".$dataAssetID.".");
            $cereal = unserialize($Q_DataCollection['as_serialized']);                             
            ss_paramKey($cereal, "AST_DATABASE_FIELDS", '');                            
            ss_paramKey($cereal, "AST_DATABASE_SUBPAGE_CONTENT", '');                                                
            if (strlen($cereal['AST_DATABASE_FIELDS'])) {
                $dataFieldsArray = unserialize($cereal['AST_DATABASE_FIELDS']);
            } else {
                $dataFieldsArray = array();                    
            }
            $options = array();
                            
            if (count($dataFieldsArray)) {                        
                $Q_Options = query("
                        SELECT DaCoID, DaCo{$dataFieldsArray[0]['uuid']} 
                        FROM DataCollection_$dataAssetID 
                        WHERE DaCo{$dataFieldsArray[0]['uuid']} IS NOT NULL");
                                            
                while($option = $Q_Options->fetchRow()) {
                    $options[$option["DaCo{$dataFieldsArray[0]['uuid']}"]] = $option['DaCoID'];
                }                                                                
            }
            if (ss_optionExists('Multi Shop Product DataCollection')){
                $this->addField(new MultiSelectFromArrayField (array(
                    'name'            =>    'pr_data_collection_link',
                    'displayName'    =>    $Q_DataCollection['as_name'],
                    'note'            =>    null,
                    'required'        =>    false,
                    'verify'        =>    false,
                    'unique'        =>    false,
                    'options'        =>    $options,
                )));
            } else {
                $this->addField(new SelectFromArrayField (array(
                    'name'            =>    'pr_data_collection_link',
                    'displayName'    =>    $Q_DataCollection['as_name'],
                    'note'            =>    null,
                    'required'        =>    false,
                    'verify'        =>    false,
                    'unique'        =>    false,
                    'options'        =>    $options,
                )));
            }
        }



        if (ss_optionExists('Shop VIP Products')) {
            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_vip',
                'displayName'    =>    'VIPs Only?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/vip.gif" alt="VIP Only Product">',
            )));
        }        
        
        if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Products Offline')) {
            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_offline',
                'displayName'    =>    'Offline?',
                'note'            =>    'Offline products will not be shown on the website',
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/offline.gif" alt="Offline Product">',
            )));
        }            

        if (ss_optionExists('Restricted Shop Products')) {
            $this->addField(new CheckboxField (array(
                'name'            =>    'PrRestricted',
                'displayName'    =>    'Restricted?',
                'note'            =>    'Restricted products will not be shown in the shop, but can be linked to',
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/restricted.gif" alt="Restricted Product">',
            )));
        }

        if (ss_optionExists('Shop Gallery')) {
            $this->addField(new CheckboxField (array(
                'name'            =>    'PrGallery',
                'displayName'    =>    'Shop Gallery Product?',
                'note'            =>    'Only products with this box ticked will show in the gallery',
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/gallery.gif" alt="Gallery Product">',
            )));
        }


        if (ss_optionExists('Shop Acme Rockets')) {


            $this->addField(new SelectField (array(
                'name'            =>    'pr_type',
                'displayName'    =>    'Product Type',
                'note'            =>    null,
                'required'        =>    true,
                'verify'        =>    false,
                'unique'        =>    false,
                'size'    =>    '30',    'maxLength'    =>    '255',
                'rows'    =>    '3',    'cols'        =>    '40',
                'linkQuery'    =>    'select pt_id, pt_name from product_type order by pt_name',
                'linkQueryValueField'    =>    'pt_id',
                'linkQueryDisplayField'    =>    'pt_name',
            )));

            $this->addField(new FloatField (array(
                'name'            =>    'pr_thickness',
                'displayName'    =>    'Thickness (mm)',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_length',
                'displayName'    =>    'Length (mm)',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_sort_order',
                'displayName'    =>    'Sort Order',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
				'size'			=>	  3,
            )));


            $this->addField(new SelectField (array(
                'name'            =>    'pr_ve_id',
                'displayName'    =>    'vendor',
                'note'            =>    null,
                'required'        =>    true,
                'verify'        =>    false,
                'unique'        =>    false,
                'size'    =>    '30',    'maxLength'    =>    '255',
                'rows'    =>    '3',    'cols'        =>    '40',
                'linkQueryAction'    =>    'shopsystem_categories.Vendors',
                'linkQueryValueField'    =>    'VeID',
                'linkQueryDisplayField'    =>    'VeName',
            )));

            $this->addField(new SelectField (array(
                'name'            =>    'pr_sales_zone',
                'displayName'    =>    'Effective Sales Zone',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'size'    =>    '30',    'maxLength'    =>    '255',
                'rows'    =>    '3',    'cols'        =>    '40',
                'linkQuery'    =>    'select sz_id, sz_name from sales_zone order by sz_id',
                'linkQueryValueField'    =>    'sz_id',
                'linkQueryDisplayField'    =>    'sz_name',
            )));

            $this->addField(new SelectField (array(
                'name'            =>    'pr_authd_sales_zone',
                'displayName'    =>    'Effective Sales Zone for authenticated customers',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'size'    =>    '30',    'maxLength'    =>    '255',
                'rows'    =>    '3',    'cols'        =>    '40',
                'linkQuery'    =>    'select sz_id, sz_name from sales_zone order by sz_id',
                'linkQueryValueField'    =>    'sz_id',
                'linkQueryDisplayField'    =>    'sz_name',
            )));

            $this->addField(new SelectField (array(
                'name'            =>    'pr_specials_sales_zone',
                'displayName'    =>    'On Special Sales Zone',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'size'    =>    '30',    'maxLength'    =>    '255',
                'rows'    =>    '3',    'cols'        =>    '40',
                'linkQuery'    =>    'select sz_id, sz_name from sales_zone order by sz_id',
                'linkQueryValueField'    =>    'sz_id',
                'linkQueryDisplayField'    =>    'sz_name',
            )));

/*
            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_ve_id',
                'displayName'    =>    'Sell on behalf of another shop?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="External Product">',
            )));
*/
            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_add_gift',
                'displayName'    =>    'Add free gift?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="Free Gift">',
            )));

/*
            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_combo',
                'displayName'    =>    'Combo Product?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="Combo Product">',
            )));
*/
            $this->addField(new SelectField (array(
                'name'            =>    'pr_combo',
                'displayName'    =>    'Combo or Multipack?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'multi'            =>    FALSE,
                'size'    =>    '30',    'maxLength'    =>    '25',
                'rows'    =>    '6',    'cols'        =>    '40',
				'displayValues' => array( 1 => '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="Combo Product">',
											2 => '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/multipack.gif" alt="Multipack Product">' ),
                'linkQuery'    =>    'select "no" as id, NULL as val union select "Combo", 1 union select "Multipack", 2',
                'linkQueryValueField'    =>    'val',
                'linkQueryDisplayField'    =>    'id',
            )));

            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_points',
                'displayName'    =>    'Loyalty Points Product?',
                'note'            =>    'Tick this if the product can be purchased for free using loyalty program points',
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/points.gif" alt="Loyalty Points Product">',
            )));
			/*
            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_needs_extra_padding',
                'displayName'    =>    'Needs Extra Protection?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));
			*/

			$this->addField(new CheckboxField (array(
                'name'            =>    'pr_stock_graph',
                'displayName'    =>    'Display in stock graph?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="Graph Show">',
            )));

			$this->addField(new CheckboxField (array(
                'name'            =>    'pr_stock_warning',
                'displayName'    =>    'Emit low stock warnings?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="Warning Show">',
            )));

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_stock_lead_time',
                'displayName'    =>    'Order lead time in Days',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_stock_warning_level',
                'displayName'    =>    'Minimum Stock Warning Level',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

			$this->addField(new CheckboxField (array(
                'name'            =>    'pr_quote_shipping',
                'displayName'    =>    'Manually quote shipping for this item?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="Warning Show">',
            )));

			$this->addField(new CheckboxField (array(
                'name'            =>    'pr_add_watermark',
                'displayName'    =>    'Add per-site watermark to displayed images.',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/combo.gif" alt="Warning Show">',
            )));

			$this->addField(new TextField (array(
				'name'            =>    'pr_latest_batch',
				'displayName'    =>    'Latest batch code',
				'note'            =>    null,
				'required'        =>    false,
				'verify'        =>    false,
				'unique'        =>    false,
				'size'    =>    '60',    'maxLength'    =>    '100',
				'rows'    =>    '6',    'cols'        =>    '40',
			)));



        }        

        if (ss_optionExists('Shop Featured Products')) {
				$this->addField(new SelectField (array(
				'name'			=>	'pr_featured',
				'displayName'	=>	'Featured',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
			)));	
        }

		$this->addField(new CheckboxField (array(
			'name'            =>    'pr_outlet',
                'displayName'    =>    'Outlet',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/featured.gif" alt="Outlet Product">',
            )));

		$this->addField(new CheckboxField (array(
			'name'            =>    'pr_upsell',
                'displayName'    =>    'Upsell product',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'displayValueYes'    =>    '<img src="System/Classes/AssetTypes/ShopSystemAsset/Templates/Images/featured.gif" alt="Upsell Product">',
            )));

		$this->addField(new SelectField (array(
			'name'			=>	'pr_daily_special',
			'displayName'	=>	'Daily Special - Day',
			'tableName'		=>	'shopsystem_products',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'enumField'		=>	true,
		)));	

        if (ss_optionExists('Shop Donations')) {
            $this->addField(new CheckboxField (array(
                'name'            =>    'pr_donation',
                'displayName'    =>    'Donation Product?',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));
        }
        if (ss_optionExists('Shop Quick Order List')) {
            $this->addField(new SelectField (array(
                'name'            =>    'pr_qoc_id',
                'displayName'    =>    'Quick Order Category',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
                'size'    =>    '30',    'maxLength'    =>    '255',
                'rows'    =>    '6',    'cols'        =>    '40',
                'linkQueryAction'    =>    'ShopSystem_QuickOrderCategoriesAdministration.Query',
                'linkQueryValueField'    =>    'qoc_id',
                'linkQueryDisplayField'    =>    'qoc_name',
                'linkQueryParameters'    =>    array('as_id'=>$assetID),
            )));
        }
        if (ss_optionExists('Shop Products Out Of Stock')) {
            $this->addField(new CheckboxField (array(
                'name'            =>    'PrOutOfStock',
                'displayName'    =>    'Out of Stock?',
                'note'            =>    'Products marked \'out of stock\' cannot be added to the basket',
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));
        }
        if (ss_optionExists("Shop Products Block Individual countries")) {
            $options = array();
            foreach (ListToArray(ss_optionExists("Shop Products Block Individual countries"),":") as $countryDef) {
                $options[ListFirst($countryDef)] = ListLast($countryDef);
            }

            $this->addField(new MultiCheckFromArrayField (array(
                'name'            =>    'PrExcludeCountries',
                'displayName'    =>    'Exclude countries',
                'note'            =>    NULL,
                'required'        =>    FALSE,
                'verify'        =>    FALSE,
                'unique'        =>    FALSE,
                'size'    =>    '30',    'maxLength'    =>    '40',
                'rows'    =>    '6',    'cols'        =>    '40',
                'options'    =>    $options,
                'columns'    =>    6,
            )));

        }

        if (ss_optionExists("Shop Products Limited countries")) {
            $options = array();
            foreach (ListToArray(ss_optionExists("Shop Products Limited countries"),":") as $countryDef) {
                $options[ListFirst($countryDef)] = ListLast($countryDef);
            }

            $this->addField(new MultiCheckFromArrayField (array(
                'name'            =>    'PrIncludedCountries',
                'displayName'    =>    'Included countries',
                'note'            =>    NULL,
                'required'        =>    FALSE,
                'verify'        =>    FALSE,
                'unique'        =>    FALSE,
                'size'    =>    '30',    'maxLength'    =>    '40',
                'rows'    =>    '6',    'cols'        =>    '40',
                'options'    =>    $options,
                'columns'    =>    6,
            )));
        }

        $this->addField(new HTMLMemoField2 (array(
            'name'            =>    'pr_short',
            'displayName'    =>    'Short Description',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '50',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
            'width'    =>    'document.body.clientWidth*0.80',
            'height'    =>    300,
        )));
        $this->addField(new HTMLMemoField2 (array(
            'name'            =>    'pr_long',
            'displayName'    =>    'Long Description',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '50',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
            'width'    =>    'document.body.clientWidth*0.80',
            'height'    =>    300,
        )));
        $this->addField(new MemoField (array(
            'name'            =>    'pr_keywords',
            'displayName'    =>    'Keywords',
            'note'            =>    'The keywords entered here will be used when a customer uses the product search, but will not be displayed on the website.',
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '50',    'maxLength'    =>    '255',
            'rows'    =>    '5',    'cols'        =>    '50',
        )));
        
        if (ss_optionExists('Shop Acme Rockets')) {

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_customer_rating_count',
                'displayName'    =>    'Number of Customer Ratings',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

            $this->addField(new FloatField (array(
                'name'            =>    'pr_customer_rating',
                'displayName'    =>    'Customer Rating',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

			$this->addField(new SelectField (array(
				'name'			=>	'pr_shipping_method_us',
				'displayName'	=>	'Shipping Method - Intra US',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
			)));	

			$this->addField(new SelectField (array(
				'name'			=>	'pr_shipping_method_international',
				'displayName'	=>	'Shipping Method - International',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
			)));	

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_shipping_d1',
                'displayName'    =>    'Shipping Dimension Length (mm)',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_shipping_d2',
                'displayName'    =>    'Shipping Dimension Width (mm)',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

            $this->addField(new IntegerField (array(
                'name'            =>    'pr_shipping_d3',
                'displayName'    =>    'Shipping Dimension Height (mm)',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

			$this->addField(new SelectField (array(
				'name'			=>	'pr_shipping_usps',
				'displayName'	=>	'USPS Shipping Category',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
			)));	

            $this->addField(new FloatField (array(
                'name'            =>    'pr_cheapest_shipping_subsidy',
                'displayName'    =>    'Cheapest Shipping Subsidy (OsCommerce/Fedex)',
                'note'            =>    null,
                'required'        =>    true,
                'verify'        =>    false,
                'unique'        =>    false,
            )));

			$this->addField(new SelectField (array(
				'name'			=>	'pr_us_only',
				'displayName'	=>	'Ship to the US only',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
			)));	

			$this->addField(new SelectField (array(
				'name'			=>	'pr_sales_tax_exempt',
				'displayName'	=>	'Do not charge Sales Tax',
				'tableName'		=>	'shopsystem_products',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
			)));	

        }        

		$this->addField(new TextField (array(
			'name'            =>    'pr_location',
			'displayName'    =>    'Storage Location',
			'note'            =>    null,
			'required'        =>    false,
			'verify'        =>    false,
			'unique'        =>    false,
			'size'    =>    '60',    'maxLength'    =>    '100',
			'rows'    =>    '6',    'cols'        =>    '40',
		)));

        $this->addField(new MemoField (array(
            'name'            =>    'pr_admin_text',
            'displayName'    =>    'Administration Price Note',
            'note'            =>    NULL,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'default'        =>    null,
            'size'    =>    '100',    'maxLength'    =>    '255',
            'rows'    =>    '5',    'cols'        =>    '50',
        )));

        $imgDir = ss_secretStoreForAsset($assetID,"ProductImages");
        
        if (ss_optionExists('Shop Product Flash')) {
            $this->addField(new FlashFileField (array(
                'name'            =>    'pr_flash',
                'displayName'    =>    'Flash File',
                'directory'        =>    $imgDir,                
            )));
        }    
        
        if (ss_optionExists('Shop Product Features')) {
                $this->addField(new MultiCheckField (array(
                    'name'            =>    'Features',
                    'displayName'    =>    'Features',
                    'note'            =>    NULL,
                    'required'        =>    FALSE,
                    'verify'        =>    FALSE,
                    'unique'        =>    FALSE,
                    'size'    =>    '30',    'maxLength'    =>    '40',
                    'rows'    =>    '6',    'cols'        =>    '40',
                    'linkQueryAction'    =>    'shopsystem_features_administration.Query',
                    'linkQueryValueField'    =>    'fe_id',
                    'linkQueryDisplayField'    =>    'fe_name',
                    'linkTableName'        =>    'ShopSystem_ProductFeatures',
                    'linkTableOurKey'    =>    'ProductLink',
                    'linkTableTheirKey'    =>    'FeatureLink',
                )));    
        }

        if (ss_optionExists('Shop Discount Codes')) {
            $this->addField(new HiddenField (array(
                'name'            =>    'pr_dig_id',
                'displayName'    =>    'Discount Group',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,            
            )));
        }

        if ($assetID !== null) {
			$combo = false;
            if ($pr_id !== null) {
                //ss_DumpVarDie($pr_id);
                //ss_log_message_r($this,'this');
                //ss_log_message_r($_REQUEST,'request');
                $Q_Cat = getRow("
                    SELECT 
                        shopsystem_categories.* 
                    FROM 
                        shopsystem_categories, shopsystem_products 
                    WHERE shopsystem_products.pr_ca_id = shopsystem_categories.ca_id
                     AND shopsystem_products.pr_id IN ($pr_id)
                ");
				$fcombo = getField( "select pr_combo from shopsystem_products where pr_id = $pr_id" );
				$combo = ( $combo > 0 );

            } else {
                //ss_DumpVarDie($this);
                $ca_id = null;
                if ($this->parentKey == null) {
                    if (array_key_exists('pr_ca_id', $_REQUEST)) {
                        $ca_id = $_REQUEST['pr_ca_id'];
                    }                                
                } else {
                    $ca_id = $this->parentKey;
                }
                
                if ($ca_id !== null and strlen(trim($ca_id))) {
                    $Q_Cat = getRow("SELECT * FROM shopsystem_categories WHERE ca_id = $ca_id");
                } else {
                    $Q_Cat = array();
                    ss_paramKey($Q_Cat, "ca_attr_setting","");
                    ss_paramKey($Q_Cat, "ca_option_setting","");    
                }                
            }    
                
                    
            $fieldsArray = array();                            
            $displayFields = array();    

            $Q_Asset = getRow("SELECT * FROM assets WHERE as_id = $assetID");
            ss_paramKey($Q_Asset,'as_serialized',''); 
                
            if (strlen($Q_Asset['as_serialized'])) {
                $cereal = unserialize($Q_Asset['as_serialized']);                             
                ss_paramKey($cereal, "AST_SHOPSYSTEM_ATTRIBUTES", '');                            
                    
                if (strlen($cereal['AST_SHOPSYSTEM_ATTRIBUTES'])) {
                    $fieldsArray = unserialize($cereal['AST_SHOPSYSTEM_ATTRIBUTES']);
                } else {
                    $fieldsArray = array();                    
                }
            }
            foreach ($fieldsArray as $field) {
                ss_paramKey($field,'ShowTo','');
                ss_paramKey($field,'uuid','');
                if ($field['ShowTo'] == 'all' or strstr($Q_Cat['ca_attr_setting'],$field['uuid'])) {
                //if ($field['ShowTo'] == 'all') {
                    array_push($displayFields, $field);
                }
            }
            // add the customized attribute fields
            $this->addCustomizedFields($displayFields, "pr_");                                                
        
    
            $thumbSize    =    '100x100';
            
            if(!ss_optionExists("Shop Product No Thumbnail Images")) {
                $this->addField(new PopupUniqueImageField (array(
                    'name'            =>    'pr_image1_thumb',
                    'displayName'    =>    'Image 1 (Thumbnail)',
                    'directory'        =>    $imgDir,
                    'preview'        =>    $thumbSize,
                )));
            }
            
            $imageNum = 1;
            
            
            while($imageNum <= ss_optionExists('Shop Product Images')) {
                if(!ss_optionExists("Shop Product No Normal Images")) {
                    $this->addField(new PopupUniqueImageField (array(
                        'name'            =>    'pr_image'.$imageNum.'_normal',
                        'displayName'    =>    'Image '.$imageNum.' (Normal)',
                        'directory'        =>    $imgDir,
                        'preview'        =>    $thumbSize,
                    )));
                }
                if(!ss_optionExists("Shop Product No Large Images")) {
                    $this->addField(new PopupUniqueImageField (array(
                        'name'            =>    'pr_image'.$imageNum.'_large',
                        'displayName'    =>    'Image '.$imageNum.' (Large)',
                        'directory'        =>    $imgDir,
                        'preview'        =>    $thumbSize,
                    )));
                }
                if ($imageNum == ss_optionExists('Shop Product Images')) {
                    break;
                }
                $imageNum++;    
            }
            
            $rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
            $customFolder = $rootFolder.'Custom/Classes/ShopSystemAdministration';        
            $name = 'inc_extraFields.php';
            if (file_exists($customFolder.'/'.$name)) {            
                include($customFolder."/".$name);
            }
            //ss_DumpVarDie(ss_optionExists('Shop Product Images'));
                    
            $fieldsArray = array();                            
            $displayFields = array();                        
            $cereal = array();
            if (strlen($Q_Asset['as_serialized'])) {
                $cereal = unserialize($Q_Asset['as_serialized']);                             
                ss_paramKey($cereal, "AST_SHOPSYSTEM_PRODUCT_OPTIONS", '');                            
                
                if (strlen($cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS'])) {
                    $fieldsArray = unserialize($cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS']);
                } else {
                    $fieldsArray = array();                    
                }
            }
            
            /// hmm need to get this frmo the asset cereal
            $currency = $this->getCurrencyFromCereal($cereal,'ENTER');


            foreach ($fieldsArray as $field) {                
                    ss_paramKey($field,'uuid','');
                    ss_paramKey($field,'ShowTo','');
                    ss_paramKey($field,'options','');
                if ($field['ShowTo'] == 'all' or strstr($Q_Cat['ca_option_setting'],$field['uuid'])) {
                //if ($field['ShowTo'] == 'all') {
                    array_push($displayFields, $field);
                }
            }            
                        
            if (count($displayFields) && !$combo ) {
				$this->addField(new ProductExtendedOptionsField (array(
                        'name'            =>    'ExtendedOptions',
                        'displayName'    =>    'Product Options',
                        'options'        =>     $displayFields,    
                        'required'        =>    false,
                        'directory'        =>    $imgDir,                        
                        'countriesSetting'    => $currency,                
                        'linkTableName'    => "shopsystem_product_extended_options",                                                        
                        'linkTableOurKey'    => "pro_pr_id",            
                        'linkTableTheirKey'    => "pro_uuids",                    
                        //'linkQueryParameters'    => array('FilterSQL'=>'AND sfo_parent_uuid LIKE \''.$field['uuid'].'\''),            
                    )));
            } else {                
				$this->addField(new ProductExtendedOptionField (array(
                        'name'            =>    'ExtendedOptions',
                        'displayName'    =>    'Product Options',                        
                        'required'        =>    false,
                        'directory'        =>    $imgDir,
                        'currencySettings'    => $currency,                
                        'linkTableName'    => "shopsystem_product_extended_options",                                                        
                        'linkTableOurKey'    => "pro_pr_id",            
                        'linkTableTheirKey'    => "pro_uuids",                    
                        //'linkQueryParameters'    => array('FilterSQL'=>'AND sfo_parent_uuid LIKE \''.$field['uuid'].'\''),            
                    )));
            }                                        
        }


?>
