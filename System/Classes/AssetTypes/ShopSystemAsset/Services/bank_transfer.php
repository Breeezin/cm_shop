<?php
query( "Update transactions set tr_completed = 1
		where tr_id = {$this->ATTRIBUTES['tr_id']} and tr_token = '{$this->ATTRIBUTES['tr_token']}'" );
location(rawurldecode($backURL));
?>
