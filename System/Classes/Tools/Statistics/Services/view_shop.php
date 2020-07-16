<?php 
	$barWidth = 200;
	$counter = 0;
?>
<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
		<?php 
		$image = '<img src="'.$this->classDirectory.'/Templates/Images/h-stats4.gif" ALT="Shop" border="0">';
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     	$image = "<a href=\"javascript:void(0)\" onClick=\"showhide('ShopStats', true)\">".$image."</a>";
	     }
	     ?>
		<?=$image?>		
		</TD>
	</TR>
	<TR>
		<TD><INPUT type="hidden" name="ShopPrParam" value="<?=$this->ATTRIBUTES['ShopPrParam']?>">
	<TABLE id="ShopStats" style="display:<?=$tableShow?>">
	<TR>
		<TD>
		<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
		  <tr>
		    <td>
		    <?php 
		     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
		     ?> 
			  There have been <?=$totalHits?> product views on your website. 
		      <P>
		        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Product/Category Views', 'ShopViews');">reset them</A>. 
		     </P>      	 
		      <?php } ?>  
			  	<P>
					<?=$allproducthitsDefined?> <BR>
			<?php 
		    	 if (array_key_exists('Service', $this->ATTRIBUTES)) {
		     ?> 
					 There have been <?=$totalHits?> product views on your website.
			<?php } ?>     	  		
				</P>
			
		      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
		        <tr bgcolor="#EFEFEF">
		          <td class="bottomline" nowrap><strong>Product</strong></td>
		          <td width="30" class="bottomline"><div align="center"><strong>Views</strong></div></td>
		          <td width="250" class="bottomline">&nbsp;</td>
		        </tr>		
			
				
				<?php 
					$topCount = 0;
					
					while($aHit = $Q_ProductHits->fetchRow()) {									
						if ($counter == 0) $topCount = $aHit['Hits'];					
						$resource =  $aHit['ShStProductName']." (".$aHit['sst_ca_id'].")";
						
						$disResource = substr($resource,0,100);
						$width = ($aHit['Hits'] / $topCount) * $barWidth;
						if ($width < 2) $width = 2;					
						print <<< EOD
						<tr>
				          <td bgcolor="#FFFFFF"><A HREF="javascript:void(0);" onClick="openWindow('$shopAssetPath/Service/Detail/Product/{$aHit['sst_pr_id']}','Shop', 650,600);">$disResource</A></td>
				          <td bgcolor="d6d6d6"><div align="center">{$aHit['Hits']}</div></td>			         
				          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
				            <tr>
				              <td width="120">&nbsp;</td>
				            </tr>
				          </table></td>
				        </tr>
EOD;
						$counter++;
						if ($counter >= 10 AND !strlen($this->ATTRIBUTES["ShopPrParam"])) {
							break;
						}
					}		
				
				?>
		      </table>      
		   </td>
		  </tr>
		</table>
	</TD>
	</TR>
	<TR>
		<TD><INPUT type="hidden" name="ShopCatParam" value="<?=$this->ATTRIBUTES['ShopCatParam']?>">
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
	  <tr>
	    <td>
	     <?php 
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
		  There have been <?=$totalCatHits?> category views on your website. 
		 <?php } ?>
	      <P>
	        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Product/Category Views', 'ShopViews');">reset them</A>. 
	     </P>      	 
		  	<P>
				<?=$allcathitsDefined?><BR>
			<?php 
	    	 if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
				 There have been <?=$totalCatHits?> category views on your website.
		<?php } ?>  
			</P>
		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline" nowrap><strong>Product Category</strong></td>
	          <td width="30" class="bottomline"><div align="center"><strong>Views</strong></div></td>
	          <td width="250" class="bottomline">&nbsp;</td>
	        </tr>		
		
			
			<?php
				$barWidth = 200;
				$counter = 0; 
				$topCount = 0;
				
				while($aHit = $Q_CategoryHits->fetchRow()) {									
					if ($counter == 0) $topCount = $aHit['Hits'];					
					$resource =  $aHit['sst_ca_id'];
					
					$disResource = substr($resource,0,100);
					if ($aHit['Hits'] == 0) 
						$width = 0;
					else 
						$width = ($aHit['Hits'] / $topCount) * $barWidth;
					if ($width < 2) $width = 2;					
					print <<< EOD
					<tr>
			          <td bgcolor="#FFFFFF">$disResource</td>
			          <td bgcolor="d6d6d6"><div align="center">{$aHit['Hits']}</div></td>			         
			          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
			            <tr>
			              <td width="120">&nbsp;</td>
			            </tr>
			          </table></td>
			        </tr>
EOD;
					$counter++;
					if ($counter >= 10 AND !strlen($this->ATTRIBUTES["ShopCatParam"])) {
						break;
					}
				}		
			
			?>
	      </table>      
	   </td>
	  </tr>
	</table>
	</TD>
	</TR>
	<TR>
		<TD><INPUT type="hidden" name="ShopOrderParam" value="<?=$this->ATTRIBUTES['ShopOrderParam']?>">
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
	  <tr>
	    <td>
	     <?php 
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
		  There have been <?=$totalOrderHits?> product orders on your website. 
		 
	      <P>
	        We recommend you periodically print your stats as a record and then <A HREF="Javascript:confirmReset('Product Orders', 'ShopOrders');">reset them</A>. 
	     </P>   <?php } ?>     	 
		  	<P>
				<?=$allorderhitsDefined?><BR>
			<?php 
	    	 if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	
	     	There have been <?=$totalOrderHits?> product orders on your website. 
		 <?php } ?> 
			</P>
		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline" nowrap><strong>Product</strong></td>
	          <td width="30" class="bottomline"><div align="center"><strong>Orders</strong></div></td>
	          <td width="250" class="bottomline">&nbsp;</td>
	        </tr>		
		
			
			<?php
				$barWidth = 200;
				$counter = 0; 
				$topCount = 0;
				
				while($aHit = $Q_OrderHits->fetchRow()) {									
					if ($counter == 0) $topCount = $aHit['Hits'];					
					$resource =  $aHit['orpr_pr_name'];
					
					$disResource = substr($resource,0,100);
					if ($aHit['Hits'] == 0) 
						$width = 0;
					else 
						$width = ($aHit['Hits'] / $topCount) * $barWidth;
					if ($width < 2) $width = 2;					
					print <<< EOD
					<tr>
			          <td bgcolor="#FFFFFF">$disResource</td>
			          <td bgcolor="d6d6d6"><div align="center">{$aHit['Hits']}</div></td>			         
			          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
			            <tr>
			              <td width="120">&nbsp;</td>
			            </tr>
			          </table></td>
			        </tr>
EOD;
					$counter++;
					if ($counter >= 10 AND !strlen($this->ATTRIBUTES["ShopOrderParam"])) {
						break;
					}
				}		
			
			?>
	      </table>      
	   </td>
	  </tr>
	</table>
	</TD>
	</TR>

	<? if (ss_optionExists("Shop Acme Rockets")) { ?>
	<TR>
		<TD><INPUT type="hidden" name="ShopWishListParam" value="<?=$this->ATTRIBUTES['ShopWishListParam']?>">
	<table width="100%" border="0" cellpadding="10" cellspacing="0" class="border-stats">
	  <tr>
	    <td>
	     <?php 
	     if (!array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 
		  There have been <?=$totalWishHits?> products wished for on your website. 
		 
	        <?php } ?>     	 
		  	<P>
				<?=$allwishhitsDefined?><BR>
			<?php 
	    	 if (array_key_exists('Service', $this->ATTRIBUTES)) {
	     ?> 	
	     	There have been <?=$totalWishHits?> product wished for on your website. 
		 <?php } ?> 
			</P>
		
	      <table width="100%" border="0" cellpadding="3" cellspacing="0" class="border-statsinner">
	        <tr bgcolor="#EFEFEF">
	          <td class="bottomline" nowrap><strong>Product</strong></td>
	          <td width="30" class="bottomline"><div align="center"><strong>Wish Count</strong></div></td>
	          <td width="250" class="bottomline">&nbsp;</td>
	        </tr>		
		
			
			<?php
				$barWidth = 200;
				$counter = 0; 
				$topCount = 0;
				
				while($aHit = $Q_WishHits->fetchRow()) {									
					if ($counter == 0) $topCount = $aHit['Hits'];					
					$resource =  $aHit['pr_name'];
					
					$disResource = substr($resource,0,100);
					if ($aHit['Hits'] == 0) 
						$width = 0;
					else 
						$width = ($aHit['Hits'] / $topCount) * $barWidth;
					if ($width < 2) $width = 2;					
					print <<< EOD
					<tr>
			          <td bgcolor="#FFFFFF">$disResource</td>
			          <td bgcolor="d6d6d6"><div align="center">{$aHit['Hits']}</div></td>			         
			          <td width="250" bgcolor="#FFFFFF"><table width="$width" border="0" cellpadding="0" cellspacing="0" class="statsbar">
			            <tr>
			              <td width="120">&nbsp;</td>
			            </tr>
			          </table></td>
			        </tr>
EOD;
					$counter++;
					if ($counter >= 10 AND !strlen($this->ATTRIBUTES["ShopWishListParam"])) {
						break;
					}
				}		
			
			?>
	      </table>      
	   </td>
	  </tr>
	</table>
	   </td>
	  </tr>
	<? 
		}	
	?>
	</table>
	</TD>
	</TR>
	
	
</table>   	