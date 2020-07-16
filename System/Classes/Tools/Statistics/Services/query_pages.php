<?php 
	
	$allhitsDefined = '';
	$whereSQL = '';
	if (array_key_exists('SpecificResult', $this->ATTRIBUTES)){
		if (strlen($this->ATTRIBUTES['DateFrom']) or strlen($this->ATTRIBUTES['DateTo'])) {
			if (strlen($this->ATTRIBUTES['DateFrom']) and !strlen($this->ATTRIBUTES['DateTo'])) {
				$whereSQL =  " AND sts_access_timestamp >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$allhitsDefined = 'The statistics for all the pages since '.$this->ATTRIBUTES['DateFrom'];
			} else if (strlen($this->ATTRIBUTES['DateTo']) and !strlen($this->ATTRIBUTES['DateFrom'])) {
				$whereSQL =  " AND sts_access_timestamp <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$allhitsDefined = 'The statistics for all the pages until '.$this->ATTRIBUTES['DateTo'];
			} else {
				$whereSQL =  " AND sts_access_timestamp BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$allhitsDefined = 'The statistics for all the pages between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
			}
		}
	}

	$Q_ResourceHits = query("
			SELECT sts_as_id, Count(sts_id) AS Hits
			FROM statistics, assets 
			WHERE sts_as_id = as_id AND as_system != 1 
			$whereSQL
			GROUP BY sts_as_id
			ORDER BY Hits DESC
	");	
	$Q_ResourceHits->addColumn('UsersHits');
	
	$counter = 0;
	while ($row = $Q_ResourceHits->fetchRow()) {
		$Q_CountUser = getRow("
			SELECT count(distinct `sts_client_id`) UserHit
			FROM statistics
			WHERE sts_as_id = {$row['sts_as_id']}
			GROUP BY sts_as_id
		");
		
		
		$Q_ResourceHits->setCell('UsersHits',$Q_CountUser['UserHit'],$counter++);
	}
			
	$hits = $Q_ResourceHits->columnValuesArray('Hits');
	
	$totalHits = 0;
	
	foreach ($hits as $hit) {
		$totalHits += $hit;
	}
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {		
		if (strlen($this->ATTRIBUTES["PagesParam"])) {
			$this->showAllParameters .= "&AllPagesStats=";
			$allhitsDefined = 'Below are the statistics for all the pages on your website.  To return to the details of the top ten 
				pages, <A HREF="javascript:document.StatsForm.PagesParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllPagesStats=Yes";
			$allhitsDefined = 'Below are the statistics for the top ten pages on your website.  For details of the hits on all your pages <A HREF="javascript:document.StatsForm.PagesParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
	}
?>