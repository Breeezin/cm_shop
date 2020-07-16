<?php
requireOnceClass('Administration');
class ShopSystem_AbonosAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_Abonos');
	}

	function __construct() {
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_Abonos',
			'singular'					=>	'Counter Invoice',
			'plural'					=>	'Counter Invoices',
			'tableName'					=>	'ShopSystem_CounterInvoices',
			'tablePrimaryKey'			=>	'CoInID',
			'tableDisplayFields'		=>	array('CoInID'),
			'tableDisplayFieldTitles'	=>	array('Counter Invoice Number'),
			'tableOrderBy'				=>	array('CoInID' => 'Default'),
			'backButtonText'			=>	'Return to Order',
		));
		

	/*	$this->addField(new TextField (array(
			'name'			=>	'TrDoNodeDua',
			'displayName'	=>	'&#8470; de DUA',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			*/
		
		$this->addField(new FloatField (array(
			'name'			=>	'CoInTotal',
			'displayName'	=>	'Total',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			

		
		/*$this->addField(new TextField (array(
			'name'			=>	'TrDoElSolicitante',
			'displayName'	=>	'El Solicitante',
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'Ingreso en Cuenta',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			*/

		/*if ($assetID !== null) {
			$imgDir = ss_secretStoreForAsset($assetID,"TransitDocImages");
			$this->addField(new PopupUniqueImageField (array(
				'name'			=>	'TrDoEspacioparaSellodeEmpresa',
				'displayName'	=>	'Image',
				'directory'		=>	$imgDir,
				'preview'	=>	false,
				'note'		=>	'Leave blank for default image',
			)));
		}*/
		
	/*	$this->addField(new TextField (array(
			'name'			=>	'TrDoComosePide',
			'displayName'	=>	'Como se Pide',
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	'',
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
		$this->addField(new MemoField (array(
			'name'			=>	'TrDoEspacioparaselloaduanas',
			'displayName'	=>	'Espacio para sello aduanas',
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));	*/
	
	}

}
?>
