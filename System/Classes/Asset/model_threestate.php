<?php 

	$this->param('FieldName','');
	$this->param('FormName','');
	$this->param('Value','');
	$this->param('AllowChange','0');
	$this->param('AllowChangeUse','0');
	$rand = md5(rand());
	$FnName = "F_$rand";
	$RelativeHere = $this->classDirectory."/";
	
	$state = array();
	$this->display->layout = 'None';
	$state['FieldName'] = $this->ATTRIBUTES['FieldName'];
	$state['FormName'] = $this->ATTRIBUTES['FormName'];
	$state['Value'] = $this->ATTRIBUTES['Value'];
	$state['AllowChange'] = $this->ATTRIBUTES['AllowChange'];
	$state['AllowChangeUse'] = $this->ATTRIBUTES['AllowChangeUse'];
	$state['FnName'] = $FnName;
	$state['RelativeHere'] = $RelativeHere;
	
	
	$this->useTemplate('ThreeState',$state);	
?>