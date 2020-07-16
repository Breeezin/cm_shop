<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
		<?php 
		$image = '<img src="'.$this->classDirectory.'/Templates/Images/h-stats10.gif" ALT="Enquiry Manager Stats" border="0">';
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     	$image = "<a href=\"javascript:void(0)\" onClick=\"showhide('EnquiryManagerStats', true)\">".$image."</a>";
	     }
	     ?>
		<?=$image?>		
	   </Td>
	</TR>
	<TR>
		<TD>	
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats" id="EnquiryManagerStats" style="display:<?=$tableShow?>">
	
	<?
		$loopyMax = 1;
		if (array_key_exists('Service',$this->ATTRIBUTES)) {
			$loopyMax = 2;
		}
		for ($loopy=0; $loopy < $loopyMax; $loopy++) {
			if ($loopy) {
				$allhitsDefined = $allhitsDefined_Received;
				$whereSQL = $whereSQL_Received;
				$totalHits = $totalHits_Received;	
			}
	?>
	
	  <tr>
	     <td>
	     <?php
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	    		 
		  <p>  
		  	There have been <?=$totalHits?> enquiries.
	        </p>
	      <?php } ?>     	  
	        <p>	        	
				<?=$allhitsDefined?> <BR>
			 <?php
	     if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	
				There have been <?=$totalHits?> enquires.<?php } ?>     	
			</P>
		
	      <?php $counter = 0;
	    		$barWidth = 200;
	    		
	    		while ($asset = $Q_AssetStats->fetchRow()) { 
	    			
	    			// need to find the total enquiries for this asset
		    		$TotalHits = getRow("
		    			SELECT Count(EnID) AS TotalHits
						FROM EnquiryManager_{$asset['as_id']}
						WHERE 1=1 $whereSQL
		    		");
	    			$asset['TotalHits'] = $TotalHits['TotalHits'];
	    					
	    	?>
	    		
	    		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline"><strong><?=$asset['as_name']?> - Statuses</strong>&nbsp;&nbsp;Total <?=$asset['TotalHits']?> <?=ss_pluralize($asset['TotalHits'], "enquiry", "enquiries")?></td>
	          <td width="20" class="bottomline"><div align="center"><strong>Hits</strong></div></td>	          
	          <td width="250" bgcolor="#EFEFEF" class="bottomline"><strong>% of Total</strong></td>
	        </tr>
	    <?php 	
	    		
	    		$Q_Hits = query("
	    			SELECT Count(EnID) AS Hits, EnStatusLink, StName
					FROM EnquiryManager_{$asset['as_id']}, EnquiryManager_Statuses
					WHERE sts_as_id = {$asset['as_id']}
	    				AND sts_id = EnStatusLink
	    				$whereSQL
					GROUP BY EnStatusLink
	    		");
	    		 $counter = 0;
				while($aHit = $Q_Hits->fetchRow()) {				
					//$result = new Request("Asset.PathFromID", array('as_id'=> $aHit['sts_as_id']));				
					
					//$resource =  $result->value;
						//$disResource = substr($resource,0,50);
						$width = ($aHit['Hits'] / $asset['TotalHits']) * $barWidth;
						if ($width < 2) $width = 2;
						//$fullresource = $resource;
						$description = $aHit['StName'];
							
						
						print <<< EOD
				        <tr>
				          <td bgcolor="#FFFFFF">$description</td>
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
						if ($counter >= 10 AND !strlen($this->ATTRIBUTES["EnquiryManagerParam"])) {
							break;
						}
				}
			?>    
	      </table>  <BR>
	        <?php } ?>    
	   </td>
	  </tr>

	  
	  <tr>
	     <td>
	        <p>	        	
				<?=$allhitsDefined?> <BR>
			</P>
		
	      <?php $counter = 0;
	    		$barWidth = 200;
	    		
	    		while ($asset = $Q_AssetStats->fetchRow()) { 
	    			
	    			// need to find the total enquiries for this asset
		    		$TotalHits = getRow("
		    			SELECT Count(EnID) AS TotalHits
						FROM EnquiryManager_{$asset['as_id']}
						WHERE 1=1 $whereSQL
		    		");
	    			$asset['TotalHits'] = $TotalHits['TotalHits'];
	    					
	    	?>
	    		
	    		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline"><strong><?=$asset['as_name']?> - Recipients</strong>&nbsp;&nbsp;Total <?=$asset['TotalHits']?> <?=ss_pluralize($asset['TotalHits'], "enquiry", "enquiries")?></td>
	          <td width="20" class="bottomline"><div align="center"><strong>Hits</strong></div></td>	          
	          <td width="250" bgcolor="#EFEFEF" class="bottomline"><strong>% of Total</strong></td>
	        </tr>
	    <?php 	
	    		
	    		$Q_Hits = query("
	    			SELECT Count(EnID) AS Hits, EnUserLink, us_first_name, us_last_name
					FROM EnquiryManager_{$asset['as_id']}, users
					WHERE us_id = EnUserLink
	    				$whereSQL
					GROUP BY EnUserLink
	    		");
	    		 $counter = 0;
				while($aHit = $Q_Hits->fetchRow()) {				
					//$result = new Request("Asset.PathFromID", array('as_id'=> $aHit['sts_as_id']));				
					
					//$resource =  $result->value;
						//$disResource = substr($resource,0,50);
						$width = ($aHit['Hits'] / $asset['TotalHits']) * $barWidth;
						if ($width < 2) $width = 2;
						//$fullresource = $resource;
						$description = $aHit['us_first_name'].' '.$aHit['us_last_name'];
							
						
						print <<< EOD
				        <tr>
				          <td bgcolor="#FFFFFF">$description</td>
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
						if ($counter >= 10 AND !strlen($this->ATTRIBUTES["EnquiryManagerRecipientParam"])) {
							break;
						}
				}
			?>    
	      </table>  <BR>
	        <?php } ?>    
	   </td>
	  </tr>
	  
	<?
		}
	?>
	  
	  </table>   
	</td>
</tr>



</table>