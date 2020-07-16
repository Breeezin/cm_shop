<?php
requireOnceClass('Administration');

class AssetsAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('Assets');
	}

	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'assets',
			'singular'					=>	'Asset',
			'plural'					=>	'assets',
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'tableDisplayFields'		=>	array('as_name', 'as_type'),
			'tableOrderBy'				=>	array('as_name' => 'Item Name'),
		));
		
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));

/*		A template field:
		$this->addField(new TextField (array(
			'name'			=>	'MemberFirstName',
			'displayName'	=>	'First Name',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'127',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));
*/		
		
		$this->addField(new TextField (array(
			'name'			=>	'as_name',
			'displayName'	=>	'Asset Name',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new TextField (array(
			'name'			=>	'as_type',
			'displayName'	=>	'Asset Type',
			'note'			=>	'(\'Page\', \'Folder\', \'Image\')',
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'25',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new MemoField (array(
			'name'			=>	'as_serialized',
			'displayName'	=>	'Asset CEREAL',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'127',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new CheckBoxField (array(
			'name'			=>	'as_appear_in_menus',
			'displayName'	=>	'Appears In Menus',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new IntegerField (array(
			'name'			=>	'as_sort_order',
			'displayName'	=>	'Asset Sort Order',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new MemoField (array(
			'name'			=>	'as_layout_serialized',
			'displayName'	=>	'Asset Layout CEREAL',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'10',	'maxLength'	=>	'20',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addChild(new ChildTable (array(
			'prefix'					=>	'assets',
			'plural'					=>	'Sub assets',
			'singular'					=>	'Sub Asset',
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id'
		)));
		
	}

}
?>
