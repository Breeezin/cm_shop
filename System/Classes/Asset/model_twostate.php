<?php 

	$this->param('FieldName','');
	$this->param('FormName','');
	$this->param('Value','');
	$this->param('AllowChange','0');
	$this->param('AllowChangeUse','0');
	$this->param('JavaScriptOnly',false);

	$rand = md5(rand());
	$FnName = "F_$rand";
	$RelativeHere = $this->classDirectory."/Templates/";

	if ($this->ATTRIBUTES['AllowChange'] == 'Yes') $this->ATTRIBUTES['AllowChange'] = 1;
	if ($this->ATTRIBUTES['AllowChangeUse'] == 'Yes') $this->ATTRIBUTES['AllowChangeUse'] = 1;
	
	
	$state = array();
	$this->display->layout = 'None';
	$state['FieldName'] = $this->ATTRIBUTES['FieldName'];
	$state['FormName'] = $this->ATTRIBUTES['FormName'];
	$state['Value'] = $this->ATTRIBUTES['Value'];
	$state['AllowChange'] = $this->ATTRIBUTES['AllowChange'];
	$state['AllowChangeUse'] = $this->ATTRIBUTES['AllowChangeUse'];
	$state['FnName'] = $FnName;
	$state['RelativeHere'] = $RelativeHere;
	$state['JavaScriptOnly'] = $this->ATTRIBUTES['JavaScriptOnly'];
	
	
	$this->useTemplate('TwoState',$state);	
?>