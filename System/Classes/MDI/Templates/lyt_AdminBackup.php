<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<!-- lyt_Admin -->
<head>
<title>Administration</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=['cfg']['Web_Charset']?>">
<link href="sty_admin.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/JavaScript">
	function mykeyhandler() {
		if (window.event && window.event.keyCode == 8) { // try to cancel the backspace
			if (window.event.srcElement.tagName.toUpperCase() != "INPUT" && window.event.srcElement.tagName.toUpperCase() != "TEXTAREA") {
				window.event.cancelBubble = true;
				window.event.returnValue = false;
				return false;
			}		 
		}
	}
	document.onkeydown = mykeyhandler;

function mmLoadMenus() {
  if (window.mm_menu_0507142727_0) return;
                                      window.mm_menu_0507142727_0 = new Menu("root",140,19,"Arial, Helvetica, sans-serif",11,"#000000","#000000","#FFFFFF","#84A2AF","left","middle",4,0,200,-5,7,true,true,true,0,true,true);
  mm_menu_0507142727_0.addMenuItem("New&nbsp;Asset","window.open('index.php?act=Asset.Add', 'addAsset', 'width=300,height=610')");

<?php 
	$temp = new Request('Security.Authenticate',array(
		'Permission'	=>	'IsDeployer',
		'LoginOnFail'	=>	false,
	));
	$isTheDeployer = $temp->value;
	if ($isTheDeployer) { ?>

  mm_menu_0507142727_0.addMenuItem("Create&nbsp;Multiple&nbsp;assets","window.open('index.php?act=Asset.AddMany', 'addAsset', 'width=650,height=400,scrollbar=1')");
  mm_menu_0507142727_0.addMenuItem("Export&nbsp;All&nbsp;Pages","window.open('index.php?act=Export.AllPages&DisableOutputBuffering=Yes', 'Export', 'width=300,height=100')");  
  mm_menu_0507142727_0.addMenuItem("Import&nbsp;All&nbsp;Pages","window.open('index.php?act=Import.AllPages&DisableOutputBuffering=Yes', 'Import', 'width=300,height=100')");
<?php } ?>

//  mm_menu_0507142727_0.addMenuItem("View&nbsp;statistics","openStatPanel();");
//  mm_menu_0507142727_0.addMenuItem("View&nbsp;Website","openWebsitePanel();");
  mm_menu_0507142727_0.addMenuItem("Close","fileClose();");
  mm_menu_0507142727_0.addMenuItem("Exit","fileExit();");
   mm_menu_0507142727_0.hideOnMouseOut=true;
   mm_menu_0507142727_0.bgColor='#FFFFFF';
   mm_menu_0507142727_0.menuBorder=1;
   mm_menu_0507142727_0.menuLiteBgColor='#FFFFFF';
   mm_menu_0507142727_0.menuBorderBgColor='#999999';

    window.mm_menu_0507153045_0 = new Menu("root",132,17,"Arial, Helvetica, sans-serif",11,"#000000","#000000","#FFFFFF","#84A2AF","left","middle",3,0,200,-5,7,true,true,true,0,true,true);
//  mm_menu_0507153045_0.addMenuItem("Options","location='#'");
<?php if ($isTheDeployer) {?>
	  mm_menu_0507153045_0.addMenuItem("Asset&nbsp;Types","window.open('<?php print($_SERVER['SCRIPT_NAME']);?>?act=AssetTypesAdministration.List&RowsPerPage=1000', 'editAssetTypes', 'width=600,height=440,scrollbars,menubar')");
 <?php } ?>
  mm_menu_0507153045_0.addMenuItem("Change&nbsp;Password","window.open('index.php?act=UsersAdministration.Edit&us_id=<?php print($_SESSION['User']['us_id']);?>&NoGroups=Yes&BackURL=<?php print(ss_URLEncodedFormat('index.php?fuseaction=closeWin'));?>', 'editPassword', 'width=600,height=440,scrollbars,menubar')");
   mm_menu_0507153045_0.hideOnMouseOut=true;
   mm_menu_0507153045_0.bgColor='#FFFFFF';
   mm_menu_0507153045_0.menuBorder=1;
   mm_menu_0507153045_0.menuLiteBgColor='#FFFFFF';
   mm_menu_0507153045_0.menuBorderBgColor='#999999';
  window.mm_menu_0507153229_0 = new Menu("root",51,17,"Arial, Helvetica, sans-serif",11,"#000000","#000000","#FFFFFF","#84A2AF","left","middle",3,0,200,-5,7,true,true,true,0,true,true);
  mm_menu_0507153229_0.addMenuItem("About","alert('This is an alert box');");
   mm_menu_0507153229_0.hideOnMouseOut=true;
   mm_menu_0507153229_0.bgColor='#FFFFFF';
   mm_menu_0507153229_0.menuBorder=1;
   mm_menu_0507153229_0.menuLiteBgColor='#FFFFFF';
   mm_menu_0507153229_0.menuBorderBgColor='#999999';

mm_menu_0507153229_0.writeMenus();
} // mmLoadMenus()
</script>
<script language="JavaScript" src="System/Classes/MDI/mm_menu.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

