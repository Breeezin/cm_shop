<?php
/*
* This script assumes that the user has permission to access the resource
*/
    // date, as off Midnight in the morning
    $this->ATTRIBUTES['dateTS'] = strtotime($this->ATTRIBUTES['date']);
    $t = getdate($this->ATTRIBUTES['dateTS']);  // date('Y-m-d H:i:s',$today)
    $ts = mktime(12,0,0,$t['mon'],$t['mday'],$t['year']); 

	$xy=Date("w",$ts);

     /* Back to last Monday or Sunday before ts */
     while ( Date("w",$ts) != 1) { 
       $ts -= 86400;
     }

     $w0 =  (( 1 + Date("w",mktime(12,0,0,1,1, Date("Y",$ts) ) )) % 7) > 3;
     $wn = sprintf("%02d", Round( (Date("z",$ts)+7 ) / 7) );

    $rid = getRow("select as_id from assets where as_type like 'Scheduler'");
    $imgDir = ss_secretStoreForAsset($rid['as_id'],"Icons");

     #$yy = Date("y",$ts);
     $yy = Date("Y",$ts);
     $day_from = Date("d",$ts);
     $day_to = Date("d",$ts + 6 * 86400);
     $mon = Date("n",$ts);
     $m_name = Date("F",$ts);
     $mon_next = Date("n",$ts + 7 * 86400);
     $mn_name =  Date("F",$ts + 7 * 86400)."&nbsp;";
     $last_week = Date("Y-m-d H:i:00",$ts -  7 * 86400);
     $next_week = Date("Y-m-d H:i:00",$ts +  7 * 86400);
    

     if ($mn_name == $m_name)
     {
	$mn_name ="";
     }
  //  $tableContent = "<input type=\"hidden\" name=\"date\" id=\"date\" value=\"". $this->ATTRIBUTES['date'] ."\">";
     $tableContent = "<table class=\"navigate\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"98%\">\n";
     $tableContent .= " <tr height=\"35\">\n";
     $tableContent .= " <td nowrap=\"nowrap\" width=\"20\" align=\"center\">";
     $tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(-7)\" value=\"&lt;&lt;\">"; 

	 $tableContent .= " </td>\n";
	 $tableContent .= " <td align=\"center\" nowrap=\"nowrap\" class=\"calhead\">\n";
	 $tableContent .= "&nbsp;". Date('l, j F Y',$this->ATTRIBUTES['dateTS']) /*strftime($mod_strings['LBL_DATE_TITLE'],$from->ts)*/ ."&nbsp;";
	 $tableContent .= " </td>\n";
	 $tableContent .= " <td nowrap=\"nowrap\" width=\"20\" align=\"center\">\n";
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(7)\" value=\"&gt;&gt;\">"; 
     $tableContent .= "</td></tr>\n";
	 $tableContent .= "</table>\n";
     
     
	 $tableContent .= " </td></tr>\n";
	 $tableContent .= " <tr><td colspan=\"2\" class=\"inner\">\n";
	 $tableContent .= " <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"98%\" align=\"center\">\n";
     $day = 0;
     $col = 1;
     while ( $day < 7 ) {
	   if ($day!=7) {
		   $tref = Date("Ymd",$ts);
		   /* Select appointments for this day */
	     $from =  $ts - 12 * 3600;
	     $to =  $ts + 12 * 3600;
          $fts = date('Y-m-d H:i:s',$from+1);
          $tts = date('Y-m-d H:i:s',$to);
          
           // can not parse EvUser Field directly
            $Q_AllEvents = query("
                select ev_id, ev_users from events   
                   where 
                   (
				        ev_start BETWEEN '$fts' AND '$tts'
				        OR
				        ev_end BETWEEN '$fts' AND '$tts'
				        OR
				        (
                            ev_start <= '$fts'
                            AND
                            ev_end >= '$fts'
				        )
                   )
            ") ;

            $foundEventIDs = array();
            while ($row = $Q_AllEvents->fetchRow()) {
                if ( $this->ATTRIBUTES['userid'] == 0 ||
                     strpos(','.$row['ev_users'].',',','.$this->ATTRIBUTES['userid'].',') !== false ||
                     $row['ev_users'] == '') {
                    array_push($foundEventIDs,$row['ev_id']);
                }
            }

            $Q_AllEvents->free();
            
            $EvIDSQL = " 1 = 2 ";
            if (count($foundEventIDs) == 1)
                $EvIDSQL = " ev_id = {$foundEventIDs[0]} ";
            else if (count($foundEventIDs) > 1)
                $EvIDSQL = " ev_id in (".ArrayToList($foundEventIDs).")";
 
            $Q_Events = query("  select * from events , EventTypes   where $EvIDSQL  and EvTypeLink = EvTyID ");
            $tasksArray = array();
            $counter = 1;
            while ($row = $Q_Events->fetchRow()) {
                $tasksArray[$counter] = $row;
                // round each down to the nearest 15 minutes Eg: [ev_start] => 2005-11-29 04:00:00
                $s = getdate(strtotime($row['ev_start']));
                $m = ($s['minutes'] < 15?0:( $s['minutes'] < 30?15:($s['minutes'] < 45?30:45 ))) ;
                $tasksArray[$counter]['S'] = date('Y-m-d H:i:s',mktime($s['hours'],$m,0,$s['mon'],$s['mday'],$s['year']));

                $s = getdate(strtotime($row['ev_end']));
                $m = ($s['minutes'] < 15?0:( $s['minutes'] < 30?15:($s['minutes'] < 45?30:45 ))) ;
                $tasksArray[$counter]['F'] = date('Y-m-d H:i:s',mktime($s['hours'],$m,0,$s['mon'],$s['mday'],$s['year']));
                $tasksArray[$counter]['color'] = $taskTypesArray[$row['EvTypeLink']]['EvTyColor'];
                if( strlen( $tasksArray[$counter]['ev_users']) > 0 ){
                    $Q_Users = query("
                        Select * from users where us_id in (".$tasksArray[$counter]['ev_users'].")
                    ");
                    $users = '';
                    while ($row = $Q_Users->fetchRow()){
                        $users = $users. ' ' . $row['us_first_name'] . ' ' . $row['us_last_name'];
                    }
                    if ( strlen ($users) )
                        $users = substr($users, 0, strlen($users)-1);
                        
                    $tasksArray[$counter]['users'] = $users;
                } else{
                    $tasksArray[$counter]['users'] = "Everyone";
                }
                $counter++;        
            } 
             $Q_EventCount = getRow("  select count(*) as count from events    where $EvIDSQL   ");
             $Q_Events->free();

         $next = $this->NextDay($ts);
	
		   if ( $col == 1 ) {
			 $tableContent .= " <tr>\n";
		   }
		   
           if (  $day!=6 )
		        $tableContent .= "  <td valign=\"top\" width=\"49%\" height=\"98%\">\n";	
		   
		   # WEEK-TABLE STARTS
		   $tableContent .= "<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"98%\" valign=\"top\" height=\"98%\">\n";
		   $tableContent .= " <tr>\n";
    	   $tableContent .= "  <th style=\"background-color:#ffffff;\" bgcolor=\"ffffff\" class=\"weekhead\">\n";
  //         $tableContent .= " ".date('l, Y-m-d',$from)." ";

          $tableContent .= "<a class=\"nodeco\" 
            href=\"javascript:createNew('"
                .date('l, Y-m-d',$from). "'); \"                
                onmouseover=\"self.status='Create a new appointment' ;return true;\" 
                onmouseout=\"self.status='';return true;\" 
            title=\"Create a new appointment\">".
            date('l, Y-m-d',$from)."</a>";  

		   $tableContent .= "  </th>\n";
		   $tableContent .= " </tr>\n";
		   $tableContent .= " <tr>\n";
		   $tableContent .= "  <td width=\"49%\" cellpadding=\"5\" valign=\"top\" height=\"98%\" style=\"\">\n";	   
           
		   $hastable = false;
           foreach ($tasksArray as $task) {
                if ( !$hastable ) {
                    $tableContent .= "<tr><td width=\"98%\" class=\"eventSep\" colspan=\"2\">\n";
                    $tableContent .= "<table width=\"98%\" class=\"event\" cellspacing=\"5\"  border=\"0\">\n";
                    $hastable = true;
                } else {
                    $tableContent .= "  <tr><td width=\"98%\"  class=\"eventSep\" colspan=\"2\">&nbsp;</td></tr>\n";
                }
                
                $tableContent .= "<table style=\"border:1px solid #999999;background-color:#{$task['color']};\" bgcolor=\"{$task['color']}\" height=\"98%\" width=\"98%\" margin=\"3\" cellpadding=\"3\" cellspacing=\"0\" class=\"event\">\n";
                $tableContent .= '<tr><td>';
                $tableContent .= '<img src="Images/clock.gif" border="0" width="14" height="14" alt="Time"> ' .$task['S']. ' - ' .$task['F']. '<br>';
                $tableContent .= '<img src="Images/user.gif" border="0" width="14" height="14" alt="Users"> ' .$task['Users']. '<br>';

                if(strlen($task['EvTyImage']) > 0)
                    $tableContent .= '<img src="'.$imgDir.'/'.$task['EvTyImage'].'" border="0" width="14" height="14" alt="'.$task['EvTyName'].'"> ' ;
                // brief description
                if (strlen($task['EvDescription']) > 500)
                    $tableContent .= substr($task['EvDescription'],0,496) . ' ...<br>';
                else
                    $tableContent .= $task['EvDescription']. '<br>';
                // location
                if (strlen($task['EvLocation']) > 500)
                    $tableContent .= substr($task['EvDescription'],0,496) . ' ...<br>';
                else
                    $tableContent .= $task['EvLocation']. '<br>';

                $tableContent .= "<a class=\"nodeco\" href=\"/$assetPath/Service/Details/ev_id/{$task['ev_id']}".
                     "/br/$backURL\">View Details</a>";  
                $tableContent .= " <a class=\"nodeco\" href=\"/$assetPath/Service/Edit/ev_id/{$task['ev_id']}".
                     "/br/$backURL\">Edit</a>";  
                $tableContent .= "&nbsp;&nbsp; <a href=\"javascript:deleteEvent('{$task['ev_id']}','$backURL');\" class=\"nodeco\">Delete</a>";             // /$assetPath/Service/Delete/ev_id/{$table[$i][$c]['ev_id']}/br/$backURL

                $tableContent .= '</td></tr>'; 
                $tableContent .= " </table>\n";
		        $hastable = false;
           }
	
		   if ( $hastable ) {
			 $tableContent .= " </table>\n";
		   } else {
			  $tableContent .= "<br/>\n";
		   }
	
		   # DAY-TABLE ENDS
		   $tableContent .= "</td></tr>\n";
		   $tableContent .= "</table>\n";
	
           if ( $day!=5  ) {
		        $tableContent .= "  </td>\n";
		        if ( $col == 2 ) {
			        $tableContent .= " </tr>\n";
			        $col = 0;
		        }
            }
		   $day++;
		   $col++;
		   $ts = $next;
  	   } else {
			$day++;
			$col++;
	   }
     }
     if ( $col == 2 ) {
       $tableContent .= " </tr>\n";
     }
     $tableContent .= "</table>\n";
     $tableContent .= "</td></tr></table>\n";

    $data = array(
        'AllUsers'  =>  $allUsers,
        'AllTaskTypes'  =>  $taskTypesArray,    
        'isAdmin'    =>  $isAdmin,
        'End'    =>  $this->ATTRIBUTES['endtime'],
        'Start'    =>  $this->ATTRIBUTES['starttime'],
        'Date'    =>  $this->ATTRIBUTES['date'],
        'User'    =>  $this->ATTRIBUTES['userid'],
        'AssetPath'     =>  $assetPath,
        'T'      => $this->ATTRIBUTES['T'],  
        'showAll'  => $showAll, 
        'br'  => $backURL, 
    );
    
    $data['tableContent'] = $tableContent;
    $this->useTemplate('Weekly',$data);
?>
