<?php
/*
*  The sole purpose of this class is too return a custom query of users from given groups
*/
requireOnceClass('Administration');
class SchedulerUsersAdministration extends Administration {
	
	
	function exposeServices() {
		return array(
			'SchedulerUsersAdministration.Query'	=>	array('method'=>'query'));
	}
    
    function query($params = Array()){
         $this->param('groups');
         if (is_array($this->ATTRIBUTES['groups']) && count ( $this->ATTRIBUTES['groups']) ) {
             if ( count ( $this->ATTRIBUTES['groups']) == 1)
                 // limit as it is otherwise consumes too much memory 
                 $Q_Users = query("select us_id, us_first_name, us_last_name from users, user_user_groups
                         where us_id = uug_us_id 
                         and us_id > 2
                         and uug_ug_id = {$this->ATTRIBUTES['groups'][0]} limit 1000");
             else
                 // limit as it is otherwise consumes too much memory 
                 $Q_Users = query("select us_id, us_first_name, us_last_name from users, user_user_groups
                         where us_id = uug_us_id 
                         and us_id > 2
                         and uug_ug_id in (".ArrayToList($this->ATTRIBUTES['groups']).") 
                         limit 1000");
         } else 
             $Q_Users = query("select us_id, us_first_name, us_last_name from users, user_user_groups
                     where 1=2");
            
            
         return $Q_Users;
    }

//	function SchedulerUsersAdministration() {
 //   }
}
?>