{[Head]}

//-->
</script>
</head>

<body bgcolor="white" alink="485C65" vlink="485C65" link="485C65" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="MM_preloadImages('Images/tab-reportstats-on.gif','Images/tab-configuration-on.gif','Images/tab-users-on.gif','Images/but-save-lb.gif','Images/but-save-on.gif','Images/but-move-lb.gif','Images/but-move-on.gif','Images/but-copy-lb.gif','Images/but-copy-on.gif','Images/but-delete-lb.gif','Images/but-delete-on.gif','Images/i-close.gif','Images/i-edit.gif','Images/but-tree-image-on.gif','Images/but-tree-file-on.gif','Images/but-tree-delete-on.gif','Images/but-tree-down-on.gif','Images/but-tree-up-on.gif','Images/but-tree-page-on.gif');init()">
<script language="JavaScript1.2">mmLoadMenus();</script>

<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr align="right" valign="bottom">
    <td width="100%" height="58" colspan="2" background="Images/topbanner.jpg">
	    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr><td align="right" id="topTabs" style="display:none;"><a href="Javascript:void(0);" onclick="setCurrentTopTab(1);focusWindow(currentWindow);"><img src="Images/tab-1-on.gif" name="Tab1" width="79" height="20" border="0" id="Tab1" onMouseOver="tabMouseOver(1);" onMouseOut="tabMouseOut(1);"></A><IMG SRC="Images/tabspace.gif" WIDTH="18" HEIGHT="20"><a href="Javascript:void(0);" onclick="setCurrentTopTab(2);showNonContentTab('ReportsAndStats');"><img src="Images/tab-2.gif" name="Tab2" width="123" height="20" border="0" id="Tab2" onMouseOver="tabMouseOver(2);" onMouseOut="tabMouseOut(2);"></A><IMG SRC="Images/tabspace.gif" WIDTH="18" HEIGHT="20"><a href="Javascript:void(0);" onclick="setCurrentTopTab(3);showNonContentTab('configuration');"><img src="Images/tab-3.gif" name="Tab3" width="114" height="20" border="0" id="Tab3" onMouseOver="tabMouseOver(3);" onMouseOut="tabMouseOut(3);"></A><IMG SRC="Images/tabspace.gif" WIDTH="18" HEIGHT="20"><a href="Javascript:void(0);" onclick="setCurrentTopTab(4);showNonContentTab('users');"><img src="Images/tab-4.gif" name="Tab4" width="65" height="20" border="0" id="Tab4" onMouseOver="tabMouseOver(4);" onMouseOut="tabMouseOut(4);"></A><IMG SRC="Images/tabspace.gif" WIDTH="18" HEIGHT="20"></td>
      </tr>
    </table>
   </td>
  </tr>
  <tr>
    <td height="18" colspan="2" align="left" valign="top" class="topline-light">
      <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr> 
          <td width="10"><img src="Images/holder.gif" width="10" height="10"></td>
          <td class="TreeItem"><a style="text-decoration:none" href="javascript:void(0);" name="link3" id="link1" onMouseOver="MM_showMenu(window.mm_menu_0507142727_0,0,12,null,'link3')" onMouseOut="MM_startTimeout();">File</a><img src="Images/holder.gif" width="20" height="10"> <a style="text-decoration:none" href="javascript:void(0);" name="link5" id="link2" onMouseOver="MM_showMenu(window.mm_menu_0507153045_0,0,12,null,'link5')" onMouseOut="MM_startTimeout();">Edit</a><img src="Images/holder.gif" width="20" height="10"><a style="text-decoration:none" href="javascript:void(0);" name="link6" id="link4" onMouseOver="MM_showMenu(window.mm_menu_0507153229_0,0,12,null,'link6')" onMouseOut="MM_startTimeout();">Help</a>
