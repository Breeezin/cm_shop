<?
	$errors = array();
	if (array_key_exists('Do',$this->ATTRIBUTES)) {
		$Q_Delete = query("
			DELETE FROM lottery_winners
			WHERE lotw_draw_date IS NULL
				AND lotw_upcoming IS NULL
		");
		
		// now insert the temporary record
		$productLink = ListFirst($this->ATTRIBUTES['Key'],'_');
		$Q_Insert = query("
			INSERT INTO lottery_winners (lotw_pr_id)  
			VALUES ($productLink)
		");
	}
	
?>