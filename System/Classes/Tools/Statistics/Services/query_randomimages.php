<?php 
	$allhitsDefined = '';
	$allhitsDefined_Display = '';
	$whereSQL = '';
	$whereSQL_Display = '';
	if (array_key_exists('SpecificResult', $this->ATTRIBUTES)){
		if (strlen($this->ATTRIBUTES['DateFrom']) or strlen($this->ATTRIBUTES['DateTo'])) {
			if (strlen($this->ATTRIBUTES['DateFrom']) and !strlen($this->ATTRIBUTES['DateTo'])) {
				$whereSQL =  " AND ris_timestamp >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$whereSQL_Display =  " AND rids_timestamp >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$allhitsDefined = 'The statistics for all the random images since '.$this->ATTRIBUTES['DateFrom'];
			} else if (strlen($this->ATTRIBUTES['DateTo']) and !strlen($this->ATTRIBUTES['DateFrom'])) {
				$whereSQL =  " AND ris_timestamp <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$whereSQL_Display =  " AND rids_timestamp <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$allhitsDefined = 'The statistics for all the random images until '.$this->ATTRIBUTES['DateTo'];
			} else {
				$whereSQL =  " AND ris_timestamp BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$whereSQL_Display =  " AND rids_timestamp BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$allhitsDefined = 'The statistics for all the random images between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
			}
		}
	}
	
	$Q_RandomImages = query("
		SELECT as_name, as_id, Count(ris_id) AS TotalHits 
		FROM assets, random_images_statistics 
		WHERE 
			as_type LIKE 'RandomImages' 
		AND 
			as_id = ris_as_id 
		$whereSQL
		GROUP BY ris_as_id
	");	
	
	$Q_RandomImages_Display = query("
		SELECT as_name, as_id, Count(rids_timestamp) AS TotalHits, as_serialized 
		FROM assets, random_images_display_statistics 
		WHERE 
			as_type LIKE 'RandomImages' 
		AND 
			as_id = rids_as_id 
		$whereSQL_Display
		GROUP BY rids_as_id
	");	
	
	$hits = $Q_RandomImages->columnValuesArray('TotalHits');
	$hits_Display = $Q_RandomImages_Display->columnValuesArray('TotalHits');
	
	$totalHits = 0;
	$totalHits_Display = 0;
	
	foreach ($hits as $hit) {
		$totalHits += $hit;
	}
	foreach ($hits_Display as $hit) {
		$totalHits_Display += $hit;
	}
	
	$assets = array();
	while($asset = $Q_RandomImages_Display->fetchRow()) {
		$temp = unserialize($asset['as_serialized']);
		$assets["{$asset['as_id']}"] = $temp['AST_RANDOMIMAGES_FORM'];
		if (!is_array($temp['AST_RANDOMIMAGES_FORM'])){
			$assets["{$asset['as_id']}"]= unserialize($temp['AST_RANDOMIMAGES_FORM']);
		}
	}
	
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {		
		if (strlen($this->ATTRIBUTES["RandomImagesParam"])) {
			$this->showAllParameters .= "&AllRandomImagesStats=";
			$allhitsDefined = 'Below are the statistics for all the random image link hits on your website.  To return to the details of the top ten 
				image link, <A HREF="javascript:document.StatsForm.RandomImagesParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllRandomImagesStats=Yes";
			$allhitsDefined = 'Below are the statistics for the top ten random image link hits on your website.  For details of the hits on all your random image link <A HREF="javascript:document.StatsForm.RandomImagesParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
		if (strlen($this->ATTRIBUTES["RandomImagesDisplayParam"])) {
			$this->showAllParameters .= "&AllRandomImagesStats=";
			$allhitsDefined_Display = 'Below are the statistics for all the random image display on your website.  To return to the details of the top ten 
				image display, <A HREF="javascript:document.StatsForm.RandomImagesDisplayParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllRandomImagesStats=Yes";
			$allhitsDefined_Display = 'Below are the statistics for the top ten random image display on your website.  For details of the hits on all your random image display <A HREF="javascript:document.StatsForm.RandomImagesDisplayParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
	}
	
?>