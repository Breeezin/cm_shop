<?php
requireOnceClass('Administration');
class TestimonialsAdministration extends Administration {

	function exposeServices() {		
		return	Administration::exposeServicesUsing('Testimonials');		
	}
	
	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			} else if (array_key_exists("assetLink", $_REQUEST)) {
				$assetID = $_REQUEST['assetLink'];			
			}			
		}

		parent::__construct(array(
			'prefix'					=>	'Testimonials',
			'singular'					=>	'Testimonial',
			'plural'					=>	'Testimonials',
			'tableName'					=>	'testimonial_testimonials',
			'tablePrimaryKey'			=>	'te_id',
			'tableDisplayFields'		=>	array('te_date', 'te_customer','te_text'),
			'tableDisplayFieldTitles'	=>	array('Date', 'Customer', 'Testimonial'),
			'tableOrderBy'				=>	array('te_customer' => 'Customer','te_date'=>'Date'),
			'tableAssetLink'			=>	'te_as_id',
			'assetLink'					=>	$assetID,
		));
		
/*
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));
*/
		
		$this->addField(new MemoField (array(
			'name'			=>	'te_text',
			'displayName'	=>	'Testimonal',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'2550',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));
		

		$this->addField(new TextField (array(
			'name'			=>	'te_customer',
			'displayName'	=>	'Customer Name',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'width'	=>	'document.body.clientWidth-35',
		)));
				
		/*$directroy = ss_secretStoreForAsset($assetID,"NewsImages"); 
		//print($directroy);		
		$this->addField(new PopupUniqueImageField (array(
			'name'			=>	'nei_image',
			'displayName'	=>	'News Image',
			'directory'		=>	$directroy,
			'preview'		=>	true,
		)));					*/
		
		$this->addField(new DateField (array(
			'name'			=>	'te_date',
			'displayName'	=>	'Date',
			'note'			=>	NULL,			
			'required'		=>	false,
			'class'			=>	'formborder',
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'defaultValue'	=>	'Now',
			'showCalendar'	=> 	TRUE,
			'size'	=>	'6',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'40',			
		)));

		
/*		$this->addChild(new ChildTable (array(
			'prefix'					=>	'assets',
			'plural'					=>	'Sub assets',
			'singular'					=>	'Sub Asset',
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id'
		)));*/
		
	}

}
?>
