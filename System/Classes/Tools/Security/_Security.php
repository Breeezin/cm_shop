<?php

class Security extends Plugin {

	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
//		$this->Plugin();
	}

	function login() {
		$this->display->title = 'Login';
		require('query_login.php');
		$this->useTemplate("{$this->ATTRIBUTES['LoginType']}Login", $data);
	}

	function redirect() {
		$this->display->title = 'Redirect';
		require('query_redirect.php');
		$this->useTemplate("Redirect", $data);
	}

	function createCookie() {
		$this->display->layout = 'None';
		require('query_createCookie.php');
	}	
	
	function logout() {
//		ss_DumpVar(debug_backtrace());
		$this->display->title = 'Logout';
		require('query_logout.php');
		$data = array();
		$this->useTemplate("Logout", $data);
	}
	
	function authenticate() {
		$this->display->layout = 'None';	
		$this->cache = 'Application';
		return include('query_authenticate.php');
	}
	
	function sudo() {
		global $sudo;
		$this->display->layout = 'None';
		if ($sudo === NULL) {
			$sudo = 0;
		}
		
		if (strcasecmp($this->ATTRIBUTES['Action'], 'Start') == 0) {
			$sudo++;
		} elseif ($sudo >= 1) {
			$sudo--;
		}
	}
	
	function forgotPassword() {
		$this->display->title = 'Password Reminder';
		$displayData = array();
		require('query_forgotPassword.php');
		$this->useTemplate("view_forgotPassword", $displayData);
	}
	
	function createAssetPermissions() {
		$this->display->layout = 'None';
		require('model_createAssetPermissions.php');	
	}
	
	function propagateAssetPermissions() {
		$this->display->layout = 'None';
		require('model_propagateAssetPermissions.php');	
	}	

	function createGroupAssetPermissions() {
		$this->display->layout = 'None';
		require('model_createGroupAssetPermissions.php');	
	}	

	function setCountry() {
		require('model_setCountry.php');	
	}

	function addToWishList() {
		require( 'model_addtowishlist2.php');
	}

	function suggested() {
		require( 'model_suggested.php');
	}

	function chooseShipping() {
		require('query_chooseShipping.php');
	}

	function exposeServices() {
		$prefix = 'Security';
		return array(
			"$prefix.Login"			=>		array('method' => 'login'),
            "$prefix.Redirect"			=>		array('method' => 'redirect'),
			"$prefix.CreateCookie"			=>		array('method' => 'createCookie'),
			"$prefix.Logout"		=>		array('method' => 'logout'),
			"$prefix.Authenticate"	=>		array('method' => 'authenticate'),
			"$prefix.Sudo"			=>		array('method' => 'sudo'),
			"$prefix.ForgotPassword"=>		array('method' => 'forgotPassword'),
			"$prefix.CreateAssetPermissions"	=>	array('method' => 'createAssetPermissions'),
			"$prefix.PropagateAssetPermissions"	=>	array('method' => 'propagateAssetPermissions'),
			"$prefix.CreateGroupAssetPermissions"	=>	array('method' => 'createGroupAssetPermissions'),
			"$prefix.SetCountry"	=>	array('method'	=>	'setCountry'),
			"$prefix.AddToWishList"	=>	array('method'	=>	'addToWishList'),
			"$prefix.Suggested"	=>	array('method'	=>	'suggested'),
			"$prefix.ChooseShipping"	=>	array('method'	=>	'chooseShipping'),
		);
	}
}

?>
