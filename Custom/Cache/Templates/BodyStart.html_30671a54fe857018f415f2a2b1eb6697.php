<!--- Create the asset tree panel --->

<IFRAME ID="assetPanel" STYLE="visibility:hidden;position:absolute;top:<?php print(ss_HTMLEditFormat($data['AssetPanelTop'])); ?>;left:<?php print(ss_HTMLEditFormat($data['AssetPanelLeft'])); ?>;width:<?php print(ss_HTMLEditFormat($data['AssetPanelWidth'])); ?>px;height:100px;z-index:1000;" NAME="AssetPanelFrame" SRC="<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=TabbedAssetPanel" FRAMEBORDER="0"></IFRAME>
<?php while ($data['Index'] < $data['MaxWindows']) { ?>
<IFRAME SRC="<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>loading.html" ID="F<?php print(ss_HTMLEditFormat($data['Index'])); ?>" NAME="Frame<?php print(ss_HTMLEditFormat($data['Index'])); ?>" WIDTH="100%" HEIGHT="100%" STYLE="position:absolute;width:400;height:100;left:<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>;top:<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>;visibility:hidden;z-index:0;border:1px;" FRAMEBORDER="0"></IFRAME>
<?php $data['Index']++; ?>	<?php } ?>
<IFRAME SRC="<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>loading.html" ID="FReportsAndStats" NAME="FrameReportsAndStats" WIDTH="100%" HEIGHT="100%" STYLE="position:absolute;width:400;height:100;left:<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>;top:<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>;visibility:hidden;z-index:0;border:1px;" FRAMEBORDER="0"></IFRAME>
<IFRAME SRC="<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=TabbedInterfaceConfiguration" ID="FConfiguration" NAME="FrameConfiguration" WIDTH="100%" HEIGHT="100%" STYLE="position:absolute;width:400;height:100;left:<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>;top:<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>;visibility:hidden;z-index:0;border:1px;" FRAMEBORDER="0"></IFRAME>
<div id="popupWindow" style="display:none;position:absolute;z-index:1000000;background-color:blue;border:1px solid black;">
  <table width="100%" cellpadding="2" cellspacing="0">
    <tr>
      <td>&nbsp;<span id="popupWindowTitle" style="color:white;font-weight:bold;"></span></td>
      <td align="right"><span id="popupWindowClose">
        <button onclick="popup_window_close();"><strong>&nbsp;x&nbsp;</strong></button>
        </span></td>
    </tr>
  </table>
  <div style="background-color:white;border:1px insert gray;padding:2px;">
    <IFRAME SRC="<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>loading.html" ID="FPopupWindow" name="FramePopupWindow" STYLE="width:100%;height:100%;z-index:1000001;overflow:auto;"></IFRAME>
  </div>
</div>
<?php if ($data['Authencated']) { ?>
<IFRAME SRC="<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>loading.html" ID="FUsers" NAME="FrameUsers" WIDTH="100%" HEIGHT="100%" STYLE="position:absolute;width:400;height:100;left:<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>;top:<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>;visibility:hidden;z-index:0;border:1px;" FRAMEBORDER="0"></IFRAME>
<?php } else { ?>
<IFRAME SRC="<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>loading.html" ID="FUsers" NAME="FrameUsers" WIDTH="100%" HEIGHT="100%" STYLE="position:absolute;width:400;height:100;left:<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>;top:<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>;visibility:hidden;z-index:0;border:1px;" FRAMEBORDER="0"></IFRAME>
<?php } ?>
<div id="assetBar" class="siteComponentBar" style="display:none;">
  <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td id="assetBarCell" height="21" valign="middle" class="assetBarOpen" onclick="togglePanel('asset');"><span class="panelBarText">Website</span></TD>
    </tr>
  </table>
</div>
<div id="propertiesBar" class="siteComponentBar" style="display:none;">
  <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td id="propertiesBarCell" height="21" valign="middle" class="assetBarOpen" onclick="togglePanel('properties');"><span id="propertiesPanelBarText" class="panelBarText">Properties</span></TD>
    </tr>
  </table>
