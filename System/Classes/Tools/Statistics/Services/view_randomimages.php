<INPUT name="ShopPrParam" type="hidden" value="<?=$this->ATTRIBUTES['RandomImagesParam']?>">
<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
		<?php 
		$image = '<img src="'.$this->classDirectory.'/Templates/Images/h-stats7.gif" ALT="Random Images Stats" border="0">';
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     	$image = "<a href=\"javascript:void(0)\" onClick=\"showhide('RandomImagesStats', true)\">".$image."</a>";
	     }
	     ?>
		<?=$image?>		
	   </Td>
	</TR>
	<TR>
		<TD>	
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats" id="RandomImagesStats" style="display:<?=$tableShow?>">
	  <tr>
	     <td>
	     <?php
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	    		 
		  <p>  
		  	There have been <?=$totalHits?> hits on your website. <BR>
	        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Random Image Link Hits', 'RandomImages');">reset them</A>.
	        </p>
	      <?php } ?>     	  
	        <p>	        	
				<?=$allhitsDefined?> <BR>
			 <?php
	     if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	
				There have been <?=$totalHits?> hits on your website.<?php } ?>     	
			</P>
		
	      <?php $counter = 0;
	    		$barWidth = 200;
	    		
	    		while($randAsset = $Q_RandomImages->fetchRow()) { ?>
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline"><strong><?=$randAsset['as_name']?></strong>&nbsp;&nbsp;Total <?=$randAsset['TotalHits']?> link <?=ss_pluralize($randAsset['TotalHits'], "Hit", "Hits")?></td>
	          <td width="20" class="bottomline"><div align="center"><strong>Hits</strong></div></td>	          
	          <td width="250" bgcolor="#EFEFEF" class="bottomline"><strong>% of Total</strong></td>
	        </tr>
	    <?php 	
	    		
	    		$Q_RandomHits = query("SELECT Count(ris_id) AS Hits, ris_link
										FROM random_images_statistics
										WHERE ris_as_id = {$randAsset['as_id']}
	    								$whereSQL
										GROUP BY ris_link
	    								
	    						");
	    		 $counter = 0;
				while($aHit = $Q_RandomHits->fetchRow()) {				
					//$result = new Request("Asset.PathFromID", array('as_id'=> $aHit['sts_as_id']));				
					
					//$resource =  $result->value;
					if ($resource != null) {
						//$disResource = substr($resource,0,50);
						$width = ($aHit['Hits'] / $randAsset['TotalHits']) * $barWidth;
						if ($width < 2) $width = 2;
						//$fullresource = $resource;
						$referral = $aHit['ris_link'];												
						$referral = "<A HREF=\"http://{$aHit['ris_link']}\" target=\"_blank\">$referral</A>";
							
						
						print <<< EOD
				        <tr>
				          <td bgcolor="#FFFFFF">$referral</td>
				          <td bgcolor="#d6d6d6"><div align="center">{$aHit['Hits']}</div></td>					  				         				         
				          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
				              <tr>
				                <td width="120">&nbsp;</td>
				              </tr>
				            </table>
				           </td>
				        </tr>
EOD;
						$counter++;
						if ($counter >= 10 AND !strlen($this->ATTRIBUTES["RandomImagesParam"])) {
							break;
						}
					}		
				}
			?>    
	      </table>  <BR>
	        <?php } ?>    
	   </td>
	  </tr>
	</table>   
	</td>
</tr>



<TR>
		<TD>	
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats" id="RandomImagesDisplayStats" style="display:<?=$tableShow?>">
	  <tr>
	     <td>
	     <?php
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	    		 
		  <p>  
		  	There have been <?=$totalHits_Display?> image displays on your website. <BR>
	        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Random Image Display', 'RandomImagesDisplay');">reset them</A>.
	        </p>
	      <?php } ?>     	  
	        <p>	        	
				<?=$allhitsDefined_Display?> <BR>
			 <?php
	     if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	
				There have been <?=$totalHits_Display?> hits on your website.<?php } ?>     	
			</P>
		
	      <?php $counter = 0;
	    		$barWidth = 200;
	    		
	    		while($randAsset = $Q_RandomImages_Display->fetchRow()) { 	    			
	    	?>
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline"><strong><?=$randAsset['as_name']?></strong>&nbsp;&nbsp;Total <?=$randAsset['TotalHits']?> image <?=ss_pluralize($randAsset['TotalHits'], "Display", "Displays")?></td>	          
	          <td width="20" class="bottomline"><div align="center"><strong>Display</strong></div></td>	          
	          <td width="250" bgcolor="#EFEFEF" class="bottomline"><strong>% of Total</strong></td>
	        </tr>
	    <?php 	
	    		
	    		
	    		$counter = 0;
	    		$Q_RandomHits = query("SELECT Count(rids_timestamp) AS Hits, rids_image_uuid 
										FROM random_images_display_statistics
										WHERE rids_as_id = {$randAsset['as_id']}
	    								$whereSQL_Display
										GROUP BY rids_image_uuid 	    								
	    					");
	    		
				while($aHit = $Q_RandomHits->fetchRow()) {				
					//$result = new Request("Asset.PathFromID", array('as_id'=> $aHit['sts_as_id']));				
					$referral = '';
					$resource =  null;
					foreach($assets["{$randAsset['as_id']}"] as $anImage) {
						if ($anImage['uuid'] == $aHit['rids_image_uuid']) {
							$resource = $anImage['title'];
							$referral = $anImage['url'];
							break;
						}
					}
					
					if ($resource != null) {
						//$disResource = substr($resource,0,50);
						$width = ($aHit['Hits'] / $randAsset['TotalHits']) * $barWidth;
						if ($width < 2) $width = 2;
						//$fullresource = $resource;
						if (strlen($referral)) {
							$referral = "<A HREF=\"".$GLOBALS['cfg']['currentServer'].ss_storeForAsset($randAsset['as_id'])."$referral\" target=\"_blank\">$resource</A>";
						} else {
							$referral = $resource;
						}
							
						
						print <<< EOD
				        <tr>
				          <td bgcolor="#FFFFFF">$referral</td>
				          <td bgcolor="#d6d6d6"><div align="center">{$aHit['Hits']}</div></td>					  				         				         
				          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
				              <tr>
				                <td width="120">&nbsp;</td>
				              </tr>
				            </table>
				           </td>
				        </tr>
EOD;
						$counter++;
						if ($counter >= 10 AND !strlen($this->ATTRIBUTES["RandomImagesDisplayParam"])) {
							break;
						}
					}		
				}
			?>    
	      </table>  <BR>
	        <?php } ?>    
	   </td>
	  </tr>
	</table>   
	</td>
</tr>
</table>