<?php
requireOnceClass('Administration');

class StatesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('States');
	}

	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'country_states',
			'singular'					=>	'State',
			'plural'					=>	'country_states',
			'tableName'					=>	'country_states',
			'tablePrimaryKey'			=>	'sts_id',
			'tableDisplayFieldTitles'	=>	array('State','Code'),
			'tableDisplayFields'		=>	array('StName','StCode'),
			'tableOrderBy'				=>	array('StName' => 'State'),
		));
		
		$this->addField(new TextField (array(
			'name'			=>	'StName',
			'displayName'	=>	'State',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'127',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));	
		
		
		
		$this->addField(new TextField (array(
			'name'			=>	'StCode',
			'displayName'	=>	'Code',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'10',	'maxLength'	=>	'50',		
		)));
			
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'countries',
			'tablePrimaryKey'			=>	'cn_id',
			'linkField'					=>	'StCountryLink',
			//'linkValue'					=>	$this->ATTRIBUTES['cfg_id'],
		)));
	}

}
?>
