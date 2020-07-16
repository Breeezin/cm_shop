<script language="JavaScript" type="text/JavaScript">
<!--
function MM_callJS(jsStr) { //v2.0
  return eval(jsStr)
}
//-->
</script>
<link href="sty_admin.css" rel="stylesheet" type="text/css">

 <p>&nbsp;</P>
  <div align="center"></div>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="bodytext">
    <tr>
      <td width="213" align="left" valign="top"><table width="213" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td colspan="2"><img src="Images/welcome1.gif" width="250" height="125"></td>
        </tr>
        <tr>
          <td width="178" align="left" valign="bottom">&nbsp;</td>
          <td width="72" height="172" background="Images/welcome-right-bot.gif">&nbsp;</td>
        </tr>
      </table></td>
      <td align="left" valign="top"><?php if (!ss_hasPermission('CanReview')) { ?><img src="Images/holder.gif" width="25" height="15"><?php } ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td colspan="2"><img src="Images/content-h.gif" width="172" height="23"></td>
        </tr>
        <tr>
          <td width="25"><img src="Images/holder.gif" width="25" height="15"></td>
          <td align="left" valign="top"><p><SPAN CLASS="bodytextBlue">By selecting an item from the item tree,
            you can create, edit or delete items</SPAN> <img src="Images/go-arrow.gif" width="9" height="9"></p>
            <p>&nbsp;</p></td>
        </tr>
        <tr>
          <td colspan="2"><img src="Images/report-stats-h.gif" width="185" height="23"></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td align="left" valign="top"><p><a href="javascript:void(0);" class="bodytextBlue" onClick="parent.setCurrentTopTab(2);parent.showNonContentTab('ReportsAndStats');">View detailed statistics for each
              page on your website <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
              <p>&nbsp;</p>
          </td>
        </tr>
        <tr>
          <td colspan="2"><img src="Images/configuration-h.gif" width="172" height="23"></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td align="left" valign="top"><p><a href="javascript:void(0);" class="bodytextBlue" onclick="parent.setCurrentTopTab(3);parent.showNonContentTab('Configuration');">Change Global settings for your website <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
            <p>&nbsp;</p></td>
        </tr>
        <tr>
          <td colspan="2"><img src="Images/users-h.gif" width="172" height="23"></td>
        </tr>
        <tr>
          <td >&nbsp;</td>
          <td align="left" valign="top"><p><a href="javascript:void(0);" class="bodytextBlue" onclick="parent.setCurrentTopTab(4);parent.showNonContentTab('Users');">Manage User Groups <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
            <p>&nbsp;</p></td>
        </tr>
        <?php if (ss_hasPermission('CanReview')) { ?>
        <tr>
          <td colspan="2"><img src="Images/review-h.gif"></td>
        </tr>
        <tr>
          <td >&nbsp;</td>
          <?
          
				$review = getRow("
					SELECT COUNT(*) AS TheCount FROM Assets
					WHERE AssetReview = 1
						AND AssetReviewer = ".ss_getUserID()."
				");
				
          ?>
          <td align="left" valign="top"><p><a href="javascript:void(0);" class="bodytextBlue" onclick="parent.openNamedNonAssetPanel('index.php?act=Review.List','Review','ReviewAssets');return false;">Review <?=$review['TheCount'];?> <?=ss_pluralize($review['TheCount'],'Item','Items');?> <img src="Images/go-arrow.gif" width="9" height="9" border="0"></a></p>
            <p>&nbsp;</p></td>
        </tr>
        <?php } ?>
        <tr>
          <td >&nbsp;</td>
          <td align="left" valign="top">
		        <p style="font-weight:bold;"><?php print(ss_HTMLEditFormat($data['limitInfo'])); ?></p></td>
	 	</tr>
      </table></td>
    </tr>
  </table>
