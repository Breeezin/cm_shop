<?php

requireOnceClass('FieldSet');
class OwnOrders extends FieldSet {
	var $webPayConfig = null;
	var $children = array();
	var $prefix;
	var $plural;
	var $singular;
	var $payment;
	var $tableDeleteFlag = NULL;
	var $tableOrderBy = array();
	var $tableDisplayFields;
	var $tableDisplayFieldTitles = NULL;
	var $linkedTables = array();	
	var $tableSearchFields = array();
	
	
	function __construct($settings = array()) 
    {
		parent::__construct();
		
		$tableDisplayFields = array();
		$tableDisplayFieldTitles = array();

		array_push($tableDisplayFields,'tr_timestamp');
		array_push($tableDisplayFieldTitles,'Date');

		array_push($tableDisplayFields,'tr_id');
		array_push($tableDisplayFieldTitles,'Order ID');

		array_push($tableDisplayFields,'tr_currency_link');
		array_push($tableDisplayFieldTitles,'Order Total');

		if (ss_optionExists('Shop Orders Hide Charge Total') === false) 
        {
			array_push($tableDisplayFields,'tr_charge_total');
			array_push($tableDisplayFieldTitles,'Amount Charged');
		}
		
		$settings = array_merge($settings, array(
				'prefix'					=>	'OwnOrders',
				'singular'					=>	'Web Payment',
				'plural'					=>	'Web Payments',
				'tableName'					=>	'transactions',
				'tablePrimaryKey'			=>	'tr_id',
				'tableTimeStamp'			=>	'tr_timestamp',
				'tableDisplayFields'		=>	$tableDisplayFields,
				'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,
				'tableSearchFields'			=>	array('tr_id','tr_client_name'),
				'tableOrderBy'				=>	array('tr_reference' => 'Code'),
		));
		
		foreach($settings as $property => $value)
            $this->{$property} = $value;		
		
		// get web pay configuration, it will be used for which payment method to display etc
		
		//$this->webPayConfig = getRow("SELECT * FROM OwnOrdersConfiguration WHERE wpc_id = 1");				
		//$this->webPayConfig = getRow("SELECT * FROM NOOwnOrdersConfigurations WHERE wpc_id = 1");				
	}	
	
	function inputFilter($forAdmin = false) 
    {					
		// TODO , something here....
//		if ($forAdmin) 
 //       {
//			$this->display->layout = 'Administration';
//			$result = new Request('Security.Authenticate',array(
//				'Permission'	=>	'CanAdministerAtLeastOneAsset',
//			));
//		}		
	}

	
	function listPayment() 
    {
	/*
        if( $_SERVER['REQUEST_METHOD'] == 'POST' )        // this is a POST, translate it into a simple GET so refresh works properly
        {
            $newURL = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://") 
                        . array_shift(explode(':',$_SERVER['HTTP_HOST'])) 
                        . ($_SERVER['SERVER_PORT'] != 80?':'.$_SERVER['SERVER_PORT']:'') 
                        . $_SERVER['REQUEST_URI'];

			reset( $_POST );
            while (list($key,$val) = each($_POST))
                $newURL .= '&'.$key.'='.$val;

            header( 'Location:'.$newURL );
        }
        
		$this->inputFilter(true);
		*/
		require('query_list.php');
		require('view_list.php');		
	}
	
	function newOrder() 
    {
//		$this->inputFilter(true);
		require('new_order.php');		
	}

	function exposeServices() 
    {
		$prefix = 'OwnOrders';
		
		return array(
			"{$prefix}Administration.List"		=>		array('method' => 'listPayment'),			
			"{$prefix}Administration.NewOrder"	=>		array('method' => 'newOrder'),			
		);
	}
}
?>
