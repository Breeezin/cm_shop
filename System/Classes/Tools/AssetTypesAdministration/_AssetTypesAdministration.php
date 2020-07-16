<?php
requireOnceClass('Administration');

class AssetTypesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('AssetTypes');
	}


	function inputFilter() {
		parent::inputFilter();

		// Must be able to Administer something to access these Actions
		$result = new Request('Security.Authenticate',array(
			'Permission'	=>	'IsDeployer',
		));
	}


	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'AssetTypes',
			'singular'					=>	'Item Type',
			'plural'					=>	'Item Types',
			'tableName'					=>	'asset_types',
			'tablePrimaryKey'			=>	'at_id',
			'tableDisplayFields'		=>	array('at_display','at_name','at_limit', 'at_allow_search'),
			'tableOrderBy'				=>	array('at_display' => 'Display','at_name'=> 'Name','at_limit'=>'Limit', 'at_allow_search' => 'Allow Search'),
			'tableDisplayFieldTitles'	=>	array('Display','Name','Limit', 'Allow Search'),
		));

		/*$this->setParent(new ParentTable(array(
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
			'name'			=>	'at_display',
			'displayName'	=>	'Display Name',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	TRUE,
			'size'	=>	'30',	'maxLength'	=>	'50',
		)));

		$this->addField(new TextField (array(
			'name'			=>	'at_name',
			'displayName'	=>	'Plugin Name',
			'note'			=>	'The name before Asset in the as_type directory<br>e.g. dir name = CCCAsset<br>Plugin Name = CCC',
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	TRUE,
			'size'	=>	'30',	'maxLength'	=>	'50',
		)));

		$this->addField(new CheckBoxField(array(
			'name'		=>	'at_allow_search',
			'displayName'	=>	'Allow Search',
			'required'	=>	false,
		)));

		$this->addField(new IntegerField (array(
			'name'			=>	'at_limit',
			'displayName'	=>	'Limit',
			'note'			=>	'Leave blank for infinite',
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'10',	'maxLength'	=>	'8',
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
