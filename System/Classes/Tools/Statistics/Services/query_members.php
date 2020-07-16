<?php 
	$allhitsDefined = '';
	$whereSQL = '';
	if (array_key_exists('SpecificResult', $this->ATTRIBUTES)){
		if (strlen($this->ATTRIBUTES['DateFrom']) or strlen($this->ATTRIBUTES['DateTo'])) {
			if (strlen($this->ATTRIBUTES['DateFrom']) and !strlen($this->ATTRIBUTES['DateTo'])) {
				$whereSQL =  " WHERE los_timestamp >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$allreferralsDefined = 'The statistics for all the refferals since '.$this->ATTRIBUTES['DateFrom'];
			} else if (strlen($this->ATTRIBUTES['DateTo']) and !strlen($this->ATTRIBUTES['DateFrom'])) {
				$whereSQL =  " WHERE los_timestamp <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$allreferralsDefined = 'The statistics for all the refferals until '.$this->ATTRIBUTES['DateTo'];
			} else {
				$whereSQL =  " WHERE los_timestamp BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$allreferralsDefined = 'The statistics for all the refferals between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
			}
		}
	}
	$Q_ResourceHits = query("
			SELECT los_us_id, Count(los_timestamp) AS Hits
			FROM login_statistics			
			$whereSQL
			GROUP BY los_us_id
			ORDER BY Hits DESC
	");	
	
	$hits = $Q_ResourceHits->columnValuesArray('Hits');
	$totalHits = 0;
	foreach ($hits as $hit) {
		$totalHits += $hit;
	}
	
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {
		if (strlen($this->ATTRIBUTES["MembersParam"])) {
			$this->showAllParameters .= "&AllMembersStats=";
			$allhitsDefined = 'Below are the statistics for all the members\' logins on your website.  To return to the details of the top ten 
				members\' logins, <A HREF="javascript:document.StatsForm.MembersParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllMembersStats=Yes";
			$allhitsDefined = 'Below are the statistics for the top ten members\' logins on your website.  For details of all members\' logins on your site <A HREF="javascript:document.StatsForm.MembersParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
	} 
?>