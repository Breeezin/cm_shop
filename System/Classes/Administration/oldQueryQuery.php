<?php
	
	// Default some values
	$this->param('SearchKeyword','');
	$this->param('CustomOrder','');

	// filterbymulti is paramed later
	$this->param('Min',null);
	$this->param('Max',null);
	$this->param('FilterSQL',null);
	$this->param('FilterTablesSQL',null);
	$this->param('StartRow',null);
	$this->param('MaxRows',null);
	$this->param('CountOnly',false);
	$this->param('OrderBy','');
	$this->param('SortBy','');  // sortby should be either ASC or DESC
		
	
	ss_paramKey($params,'Min',$this->ATTRIBUTES['Min']);
	ss_paramKey($params,'Max',$this->ATTRIBUTES['Max']);
	ss_paramKey($params,'FilterSQL',$this->ATTRIBUTES['FilterSQL']);
	ss_paramKey($params,'FilterTablesSQL',$this->ATTRIBUTES['FilterTablesSQL']);
	ss_paramKey($params,'StartRow',$this->ATTRIBUTES['StartRow']);
	ss_paramKey($params,'MaxRows',$this->ATTRIBUTES['MaxRows']);
	ss_paramKey($params,'SearchKeyword',$this->ATTRIBUTES['SearchKeyword']);
	ss_paramKey($params,'CustomOrder',$this->ATTRIBUTES['CustomOrder']);
	ss_paramKey($params,'CountOnly',$this->ATTRIBUTES['CountOnly']);
	
	

	// build selectJoin SQL
