<?php

requireOnceClass('Administration');
class TestAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('Test');
	}

	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'Test',
			'singular'					=>	'Test',
			'plural'					=>	'Tests',
			'tableName'					=>	'Test',
			'tablePrimaryKey'			=>	'te_id',
			'tableDisplayFields'		=>	array('TeString'),
			'tableOrderBy'				=>	array('TeString'=>'test'),
		));
		
/*		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));*/

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
			'name'			=>	'TeString',
			'displayName'	=>	'String',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'100',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

/*		$this->addField(new CheckBoxField (array(
			'name'			=>	'ug_mailing_list',
			'displayName'	=>	'Mailing List',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'25',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));*/


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
