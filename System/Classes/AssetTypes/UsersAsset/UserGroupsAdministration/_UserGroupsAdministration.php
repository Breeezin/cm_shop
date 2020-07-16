<?php

requireOnceClass('Administration');
class UserGroupsAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('UserGroups');
	}

	function query($params = array()) {
		$query = parent::query(array_merge(array('Min'=>1),$params));
		
		if (is_object($query)) {
			$query->addColumn('UserCount');
			$counter = 0;
			while ($row = $query->fetchRow()) {
				$userCount = getRow("
					SELECT COUNT(*) AS UserCount FROM user_user_groups
					WHERE uug_ug_id = {$row['ug_id']}
				");
				$query->setCell('UserCount',$userCount['UserCount'],$counter++);
			}
		}
		return $query;
	}	
	
	function create() {
		$errors = array();
		if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
	
			$this->loadFieldValuesFromForm($this->ATTRIBUTES);
			
			// Validate and then write to the database
			$errors = $this->insert();

			// Return if no error messages were returned
			if (count($errors) == 0) {
				
				// Create the permissions for all assets and the new group
				$temp = new Request('Security.CreateGroupAssetPermissions',array());
				location($this->ATTRIBUTES['BackURL']);
			}
		} else {
			parent::create();	
		}
	}

	function entries() {
		if( ss_adminCapability( ADMIN_EDIT_USERS ) )
			parent::entries();
	}

	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'user_groups',
			'singular'					=>	'User Group',
			'plural'					=>	'User Groups',
			'tableName'					=>	'user_groups',
			'tablePrimaryKey'			=>	'ug_id',
			'tableDisplayFields'		=>	array('ug_id', 'ug_name','UserCount'),
			'tableOrderBy'				=>	array('ug_id' => 'Group ID'),
			'tableDisplayFieldTitles'	=>	array('Group ID','Group Name','User Count'),
            'tableSearchFields'			=>	array('ug_name'),
            'listManageOptions'			=>	array(
				'users'	=>	'index.php?act=UsersAdministration.List&FilterByUserGroup=[ug_id]&BreadCrumbs=[BreadCrumbs]',
			),
		));
		
		// These are used when deleting user groups
		$this->addLinkedTable(new LinkedTable(array(
			'tableName'	=>	'asset_user_groups',
			'ourKey'	=>	'uug_ug_id',
		)));
		$this->addLinkedTable(new LinkedTable(array(
			'tableName'	=>	'user_user_groups',
			'ourKey'	=>	'uug_ug_id',
		)));		
		
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
			'name'			=>	'ug_name',
			'displayName'	=>	'Name',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'50',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new CheckBoxField (array(
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
		)));

		if (ss_optionExists('Review Process')) {
			$this->tableDisplayFields[] = 'ug_reviewer';
			$this->tableDisplayFieldTitles[] = 'Can Review?';
			
			$this->addField(new CheckBoxField (array(
				'name'			=>	'ug_reviewer',
				'displayName'	=>	'Reviewer',
				'note'			=>	NULL,
				'required'		=>	FALSE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'25',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	NULL,
				'linkQueryValueField'	=>	NULL,
				'linkQueryDisplayField'	=>	NULL,
			)));
		}


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
