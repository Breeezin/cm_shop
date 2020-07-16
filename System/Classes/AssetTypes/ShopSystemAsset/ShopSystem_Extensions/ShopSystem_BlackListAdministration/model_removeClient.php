<?php 
	$this->param('us_id');
	$this->param('BackURL');

	$us_id = (int) $this->ATTRIBUTES['us_id'];

	ss_log_message( "removing blacklist entries for us_id:$us_id" );


	query( "update users set us_bl_id = NULL where us_id = $us_id" );
	query( "delete from blacklist_ip_addresses where blip_bl_id in (select bl_id from blacklist where bl_us_id = $us_id)" );
	query( "delete from blacklist_cc_details where blcc_bl_id in (select bl_id from blacklist where bl_us_id = $us_id)" );
	query( "delete from blacklist where bl_us_id = $us_id" );

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
