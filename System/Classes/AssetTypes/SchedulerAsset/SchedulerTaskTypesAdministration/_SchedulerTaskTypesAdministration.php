<?php
requireOnceClass('Administration');
class SchedulerTaskTypesAdministration extends Administration {
	
	// var $className = "SchedulerTaskTypesAdministration";
	
	function exposeServices() {
		return Administration::exposeServicesUsing('SchedulerTaskTypes');
	}

	function __construct() {
		
		parent::__construct(array(
			'prefix'					=>	'SchedulerTaskTypes',
			'singular'					=>	'Scheduler Task Type',
			'plural'					=>	'Scheduler Task Types',
			'tableName'					=>	'EventTypes',
			'tablePrimaryKey'			=>	'EvTyID',
			'tableDisplayFields'		=>	array('EvTyName','EvTyDescription','EvTyImage','EvTyColor'),
			'tableDisplayFieldTitles'	=>	array('Name','Description','Icon','Color'),
			'tableOrderBy'				=>	array('EvTyName' => 'Name', ),			
		));
									
		$this->addField(new TextField (array(
			'name'			=>	'EvTyName',
			'displayName'	=>	'Event Type Name',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'127',
			'rows'	=>	'6',	'cols'		=>	'40',			
		)));
		
		$this->addField(new TextField (array(
			'name'			=>	'EvTyDescription',
			'displayName'	=>	'Event Type Description',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'127',
			'rows'	=>	'6',	'cols'		=>	'40',			
		)));
        
        $rid = getRow("select as_id from assets where as_type like 'Scheduler'");
		$imgDir = ss_secretStoreForAsset($rid['as_id'],"Icons");
		$this->addField(new PopupUniqueImageField (array(
			'name'			=>	'EvTyImage',
			'displayName'	=>	'Event Type Icon',
			'note'			=>	NULL,
			'directory'			=>	$imgDir,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
		)));
		
		$this->addField(new TextField (array(
			'name'			=>	'EvTyColor',
			'displayName'	=>	'Event Type Color',
			'note'			=>	'Use 000000 to FFFFFF',
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'127',
			'rows'	=>	'6',	'cols'		=>	'40',			
		)));
		
		
	}
	

}
?>
