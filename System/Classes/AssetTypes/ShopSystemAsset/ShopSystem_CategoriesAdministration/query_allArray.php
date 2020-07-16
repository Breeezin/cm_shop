<?php
	$this->display->layout = 'None';
	$this->param('as_id');
	$this->param('ca_id','');
	$this->param('ForAdmin',false);
	
	$this->restrictedCategoriesSQL = '';
	if (!$this->ATTRIBUTES['ForAdmin']) {
		$this->restrictedCategoriesSQL = ss_shopRestrictedCategoriesSQL();
	}

	$startCategory = 'IS NULL';
	if (strlen($this->ATTRIBUTES['ca_id']) ) {
		if( is_numeric( $this->ATTRIBUTES['ca_id']) ) {
			$startCategory = '= '.$this->ATTRIBUTES['ca_id'];	
			$Category = getRow("
				SELECT * FROM shopsystem_categories
				WHERE ca_as_id = ".safe($this->ATTRIBUTES['as_id'])."
					AND ca_id = ".safe($this->ATTRIBUTES['ca_id'])."
					{$this->restrictedCategoriesSQL}
				ORDER BY ca_sort_order, ca_name
			");				
		
			$result = array(
				$Category['ca_name']	=>	$this->ATTRIBUTES['ca_id'],
			);
		}
		else
		{
	//		header("Location: /index.php");
			die;
		}
	} else {
		$startCategory = 'IS NULL';
		$result = array();
	}
	
	$this->getChildCategories($this->ATTRIBUTES['as_id'],$startCategory,'',$result,$isReturnAll, $this->ATTRIBUTES['ForAdmin']);
	return $result;
?>
