<?php 
	$this->param('bl_id');
	$this->param('BackURL');

	$bl_id = (int) $this->ATTRIBUTES['bl_id'];

	ss_log_message( "removing blacklist id:$bl_id" );

	query( "update users set us_bl_id = NULL where us_bl_id = $bl_id" );
	query( "delete from blacklist_ip_addresses where blip_bl_id = $bl_id" );
	query( "delete from blacklist_cc_details where blcc_bl_id = $bl_id" );
	query( "delete from blacklist where bl_id = $bl_id" );

	locationRelative($this->ATTRIBUTES['BackURL']);
?>

