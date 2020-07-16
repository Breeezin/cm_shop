<?php 
	
	$this->param('DateFrom','');
	$this->param('DateTo','');
	
	$dateFromValue = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"),  date("Y")));
	if (strlen($this->ATTRIBUTES['PageDateFrom'])) {
		$dateFromValue = "{$this->ATTRIBUTES['PageDateFrom']}";
	} 
	
	$dateFrom = new DateField (array(
		'name'			=>	'PageDateFrom',
		'displayName'	=>	'From',
		'note'			=>	NULL,
		'required'		=>	TRUE,
		'class'			=>	'formborder',
		'formName'		=>	'PageStatForm',
		'verify'		=>	FALSE,
		'value'			=>	$dateFromValue,
		'unique'		=>	FALSE,
		'showCalendar'	=> 	TRUE,
		'size'	=>	'8',	'maxLength'	=>	'10',			
	));
	
	$dateToValue = date('Y-m-d');	
	if (strlen($this->ATTRIBUTES['PageDateTo'])) {
		$dateToValue = "{$this->ATTRIBUTES['PageDateTo']}";
	}
	$dateTo = new DateField (array(
		'name'			=>	'PageDateTo',
		'displayName'	=>	'To',
		'note'			=>	NULL,
		'required'		=>	TRUE,
		'class'			=>	'formborder',
		'formName'		=>	'PageStatForm',
		'verify'		=>	FALSE,
		'value'			=>	$dateToValue,
		'unique'		=>	FALSE,
		'showCalendar'	=> 	TRUE,
		'size'	=>	'8',	'maxLength'	=>	'10',						
	));
?>