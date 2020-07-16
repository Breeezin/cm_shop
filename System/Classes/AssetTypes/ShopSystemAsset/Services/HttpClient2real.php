<?php
/*
 * This program requires Request.php file, which is a part
 * of pear package. It can be downloaded from the given website
 * http://pear.php.net/package/HTTP_Request/
 */
require_once '/usr/share/php/HTTP/Request.php';

//Class HttpClient2real

class HttpClient2real {

	//URI of the API
	// protected $URI = 'https://www.badcustomer.com/api/';

	//HTTP_METHOD use is POST
	//HTTP_METHOD = 'POST';

	var $client;
	var $secretKey;
	var $params = array();

	function HttpClient2real($secretKey)
	{
		$this->secretKey = $secretKey;
	}

	function call($method, $args, $mode)
	{

		//$request = new HTTP_Request($this->URI . '/' . $method);
		$request = new HTTP_Request('https://www.badcustomer.com/api//'.$method);
		$request->setMethod(HTTP_REQUEST_METHOD_POST);
		$args['secretkey'] = $this->secretKey;
		$args['mode'] = $mode;
		foreach ($args as $key=>$val)
		$request->addPostData($key, $val);
		$request->addPostData('auth', $this->signArgs($args));
		$request->sendRequest();

		return $request->getResponseBody();


	}
	function signArgs($args){
		ksort($args);
		$a = '';
		foreach($args as $key => $val)
		{
			$a .= $key . $val;
		}
		return md5($this->secretKey.$a);
	}

	function setParams($name, $value) {
		 
		$parray = array();
		 
		$parray = &$this->params;
		 
		if ($value === null) {
			if (isset($parray[$name])) unset($parray[$name]);
		} else {
			$parray[$name] = $value;
		}
	}

}

?>
