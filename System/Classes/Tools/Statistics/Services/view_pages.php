<?php 
	$barWidth = 200;
	$counter = 0;
?>
<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
		<?php 
		$image = '<img src="'.$this->classDirectory.'/Templates/Images/h-stats2.gif" ALT="Pages Usage" border="0">';
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     	$image = "<a href=\"javascript:void(0)\" onClick=\"showhide('PagesStats', true)\">".$image."</a>";
	     }
	     ?>
		<?=$image?>		
		</TD>
	</TR>
	<TR>
		<TD>		
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats" id="PagesStats" style="display:<?=$tableShow?>">
	  <tr>
	    <td>
	    <?php 
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
	    
		  
		  <p>  
		  	There have been <?=$totalHits?> hits on your website. <BR>
	        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Page Hits and Referrals', 'Pages');">reset them</A>.
	        </p>
	      <?php } ?>     	  
	        <p>
	        	
				<?=$allhitsDefined?> <BR>
		<?php 
	    	 if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
				There have been <?=$totalHits?> hits on your website.
		<?php } ?>     	  		
			</P>
		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline"><strong>Page</strong></td>
	          <td width="30" class="bottomline"><div align="center"><strong>Hits</strong></div></td>
	          <td width="60" class="bottomline"><div align="center"><strong>users</strong></div></td>
	          <td width="250" class="bottomline">&nbsp;</td>
	        </tr>		
		
			
			<?php 
				$topCount = 0;
				while($aHit = $Q_ResourceHits->fetchRow()) {				
					$result = new Request("Asset.PathFromID", array('as_id'=> $aHit['sts_as_id']));				
					if ($counter == 0) $topCount = $aHit['Hits'];
					
					$resource =  $result->value;
					if ($resource != null) {
						$disResource = substr($resource,0,50);
						$width = ($aHit['Hits'] / $topCount) * $barWidth;
						if ($width < 2) $width = 2;
						$fullresource = $resource;
						print <<< EOD
						<tr>
				          <td bgcolor="#FFFFFF"><A HREF="javascript:void(0);" onClick="openWindow('$fullresource','$resource', 650,600);">$disResource</A></td>
				          <td bgcolor="d6d6d6"><div align="center">{$aHit['Hits']}</div></td>
				          <td ><div align="center">{$aHit['UsersHits']}</div></td>					  					 
				          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
				            <tr>
				              <td width="120">&nbsp;</td>
				            </tr>
				          </table></td>
				        </tr>
EOD;
						$counter++;
						if ($counter >= 10 AND !strlen($this->ATTRIBUTES["PagesParam"])) {
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