</div>
<script language="Javascript">
		function updateAssetName() {
			if (checkAssetName()) {
				form = document.forms.PropertiesPanelForm;
				if (form.as_id.value.length) {
					if (!form.as_name.disabled) {
						assetName = form.as_name.value;
						assetID = form.as_id.value;
						document.getElementById('propertiesLoader').src = 'index.php?act=Asset.UpdateNameAndAppearsInMenus&as_id='+assetID+'&as_name='+escape(assetName);
					} else {
						alert('You do not have permission to change these settings on this item.');
					}
				}
			}
		}
		function updateAssetAppearsInMenus() {
			form = document.forms.PropertiesPanelForm;
			if (form.as_id.value.length) {
				if (!form.as_name.disabled) {
					assetAppearsInMenus = 0;
					if (form.as_appear_in_menus.checked) assetAppearsInMenus = 1;
					assetID = form.as_id.value;
					document.getElementById('propertiesLoader').src = 'index.php?act=Asset.UpdateNameAndAppearsInMenus&as_id='+assetID+'&as_appear_in_menus='+assetAppearsInMenus;
				} else {
					alert('You do not have permission to change these settings on this item.');
				}
			}
		}
	</script>
<script type="text/javascript">
					<!--
						// copyright 1999 Idocs, Inc. http://www.idocs.com
						// Distribute this script freely but keep this notice in place
						var allowedCharacters = 'abcdefghijklmnopqrstuvwxyz 0123456789-,.)(';
						
						function letternumber(e) {
							var key;
							var keychar;
							
							if (window.event)
							   key = window.event.keyCode;
							else if (e)
							   key = e.which;
							else
							   return true;
							keychar = String.fromCharCode(key);
							keychar = keychar.toLowerCase();
							
							// control keys
							if ((key==null) || (key==0) || (key==8) || 
							    (key==9) || (key==13) || (key==27) )
							   return true;
							
							// alphas and numbers
							else if ((allowedCharacters.indexOf(keychar) > -1))
							   return true;
							else {
							   alert('Sorry, that character is not allowed in the item name.\n\nIf you would like to use special characters, open the\n\'layout\' bar for this item and enter values in the \'titles\' section.');
							   return false;
							}
						}
						
						
					  	function checkAssetName() {
							// This is a safety incase they try to do a cut and paste
							var checkStr = document.forms.PropertiesPanelForm.as_name.value;
							var allValid = true;
							var newVersion = '';
							for (var i = 0;  i < checkStr.length;  i++) {
								ch = checkStr.charAt(i);
								if (allowedCharacters.indexOf(ch.toLowerCase()) == -1) {
									allValid = false;
								} else {
									newVersion += ch;
								}
							}
							if (!allValid) {
								document.forms.PropertiesPanelForm.as_name.value = newVersion;
								alert('Sorry, one or more characters from your item name are not allowed.\n\nIf you would like to use special characters, open the\n\'layout\' bar for this item and enter values in the \'titles\' section.');
								return false;
							}
							return true;
						}
						
					//-->
					</script>
<div id="propertiesPanel" class="siteComponentBar propertiesPanel" style="height:120px;display:none;">
  <table cellpadding="0" cellspacing="0" width="100%">
    <form name="PropertiesPanelForm">
      <tr>
        <td><img src="Images/holder.gif" width="1" height="120"></td>
        <td valign="top">
          <input type="hidden" name="as_id" value="">
          <table cellpadding="0" cellspacing="2" width="100%">
            <tr>
              <td class="propertiesLabel">Name :</td>
              <td><input onKeyPress="return letternumber(event);" onchange="updateAssetName();" name="as_name" style="width:85px;" value="" disabled="disabled">
              </td>
              <!--					<td><a href="Javascript:void(0);"><img src="Images/but-save.gif" border="0"><a></td>-->
            </tr>
            <tr>
              <td class="propertiesLabel" nowrap colspan="2">Appears&nbsp;In&nbsp;Menus&nbsp;:
                <input onclick="updateAssetAppearsInMenus();" style="border:0px;" name="as_appear_in_menus" type="checkbox" value="1" disabled="disabled">
              </td>
            </tr>			
          </table>
          <div id="assetTypeProperties" style="height:60px;padding:0px;margin:0px;">&nbsp;</div>
        </td>
      </tr>
    </form>
  </table>
  <iframe style="width:100px;height:100px;display:none;" id="PropertiesLoader" src=""></iframe>
