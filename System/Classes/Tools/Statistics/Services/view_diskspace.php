<?php	
	die;
		
	$diskUsageInfo = ss_getDiskSpaceUsage();
	

	$sysWidth = ($diskUsageInfo['system'] / $diskUsageInfo['allowance']) * 200;
	$customWidth = ($diskUsageInfo['custom'] / $diskUsageInfo['allowance']) * 200;
	$dbWidth = ($diskUsageInfo['database'] / $diskUsageInfo['allowance']) * 200;
	$freeSpaceWidth = 200 - (($diskUsageInfo['system']+$diskUsageInfo['custom']+$diskUsageInfo['database'])/$diskUsageInfo['allowance']) * 200;
	//ss_DumpVarDie($spaceAllowanceMB, $freeSpaceWidth);
	$allowedSpaceMB = $diskUsageInfo['freespace'];
	if ($allowedSpaceMB < 0 || $freeSpaceWidth < 0) $freeSpaceWidth = 0; 
	
?>
<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
<a href="javascript:void(0)" onClick="showhide('DiskSpaceStats', true)"><img src="<?=$this->classDirectory?>/Templates/Images/h-stats8.gif" border="0" alt="Disk Space"></a>
</td>
</tr>
<tr>
    <td>
<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats" id="DiskSpaceStats" style="display:none">
  <tr>
    <td>
 
	Your website is currently using <?=$diskUsageInfo['total']?> Megabytes<?php if ($allowedSpaceMB >= 0) { ?> and has approximately <?=$allowedSpaceMB?> Megabytes remaining...<?php } else { print (".  It is exceeding its limit by ".($allowedSpaceMB*-1)." Megabytes."); }?><BR>
	<br>
	
      <table width="400" border="0" cellspacing="0" cellpadding="10">
        <tr align="left" valign="top">
          <td width="20%">
          <table width="200" height="40" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
            <tr>
              <td width="<?=$sysWidth?>" class="statssysbar"><DIV title="System">&nbsp;</DIV></td>
              <td width="<?=$customWidth?>" class="statscustombar"><DIV title="Content">&nbsp;</DIV></td>
              <td width="<?=$dbWidth?>" class="statsdbbar"><DIV title="Database">&nbsp;</DIV></td>
              <?php if ($freeSpaceWidth) {?>
              <td width="<?=$freeSpaceWidth?>" class="statsbarwhite">&nbsp;</td>
              <?php } ?>
            </tr>
          </table>
            </td>
          <td><strong>Key</strong>
            <table width="100" border="0" cellspacing="0" cellpadding="4">
              <tr>
                <td><table width="15" height="15" border="0" cellpadding="0" cellspacing="0" class="statssysbar1">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                </td>
                <td>System (<?=$diskUsageInfo['system']?>MB)</td>
              </tr>
              <tr>
                <td><table width="15" height="15" border="0" cellpadding="0" cellspacing="0" class="statscustombar1">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                </td>
                <td>Content (<?=$diskUsageInfo['custom']?>MB)</td>
              </tr>
              <tr>
                <td><table width="15" height="15" border="0" cellpadding="0" cellspacing="0" class="statsdbbar1">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                </td>
                <td>Database (<?=$diskUsageInfo['database']?>MB)</td>
              </tr>
              <tr>
                <td><table width="15" height="15" border="0" cellpadding="0" cellspacing="0" class="statsbar<?php if($allowedSpaceMB < 0) print "red"; else print "white"?>1">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                </td>
                <td><?php 
                		if($allowedSpaceMB < 0) {
                			print "Over Usage (".(-1*$allowedSpaceMB)."MB)";
                		} else {
                			print "Free Space ({$allowedSpaceMB}MB)";
                		}
                	?></td>
              </tr>
            </table>
            </td>
        </tr>
      </table>                                        
	
	
	  </td>
</tr>
  
  </table>  
  	
	  </td>
</tr>
  
  </table>  
