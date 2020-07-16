<?php
requireOnceClass('Administration');
class UserSpecificPagesAdministration extends Administration {

	function exposeServices() {		
		return	Administration::exposeServicesUsing('UserSpecificPages');		
	}
	
	function query($params = array()) {
		$params['FilterSQL'] = ' AND pag_us_id = us_id';
		$params['FilterTablesSQL'] = 'users';
		return parent::query($params);
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
		
		//$this->Administration(array(
		parent::__construct(array(
			'prefix'					=>	'UserSpecificPages',
			'singular'					=>	'User Specific Page',
			'plural'					=>	'User Specific Pages',
			'tableName'					=>	'user_specific_page_pages',
			'tablePrimaryKey'			=>	'pag_id',
			'tableDisplayFields'		=>	array('us_first_name','us_last_name'),
			'tableDisplayFieldTitles'	=>	array('First Name','Last Name'),
			'tableOrderBy'				=>	array('us_first_name,us_last_name' => 'User'),
			'tableAssetLink'			=>	'pag_as_id',
			'assetLink'					=>	$assetID,
		));
		
/*
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));
*/

		$nf = new SelectField (array(
			'name'			=>	'pag_us_id',
			'displayName'	=>	'User',
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	true,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	'UsersAdministration.Query',
			'linkQueryValueField'	=>	'us_id',
			'linkQueryDisplayField'	=>	array('us_first_name','us_last_name','us_email'),
			'linkQueryDisplayFieldDelimiters'	=>	array(' ',' - '),
		));
		$this->addField( $nf );

		$this->addField(new HTMLMemoField2 (array(
			'name'			=>	'pag_content',
			'displayName'	=>	'Content',
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'width'	=>	'document.body.clientWidth-185',
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
