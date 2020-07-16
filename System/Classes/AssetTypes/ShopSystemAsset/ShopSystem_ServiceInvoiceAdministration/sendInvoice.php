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
			$mailerHTML = new htmlMimeMail();
			$mailerHTML->setFrom('admin@acmerockets.com');
			$mailerHTML->setSubject('Your invoice, number '.$siv_id.', created on '.$details['siv_created_date']);
			$mailerHTML->setHtml($htmlMessage, 'This is an HTML email');
            $mailerHTML->is_built = false;
            unset( $mailerHTML->output );
            $mailerHTML->send(array($details['SiCoEmailAddressFrom']));		// go live!  Rex
            $mailerHTML->send(array($details['SiCoEmailAddressTo']));		// go live!  Rex
		}
		else
			ss_DumpVarDie( $data );
	}
	else
		ss_DumpVarDie( "No such Service Invoice ID" );

?>
