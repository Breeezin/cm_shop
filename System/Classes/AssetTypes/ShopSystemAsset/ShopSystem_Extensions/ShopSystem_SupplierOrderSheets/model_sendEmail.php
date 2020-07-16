<?php

	require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');	
	
	// load up the data
	include('query_view.php');
	
	$data = array(
		'Q_OrderSheet'	=>	$Q_OrderSheet,
		'Q_OrderSheetItems'	=>	$Q_OrderSheetItems,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
		'HideButtons'	=>	'yes please i dont want any buttons on my email',
	);
	
	$htmlMessage = $this->processTemplate('AcmeOrderSheet',$data);	

				foreach (array('/<img[^>]* src="([^"]+)"[^>]*>/is','/background="([^"]+)"/is','/<link[^>]* href="([^"]+\.css)"[^>]*>/is') as $regex) {
					preg_match_all($regex,$htmlMessage,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
					for ($i=count($matches[0])-1; $i>=0; $i--) {
						// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
						// matches[1] : Images/imagename
						// matches[2] : imagename
			
						$imagePath = $matches[1][$i][0];
						if (substr($imagePath,0,5) != "http:" and substr($imagePath,0,6) != "https:") {
							$imagePath = $GLOBALS['cfg']['currentServer'] . $imagePath;
						}
						
						$htmlMessage = substr_replace($htmlMessage,$imagePath,$matches[1][$i][1],strlen($matches[1][$i][0]));	
					}
				}
	
	
	$mailer = new htmlMimeMail();		
	$mailer->setFrom($GLOBALS['cfg']['EmailAddress']);
	$mailer->setSubject("Order #".$this->ATTRIBUTES['sos_id']);
	$mailer->setHTML($htmlMessage);
	$mailer->send(array('im@admin.com','macbjorck@mac.com'));				

	locationRelative('index.php?act=shopsystem_supplier_order_sheets.View&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&sos_id='.$this->ATTRIBUTES['sos_id']);
	
?>
