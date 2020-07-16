<?php
/*
* This script assumes that the user has permission to access the resource
*/
   
   // can not parse EvUser Field directly
    $Q_AllEvents = query("
        select ev_id, ev_users from events   
           where 
           (
				EvStart BETWEEN '".$this->ATTRIBUTES['starttime']."' AND '".$this->ATTRIBUTES['endtime']."'
				OR
				EvEnd BETWEEN '".$this->ATTRIBUTES['starttime']."' AND '".$this->ATTRIBUTES['endtime']."'
				OR
				(
                    EvStart <= '".$this->ATTRIBUTES['starttime']."'
                    AND
                    EvEnd >= '".$this->ATTRIBUTES['starttime']."'
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
    
    $ev_idSQL = " 1 = 2 ";
    if (count($foundEventIDs) == 1)
        $ev_idSQL = " ev_id = {$foundEventIDs[0]} ";
    else if (count($foundEventIDs) > 1)
        $ev_idSQL = " ev_id in (".ArrayToList($foundEventIDs).")";
      
    $Q_Events = query("  select * from events , eventTypes   where $ev_idSQL  and ev_type_link = EvTyID");
    $tasksArray = array();
    $counter = 1;
    while ($row = $Q_Events->fetchRow()) {
        $tasksArray[$counter] = $row;
        // round each down to the nearest 15 minutes Eg: [EvStart] => 2005-11-29 04:00:00
        $s = getdate(strtotime($row['EvStart']));
        $m = ($s['minutes'] < 15?0:( $s['minutes'] < 30?15:($s['minutes'] < 45?30:45 ))) ;
        $tasksArray[$counter]['S'] = date('Y-m-d H:i:s',mktime($s['hours'],$m,0,$s['mon'],$s['mday'],$s['year']));

        $s = getdate(strtotime($row['EvEnd']));
        $m = ($s['minutes'] < 15?0:( $s['minutes'] < 30?15:($s['minutes'] < 45?30:45 ))) ;
        $tasksArray[$counter]['F'] = date('Y-m-d H:i:s',mktime($s['hours'],$m,0,$s['mon'],$s['mday'],$s['year']));
        $tasksArray[$counter]['color'] = $taskTypesArray[$row['ev_type_link']]['EvTyColor'];
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
     $Q_EventCount = getRow("  select count(*) as count from events    where $ev_idSQL   ");

    $data = array(
        'Events'    =>  $tasksArray,
        'EventCount'    =>  $Q_EventCount['count'],
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
    
    /*
    * This generates a matrix of events to display
    * This contains 25 * 4 entries, the first is a flag
    */
    $table = array();  
    $rowspan = array();  
    $table[-1] = array();
    $rowspan[-1] = array();
    
    $s = getdate(strtotime($this->ATTRIBUTES['starttime']));
    $f = getdate(strtotime($this->ATTRIBUTES['endtime']));
    $stime = $s['hours'] * 4 + ($s['minutes'] < 15?0:( $s['minutes'] < 30?1:($s['minutes'] < 45?2:3 )));
    $ftime = $f['hours'] * 4 + ($f['minutes'] < 15?0:( $f['minutes'] < 30?1:($f['minutes'] < 45?2:3 )));
    
    for ($i = $stime ; $i < ($ftime) ; $i++ ) {
       $table[$i] = array();
       $rowspan[$i] = array();
    }

    $this->ATTRIBUTES['dateTS'] = strtotime($this->ATTRIBUTES['date']);
    foreach ($tasksArray as $idx => $xx) {
        $tasksArray[$idx]['ST'] = strtotime($tasksArray[$idx]['S']);
        $tasksArray[$idx]['FT'] = strtotime($tasksArray[$idx]['F']);
        
        $table[-1][] = $tasksArray[$idx];
        $rowspan[-1][] = 1;
        
        $x1_m = Date("i",$tasksArray[$idx]['ST']);
        $x1_m = ($x1_m == 0?0:$x1_m/15);
        $x2_m = Date("i",$tasksArray[$idx]['FT']);
        $x2_m = ($x2_m == 0?0:$x2_m/15);
        
        $x1 = Date("G",$tasksArray[$idx]['ST'])*4 + $x1_m;
        $x2 = Date("G",$tasksArray[$idx]['FT'])*4 + $x2_m;
        // adjust if multi-day
        if ( $tasksArray[$idx]['ST'] <  strtotime($this->ATTRIBUTES['starttime']) ) { //strtotime($x1) ) { //Date('Y-m-d H:i:00',$this->ATTRIBUTES['dateTS'])) ) {
            $x1 = $stime; //0;
        }
        if ( $tasksArray[$idx]['FT'] >  strtotime($this->ATTRIBUTES['endtime']) ) { //strtotime($x2)) { //Date('Y-m-d 23:45:00',$this->ATTRIBUTES['dateTS']) ) ){
            $x2 = $ftime; //24*4;
        }

        # find a free position
        $pos = -1;
        $found = false;
        while ( $found == false ) {
            $found = true;
            $pos ++;
            for ( $i = $x1; $i <= $x2 ; $i++ ) {
                if (isset($table[$i][$pos]) ) {
                    $found = false;
                    continue;
                }
            }
        }
        for ( $i = $x1; $i <= $x2 ; $i++ ) {
            if ( $i == $x1 ) {
                $table[$i][$pos] = $tasksArray[$idx];
                $rowspan[$i][$pos] = ($x2 - $x1  + 1); // with <= add "+1"
            } else {
                $table[$i][$pos] = -1;
            }
        }
    }
    $maxcol = 1;
    for ($i = $stime ; $i < ($ftime) ; $i++ ) {
        $maxcol = max($maxcol,count($table[$i]));
    }
 
     $tableContent = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"98%\">";
     $tableContent .= "<tr><td width=\"65%\">";
	 $tableContent .= "<table class=\"outer\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"98%\"><tr><td>\n";
     $tableContent .= "<table class=\"navigate\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"98%\">\n";
     $tableContent .= " <tr height=\"35\">\n";
     $tableContent .= " <td nowrap=\"nowrap\" width=\"20\" align=\"center\">";
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(-1)\" value=\"&lt;\">"; 
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(-7)\" value=\"&lt;&lt;\">"; 

	 $tableContent .= " </td>\n";
	 $tableContent .= " <td align=\"center\" nowrap=\"nowrap\" class=\"calhead\">\n";
	 $tableContent .= "&nbsp;". Date('l, j F Y',$this->ATTRIBUTES['dateTS']) ."&nbsp;";
	 $tableContent .= " </td>\n";
	 $tableContent .= " <td nowrap=\"nowrap\" width=\"20\" align=\"center\">\n";
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(1)\" value=\"&gt;\">"; 
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(7)\" value=\"&gt;&gt;\">"; 
     $tableContent .= "</td></tr>\n";
	 $tableContent .= "</table>\n";
	 $tableContent .= "<tr><td>\n";
     
     $this->t = Date("Ymd",$this->ATTRIBUTES['dateTS']);
     
	 $tableContent .= "<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"98%\">\n";
     
    for ($i = $stime ; $i <= ($ftime) ; $i++ ) {
       $tableContent .= " <tr>\n";

       if ( $i == -1 ) {
        $tableContent .= " <th id=\"time_".$this->t."\" class=\"daytime\" width=\"10%\" align=\"right\" valign=\"top\">&nbsp;\n";
       } else {
	   	 $tableContent .= " <th id=\"time_".$this->t.sprintf("%02d",($i==0?0:15*$i/4))."00\" class=\"daytime\" width=\"10%\" align=\"right\" valign=\"top\">\n";
          $tableContent .= "<a class=\"nodeco\" 
            href=\"javascript:createNew('"
                .sprintf("%02d",($i==0?0:$i/4)) ."','" 
                .sprintf("%02d",($i%4*15)). "'); \"                
                onmouseover=\"self.status='Create a new appointment' ;return true;\" 
                onmouseout=\"self.status='';return true;\" 
            title=\"Create a new appointment\">".
            sprintf("%02d",($i==0?0:$i/4)).':'.
            sprintf("%02d",($i%4*15))."</a>";  
       }
       $tableContent .= "&nbsp;</th>\n";
       
       for ($c = 0 ; $c < $maxcol ; $c++ ) {
         if ( isset ( $table[$i][$c] ) ) {
           if ( $table[$i][$c] == -1 ) {
             # SKIP occupied by rowspan
           } else {
                $rid = getRow("select AssetID from Assets where AssetType like 'Scheduler'");
		        $imgDir = ss_secretStoreForAsset($rid['AssetID'],"Icons");
                 $tableContent .= " <td style=\"border:1px solid #999999;\" class=\"line". (1+($i % 2)) ."\" valign=\"top\" rowspan=\"". $rowspan[$i][$c]."\" bgcolor=\"{$table[$i][$c]['color']}\">";
                 $tableContent .= "<table height=\"98%\"  cellpadding=\"3\" cellspacing=\"0\" class=\"event\">\n";
                 $tableContent .= '<tr><td>';
                 $tableContent .= '<img src="Images/clock.gif" border="0" width="14" height="14" alt="Time"> ' . date('g:ia, l, M',strtotime( $table[$i][$c]['S'] )) . ' - ' . date('g:ia, l, M \'y',strtotime( $table[$i][$c]['F'] )) . '<br>';

                 $tableContent .= '<img src="Images/user.gif" border="0" width="14" height="14" alt="Users"> ' .$table[$i][$c]['users']. '<br>';
                 
                 if(strlen($table[$i][$c]['EvTyImage']) > 0)
                        $tableContent .= '<img src="'.$imgDir.'/'.$table[$i][$c]['EvTyImage'].'" border="0" width="14" height="14" alt="'.$table[$i][$c]['EvTyName'].'"> ' ;
                 // brief description
                 if (strlen($table[$i][$c]['EvDescription']) > 500)
                    $tableContent .= nl2br(substr($table[$i][$c]['EvDescription'],0,496)) . ' ...<br>';
                 else
                    $tableContent .= nl2br($table[$i][$c]['EvDescription']). '<br>';
                    
                 // location
                 if (strlen($table[$i][$c]['EvLocation']) > 500)
                    $tableContent .= nl2br(substr($table[$i][$c]['EvLocation'],0,496)) . ' ...<br><br>';
                 else
                    $tableContent .= nl2br($table[$i][$c]['EvLocation']). '<br><br>';
                    
                    $tableContent .= "<a class=\"nodeco\" href=\"/$assetPath/Service/Details/ev_id/{$table[$i][$c]['ev_id']}".
                        "/br/$backURL\">View Details</a>";  
                    $tableContent .= " <a class=\"nodeco\" href=\"/$assetPath/Service/Edit/ev_id/{$table[$i][$c]['ev_id']}".
                        "/br/$backURL\">Edit</a>";  
                    $tableContent .= "&nbsp;&nbsp; <a href=\"javascript:deleteEvent('{$table[$i][$c]['ev_id']}','$backURL');\" class=\"nodeco\">Delete</a>";             // /$assetPath/Service/Delete/ev_id/{$table[$i][$c]['ev_id']}/br/$backURL
                         

                 $tableContent .= '</td></tr>'; 
                 $tableContent .= " </table></td>\n";
                 
             }
           } else {
               $tableContent .= "<td class=\"line". (1+($i % 2)) ."\" valign=\"top\">";
		       $tableContent .= "&nbsp;</td>\n";
           }
        }
        $tableContent .= " </tr>\n";
     }
	 $tableContent .= " </table>\n";
	 $tableContent .= "</td></tr>\n";
     
	 $tableContent .= "<tr><td>\n";
	 $tableContent .= "<table class=\"navigate\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"98%\">\n";
     $tableContent .= " <tr height=\"30\">\n";
     $tableContent .= " <td nowrap=\"nowrap\" width=\"20\" align=\"center\">";
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(-1)\" value=\"&lt;\">"; 
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(-7)\" value=\"&lt;&lt;\">"; 
	 $tableContent .= " </td>\n";
	 $tableContent .= " <td align=\"center\" nowrap=\"nowrap\">&nbsp;\n";
 	 $tableContent .= " </td>\n";
	 $tableContent .= " <td nowrap=\"nowrap\" width=\"30\" align=\"center\">\n";
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(1)\" value=\"&gt;\">"; 
$tableContent .= "<input type=\"submit\" class=\"submitLink\" onClick=\"traverse(7)\" value=\"&gt;&gt;\">"; 
     	 $tableContent .= "</td></tr>\n";
	 $tableContent .= "</table>\n";
	 $tableContent .= "</td></tr></table>\n";

         $tableContent .= "</td>\n";
         $tableContent .= "<td valign=top align=center style=\"padding-left:10px;padding-right:10px\">\n";
         $tableContent .= "</td></tr></table>\n";
  
    $data['tableContent'] = $tableContent;
    $this->useTemplate('Daily',$data);
?>
