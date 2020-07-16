<?php

	$this->param('Month',month(now()));
	$this->param('Year',year(now()));
	$this->param('OnClick','alert(date);');
	$this->param('Format','Ymd');

	$startDate = mktime(12,0,0,$this->ATTRIBUTES['Month'],1,$this->ATTRIBUTES['Year']);
	
?>