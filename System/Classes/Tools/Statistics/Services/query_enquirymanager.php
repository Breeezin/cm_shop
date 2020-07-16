<?php 
	$allhitsDefined = '';
	$allhitsDefined_Recipient = '';
	$whereSQL = '';
	$whereSQL_Display = '';
	$whereSQL_Received ='';
	if (array_key_exists('SpecificResult', $this->ATTRIBUTES)){
		if (strlen($this->ATTRIBUTES['DateFrom']) or strlen($this->ATTRIBUTES['DateTo'])) {
			if (strlen($this->ATTRIBUTES['DateFrom']) and !strlen($this->ATTRIBUTES['DateTo'])) {
				$whereSQL =  " AND EnStatusUpdatedDateTime >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$allhitsDefined = 'The statistics for enquiries updated since '.$this->ATTRIBUTES['DateFrom'];
	
				$whereSQL_Received =  " AND EnDateTime >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$allhitsDefined_Received = 'The statistics for enquiries received since '.$this->ATTRIBUTES['DateFrom'];
			} else if (strlen($this->ATTRIBUTES['DateTo']) and !strlen($this->ATTRIBUTES['DateFrom'])) {
				$whereSQL =  " AND EnStatusUpdatedDateTime <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$allhitsDefined = 'The statistics for enquiries updated before '.$this->ATTRIBUTES['DateTo'];
	
				$whereSQL_Received =  " AND EnDateTime <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$allhitsDefined_Received = 'The statistics for enquiries received before '.$this->ATTRIBUTES['DateTo'];
			} else {
				$whereSQL =  " AND EnStatusUpdatedDateTime BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$allhitsDefined = 'The statistics for enquiries updated between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
				
				$whereSQL_Received =  " AND EnDateTime BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$allhitsDefined_Received = 'The statistics for enquiries received between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
			}
		}
	}
	
	// Find all the unique assets to display
	$Q_AssetStats = query("
		SELECT as_name, as_id
		FROM assets
		WHERE as_deleted = 0
			AND as_type = 'EnquiryManager'
	");	
	
	// find the total hits
	$totalHits = 0;
	$totalHits_Received = 0;
	while ($row=$Q_AssetStats->fetchRow()) {
		$hits = getRow("
			SELECT COUNT(EnID) AS Hits FROM EnquiryManager_{$row['as_id']}
			WHERE 1=1 $whereSQL
		");
		$totalHits += $hits['Hits'];

		$hits = getRow("
			SELECT COUNT(EnID) AS Hits FROM EnquiryManager_{$row['as_id']}
			WHERE 1=1 $whereSQL_Received
		");
		$totalHits_Received += $hits['Hits'];
	}
	
	
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {		
		if (strlen($this->ATTRIBUTES["EnquiryManagerParam"])) {
			$this->showAllParameters .= "&AllEnquiryManagerStats=";
			$allhitsDefined = 'Below are the statistics for all the enquiry statuses on your website.  To return to the details of the top ten 
				enquiry statuses, <A HREF="javascript:document.StatsForm.EnquiryManagerParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllEnquiryManagerStats=Yes";
			$allhitsDefined = 'Below are the statistics for the top ten enquiry statuses on your website.  For details of all the enquiry statuses <A HREF="javascript:document.StatsForm.EnquiryManagerParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
		if (strlen($this->ATTRIBUTES["EnquiryManagerRecipientParam"])) {
			$this->showAllParameters .= "&AllEnquiryManagerRecipientStats=";
			$allhitsDefined_Recipient = 'Below are the statistics for all the enquiry recipients on your website.  To return to the details of the top ten 
				enquiry recipients, <A HREF="javascript:document.StatsForm.EnquiryManagerRecipientParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllEnquiryManagerRecipientStats=Yes";
			$allhitsDefined_Recipient = 'Below are the statistics for the top ten enquiry recipients on your website.  For details of all the enquiry recipients <A HREF="javascript:document.StatsForm.EnquiryManagerRecipientParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
	}
	
?>