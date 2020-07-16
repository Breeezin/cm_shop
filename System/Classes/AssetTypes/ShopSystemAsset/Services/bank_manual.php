<?php
location($secureSite."index.php?act=WebPay.ByCreditCard&AccessCode=$accessCode&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&us_id=$usID&BackURL={$backURL}&Type=Shop&as_id={$assetID}");
?>
