<?PHP 
	$data = array();
	
	$allhitsDefined = 'Below are the statistics for the top ten pages on your website.  For details of the hits 
			on all your pages <A HREF="index.php?act=statistics.Display&AllPageHits=1">click here</A>.';
	
	if (array_key_exists("AllPageHits", $this->ATTRIBUTES)) {
		$allhitsDefined = 'Below are the statistics for all the pages on your website.  To return to the details of the top ten 
			pages, <A HREF="index.php?act=statistics.Display">click here</A>.';
	}

	$barWidth = 200;
	$counter = 0;
?>



<SCRIPT LANGUAGE="Javascript">	
	function confirmReset() {
		if (confirm('Are you sure you want to reset all statistics for your website?')) {
			document.location = 'index.cfm?act=statistics.Reset';
		}
	}
	
	function getStats(theForm, type) {
		
		url = 'index.php?act=statistics.'+type + 'Search&DateFrom='+theForm[type+'DateFrom'].value + '&DateTo=' + theForm[type+'DateTo'].value;
		openWindow(url, "StatsWindow", 650, 600);
		
	}
	
	function openWindow(url, name, width, height ) {		
	     w = width
	     h = height
	     x = Math.round((screen.availWidth-w)/2); //center the top edge
	     y = Math.round((screen.availHeight-h)/2); //center the left edge
	     
	     popupWin = window.open(url, "Preview", "width="+w+",height="+h+",toolbar=0,location=0,scrollbars=1,statusbar=1,menubar=0,resizable=1,top="+y+",left="+x+",screeenY="+y+",screenX="+x);
	
	     popupWin.creator=self;
	     return popupWin;
	}
</SCRIPT>

<img src="<?=$this->classDirectory?>/Templates/Images/h-usage.gif" ALT="Usage"><br>
<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
  <tr>
    <td>
	  There have been <?=$totalHits?> hits on your website. 
      <P>
        We recommend you periodically <A HREF="Javascript:void(0);" ONCLICK="window.print();">print your stats</A> as a record and then <A HREF="Javascript:confirmReset();">reset them</A>. 
     </P>      	 
	  	<P>
			<?=$allhitsDefined?>
		</P>
	
      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
        <tr bgcolor="#EFEFEF">
          <td class="bottomline"><strong>Page</strong></td>
          <td width="30" class="bottomline"><div align="center"><strong>Hits</strong></div></td>
          <td width="30" class="bottomline"><div align="center"><strong>users</strong></div></td>
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
			          <td><div align="center">{$aHit['UsersHits']}</div></td>					  					 
			          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
			            <tr>
			              <td width="120">&nbsp;</td>
			            </tr>
			          </table></td>
			        </tr>
EOD;
					$counter++;
					if ($counter >= 10 AND !array_key_exists("AllPageHits", $this->ATTRIBUTES)) {
						break;
					}
				}		
			}
		?>
      </table>      
   </td>
  </tr>
</table>
<BR>
<img src="<?=$this->classDirectory?>/Templates/Images/h-referrals.gif" ALT="Referrals"><BR>
<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
  <tr>
     <td>There have been <?=$totalReferralHits?> referrals to your website<?php if($totalReferralHits > 0)print(", the top ten referrers are..."); else print('.');?><BR>
      <br>
      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
        <tr bgcolor="#EFEFEF">
          <td class="bottomline"><strong>Referrals Page</strong></td>
          <td width="20" class="bottomline"><div align="center"><strong>Hits</strong></div></td>
          <td width="20" class="bottomline"><div align="center"><strong>Pages</strong></div></td>
          <td width="20" class="bottomline"><div align="center"><strong>users</strong></div></td>
          <td width="250" bgcolor="#EFEFEF" class="bottomline"><strong>% of Total</strong></td>
        </tr>
    <?php 			
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
				}		
			}
		?>    
      </table>      
   </td>
  </tr>
</table>
<BR>


