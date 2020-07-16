<?php
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());

	if( !array_key_exists('User', $_SESSION )
	 || !array_key_exists('us_email', $_SESSION['User'] )
	 || ( strlen( $_SESSION['User']['us_email'] ) == 0 ) )
	{
		// User isn't a member...
		$login = new Request('Security.Login',array(
			'BackURL'	=>	$assetPath,
			'NoHusk'	=>	1,
		));
		$type = "";
		$data = array(
			'EditableContent'	=>	ss_parseText($asset->cereal[$this->fieldPrefix.'LOGIN_CONTENT']),
			'BackURL'			=>	$assetPath,
			'Type'				=>	$type,
			'LoginForm'			=>	$login->display,
		);
		$this->useTemplate('LoginService',$data);
		return;
	}

	if( array_key_exists('User', $_SESSION )
	 && array_key_exists('us_password', $_SESSION['User'] )
	 && ( strlen( $_SESSION['User']['us_password'] ) > 0 ) )
	{

		ss_paramKey($asset->cereal,$this->fieldPrefix.'LAYOUT', '');
		if (strlen($asset->cereal[$this->fieldPrefix.'LAYOUT'])) {
			$asset->display->layout = $asset->cereal[$this->fieldPrefix.'LAYOUT'];
		}
		
		$editableContent = ss_parseText($asset->cereal[$this->fieldPrefix.'WELCOME_CONTENT']);
		$editableContent = stri_replace('[first_name]',ss_HTMLEditFormat(ss_getFirstName()),$editableContent);
		$editableContent = stri_replace('[last_name]',ss_HTMLEditFormat(ss_getLastName()),$editableContent);
		$usID = ss_getUserID();
		$tempE = null;

		if( array_key_exists('User', $_SESSION )
		  and array_key_exists('us_id', $_SESSION['User'] ) )
		{
			if( !array_key_exists('us_token', $_SESSION['User'] )
			  OR $_SESSION['User']['us_token'] == NULL
			  OR strlen( $_SESSION['User']['us_token'] ) == 0 )
			{

				srand( $usID );
				ss_setUserToken( md5( rand() ) );
			}
		}

		ss_login($usID,$tempE);
		$userDetail = ss_getUser();

		$data = array(
			'EditableContent'	=>	$editableContent,
			'AssetPath'			=>	$assetPath,
		);

		$this->useTemplate('WishList',$data);
		return;
	}
	else
	{
		$nothing = query("select NULL");
		$data = array(
			'AssetPath'			=>	$assetPath,
		);

		$this->useTemplate('WishList',$data);
		return;

	}


?>
