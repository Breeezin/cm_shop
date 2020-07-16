<?php

	ss_RestrictPermission('CanAdministerAtLeastOneAsset');

	// create a job
	$this->param('URL');
	$this->param('StartDateTime');	// unix timestamp
	$this->param('EndDateTime',null);	// unix timestamp
	$this->param('Interval','NULL');		// run interval 
	$this->param('IntervalType','NULL');	// interval type
	$this->param('Description','');

	global $commonDB;
	
	$startDateTime = "'".date('Y-m-d H:i:s',$this->ATTRIBUTES['StartDateTime'])."'";
	
	$endDateTime = 'NULL';
	if ($this->ATTRIBUTES['EndDateTime'] !== null) {
		$endDateTime = "'".date('Y-m-d H:i:s',$this->ATTRIBUTES['EndDateTime'])."'";
	}

	$intervalType = $this->ATTRIBUTES['IntervalType'];
	if ($this->ATTRIBUTES['IntervalType']) {
		$intervalType = "'".escape($this->ATTRIBUTES['IntervalType'])."'";	
	}
	
	startTransaction();
	$id = newPrimaryKey('ScheduledJobs','ID',1,'shared');
	$Q_Insert = $commonDB->query("
		INSERT INTO ScheduledJobs
			(ID, URL, NextRunDateTime, DeleteDateTime, Interval, IntervalType, Deleted, Description)
		VALUES
			($id, '".escape($this->ATTRIBUTES['URL'])."', $startDateTime, $endDateTime, {$this->ATTRIBUTES['Interval']}, $intervalType, NULL, '".escape($this->ATTRIBUTES['Description'])."'
	");
	
	commit();
	
?>