<?php	
	$dirPath = expandPath('');
		/*
		$dirPath = expandPath('');
		$size = disk_total_space($dirPath);
				
		$dirSize = sprintf("%1.1f", (disk_total_space($dirPath)/1024));
		$dirSize2 = sprintf("%01.2f", disk_total_space($dirPath) / (1024*1024));
				
		print $dirPath.' '.$dirSize.' '.$dirSize2;			
		$mb = 1024*1024;
	    /// display available and used space
		echo "<BR>Total available space: " .round((disk_total_space($dirPath)) / $mb) . " MB\r\n";
		*/
		$cmdDu = "/usr/bin/du -cs $dirPath | /usr/bin/grep total";		
	    $cm_result = exec($cmdDu);
		$cm_result = str_replace(chr(13).chr(10),chr(10),$cm_result);				
		
		// define some characters for clarity
		$tab = chr(9);
		$temp = ListToArray($cm_result, $tab);
		$dirSize = str_replace('M','',$temp[0]);

		$cmdDu = "/usr/bin/du -cs {$dirPath}Custom/ContentStore/Assets/ | /usr/bin/grep total";		
	    $cm_result = exec($cmdDu);
		$cm_result = str_replace(chr(13).chr(10),chr(10),$cm_result);				
		
		// define some characters for clarity
		$tab = chr(9);
		$temp = ListToArray($cm_result, $tab);
		$customSize = str_replace('M','',$temp[0]);
			
	
	global $cfg;
	$spaceAllowanceMB = 10;
	
	$space = ss_optionExists("Disk Space Allowance");
	if ($space) {
		$spaceAllowanceMB = $space;
	}
	
	$spaceAllowance = $spaceAllowanceMB * 1024;	
	
	//$spaceAllowance = $spaceAllowance * (1024 * 1024);
		
	$dbSize = ss_get_DB_size();
	
	$totalUsageMB = round(($dirSize+$dbSize)/1024);
	$allowedSpaceMB = round(($spaceAllowance - $dbSize - $dirSize)/1024);	
	$systemSizeMB = round(($dirSize - $customSize) / 1024);
	$dbSizeMB = round($dbSize/1024);
	$customSizeMB = round($customSize/1024);
	
	
	$sysWidth = ($systemSizeMB / $spaceAllowanceMB) * 200;
	$customWidth = ($customSizeMB / $spaceAllowanceMB) * 200;
	$dbWidth = ($dbSizeMB / $spaceAllowanceMB) * 200;
	$freeSpaceWidth = 200 - (($systemSizeMB+$dbSizeMB+$dbSizeMB)/$spaceAllowanceMB) * 200;
	//ss_DumpVarDie($spaceAllowanceMB, $freeSpaceWidth);
	if ($freeSpaceWidth < 0) $freeSpaceWidth = 0; 

?>
<br>
<img src="<?=$this->classDirectory?>/Templates/Images/h-diskspace.gif" width="113" height="24" alt="Disk Space"><br>
<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
  <tr>
    <td>
 
	Your website is currently using <?=$totalUsageMB?> Megabytes and has approximately <?=$allowedSpaceMB?> Megabytes remaining...<BR>
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
                <td>System (<?=$systemSizeMB?>MB)</td>
              </tr>
              <tr>
                <td><table width="15" height="15" border="0" cellpadding="0" cellspacing="0" class="statscustombar1">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                </td>
                <td>Content (<?=$customSizeMB?>MB)</td>
              </tr>
              <tr>
                <td><table width="15" height="15" border="0" cellpadding="0" cellspacing="0" class="statsdbbar1">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                </td>
                <td>Database (<?=$dbSizeMB?>MB)</td>
              </tr>
              <tr>
                <td><table width="15" height="15" border="0" cellpadding="0" cellspacing="0" class="statsbarwhite1">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                </td>
                <td>Free Space (<?=$allowedSpaceMB?>MB)</td>
              </tr>
            </table>
            </td>
        </tr>
      </table>                                        
	
	
	  </td>
</tr>
  
  </table>  
<BR>
<?php 

	if ($Q_RandomImages->numRows()) {
?>
	<img src="<?=$this->classDirectory?>/Templates/Images/h-usage.gif" ALT="Random Images Stats"><br>
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
	  <tr>
	     <td>
	      <?php while($randAsset = $Q_RandomImages->fetchRow()) { ?>
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
										GROUP BY ris_link
	    								LIMIT 0 , 10
	    						");
	    		
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

					}		
				}
			?>    
	      </table>  <BR>
	        <?php } ?>    
	   </td>
	  </tr>
	</table>
<?php 
	
	}
?>