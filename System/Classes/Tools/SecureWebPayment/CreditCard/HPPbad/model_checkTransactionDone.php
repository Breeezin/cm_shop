<?php

if (isset($_REQUEST["result"])){
    include "pxaccess.inc";
    $configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);

    $PxAccess_Url     =   "https://www.paymentexpress.com/pxpay/pxpay.asp";
    $PxAccess_Userid  =   $configDetails['HPPUserID'];
    $PxAccess_Key     =   $configDetails['HPPAccessKey'];
    $Mac_Key          =   $configDetails['HPPMacKey'];

    $pxaccess = new PxAccess($PxAccess_Url, $PxAccess_Userid, $PxAccess_Key, $Mac_Key);


    $enc_hex = $_REQUEST["result"];
    #getResponse method in PxAccess object returns PxPayResponse object
    #which encapsulates all the response data
    $rsp = $pxaccess->getResponse($enc_hex);

    # the following are the fields available in the PxPayResponse object
    $Success                    = $rsp->getSuccess();   # =1 when request succeeds
    $Retry                      = $rsp->getRetry();     # =1 when a retry might help
    $StatusRequired             = $rsp->getStatusRequired();      # =1 when transaction "lost"
    $AmountSettlement           = $rsp->getAmountSettlement();
    $AuthCode                   = $rsp->getAuthCode();  # from bank
    $CardName                   = $rsp->getCardName();  # e.g. "Visa"
    $DpsTxnRef	                = $rsp->getDpsTxnRef();
    $MerchantResponseText       = $rsp->getResponseText();
    $TxnType                    = $rsp->getTxnType();

    # the following values are returned, but are from the original request
    $CurrencyInput     = $rsp->getCurrencyInput();
    $MerchantReference = $rsp->getMerchantReference();

    //the following section can't be indented!  :)

$html = <<<HTMLEOF
<html>
<head>
<table align='center' width='500' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial, Helvetica, sans-serif'>
<BR><hr><BR>
<tr><td>Merchant Reference: </td>           <td>$MerchantReference</td></tr>
<tr><td>AuthCode: </td>                     <td>$AuthCode</td></tr>
<tr><td>Amount: </td>                       <td>$AmountSettlement</td></tr>
<tr><td>CurrencyName: </td>                 <td>$CurrencyInput</td></tr>
<tr><td>Card Name: </td>                    <td>$CardName</td></tr>
<tr><td>Response Text: </td>                <td>$MerchantResponseText</td></tr>
<tr><td>Transaction Type: </td>             <td>$TxnType</td></tr>
<tr><td>DPSTxnRef: </td>                    <td>$DpsTxnRef</td></tr>
</table>
</body>
</html>
HTMLEOF;

    $this->responseHTML = $html;

    if ($rsp->getStatusRequired() == "1") {
        //$result = "An error has occurred.";
        return 1;
    }
    elseif ($rsp->getSuccess() == "1") {
        //$result = "The transaction was approved.";
        return 2;
    }
    else {
        //$result = "The transaction was declined.";
        return 3;
    }
} else {
    return 3;
}
?>
