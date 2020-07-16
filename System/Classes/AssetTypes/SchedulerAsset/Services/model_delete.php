<?php
  	ss_RestrictPermission('IsLoggedIn');

    // by default the admins and if the asset is set up to "show all" can delete
    // as can the owner of the event
	$this->param('EvID');
	$this->param('br','');
	$this->param('backurl',$_SESSION['BackStack']->getIndexedURL($this->ATTRIBUTES['br'],'/'.$assetPath));

	$event = getRow("
		SELECT * FROM Events
		WHERE EvID = ".safe($this->ATTRIBUTES['EvID'])."
	");
    $eventUsers = ListToArray($event['EvUsers']);
    
    if ( $event ) {
        if ( ! in_array($userid,$eventUsers) ) {
	        if (! $isAdmin || ! $showAll ) {
		        // If they're not allowed.. send them somewhere else
		        locationRelative($this->ATTRIBUTES['backurl']);	
	        }
        }
	    startTransaction();
	    // delete the message
	    $Q_DeleteEvent = query("
		    DELETE FROM Events
            WHERE EvID = ".safe($this->ATTRIBUTES['EvID'])."
        ");
	    commit();
    }    
	
    locationRelative($this->ATTRIBUTES['backurl']);

?>