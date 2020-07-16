<?

	$this->param('BackURL');

	$Q_Countries = query("
		SELECT * FROM countries
		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
		ORDER BY cn_name
	");

?>