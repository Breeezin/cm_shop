<?php
requireOnceClass('Administration');

class CountriesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('Country');
	}

	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'Country',
			'singular'					=>	'Country',
			'plural'					=>	'countries',
			'tableName'					=>	'countries',
			'tablePrimaryKey'			=>	'cn_id',
			'tableDisplayFieldTitles'	=>	array('Country Not Allowed', 'Country','Two Code', 'Currency Not Allowed', 'No Shipping to this Country', 'Currency Code'),
			'tableDisplayFields'		=>	array('cn_disabled', 'cn_name','cn_two_code', 'cn_currency_disabled', 'cn_restrict_shipping', 'cn_currency_code'),
			'tableOrderBy'				=>	array('cn_name' => 'Country'),
		));

		$this->addField(new TextField (array(
			'name'			=>	'cn_redirect_url',
			'displayName'	=>	'Redirect customer to dedicated Website',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'40',
		)));	

		$this->addField(new CheckBoxField (array(
			'name'			=>	'cn_disabled',
			'displayName'	=>	'Do Not Allow Country',
			'note'			=>	"Tick if you do not want the country to appear in your country options",
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));	
		
		$this->addField(new TextField (array(
			'name'			=>	'cn_name',
			'displayName'	=>	'Country',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));	
		
		
		
		$this->addField(new TextField (array(
			'name'			=>	'cn_two_code',
			'displayName'	=>	'Two Code',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'10',	'maxLength'	=>	'2',		
		)));	
		
		$this->addField(new TextField (array(
			'name'			=>	'cn_three_code',
			'displayName'	=>	'Three Code',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'10',	'maxLength'	=>	'3',		
		)));	
		
		$this->addField(new CheckBoxField (array(
			'name'			=>	'cn_currency_disabled',
			'displayName'	=>	'Do Not Allow Currency',
			'note'			=>	"Tick if you do not want the country to appear in your currency options",
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new CheckBoxField (array(
			'name'			=>	'cn_restrict_shipping',
			'displayName'	=>	'No Shipping to this Country',
			'note'			=>	"Tick if you do not want the country to appear in your shipping country list",
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));	

		$this->addField(new TextField (array(
			'name'			=>	'cn_currency',
			'displayName'	=>	'Currency',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'127',		
		)));

		$this->addField(new TextField (array(
			'name'			=>	'cn_currency_code',
			'displayName'	=>	'Currency Code',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'10',	'maxLength'	=>	'5',		
		)));	
		
		$this->addField(new TextField (array(
			'name'			=>	'cn_currency_symbol',
			'displayName'	=>	'Currency Symbol',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'10',	'maxLength'	=>	'6',		
		)));	
	
		if (ss_optionExists('Restrict countries')) {		
			$this->addField(new CheckBoxField (array(			
				'name'			=>	'cn_disable_access',
				'displayName'	=>	'Restrict Site Access',
				'note'			=>	"Tick if you do not want visitors from this country to access your website. They will instead be displayed a site access error.",
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'size'	=>	'50',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	NULL,
				'linkQueryValueField'	=>	NULL,
				'linkQueryDisplayField'	=>	NULL,
			)));
			$this->addField(new TextField (array(			
				'name'			=>	'cn_disable_access_code',
				'displayName'	=>	'Secret Site Access Code',
				'note'			=>	"If you tick the \"Restrict Site Access\" for this country, please define a secret access code. Only allows alphanumeric characters",
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'size'	=>	'50',	'maxLength'	=>	'128',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	NULL,
				'linkQueryValueField'	=>	NULL,
				'linkQueryDisplayField'	=>	NULL,
			)));	
			array_push($this->tableDisplayFields,'cn_disable_access');			
			array_push($this->tableDisplayFieldTitles,'Site Access Restricted?');
		}

		$this->addChild(new ChildTable (array(
			'prefix'					=>	'country_states',
			'plural'					=>	'country_states',
			'singular'					=>	'State',
			'tableName'					=>	'country_states',
			'tablePrimaryKey'			=>	'sts_id',
			'linkField'					=>	'StCountryLink'
		)));
		
		$this->addField(new MemoField (array(
			'name'			=>	'cn_note',
			'displayName'	=>	'Country Note',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,			
			'rows'	=>	'6',	'cols'		=>	'40',
		)));

		$this->addField(new IntegerField (array(
			'name'			=>	'cn_tax_x100',
			'displayName'	=>	'Local Tax, percent times 100',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
		)));	

		$this->addField(new TextField (array(
			'name'			=>	'cn_warning',
			'displayName'	=>	'Price Warning Note',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'100',	'maxLength'	=>	'100',		
		)));	

		$this->addField(new TextField (array(
			'name'			=>	'cn_usps_ident',
			'displayName'	=>	'United country_states Parcel Service Ident',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'40',		
		)));

		$this->addField(new SelectField (array(
			'name'			=>	'cn_post_zone',
			'displayName'	=>	'Postal Zone',
			'tableName'		=>	'countries',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'enumField'		=>	true,
		)));	

		$this->addField(new SelectField (array(
			'name'			=>	'cn_shipping_tracking',
			'displayName'	=>	'Shipping Tracking',
			'tableName'		=>	'countries',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'enumField'		=>	true,
		)));	

		$this->addField(new IntegerField (array(
			'name'			=>	'cn_box_tracking_cost_x100',
			'displayName'	=>	'Per Box Tracking Cost (x100)',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
		)));	

		$this->addField(new TextField (array(
			'name'			=>	'cn_box_tracking_cost_currency',
			'displayName'	=>	'Currency for above tracking cost',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'3',	'maxLength'	=>	'3',		
		)));	

		$this->addField(new SelectField (array(
			'name'			=>	'cn_hold_status',
			'displayName'	=>	'Default Hold Status for new orders to this country',
			'tableName'		=>	'countries',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'enumField'		=>	true,
		)));	

		$this->addField(new IntegerField (array(
			'name'			=>	'cn_max_order_total',
			'displayName'	=>	'Max order total',
			'note'			=>	NULL,
			'required'		=>	true,
			'verify'		=>	FALSE,
			'unique'		=>	false,
		)));

		$this->addField(new HtmlMemoField2(array(
					'name'			=>	'cn_warning_email',
					'displayName'	=>	'Standby Email Content',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'60',	'maxLength'	=>	'1024',
					'rows'	=>	'6',	'cols'		=>	'60',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));

		$this->addField(new IntegerField (array(
			'name'			=>	'cn_max_order_boxes',
			'displayName'	=>	'Max number of boxes per order',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
		)));

		$this->addField(new IntegerField (array(
			'name'			=>	'cn_min_reorder_interval',
			'displayName'	=>	'Min number of days to allow non-receipted prior order',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
		)));

		$all_zones_q = query( "select * from sales_zone" );
		$all_zones = "<table border=1>";
		while( $zrow = $all_zones_q->fetchRow())
			$all_zones .= "<tr><td>{$zrow['sz_id']}</td><td>{$zrow['sz_name']}</td></tr>";
		$all_zones .= "</table>";

		$this->addField(new TextField (array(
			'name'			=>	'cn_sales_zones',
			'displayName'	=>	'Sales Zones for this country<br />Comma separated list<br />'.$all_zones,
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'64',	'maxLength'	=>	'64',
		)));	

		$this->addField(new IntegerField (array(
			'name'			=>	'cn_no_chargeback_count',
			'displayName'	=>	'Number of times new customer must use a no chargeback gateway',
			'note'			=>	'',
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
		)));

		$this->addField(new SelectField (array(
			'name'			=>	'cn_ship_via_distributor',
			'displayName'	=>	'Send to this country via which distributor',
			'tableName'		=>	'countries',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQuery'             =>  'select * from users where us_vendor_distributor IS NOT NULL',
			'linkQueryValueField'	=>	'us_id',
			'linkQueryDisplayField'	=>	"us_0_50A2",
		)));	

		$this->addField(new SelectField (array(
			'name'			=>	'cn_bypass_stock_control',
			'displayName'	=>	'Bypass Stock Control',
			'tableName'		=>	'countries',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'enumField'		=>	true,
		)));	

		$this->addField(new IntegerField (array(
			'name'			=>	'cn_shipping_penalty_min_total',
			'displayName'	=>	'Amount in EURO below which extra shipping is added',
			'note'			=>	'',
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
		)));

	}

}
?>
