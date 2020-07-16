<?php
requireOnceClass('Administration');

class SupplierInvoices extends Administration {

	var $prefix = 'SupplierInvoices';
	var $singular = 'Supplier Invoice';
	var $plural = 'Supplier Invoices';
	var $tableName = 'supplier_invoice';
	var $tablePrimaryKey = 'sin_id';
	var $parentTable = null;
	var $tableAssetLink = null;
	var $assetLink = null;
//	var $tableDisplayFieldTitles = array('Supplier', 'Invoice num', 'Date', 'Currency');
//	var $tableDisplayFields = array('sin_sp_id', 'sin_invoice_number', 'sin_invoice_date', 'sin_entered_currency');
	var $tableTimeStamp = null;
	var $fields = array();
	
	function __construct() {		

		$options = array("Print" => "index.php?act=SupplierInvoicesAdministration.Show&BreadCrumbs=[BreadCrumbs]&sin_id=[sin_id]&BackURL=[BackURL]&as_id=[as_id]",);

		//$this->Administration(array(
		parent::__construct( array(
			'prefix'					=>	'SupplierInvoices',
			'singular'					=>	'Supplier Invoice',
			'plural'					=>	'Supplier Invoices',
			'tableName'					=>	'supplier_invoice',
			'tablePrimaryKey'			=>	'sin_id',
			'tableSearchFields'			=>	array('sin_id', ),
			'tableDisplayFields'        => array('sin_id', 'sin_sp_id', 'sin_invoice_number', 'sin_invoice_date', 'sin_entered_currency', 'sin_dest_ve_id', 'sin_from_cn_id', 'sin_customs_reference'),
			'tableDisplayFieldTitles'	=>	array('Invoice Identifier', 'Supplier ID', 'Invoice Number', 'Date', 'Currency', 'Dest vendor', 'Country', 'Customs Ref'),
			'tableOrderBy'				=>	array('sin_id' => 'Supplier Invoice Ident', 'sin_invoice_date' => 'Date', ),			
			'listManageOptions'			=>	$options,
		));

		$this->addLinkedTable(new LinkedTable(array(
			'tableName'	=>	'supplier_invoice_line',
			'ourKey'	=>	'sil_sin_id',
			'uniqueID'		=>	'sil_id',
		)));		

        $t = new SelectField (array(
            'name'            =>    'sin_sp_id',
            'displayName'    =>    'Supplier Ident',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from supplier',
			'linkQueryValueField'	=>	'sp_id',
			'linkQueryDisplayField'	=>	array( 'sp_id', 'sp_name' ),
        ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'sin_invoice_number',
			'displayName'	=>	'Supplier Invoice Number',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'54',
		));
		$this->addField( $t );

		$t = new DateField (array(
				'name'          =>  'sin_invoice_date',
				'displayName'   =>  'Invoice Date',
				'note'          =>  NULL,
				'required'      =>  TRUE,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				));
		$this->addField( $t );

		$t = new DateField (array(
				'name'          =>  'sin_instock_date',
				'displayName'   =>  'Date product put in stock',
				'note'          =>  NULL,
				'required'      =>  FALSE,
				'verify'        =>  FALSE,
				'defaultValue'  =>  'Now',
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'sin_entered_currency',
			'displayName'	=>	'Invoice Items Currency',
			'note'			=>	null,
            'required'        =>    true,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'3',	'maxLength'	=>	'3',
		));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'sin_discount',
                'displayName'    =>    'Invoice Supplier Discount',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'sin_fixed_costs',
                'displayName'    =>    'Fixed Costs on Invoice (Freight, Commission etc)',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'sin_paid_currency',
			'displayName'	=>	'Invoice Paid Currency',
			'note'			=>	null,
            'required'        =>    true,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'3',	'maxLength'	=>	'3',
		));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'sin_paid_amount',
                'displayName'    =>    'Total Paid in above currency',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

        $t = new SelectField (array(
            'name'            =>    'sin_dest_ve_id',
            'displayName'    =>    'Destination vendor',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from vendor',
			'linkQueryValueField'	=>	've_id',
			'linkQueryDisplayField'	=>	array( 've_id', 've_name' ),
        ));
		$this->addField( $t );

        $t = new SelectField (array(
            'name'            =>    'sin_from_cn_id',
            'displayName'    =>    'Shipped from Country',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from countries',
			'linkQueryValueField'	=>	'cn_id',
			'linkQueryDisplayField'	=>	array( 'cn_id', 'cn_name' ),
        ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'sin_customs_reference',
			'displayName'	=>	'Customs Reference Number',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'54',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'sin_forwarder_name',
			'displayName'	=>	'Forwarder Name',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'54',
		));
		$this->addField( $t );

        $t = new SelectField (array(
            'name'            =>    'sin_product_sort',
            'displayName'    =>    'Preferred sort',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'tableName' =>  'supplier_invoice',
			'enumField' =>	true,
        ));
		$this->addField( $t );

	}

	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';
		// Must be able to Administer something to access these Actions
			
		$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'RestrictedAdmin',
		));
		
	}	

	function entries() {	
		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES))
			$this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
	}

	function edit() {		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES))
			$this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];		
		if (array_key_exists('as_id',$this->ATTRIBUTES))
			$this->assetLink = $this->ATTRIBUTES['as_id'];

		require('EditAction.php');		
		require('EditDisplay.php'); 
	}

	function show() {
		require('query_show.php');		
		require('view_show.php');				
	}	

	function create() {	
		//ss_DumpVarDie($this, "create");	
		ss_log_message( "create()" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		if ($this->parentTable !== null and array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) $this->parentKey = $this->ATTRIBUTES[$this->parentTable->linkField];
		if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];
		require('CreateAction.php');			
		require('CreateDisplay.php');			
	}

	function exposeServices() {
		$prefix = 'SupplierInvoices';
		return array_merge(
			array( "{$prefix}Administration.Show"	=>			array('method' => 'show'),),
			Administration::exposeServicesUsing($prefix)
			);
	}
}

?>
