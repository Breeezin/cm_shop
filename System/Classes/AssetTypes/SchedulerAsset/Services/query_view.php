<?php 
    $today = mktime(0,0,0,date("m")  , date("d"), date("Y"));
    $afternoon = mktime(8,0,0,date("m")  , date("d"), date("Y"));
    $morning = mktime(18,0,0,date("m")  , date("d"), date("Y"));

    // daily or weekly
    $this->param('T','daily'); 

    // only used for daily 
    $this->param('starttime',date('Y-m-d H:i:s',$morning));
    $this->param('endtime',date('Y-m-d H:i:s',$afternoon));
    // quick date check
    if (strtotime($this->ATTRIBUTES['starttime']) > strtotime($this->ATTRIBUTES['endtime'])){
        $temp = $this->ATTRIBUTES['starttime'];
        $this->ATTRIBUTES['starttime'] = $this->ATTRIBUTES['endtime']; 
        $this->ATTRIBUTES['endtime'] = $temp;   
    }
    // date, as off Midnight in the morning
    $this->param('date',date('Y-m-d H:i:s',$today));
    

    // get the index of the current backstack
     $backURL = $_SESSION['BackStack']->getCurrentIndex();
    // USAGE :: 
    // $backURL = $_SESSION['BackStack']->getIndexedURL($backURL,'/'.$assetPath);
  
    // userid : super user id means view all
    // but this shouldn't bypass other controls
    if ( $isAdmin || $showAll )
        $this->param('userid','0');
    else
        $this->param('userid',$userid);
        
    if ($this->ATTRIBUTES['userid'] == 'me')
        $this->ATTRIBUTES['userid'] = $userid;

     $assetPath = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));

    $Q_AllTaskTypes = query("
        select * from EventTypes
    ");
    $taskTypesArray = array();
    while ($row = $Q_AllTaskTypes->fetchRow()) {
        $taskTypesArray[$row['EvTyID']] = $row;
    } 
   
if ( $isAdmin || $showAll || ($isMember && $userid == $this->ATTRIBUTES['userid'])) {
     $allUsers = null;
     if ( $isAdmin || $showAll) {
        // these are the users that can access the asset 
        ss_paramKey($asset->cereal, 'AST_SCHEDULER_GROUPS', array());
        $parameters = array (
            'groups'               =>  $asset->cereal['AST_SCHEDULER_GROUPS'],           
        );
        $temp = new Request("Security.Sudo",array('Action'=>'start'));
        $result = new Request('SchedulerUsersAdministration.Query',$parameters);
        $temp = new Request("Security.Sudo",array('Action'=>'stop'));
        $result = $result->value;
        $allUsers = array();
        while ($row = $result->fetchRow()) {
            $allUsers[$row['us_first_name'].' '.$row['us_last_name']] = $row['us_id'];
        }
     }
     
    if ($this->ATTRIBUTES['T'] == 'daily') {
        include('daily.inc'); 
    } else {
        include('weekly.inc'); 
    }
    // both will return $Q_Events query
} else {
    echo 'Sorry you can not access this resource.';   
}
    
?>