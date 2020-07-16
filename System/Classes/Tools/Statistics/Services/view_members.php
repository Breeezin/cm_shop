<?php 
	$barWidth = 200;
	$counter = 0;
?>
<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
		<?php 
		$image = '<img src="'.$this->classDirectory.'/Templates/Images/h-stats9.gif" ALT="Member Logins" border="0">';
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     	$image = "<a href=\"javascript:void(0)\" onClick=\"showhide('MembersStats', true)\">".$image."</a>";
	     }
	     ?>
		<?=$image?>		
		</TD>
	</TR>
	<TR>
		<TD>		
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats" id="MembersStats" style="display:<?=$tableShow?>">
	  <tr>
	    <td>
	    <?php 
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	    		  
		  <p>  
		  	There have been <?=$totalHits?> logins on your website. <BR>
	        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Members\' Logins', 'Members');">reset them</A>.
	        </p>
	      <?php } ?>     	  
	        <p>
	        	
				<?=$allhitsDefined?> <BR>
		<?php 
	    	 if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
				There have been <?=$totalHits?> logins on your website.
		<?php } ?>     	  		
			</P>
		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline"><strong>Members</strong></td>
	          <td width="30" class="bottomline"><div align="center"><strong>Logins</strong></div></td>	          
	          <td width="80" class="bottomline"><div align="center"><strong>Last Login</strong></div></td>	          	          
	          <td width="250" class="bottomline">&nbsp;</td>
	        </tr>		
		
			
			<?php 
				$topCount = 0;
				while($aHit = $Q_ResourceHits->fetchRow()) {				
					$aUser = getRow("SELECT us_first_name, us_last_name FROM users WHERE us_id = {$aHit['los_us_id']}");
					$aLogin = getRow("SELECT Max(los_timestamp) LastLogin FROM login_statistics WHERE los_us_id = {$aHit['los_us_id']}");
					$lastLogin = date(ss_PHPDateFormat('dd-mm-yy h:s'), ss_SQLtoTimeStamp($aLogin['LastLogin']));
					if ($counter == 0) $topCount = $aHit['Hits'];
					
					$resource =  $aUser['us_first_name']." ".$aUser['us_last_name'];
					if ($resource != null) {
						$disResource = substr($resource,0,50);
						$width = ($aHit['Hits'] / $topCount) * $barWidth;
						if ($width < 2) $width = 2;
						$fullresource = $resource;
						print <<< EOD
						<tr>
				          <td bgcolor="#FFFFFF">$disResource</td>
				          <td bgcolor="d6d6d6"><div align="center">{$aHit['Hits']}</div></td>				          				          
				          <td width="80"><div align="center">{$lastLogin}</div></td>					  					 			          
				          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
				            <tr>
				              <td width="120">&nbsp;</td>
				            </tr>
				          </table></td>
				        </tr>
EOD;
						$counter++;
						if ($counter >= 10 AND !strlen($this->ATTRIBUTES["MembersParam"])) {
							break;
						}
					}		
				}
			?>
	      </table>      
	   </td>
	  </tr>
	</table>
	</TD>
	</TR>
</table>   	