  </p>
<link href="sty_admin.css" rel="stylesheet" type="text/css">

  <br>
  <br>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="bodytext">
    <tr>
      <td width="100" align="center" valign="top"><p><img src="Images/cogs1.gif" width="82" height="82"></p>
      <?php if ($data['HasOnlineShop']) { ?>
		      <p><img src="Images/cogs2.gif" width="82" height="82"></p>
      <?php } ?>
	  </td>
      <td align="left" valign="top"><img src="Images/holder.gif" width="25" height="15">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
            
              <td colspan="2"><img src="Images/main-configuration-h.gif" width="220" height="23"></td>
           
            </tr>
            <tr>
              <td width="25"><img src="Images/holder.gif" width="25" height="15"></td>
              <td align="left" valign="top">
              
              <?php
            	$Q_MultiSites = query("SELECT * FROM configuration");            	
            	if ($Q_MultiSites->numRows() and count($GLOBALS['cfg']['multiSites'])) {
            		print ('<p>');
            		while($aSite = $Q_MultiSites->fetchRow()) {            			
            ?>
            	<a href="<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Configuration.Edit&cfg_id=<?=$aSite['cfg_id']?>&BackURL=<?php print(ss_URLEncodedFormat($data['RFA'])); ?>&BreadCrumbs=<?php print(ss_URLEncodedFormat($data['BreadCrumbs'])); ?>" class="bodytextBlue">Change Global settings for <?=$aSite['cfg_website_name']?> <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a><BR>                  
            <?php 
            		}
            		print ('</p> <p>&nbsp;</p>');
            	} else {
            ?>
              	<p><a href="<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Configuration.Edit&cfg_id=1&BackURL=<?php print(ss_URLEncodedFormat($data['RFA'])); ?>&BreadCrumbs=<?php print(ss_URLEncodedFormat($data['BreadCrumbs'])); ?>" class="bodytextBlue">Change
                    Global settings for your website <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
                  <p>&nbsp;</p>
               <?php  } ?>    
              </td>
            </tr> 
            <?php if (ss_optionExists("Payment Configuration")) { ?>
            <tr>
            	<td colspan="2"><img src="Images/h-web-payment.gif" width="260" height="26"></td>
       	   </tr>
            <tr>
              <td>&nbsp;</td>
              <td align="left" valign="top"><p> <a href="index.php?act=WebPayConfiguration.Edit&BackURL=<?php print(ss_URLEncodedFormat($data['RFA'])); ?>&BreadCrumbs=<?php print(ss_URLEncodedFormat($data['BreadCrumbs'])); ?>" class="bodytextBlue">Change
                    Global settings for your web payment <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
                  <p>&nbsp;</p>
              </td>
              
            </tr>
            <?php } ?>
			<?php if ($data['HasOnlineShop']) { ?>
            <tr>
              <td colspan="2"><img src="Images/shop-configuration-h.gif" width="195" height="23"></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td align="left" valign="top"><p><a href="javascript:void(0);" class="bodytextblue" onClick="javascript:parent.openAsset(<?php print($data['OnlineShopAssetID']); ?>,'<?php print($data['CurAssetPath']); ?>', '<?php print($data['OnlineShopAssetPath']); ?>', '<?php print($data['OnlineShopAssetParentLink']); ?>', '<?php print($data['OnlineShopAssetParentPath']); ?>', 'OnlineShop');">Change
                    Global settings for your online shop <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
                  <p>&nbsp;</p>
              </td>
            </tr>
			 <?php } ?>
			  <?php if (ss_optionExists("Countries and Currencies Configurations")) { ?>
			  <tr>
			  	<td colspan="2"><img src="Images/h-country-currency.gif" width="260" height="26"></td>
		  	</tr>
		    <tr>
              <td>&nbsp;</td>
              <td align="left" valign="top"><p><a href="javascript:window.open('index.php?act=CountryAdministration.List', 'Countries', 'height=430,width=590,scrollbars,resizable');void(0);" class="bodytextBlue">Change
                    Global settings for the countries and currencies <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
                  <p>&nbsp;</p>
              </td>
            </tr>
             <?php } ?>
          </table>
      </td>
    </tr>
  </table>
