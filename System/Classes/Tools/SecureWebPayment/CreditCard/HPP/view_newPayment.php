<?php
    include "pxaccess.inc";
    $configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);
    if (isset($webpay->ATTRIBUTES['as_id']) and isset($webpay->ATTRIBUTES['us_id'])){
        $_SESSION['SessionInfo'] = $webpay->ATTRIBUTES['as_id']."US".$webpay->ATTRIBUTES['us_id'];
    }
    $PxAccess_Url     =   "https://www.paymentexpress.com/pxpay/pxpay.asp";
    $PxAccess_Userid  =   $configDetails['HPPUserID'];
    $PxAccess_Key     =   $configDetails['HPPAccessKey'];
    $Mac_Key          =   $configDetails['HPPMacKey'];

    $pxaccess = new PxAccess($PxAccess_Url, $PxAccess_Userid, $PxAccess_Key, $Mac_Key);
    $request_string = explode('/',$_REQUEST['REQUEST_URI']);

    $request_uri = (version_compare(PHP_VERSION, "4.3.4", ">=")) ?'/index.php' : 'index.php';
    $server_url     =  $GLOBALS['cfg']['CurrentServer'];
    $script_url = "$server_url$request_uri";
    $success_url = $server_url.'/index.php?act=WebPay.ByCreditCard&DoAction=1&Paid=1';
    $fail_url = $server_url.'/index.php?act=WebPay.ByCreditCard&DoAction=1&Paid=0';

    if (!isset($_REQUEST['result'])){
        //store the URI for when the response is sent back
        //hpp appends ?result=xxx to the $script URL so can't have other parameters in the URL
        $_SESSION['Transaction'] = $_REQUEST['REQUEST_URI'];
        $request = new PxPayRequest();


        #Set up PxPayRequest Object
        $request->setAmountInput($webpay->payment['tr_total']);
        $request->setTxnData1($webpay->payment['tr_client_name']);# whatever you want to appear
        //$request->setTxnData2('yo yo');		# whatever you want to appear
        //$request->setTxnData3('whoop whoop');		# whatever you want to appear
        $request->setTxnType("Purchase");
        $request->setInputCurrency($webpay->payment['cn_currency_code']);
        $request->setMerchantReference($webpay->payment['tr_id']); # fill this with your order number
        $request->setEmailAddress("briar@innovativemedia.co.nz");
        $request->setUrlFail($fail_url);
        $request->setUrlSuccess($success_url);

        #Call makeResponse of PxAccess object to obtain the 3-DES encrypted payment request
        $request_string = $pxaccess->makeRequest($request);

        header("Location: $request_string");

         //else we just want it to carry on

    }else {
        //die('whoop whoop');
        $enc_hex = $_REQUEST["result"];
        #getResponse method in PxAccess object returns PxPayResponse object
        #which encapsulates all the response data
        $rsp = $pxaccess->getResponse($enc_hex);

        $server_url = $GLOBALS['cfg']['plaintext_server'];

        $successURL = $server_url . $_REQUEST['REQUEST_URI'] . '&DoAction=1&Paid=1';
        $failURL = $server_url . $_REQUEST['REQUEST_URI'] . '&DoAction=1&Paid=0';

        if ($rsp->getSuccess()){
            location($successURL);
        } else {
            location($failURL);
        }
}


?>
