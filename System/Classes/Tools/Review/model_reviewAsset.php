<?php
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		
		$Asset = $Q_Asset->fetchRow();
		$toAddress = $Asset['us_email'];
		$data = array();
		$data['Author'] = $Asset['us_first_name'].' '.$Asset['us_last_name'];
		$data['AuthorFirstName'] = $Asset['us_first_name'];
		$data['as_type'] = strtolower($Asset['as_type']);
		$temp = new Request('Asset.PathFromID',array('as_id'	=>	$Asset['as_id'],));
		$data['AssetPath'] = $temp->value;		
		
		$reviewer = getRow("
			SELECT * FROM users
			WHERE us_id = ".ss_getUserID()."
		");
		$fromAddress = $reviewer['us_email'];
		$data['Reviewer'] = $reviewer['us_first_name'].' '.$reviewer['us_last_name'];
		
		if (array_key_exists('AcceptChanges_x',$this->ATTRIBUTES)) {
			// Make the changes live! :)

			$this->param('AssetReviewerComments');

			if ($Asset['as_pending_serialized'] == 'Delete') {
				// delete the asset
				$result = new Request('Asset.Delete',array(
					'as_id'	=>	$this->ATTRIBUTES['as_id'],
					'AsService'	=>	true,
				));
				// remove from review, remove the delete request, save the comments
				$Q_Delete = query("
					UPDATE assets
					SET
						AssetReview = 0,
						as_pending_serialized = NULL,
						AssetReviewerComments = '".escape($this->ATTRIBUTES['AssetReviewerComments'])."'
					WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
				");

				// send the email
				require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
				$mailer = new htmlMimeMail();
				$mailer->setFrom($fromAddress);
				$mailer->setSubject('Your delete request has been accepted by reviewer');
				$htmlEmail = $this->processTemplate('EmailDeleteAccepted',$data);
				$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
				$mailer->send(array($toAddress));
				
				locationRelative('index.php?act=Review.List&ReloadTree=1');
			} else {
				// set it not to review, update the cereal, clear the pending cereal, save the comments
				$Q_MakeLive = query("
					UPDATE assets
					SET
						AssetReview = 0,
						as_serialized = '".escape($Asset['as_pending_serialized'])."',
						as_pending_serialized = NULL,
						AssetReviewerComments = '".escape($this->ATTRIBUTES['AssetReviewerComments'])."'
					WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
				");

				// send the email
				require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
				$mailer = new htmlMimeMail();
				$mailer->setFrom($fromAddress);
				$mailer->setSubject('Your changes have been accepted by reviewer');
				$htmlEmail = $this->processTemplate('EmailChangesAccepted',$data);
				$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
				$mailer->send(array($toAddress));
				
				locationRelative('index.php?act=Review.List');
			}
				
		} else {
			
			// Decline the changes
			// set it not to review, save the comments
			$Q_Decline = query("
				UPDATE assets
				SET
					AssetReview = 0,
					AssetReviewerComments = '".escape($this->ATTRIBUTES['AssetReviewerComments'])."'
				WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
			");

			// send the email
			require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
			$mailer = new htmlMimeMail();
			$mailer->setFrom($fromAddress);

			if ($Asset['as_pending_serialized'] == 'Delete') {
				$mailer->setSubject('Your delete request has been declined by reviewer');
				$htmlEmail = $this->processTemplate('EmailDeleteDeclined',$data);
			} else {
				$mailer->setSubject('Your changes have been declined by reviewer');
				$htmlEmail = $this->processTemplate('EmailChangesDeclined',$data);
			}

			$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
			$mailer->send(array($toAddress));
			
			locationRelative('index.php?act=Review.List');
			
		}
	}
?>