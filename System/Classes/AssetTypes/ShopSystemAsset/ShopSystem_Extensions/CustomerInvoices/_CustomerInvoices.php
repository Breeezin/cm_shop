<?php
requireOnceClass('Administration');

class CustomerInvoices extends Administration {

	var $prefix = 'CustomerInvoices';
	var $singular = 'Customer Invoice';
	var $plural = 'Customer Invoices';
	var $tableName = 'customer_invoice';
	var $tablePrimaryKey = 'cin_id';
	var $parentTable = null;
	var $tableAssetLink = null;
	var $assetLink = null;
	var $tableTimeStamp = null;
	var $fields = array();
	
	function __construct() {		

		$options = array("Print" => "index.php?act=CustomerInvoicesAdministration.Show&BreadCrumbs=[BreadCrumbs]&cin_id=[cin_id]&BackURL=[BackURL]&as_id=[as_id]",);

		//$this->Administration(array(
		parent::__construct( array(
			'prefix'					=>	'CustomerInvoices',
			'singular'					=>	'Customer Invoice',
			'plural'					=>	'Customer Invoices',
			'tableName'					=>	'customer_invoice',
			'tablePrimaryKey'			=>	'cin_id',
			'tableSearchFields'			=>	array('cin_id', ),
			'tableDisplayFields'        => array('cin_id', 'cin_cp_id', 'cin_invoice_number', 'cin_invoice_date', 'cin_paid_currency', 'cin_dest_ve_id', 'cin_from_cn_id', 'cin_customs_reference'),
			'tableDisplayFieldTitles'	=>	array('Invoice Identifier', 'Customer ID', 'Invoice Number', 'Date', 'Currency', 'Dest vendor', 'Country', 'Customs Ref'),
			'tableOrderBy'				=>	array('cin_id' => 'Customer Invoice Ident', 'cin_invoice_date' => 'Date', ),			
			'listManageOptions'			=>	$options,
		));

		$this->addLinkedTable(new LinkedTable(array(
			'tableName'	=>	'customer_invoice_line',
			'ourKey'	=>	'cil_cin_id',
			'uniqueID'		=>	'cil_id',
		)));		

        $t = new SelectField (array(
            'name'            =>    'cin_cp_id',
            'displayName'    =>    'Customer Ident',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from customer',
			'linkQueryValueField'	=>	'cp_id',
			'linkQueryDisplayField'	=>	array( 'cp_id', 'cp_name' ),
        ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'cin_invoice_description',
			'displayName'	=>	'Customer Invoice Description',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new DateField (array(
				'name'          =>  'cin_invoice_date',
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
				'name'          =>  'cin_paid_date',
				'displayName'   =>  'Paid Date',
				'note'          =>  NULL,
				'required'      =>  FALSE,
				'verify'        =>  FALSE,
				'unique'        =>  FALSE,
				'showCalendar'  =>  TRUE,
				'size'  =>  '8',    'maxLength' =>  '10',
				));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'cin_paid_currency',
			'displayName'	=>	'Invoice Currency',
			'note'			=>	null,
            'required'        =>    true,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'3',	'maxLength'	=>	'3',
		));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'cin_paid_amount',
                'displayName'    =>    'Invoice Paid Total',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'cin_discount',
                'displayName'    =>    'Invoice Customer Discount',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'cin_commission',
                'displayName'    =>    'Fixed Costs on Invoice (Freight, Commission etc)',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'cin_paid_currency',
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
                'name'            =>    'cin_paid_amount',
                'displayName'    =>    'Total Paid in above currency',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

        $t = new SelectField (array(
            'name'            =>    'cin_src_ve_id',
            'displayName'    =>    'Source vendor',
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
            'name'            =>    'cin_to_cn_id',
            'displayName'    =>    'Shipped to Country',
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
			'name'			=>	'cin_customs_reference',
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
			'name'			=>	'cin_forwarder_name',
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
            'name'            =>    'cin_product_sort',
            'displayName'    =>    'Preferred sort',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'tableName' =>  'customer_invoice',
			'enumField' =>	true,
        ));
		$this->addField( $t );

		$this->addField(new SelectField (array(
				'name'			=>	'cin_invoice_finished',
				'displayName'	=>	'Invoice is finished and subtracted from stock',
				'tableName' =>  'customer_invoice',
				'note'			=>	NULL,
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	false,
				'enumField'		=>	true,
			)));	

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
		$prefix = 'CustomerInvoices';
		return array_merge(
			array( "{$prefix}Administration.Show"	=>			array('method' => 'show'),),
			Administration::exposeServicesUsing($prefix)
			);
	}
}

?>
