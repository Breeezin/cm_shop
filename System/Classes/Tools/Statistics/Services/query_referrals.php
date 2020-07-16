<?php 
	$allreferralsDefined = '';
	$whereSQL = '';
	if (array_key_exists('SpecificResult', $this->ATTRIBUTES)){
		if (strlen($this->ATTRIBUTES['DateFrom']) or strlen($this->ATTRIBUTES['DateTo'])) {
			if (strlen($this->ATTRIBUTES['DateFrom']) and !strlen($this->ATTRIBUTES['DateTo'])) {
				$whereSQL =  " WHERE sts_access_timestamp >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$allreferralsDefined = 'The statistics for all the refferals since '.$this->ATTRIBUTES['DateFrom'];
			} else if (strlen($this->ATTRIBUTES['DateTo']) and !strlen($this->ATTRIBUTES['DateFrom'])) {
				$whereSQL =  " WHERE sts_access_timestamp <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$allreferralsDefined = 'The statistics for all the refferals until '.$this->ATTRIBUTES['DateTo'];
			} else {
				$whereSQL =  " WHERE sts_access_timestamp BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$allreferralsDefined = 'The statistics for all the refferals between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
			}
		}
	}
	$Q_ReferralHits = query("
			SELECT sts_referrer, Count(sts_id) AS Hits, Count(DISTINCT(sts_as_id)) AS PagesHits, Count(DISTINCT(sts_client_id))+1 AS UsersHits
			FROM statistics			
			$whereSQL
			GROUP BY sts_referrer
			ORDER BY Hits DESC
	");	
	
	$hits = $Q_ReferralHits->columnValuesArray('Hits');
	$totalReferralHits = 0;
	foreach ($hits as $hit) {
		$totalReferralHits += $hit;
	}
	
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {
		if (strlen($this->ATTRIBUTES["ReferralsParam"])) {
			$this->showAllParameters .= "&AllReferralsStats=";
			$allreferralsDefined = 'Below are the statistics for all the refferals on your website.  To return to the details of the top ten 
				referrals, <A HREF="javascript:document.StatsForm.ReferralsParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllReferralsStats=Yes";
			$allreferralsDefined = 'Below are the statistics for the top ten referrals on your website.  For details of all your referrals on your site <A HREF="javascript:document.StatsForm.ReferralsParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
	} 
?>