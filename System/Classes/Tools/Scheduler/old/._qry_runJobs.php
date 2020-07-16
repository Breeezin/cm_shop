<?php

	//ss_log_message_r($_REQUEST);
	//print($this->ATTRIBUTES['IMLogin']."\n");
	//index.php?act=Scheduler.RunJobs&IMLogin=so%5C%40v93mv5%5C*6zxvpa912%5C%23%5C%25%5C$
	
	// simple authentication for now
	$this->param('IMLogin','');
	$inputPassword = str_replace('\\','',$this->ATTRIBUTES['IMLogin']);
	if ($inputPassword !== ss_getTheDeployerPassword()) die('.');	
	
	global $commonDB;

	$this->display->layout = 'none';
	
	// set time limit to don't care
	set_time_limit(0);

	startTransaction('commonDB');
	
	// find out what "now" is..
	// for our purposes it is now plus five minutes
	$now = ss_TimeStampToSQL(time()+60*5);
	
	// get any jobs that need running
	$Q_Jobs = $commonDB->query("
		SELECT * FROM ScheduledJobs
		WHERE NextRunDateTime < $now
			AND Deleted IS NULL
		ORDER BY NextRunDateTime
	");
	
	// grab the full url
	$password = 'IMLogin='.ss_URLEncodedFormat(ss_getTheDeployerPassword());
	//print $password;

	function donothing() {
		print("error calling scheduled job");
	}
	set_error_handler("donothing");	
	
	$deleteJobs = '-1';
	while ($job = $Q_Jobs->fetchRow()) {

		/*
		// we can't do this here because some jobs might take ages and in the next 10 minutes, the system will check what jobs 
		// need to be run .. so we update the status of the jobs so they will only run once
		
		if (strpos($job['URL'],'?') !== false) {
			$job['URL'] .= '&';	
		} else {
			$job['URL'] .= '?';	
		}
		
		print(ss_TimeStampToSQL(time(),'').': Started job id: '.$job['ID'].' on url '.ss_HTMLEditFormat($job['URL'].$password)."<br>\n");
		flush();
		ob_start();

		// grab the start time
		$started = ss_TimeStampToSQL(time());

		// hit the url
		print(ss_httpGet($job['URL'].$password));	

		// grab the finish time
		$finished = ss_TimeStampToSQL(time());
		
		$result = ob_get_contents();
		ob_end_clean();
		print(ss_TimeStampToSQL(time(),'').': Finished job id: '.$job['ID']."<br>\n");
		flush();

		// log the results
		$Q_Log = $commonDB->query("
			INSERT INTO SchedulerLog
				(Started, Finished, Output, ScheduledJobLink)
			VALUES	
				($started, $finished, '".escape($result)."', {$job['ID']})
		");*/
		
		// update the next start time 
		if ($job['Interval'] !== null) {
			$nextStart = ss_SQLtoTimeStamp($job['NextRunDateTime']);
			switch (rtrim(strtolower($job['IntervalType']),'s')) {
				case 'second' :
					$nextStart += $job['Interval'];
					break;
				case 'minute' :
					$nextStart += $job['Interval']*60;
					break;
				case 'hour' :
					$nextStart += $job['Interval']*60*60;
					break;
				case 'day' :
					$nextStart += $job['Interval']*60*60*24;
					break;
				case 'week' :
					$nextStart += $job['Interval']*60*60*24*7;
					break;
				case 'month' :
					// Keep looping and add the correct number of days for each month
					$counter = $job['Interval'];
					while ($counter > 0) {
						$yearMonths = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);	
						$leapYearMonths = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);	
						$month = date('m',$nextStart)-1;
						if (isLeapYear($nextStart)) {
							$days = $leapYearMonths[$month];
						} else {
							$days = $yearMonths[$month];
						}
						$nextStart += $days*60*60*24;
						$counter--;
					}
					break;
			}	
			$Q_Update = $commonDB->query("
				UPDATE ScheduledJobs
				SET NextRunDateTime = ".ss_TimeStampToSQL($nextStart)."
				WHERE ID = {$job['ID']}
			");
			print(ss_TimeStampToSQL(time(),'').': Updated job id: '.$job['ID'].' to run at next '.ss_TimeStampToSQL($nextStart,'')."<br>\n");
			flush();
		} else {
			$deleteJobs .= ','.$job['ID'];
		}
		

	}
	
	// flag deleted any jobs that should be 
	$Q_Jobs = $commonDB->query("
		UPDATE ScheduledJobs
		SET Deleted = 1
		WHERE	(
					(ID IN ($deleteJobs))
				OR 
					(DeleteDateTime IS NOT NULL AND DeleteDateTime < $now)
				)
			AND Deleted IS NULL
	");

	commit('commonDB');
	

	// now run the actual job
	while ($job = $Q_Jobs->fetchRow()) {

		if (strpos($job['URL'],'?') !== false) {
			$job['URL'] .= '&';	
		} else {
			$job['URL'] .= '?';	
		}
		
		print(ss_TimeStampToSQL(time(),'').': Started job id: '.$job['ID'].' on url '.ss_HTMLEditFormat($job['URL'].$password)."<br>\n");
		flush();
		ob_start();

		// grab the start time
		$started = ss_TimeStampToSQL(time());

		// hit the url
		print(ss_httpGet($job['URL'].$password));	

		// grab the finish time
		$finished = ss_TimeStampToSQL(time());
		
		$result = ob_get_contents();
		ob_end_clean();
		print(ss_TimeStampToSQL(time(),'').': Finished job id: '.$job['ID']."<br>\n");
		flush();

		// log the results
		$Q_Log = $commonDB->query("
			INSERT INTO SchedulerLog
				(Started, Finished, Output, ScheduledJobLink)
			VALUES	
				($started, $finished, '".escape($result)."', {$job['ID']})
		");
		
	}
	
	$GLOBALS['cfg']['debugMode'] = false;
	
?>