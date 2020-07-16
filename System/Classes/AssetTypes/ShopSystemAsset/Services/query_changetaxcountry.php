<?

	$this->param('BackURL');


	if (ss_optionExists("Shop Products Limited countries")) {
        // Limit Query to those listed, :-)
        $options = array();
		foreach (ListToArray(ss_optionExists("Shop Products Limited countries"),":") as $countryDef) {
			$options[ListFirst($countryDef)] = "'". strtoupper(ListLast($countryDef))."'";
		}

        $allowed = join(',', $options);

        if ( count($options) > 0) {
    		$Q_Countries = query("
        		SELECT * FROM countries
        		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
                AND cn_three_code IN (".ArrayToList($options).")
        		ORDER BY cn_name
            ");
        } else {
    		$Q_Countries = query("
        		SELECT * FROM countries
                WHERE cn_three_code='XXX'
            ");
        }
	} else {
    	$Q_Countries = query("
    		SELECT * FROM countries
    		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
    		ORDER BY cn_name
    	");
    }



?>