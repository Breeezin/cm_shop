<?php
	// admin splits issue
	$this->param('ce_id');

	$ce_id = (int)$this->ATTRIBUTES['ce_id'];

	if( $ce_id )
	{
		$show = getRow( "select * from client_issue_entry where ce_id = $ce_id" );

		if( $show )
		{
			$logLines = ss_grep_log( $show['ce_session'], $show['ce_website'] );
			echo "<br />";
			foreach ( $logLines as $line )
				echo $line."<br />";
		}
		else
		{
			echo "Invalid id";
		}
	}

?>
