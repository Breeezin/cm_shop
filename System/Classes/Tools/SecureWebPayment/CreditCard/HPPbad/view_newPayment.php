<?php

    include "pxaccess.inc";
    //ss_DumpVarDie($webpay->payment);
    $configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);
    $sessionInfo = $webpay->ATTRIBUTES['as_id']."US".$webpay->ATTRIBUTES['us_id'];
    $_SESSION['SessionInfo'] = $sessionInfo;

    $PxAccess_Url     =   "https://www.paymentexpress.com/pxpay/pxpay.asp";
    $PxAccess_Userid  =   $configDetails['HPPUserID'];
    $PxAccess_Key     =   $configDetails['HPPAccessKey'];
    $Mac_Key          =   $configDetails['HPPMacKey'];

    $pxaccess = new PxAccess($PxAccess_Url, $PxAccess_Userid, $PxAccess_Key, $Mac_Key);

    $request_uri    =  '/index.php';
    $server_url     =  $GLOBALS['cfg']['plaintext_server'];
    $script_url     =  "$server_url$request_uri"; //Using this code after PHP version 4.3.4

    $request_string = explode('/',$_REQUEST['REQUEST_URI']);
    $return_string  = $request_string[2];
    $_SESSION['Transaction'] = $return_string;
    ss_DumpVarDie($webpay);
    if (!isset($_REQUEST['result'])){
        //store the URI for when the response is sent back
        //hpp appends ?result=xxx to the $script URL so can't have other parameters in the URL
        $request = new PxPayRequest();


        #Set up PxPayRequest Object
        $request->setAmountInput($webpay->payment['tr_total']);
        $request->setTxnData1($webpay->payment['tr_client_name']);# whatever you want to appear
        //$request->setTxnData2('xx');		# whatever you want to appear
        //$request->setTxnData3('yy');		# whatever you want to appear
        $request->setTxnType("Purchase");
        $request->setInputCurrency($webpay->payment['cn_currency_code']);
        $request->setMerchantReference($webpay->payment['tr_id']); # fill this with your order number
        $request->setEmailAddress("briar@innovativemedia.co.nz");
        $request->setUrlFail($script_url);
        $request->setUrlSuccess($script_url);

        #Call makeResponse of PxAccess object to obtain the 3-DES encrypted payment request
        $request_string = $pxaccess->makeRequest($request);

        //ss_DumpVarDie($_SESSION['Transaction']);

        location($request_string);

         //else we just want it to carry on

    }else {
        $enc_hex = $_REQUEST["result"];
        #getResponse method in PxAccess object returns PxPayResponse object
        #which encapsulates all the response data
        $rsp = $pxaccess->getResponse($enc_hex);
        $server_url = $GLOBALS['cfg']['plaintext_server'];
        $successURL = $server_url . $_SESSION['Transaction'] . '&DoAction=1&Paid=1';
        $failURL = $server_url . $_SESSION['Transaction'] . '&DoAction=1&Paid=0';

        if ($rsp->getSuccess()){
            location($successURL);
        } else {
            location($failURL);
        }
}


?>