<!---		  File<img src="Images/holder.gif" width="20" height="10"> 
            Edit<img src="Images/holder.gif" width="20" height="10"> View<img src="Images/holder.gif" width="20" height="10"> 
            Tools <img src="Images/holder.gif" width="20" height="10">Help---></td>
          <td align="right" valign="top" class="TreeItem"><A HREF="<?php print($_SERVER['SCRIPT_NAME']);?>?act=Security.Logout" STYLE="text-decoration:none">Logout</A></td>
          <td align="right" valign="top" width="10"><img src="Images/holder.gif" width="10" height="10"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td height="28" colspan="2" align="left" valign="top" class="topline-dark"><div align="right">
        <table width="100%" border="0" cellpadding="2" cellspacing="0">
          <tr> 
            <td width="10"><img src="Images/holder.gif" width="10" height="10"></td>
            <td align="left" valign="middle" class="CustomerName">Welcome <?php print($_SESSION['User']['us_first_name'].' '.$_SESSION['User']['us_last_name']); if(ss_isOffline()) {?> <FONT COLOR="#FFFF00">- <SPAN>Offline Version</SPAN></FONT>           	 <?php } ?></td>
            <td>
              <div align="right"><img src="Images/h-administration.jpg" alt="Website Administration" width="195" height="27"></div>
            </td>
          </tr>
        </table>
    </div></td>
  </tr>
  <tr>
    <td height="100%" colspan="2" align="left" valign="top"><table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="140"><table width="180" height="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="180" height="100%"><table height="100%" border="0" cellpadding="0" cellspacing="0" CLASS="page">
                <tr>
                  <td height="25"><img src="Images/tree-header.gif" width="153" height="25"></td>
                </tr>
<!--				<tr>
<td height="23" align="center" valign="top" id="treeTools"></td>				
</tr>-->
<!---                <tr>
                  <td align="left" valign="top" background="Images/tree-mid.gif"><img src="Images/tab-website.gif" width="156" height="21"><br>
                      <br>
                      <br>
                  </td>
                </tr>--->
                <tr>
                  <td HEIGHT="100%" id="siteComponents">&nbsp;</td>
                </tr>
                <tr>
                	<td><table width="100%" border="0" cellspacing="0" cellpadding="6">
                    	<tr>
                    		<td><img border="0" src="Custom/ContentStore/Layouts/Images/adminClientLogo.gif" width="138" height="88"></td>
               		 </tr>
                    	</table></td>
           	 </tr>
              </table>
            </td>
          </tr>
        </table></td>
        <td width="100%" height="100%" align="left" valign="top"><table width="100%" height="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td align="left" valign="top"><table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" id="tabbedPagesHolder">
                <tr>
                  <td height="24" id="TaskBarCell"><span id="TaskBar"><table width="100%" height="24" border="0" cellpadding="0" cellspacing="0">
                      <tr>
<!---                        <td width="7" class="tab-bottomline"><img src="Images/tab-start.gif" width="7" height="24"></td>
                        <td width="150" background="Images/tab-inner.gif" class="tab-bottomline"><table width="100%" border="0" cellpadding="2" cellspacing="0" class="tabtext-off">
                            <tr>
                              <td><div align="center">Home</div>
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td width="7" class="tab-bottomline"><img src="Images/tab-end.gif" width="7" height="24"></td>
                        <td width="7" class="tab-on"><img src="Images/tab-start-inner.gif" width="7" height="24"></td>
                        <td width="150" background="Images/tab-inner.gif" class="tab-on"><table width="100%" border="0" cellpadding="2" cellspacing="0" class="tabtext-on">
                            <tr>
                              <td><div align="center">Stats</div>
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td width="7" class="tab-on"><img src="Images/tab-end.gif" width="7" height="24"></td>--->
                        <td class="line-notab" align="right" valign="middle">&nbsp;<!--<A HREF="Javascript:void(0);"><IMG SRC="Images/close.gif" BORDER="0" WIDTH="19" HEIGHT="17"></A>--></td>
                      </tr>
                    </table></SPAN>
                  </td>
                </tr>
                <tr>
                  <td height="100%" align="left" valign="top"><table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" class="page">
                      <tr>
                        <td align="left" valign="top" CLASS="tabtext-on"><br>
