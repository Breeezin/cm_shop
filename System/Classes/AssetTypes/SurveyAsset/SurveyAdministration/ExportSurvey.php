<?php
 	// Firstly, find the fields to export
	$fieldPrefix = 'AST_SURVEY';

	$this->param('as_id','');

    if( strlen($this->ATTRIBUTES['as_id']) == 0 )
        die('Invalid Request');

	$Q_Assets = getRow("
		SELECT * FROM assets
		WHERE (as_deleted IS NULL OR as_deleted = 0)
            AND as_id={$this->ATTRIBUTES['as_id']}
	");

    if (count( $Q_Assets ) == 0)
        die('Invalid Request');

    $asset = unserialize($Q_Assets['as_serialized']);

	// Load the field set
	if (strlen($asset['AST_SURVEYFIELDS'])) {
		$fieldsArray2 = unserialize($asset['AST_SURVEYFIELDS']);
	} else {
		$fieldsArray2 = array();
	}

    // Get the Fields to Query
    $extraFields = 'efs_id as ID,';

    foreach ($fieldsArray2 as $customField) {
        $extraFields .= " Su{$customField['uuid']} as {$customField['uuid']}, ";
	}

    $extraFields .= ' efs_timestamp as Date ';

	$Q_Users = query("
		SELECT
            $extraFields
	 	FROM Survey_{$this->ATTRIBUTES['as_id']}
	");

    foreach ($fieldsArray2 as $customField) {
        if( $customField ['type'] == 'RadioFromArrayField' ||
            $customField ['type'] =='SelectFromArrayField' ||
            $customField ['type'] =='RadioWithOtherFromArrayField' ) {
            // replace the option with the correct option text
            $counter = 0;
            while ($row = $Q_Users->fetchRow()) {
                $answer = "";
                foreach ( $customField['options'] as $opt ) {
                    if ( $row[$customField['uuid']] == $opt['uuid'] ) {
                        if ( $customField ['type'] =='RadioWithOtherFromArrayField' and
                             strpos($opt['name'],"*")!== false ){
                            $other = getRow("
                                    select Su{$customField['uuid']}_otherValue as other
                                    from Survey_{$this->ATTRIBUTES['as_id']}
                                    where efs_id = {$row['ID']}"
                            );
                            $answer = $opt['name']." ".$other['other'];
                        } else
                            $answer = $opt['name'];
                        break;
                    }
                }
		        $Q_Users->setCell($customField['uuid'],$answer,$counter);
                $counter++;
            }
        } else if ( $customField ['type'] == 'MultiSelectFromArrayField' || $customField ['type'] =='MultiCheckFromArrayField' ) {
            // replace the option with the correct option text
            $counter = 0;
            while ($row = $Q_Users->fetchRow()) {
                $answer = "";
                $selectedoptions = explode(',',$row[$customField['uuid']]);
                foreach ( $customField['options'] as $opt ) {
                    if ( in_array($opt['uuid'], $selectedoptions) ) {
                        $answer .= $opt['name'] . ' ';
                    }
                }
		        $Q_Users->setCell($customField['uuid'],$answer,$counter);
                $counter++;
            }
        }
	}
	$users = ss_queryToTab($Q_Users,array());

	header('Content-Type: application/download',true);
	header('Content-Disposition: attachment; filename=Survey'.$this->ATTRIBUTES['as_id'].'.txt',true);
    print ("This file represents the data gathered from your survey. It is shown in tab delimited format so that it can easily be imported in other applications for processing.\n\nKEY\n");
    print ("ID : The ID generated for this survey response.\n");
    foreach ($fieldsArray2 as $customField) {
       print ("{$customField['uuid']} : {$customField['name']}\n");
    }
    print ("Date : The date and time that this response was completed.\n\n");
	print($users);

?>