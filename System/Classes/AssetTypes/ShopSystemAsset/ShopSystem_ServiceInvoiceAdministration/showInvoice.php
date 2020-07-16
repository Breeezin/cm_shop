<?php

	require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');	
	require_once('System/Libraries/image/image.php');

	$siv_id = $this->ATTRIBUTES['siv_id'];

	include( 'invoice_common.php' );

	if( $details )
	{
		$htmlMessage = processTemplate("System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_ServiceInvoiceAdministration/Templates/Invoice{$details['SiCoTemplateSuffixFrom']}.html",$data);

		if ($htmlMessage) 
		{
			echo "<a href='javascript:history.go(-1)'>Return</a>";
			echo $htmlMessage;
			die;
		}
		else
			ss_DumpVarDie( $data );
	}
	else
		ss_DumpVarDie( "No such Service Invoice ID" );

?>
