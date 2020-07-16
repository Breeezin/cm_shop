<HTML>
<!-- lyt_MdiAdmin --->
<HEAD>
		<TITLE><?php if (file_exists(expandPath('Custom/Tags/Title.php'))) require('Custom/Tags/Title.php'); else require('System/Tags/Title.php'); ?></TITLE>
		<LINK REL="STYLESHEET" TYPE="text/css" HREF="sty_admin.css">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?=$GLOBALS['cfg']['Web_Charset']?>">
	<?php if (file_exists(expandPath('Custom/Tags/Head.php'))) require('Custom/Tags/Head.php'); else require('System/Tags/Head.php'); ?>
</HEAD>
<BODY CLASS="adminBackground" LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0">
<!-- ONLOAD="try {if (document.body.focus) document.body.focus(); }catch(e){ }" -->
<?php if (file_exists(expandPath('Custom/Tags/Content.php'))) require('Custom/Tags/Content.php'); else require('System/Tags/Content.php'); ?>
</BODY>
</HTML>
