<?php header( "X-XSS-Protection: 0" ); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title><?php if (file_exists(expandPath('Custom/Tags/SimpleTitle.php'))) require('Custom/Tags/SimpleTitle.php'); else require('System/Tags/SimpleTitle.php'); ?></title>
	<META NAME="keywords" 	     CONTENT="<?php if (file_exists(expandPath('Custom/Tags/Keywords.php'))) require('Custom/Tags/Keywords.php'); else require('System/Tags/Keywords.php'); ?>">
	<META NAME="description" 	 CONTENT="<?php if (file_exists(expandPath('Custom/Tags/Description.php'))) require('Custom/Tags/Description.php'); else require('System/Tags/Description.php'); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=<?=$GLOBALS['cfg']['Web_Charset']?>">
	<link rel="stylesheet" href="sty_Administration<?php print ss_optionExists('Advanced Administration')?'Upgraded':'';?>.css" type="text/css">
</head>
<script language="Javascript">
<!--
    var needsReload;

    function doOnFocus()
    {
        if( needsReload > 0 )
        {
            location.reload();
            needsReload = 0;
        }
    }
-->
</script>

<BODY  STYLE="border:0px; background-color:#f9f9f9;" onFocus="doOnFocus();">
<STRONG><?php if (file_exists(expandPath('Custom/Tags/Title.php'))) require('Custom/Tags/Title.php'); else require('System/Tags/Title.php'); ?></STRONG>

<?php if (file_exists(expandPath('Custom/Tags/Content.php'))) require('Custom/Tags/Content.php'); else require('System/Tags/Content.php'); ?>

</BODY>
</html>
