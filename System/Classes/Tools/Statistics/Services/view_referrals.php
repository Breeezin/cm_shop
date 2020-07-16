<?php 
	$barWidth = 200;
	$counter = 0;
	$resource = 1;
?>
<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
		<?php 
		$image = '<img src="'.$this->classDirectory.'/Templates/Images/h-stats3.gif" ALT="Referrals" border="0">';
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     	$image = "<a href=\"javascript:void(0)\" onClick=\"showhide('ReferralsStats', true)\">".$image."</a>";
	     }
	     ?>
		<?=$image?>			
		</TD>
	</TR>
	<TR>
		<TD>	
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats" id="ReferralsStats" style="display:<?=$tableShow?>">
	  <tr>
	     <td>
	     <?php 
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
	      <P>
	      	There have been <?=$totalReferralHits?> hits on your website.<br>
	        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Page Hits and Referrals', 'Referrals');">reset them</A>. 
	     </P>
	     <?php } ?>     	 
		  	<P>
				<?=$allreferralsDefined?><BR>
				 <?php 
	     if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
				There have been <?=$totalReferralHits?> hits on your website.
			<?php } ?>	
			</P>
		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline"><strong>Referrals Page</strong></td>
	          <td width="20" class="bottomline"><div align="center"><strong>Hits</strong></div></td>
	          <td width="20" class="bottomline"><div align="center"><strong>Pages</strong></div></td>
	          <td width="20" class="bottomline"><div align="center"><strong>users</strong></div></td>
	          <td width="250" bgcolor="#EFEFEF" class="bottomline"><strong>% of Total</strong></td>
	        </tr>
	    <?php 	
	    		$counter = 0;		
				while($aHit = $Q_ReferralHits->fetchRow()) {				
					//$result = new Request("Asset.PathFromID", array('as_id'=> $aHit['sts_as_id']));				
					
					//$resource =  $result->value;
					
					if ($resource != null) {
						//$disResource = substr($resource,0,50);
						$width = ($aHit['Hits'] / $totalReferralHits) * $barWidth;
						if ($width < 2) $width = 2;
						//$fullresource = $resource;
						$referral = $aHit['sts_referrer'];
						$disReferral = substr($referral,0,50);
						if (!strlen($referral)) $referral = "Unknown Referrer";
						else $referral = "<A HREF=\"javascript:void(0);\" onClick=\"openWindow('$referral','Referrer', 650,600);\">$disReferral</A>";
							
						
						print <<< EOD
				        <tr>
				          <td bgcolor="#FFFFFF">$referral</td>
				          <td bgcolor="#d6d6d6"><div align="center">{$aHit['Hits']}</div></td>					  
				          <td><div align="center">{$aHit['PagesHits']}</div></td>		
				          <td><div align="center">{$aHit['UsersHits']}</div></td>					          
				          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
				              <tr>
				                <td width="120">&nbsp;</td>
				              </tr>
				            </table>
				           </td>
				        </tr>
				        
EOD;
						$counter++;
						if ($counter > 10 AND !strlen($this->ATTRIBUTES["ReferralsParam"])) {
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