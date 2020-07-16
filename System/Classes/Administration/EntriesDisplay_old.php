<?php
	
	// set the title for the page
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'] . ' : ' . $this->plural;
	
	// Load the template file
	timeBlock('vlib');
	require_once('Libraries/vlibTemplate/vlibTemplate.php');
	timeBlock('act');
	$currentDirectory = dirname(__FILE__);
	$tmpl = new vlibTemplate("${currentDirectory}/Templates/Entries.html");

	// Get the URL to view this page again (useful for breadcrumbs and to return)
	$backURL = $_SESSION['BackStack']->getURL();
	
	// Set globals for the template
	$tmpl->setVar('script_name', 			basename($_SERVER['SCRIPT_NAME']));
	$tmpl->setVar('prefix', 				$this->prefix);
	$tmpl->setVar('tableName', 				$this->tableName);
	$tmpl->setVar('tablePrimaryKey', 		$this->tablePrimaryKey);
	$tmpl->setVar('singular', 				$this->singular);
	$tmpl->setVar('plural', 				$this->plural);
	$tmpl->setVar('rfa', 					$backURL);
	$tmpl->setVar('rowsPerPage', 			$this->ATTRIBUTES['RowsPerPage']);
	$tmpl->setVar('numRows',	 			$result->numRows());
	$tmpl->setVar('hasRows',				$result->numRows() > 0);
	$tmpl->setVar('searchKeyword',			$this->ATTRIBUTES['SearchKeyword']);
	$tmpl->setVar('currentPage',			$this->ATTRIBUTES['CurrentPage']);
//	$tmpl->setVar('sid',					$SID);
	$tmpl->setVar('breadCrumbs',			$this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">'.$this->plural.'</A>');
	$tmpl->setVar('pagethru',				$pageThru->display);

	// Only include the parent hidden field if this administration has a parent
	$parentHiddenField = '';
	if (($this->parentTable != NULL) && array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
		$parentHiddenField = "<INPUT TYPE=\"HIDDEN\" NAME=\"{$this->parentTable->linkField}\" VALUE=\"{$this->ATTRIBUTES[$this->parentTable->linkField]}\">";
	}
	$tmpl->setVar('parentHiddenField', 		$parentHiddenField);
	
	// Initialise the loops

	$data = array();
	$evenRow = TRUE;
	$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$currentRow = $startRow;
	while(($row = $result->fetchRow(DB_FETCHMODE_ASSOC,$currentRow)) 
		&& ($currentRow < $startRow+$this->ATTRIBUTES['RowsPerPage'])) { 
		$temp = array();
		$temp['rowClass'] = $evenRow ? 'AdminEvenRow' : 'AdminOddRow';
		$evenRow = !$evenRow;
		$temp['index'] = $row[$this->tablePrimaryKey];
		$temp['breadCrumbs'] = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">';
		
		$comma = '';
		$temp['cells'] = array();
		foreach ($this->tableDisplayFields as $displayField) {
			// Find the value for the field
			$value = $row[$displayField];

			// Add the field into the bread crumbs
			$temp['breadCrumbs'] .= $comma;	$comma = ',';
			$temp['breadCrumbs'] .= ss_HTMLEditFormat($value);

			// Add the field into the cells display for this row
			array_push($temp['cells'],array('fieldValue' => ss_HTMLEditFormat($value)));
		}
		$temp['breadCrumbs'] .= '</A>';

		$theChildren = array();
		foreach ($this->children as $child) {
			$newChild = array();
			$newChild['parentKey'] = $row[$this->tablePrimaryKey];
			$newChild['prefix'] = $child->prefix;
			$newChild['linkField'] = $child->linkField;
			$newChild['childplural'] = $child->plural;
			$newChild['breadCrumbs'] = $temp['breadCrumbs'];
	
			// Add the field into the cells display for this row
			array_push($theChildren,$newChild);
		}
		$temp['children'] = $theChildren;
		$temp['childCount'] = count($theChildren);
		
		array_push($data,$temp);
		$currentRow++;
	}	
	$tmpl->setLoop('rows',$data);

	// Display the template
	$tmpl->pparse();
?>
