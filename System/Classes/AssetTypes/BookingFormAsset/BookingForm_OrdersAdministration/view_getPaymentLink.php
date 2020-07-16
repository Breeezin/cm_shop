<?php
	$this->display->title = "Secure Payment Link";
	print('<p>Please send the following secure payment link to your customer:</p>');
	print('<p>'.ss_withTrailingSlash($GLOBALS['cfg']['secure_server'])."index.php?act=WebPay.ByCreditCard&tr_id={$Transaction['tr_id']}&tr_token={$Transaction['tr_token']}&BackURL=".ss_URLEncodedFormat(ss_withTrailingSlash($GLOBALS['cfg']['plaintext_server']).ss_withoutPreceedingSlash($assetPath)."?Service=ThankYou&bo_id={$Booking['bo_id']}&tr_id={$Transaction['tr_id']}&tr_token={$Transaction['tr_token']}").'</p>');
?>