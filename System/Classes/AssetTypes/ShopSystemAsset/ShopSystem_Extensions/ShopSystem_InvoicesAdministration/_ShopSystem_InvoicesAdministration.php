<?php
requireOnceClass('Administration');
class ShopSystem_InvoicesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_Invoices');
	}

	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}
		
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_Invoices',
			'singular'					=>	'Invoice',
			'plural'					=>	'Invoices',
			'tableName'					=>	'shopsystem_invoices',
			'tablePrimaryKey'			=>	'inv_id',
			'tableDisplayFields'		=>	array('in_or_id'),
			'tableDisplayFieldTitles'	=>	array('Order'),
			'tableOrderBy'				=>	array('in_or_id' => 'Default'),
			'tableAssetLink'			=>	'InAssetLink',
			'tableSortOrderField'		=>	'in_or_id',
			'backButtonText'			=>	'Return to Order',
		));
		
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_orders',
			'tablePrimaryKey'			=>	'or_id',
			'linkField'					=>	'in_or_id',
		)));

	/*	$this->addField(new TextField (array(
			'name'			=>	'in_document',
			'displayName'	=>	'Documento',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'Factura Bj&ouml;rck Bros. S.L.',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
		$this->addField(new TextField (array(
			'name'			=>	'in_sender_company',
			'displayName'	=>	'Empresa Remitente',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'Bjork Bros. S.L.U. Cif:B35702786',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			

		$this->addField(new TextField (array(
			'name'			=>	'in_sender_address',
			'displayName'	=>	'Direcci&oacute;n Bj&ouml;rck Bros.S.L.',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'C/Andres Perdomo s/n Edif.ZF M214, Las Palmas de GC 35008',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
		$this->addField(new TextField (array(
			'name'			=>	'in_sender_phone',
			'displayName'	=>	'Telefono',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'928 466366, Fax: 928 468656',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		*/
		$this->addField(new DateField (array(
			'name'			=>	'in_date',
			'displayName'	=>	'Fecha',
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'showCalendar'	=>	true,
		)));			
		
		$this->addField(new TextField (array(
			'name'			=>	'in_paymethod',
			'displayName'	=>	'Forma de Pago',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'Ingreso en Cuenta',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			

		$this->addField(new TextField (array(
			'name'			=>	'in_destination',
			'displayName'	=>	'Destinatario',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			

		$this->addField(new TextField (array(
			'name'			=>	'in_country',
			'displayName'	=>	'Pais',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
		$this->addField(new TextField (array(
			'name'			=>	'in_boxes',
			'displayName'	=>	'Numero de Cajas',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'1',
			'size'	=>	'10',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			

		$this->addField(new TextField (array(
			'name'			=>	'in_parcels',
			'displayName'	=>	'Numero de Paquetes',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'1',
			'size'	=>	'10',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
		$this->addField(new TextField (array(
			'name'			=>	'in_value',
			'displayName'	=>	'Peso',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'1 Kg',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
		$this->addField(new TextField (array(
			'name'			=>	'in_unit_value',
			'displayName'	=>	'Valor Unidad &euro;',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
		$this->addField(new TextField (array(
			'name'			=>	'in_units',
			'displayName'	=>	'Numero deUnidades (Acmes)',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'25',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			


		$this->addField(new TextField (array(
			'name'			=>	'in_origin',
			'displayName'	=>	'Peru',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
	
		$this->addField(new TextField (array(
			'name'			=>	'in_total_value',
			'displayName'	=>	'Valor Total',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'',
			'size'	=>	'10',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
	
	}

}
?>
