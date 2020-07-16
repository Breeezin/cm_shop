<?php
    requireOnceClass("SchedulerAdministration");
    $DoCoAdmin = new SchedulerAdministration($assetID, true);

  	$this->paramLen('EvID');
	$this->param('br','');
    //br/$backURL\

	$DoCoAdmin->ATTRIBUTES = $this->ATTRIBUTES;
	$DoCoAdmin->primaryKey = $this->ATTRIBUTES['EvID'];	

	// get the form fields to display in the form
    $errors = array();
	$temp = new Request("Security.Sudo",array('Action'=>'start'));
	$form = $DoCoAdmin->form($errors);
	$temp = new Request("Security.Sudo",array('Action'=>'stop'));
    
    $backURL = $_SESSION['BackStack']->getIndexedURL($this->ATTRIBUTES['br'],'/'.$assetPath);
    //ss_DumpVarDie($backURL);
    $data = array (
        'fields' =>  $DoCoAdmin->fields,
        'BackURL' =>  $backURL,
        
    );
    
    $this->useTemplate("Display",$data);
    
?>	