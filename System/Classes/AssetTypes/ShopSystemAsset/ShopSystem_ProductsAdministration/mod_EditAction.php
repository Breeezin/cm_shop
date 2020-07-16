<?php
    $this->param('Edit_Next','');
    $this->param('OrderBy','');
    $this->param('SortBy','');
    $this->param('BreadCrumbs');


	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
	
		// We're writing to the database, so must load each field
		// with the value receieved from the form

		$this->loadFieldValuesFromForm($this->ATTRIBUTES);
		
		// Write to the database
		$errors = $this->update();

		// Return if no error messages were returned
		if (count($errors) == 0) {
			// Return (to the list of records hopefully)
			//rfaReturn();
			//location($this->ATTRIBUTES['RFA']);


            if( $this->ATTRIBUTES['DoAction'] == 'Save & View Next') {


            	$result = $this->query(array(
//            		'FilterSQL'	=>  ' AND pr_id = '.$this->ATTRIBUTES['pr_id'].' ',
            		'OrderBy'	=>  $this->ATTRIBUTES['OrderBy'],
            		'SortBy'	=>  $this->ATTRIBUTES['SortBy'],
            	));

               // ss_DumpVar($result);
               // ss_DumpVarDie($this->ATTRIBUTES);

                while ($row = $result->fetchRow()) {
                    if($row['pr_id'] == $this->ATTRIBUTES['pr_id']){
                        if($row2 = $result->fetchRow())
                            location("{$script_name}?act={$this->prefix}Administration%2EEdit&{$this->tablePrimaryKey}={$row2[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($this->ATTRIBUTES['BreadCrumbs'])."&BackURL=".ss_URLEncodedFormat($_SESSION['BackStack']->getURL())."&as_id={$this->assetLink}&SortBy={$this->ATTRIBUTES['SortBy']}&OrderBy={$this->ATTRIBUTES['OrderBy']}");
                        else
                            break;
                    }
                }


               // ss_DumpVarDie($this);

            }
            /*
<!--  ----------------------------------------------------------------------------------   -->
"{$script_name}?act={$this->prefix}Administration%2EEdit&{$this->tablePrimaryKey}={$row[$this->tablePrimaryKey]}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&BackURL=".ss_URLEncodedFormat($rfa))."&as_id={$this->assetLink}";



<!--  ----------------------------------------------------------------------------------   -->

            */
            // droppped through & backto default
		    location($this->ATTRIBUTES['BackURL']);

		}
	}
?>