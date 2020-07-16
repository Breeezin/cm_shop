<?php
requireOnceClass('Administration');
class ShopSystem_ServiceInvoiceAdministration extends Administration {

	var $restrictedServiceInvoiceSQL = '';
	
	function exposeServices() {
		return array_merge(array(
			'shopsystem_service_invoice.Company'	=>	array('method'	=>	'queryCompany'),
			'shopsystem_service_invoice.Appears'		=>	array('method'	=>	'updateAppears'),
			'shopsystem_service_invoice.SettingEdit'	=>	array('method'	=>	'settingEdit'),
			'shopsystem_service_invoice.ListCompanies'	=>	array('method'	=>	'listCompanies'),
			'shopsystem_service_invoice.MarkPaid'	=>	array('method'	=>	'markPaid'),
			'shopsystem_service_invoice.ShowInvoice'	=>	array('method'	=>	'showInvoice'),
			'shopsystem_service_invoice.SendInvoice'	=>	array('method'	=>	'sendInvoice'),
			'ShopSystem_ServiceInvoiceAdministration.Auto' => array('method'	=>	'autoCreate' ),
			),
			Administration::exposeServicesUsing('ShopSystem_ServiceInvoice')
		);
	}
	function updateAppears() {
		require("model_updateAppears.php");
	}
	function settingEdit() {
		require("query_settingEdit.php");
		require("model_settingEdit.php");
		require("view_settingEdit.php");
	}

	function autoCreate() {
		require( "query_autocreate.php" );
	}

	function showInvoice()
	{
		if( array_key_exists( 'siv_id', $this->ATTRIBUTES)
			&& strlen( $this->ATTRIBUTES['siv_id'] ) )
		{
			require("showInvoice.php");
			location($this->ATTRIBUTES['BackURL']);
		}
	}

	function sendInvoice()
	{
		if( array_key_exists( 'siv_id', $this->ATTRIBUTES)
			&& strlen( $this->ATTRIBUTES['siv_id'] ) )
		{
			require("sendInvoice.php");
			location($this->ATTRIBUTES['BackURL']);
		}
	}

	function markPaid() {			
		if( array_key_exists( 'siv_id', $this->ATTRIBUTES)
			&& strlen( $this->ATTRIBUTES['siv_id'] ) )
		{
			$markPaid = Query( "Update shopsystem_service_invoice set siv_paid_date = curdate() where siv_id = ".$this->ATTRIBUTES['siv_id'] );

			require("sendInvoice.php");
			location($this->ATTRIBUTES['BackURL']);
		}
	}
	
	function entries() {	
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
	}	
	
	function listCompanies() {
		$Q_ServiceInvoice = Query("select * from shopsystem_service_company");
		return $Q_ServiceInvoice;
	}

