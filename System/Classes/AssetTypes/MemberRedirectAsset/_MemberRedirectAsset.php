<?php

requireOnceClass('AssetTypes');
requireOnceClass('FieldSet');

class MemberRedirectAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_LINK_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	function display(&$asset) {
        $user = ss_getUser();
        // ss_DumpVar($user);
        //  guest or unspecified
		ss_paramKey($asset->cereal,$this->fieldPrefix.'URL');
		ss_paramKey($asset->cereal,$this->fieldPrefix.'RELATIVE',0);

        // specified groups, not including Guests
	    $temp = new Request("Security.Sudo",array('Action'=>'start'));
        $user_groups = new Request('UserGroupsAdministration.Query', array());
	    $temp = new Request("Security.Sudo",array('Action'=>'stop'));
        
        foreach ($user_groups->value->rows as $row) {
            //ss_DumpVar($row);
            // does this person belong to the group
            if (array_key_exists($row['ug_id'], $user['user_groups']) ) {
		        ss_paramKey($asset->cereal,$this->fieldPrefix.'URL'.$row['ug_id'],'');
		        ss_paramKey($asset->cereal,$this->fieldPrefix.'RELATIVE'.$row['ug_id'],'');
                // did we find one?
                if (strlen($asset->cereal[$this->fieldPrefix.'URL'.$row['ug_id']]) > 0) {
		            if ($asset->cereal[$this->fieldPrefix.'RELATIVE'.$row['ug_id']] == 1) {
			            locationRelative($asset->cereal[$this->fieldPrefix.'URL'.$row['ug_id']]);
                        // ss_DumpVarDie($asset->cereal);
		            } else {
			            location($asset->cereal[$this->fieldPrefix.'URL'.$row['ug_id']]);
                        // ss_DumpVarDie($asset->cereal);
                    }	
                }
            }
        }      
		
		// not found use guests value
		if ($asset->cereal[$this->fieldPrefix.'RELATIVE'] == 1) {
		    locationRelative($asset->cereal[$this->fieldPrefix.'URL']);
            // ss_DumpVarDie($asset->cereal,'Guest');
   		} else {
			location($asset->cereal[$this->fieldPrefix.'URL']);
            // ss_DumpVarDie($asset->cereal,'Guest');
		}	
	}
	
	function embed(&$asset) {
		$this->display($asset);
	}

	function properties(&$asset) {
		require('view_properties.php');
	}
	
	function defineFields(&$asset) {
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
		                   
		$this->fieldSet->addField(new TextField (array(
					'name'			=>	$this->fieldPrefix.'URL',
					'displayName'	=>	'Target',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'60',	'maxLength'	=>	'255',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));

		$this->fieldSet->addField(new CheckBoxField (array(
					'name'			=>	$this->fieldPrefix.'RELATIVE',
					'displayName'	=>	'Relative',
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
              
              
        // ss_DumpVar($user_groups);
        $user_groups = new Request('UserGroupsAdministration.Query', array());
        foreach ($user_groups->value->rows as $row) {
		    $this->fieldSet->addField(new TextField (array(
					    'name'			=>	$this->fieldPrefix.'URL'.$row['ug_id'],
					    'displayName'	=>	'Target',
					    'note'			=>	NULL,
					    'required'		=>	FALSE,
					    'verify'		=>	FALSE,
					    'unique'		=>	FALSE,
					    'size'	=>	'60',	'maxLength'	=>	'255',
					    'rows'	=>	'6',	'cols'		=>	'40',
					    'linkQueryAction'	=>	NULL,
					    'linkQueryValueField'	=>	NULL,
					    'linkQueryDisplayField'	=>	NULL,
					    'Directory' => "Custom/ContentStore/Layouts/Images/",
		    )));

		    $this->fieldSet->addField(new CheckBoxField (array(
					    'name'			=>	$this->fieldPrefix.'RELATIVE'.$row['ug_id'],
					    'displayName'	=>	'Relative',
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
        }
        
        
		
	}
	
	function edit(&$asset) {
		print "<P>Enter the URL for Guests and Unspecified Groups to \"point to\" :</P><P>".$this->fieldSet->fields[$this->fieldPrefix.'URL']->display(FALSE,'AssetForm')."</P>";
		print "<P>If the above URL is relative to the root of your website, please tick this box: ".$this->fieldSet->fields[$this->fieldPrefix.'RELATIVE']->display(FALSE,'AssetForm')."</P>";
        $user_groups = new Request('UserGroupsAdministration.Query', array());
        // ss_DumpVar($user_groups);
        print "<table width=95%>";
        foreach ($user_groups->value->rows as $row) {
		    print "<tr><td>{$row['ug_name']} to \"go to\" : </td><td>".$this->fieldSet->fields[$this->fieldPrefix.'URL'.$row['ug_id']]->display(FALSE,'AssetForm');
		    print "</td><td nowrap> Relative To Root : ".$this->fieldSet->fields[$this->fieldPrefix.'RELATIVE'.$row['ug_id']]->display(FALSE,'AssetForm')."</td></tr>";
        }
        
        print "</table>";
	}
	
	
}

/*
: request Object
(
    [value] => fakequery Object
        (
            [rows] => Array
                (
                    [0] => Array
                        (
                            [ug_id] => 1
                            [ug_name] => Administrators
                            [ug_mailing_list] => 0
                            [ug_uuid] => ADMINISTRATORS
                            [ug_reviewer] => 
                            [UserCount] => 5
                        )

*/
?>
