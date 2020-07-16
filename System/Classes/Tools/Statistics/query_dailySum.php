<?php 
	// page stats
	$MaxMinDateHits = getRow("
			SELECT (Max(sts_access_timestamp) - INTERVAL 1 Day) AS MaxDate , Min(sts_access_timestamp) AS MinDate
			FROM statistics, assets
			WHERE sts_as_id = as_id AND as_system != 1 						
	");
	
	$maxDate = ss_SQLtoTimeStamp(ListFirst($MaxMinDateHits['MaxDate'], ' ').' 23:59:59');
	$minDate = ss_SQLtoTimeStamp(ListFirst($MaxMinDateHits['MinDate'], ' ').' 00:00:00');
	$dayCounter = 0;
	while($minDate < $maxDate) {		
		$minDateSql = date('Y-m-d', $minDate);
		$whereSQL =  " AND sts_access_timestamp BETWEEN '$minDateSql 00:00:00' AND '$minDateSql 23:59:59'";		
		$minDate = addOneDay($minDate);
		$Q_ResourceHits = query("
			SELECT sts_as_id, Count(sts_id) AS Hits
			FROM statistics, assets 
			WHERE sts_as_id = as_id AND as_system != 1 
			$whereSQL
			GROUP BY sts_as_id
			ORDER BY Hits DESC
		");	
		$Q_ResourceHits->addColumn('UsersHits');
		
		
		while ($row = $Q_ResourceHits->fetchRow()) {
			$Q_CountUser = getRow("
				SELECT count(distinct `sts_client_id`) UserHit
				FROM statistics
				WHERE sts_as_id = {$row['sts_as_id']}
				GROUP BY sts_as_id
			");
			$Q_InsertDaily = query("
				INSERT INTO daily_statistics 
				(das_date, das_as_id, das_hits, das_user_hits)
				VALUES
				('$minDateSql', {$row['sts_as_id']}, {$row['Hits']}, {$Q_CountUser['UserHit']})
			");
						
			//$Q_ResourceHits->setCell('UsersHits',$Q_CountUser['UserHit'],$counter++);
		}
				
		$hits = $Q_ResourceHits->columnValuesArray('Hits');
		
		$totalHits = 0;		
		foreach ($hits as $hit) {
			$totalHits += $hit;
		}
		$Q_InsertDailyHit = query("
				INSERT INTO daily_hit_totals
				(Date, Total)
				VALUES
				('$minDateSql', $totalHits)
		");
		
		
		
		$dayCounter++;
	}
	
	$Q_DeleteStats = query("DELETE FROM statistics WHERE sts_access_timestamp <= ".date('Y-m-d 23:59:59', $maxDate));
	
			
	
	
?>