	function __construct() {

		$displayFields = array('siv_id', 'siv_created_date','siv_paid_date', 'siv_sic_id', 'siv_to_sic_id');
		$displayFieldTitles = array('ID', 'Created Date','Paid Date', 'Invoice From', 'Invoice To');

		parent::__construct(array(
			'prefix'					=>	'ShopSystem_ServiceInvoice',
			'singular'					=>	'ServiceInvoice',
			'plural'					=>	'ServiceInvoices',
			'tableName'					=>	'shopsystem_service_invoice',
			'tablePrimaryKey'			=>	'siv_id',
			'tableDisplayFields'		=>	$displayFields,
			'tableDisplayFieldTitles'	=>	$displayFieldTitles ,
			'tableOrderBy'				=>	array('siv_created_date' => 'Created','siv_paid_date' => 'Paid'),
			'listManageOptions'			=>	array(
											"Mark Paid" => "index.php?act=shopsystem_service_invoice.MarkPaid&BreadCrumbs=[BreadCrumbs]&siv_id=[siv_id]&BackURL=[BackURL]",
											"Show Invoice" => "index.php?act=shopsystem_service_invoice.ShowInvoice&BreadCrumbs=[BreadCrumbs]&siv_id=[siv_id]&BackURL=[BackURL]",
											"Send Invoice" => "index.php?act=shopsystem_service_invoice.SendInvoice&BreadCrumbs=[BreadCrumbs]&siv_id=[siv_id]&BackURL=[BackURL]",
											),
			'tableSortOrderField'		=>	'ca_sort_order',
			));
		
		/*
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_service_invoice',
			'tablePrimaryKey'			=>	'ca_id',
			'linkField'					=>	'ca_parent_ca_id',
		)));
		*/
			$this->addField(new TextField (array(
				'name'			=>	'siv_external_reference',
				'displayName'	=>	'External Reference',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'30',
				'rows'	=>	'1',	'cols'		=>	'30',
				)));				

			$this->addField(new TextField (array(
				'name'			=>	'siv_notes',
				'displayName'	=>	'Notes',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
				)));			

			$this->addField(new SelectField (array(
				'name'			=>	'siv_sic_id',
				'displayName'	=>	'Service Company From:',
				'note'          =>  '', 
				'required'		=>	true,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'multi'			=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'25',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	'shopsystem_service_invoice.ListCompanies',
				'linkQueryValueField'	=>	'sic_id',
				'linkQueryDisplayField'	=>	'sic_name',	
				)));

			$this->addField(new SelectField (array(
				'name'			=>	'siv_to_sic_id',
				'displayName'	=>	'Service Company To:',
				'note'          =>  '', 
				'required'		=>	true,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'multi'			=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'25',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	'shopsystem_service_invoice.ListCompanies',
				'linkQueryValueField'	=>	'sic_id',
				'linkQueryDisplayField'	=>	'sic_name',	
				)));

			$dateFromValue = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"),  date("Y")));

			$this->addField(new DateField (array(
				'name'          =>  'siv_created_date',
				'displayName'   =>  'Created Date',
				'note'          =>  NULL,
				'required'      =>  TRUE,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				)));

/*
			$this->addField(new CheckboxField (array(
				'name'			=>	'ca_appears_in_menu',
				'displayName'	=>	'Appears In Menu',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,				
			)));
			
			$this->addField(new TextField (array(
				'name'			=>	'ca_window_title',
				'displayName'	=>	'Window Title',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'60',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));
*/

			$this->addField(new TextField (array(
				'name'			=>	'siv_1_text',
				'displayName'	=>	'Line 1 Invoice Work',
				'note'			=>	null,
				'required'		=>	TRUE,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'30',
				'rows'	=>	'1',	'cols'		=>	'30',
				)));				


			$this->addField(new DateField (array(
				'name'          =>  'siv_1_created_date',
				'displayName'   =>  'Line 1 Created Date',
				'note'          =>  NULL,
				'required'      =>  TRUE,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				)));

			$this->addField(new FloatField(array(
				'name'          =>  'siv_1_hours',
				'displayName'   =>  'Line 1 Qty',
				'required'      =>  TRUE,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_1_tax',
				'displayName'   =>  'Line 1 Tax',
				'required'      =>  TRUE,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_1_amount',
				'displayName'   =>  'Line 1 Amount',
				'required'      =>  TRUE,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new TextField (array(
				'name'			=>	'siv_2_text',
				'displayName'	=>	'Line 2 Invoice Work',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'30',
				'rows'	=>	'1',	'cols'		=>	'30',
				)));				


			$this->addField(new DateField (array(
				'name'          =>  'siv_2_created_date',
				'displayName'   =>  'Line 2 Created Date',
				'note'          =>  NULL,
				'required'      =>  false,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				)));

			$this->addField(new FloatField(array(
				'name'          =>  'siv_2_hours',
				'displayName'   =>  'Line 2 Qty',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_2_tax',
				'displayName'   =>  'Line 2 Tax',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_2_amount',
				'displayName'   =>  'Line 2 Amount',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new TextField (array(
				'name'			=>	'siv_3_text',
				'displayName'	=>	'Line 3 Invoice Work',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'30',
				'rows'	=>	'1',	'cols'		=>	'30',
				)));				

			$this->addField(new DateField (array(
				'name'          =>  'siv_3_created_date',
				'displayName'   =>  'Line 3 Created Date',
				'note'          =>  NULL,
				'required'      =>  false,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				)));

			$this->addField(new FloatField(array(
				'name'          =>  'siv_3_hours',
				'displayName'   =>  'Line 3 Qty',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_3_tax',
				'displayName'   =>  'Line 3 Tax',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_3_amount',
				'displayName'   =>  'Line 3 Amount',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new TextField (array(
				'name'			=>	'siv_4_text',
				'displayName'	=>	'Line 4 Invoice Work',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'30',
				'rows'	=>	'1',	'cols'		=>	'30',
				)));				

			$this->addField(new DateField (array(
				'name'          =>  'siv_4_created_date',
				'displayName'   =>  'Line 4 Created Date',
				'note'          =>  NULL,
				'required'      =>  false,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				)));

			$this->addField(new FloatField(array(
				'name'          =>  'siv_4_hours',
				'displayName'   =>  'Line 4 Qty',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_4_tax',
				'displayName'   =>  'Line 4 Tax',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_4_amount',
				'displayName'   =>  'Line 4 Amount',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new TextField (array(
				'name'			=>	'siv_5_text',
				'displayName'	=>	'Line 5 Invoice Work',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'30',
				'rows'	=>	'1',	'cols'		=>	'30',
				)));				

			$this->addField(new DateField (array(
				'name'          =>  'siv_5_created_date',
				'displayName'   =>  'Line 5 Created Date',
				'note'          =>  NULL,
				'required'      =>  false,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				)));

			$this->addField(new FloatField(array(
				'name'          =>  'siv_5_hours',
				'displayName'   =>  'Line 5 Qty',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_5_tax',
				'displayName'   =>  'Line 5 Tax',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_5_amount',
				'displayName'   =>  'Line 5 Amount',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new TextField (array(
				'name'			=>	'siv_6_text',
				'displayName'	=>	'Line 6 Invoice Work',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'30',
				'rows'	=>	'1',	'cols'		=>	'30',
				)));				

			$this->addField(new DateField (array(
				'name'          =>  'siv_6_created_date',
				'displayName'   =>  'Line 6 Created Date',
				'note'          =>  NULL,
				'required'      =>  false,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				)));

			$this->addField(new FloatField(array(
				'name'          =>  'siv_6_hours',
				'displayName'   =>  'Line 6 Qty',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_6_tax',
				'displayName'   =>  'Line 6 Tax',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

			$this->addField(new MoneyField(array(
				'name'          =>  'siv_6_amount',
				'displayName'   =>  'Line 6 Amount',
				'required'      =>  false,
				'size'  =>  10,
				'maxLength' =>  10,
			)));

	}

}
?>