/*	$selectJoinSQL = '';
	foreach ($this->fieldSet as $field) {
		if (is_a($field,'selectfield') and ($field->linkTableName != NULL)) {
			$selectJoinSQL .= " LEFT JOIN {$field->linkTableName} ON {$field->linkTableName}.{$field->linkQueryValueField} = {$this->tableName}.{$field->name}";
		}
	}*/
	
	// build searchKeyword SQL
	$searchKeywordSQL = '';
	$searchKeyword = $params['SearchKeyword'];
	if (strlen($searchKeyword) > 0) {
		$searchKeywordSQL = 'AND (0 = 1';
		$searchKeyword = str_replace("'","''",$searchKeyword);
		$searchfields = $this->tableDisplayFields;
		if (count($this->tableSearchFields)) {
			$searchfields = $this->tableSearchFields;
		}
		//ss_DumpVarDie($this);
		foreach($searchfields as $field) {			
			$searchKeywordSQL .= ' OR '.$field." like '%".$searchKeyword."%'";
		}
		if (count($this->tableSearchFieldsFromOption)) {
			$searchList = '';
			foreach($this->tableSearchFieldsFromOption as $searchfield => $setting) {	
				$Q_GetOptionIDs = query("SELECT {$setting['joinField']} AS Field FROM {$setting['table']} WHERE {$setting['displayField']} like '%".$searchKeyword."%' GROUP BY Field");		
				if ($Q_GetOptionIDs->numRows()) {
					while($option = $Q_GetOptionIDs->fetchRow()) {
						$searchList = ListAppend($searchList, "'{$option['Field']}'");
					}
					$searchKeywordSQL .= ' OR '.$searchfield." IN (".$searchList.")";
				}
			}	
		}
		$searchKeywordSQL .= ')';		
	}
	
	// build order by SQL
	$orderBySQL = '';
	$comma = '';
	if (strlen($this->ATTRIBUTES['OrderBy'])) {
		if (array_key_exists($this->ATTRIBUTES['OrderBy'], $this->tableSearchFieldsFromOption) ){
			$setting = $this->tableSearchFieldsFromOption[$this->ATTRIBUTES['OrderBy']];
			if (!strlen($this->tablePrefix)){
				die("need to set up tablePrefix");
			}
			$whereGetOptionIDs = '';
			if (strlen($setting['groupField'])) {
				
				$whereGetOptionIDs = "WHERE {$setting['groupField']} = '".str_replace($this->tablePrefix, '', $this->ATTRIBUTES['OrderBy'])."'";
			}
			$orderBySQL = $this->ATTRIBUTES['OrderBy'].' IS NULL';
			$Q_GetOptionIDs = query("SELECT {$setting['joinField']} AS Field FROM {$setting['table']} $whereGetOptionIDs ORDER BY Field {$this->ATTRIBUTES['SortBy']}");		
			if ($Q_GetOptionIDs->numRows()) {
				
				while($option = $Q_GetOptionIDs->fetchRow()) {
					$orderBySQL = ListAppend($orderBySQL, "{$this->ATTRIBUTES['OrderBy']} = '{$option['Field']}'");
				}
				
			}
		} else {
			$orderBySQL = $this->ATTRIBUTES['OrderBy']." {$this->ATTRIBUTES['SortBy']}, 1";
		}
	} else {
		if ($params['CustomOrder'] != null) {
			if (is_array($params['CustomOrder'])) {
				foreach ($params['CustomOrder'] as $field) {
					$orderBySQL .= "$comma $field";
					$comma = ',';
				}
			} else {
				$orderBySQL .= "$comma {$params['CustomOrder']}";
			}
		} else {
			foreach ($this->tableOrderBy as $field => $fieldName) {
				$orderBySQL .= "$comma $field";
				$comma = ',';
			}
			$orderBySQL .= "$comma 1";
		}
	}
	// build parent table SQL
	$parentTableSQL = '';
	if ($this->parentTable != NULL and !array_key_exists('NoParentLink',$this->ATTRIBUTES)) {
		if (array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
			$parentTableSQL = "AND ({$this->parentTable->linkField} = '".escape($this->ATTRIBUTES[$this->parentTable->linkField])."')";
		} else {
			if (!array_key_exists('ReturnAll',$this->ATTRIBUTES)) {						
				$parentTableSQL = "AND ({$this->parentTable->linkField} IS NULL)";
			}
		}
	}
	
	// build primary key range SQL
	$pkMinSQL = ''; $pkMaxSQL = '';
	if ($params['Min'] != NULL) $pkMinSQL = "AND {$this->tablePrimaryKey} >= {$params['Min']}";
	if ($params['Max'] != NULL) $pkMaxSQL = "AND {$this->tablePrimaryKey} <= {$params['Max']}";
	
	// exclude deleted fields SQL
	$deletedSQL = '';
	if ($this->tableDeleteFlag != NULL) {
		$deletedSQL = "AND (({$this->tableDeleteFlag} IS NULL)";
		if (array_key_exists('allowDeleted',$this->ATTRIBUTES)) {
			$deletedSQL .= escape(" OR ($this->tablePrimaryKey IN ({$this->ATTRIBUTES['allowDeleted']}))");
		}
		$deletedSQL .= ")";
	}
	
	
	// exclude deleted fields SQL
	$assetlinkSQL = '';
	if ($this->tableAssetLink != NULL AND $this->assetLink != NULL) {				
		$assetlinkSQL = "AND ({$this->tableAssetLink} = {$this->assetLink})";			
	}
	
	$limitSQL = '';
	if (($params['StartRow'] !== null) && ($params['MaxRows'] !== null)) {
		$limitSQL = "LIMIT {$params['StartRow']},{$params['MaxRows']}";
	}
	
	$selectSQL = '*';
	if ($params['CountOnly']) {
		$selectSQL = 'COUNT(*) AS TotalRows';
	}
	
	// build Multi filterby sql
	$filtertable = '';
	
	if (count($this->filterByMulti)) {
		foreach ($this->filterByMulti as $filter) {
			$this->param('FilterBy'.$filter['name'],'');
			if (strlen($this->ATTRIBUTES['FilterBy'.$filter['name']])) {
				$theFilter = $filter['filters'][$this->ATTRIBUTES['FilterBy'.$filter['name']]];
				$params['FilterSQL'] .= " AND ".$theFilter['filterSQL'];	
				if (array_key_exists('filterTablesSQL',$theFilter)) {
					if (strpos($filtertable,$theFilter['filterTablesSQL']) === false) {
						$filtertable .= ','.$theFilter['filterTablesSQL'];
					}
				}
				
			}
		}
	}		
	
	if (count($this->querySQLFilter)) {
		$filterSQL = 'AND 1 ( 1';
		foreach ($this->querySQLFilter as $aFilter) {
			$filterSQL .= " AND ".$aFilter;
		}
		$filterSQL .= '}';
	} else {
		$filterSQL = '';
	}
	
	if (strlen($params['FilterTablesSQL']) and $params['FilterTablesSQL']{0} != ',') {
		$filtertable = ','.$params['FilterTablesSQL'];
	}
	// do the query
	$result = query("
		SELECT $selectSQL FROM $this->tableName $filtertable {$this->joinTables}
		WHERE 1 = 1
			$parentTableSQL
			$pkMinSQL
			$pkMaxSQL
			$searchKeywordSQL
			$deletedSQL
			$assetlinkSQL
			{$params['FilterSQL']}
			{$this->joinConditions}
		ORDER BY $orderBySQL
		$limitSQL
	");
		
	if ($params['CountOnly']) {
		$row = $result->fetchRow();
		$result = $row['TotalRows'];
	} 
	
	return $result;
?>