</div>
<div id="shopBar" class="siteComponentBar" style="display:none;">
  <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td id="shopBarCell" height="21" valign="top" class="shopBarOpen"> <A HREF="Javascript:togglePanel('shop');" ONCLICK="this.blur();"><IMG SRC="Images/holder.gif" HEIGHT="21" WIDTH="<?php print(ss_HTMLEditFormat($data['BarWidth'])); ?>" BORDER="0"></A></TD>
    </tr>
  </table>
</div>
<div id="shopPanel" class="siteComponentBar" style="display:none;"> </div>
<div id="newsletterBar" class="siteComponentBar" style="display:none;">
  <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td id="newsletterBarCell" height="21" valign="top" class="newsletterBarOpen"> <A HREF="Javascript:togglePanel('newsletter');" ONCLICK="this.blur();"><IMG SRC="Images/holder.gif" HEIGHT="21" WIDTH="<?php print(ss_HTMLEditFormat($data['BarWidth'])); ?>" BORDER="0"></A></TD>
    </tr>
  </table>
</div>
<div id="newsletterPanel" class="siteComponentBar" style="display:none;">
  <table valign="top" cellpadding="0" cellspacing="0">
    <tr>
      <td align="left" valign="top" background="images/tree-mid.gif">
        <table cellpadding="5">
          <tr>
            <td valign="top"> <a href="" class="TreeItem">Create / Send</a><br>
              <a href="" class="TreeItem">Manage Archive</a> </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
<DIV id="assetTreeTools" class="siteComponentBar" style="display:none;"><a href="Javascript:addFromTree('Page');void(0);"><IMG border="0" SRC="Images/but-tree-page.gif" ALT="New Page" NAME="pagenew" WIDTH="25" HEIGHT="23" ID="pagenew" onMouseOver="MM_swapImage('pagenew','','Images/but-tree-page-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a><a href="Javascript:addFromTree('Folder');void(0);"><IMG border="0" SRC="Images/but-tree-image.gif" ALT="New Folder" NAME="imagenew" WIDTH="26" HEIGHT="23" ID="imagenew" onMouseOver="MM_swapImage('imagenew','','Images/but-tree-image-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a><a href="Javascript:addFromTree('File');void(0);"><IMG border="0" SRC="Images/but-tree-file.gif" ALT="New File Item" NAME="filenew" WIDTH="26" HEIGHT="23" ID="filenew" onMouseOver="MM_swapImage('filenew','','Images/but-tree-file-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a><a href="Javascript:deleteFromTree();void(0);"><IMG border="0" SRC="Images/but-tree-delete.gif" ALT="Delete Item" NAME="delete" WIDTH="26" HEIGHT="23" ID="delete" onMouseOver="MM_swapImage('delete','','Images/but-tree-delete-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a><a href="Javascript:moveAssetDown();void(0);"><IMG border="0" SRC="Images/but-tree-down.gif" ALT="Move Item Down" NAME="movedown" WIDTH="25" HEIGHT="23" ID="movedown" onMouseOver="MM_swapImage('movedown','','Images/but-tree-down-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a><a href="Javascript:moveAssetUp();void(0);"><IMG border="0" SRC="Images/but-tree-up.gif" ALT="Move Item Up" NAME="moveup" WIDTH="25" HEIGHT="23" ID="moveup" onMouseOver="MM_swapImage('moveup','','Images/but-tree-up-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a></DIV>
<iframe style="width:100px;height:50px;display:none;" id="AddAssetFromTreeLoader" src=""></iframe>
<div id="preloadTabs" style="display:none;"> <img src="Images/tab-1.gif"> <img src="Images/tab-1-on.gif"> <img src="Images/tab-2.gif"> <img src="Images/tab-2-on.gif"> <img src="Images/tab-3.gif"> <img src="Images/tab-3-on.gif"> <img src="Images/tab-4.gif"> <img src="Images/tab-4-on.gif"> </div>
<!-- Keep session alive --->
<iframe id="keepAlive" style="display:none;"></iframe>
<script language="Javascript">
		function doKeepAlive() {
			ka = document.getElementById('keepAlive');
			ka.src="index.php?act=Ping";
			setTimeout('doKeepAlive()',10*60*1000);
		}
		doKeepAlive();
</script>