<!---						
                            <table width="100%" border="0" cellspacing="0" cellpadding="6">
                              <tr>
                                <td width="140" align="left" valign="middle"><span class="bodytext">Name</span><span class="tabtext-off">
                                  <input name="textfield" type="text" value="Stupid Page">
                                </span></td>
                                <td align="left" valign="middle"><select name="select">
                                    <option>Appears in Menus</option>
                                    <option>Does Not Appear in Menus</option>
                                  </select>
                                </td>
                                <td align="right" valign="top"><div align="right"></div>
                                    <table width="100" border="0" cellpadding="0" cellspacing="0">
                                      <tr>
                                        <td width="70%"><img src="Images/but-holder-lb.gif" name="label" width="83" height="27" id="label"></td>
                                        <td><img src="Images/but-save.gif" name="Image1" width="28" height="27" id="Image1" onMouseOver="MM_swapImage('label','','images/but-save-lb.gif','Image1','','images/but-save-on.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                        <td><img src="images/but-move.gif" name="Image2" width="30" height="27" id="Image2" onMouseOver="MM_swapImage('label','','images/but-move-lb.gif','Image2','','images/but-move-on.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                        <td><img src="images/but-copy.gif" name="Image3" width="30" height="27" id="Image3" onMouseOver="MM_swapImage('label','','images/but-copy-lb.gif','Image3','','images/but-copy-on.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                        <td><img src="images/but-delete.gif" name="Image4" width="30" height="27" id="Image4" onMouseOver="MM_swapImage('label','','images/but-delete-lb.gif','Image4','','images/but-delete-on.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                      </tr>
                                    </table>
                                    <div align="right"></div>
                                </td>
                              </tr>
                            </table>
                            <br>
                            <table width="100%" height="21" border="0" cellpadding="2" cellspacing="0" background="images/background-tab.gif">
                              <tr>
                                <td>
                                  <table width="100%" border="0" cellpadding="1" cellspacing="0">
                                    <tr>
                                      <td width="20"><img src="images/i-page.gif" width="9" height="11"></td>
                                      <td class="tabtext-off">Page</td>
                                      <td align="right" valign="middle" class="tabtext-off"> <img src="images/i-label.gif" name="page" width="51" height="14" id="page"><img src="images/but-arrow.gif" width="11" height="10" onMouseOver="MM_swapImage('page','','images/i-close.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                            <br>
                            <br>
                            <table width="100%" height="21" border="0" cellpadding="2" cellspacing="0" background="images/background-tab-off.gif">
                              <tr>
                                <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
                                    <tr>
                                      <td width="20"><img src="images/i-layout.gif" width="10" height="10"></td>
                                      <td class="tabtext-off">Layout</td>
                                      <td align="right" valign="top" class="tabtext-off"><img src="images/i-label.gif" name="layout" width="51" height="14" id="layout"><img src="images/but-arrow-down.gif" width="11" height="10" onMouseOver="MM_swapImage('layout','','images/i-edit.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                            <table width="100%" height="21" border="0" cellpadding="2" cellspacing="0" background="images/background-tab-off.gif">
                              <tr>
                                <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
                                    <tr>
                                      <td width="20"><img src="images/i-sub.gif" width="10" height="11"></td>
                                      <td class="tabtext-off">Sub-assets</td>
                                      <td align="right" valign="top" class="tabtext-off"><img src="images/i-label.gif" name="sub" width="51" height="14" id="sub"><img src="images/but-arrow-down.gif" width="11" height="10" onMouseOver="MM_swapImage('sub','','images/i-edit.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                            <table width="100%" height="21" border="0" cellpadding="2" cellspacing="0" background="images/background-tab-off.gif">
                              <tr>
                                <td><table width="100%" border="0" cellpadding="1" cellspacing="0">
                                    <tr>
                                      <td width="20"><img src="images/i-security.gif" width="8" height="10"></td>
                                      <td class="tabtext-off">Security</td>
                                      <td align="right" valign="top" class="tabtext-off"><img src="images/i-label.gif" name="security" width="51" height="14" id="security"><img src="images/but-arrow-down.gif" width="11" height="10" onMouseOver="MM_swapImage('security','','images/i-edit.gif',1)" onMouseOut="MM_swapImgRestore()"></td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                            <br>
							--->
                       </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>{[BodyEnd]}</body>
</html>
